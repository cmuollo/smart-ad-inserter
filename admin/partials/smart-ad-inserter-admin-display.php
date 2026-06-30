<?php
/**
 * Fornisce il codice di markup HTML e PHP per il pannello amministrativo di Smart Ad Inserter.
 *
 * @link              https://github.com/cmuollo/smart-ad-inserter
 * @since             1.0.0
 * @package           Smart_Ad_Inserter
 * @subpackage        Smart_Ad_Inserter/admin/partials
 * @author            Carmine Muollo
 */
?>

<div class="sai-wrap">
	<h1 class="sai-title">Smart Ad Inserter — Dashboard di Controllo</h1>
	<p class="sai-subtitle">Gestione centralizzata e iniezione pubblicitaria server-side ad alte prestazioni per azzerare il CLS.</p>

	<form id="sai-settings-form" method="post" action="">
		<!-- Barra di Navigazione Tab -->
		<div class="sai-tabs">
			<button type="button" class="sai-tab active" data-tab="sai-tab-global">Globale</button>
			<button type="button" class="sai-tab" data-tab="sai-tab-home">Home</button>
			<button type="button" class="sai-tab" data-tab="sai-tab-article">Articolo Singolo</button>
			<button type="button" class="sai-tab" data-tab="sai-tab-archive">Categorie/Archivi</button>
		</div>

		<!-- Contenuto delle Schede -->
		<div class="sai-tabs-content-wrapper">
			<!-- TAB 1: GLOBALE -->
			<div id="sai-tab-global" class="sai-tab-content active">
				<div class="sai-card">
					<h2 class="sai-card-title">Codice Globale nell'&lt;head&gt;</h2>
					<p class="sai-card-desc">Inserisci qui i tag di tracciamento e le librerie pubblicitarie (GPT, Prebid.js, script SSP) da caricare nell'intestazione di ogni pagina.</p>
					<div class="sai-field-group">
						<label for="sai-head-scripts">Codice Script Header</label>
						<textarea id="sai-head-scripts" name="global_scripts" rows="6" class="sai-textarea" placeholder="<script src='https://...'></script>"></textarea>
					</div>
				</div>

				<!-- GLOBALE MASTHEAD -->
				<div class="sai-card">
					<div class="sai-card-header-row">
						<h2 class="sai-card-title">Masthead</h2>
						<span class="sai-tag">Strutturale</span>
					</div>
					<p class="sai-card-desc">Configura il primo banner pubblicitario visibile sotto l'header globale del sito.</p>
					
					<div class="sai-field-row sai-checkbox-row">
						<input type="checkbox" id="sai-global-masthead-use-default-placement" name="contexts[global][positions][masthead][use_default_placement]" value="1" checked />
						<label for="sai-global-masthead-use-default-placement"><strong>Usa posizionamento di default (- dopo &lt;header&gt;)</strong></label>
					</div>

					<div class="sai-field-row">
						<div class="sai-field-col">
							<label for="sai-global-masthead-active">Abilita Posizione</label>
							<input type="checkbox" id="sai-global-masthead-active" name="contexts[global][positions][masthead][active]" value="1" />
						</div>
						<div class="sai-field-col">
							<label for="sai-global-masthead-desktop-height">Altezza Minima Desktop (px)</label>
							<input type="number" id="sai-global-masthead-desktop-height" name="contexts[global][positions][masthead][min_height_desktop]" class="sai-input-number" min="0" placeholder="250" />
						</div>
						<div class="sai-field-col">
							<label for="sai-global-masthead-mobile-height">Altezza Minima Mobile (px)</label>
							<input type="number" id="sai-global-masthead-mobile-height" name="contexts[global][positions][masthead][min_height_mobile]" class="sai-input-number" min="0" placeholder="100" />
						</div>
					</div>

					<div class="sai-field-group">
						<label for="sai-global-masthead-code">Codice Banner Masthead</label>
						<textarea id="sai-global-masthead-code" name="contexts[global][positions][masthead][code]" rows="4" class="sai-textarea" placeholder="Codice HTML/JS per l'annuncio..."></textarea>
					</div>
					<div class="sai-field-group">
						<label for="sai-global-masthead-override-css">Override CSS Wrapper (CSS personalizzato del contenitore banner)</label>
						<textarea id="sai-global-masthead-override-css" name="contexts[global][positions][masthead][override_css]" rows="3" class="sai-textarea" placeholder="Es. margin: 20px 0; text-align: center;"></textarea>
						<p class="sai-field-desc">Queste dichiarazioni CSS verranno applicate direttamente come stile inline sul <strong>&lt;div&gt; wrapper esterno del plugin (.sai-ad-wrapper)</strong>, non sul markup interno del banner.</p>
					</div>

					<div class="sai-field-group">
						<label for="sai-global-masthead-css-selector">Selettore CSS custom (opzionale)</label>
						<input type="text" id="sai-global-masthead-css-selector" name="contexts[global][positions][masthead][custom_selector]" class="sai-input-text" placeholder="Es. #custom-header-wrapper" />
						<p class="sai-field-desc" id="sai-global-masthead-selector-desc">Il selettore custom sarà usato solo se disattivi il posizionamento di default.</p>
					</div>
				</div>

				<!-- GLOBALE FOOTER -->
				<div class="sai-card">
					<div class="sai-card-header-row">
						<h2 class="sai-card-title">Footer</h2>
						<span class="sai-tag">Strutturale</span>
					</div>
					<p class="sai-card-desc">Configura il banner pubblicitario da visualizzare sopra o sotto il footer globale del sito.</p>
					
					<div class="sai-field-row sai-checkbox-row">
						<input type="checkbox" id="sai-global-footer-use-default-placement" name="contexts[global][positions][footer][use_default_placement]" value="1" checked />
						<label for="sai-global-footer-use-default-placement"><strong>Usa posizionamento di default (- relativo al tag &lt;footer&gt;)</strong></label>
					</div>

					<div class="sai-field-group">
						<label>Posizione Relativa Footer</label>
						<div class="sai-footer-options-grid">
							<label class="sai-footer-option-box">
								<input type="radio" id="sai-global-footer-position-before" name="contexts[global][positions][footer][footer_position]" value="before_footer" checked />
								<div class="sai-option-desc">
									<strong>Prima del footer (Default)</strong>
									<span>Inietta subito prima del tag &lt;footer&gt;</span>
								</div>
							</label>
							<label class="sai-footer-option-box">
								<input type="radio" id="sai-global-footer-position-after" name="contexts[global][positions][footer][footer_position]" value="after_footer" />
								<div class="sai-option-desc">
									<strong>Dopo il footer</strong>
									<span>Inietta subito dopo il tag &lt;footer&gt; (ultimo figlio del &lt;body&gt;)</span>
								</div>
							</label>
						</div>
					</div>

					<div class="sai-field-row">
						<div class="sai-field-col">
							<label for="sai-global-footer-active">Abilita Posizione</label>
							<input type="checkbox" id="sai-global-footer-active" name="contexts[global][positions][footer][active]" value="1" />
						</div>
						<div class="sai-field-col">
							<label for="sai-global-footer-desktop-height">Altezza Minima Desktop (px)</label>
							<input type="number" id="sai-global-footer-desktop-height" name="contexts[global][positions][footer][min_height_desktop]" class="sai-input-number" min="0" placeholder="250" />
						</div>
						<div class="sai-field-col">
							<label for="sai-global-footer-mobile-height">Altezza Minima Mobile (px)</label>
							<input type="number" id="sai-global-footer-mobile-height" name="contexts[global][positions][footer][min_height_mobile]" class="sai-input-number" min="0" placeholder="100" />
						</div>
					</div>

					<div class="sai-field-group">
						<label for="sai-global-footer-code">Codice Banner Footer</label>
						<textarea id="sai-global-footer-code" name="contexts[global][positions][footer][code]" rows="4" class="sai-textarea" placeholder="Codice HTML/JS per l'annuncio..."></textarea>
					</div>
					<div class="sai-field-group">
						<label for="sai-global-footer-override-css">Override CSS Wrapper (CSS personalizzato del contenitore banner)</label>
						<textarea id="sai-global-footer-override-css" name="contexts[global][positions][footer][override_css]" rows="3" class="sai-textarea" placeholder="Es. margin: 20px 0; text-align: center;"></textarea>
						<p class="sai-field-desc">Queste dichiarazioni CSS verranno applicate direttamente come stile inline sul <strong>&lt;div&gt; wrapper esterno del plugin (.sai-ad-wrapper)</strong>, non sul markup interno del banner.</p>
					</div>

					<div class="sai-field-group">
						<label for="sai-global-footer-css-selector">Selettore CSS custom (opzionale)</label>
						<input type="text" id="sai-global-footer-css-selector" name="contexts[global][positions][footer][custom_selector]" class="sai-input-text" placeholder="Es. #custom-footer-wrapper" />
						<p class="sai-field-desc" id="sai-global-footer-selector-desc">Il selettore custom sarà usato solo se disattivi il posizionamento di default.</p>
					</div>
				</div>

				<!-- GLOBALE SIDEBAR TOP -->
				<div class="sai-card">
					<div class="sai-card-header-row">
						<h2 class="sai-card-title">Top Sidebar</h2>
						<span class="sai-tag">Strutturale</span>
					</div>
					<p class="sai-card-desc">Il primo banner nella colonna laterale destra, visibile in alto su desktop.</p>
					
					<div class="sai-field-row">
						<div class="sai-field-col">
							<label for="sai-global-sidebar-top-active">Abilita Posizione</label>
							<input type="checkbox" id="sai-global-sidebar-top-active" name="contexts[global][positions][sidebar_top][active]" value="1" />
						</div>
						<div class="sai-field-col">
							<label for="sai-global-sidebar-top-desktop-height">Altezza Minima Desktop (px)</label>
							<input type="number" id="sai-global-sidebar-top-desktop-height" name="contexts[global][positions][sidebar_top][min_height_desktop]" class="sai-input-number" min="0" />
						</div>
						<div class="sai-field-col">
							<label for="sai-global-sidebar-top-mobile-height">Altezza Minima Mobile (px)</label>
							<input type="number" id="sai-global-sidebar-top-mobile-height" name="contexts[global][positions][sidebar_top][min_height_mobile]" class="sai-input-number" min="0" />
						</div>
					</div>

					<div class="sai-field-group">
						<label for="sai-global-sidebar-top-code">Codice Banner</label>
						<textarea id="sai-global-sidebar-top-code" name="contexts[global][positions][sidebar_top][code]" rows="3" class="sai-textarea"></textarea>
					</div>

					<div class="sai-field-group">
						<label for="sai-global-sidebar-top-override-css">Override CSS Wrapper (CSS personalizzato del contenitore banner)</label>
						<textarea id="sai-global-sidebar-top-override-css" name="contexts[global][positions][sidebar_top][override_css]" rows="2" class="sai-textarea"></textarea>
						<p class="sai-field-desc">Queste dichiarazioni CSS verranno applicate come stile inline sul wrapper esterno del plugin (.sai-ad-wrapper).</p>
					</div>

					<div class="sai-field-group">
						<label for="sai-global-sidebar-top-css-selector">Selettore CSS custom (opzionale)</label>
						<input type="text" id="sai-global-sidebar-top-css-selector" name="contexts[global][positions][sidebar_top][custom_selector]" class="sai-input-text" />
					</div>
				</div>

				<!-- GLOBALE SIDEBAR STICKY -->
				<div class="sai-card">
					<div class="sai-card-header-row">
						<h2 class="sai-card-title">Sidebar Sticky</h2>
						<span class="sai-tag">Strutturale</span>
					</div>
					<p class="sai-card-desc">Un banner verticale (es. 300x600) posizionato in fondo alla sidebar che segue lo scroll dell'utente.</p>
					
					<div class="sai-field-row">
						<div class="sai-field-col">
							<label for="sai-global-sidebar-sticky-active">Abilita Posizione</label>
							<input type="checkbox" id="sai-global-sidebar-sticky-active" name="contexts[global][positions][sidebar_sticky][active]" value="1" />
						</div>
						<div class="sai-field-col">
							<label for="sai-global-sidebar-sticky-desktop-height">Altezza Minima Desktop (px)</label>
							<input type="number" id="sai-global-sidebar-sticky-desktop-height" name="contexts[global][positions][sidebar_sticky][min_height_desktop]" class="sai-input-number" min="0" />
						</div>
						<div class="sai-field-col">
							<label for="sai-global-sidebar-sticky-mobile-height">Altezza Minima Mobile (px)</label>
							<input type="number" id="sai-global-sidebar-sticky-mobile-height" name="contexts[global][positions][sidebar_sticky][min_height_mobile]" class="sai-input-number" min="0" />
						</div>
					</div>

					<div class="sai-field-group">
						<label for="sai-global-sidebar-sticky-code">Codice Banner</label>
						<textarea id="sai-global-sidebar-sticky-code" name="contexts[global][positions][sidebar_sticky][code]" rows="3" class="sai-textarea"></textarea>
					</div>

					<div class="sai-field-group">
						<label for="sai-global-sidebar-sticky-override-css">Override CSS Wrapper (CSS personalizzato del contenitore banner)</label>
						<textarea id="sai-global-sidebar-sticky-override-css" name="contexts[global][positions][sidebar_sticky][override_css]" rows="2" class="sai-textarea"></textarea>
						<p class="sai-field-desc">Queste dichiarazioni CSS verranno applicate come stile inline sul wrapper esterno del plugin (.sai-ad-wrapper).</p>
					</div>

					<div class="sai-field-group">
						<label for="sai-global-sidebar-sticky-css-selector">Selettore CSS custom (opzionale)</label>
						<input type="text" id="sai-global-sidebar-sticky-css-selector" name="contexts[global][positions][sidebar_sticky][custom_selector]" class="sai-input-text" />
					</div>
				</div>
			</div>

			<!-- TAB 2: HOME -->
			<div id="sai-tab-home" class="sai-tab-content">
				<!-- HOME MASTHEAD -->
				<div class="sai-card">
					<div class="sai-card-header-row">
						<h2 class="sai-card-title">Masthead</h2>
						<span class="sai-tag">Strutturale</span>
					</div>
					<p class="sai-card-desc">Configura la posizione Masthead per la Home Page.</p>
					
					<div class="sai-field-row sai-checkbox-row">
						<input type="checkbox" id="sai-home-masthead-use-global-config" name="contexts[home][positions][masthead][use_global_config]" value="1" class="sai-use-global-toggle" data-target="sai-home-masthead-override-container" checked />
						<label for="sai-home-masthead-use-global-config"><strong>Usa configurazione globale</strong></label>
					</div>

					<div id="sai-home-masthead-override-container" class="sai-override-container sai-hidden">
						<div class="sai-field-row sai-checkbox-row">
							<input type="checkbox" id="sai-home-masthead-use-default-placement" name="contexts[home][positions][masthead][use_default_placement]" value="1" checked />
							<label for="sai-home-masthead-use-default-placement"><strong>Usa posizionamento di default (- dopo &lt;header&gt;)</strong></label>
						</div>

						<div class="sai-field-row">
							<div class="sai-field-col">
								<label for="sai-home-masthead-active">Abilita Posizione</label>
								<input type="checkbox" id="sai-home-masthead-active" name="contexts[home][positions][masthead][active]" value="1" />
							</div>
							<div class="sai-field-col">
								<label for="sai-home-masthead-desktop-height">Altezza Minima Desktop (px)</label>
								<input type="number" id="sai-home-masthead-desktop-height" name="contexts[home][positions][masthead][min_height_desktop]" class="sai-input-number" min="0" placeholder="250" />
							</div>
							<div class="sai-field-col">
								<label for="sai-home-masthead-mobile-height">Altezza Minima Mobile (px)</label>
								<input type="number" id="sai-home-masthead-mobile-height" name="contexts[home][positions][masthead][min_height_mobile]" class="sai-input-number" min="0" placeholder="100" />
							</div>
						</div>

						<div class="sai-field-group">
							<label for="sai-home-masthead-code">Codice Banner Masthead</label>
							<textarea id="sai-home-masthead-code" name="contexts[home][positions][masthead][code]" rows="4" class="sai-textarea" placeholder="Codice ad specifico per la Home..."></textarea>
						</div>
						<div class="sai-field-group">
							<label for="sai-home-masthead-override-css">Override CSS Wrapper</label>
							<textarea id="sai-home-masthead-override-css" name="contexts[home][positions][masthead][override_css]" rows="3" class="sai-textarea"></textarea>
						</div>
						<div class="sai-field-group">
							<label for="sai-home-masthead-css-selector">Selettore CSS custom (opzionale)</label>
							<input type="text" id="sai-home-masthead-css-selector" name="contexts[home][positions][masthead][custom_selector]" class="sai-input-text" />
						</div>
					</div>
				</div>

				<!-- HOME FOOTER -->
				<div class="sai-card">
					<div class="sai-card-header-row">
						<h2 class="sai-card-title">Footer</h2>
						<span class="sai-tag">Strutturale</span>
					</div>
					<p class="sai-card-desc">Configura la posizione Footer per la Home Page.</p>
					
					<div class="sai-field-row sai-checkbox-row">
						<input type="checkbox" id="sai-home-footer-use-global-config" name="contexts[home][positions][footer][use_global_config]" value="1" class="sai-use-global-toggle" data-target="sai-home-footer-override-container" checked />
						<label for="sai-home-footer-use-global-config"><strong>Usa configurazione globale</strong></label>
					</div>

					<div id="sai-home-footer-override-container" class="sai-override-container sai-hidden">
						<div class="sai-field-row sai-checkbox-row">
							<input type="checkbox" id="sai-home-footer-use-default-placement" name="contexts[home][positions][footer][use_default_placement]" value="1" checked />
							<label for="sai-home-footer-use-default-placement"><strong>Usa posizionamento di default (- relativo al tag &lt;footer&gt;)</strong></label>
						</div>

						<div class="sai-field-group">
							<label>Posizione Relativa Footer</label>
							<div class="sai-footer-options-grid">
								<label class="sai-footer-option-box">
									<input type="radio" id="sai-home-footer-position-before" name="contexts[home][positions][footer][footer_position]" value="before_footer" checked />
									<div class="sai-option-desc">
										<strong>Prima del footer (Default)</strong>
										<span>Inietta subito prima del tag &lt;footer&gt;</span>
									</div>
								</label>
								<label class="sai-footer-option-box">
									<input type="radio" id="sai-home-footer-position-after" name="contexts[home][positions][footer][footer_position]" value="after_footer" />
									<div class="sai-option-desc">
										<strong>Dopo il footer</strong>
										<span>Inietta subito dopo il tag &lt;footer&gt; (fine del &lt;body&gt;)</span>
									</div>
								</label>
							</div>
						</div>

						<div class="sai-field-row">
							<div class="sai-field-col">
								<label for="sai-home-footer-active">Abilita Posizione</label>
								<input type="checkbox" id="sai-home-footer-active" name="contexts[home][positions][footer][active]" value="1" />
							</div>
							<div class="sai-field-col">
								<label for="sai-home-footer-desktop-height">Altezza Minima Desktop (px)</label>
								<input type="number" id="sai-home-footer-desktop-height" name="contexts[home][positions][footer][min_height_desktop]" class="sai-input-number" min="0" placeholder="250" />
							</div>
							<div class="sai-field-col">
								<label for="sai-home-footer-mobile-height">Altezza Minima Mobile (px)</label>
								<input type="number" id="sai-home-footer-mobile-height" name="contexts[home][positions][footer][min_height_mobile]" class="sai-input-number" min="0" placeholder="100" />
							</div>
						</div>

						<div class="sai-field-group">
							<label for="sai-home-footer-code">Codice Banner Footer</label>
							<textarea id="sai-home-footer-code" name="contexts[home][positions][footer][code]" rows="4" class="sai-textarea" placeholder="Codice ad specifico per il Footer della Home..."></textarea>
						</div>
						<div class="sai-field-group">
							<label for="sai-home-footer-override-css">Override CSS Wrapper</label>
							<textarea id="sai-home-footer-override-css" name="contexts[home][positions][footer][override_css]" rows="3" class="sai-textarea"></textarea>
						</div>
						<div class="sai-field-group">
							<label for="sai-home-footer-css-selector">Selettore CSS custom (opzionale)</label>
							<input type="text" id="sai-home-footer-css-selector" name="contexts[home][positions][footer][custom_selector]" class="sai-input-text" />
						</div>
					</div>
				</div>

				<!-- HOME SIDEBAR TOP -->
				<div class="sai-card">
					<div class="sai-card-header-row">
						<h2 class="sai-card-title">Top Sidebar</h2>
						<span class="sai-tag">Strutturale</span>
					</div>
					<p class="sai-card-desc">Il primo banner nella colonna laterale destra per la Home Page.</p>
					
					<div class="sai-field-row sai-checkbox-row">
						<input type="checkbox" id="sai-home-sidebar-top-use-global-config" name="contexts[home][positions][sidebar_top][use_global_config]" value="1" class="sai-use-global-toggle" data-target="sai-home-sidebar-top-override-container" checked />
						<label for="sai-home-sidebar-top-use-global-config"><strong>Usa configurazione globale</strong></label>
					</div>

					<div id="sai-home-sidebar-top-override-container" class="sai-override-container sai-hidden">
						<div class="sai-field-row">
							<div class="sai-field-col">
								<label for="sai-home-sidebar-top-active">Abilita Posizione</label>
								<input type="checkbox" id="sai-home-sidebar-top-active" name="contexts[home][positions][sidebar_top][active]" value="1" />
							</div>
							<div class="sai-field-col">
								<label for="sai-home-sidebar-top-desktop-height">Altezza Minima Desktop (px)</label>
								<input type="number" id="sai-home-sidebar-top-desktop-height" name="contexts[home][positions][sidebar_top][min_height_desktop]" class="sai-input-number" min="0" placeholder="250" />
							</div>
							<div class="sai-field-col">
								<label for="sai-home-sidebar-top-mobile-height">Altezza Minima Mobile (px)</label>
								<input type="number" id="sai-home-sidebar-top-mobile-height" name="contexts[home][positions][sidebar_top][min_height_mobile]" class="sai-input-number" min="0" placeholder="0" />
							</div>
						</div>

						<div class="sai-field-group">
							<label for="sai-home-sidebar-top-code">Codice Banner</label>
							<textarea id="sai-home-sidebar-top-code" name="contexts[home][positions][sidebar_top][code]" rows="3" class="sai-textarea" placeholder="Codice ad specifico per Sidebar Top..."></textarea>
						</div>

						<div class="sai-field-group">
							<label for="sai-home-sidebar-top-override-css">Override CSS Wrapper</label>
							<textarea id="sai-home-sidebar-top-override-css" name="contexts[home][positions][sidebar_top][override_css]" rows="2" class="sai-textarea"></textarea>
						</div>

						<div class="sai-field-group">
							<label for="sai-home-sidebar-top-css-selector">Selettore CSS custom (opzionale)</label>
							<input type="text" id="sai-home-sidebar-top-css-selector" name="contexts[home][positions][sidebar_top][custom_selector]" class="sai-input-text" />
						</div>
					</div>
				</div>

				<!-- HOME SIDEBAR STICKY -->
				<div class="sai-card">
					<div class="sai-card-header-row">
						<h2 class="sai-card-title">Sidebar Sticky</h2>
						<span class="sai-tag">Strutturale</span>
					</div>
					<p class="sai-card-desc">Il banner verticale sticky nella colonna laterale per la Home Page.</p>
					
					<div class="sai-field-row sai-checkbox-row">
						<input type="checkbox" id="sai-home-sidebar-sticky-use-global-config" name="contexts[home][positions][sidebar_sticky][use_global_config]" value="1" class="sai-use-global-toggle" data-target="sai-home-sidebar-sticky-override-container" checked />
						<label for="sai-home-sidebar-sticky-use-global-config"><strong>Usa configurazione globale</strong></label>
					</div>

					<div id="sai-home-sidebar-sticky-override-container" class="sai-override-container sai-hidden">
						<div class="sai-field-row">
							<div class="sai-field-col">
								<label for="sai-home-sidebar-sticky-active">Abilita Posizione</label>
								<input type="checkbox" id="sai-home-sidebar-sticky-active" name="contexts[home][positions][sidebar_sticky][active]" value="1" />
							</div>
							<div class="sai-field-col">
								<label for="sai-home-sidebar-sticky-desktop-height">Altezza Minima Desktop (px)</label>
								<input type="number" id="sai-home-sidebar-sticky-desktop-height" name="contexts[home][positions][sidebar_sticky][min_height_desktop]" class="sai-input-number" min="0" placeholder="600" />
							</div>
							<div class="sai-field-col">
								<label for="sai-home-sidebar-sticky-mobile-height">Altezza Minima Mobile (px)</label>
								<input type="number" id="sai-home-sidebar-sticky-mobile-height" name="contexts[home][positions][sidebar_sticky][min_height_mobile]" class="sai-input-number" min="0" placeholder="0" />
							</div>
						</div>

						<div class="sai-field-group">
							<label for="sai-home-sidebar-sticky-code">Codice Banner</label>
							<textarea id="sai-home-sidebar-sticky-code" name="contexts[home][positions][sidebar_sticky][code]" rows="3" class="sai-textarea" placeholder="Codice ad specifico per Sidebar Sticky..."></textarea>
						</div>

						<div class="sai-field-group">
							<label for="sai-home-sidebar-sticky-override-css">Override CSS Wrapper</label>
							<textarea id="sai-home-sidebar-sticky-override-css" name="contexts[home][positions][sidebar_sticky][override_css]" rows="2" class="sai-textarea"></textarea>
						</div>

						<div class="sai-field-group">
							<label for="sai-home-sidebar-sticky-css-selector">Selettore CSS custom (opzionale)</label>
							<input type="text" id="sai-home-sidebar-sticky-css-selector" name="contexts[home][positions][sidebar_sticky][custom_selector]" class="sai-input-text" />
						</div>
					</div>
				</div>

				<!-- GRID HOME -->
				<div class="sai-card">
					<div class="sai-card-header-row">
						<h2 class="sai-card-title">Box in Griglia (Home Page)</h2>
						<span class="sai-tag">Griglia</span>
					</div>
					<p class="sai-card-desc">Inserimento automatico di annunci pubblicitari all'interno della griglia degli articoli in Home Page.</p>
					
					<div class="sai-field-row">
						<div class="sai-field-col">
							<label for="sai-home-grid-home-active">Abilita Posizione</label>
							<input type="checkbox" id="sai-home-grid-home-active" name="contexts[home][positions][grid_home][active]" value="1" />
						</div>
						<div class="sai-field-col">
							<label for="sai-home-grid-home-target-element">Target Element Selector (CSS)</label>
							<input type="text" id="sai-home-grid-home-target-element" name="contexts[home][positions][grid_home][target_element]" class="sai-input-text" placeholder="Es. .post-card, article" />
						</div>
						<div class="sai-field-col">
							<label for="sai-home-grid-home-frequency">Frequenza / Posizione (N° elemento)</label>
							<input type="number" id="sai-home-grid-home-frequency" name="contexts[home][positions][grid_home][frequency]" class="sai-input-number" min="1" placeholder="3" />
						</div>
					</div>

					<div class="sai-field-row">
						<div class="sai-field-col">
							<label for="sai-home-grid-home-desktop-height">Altezza Minima Desktop (px)</label>
							<input type="number" id="sai-home-grid-home-desktop-height" name="contexts[home][positions][grid_home][min_height_desktop]" class="sai-input-number" min="0" />
						</div>
						<div class="sai-field-col">
							<label for="sai-home-grid-home-mobile-height">Altezza Minima Mobile (px)</label>
							<input type="number" id="sai-home-grid-home-mobile-height" name="contexts[home][positions][grid_home][min_height_mobile]" class="sai-input-number" min="0" />
						</div>
					</div>

					<div class="sai-field-group">
						<label for="sai-home-grid-home-code">Codice Banner</label>
						<textarea id="sai-home-grid-home-code" name="contexts[home][positions][grid_home][code]" rows="4" class="sai-textarea"></textarea>
					</div>

					<div class="sai-field-group">
						<label for="sai-home-grid-home-override-css">Override CSS Wrapper (CSS personalizzato del contenitore banner)</label>
						<textarea id="sai-home-grid-home-override-css" name="contexts[home][positions][grid_home][override_css]" rows="2" class="sai-textarea"></textarea>
					</div>

					<div class="sai-field-group">
						<label for="sai-home-grid-home-css-selector">Selettore CSS custom (opzionale)</label>
						<input type="text" id="sai-home-grid-home-css-selector" name="contexts[home][positions][grid_home][custom_selector]" class="sai-input-text" />
					</div>
				</div>
			</div>

			<!-- TAB 3: ARTICOLO SINGOLO -->
			<div id="sai-tab-article" class="sai-tab-content">
				<!-- SINGLE MASTHEAD -->
				<div class="sai-card">
					<div class="sai-card-header-row">
						<h2 class="sai-card-title">Masthead</h2>
						<span class="sai-tag">Strutturale</span>
					</div>
					<p class="sai-card-desc">Configura la posizione Masthead per gli Articoli Singoli.</p>
					
					<div class="sai-field-row sai-checkbox-row">
						<input type="checkbox" id="sai-single-masthead-use-global-config" name="contexts[single][positions][masthead][use_global_config]" value="1" class="sai-use-global-toggle" data-target="sai-single-masthead-override-container" checked />
						<label for="sai-single-masthead-use-global-config"><strong>Usa configurazione globale</strong></label>
					</div>

					<div id="sai-single-masthead-override-container" class="sai-override-container sai-hidden">
						<div class="sai-field-row sai-checkbox-row">
							<input type="checkbox" id="sai-single-masthead-use-default-placement" name="contexts[single][positions][masthead][use_default_placement]" value="1" checked />
							<label for="sai-single-masthead-use-default-placement"><strong>Usa posizionamento di default (- dopo &lt;header&gt;)</strong></label>
						</div>

						<div class="sai-field-row">
							<div class="sai-field-col">
								<label for="sai-single-masthead-active">Abilita Posizione</label>
								<input type="checkbox" id="sai-single-masthead-active" name="contexts[single][positions][masthead][active]" value="1" />
							</div>
							<div class="sai-field-col">
								<label for="sai-single-masthead-desktop-height">Altezza Minima Desktop (px)</label>
								<input type="number" id="sai-single-masthead-desktop-height" name="contexts[single][positions][masthead][min_height_desktop]" class="sai-input-number" min="0" placeholder="250" />
							</div>
							<div class="sai-field-col">
								<label for="sai-single-masthead-mobile-height">Altezza Minima Mobile (px)</label>
								<input type="number" id="sai-single-masthead-mobile-height" name="contexts[single][positions][masthead][min_height_mobile]" class="sai-input-number" min="0" placeholder="100" />
							</div>
						</div>

						<div class="sai-field-group">
							<label for="sai-single-masthead-code">Codice Banner Masthead</label>
							<textarea id="sai-single-masthead-code" name="contexts[single][positions][masthead][code]" rows="4" class="sai-textarea" placeholder="Codice ad specifico per Articoli..."></textarea>
						</div>
						<div class="sai-field-group">
							<label for="sai-single-masthead-override-css">Override CSS Wrapper</label>
							<textarea id="sai-single-masthead-override-css" name="contexts[single][positions][masthead][override_css]" rows="3" class="sai-textarea"></textarea>
						</div>
						<div class="sai-field-group">
							<label for="sai-single-masthead-css-selector">Selettore CSS custom (opzionale)</label>
							<input type="text" id="sai-single-masthead-css-selector" name="contexts[single][positions][masthead][custom_selector]" class="sai-input-text" />
						</div>
					</div>
				</div>

				<!-- SINGLE FOOTER -->
				<div class="sai-card">
					<div class="sai-card-header-row">
						<h2 class="sai-card-title">Footer</h2>
						<span class="sai-tag">Strutturale</span>
					</div>
					<p class="sai-card-desc">Configura la posizione Footer per gli Articoli Singoli.</p>
					
					<div class="sai-field-row sai-checkbox-row">
						<input type="checkbox" id="sai-single-footer-use-global-config" name="contexts[single][positions][footer][use_global_config]" value="1" class="sai-use-global-toggle" data-target="sai-single-footer-override-container" checked />
						<label for="sai-single-footer-use-global-config"><strong>Usa configurazione globale</strong></label>
					</div>

					<div id="sai-single-footer-override-container" class="sai-override-container sai-hidden">
						<div class="sai-field-row sai-checkbox-row">
							<input type="checkbox" id="sai-single-footer-use-default-placement" name="contexts[single][positions][footer][use_default_placement]" value="1" checked />
							<label for="sai-single-footer-use-default-placement"><strong>Usa posizionamento di default (- relativo al tag &lt;footer&gt;)</strong></label>
						</div>

						<div class="sai-field-group">
							<label>Posizione Relativa Footer</label>
							<div class="sai-footer-options-grid">
								<label class="sai-footer-option-box">
									<input type="radio" id="sai-single-footer-position-before" name="contexts[single][positions][footer][footer_position]" value="before_footer" checked />
									<div class="sai-option-desc">
										<strong>Prima del footer (Default)</strong>
										<span>Inietta subito prima del tag &lt;footer&gt;</span>
									</div>
								</label>
								<label class="sai-footer-option-box">
									<input type="radio" id="sai-single-footer-position-after" name="contexts[single][positions][footer][footer_position]" value="after_footer" />
									<div class="sai-option-desc">
										<strong>Dopo il footer</strong>
										<span>Inietta subito dopo il tag &lt;footer&gt; (fine del &lt;body&gt;)</span>
									</div>
								</label>
							</div>
						</div>

						<div class="sai-field-row">
							<div class="sai-field-col">
								<label for="sai-single-footer-active">Abilita Posizione</label>
								<input type="checkbox" id="sai-single-footer-active" name="contexts[single][positions][footer][active]" value="1" />
							</div>
							<div class="sai-field-col">
								<label for="sai-single-footer-desktop-height">Altezza Minima Desktop (px)</label>
								<input type="number" id="sai-single-footer-desktop-height" name="contexts[single][positions][footer][min_height_desktop]" class="sai-input-number" min="0" placeholder="250" />
							</div>
							<div class="sai-field-col">
								<label for="sai-single-footer-mobile-height">Altezza Minima Mobile (px)</label>
								<input type="number" id="sai-single-footer-mobile-height" name="contexts[single][positions][footer][min_height_mobile]" class="sai-input-number" min="0" placeholder="100" />
							</div>
						</div>

						<div class="sai-field-group">
							<label for="sai-single-footer-code">Codice Banner Footer</label>
							<textarea id="sai-single-footer-code" name="contexts[single][positions][footer][code]" rows="4" class="sai-textarea" placeholder="Codice ad specifico per il Footer degli Articoli..."></textarea>
						</div>
						<div class="sai-field-group">
							<label for="sai-single-footer-override-css">Override CSS Wrapper</label>
							<textarea id="sai-single-footer-override-css" name="contexts[single][positions][footer][override_css]" rows="3" class="sai-textarea"></textarea>
						</div>
						<div class="sai-field-group">
							<label for="sai-single-footer-css-selector">Selettore CSS custom (opzionale)</label>
							<input type="text" id="sai-single-footer-css-selector" name="contexts[single][positions][footer][custom_selector]" class="sai-input-text" />
						</div>
					</div>
				</div>

				<!-- SINGLE SIDEBAR TOP -->
				<div class="sai-card">
					<div class="sai-card-header-row">
						<h2 class="sai-card-title">Top Sidebar</h2>
						<span class="sai-tag">Strutturale</span>
					</div>
					<p class="sai-card-desc">Il primo banner nella colonna laterale destra per gli Articoli Singoli.</p>
					
					<div class="sai-field-row sai-checkbox-row">
						<input type="checkbox" id="sai-single-sidebar-top-use-global-config" name="contexts[single][positions][sidebar_top][use_global_config]" value="1" class="sai-use-global-toggle" data-target="sai-single-sidebar-top-override-container" checked />
						<label for="sai-single-sidebar-top-use-global-config"><strong>Usa configurazione globale</strong></label>
					</div>

					<div id="sai-single-sidebar-top-override-container" class="sai-override-container sai-hidden">
						<div class="sai-field-row">
							<div class="sai-field-col">
								<label for="sai-single-sidebar-top-active">Abilita Posizione</label>
								<input type="checkbox" id="sai-single-sidebar-top-active" name="contexts[single][positions][sidebar_top][active]" value="1" />
							</div>
							<div class="sai-field-col">
								<label for="sai-single-sidebar-top-desktop-height">Altezza Minima Desktop (px)</label>
								<input type="number" id="sai-single-sidebar-top-desktop-height" name="contexts[single][positions][sidebar_top][min_height_desktop]" class="sai-input-number" min="0" placeholder="250" />
							</div>
							<div class="sai-field-col">
								<label for="sai-single-sidebar-top-mobile-height">Altezza Minima Mobile (px)</label>
								<input type="number" id="sai-single-sidebar-top-mobile-height" name="contexts[single][positions][sidebar_top][min_height_mobile]" class="sai-input-number" min="0" placeholder="0" />
							</div>
						</div>

						<div class="sai-field-group">
							<label for="sai-single-sidebar-top-code">Codice Banner</label>
							<textarea id="sai-single-sidebar-top-code" name="contexts[single][positions][sidebar_top][code]" rows="3" class="sai-textarea" placeholder="Codice ad specifico per Sidebar Top..."></textarea>
						</div>

						<div class="sai-field-group">
							<label for="sai-single-sidebar-top-override-css">Override CSS Wrapper</label>
							<textarea id="sai-single-sidebar-top-override-css" name="contexts[single][positions][sidebar_top][override_css]" rows="2" class="sai-textarea"></textarea>
						</div>

						<div class="sai-field-group">
							<label for="sai-single-sidebar-top-css-selector">Selettore CSS custom (opzionale)</label>
							<input type="text" id="sai-single-sidebar-top-css-selector" name="contexts[single][positions][sidebar_top][custom_selector]" class="sai-input-text" />
						</div>
					</div>
				</div>

				<!-- SINGLE SIDEBAR STICKY -->
				<div class="sai-card">
					<div class="sai-card-header-row">
						<h2 class="sai-card-title">Sidebar Sticky</h2>
						<span class="sai-tag">Strutturale</span>
					</div>
					<p class="sai-card-desc">Il banner verticale sticky nella colonna laterale per gli Articoli Singoli.</p>
					
					<div class="sai-field-row sai-checkbox-row">
						<input type="checkbox" id="sai-single-sidebar-sticky-use-global-config" name="contexts[single][positions][sidebar_sticky][use_global_config]" value="1" class="sai-use-global-toggle" data-target="sai-single-sidebar-sticky-override-container" checked />
						<label for="sai-single-sidebar-sticky-use-global-config"><strong>Usa configurazione globale</strong></label>
					</div>

					<div id="sai-single-sidebar-sticky-override-container" class="sai-override-container sai-hidden">
						<div class="sai-field-row">
							<div class="sai-field-col">
								<label for="sai-single-sidebar-sticky-active">Abilita Posizione</label>
								<input type="checkbox" id="sai-single-sidebar-sticky-active" name="contexts[single][positions][sidebar_sticky][active]" value="1" />
							</div>
							<div class="sai-field-col">
								<label for="sai-single-sidebar-sticky-desktop-height">Altezza Minima Desktop (px)</label>
								<input type="number" id="sai-single-sidebar-sticky-desktop-height" name="contexts[single][positions][sidebar_sticky][min_height_desktop]" class="sai-input-number" min="0" placeholder="600" />
							</div>
							<div class="sai-field-col">
								<label for="sai-single-sidebar-sticky-mobile-height">Altezza Minima Mobile (px)</label>
								<input type="number" id="sai-single-sidebar-sticky-mobile-height" name="contexts[single][positions][sidebar_sticky][min_height_mobile]" class="sai-input-number" min="0" placeholder="0" />
							</div>
						</div>

						<div class="sai-field-group">
							<label for="sai-single-sidebar-sticky-code">Codice Banner</label>
							<textarea id="sai-single-sidebar-sticky-code" name="contexts[single][positions][sidebar_sticky][code]" rows="3" class="sai-textarea" placeholder="Codice ad specifico per Sidebar Sticky..."></textarea>
						</div>

						<div class="sai-field-group">
							<label for="sai-single-sidebar-sticky-override-css">Override CSS Wrapper</label>
							<textarea id="sai-single-sidebar-sticky-override-css" name="contexts[single][positions][sidebar_sticky][override_css]" rows="2" class="sai-textarea"></textarea>
						</div>

						<div class="sai-field-group">
							<label for="sai-single-sidebar-sticky-css-selector">Selettore CSS custom (opzionale)</label>
							<input type="text" id="sai-single-sidebar-sticky-css-selector" name="contexts[single][positions][sidebar_sticky][custom_selector]" class="sai-input-text" />
						</div>
					</div>
				</div>

				<!-- ATF -->
				<div class="sai-card">
					<div class="sai-card-header-row">
						<h2 class="sai-card-title">ATF — Above The Fold</h2>
						<span class="sai-tag">Contenuto</span>
					</div>
					<p class="sai-card-desc">Banner inserito all'interno dell'articolo prima del primo paragrafo del testo.</p>
					
					<div class="sai-field-row">
						<div class="sai-field-col">
							<label for="sai-single-atf-active">Abilita Posizione</label>
							<input type="checkbox" id="sai-single-atf-active" name="contexts[single][positions][atf][active]" value="1" />
						</div>
						<div class="sai-field-col">
							<label for="sai-single-atf-desktop-height">Altezza Minima Desktop (px)</label>
							<input type="number" id="sai-single-atf-desktop-height" name="contexts[single][positions][atf][min_height_desktop]" class="sai-input-number" min="0" />
						</div>
						<div class="sai-field-col">
							<label for="sai-single-atf-mobile-height">Altezza Minima Mobile (px)</label>
							<input type="number" id="sai-single-atf-mobile-height" name="contexts[single][positions][atf][min_height_mobile]" class="sai-input-number" min="0" />
						</div>
					</div>

					<div class="sai-field-group">
						<label for="sai-single-atf-code">Codice Banner</label>
						<textarea id="sai-single-atf-code" name="contexts[single][positions][atf][code]" rows="4" class="sai-textarea"></textarea>
					</div>

					<div class="sai-field-group">
						<label for="sai-single-atf-override-css">Override CSS Wrapper (CSS personalizzato del contenitore banner)</label>
						<textarea id="sai-single-atf-override-css" name="contexts[single][positions][atf][override_css]" rows="2" class="sai-textarea"></textarea>
					</div>

					<div class="sai-field-group">
						<label for="sai-single-atf-css-selector">Selettore CSS custom (opzionale)</label>
						<input type="text" id="sai-single-atf-css-selector" name="contexts[single][positions][atf][custom_selector]" class="sai-input-text" />
					</div>
				</div>

				<!-- BTF -->
				<div class="sai-card">
					<div class="sai-card-header-row">
						<h2 class="sai-card-title">BTF — Below The Fold</h2>
						<span class="sai-tag">Contenuto</span>
					</div>
					<p class="sai-card-desc">Banner inserito alla fine del testo dell'articolo, prima dei commenti.</p>
					
					<div class="sai-field-row">
						<div class="sai-field-col">
							<label for="sai-single-btf-active">Abilita Posizione</label>
							<input type="checkbox" id="sai-single-btf-active" name="contexts[single][positions][btf][active]" value="1" />
						</div>
						<div class="sai-field-col">
							<label for="sai-single-btf-desktop-height">Altezza Minima Desktop (px)</label>
							<input type="number" id="sai-single-btf-desktop-height" name="contexts[single][positions][btf][min_height_desktop]" class="sai-input-number" min="0" />
						</div>
						<div class="sai-field-col">
							<label for="sai-single-btf-mobile-height">Altezza Minima Mobile (px)</label>
							<input type="number" id="sai-single-btf-mobile-height" name="contexts[single][positions][btf][min_height_mobile]" class="sai-input-number" min="0" />
						</div>
					</div>

					<div class="sai-field-group">
						<label for="sai-single-btf-code">Codice Banner</label>
						<textarea id="sai-single-btf-code" name="contexts[single][positions][btf][code]" rows="4" class="sai-textarea"></textarea>
					</div>

					<div class="sai-field-group">
						<label for="sai-single-btf-override-css">Override CSS Wrapper (CSS personalizzato del contenitore banner)</label>
						<textarea id="sai-single-btf-override-css" name="contexts[single][positions][btf][override_css]" rows="2" class="sai-textarea"></textarea>
					</div>

					<div class="sai-field-group">
						<label for="sai-single-btf-css-selector">Selettore CSS custom (opzionale)</label>
						<input type="text" id="sai-single-btf-css-selector" name="contexts[single][positions][btf][custom_selector]" class="sai-input-text" />
					</div>
				</div>

				<!-- IN TEXT -->
				<div class="sai-card">
					<div class="sai-card-header-row">
						<h2 class="sai-card-title">In Text</h2>
						<span class="sai-tag">Contenuto</span>
					</div>
					<p class="sai-card-desc">Inserimento dinamico di banner nel corpo dell'articolo basato sul conteggio delle parole. I blockquote vengono sempre saltati e i tag &lt;br&gt; vengono usati come punti di split.</p>
					
					<div class="sai-field-row">
						<div class="sai-field-col">
							<label for="sai-single-in-text-active">Abilita Posizione</label>
							<input type="checkbox" id="sai-single-in-text-active" name="contexts[single][positions][in_text][active]" value="1" />
						</div>
						<div class="sai-field-col">
							<label for="sai-single-in-text-desktop-height">Altezza Minima Desktop (px)</label>
							<input type="number" id="sai-single-in-text-desktop-height" name="contexts[single][positions][in_text][min_height_desktop]" class="sai-input-number" min="0" placeholder="250" />
						</div>
						<div class="sai-field-col">
							<label for="sai-single-in-text-mobile-height">Altezza Minima Mobile (px)</label>
							<input type="number" id="sai-single-in-text-mobile-height" name="contexts[single][positions][in_text][min_height_mobile]" class="sai-input-number" min="0" placeholder="250" />
						</div>
					</div>

					<div class="sai-field-row">
						<div class="sai-field-col">
							<label for="sai-single-in-text-max-insertions">Numero massimo inserimenti</label>
							<input type="number" id="sai-single-in-text-max-insertions" name="contexts[single][positions][in_text][max_insertions]" class="sai-input-number" min="1" placeholder="3" />
						</div>
						<div class="sai-field-col">
							<label for="sai-single-in-text-words-interval">Inserisci dopo N parole</label>
							<input type="number" id="sai-single-in-text-words-interval" name="contexts[single][positions][in_text][words_interval]" class="sai-input-number" min="10" placeholder="150" />
						</div>
					</div>

					<div class="sai-field-row sai-checkbox-row">
						<input type="checkbox" id="sai-single-in-text-avoid-btf-single-block" name="contexts[single][positions][in_text][avoid_btf_single_block]" value="1" checked />
						<label for="sai-single-in-text-avoid-btf-single-block"><strong>Non aggiungere in posizione BTF se c'è un solo &lt;p&gt; / &lt;br&gt;</strong></label>
					</div>

					<div class="sai-field-row sai-checkbox-row">
						<input type="checkbox" id="sai-single-in-text-exclude-blockquote" name="contexts[single][positions][in_text][exclude_blockquote]" value="1" checked />
						<label for="sai-single-in-text-exclude-blockquote"><strong>Salta ed escludi i tag &lt;blockquote&gt; dal conteggio e dai punti di split</strong></label>
					</div>

					<div class="sai-field-group">
						<label for="sai-single-in-text-excluded-container-tokens">Esclusione contenitori (Classi / ID semplici)</label>
						<input type="text" id="sai-single-in-text-excluded-container-tokens" name="contexts[single][positions][in_text][excluded_container_tokens]" class="sai-input-text" placeholder="Es. .toc-box, #inline-note" />
						<p class="sai-field-desc">Inserisci classi e ID semplici separati da virgola. I contenuti interni saranno comunque conteggiati per la soglia N parole, ma non usati come punti di inserimento.</p>
					</div>

					<div class="sai-field-group">
						<label for="sai-single-in-text-code">Codice Banner</label>
						<textarea id="sai-single-in-text-code" name="contexts[single][positions][in_text][code]" rows="4" class="sai-textarea"></textarea>
					</div>

					<div class="sai-field-group">
						<label for="sai-single-in-text-override-css">Override CSS Wrapper (CSS personalizzato del contenitore banner)</label>
						<textarea id="sai-single-in-text-override-css" name="contexts[single][positions][in_text][override_css]" rows="2" class="sai-textarea"></textarea>
					</div>
				</div>
			</div>

			<!-- TAB 4: CATEGORIE / ARCHIVI -->
			<div id="sai-tab-archive" class="sai-tab-content">
				<!-- ARCHIVE MASTHEAD -->
				<div class="sai-card">
					<div class="sai-card-header-row">
						<h2 class="sai-card-title">Masthead</h2>
						<span class="sai-tag">Strutturale</span>
					</div>
					<p class="sai-card-desc">Configura la posizione Masthead per le pagine Categorie e Archivio.</p>
					
					<div class="sai-field-row sai-checkbox-row">
						<input type="checkbox" id="sai-archive-masthead-use-global-config" name="contexts[archive][positions][masthead][use_global_config]" value="1" class="sai-use-global-toggle" data-target="sai-archive-masthead-override-container" checked />
						<label for="sai-archive-masthead-use-global-config"><strong>Usa configurazione globale</strong></label>
					</div>

					<div id="sai-archive-masthead-override-container" class="sai-override-container sai-hidden">
						<div class="sai-field-row sai-checkbox-row">
							<input type="checkbox" id="sai-archive-masthead-use-default-placement" name="contexts[archive][positions][masthead][use_default_placement]" value="1" checked />
							<label for="sai-archive-masthead-use-default-placement"><strong>Usa posizionamento di default (- dopo &lt;header&gt;)</strong></label>
						</div>

						<div class="sai-field-row">
							<div class="sai-field-col">
								<label for="sai-archive-masthead-active">Abilita Posizione</label>
								<input type="checkbox" id="sai-archive-masthead-active" name="contexts[archive][positions][masthead][active]" value="1" />
							</div>
							<div class="sai-field-col">
								<label for="sai-archive-masthead-desktop-height">Altezza Minima Desktop (px)</label>
								<input type="number" id="sai-archive-masthead-desktop-height" name="contexts[archive][positions][masthead][min_height_desktop]" class="sai-input-number" min="0" placeholder="250" />
							</div>
							<div class="sai-field-col">
								<label for="sai-archive-masthead-mobile-height">Altezza Minima Mobile (px)</label>
								<input type="number" id="sai-archive-masthead-mobile-height" name="contexts[archive][positions][masthead][min_height_mobile]" class="sai-input-number" min="0" placeholder="100" />
							</div>
						</div>

						<div class="sai-field-group">
							<label for="sai-archive-masthead-code">Codice Banner Masthead</label>
							<textarea id="sai-archive-masthead-code" name="contexts[archive][positions][masthead][code]" rows="4" class="sai-textarea" placeholder="Codice ad specifico per Categorie/Archivi..."></textarea>
						</div>
						<div class="sai-field-group">
							<label for="sai-archive-masthead-override-css">Override CSS Wrapper</label>
							<textarea id="sai-archive-masthead-override-css" name="contexts[archive][positions][masthead][override_css]" rows="3" class="sai-textarea"></textarea>
						</div>
						<div class="sai-field-group">
							<label for="sai-archive-masthead-css-selector">Selettore CSS custom (opzionale)</label>
							<input type="text" id="sai-archive-masthead-css-selector" name="contexts[archive][positions][masthead][custom_selector]" class="sai-input-text" />
						</div>
					</div>
				</div>

				<!-- ARCHIVE FOOTER -->
				<div class="sai-card">
					<div class="sai-card-header-row">
						<h2 class="sai-card-title">Footer</h2>
						<span class="sai-tag">Strutturale</span>
					</div>
					<p class="sai-card-desc">Configura la posizione Footer per le pagine Categorie e Archivio.</p>
					
					<div class="sai-field-row sai-checkbox-row">
						<input type="checkbox" id="sai-archive-footer-use-global-config" name="contexts[archive][positions][footer][use_global_config]" value="1" class="sai-use-global-toggle" data-target="sai-archive-footer-override-container" checked />
						<label for="sai-archive-footer-use-global-config"><strong>Usa configurazione globale</strong></label>
					</div>

					<div id="sai-archive-footer-override-container" class="sai-override-container sai-hidden">
						<div class="sai-field-row sai-checkbox-row">
							<input type="checkbox" id="sai-archive-footer-use-default-placement" name="contexts[archive][positions][footer][use_default_placement]" value="1" checked />
							<label for="sai-archive-footer-use-default-placement"><strong>Usa posizionamento di default (- relativo al tag &lt;footer&gt;)</strong></label>
						</div>

						<div class="sai-field-group">
							<label>Posizione Relativa Footer</label>
							<div class="sai-footer-options-grid">
								<label class="sai-footer-option-box">
									<input type="radio" id="sai-archive-footer-position-before" name="contexts[archive][positions][footer][footer_position]" value="before_footer" checked />
									<div class="sai-option-desc">
										<strong>Prima del footer (Default)</strong>
										<span>Inietta subito prima del tag &lt;footer&gt;</span>
									</div>
								</label>
								<label class="sai-footer-option-box">
									<input type="radio" id="sai-archive-footer-position-after" name="contexts[archive][positions][footer][footer_position]" value="after_footer" />
									<div class="sai-option-desc">
										<strong>Dopo il footer</strong>
										<span>Inietta subito dopo il tag &lt;footer&gt; (fine del &lt;body&gt;)</span>
									</div>
								</label>
							</div>
						</div>

						<div class="sai-field-row">
							<div class="sai-field-col">
								<label for="sai-archive-footer-active">Abilita Posizione</label>
								<input type="checkbox" id="sai-archive-footer-active" name="contexts[archive][positions][footer][active]" value="1" />
							</div>
							<div class="sai-field-col">
								<label for="sai-archive-footer-desktop-height">Altezza Minima Desktop (px)</label>
								<input type="number" id="sai-archive-footer-desktop-height" name="contexts[archive][positions][footer][min_height_desktop]" class="sai-input-number" min="0" placeholder="250" />
							</div>
							<div class="sai-field-col">
								<label for="sai-archive-footer-mobile-height">Altezza Minima Mobile (px)</label>
								<input type="number" id="sai-archive-footer-mobile-height" name="contexts[archive][positions][footer][min_height_mobile]" class="sai-input-number" min="0" placeholder="100" />
							</div>
						</div>

						<div class="sai-field-group">
							<label for="sai-archive-footer-code">Codice Banner Footer</label>
							<textarea id="sai-archive-footer-code" name="contexts[archive][positions][footer][code]" rows="4" class="sai-textarea" placeholder="Codice ad specifico per il Footer di Categorie/Archivi..."></textarea>
						</div>
						<div class="sai-field-group">
							<label for="sai-archive-footer-override-css">Override CSS Wrapper</label>
							<textarea id="sai-archive-footer-override-css" name="contexts[archive][positions][footer][override_css]" rows="3" class="sai-textarea"></textarea>
						</div>
						<div class="sai-field-group">
							<label for="sai-archive-footer-css-selector">Selettore CSS custom (opzionale)</label>
							<input type="text" id="sai-archive-footer-css-selector" name="contexts[archive][positions][footer][custom_selector]" class="sai-input-text" />
						</div>
					</div>
				</div>

				<!-- ARCHIVE SIDEBAR TOP -->
				<div class="sai-card">
					<div class="sai-card-header-row">
						<h2 class="sai-card-title">Top Sidebar</h2>
						<span class="sai-tag">Strutturale</span>
					</div>
					<p class="sai-card-desc">Il primo banner nella colonna laterale destra per le pagine Categorie e Archivio.</p>
					
					<div class="sai-field-row sai-checkbox-row">
						<input type="checkbox" id="sai-archive-sidebar-top-use-global-config" name="contexts[archive][positions][sidebar_top][use_global_config]" value="1" class="sai-use-global-toggle" data-target="sai-archive-sidebar-top-override-container" checked />
						<label for="sai-archive-sidebar-top-use-global-config"><strong>Usa configurazione globale</strong></label>
					</div>

					<div id="sai-archive-sidebar-top-override-container" class="sai-override-container sai-hidden">
						<div class="sai-field-row">
							<div class="sai-field-col">
								<label for="sai-archive-sidebar-top-active">Abilita Posizione</label>
								<input type="checkbox" id="sai-archive-sidebar-top-active" name="contexts[archive][positions][sidebar_top][active]" value="1" />
							</div>
							<div class="sai-field-col">
								<label for="sai-archive-sidebar-top-desktop-height">Altezza Minima Desktop (px)</label>
								<input type="number" id="sai-archive-sidebar-top-desktop-height" name="contexts[archive][positions][sidebar_top][min_height_desktop]" class="sai-input-number" min="0" placeholder="250" />
							</div>
							<div class="sai-field-col">
								<label for="sai-archive-sidebar-top-mobile-height">Altezza Minima Mobile (px)</label>
								<input type="number" id="sai-archive-sidebar-top-mobile-height" name="contexts[archive][positions][sidebar_top][min_height_mobile]" class="sai-input-number" min="0" placeholder="0" />
							</div>
						</div>

						<div class="sai-field-group">
							<label for="sai-archive-sidebar-top-code">Codice Banner</label>
							<textarea id="sai-archive-sidebar-top-code" name="contexts[archive][positions][sidebar_top][code]" rows="3" class="sai-textarea" placeholder="Codice ad specifico per Sidebar Top..."></textarea>
						</div>

						<div class="sai-field-group">
							<label for="sai-archive-sidebar-top-override-css">Override CSS Wrapper</label>
							<textarea id="sai-archive-sidebar-top-override-css" name="contexts[archive][positions][sidebar_top][override_css]" rows="2" class="sai-textarea"></textarea>
						</div>

						<div class="sai-field-group">
							<label for="sai-archive-sidebar-top-css-selector">Selettore CSS custom (opzionale)</label>
							<input type="text" id="sai-archive-sidebar-top-css-selector" name="contexts[archive][positions][sidebar_top][custom_selector]" class="sai-input-text" />
						</div>
					</div>
				</div>

				<!-- ARCHIVE SIDEBAR STICKY -->
				<div class="sai-card">
					<div class="sai-card-header-row">
						<h2 class="sai-card-title">Sidebar Sticky</h2>
						<span class="sai-tag">Strutturale</span>
					</div>
					<p class="sai-card-desc">Il banner verticale sticky nella colonna laterale per le pagine Categorie e Archivio.</p>
					
					<div class="sai-field-row sai-checkbox-row">
						<input type="checkbox" id="sai-archive-sidebar-sticky-use-global-config" name="contexts[archive][positions][sidebar_sticky][use_global_config]" value="1" class="sai-use-global-toggle" data-target="sai-archive-sidebar-sticky-override-container" checked />
						<label for="sai-archive-sidebar-sticky-use-global-config"><strong>Usa configurazione globale</strong></label>
					</div>

					<div id="sai-archive-sidebar-sticky-override-container" class="sai-override-container sai-hidden">
						<div class="sai-field-row">
							<div class="sai-field-col">
								<label for="sai-archive-sidebar-sticky-active">Abilita Posizione</label>
								<input type="checkbox" id="sai-archive-sidebar-sticky-active" name="contexts[archive][positions][sidebar_sticky][active]" value="1" />
							</div>
							<div class="sai-field-col">
								<label for="sai-archive-sidebar-sticky-desktop-height">Altezza Minima Desktop (px)</label>
								<input type="number" id="sai-archive-sidebar-sticky-desktop-height" name="contexts[archive][positions][sidebar_sticky][min_height_desktop]" class="sai-input-number" min="0" placeholder="600" />
							</div>
							<div class="sai-field-col">
								<label for="sai-archive-sidebar-sticky-mobile-height">Altezza Minima Mobile (px)</label>
								<input type="number" id="sai-archive-sidebar-sticky-mobile-height" name="contexts[archive][positions][sidebar_sticky][min_height_mobile]" class="sai-input-number" min="0" placeholder="0" />
							</div>
						</div>

						<div class="sai-field-group">
							<label for="sai-archive-sidebar-sticky-code">Codice Banner</label>
							<textarea id="sai-archive-sidebar-sticky-code" name="contexts[archive][positions][sidebar_sticky][code]" rows="3" class="sai-textarea" placeholder="Codice ad specifico per Sidebar Sticky..."></textarea>
						</div>

						<div class="sai-field-group">
							<label for="sai-archive-sidebar-sticky-override-css">Override CSS Wrapper</label>
							<textarea id="sai-archive-sidebar-sticky-override-css" name="contexts[archive][positions][sidebar_sticky][override_css]" rows="2" class="sai-textarea"></textarea>
						</div>

						<div class="sai-field-group">
							<label for="sai-archive-sidebar-sticky-css-selector">Selettore CSS custom (opzionale)</label>
							<input type="text" id="sai-archive-sidebar-sticky-css-selector" name="contexts[archive][positions][sidebar_sticky][custom_selector]" class="sai-input-text" />
						</div>
					</div>
				</div>

				<!-- GRID ARCHIVE -->
				<div class="sai-card">
					<div class="sai-card-header-row">
						<h2 class="sai-card-title">Box in Griglia (Archivi / Categorie)</h2>
						<span class="sai-tag">Griglia</span>
					</div>
					<p class="sai-card-desc">Inserimento automatico di annunci pubblicitari all'interno della griglia degli articoli nelle pagine Categorie e Archivio.</p>
					
					<div class="sai-field-row">
						<div class="sai-field-col">
							<label for="sai-archive-grid-archive-active">Abilita Posizione</label>
							<input type="checkbox" id="sai-archive-grid-archive-active" name="contexts[archive][positions][grid_archive][active]" value="1" />
						</div>
						<div class="sai-field-col">
							<label for="sai-archive-grid-archive-target-element">Target Element Selector (CSS)</label>
							<input type="text" id="sai-archive-grid-archive-target-element" name="contexts[archive][positions][grid_archive][target_element]" class="sai-input-text" placeholder="Es. .post-card, article" />
						</div>
						<div class="sai-field-col">
							<label for="sai-archive-grid-archive-frequency">Frequenza / Posizione (N° elemento)</label>
							<input type="number" id="sai-archive-grid-archive-frequency" name="contexts[archive][positions][grid_archive][frequency]" class="sai-input-number" min="1" placeholder="3" />
						</div>
					</div>

					<div class="sai-field-row">
						<div class="sai-field-col">
							<label for="sai-archive-grid-archive-desktop-height">Altezza Minima Desktop (px)</label>
							<input type="number" id="sai-archive-grid-archive-desktop-height" name="contexts[archive][positions][grid_archive][min_height_desktop]" class="sai-input-number" min="0" />
						</div>
						<div class="sai-field-col">
							<label for="sai-archive-grid-archive-mobile-height">Altezza Minima Mobile (px)</label>
							<input type="number" id="sai-archive-grid-archive-mobile-height" name="contexts[archive][positions][grid_archive][min_height_mobile]" class="sai-input-number" min="0" />
						</div>
					</div>

					<div class="sai-field-group">
						<label for="sai-archive-grid-archive-code">Codice Banner</label>
						<textarea id="sai-archive-grid-archive-code" name="contexts[archive][positions][grid_archive][code]" rows="4" class="sai-textarea"></textarea>
					</div>

					<div class="sai-field-group">
						<label for="sai-archive-grid-archive-override-css">Override CSS Wrapper (CSS personalizzato del contenitore banner)</label>
						<textarea id="sai-archive-grid-archive-override-css" name="contexts[archive][positions][grid_archive][override_css]" rows="2" class="sai-textarea"></textarea>
					</div>

					<div class="sai-field-group">
						<label for="sai-archive-grid-archive-css-selector">Selettore CSS custom (opzionale)</label>
						<input type="text" id="sai-archive-grid-archive-css-selector" name="contexts[archive][positions][grid_archive][custom_selector]" class="sai-input-text" />
					</div>
				</div>
			</div>
		</div>

		<!-- Action Bar Sticky -->
		<div class="sai-action-bar">
			<button type="submit" id="sai-save-btn" class="sai-btn-primary">Salva Impostazioni</button>
			<span id="sai-spinner" class="sai-spinner"></span>
			<div id="sai-feedback" class="sai-feedback"></div>
		</div>
	</form>
</div>
