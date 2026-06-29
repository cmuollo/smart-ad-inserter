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

		// 2. AGGIORNAMENTO DINAMICO DEI CAMPI CONDIZIONALI
		function updateAllConditionalRenderings() {
			const contextsList = ['global', 'home', 'single', 'archive'];
			contextsList.forEach(function(ctx) {
				['masthead', 'footer'].forEach(function(pos) {
					const prefix = 'contexts.' + ctx + '.positions.' + pos + '.';

					// A. Gestione visualizzazione container di override
					if (ctx !== 'global') {
						const useGlobalCheckbox = document.getElementById(mapping[prefix + 'use_global_config']);
						const overrideContainer = document.getElementById('sai-' + ctx + '-' + pos + '-override-container');
						if (useGlobalCheckbox && overrideContainer) {
							if (useGlobalCheckbox.checked) {
								overrideContainer.classList.add('sai-hidden');
							} else {
								overrideContainer.classList.remove('sai-hidden');
							}
						}
					}

					// B. Gestione readonly del selettore CSS personalizzato
					const defaultPlacementCheckbox = document.getElementById(mapping[prefix + 'use_default_placement']);
					const customSelectorInput = document.getElementById(mapping[prefix + 'custom_selector']);

					if (defaultPlacementCheckbox && customSelectorInput) {
						if (defaultPlacementCheckbox.checked) {
							customSelectorInput.readOnly = true;
							customSelectorInput.classList.add('sai-input-readonly');
						} else {
							customSelectorInput.readOnly = false;
							customSelectorInput.classList.remove('sai-input-readonly');
						}
					}
				});
			});
		}

		// Associa gli ascoltatori agli eventi di cambio stato
		function bindChangeListeners() {
			const contextsList = ['global', 'home', 'single', 'archive'];
			contextsList.forEach(function(ctx) {
				['masthead', 'footer'].forEach(function(pos) {
					const prefix = 'contexts.' + ctx + '.positions.' + pos + '.';

					// Toggle ereditarietà globale
					if (ctx !== 'global') {
						const useGlobalCheckbox = document.getElementById(mapping[prefix + 'use_global_config']);
						if (useGlobalCheckbox) {
							useGlobalCheckbox.addEventListener('change', updateAllConditionalRenderings);
						}
					}

					// Toggle posizionamento di default
					const defaultPlacementCheckbox = document.getElementById(mapping[prefix + 'use_default_placement']);
					if (defaultPlacementCheckbox) {
						defaultPlacementCheckbox.addEventListener('change', updateAllConditionalRenderings);
					}
				});
			});
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
				updateAllConditionalRenderings();
				bindChangeListeners();
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

			// Contexts
			if (data.contexts) {
				Object.keys(data.contexts).forEach(function(ctx) {
					const contextData = data.contexts[ctx];
					if (contextData && contextData.positions) {
						Object.keys(contextData.positions).forEach(function(pos) {
							const positionData = contextData.positions[pos];
							Object.keys(positionData).forEach(function(key) {
								// Gestione specifica per i controlli radio della posizione footer
								if (key === 'footer_position') {
									const radioBefore = document.getElementById('sai-' + ctx + '-footer-position-before');
									const radioAfter = document.getElementById('sai-' + ctx + '-footer-position-after');
									if (positionData[key] === 'after_footer') {
										if (radioAfter) radioAfter.checked = true;
									} else {
										if (radioBefore) radioBefore.checked = true;
									}
									return;
								}

								const fieldId = mapping['contexts.' + ctx + '.positions.' + pos + '.' + key];
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
				contexts: {}
			};

			const contexts = {
				global: ['masthead', 'footer', 'sidebar_top', 'sidebar_sticky'],
				home: ['masthead', 'footer', 'grid_home'],
				single: ['masthead', 'footer', 'atf', 'btf'],
				archive: ['masthead', 'footer', 'grid_archive']
			};

			Object.keys(contexts).forEach(function(ctx) {
				data.contexts[ctx] = { positions: {} };

				contexts[ctx].forEach(function(pos) {
					const prefix = 'contexts.' + ctx + '.positions.' + pos + '.';
					const domPrefix = 'sai-' + ctx + '-' + pos.replace(/_/g, '-') + '-';
					data.contexts[ctx].positions[pos] = {};

					// active
					const activeEl = document.getElementById(mapping[prefix + 'active']);
					data.contexts[ctx].positions[pos].active = activeEl ? activeEl.checked : false;

					// min_height_desktop
					const desktopHeightEl = document.getElementById(mapping[prefix + 'min_height_desktop']);
					data.contexts[ctx].positions[pos].min_height_desktop = desktopHeightEl ? parseInt(desktopHeightEl.value, 10) || 0 : 0;

					// min_height_mobile
					const mobileHeightEl = document.getElementById(mapping[prefix + 'min_height_mobile']);
					data.contexts[ctx].positions[pos].min_height_mobile = mobileHeightEl ? parseInt(mobileHeightEl.value, 10) || 0 : 0;

					// code
					const codeEl = document.getElementById(mapping[prefix + 'code']);
					data.contexts[ctx].positions[pos].code = codeEl ? codeEl.value : '';

					// override_css
					const overrideEl = document.getElementById(mapping[prefix + 'override_css']);
					data.contexts[ctx].positions[pos].override_css = overrideEl ? overrideEl.value : '';

					// custom_selector
					const selectorEl = document.getElementById(mapping[prefix + 'custom_selector']);
					data.contexts[ctx].positions[pos].custom_selector = selectorEl ? selectorEl.value : '';

					// use_default_placement (masthead e footer)
					if (pos === 'masthead' || pos === 'footer') {
						const defPlacementEl = document.getElementById(mapping[prefix + 'use_default_placement']);
						data.contexts[ctx].positions[pos].use_default_placement = defPlacementEl ? defPlacementEl.checked : true;
					}

					// footer_position (solo footer)
					if (pos === 'footer') {
						const radioBefore = document.getElementById(domPrefix + 'position-before');
						const radioAfter = document.getElementById(domPrefix + 'position-after');
						if (radioAfter && radioAfter.checked) {
							data.contexts[ctx].positions[pos].footer_position = 'after_footer';
						} else {
							data.contexts[ctx].positions[pos].footer_position = 'before_footer';
						}
					}

					// target_element e frequency (solo griglie)
					if (pos === 'grid_home' || pos === 'grid_archive') {
						const targetEl = document.getElementById(mapping[prefix + 'target_element']);
						data.contexts[ctx].positions[pos].target_element = targetEl ? targetEl.value : '';

						const freqEl = document.getElementById(mapping[prefix + 'frequency']);
						data.contexts[ctx].positions[pos].frequency = freqEl ? parseInt(freqEl.value, 10) || 0 : 0;
					}

					// use_global_config (tutte le tab tranne global e solo masthead/footer)
					if (ctx !== 'global' && (pos === 'masthead' || pos === 'footer')) {
						const useGlobalEl = document.getElementById(mapping[prefix + 'use_global_config']);
						data.contexts[ctx].positions[pos].use_global_config = useGlobalEl ? useGlobalEl.checked : true;
					}
				});
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

			const contexts = {
				global: ['masthead', 'footer', 'sidebar_top', 'sidebar_sticky'],
				home: ['masthead', 'footer', 'grid_home'],
				single: ['masthead', 'footer', 'atf', 'btf'],
				archive: ['masthead', 'footer', 'grid_archive']
			};

			Object.keys(contexts).forEach(function(ctx) {
				contexts[ctx].forEach(function(pos) {
					const prefix = 'contexts.' + ctx + '.positions.' + pos + '.';
					const domPrefix = 'sai-' + ctx + '-' + pos.replace(/_/g, '-') + '-';

					map[prefix + 'active'] = domPrefix + 'active';
					map[prefix + 'min_height_desktop'] = domPrefix + 'desktop-height';
					map[prefix + 'min_height_mobile'] = domPrefix + 'mobile-height';
					map[prefix + 'code'] = domPrefix + 'code';
					map[prefix + 'override_css'] = domPrefix + 'override-css';
					map[prefix + 'custom_selector'] = domPrefix + 'css-selector';

					if (pos === 'masthead' || pos === 'footer') {
						map[prefix + 'use_default_placement'] = domPrefix + 'use-default-placement';
					}
					if (pos === 'footer') {
						map[prefix + 'footer_position'] = domPrefix + 'position';
					}
					if (pos === 'grid_home' || pos === 'grid_archive') {
						map[prefix + 'target_element'] = domPrefix + 'target-element';
						map[prefix + 'frequency'] = domPrefix + 'frequency';
					}
					if (ctx !== 'global' && (pos === 'masthead' || pos === 'footer')) {
						map[prefix + 'use_global_config'] = domPrefix + 'use-global-config';
					}
				});
			});

			return map;
		}

		// Carica i dati iniziali all'avvio
		loadSettings();
	});
})();
