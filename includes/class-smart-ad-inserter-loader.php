<?php
namespace SmartAdInserter;

/**
 * Registra tutte le azioni (Actions) ed i filtri (Filters) di WordPress.
 *
 * Mantiene un elenco centralizzato di tutti gli hook registrati all'interno del plugin
 * e li esegue tramite le API native del core di WordPress al caricamento del plugin.
 *
 * @since      1.0.0
 * @package    Smart_Ad_Inserter
 * @subpackage Smart_Ad_Inserter/includes
 * @author     Carmine Muollo
 */
class SmartAdInserterLoader {

	/**
	 * La collezione di azioni registrate con WordPress.
	 *
	 * @since    1.0.0
	 * @var      array    $actions    Le azioni da registrare con WordPress.
	 */
	protected $actions;

	/**
	 * La collezione di filtri registrati con WordPress.
	 *
	 * @since    1.0.0
	 * @var      array    $filters    I filtri da registrare con WordPress.
	 */
	protected $filters;

	/**
	 * Inizializza le collezioni utilizzate per salvare i filtri e le azioni.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->actions = [];
		$this->filters = [];
	}

	/**
	 * Aggiunge una nuova azione alla collezione da registrare.
	 *
	 * @since    1.0.0
	 * @param    string               $hook             Il nome dell'azione di WordPress.
	 * @param    object               $component        Riferimento all'istanza dell'oggetto in cui è definito il callback.
	 * @param    string               $callback         Il nome della funzione di callback.
	 * @param    int                  $priority         Opzionale. La priorità di esecuzione (default 10).
	 * @param    int                  $accepted_args    Opzionale. Il numero di argomenti passati al callback (default 1).
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Aggiunge un nuovo filtro alla collezione da registrare.
	 *
	 * @since    1.0.0
	 * @param    string               $hook             Il nome del filtro di WordPress.
	 * @param    object               $component        Riferimento all'istanza dell'oggetto in cui è definito il callback.
	 * @param    string               $callback         Il nome della funzione di callback.
	 * @param    int                  $priority         Opzionale. La priorità di esecuzione (default 10).
	 * @param    int                  $accepted_args    Opzionale. Il numero di argomenti passati al callback (default 1).
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Funzione di utilità per inserire un hook nell'array interno.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    array                $hooks            La collezione di hook (azioni o filtri).
	 * @param    string               $hook             Il nome dell'hook di WordPress.
	 * @param    object               $component        Riferimento all'istanza dell'oggetto.
	 * @param    string               $callback         Il nome della funzione di callback.
	 * @param    int                  $priority         Il valore di priorità.
	 * @param    int                  $accepted_args    Il numero di argomenti accettati.
	 * @return   array                                  La collezione aggiornata degli hook.
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {
		$hooks[] = [
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		];

		return $hooks;
	}

	/**
	 * Registra effettivamente i filtri e le azioni memorizzati all'interno delle API di WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], [ $hook['component'], $hook['callback'] ], $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], [ $hook['component'], $hook['callback'] ], $hook['priority'], $hook['accepted_args'] );
		}
	}
}
