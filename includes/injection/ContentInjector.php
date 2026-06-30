<?php
namespace SmartAdInserter\Injection;

use DOMDocument;

/**
 * Gestisce l'iniezione degli annunci posizionati all'interno dell'articolo (Content-based).
 *
 * Questa classe implementa la strategia concreta per il posizionamento dei banner
 * legati al flusso del testo dell'articolo (es. ATF - Above The Fold, BTF - Below The Fold).
 * Utilizza DOMDocument applicato al solo frammento dell'articolo per identificare il tag <p>.
 *
 * @since      1.0.0
 * @package    Smart_Ad_Inserter
 * @subpackage Smart_Ad_Inserter/includes/injection
 * @author     Carmine Muollo
 */
class ContentInjector implements AdInjectorInterface {

	/**
	 * Mappa delle opzioni di configurazione delle posizioni.
	 *
	 * @since    1.0.0
	 * @var      array
	 */
	protected $settings;

	/**
	 * Inizializza la classe con le opzioni configurate.
	 *
	 * @since    1.0.0
	 * @param    array    $settings    Mappa delle opzioni di configurazione.
	 */
	public function __construct( array $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Esegue l'iniezione in base alle strategie configurate sul contenuto.
	 *
	 * @since    1.0.0
	 * @param    string    $content     L'HTML originario del contenuto.
	 * @return   string                 L'HTML modificato.
	 */
	public function inject( string $content ): string {
		// Iniezione ATF (Subito prima del primo paragrafo dell'articolo)
		if ( ! empty( $this->settings['atf']['active'] ) && ! empty( trim( $this->settings['atf']['code'] ?? '' ) ) ) {
			$content = $this->inject_atf( $content );
		}

		// Iniezione In text
		if ( ! empty( $this->settings['in_text']['active'] ) && ! empty( trim( $this->settings['in_text']['code'] ?? '' ) ) ) {
			$content = $this->inject_in_text( $content, $this->settings['in_text'] );
		}

		// Iniezione BTF (Al fondo del testo dell'articolo)
		if ( ! empty( $this->settings['btf']['active'] ) && ! empty( trim( $this->settings['btf']['code'] ?? '' ) ) ) {
			$content = $this->inject_btf( $content );
		}

		return $content;
	}

	/**
	 * Inietta il wrapper pubblicitario ATF prima del primo paragrafo del testo.
	 *
	 * Utilizza DOMDocument sul solo frammento di testo per garantire leggerezza e velocità
	 * di parsing lato server, azzerando il CLS grazie alle altezze minime pre-allocate.
	 *
	 * @since    1.0.0
	 * @param    string    $content     L'HTML originario del contenuto.
	 * @return   string                 L'HTML modificato.
	 */
	private function inject_atf( string $content ): string {
		$ad_code = trim( $this->settings['atf']['code'] ?? '' );
		if ( $ad_code === '' ) {
			return $content;
		}
		$min_h_desktop = $this->settings['atf']['min_height_desktop'] ?? 250;
		$min_h_mobile  = $this->settings['atf']['min_height_mobile'] ?? 250;

		libxml_use_internal_errors( true );
		$dom = new DOMDocument();
		// Forza il caricamento in UTF-8 per evitare la corruzione degli accenti italiani
		$dom->loadHTML( '<?xml encoding="UTF-8"><div>' . $content . '</div>', LIBXML_HTML_NODEFDTD );
		libxml_clear_errors();

		$p_elements = $dom->getElementsByTagName( 'p' );
		if ( $p_elements->length > 0 ) {
			$first_p  = $p_elements->item( 0 );
			
			// Crea il wrapper div direttamente nel DOM di destinazione
			$wrapper_el = $dom->createElement( 'div' );
			$wrapper_el->setAttribute( 'class', 'sai-ad-wrapper sai-atf' );
			$wrapper_el->setAttribute( 'style', sprintf( 'min-height:%dpx; --min-h-mobile:%dpx;', $min_h_desktop, $min_h_mobile ) );

			if ( ! empty( $this->settings['atf']['override_css'] ) ) {
				$wrapper_el->setAttribute( 'style', $wrapper_el->getAttribute( 'style' ) . ' ' . $this->settings['atf']['override_css'] );
			}

			// Carica l'ad_code como HTML e importalo nel wrapper
			$temp_dom = new DOMDocument();
			libxml_use_internal_errors( true );
			$temp_dom->loadHTML( '<?xml encoding="UTF-8"><div>' . $ad_code . '</div>', LIBXML_HTML_NODEFDTD );
			libxml_clear_errors();

			$temp_div = $temp_dom->getElementsByTagName( 'div' )->item( 0 );
			if ( $temp_div ) {
				foreach ( $temp_div->childNodes as $child ) {
					$imported = $dom->importNode( $child, true );
					$wrapper_el->appendChild( $imported );
				}
			}

			$first_p->parentNode->insertBefore( $wrapper_el, $first_p );
		}

		// Estrae l'HTML interno privo del wrapper div inserito per il parsing
		$root_div    = $dom->getElementsByTagName( 'div' )->item( 0 );
		$new_content = '';
		if ( $root_div ) {
			foreach ( $root_div->childNodes as $child ) {
				$new_content .= $dom->saveHTML( $child );
			}
		}

		return ! empty( $new_content ) ? $new_content : $content;
	}

	/**
	 * Inietta il wrapper pubblicitario BTF concatenandolo al fondo del testo.
	 *
	 * @since    1.0.0
	 * @param    string    $content     L'HTML originario del contenuto.
	 * @return   string                 L'HTML modificato.
	 */
	private function inject_btf( string $content ): string {
		$ad_code = trim( $this->settings['btf']['code'] ?? '' );
		if ( $ad_code === '' ) {
			return $content;
		}
		$min_h_desktop = $this->settings['btf']['min_height_desktop'] ?? 250;
		$min_h_mobile  = $this->settings['btf']['min_height_mobile'] ?? 250;

		$style = sprintf( 'min-height:%dpx; --min-h-mobile:%dpx;', $min_h_desktop, $min_h_mobile );
		if ( ! empty( $this->settings['btf']['override_css'] ) ) {
			$style .= ' ' . $this->settings['btf']['override_css'];
		}

		$wrapper = sprintf(
			'<div class="sai-ad-wrapper sai-btf" style="%s">%s</div>',
			$style,
			$ad_code
		);

		return $content . $wrapper;
	}

	/**
	 * Inietta gli annunci pubblicitari all'interno del testo dell'articolo (In Text).
	 *
	 * Gestisce il conteggio cumulativo delle parole, il bypass dei blockquote,
	 * la scissione dei paragrafi in corrispondenza del tag <br> e l'esclusione di specifici contenitori.
	 *
	 * @since    1.0.0
	 * @param    string $content L'HTML originario del contenuto.
	 * @param    array  $config  La configurazione del posizionamento In Text.
	 * @return   string          L'HTML modificato.
	 */
	private function inject_in_text( string $content, array $config ): string {
		$ad_code = trim( $config['code'] ?? '' );
		if ( $ad_code === '' ) {
			return $content;
		}

		$min_h_desktop = $config['min_height_desktop'] ?? 250;
		$min_h_mobile  = $config['min_height_mobile'] ?? 250;
		$words_interval = $config['words_interval'] ?? 150;
		$max_insertions = $config['max_insertions'] ?? 3;
		
		// Converte la stringa di token esclusi in array
		$excluded_str = $config['excluded_container_tokens'] ?? '';
		$excluded_tokens = [];
		if ( class_exists( 'SmartAdInserter\\SmartAdInserterSettings' ) ) {
			$excluded_tokens = \SmartAdInserter\SmartAdInserterSettings::sanitize_exclusion_tokens( $excluded_str );
		} else {
			// Fallback di compatibilità locale per i test unitari autonomi
			$raw_tokens = explode( ',', $excluded_str );
			foreach ( $raw_tokens as $t ) {
				$t = trim( $t );
				if ( $t !== '' && ( $t[0] === '.' || $t[0] === '#' ) ) {
					$excluded_tokens[] = $t;
				}
			}
		}

		if ( $words_interval < 1 || $max_insertions < 1 ) {
			return $content;
		}

		libxml_use_internal_errors( true );
		$dom = new DOMDocument();
		$dom->loadHTML( '<?xml encoding="UTF-8"><div>' . $content . '</div>', LIBXML_HTML_NODEFDTD );
		libxml_clear_errors();

		$root = $dom->getElementsByTagName( 'div' )->item( 0 );
		if ( ! $root ) {
			return $content;
		}

		// 1. Fase di analisi: trova i punti candidati di inserimento
		$insertions = $this->find_in_text_insertions( $root, $words_interval, $max_insertions, $excluded_tokens, $config );

		if ( empty( $insertions ) ) {
			return $content;
		}

		// 2. Fase di iniezione: inserisce i wrapper pubblicitari nei punti pianificati
		foreach ( $insertions as $item ) {
			// Crea il wrapper per l'annuncio
			$wrapper = $dom->createElement( 'div' );
			$wrapper->setAttribute( 'class', 'sai-ad-wrapper sai-in-text' );
			$wrapper->setAttribute( 'style', sprintf( 'min-height:%dpx; --min-h-mobile:%dpx;', $min_h_desktop, $min_h_mobile ) );
			
			if ( ! empty( $config['override_css'] ) ) {
				$wrapper->setAttribute( 'style', $wrapper->getAttribute( 'style' ) . ' ' . $config['override_css'] );
			}

			// Carica l'ad_code come HTML e importalo nel wrapper
			$temp_dom = new DOMDocument();
			libxml_use_internal_errors( true );
			$temp_dom->loadHTML( '<?xml encoding="UTF-8"><div>' . $ad_code . '</div>', LIBXML_HTML_NODEFDTD );
			libxml_clear_errors();

			$temp_div = $temp_dom->getElementsByTagName( 'div' )->item( 0 );
			if ( $temp_div ) {
				foreach ( $temp_div->childNodes as $child ) {
					$imported = $dom->importNode( $child, true );
					$wrapper->appendChild( $imported );
				}
			}

			// Esegue l'infezione in base al tipo di punto pianificato
			if ( $item['type'] === 'br' ) {
				$this->split_paragraph( $item['p'], $item['br'], $wrapper );
			} else {
				$p_node = $item['p'];
				if ( $p_node->nextSibling ) {
					$p_node->parentNode->insertBefore( $wrapper, $p_node->nextSibling );
				} else {
					$p_node->parentNode->appendChild( $wrapper );
				}
			}
		}

		// Estrae l'HTML finale privo del wrapper root
		$new_content = '';
		foreach ( $root->childNodes as $child ) {
			$new_content .= $dom->saveHTML( $child );
		}

		return ! empty( $new_content ) ? $new_content : $content;
	}

	/**
	 * Trova i punti candidati di inserimento all'interno del DOM.
	 *
	 * @since    1.0.0
	 * @param    \DOMNode $root            Il nodo radice del contenuto.
	 * @param    int      $words_interval  La frequenza/intervallo di parole tra i banner.
	 * @param    int      $max_insertions  Il numero massimo di inserimenti consentiti.
	 * @param    array    $excluded_tokens Lista di token di classi/ID da escludere.
	 * @param    array    $config          La configurazione completa.
	 * @return   array                     L'elenco dei punti di inserimento pianificati.
	 */
	private function find_in_text_insertions( $root, int $words_interval, int $max_insertions, array $excluded_tokens, array $config ): array {
		$insertions = [];
		$accumulated_words = 0;
		$insertions_count = 0;
		$exclude_blockquote = isset( $config['exclude_blockquote'] ) ? (bool) $config['exclude_blockquote'] : true;

		// Raccogliamo prima tutti i paragrafi <p> utili
		$p_elements = [];
		foreach ( $root->childNodes as $child ) {
			if ( $child->nodeType === XML_ELEMENT_NODE ) {
				$tag_name = strtolower( $child->nodeName );
				if ( $tag_name === 'p' && trim( $child->textContent ) !== '' ) {
					$p_elements[] = $child;
				}
			}
		}

		// Se c'è un solo paragrafo e l'opzione di evitare il conflitto BTF è attiva, non inseriamo nulla
		if ( count( $p_elements ) === 1 && ! empty( $config['avoid_btf_single_block'] ) ) {
			return [];
		}

		foreach ( $root->childNodes as $child ) {
			if ( $insertions_count >= $max_insertions ) {
				break;
			}

			if ( $child->nodeType !== XML_ELEMENT_NODE ) {
				if ( $child->nodeType === XML_TEXT_NODE ) {
					$accumulated_words += $this->count_words( $child->textContent );
				}
				continue;
			}

			$tag_name = strtolower( $child->nodeName );

			// 1. Esclusione Blockquote
			if ( $exclude_blockquote && $tag_name === 'blockquote' ) {
				continue;
			}

			// 2. Esclusione Contenitori tramite classi/id/tag
			if ( $this->is_excluded_container( $child, $excluded_tokens ) ) {
				// Conteggiamo comunque le parole ma non discendiamo per inserimenti
				$accumulated_words += $this->count_words_in_node( $child, $exclude_blockquote );
				continue;
			}

			// 3. Gestione Paragrafi <p>
			if ( $tag_name === 'p' ) {
				$p_children = iterator_to_array( $child->childNodes );
				$inserted_in_p = false;

				for ( $i = 0; $i < count( $p_children ); $i++ ) {
					$child_node = $p_children[$i];

					// Conteggio parole nel sotto-nodo
					if ( $child_node->nodeType === XML_TEXT_NODE ) {
						$accumulated_words += $this->count_words( $child_node->textContent );
					} elseif ( $child_node->nodeType === XML_ELEMENT_NODE ) {
						if ( strtolower( $child_node->nodeName ) !== 'br' ) {
							$accumulated_words += $this->count_words_in_node( $child_node, $exclude_blockquote );
						}
					}

					// Verifica superamento della soglia
					if ( $accumulated_words >= $words_interval ) {
						// Cerchiamo il primo tag <br> utile a partire da questa posizione nel paragrafo
						$br_found = null;
						for ( $j = $i; $j < count( $p_children ); $j++ ) {
							if ( $p_children[$j]->nodeType === XML_ELEMENT_NODE && strtolower( $p_children[$j]->nodeName ) === 'br' ) {
								$br_found = $p_children[$j];
								break;
							}
						}

						if ( $br_found ) {
							$insertions[] = [
								'type' => 'br',
								'br'   => $br_found,
								'p'    => $child
							];
							$accumulated_words = 0;
							$insertions_count++;
							$inserted_in_p = true;
							break; // Usciamo dal paragrafo per evitare più di un inserimento per paragrafo
						}
					}
				}

				// Se la soglia è stata superata nel paragrafo ma non abbiamo splittato su un <br>
				if ( ! $inserted_in_p && $accumulated_words >= $words_interval ) {
					$insertions[] = [
						'type' => 'p',
						'p'    => $child
					];
					$accumulated_words = 0;
					$insertions_count++;
				}
			} else {
				// Per gli altri tag non esclusi, contiamo solo le parole
				$accumulated_words += $this->count_words_in_node( $child, $exclude_blockquote );
			}
		}

		// Regola minima di inserimento (Short Content Fallback)
		if ( empty( $insertions ) && ! empty( $p_elements ) ) {
			$is_single_block = ( count( $p_elements ) === 1 );
			$avoid = ! empty( $config['avoid_btf_single_block'] );

			if ( ! ( $is_single_block && $avoid ) ) {
				$insertions[] = [
					'type' => 'p',
					'p'    => $p_elements[0]
				];
			}
		}

		return $insertions;
	}

	/**
	 * Scinde un elemento paragrafo in due in corrispondenza di un tag <br>.
	 *
	 * Preserva tutti gli attributi del paragrafo originale (es. classi CSS, stili).
	 *
	 * @since    1.0.0
	 * @param    \DOMElement $p  Il paragrafo da scindere.
	 * @param    \DOMNode    $br Il nodo <br> di scissione.
	 * @param    \DOMNode    $ad Il nodo dell'annuncio.
	 */
	private function split_paragraph( $p, $br, $ad ): void {
		$parent = $p->parentNode;
		if ( ! $parent ) {
			return;
		}

		// Crea il nuovo paragrafo
		$new_p = $p->ownerDocument->createElement( 'p' );

		// Copia tutti gli attributi
		if ( $p->hasAttributes() ) {
			foreach ( $p->attributes as $attr ) {
				$new_p->setAttribute( $attr->nodeName, $attr->nodeValue );
			}
		}

		// Sposta tutti i nodi successivi al <br> nel nuovo paragrafo
		$next = $br->nextSibling;
		while ( $next ) {
			$temp = $next->nextSibling;
			$new_p->appendChild( $next );
			$next = $temp;
		}

		// Rimuove il <br> dal paragrafo originario
		$p->removeChild( $br );

		// Inserisce l'annuncio dopo il paragrafo originario
		if ( $p->nextSibling ) {
			$parent->insertBefore( $ad, $p->nextSibling );
		} else {
			$parent->appendChild( $ad );
		}

		// Inserisce il nuovo paragrafo dopo l'annuncio (se contiene nodi)
		if ( $new_p->hasChildNodes() ) {
			if ( $ad->nextSibling ) {
				$parent->insertBefore( $new_p, $ad->nextSibling );
			} else {
				$parent->appendChild( $new_p );
			}
		}
	}

	/**
	 * Verifica se un elemento DOM corrisponde a un contenitore da escludere.
	 *
	 * @since    1.0.0
	 * @param    \DOMElement $element         L'elemento da controllare.
	 * @param    array       $excluded_tokens I token esclusi (.classe o #id).
	 * @return   bool
	 */
	private function is_excluded_container( $element, array $excluded_tokens ): bool {
		if ( empty( $excluded_tokens ) ) {
			return false;
		}

		$id    = $element->getAttribute( 'id' );
		$class = $element->getAttribute( 'class' );
		$tag   = strtolower( $element->nodeName );

		$classes = ! empty( $class ) ? preg_split( '/\s+/', $class ) : [];

		foreach ( $excluded_tokens as $token ) {
			if ( str_starts_with( $token, '#' ) ) {
				$token_id = substr( $token, 1 );
				if ( $id === $token_id ) {
					return true;
				}
			} elseif ( str_starts_with( $token, '.' ) ) {
				$token_class = substr( $token, 1 );
				if ( in_array( $token_class, $classes, true ) ) {
					return true;
				}
			} else {
				// Confronto diretto per tag semplici (es. blockquote, aside, nav)
				if ( $tag === strtolower( $token ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Conta le parole in modo ricorsivo all'interno di un nodo DOM, escludendo blockquote condizionalmente.
	 *
	 * @since    1.0.0
	 * @param    \DOMNode $node               Il nodo DOM.
	 * @param    bool     $exclude_blockquote Se impostato a true, esclude i blockquote dal conteggio.
	 * @return   int
	 */
	private function count_words_in_node( $node, bool $exclude_blockquote = true ): int {
		$words = 0;
		if ( $node->nodeType === XML_TEXT_NODE ) {
			$words += $this->count_words( $node->textContent );
		} elseif ( $node->nodeType === XML_ELEMENT_NODE ) {
			if ( $exclude_blockquote && strtolower( $node->nodeName ) === 'blockquote' ) {
				return 0;
			}
			foreach ( $node->childNodes as $child ) {
				$words += $this->count_words_in_node( $child, $exclude_blockquote );
			}
		}
		return $words;
	}

	/**
	 * Conta le parole in una stringa di testo.
	 *
	 * @since    1.0.0
	 * @param    string $text Il testo.
	 * @return   int
	 */
	private function count_words( string $text ): int {
		$text = trim( $text );
		if ( $text === '' ) {
			return 0;
		}
		$words = preg_split( '/\s+/', $text );
		return is_array( $words ) ? count( $words ) : 0;
	}
}
