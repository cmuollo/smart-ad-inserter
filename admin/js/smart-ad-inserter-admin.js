/**
 * Logica JavaScript per il pannello di controllo di Smart Ad Inserter.
 *
 * Gestisce la navigazione tra le schede, la visualizzazione condizionale per il Masthead,
 * l'interazione asincrona con le API REST di WordPress (GET/POST), le notifiche di feedback
 * e la prevenzione della chiusura involontaria in caso di modifiche non salvate.
 *
 * @since             1.0.0
 * @package           Smart_Ad_Inserter
 * @subpackage        Smart_Ad_Inserter/admin/js
 * @author            Carmine Muollo
 */
(function() {
	'use strict';

	document.addEventListener('DOMContentLoaded', function() {
		let isDirty = false;
		let justSaved = false;

		const form = document.getElementById('sai-settings-form');
		const saveBtn = document.getElementById('sai-save-btn');
		const spinner = document.getElementById('sai-spinner');
		const feedback = document.getElementById('sai-feedback');
		const mastheadCheckbox = document.getElementById('sai-use-default-placement');
		const mastheadSelectorInput = document.getElementById('sai-css-selector');
		const footerCheckbox = document.getElementById('sai-footer-use-default-placement');
		const footerSelectorInput = document.getElementById('sai-footer-css-selector');

		// Mappatura delle proprietà del modello dati con gli ID del DOM
		const mapping = getFieldIdMapping();

		// 1. GESTIONE NAVIGAZIONE SCHEDE (TAB SWITCHING)
		const tabs = document.querySelectorAll('.sai-tab');
		tabs.forEach(function(tab) {
			tab.addEventListener('click', function() {
				// Rimuove la classe attiva da tutti i tab
				tabs.forEach(function(t) {
					t.classList.remove('active');
				});
				// Attiva il tab cliccato
				tab.classList.add('active');

				// Nasconde tutti i contenuti dei tab
				const contents = document.querySelectorAll('.sai-tab-content');
				contents.forEach(function(c) {
					c.classList.remove('active');
				});

				// Mostra il contenuto del tab di destinazione
				const targetId = tab.getAttribute('data-tab');
				const targetContent = document.getElementById(targetId);
				if (targetContent) {
					targetContent.classList.add('active');
				}
			});
		});

		// 2. RENDERING CONDIZIONALE MASTHEAD
		function updateMastheadVisibility() {
			if (mastheadCheckbox && mastheadSelectorInput) {
				if (mastheadCheckbox.checked) {
					mastheadSelectorInput.readOnly = true;
					mastheadSelectorInput.classList.add('sai-input-readonly');
				} else {
					mastheadSelectorInput.readOnly = false;
					mastheadSelectorInput.classList.remove('sai-input-readonly');
				}
			}
		}

		if (mastheadCheckbox) {
			mastheadCheckbox.addEventListener('change', updateMastheadVisibility);
		}

		// 2b. RENDERING CONDIZIONALE FOOTER
		function updateFooterVisibility() {
			if (footerCheckbox && footerSelectorInput) {
				if (footerCheckbox.checked) {
					footerSelectorInput.readOnly = true;
					footerSelectorInput.classList.add('sai-input-readonly');
				} else {
					footerSelectorInput.readOnly = false;
					footerSelectorInput.classList.remove('sai-input-readonly');
				}
			}
		}

		if (footerCheckbox) {
			footerCheckbox.addEventListener('change', updateFooterVisibility);
		}

		// 3. CARICAMENTO DELLE IMPOSTAZIONI DAL SERVER
		function loadSettings() {
			if (typeof smartAdInserter === 'undefined' || !smartAdInserter.restUrl) {
				showFeedback('Errore: API REST non localizzate correttamente.', 'error');
				return;
			}

			showSpinner(true);
			fetch(smartAdInserter.restUrl + 'settings', {
				method: 'GET',
				headers: {
					'X-WP-Nonce': smartAdInserter.nonce
				}
			})
			.then(function(response) {
				if (response.ok) {
					return response.json();
				} else {
					throw new Error('Impossibile caricare le impostazioni.');
				}
			})
			.then(function(data) {
				populateFields(data);
				updateMastheadVisibility();
				updateFooterVisibility();
				showSpinner(false);
			})
			.catch(function(error) {
				showSpinner(false);
				showFeedback(error.message, 'error');
			});
		}

		// Popola i campi del modulo con i valori ricevuti dall'API REST
		function populateFields(data) {
			if (!data) return;

			// Scripts Globali
			const globalScriptsEl = document.getElementById(mapping['global_scripts']);
			if (globalScriptsEl) {
				globalScriptsEl.value = data.global_scripts || '';
			}

			// Posizioni
			if (data.positions) {
				Object.keys(data.positions).forEach(function(pos) {
					const positionData = data.positions[pos];
					Object.keys(positionData).forEach(function(key) {
						const fieldId = mapping['positions.' + pos + '.' + key];
						const fieldEl = document.getElementById(fieldId);
						if (!fieldEl) return;

						if (fieldEl.type === 'checkbox') {
							fieldEl.checked = !!positionData[key];
						} else {
							fieldEl.value = positionData[key] !== null ? positionData[key] : '';
						}
					});
				});
			}
		}

		// 4. SALVATAGGIO ASINCRONO DELLE IMPOSTAZIONI
		if (form) {
			form.addEventListener('submit', function(e) {
				e.preventDefault();

				if (typeof smartAdInserter === 'undefined' || !smartAdInserter.restUrl) {
					showFeedback('Errore: Parametri API mancanti.', 'error');
					return;
				}

				// Disabilita pulsante e mostra spinner
				saveBtn.disabled = true;
				showSpinner(true);
				hideFeedback();

				const payload = collectFormData();

				fetch(smartAdInserter.restUrl + 'settings', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': smartAdInserter.nonce
					},
					body: JSON.stringify(payload)
				})
				.then(function(response) {
					if (response.ok) {
						return response.json();
					} else {
						if (response.status === 401) {
							throw new Error('Errore 401 (Non autorizzato): Sessione scaduta. Ricarica la pagina.');
						} else if (response.status === 403) {
							throw new Error('Errore 403 (Vietato): Permessi amministrativi insufficienti per questa operazione.');
						} else if (response.status === 422) {
							throw new Error('Errore 422 (Dati non validi): Alcuni campi presentano un formato non consentito.');
						} else {
							throw new Error('Errore ' + response.status + ': Impossibile completare il salvataggio.');
						}
					}
				})
				.then(function(data) {
					showSpinner(false);
					saveBtn.disabled = false;

					if (data && data.success) {
						showFeedback('Impostazioni salvate con successo!', 'success');
						isDirty = false;
						justSaved = true;
					} else {
						showFeedback('Errore durante il salvataggio dei dati sul database.', 'error');
					}
				})
				.catch(function(error) {
					showSpinner(false);
					saveBtn.disabled = false;
					showFeedback(error.message, 'error');
				});
			});

			// 5. RILEVAMENTO MODIFICHE NON SALVATE (UNSAVED GUARD)
			form.addEventListener('input', function() {
				isDirty = true;
				justSaved = false;
			});
		}

		window.addEventListener('beforeunload', function(e) {
			if (isDirty && !justSaved) {
				const msg = 'Ci sono modifiche non salvate. Sei sicuro di voler lasciare la pagina?';
				e.preventDefault();
				e.returnValue = msg;
				return msg;
			}
		});

		// --- FUNZIONI DI UTILITÀ ---

		// Raccoglie i dati compilati nel modulo in un oggetto JSON strutturato
		function collectFormData() {
			const data = {
				global_scripts: document.getElementById(mapping['global_scripts']).value,
				positions: {}
			};

			const positions = ['atf', 'btf', 'masthead', 'footer', 'sidebar_top', 'sidebar_sticky', 'grid_home', 'grid_archive'];
			positions.forEach(function(pos) {
				data.positions[pos] = {};

				// active
				const activeEl = document.getElementById(mapping['positions.' + pos + '.active']);
				data.positions[pos].active = activeEl ? activeEl.checked : false;

				// min_height_desktop
				const desktopHeightEl = document.getElementById(mapping['positions.' + pos + '.min_height_desktop']);
				data.positions[pos].min_height_desktop = desktopHeightEl ? parseInt(desktopHeightEl.value, 10) || 0 : 0;

				// min_height_mobile
				const mobileHeightEl = document.getElementById(mapping['positions.' + pos + '.min_height_mobile']);
				data.positions[pos].min_height_mobile = mobileHeightEl ? parseInt(mobileHeightEl.value, 10) || 0 : 0;

				// code
				const codeEl = document.getElementById(mapping['positions.' + pos + '.code']);
				data.positions[pos].code = codeEl ? codeEl.value : '';

				// override_css
				const overrideEl = document.getElementById(mapping['positions.' + pos + '.override_css']);
				data.positions[pos].override_css = overrideEl ? overrideEl.value : '';

				// custom_selector
				const selectorEl = document.getElementById(mapping['positions.' + pos + '.custom_selector']);
				data.positions[pos].custom_selector = selectorEl ? selectorEl.value : '';

				// use_default_placement (masthead e footer)
				if (pos === 'masthead' || pos === 'footer') {
					const defPlacementEl = document.getElementById(mapping['positions.' + pos + '.use_default_placement']);
					data.positions[pos].use_default_placement = defPlacementEl ? defPlacementEl.checked : true;
				}

				// footer_position (solo footer)
				if (pos === 'footer') {
					const footerPosEl = document.getElementById(mapping['positions.footer.footer_position']);
					data.positions[pos].footer_position = footerPosEl ? footerPosEl.value : 'before_footer';
				}

				// target_element e frequency (solo griglie)
				if (pos === 'grid_home' || pos === 'grid_archive') {
					const targetEl = document.getElementById(mapping['positions.' + pos + '.target_element']);
					data.positions[pos].target_element = targetEl ? targetEl.value : '';

					const freqEl = document.getElementById(mapping['positions.' + pos + '.frequency']);
					data.positions[pos].frequency = freqEl ? parseInt(freqEl.value, 10) || 0 : 0;
				}
			});

			return data;
		}

		// Mostra/Nasconde lo spinner di caricamento
		function showSpinner(show) {
			if (spinner) {
				if (show) {
					spinner.classList.add('sai-show');
				} else {
					spinner.classList.remove('sai-show');
				}
			}
		}

		// Mostra il feedback all'utente (Successo/Errore)
		function showFeedback(message, type) {
			if (feedback) {
				feedback.className = 'sai-feedback ' + type + ' sai-show';
				feedback.textContent = message;
			}
		}

		// Nasconde il box di feedback
		function hideFeedback() {
			if (feedback) {
				feedback.className = 'sai-feedback';
				feedback.textContent = '';
			}
		}

		// Ritorna la mappa delle relazioni chiave-ID per la serializzazione
		function getFieldIdMapping() {
			const map = {};
			map['global_scripts'] = 'sai-head-scripts';

			const positions = ['atf', 'btf', 'masthead', 'footer', 'sidebar_top', 'sidebar_sticky', 'grid_home', 'grid_archive'];
			positions.forEach(function(pos) {
				map['positions.' + pos + '.active'] = 'sai-' + pos.replace('_', '-') + '-active';
				map['positions.' + pos + '.min_height_desktop'] = 'sai-' + pos.replace('_', '-') + '-desktop-height';
				map['positions.' + pos + '.min_height_mobile'] = 'sai-' + pos.replace('_', '-') + '-mobile-height';

				if (pos === 'masthead') {
					map['positions.masthead.code'] = 'sai-banner-code';
					map['positions.masthead.override_css'] = 'sai-override-css';
					map['positions.masthead.custom_selector'] = 'sai-css-selector';
					map['positions.masthead.use_default_placement'] = 'sai-use-default-placement';
				} else if (pos === 'footer') {
					map['positions.footer.code'] = 'sai-footer-code';
					map['positions.footer.override_css'] = 'sai-footer-override-css';
					map['positions.footer.custom_selector'] = 'sai-footer-css-selector';
					map['positions.footer.use_default_placement'] = 'sai-footer-use-default-placement';
					map['positions.footer.footer_position'] = 'sai-footer-position';
				} else if (pos === 'sidebar_top' || pos === 'sidebar_sticky') {
					map['positions.' + pos + '.code'] = 'sai-' + pos.replace('_', '-') + '-code';
					map['positions.' + pos + '.override_css'] = 'sai-' + pos.replace('_', '-') + '-override-css';
					map['positions.' + pos + '.custom_selector'] = 'sai-' + pos.replace('_', '-') + '-selector';
				} else {
					map['positions.' + pos + '.code'] = 'sai-' + pos.replace('_', '-') + '-banner-code';
					map['positions.' + pos + '.override_css'] = 'sai-' + pos.replace('_', '-') + '-override-css';
					map['positions.' + pos + '.custom_selector'] = 'sai-' + pos.replace('_', '-') + '-custom-selector';
				}

				if (pos === 'grid_home' || pos === 'grid_archive') {
					map['positions.' + pos + '.target_element'] = 'sai-' + pos.replace('_', '-') + '-target-element';
					map['positions.' + pos + '.frequency'] = 'sai-' + pos.replace('_', '-') + '-frequency';
				}
			});

			return map;
		}

		// Carica i dati iniziali all'avvio
		loadSettings();
	});
})();
