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

				<div class="sai-card">
					<div class="sai-card-header-row">
						<h2 class="sai-card-title">Masthead</h2>
						<span class="sai-tag">Strutturale</span>
					</div>
					<p class="sai-card-desc">Configura il primo banner pubblicitario visibile sotto l'header globale del sito.</p>
					
					<div class="sai-field-row sai-checkbox-row">
						<input type="checkbox" id="sai-use-default-placement" name="positions[masthead][use_default_placement]" value="1" checked />
						<label for="sai-use-default-placement"><strong>Usa posizionamento di default (- dopo &lt;header&gt;)</strong></label>
					</div>

					<div class="sai-field-row">
						<div class="sai-field-col">
							<label for="sai-masthead-active">Abilita Posizione</label>
							<input type="checkbox" id="sai-masthead-active" name="positions[masthead][active]" value="1" />
						</div>
						<div class="sai-field-col">
							<label for="sai-masthead-desktop-height">Altezza Minima Desktop (px)</label>
							<input type="number" id="sai-masthead-desktop-height" name="positions[masthead][min_height_desktop]" class="sai-input-number" min="0" placeholder="250" />
						</div>
						<div class="sai-field-col">
							<label for="sai-masthead-mobile-height">Altezza Minima Mobile (px)</label>
							<input type="number" id="sai-masthead-mobile-height" name="positions[masthead][min_height_mobile]" class="sai-input-number" min="0" placeholder="100" />
						</div>
					</div>

					<div class="sai-field-group">
						<label for="sai-banner-code">Codice Banner Masthead</label>
						<textarea id="sai-banner-code" name="positions[masthead][code]" rows="4" class="sai-textarea" placeholder="Codice HTML/JS per l'annuncio..."></textarea>
					</div>
					<div class="sai-field-group">
						<label for="sai-override-css">Override CSS Wrapper (CSS personalizzato del contenitore banner)</label>
						<textarea id="sai-override-css" name="positions[masthead][override_css]" rows="3" class="sai-textarea" placeholder="Es. margin: 20px 0; text-align: center;"></textarea>
						<p class="sai-field-desc">Queste dichiarazioni CSS verranno applicate direttamente come stile inline sul <strong>&lt;div&gt; wrapper esterno del plugin (.sai-ad-wrapper)</strong>, non sul markup interno del banner.</p>
					</div>

					<div class="sai-field-group">
						<label for="sai-css-selector">Selettore CSS custom (opzionale)</label>
						<input type="text" id="sai-css-selector" name="positions[masthead][custom_selector]" class="sai-input-text" placeholder="Es. #custom-header-wrapper" />
						<p class="sai-field-desc" id="sai-masthead-selector-desc">Il selettore custom sarà usato solo se disattivi il posizionamento di default.</p>
					</div>
				</div>

				<div class="sai-card">
					<div class="sai-card-header-row">
						<h2 class="sai-card-title">Footer</h2>
						<span class="sai-tag">Strutturale</span>
					</div>
					<p class="sai-card-desc">Configura il banner pubblicitario da visualizzare immediatamente sopra il footer globale del sito.</p>
					
					<div class="sai-field-row sai-checkbox-row">
						<input type="checkbox" id="sai-footer-use-default-placement" name="positions[footer][use_default_placement]" value="1" checked />
						<label for="sai-footer-use-default-placement"><strong>Usa posizionamento di default (- relativo al tag &lt;footer&gt;)</strong></label>
					</div>

					<div class="sai-field-group">
						<label for="sai-footer-position">Posizione Relativa Footer</label>
						<select id="sai-footer-position" name="positions[footer][footer_position]" class="sai-input-text">
							<option value="before_footer">Prima del footer (default)</option>
							<option value="after_footer">Dopo il footer</option>
						</select>
						<p class="sai-field-desc">Scegli se inserire il banner subito prima del tag &lt;footer&gt; o subito dopo (alla fine del tag &lt;body&gt;).</p>
					</div>

					<div class="sai-field-row">
						<div class="sai-field-col">
							<label for="sai-footer-active">Abilita Posizione</label>
							<input type="checkbox" id="sai-footer-active" name="positions[footer][active]" value="1" />
						</div>
						<div class="sai-field-col">
							<label for="sai-footer-desktop-height">Altezza Minima Desktop (px)</label>
							<input type="number" id="sai-footer-desktop-height" name="positions[footer][min_height_desktop]" class="sai-input-number" min="0" placeholder="250" />
						</div>
						<div class="sai-field-col">
							<label for="sai-footer-mobile-height">Altezza Minima Mobile (px)</label>
							<input type="number" id="sai-footer-mobile-height" name="positions[footer][min_height_mobile]" class="sai-input-number" min="0" placeholder="100" />
						</div>
					</div>

					<div class="sai-field-group">
						<label for="sai-footer-code">Codice Banner Footer</label>
						<textarea id="sai-footer-code" name="positions[footer][code]" rows="4" class="sai-textarea" placeholder="Codice HTML/JS per l'annuncio..."></textarea>
					</div>
					<div class="sai-field-group">
						<label for="sai-footer-override-css">Override CSS Wrapper (CSS personalizzato del contenitore banner)</label>
						<textarea id="sai-footer-override-css" name="positions[footer][override_css]" rows="3" class="sai-textarea" placeholder="Es. margin: 20px 0; text-align: center;"></textarea>
						<p class="sai-field-desc">Queste dichiarazioni CSS verranno applicate direttamente come stile inline sul <strong>&lt;div&gt; wrapper esterno del plugin (.sai-ad-wrapper)</strong>, non sul markup interno del banner.</p>
					</div>

					<div class="sai-field-group">
						<label for="sai-footer-css-selector">Selettore CSS custom (opzionale)</label>
						<input type="text" id="sai-footer-css-selector" name="positions[footer][custom_selector]" class="sai-input-text" placeholder="Es. #custom-footer-wrapper" />
						<p class="sai-field-desc" id="sai-footer-selector-desc">Il selettore custom sarà usato solo se disattivi il posizionamento di default.</p>
					</div>
				</div>

				<div class="sai-card">
					<div class="sai-card-header-row">
						<h2 class="sai-card-title">Top Sidebar</h2>
						<span class="sai-tag">Strutturale</span>
					</div>
					<p class="sai-card-desc">Il primo banner nella colonna laterale destra, visibile in alto su desktop.</p>
					
					<div class="sai-field-row">
						<div class="sai-field-col">
							<label for="sai-sidebar-top-active">Abilita Posizione</label>
							<input type="checkbox" id="sai-sidebar-top-active" name="positions[sidebar_top][active]" value="1" />
						</div>
						<div class="sai-field-col">
							<label for="sai-sidebar-top-desktop-height">Altezza Minima Desktop (px)</label>
							<input type="number" id="sai-sidebar-top-desktop-height" name="positions[sidebar_top][min_height_desktop]" class="sai-input-number" min="0" />
						</div>
						<div class="sai-field-col">
							<label for="sai-sidebar-top-mobile-height">Altezza Minima Mobile (px)</label>
							<input type="number" id="sai-sidebar-top-mobile-height" name="positions[sidebar_top][min_height_mobile]" class="sai-input-number" min="0" />
						</div>
					</div>

					<div class="sai-field-group">
						<label for="sai-sidebar-top-code">Codice Banner</label>
						<textarea id="sai-sidebar-top-code" name="positions[sidebar_top][code]" rows="3" class="sai-textarea"></textarea>
					</div>

					<div class="sai-field-group">
						<label for="sai-sidebar-top-override-css">Override CSS Wrapper (CSS personalizzato del contenitore banner)</label>
						<textarea id="sai-sidebar-top-override-css" name="positions[sidebar_top][override_css]" rows="2" class="sai-textarea"></textarea>
						<p class="sai-field-desc">Queste dichiarazioni CSS verranno applicate come stile inline sul wrapper esterno del plugin (.sai-ad-wrapper).</p>
					</div>

					<div class="sai-field-group">
						<label for="sai-sidebar-top-selector">Selettore CSS custom (opzionale)</label>
						<input type="text" id="sai-sidebar-top-selector" name="positions[sidebar_top][custom_selector]" class="sai-input-text" />
					</div>
				</div>

				<div class="sai-card">
					<div class="sai-card-header-row">
						<h2 class="sai-card-title">Sidebar Sticky</h2>
						<span class="sai-tag">Strutturale</span>
					</div>
					<p class="sai-card-desc">Un banner verticale (es. 300x600) posizionato in fondo alla sidebar che segue lo scroll dell'utente.</p>
					
					<div class="sai-field-row">
						<div class="sai-field-col">
							<label for="sai-sidebar-sticky-active">Abilita Posizione</label>
							<input type="checkbox" id="sai-sidebar-sticky-active" name="positions[sidebar_sticky][active]" value="1" />
						</div>
						<div class="sai-field-col">
							<label for="sai-sidebar-sticky-desktop-height">Altezza Minima Desktop (px)</label>
							<input type="number" id="sai-sidebar-sticky-desktop-height" name="positions[sidebar_sticky][min_height_desktop]" class="sai-input-number" min="0" />
						</div>
						<div class="sai-field-col">
							<label for="sai-sidebar-sticky-mobile-height">Altezza Minima Mobile (px)</label>
							<input type="number" id="sai-sidebar-sticky-mobile-height" name="positions[sidebar_sticky][min_height_mobile]" class="sai-input-number" min="0" />
						</div>
					</div>

					<div class="sai-field-group">
						<label for="sai-sidebar-sticky-code">Codice Banner</label>
						<textarea id="sai-sidebar-sticky-code" name="positions[sidebar_sticky][code]" rows="3" class="sai-textarea"></textarea>
					</div>

					<div class="sai-field-group">
						<label for="sai-sidebar-sticky-override-css">Override CSS Wrapper (CSS personalizzato del contenitore banner)</label>
						<textarea id="sai-sidebar-sticky-override-css" name="positions[sidebar_sticky][override_css]" rows="2" class="sai-textarea"></textarea>
						<p class="sai-field-desc">Queste dichiarazioni CSS verranno applicate come stile inline sul wrapper esterno del plugin (.sai-ad-wrapper).</p>
					</div>

					<div class="sai-field-group">
						<label for="sai-sidebar-sticky-selector">Selettore CSS custom (opzionale)</label>
						<input type="text" id="sai-sidebar-sticky-selector" name="positions[sidebar_sticky][custom_selector]" class="sai-input-text" />
					</div>
				</div>
			</div>

			<!-- TAB 2: HOME -->
			<div id="sai-tab-home" class="sai-tab-content">
				<div class="sai-card">
					<div class="sai-card-header-row">
						<h2 class="sai-card-title">Box in Griglia (Home Page)</h2>
						<span class="sai-tag">Griglia</span>
					</div>
					<p class="sai-card-desc">Inserimento automatico di annunci pubblicitari all'interno della griglia degli articoli in Home Page.</p>
					
					<div class="sai-field-row">
						<div class="sai-field-col">
							<label for="sai-grid-home-active">Abilita Posizione</label>
							<input type="checkbox" id="sai-grid-home-active" name="positions[grid_home][active]" value="1" />
						</div>
						<div class="sai-field-col">
							<label for="sai-grid-home-target-element">Target Element Selector (CSS)</label>
							<input type="text" id="sai-grid-home-target-element" name="positions[grid_home][target_element]" class="sai-input-text" placeholder="Es. .post-card, article" />
						</div>
						<div class="sai-field-col">
							<label for="sai-grid-home-frequency">Frequenza / Posizione (N° elemento)</label>
							<input type="number" id="sai-grid-home-frequency" name="positions[grid_home][frequency]" class="sai-input-number" min="1" placeholder="3" />
						</div>
					</div>

					<div class="sai-field-row">
						<div class="sai-field-col">
							<label for="sai-grid-home-desktop-height">Altezza Minima Desktop (px)</label>
							<input type="number" id="sai-grid-home-desktop-height" name="positions[grid_home][min_height_desktop]" class="sai-input-number" min="0" />
						</div>
						<div class="sai-field-col">
							<label for="sai-grid-home-mobile-height">Altezza Minima Mobile (px)</label>
							<input type="number" id="sai-grid-home-mobile-height" name="positions[grid_home][min_height_mobile]" class="sai-input-number" min="0" />
						</div>
					</div>

					<div class="sai-field-group">
						<label for="sai-grid-home-banner-code">Codice Banner</label>
						<textarea id="sai-grid-home-banner-code" name="positions[grid_home][code]" rows="4" class="sai-textarea"></textarea>
					</div>

					<div class="sai-field-group">
						<label for="sai-grid-home-override-css">Override CSS Wrapper (CSS personalizzato del contenitore banner)</label>
						<textarea id="sai-grid-home-override-css" name="positions[grid_home][override_css]" rows="2" class="sai-textarea"></textarea>
						<p class="sai-field-desc">Queste dichiarazioni CSS verranno applicate come stile inline sul wrapper esterno del plugin (.sai-ad-wrapper).</p>
					</div>

					<div class="sai-field-group">
						<label for="sai-grid-home-custom-selector">Selettore CSS custom (opzionale)</label>
						<input type="text" id="sai-grid-home-custom-selector" name="positions[grid_home][custom_selector]" class="sai-input-text" />
					</div>
				</div>
			</div>

			<!-- TAB 3: ARTICOLO SINGOLO -->
			<div id="sai-tab-article" class="sai-tab-content">
				<div class="sai-card">
					<div class="sai-card-header-row">
						<h2 class="sai-card-title">ATF — Above The Fold</h2>
						<span class="sai-tag">Contenuto</span>
					</div>
					<p class="sai-card-desc">Banner inserito all'interno dell'articolo prima del primo paragrafo del testo.</p>
					
					<div class="sai-field-row">
						<div class="sai-field-col">
							<label for="sai-atf-active">Abilita Posizione</label>
							<input type="checkbox" id="sai-atf-active" name="positions[atf][active]" value="1" />
						</div>
						<div class="sai-field-col">
							<label for="sai-atf-desktop-height">Altezza Minima Desktop (px)</label>
							<input type="number" id="sai-atf-desktop-height" name="positions[atf][min_height_desktop]" class="sai-input-number" min="0" />
						</div>
						<div class="sai-field-col">
							<label for="sai-atf-mobile-height">Altezza Minima Mobile (px)</label>
							<input type="number" id="sai-atf-mobile-height" name="positions[atf][min_height_mobile]" class="sai-input-number" min="0" />
						</div>
					</div>

					<div class="sai-field-group">
						<label for="sai-atf-banner-code">Codice Banner</label>
						<textarea id="sai-atf-banner-code" name="positions[atf][code]" rows="4" class="sai-textarea"></textarea>
					</div>

					<div class="sai-field-group">
						<label for="sai-atf-override-css">Override CSS Wrapper (CSS personalizzato del contenitore banner)</label>
						<textarea id="sai-atf-override-css" name="positions[atf][override_css]" rows="2" class="sai-textarea"></textarea>
						<p class="sai-field-desc">Queste dichiarazioni CSS verranno applicate come stile inline sul wrapper esterno del plugin (.sai-ad-wrapper).</p>
					</div>

					<div class="sai-field-group">
						<label for="sai-atf-custom-selector">Selettore CSS custom (opzionale)</label>
						<input type="text" id="sai-atf-custom-selector" name="positions[atf][custom_selector]" class="sai-input-text" />
					</div>
				</div>

				<div class="sai-card">
					<div class="sai-card-header-row">
						<h2 class="sai-card-title">BTF — Below The Fold</h2>
						<span class="sai-tag">Contenuto</span>
					</div>
					<p class="sai-card-desc">Banner inserito alla fine del testo dell'articolo, prima dei commenti.</p>
					
					<div class="sai-field-row">
						<div class="sai-field-col">
							<label for="sai-btf-active">Abilita Posizione</label>
							<input type="checkbox" id="sai-btf-active" name="positions[btf][active]" value="1" />
						</div>
						<div class="sai-field-col">
							<label for="sai-btf-desktop-height">Altezza Minima Desktop (px)</label>
							<input type="number" id="sai-btf-desktop-height" name="positions[btf][min_height_desktop]" class="sai-input-number" min="0" />
						</div>
						<div class="sai-field-col">
							<label for="sai-btf-mobile-height">Altezza Minima Mobile (px)</label>
							<input type="number" id="sai-btf-mobile-height" name="positions[btf][min_height_mobile]" class="sai-input-number" min="0" />
						</div>
					</div>

					<div class="sai-field-group">
						<label for="sai-btf-banner-code">Codice Banner</label>
						<textarea id="sai-btf-banner-code" name="positions[btf][code]" rows="4" class="sai-textarea"></textarea>
					</div>

					<div class="sai-field-group">
						<label for="sai-btf-override-css">Override CSS Wrapper (CSS personalizzato del contenitore banner)</label>
						<textarea id="sai-btf-override-css" name="positions[btf][override_css]" rows="2" class="sai-textarea"></textarea>
						<p class="sai-field-desc">Queste dichiarazioni CSS verranno applicate come stile inline sul wrapper esterno del plugin (.sai-ad-wrapper).</p>
					</div>

					<div class="sai-field-group">
						<label for="sai-btf-custom-selector">Selettore CSS custom (opzionale)</label>
						<input type="text" id="sai-btf-custom-selector" name="positions[btf][custom_selector]" class="sai-input-text" />
					</div>
				</div>
			</div>

			<!-- TAB 4: CATEGORIE / ARCHIVI -->
			<div id="sai-tab-archive" class="sai-tab-content">
				<div class="sai-card">
					<div class="sai-card-header-row">
						<h2 class="sai-card-title">Box in Griglia (Archivi / Categorie)</h2>
						<span class="sai-tag">Griglia</span>
					</div>
					<p class="sai-card-desc">Inserimento automatico di annunci pubblicitari all'interno della griglia degli articoli nelle pagine Categorie e Archivio.</p>
					
					<div class="sai-field-row">
						<div class="sai-field-col">
							<label for="sai-grid-archive-active">Abilita Posizione</label>
							<input type="checkbox" id="sai-grid-archive-active" name="positions[grid_archive][active]" value="1" />
						</div>
						<div class="sai-field-col">
							<label for="sai-grid-archive-target-element">Target Element Selector (CSS)</label>
							<input type="text" id="sai-grid-archive-target-element" name="positions[grid_archive][target_element]" class="sai-input-text" placeholder="Es. .post-card, article" />
						</div>
						<div class="sai-field-col">
							<label for="sai-grid-archive-frequency">Frequenza / Posizione (N° elemento)</label>
							<input type="number" id="sai-grid-archive-frequency" name="positions[grid_archive][frequency]" class="sai-input-number" min="1" placeholder="3" />
						</div>
					</div>

					<div class="sai-field-row">
						<div class="sai-field-col">
							<label for="sai-grid-archive-desktop-height">Altezza Minima Desktop (px)</label>
							<input type="number" id="sai-grid-archive-desktop-height" name="positions[grid_archive][min_height_desktop]" class="sai-input-number" min="0" />
						</div>
						<div class="sai-field-col">
							<label for="sai-grid-archive-mobile-height">Altezza Minima Mobile (px)</label>
							<input type="number" id="sai-grid-archive-mobile-height" name="positions[grid_archive][min_height_mobile]" class="sai-input-number" min="0" />
						</div>
					</div>

					<div class="sai-field-group">
						<label for="sai-grid-archive-banner-code">Codice Banner</label>
						<textarea id="sai-grid-archive-banner-code" name="positions[grid_archive][code]" rows="4" class="sai-textarea"></textarea>
					</div>

					<div class="sai-field-group">
						<label for="sai-grid-archive-override-css">Override CSS Wrapper (CSS personalizzato del contenitore banner)</label>
						<textarea id="sai-grid-archive-override-css" name="positions[grid_archive][override_css]" rows="2" class="sai-textarea"></textarea>
						<p class="sai-field-desc">Queste dichiarazioni CSS verranno applicate come stile inline sul wrapper esterno del plugin (.sai-ad-wrapper).</p>
					</div>

					<div class="sai-field-group">
						<label for="sai-grid-archive-custom-selector">Selettore CSS custom (opzionale)</label>
						<input type="text" id="sai-grid-archive-custom-selector" name="positions[grid_archive][custom_selector]" class="sai-input-text" />
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
