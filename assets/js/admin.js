/**
 * SiteShield Security — Enterprise UI Controller
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		const cfg = window.SiteShieldAdmin || {};

		// 1. Toast Notification Helper
		function showToast(message, type = 'success') {
			const $toast = $('<div class="siteshield-toast ' + type + '">' + message + '</div>');
			$('body').append($toast);
			$toast.fadeIn(200).delay(2500).fadeOut(300, function() {
				$(this).remove();
			});
		}

		// Inject minimal toast CSS dynamically if not present
		if (!$('#ss-toast-style').length) {
			$('head').append(`
				<style id="ss-toast-style">
					.siteshield-toast {
						position: fixed;
						bottom: 24px;
						right: 24px;
						background: #0F172A;
						color: #FFFFFF;
						padding: 12px 20px;
						border-radius: 8px;
						font-size: 13px;
						font-weight: 600;
						box-shadow: 0 10px 25px rgba(0,0,0,0.15);
						z-index: 999999;
						display: none;
						border-left: 4px solid #0066FF;
					}
					.siteshield-toast.error {
						border-left-color: #0F172A;
					}
				</style>
			`);
		}

		// 2. Generic Settings Form Submission via AJAX
		$(document).on('submit', '.ss-settings-form', function(e) {
			e.preventDefault();
			const $form = $(this);
			const $btn  = $form.find('button[type="submit"]');
			const originalText = $btn.html();

			$btn.prop('disabled', true).text(cfg.strings.saving || 'Salvando...');

			const formData = $form.serializeArray();
			const settings = {};

			// Convert serialized array to object
			formData.forEach(function(item) {
				if (item.name !== 'nonce' && item.name !== '_wp_http_referer') {
					if (settings[item.name]) {
						if (!Array.isArray(settings[item.name])) {
							settings[item.name] = [settings[item.name]];
						}
						settings[item.name].push(item.value);
					} else {
						settings[item.name] = item.value;
					}
				}
			});

			$.post(cfg.ajaxUrl, {
				action: 'siteshield_save_settings',
				nonce: cfg.nonce,
				settings: settings
			}, function(response) {
				$btn.prop('disabled', false).html(originalText);
				if (response.success) {
					showToast(response.data.message || cfg.strings.saved);
				} else {
					showToast(response.data.message || 'Erro ao salvar.', 'error');
				}
			}).fail(function() {
				$btn.prop('disabled', false).html(originalText);
				showToast('Erro de comunicação com o servidor.', 'error');
			});
		});

		// 3. WAF Radio Card Selection
		$(document).on('change', '.waf-radio-card input[type="radio"]', function() {
			$('.waf-radio-card').removeClass('active');
			$(this).closest('.waf-radio-card').addClass('active');
		});

		// 4. Custom Login URL Form & Copying
		$(document).on('submit', '#form-custom-login-url', function(e) {
			e.preventDefault();
			const $btn = $('#btn-save-custom-login');
			const originalText = $btn.html();
			const enabled = $('#login_custom_url_enabled').is(':checked') ? 1 : 0;
			const slug = $('#login_custom_slug').val();

			$btn.prop('disabled', true).text(cfg.strings.saving || 'Salvando...');

			$.post(cfg.ajaxUrl, {
				action: 'siteshield_save_custom_login',
				nonce: cfg.nonce,
				enabled: enabled,
				slug: slug
			}, function(response) {
				$btn.prop('disabled', false).html(originalText);
				if (response.success) {
					showToast(response.data.message);
					if (response.data.login_url) {
						$('#active-login-url-preview').text(response.data.login_url);
					}
				} else {
					showToast(response.data.message, 'error');
				}
			}).fail(function() {
				$btn.prop('disabled', false).html(originalText);
				showToast('Erro de comunicação.', 'error');
			});
		});

		$(document).on('click', '#btn-copy-active-login-url', function() {
			const url = $('#active-login-url-preview').text();
			navigator.clipboard.writeText(url).then(function() {
				showToast(cfg.strings.copied || 'URL copiada!');
			});
		});

		// 5. Malware Scanner (Quick & Full)
		function runScan(type) {
			$('#scanner-progress-box').fadeIn(200);
			$('#scanner-status-text').text(cfg.strings.scanStarting || 'Iniciando varredura heurística...');

			$.post(cfg.ajaxUrl, {
				action: 'siteshield_run_scan',
				nonce: cfg.nonce,
				scan_type: type
			}, function(response) {
				if (response.success) {
					showToast(response.data.message);
					setTimeout(function() {
						location.reload();
					}, 1000);
				} else {
					showToast(response.data.message || 'Erro na varredura.', 'error');
					$('#scanner-progress-box').hide();
				}
			}).fail(function() {
				showToast('Erro ao executar scanner.', 'error');
				$('#scanner-progress-box').hide();
			});
		}

		$(document).on('click', '#btn-run-quick-scan, .btn-trigger-quick-scan', function() {
			runScan('quick');
		});

		$(document).on('click', '#btn-run-full-scan', function() {
			runScan('full');
		});

		// 6. Quarantine File Action
		$(document).on('click', '.btn-quarantine-file', function() {
			const file = $(this).data('file');
			if (!confirm('Deseja realmente isolar este arquivo na quarentena protegida?')) {
				return;
			}
			$.post(cfg.ajaxUrl, {
				action: 'siteshield_quarantine_action',
				nonce: cfg.nonce,
				quarantine_action: 'quarantine',
				file_path: file
			}, function(response) {
				if (response.success) {
					showToast(response.data.message);
					setTimeout(function() { location.reload(); }, 800);
				} else {
					showToast(response.data.message, 'error');
				}
			});
		});

		// Restore from Quarantine
		$(document).on('click', '.btn-restore-file', function() {
			const id = $(this).data('id');
			$.post(cfg.ajaxUrl, {
				action: 'siteshield_quarantine_action',
				nonce: cfg.nonce,
				quarantine_action: 'restore',
				quarantine_id: id
			}, function(response) {
				if (response.success) {
					showToast(response.data.message);
					setTimeout(function() { location.reload(); }, 800);
				} else {
					showToast(response.data.message, 'error');
				}
			});
		});

		// Delete Permanently
		$(document).on('click', '.btn-delete-quarantine', function() {
			const id = $(this).data('id');
			if (!confirm('Excluir permanentemente este arquivo da quarentena? Esta ação não pode ser desfeita.')) {
				return;
			}
			$.post(cfg.ajaxUrl, {
				action: 'siteshield_quarantine_action',
				nonce: cfg.nonce,
				quarantine_action: 'delete',
				quarantine_id: id
			}, function(response) {
				if (response.success) {
					showToast(response.data.message);
					setTimeout(function() { location.reload(); }, 800);
				} else {
					showToast(response.data.message, 'error');
				}
			});
		});

		// 7. File Integrity Actions
		$(document).on('click', '#btn-check-integrity', function() {
			const $btn = $(this);
			$btn.prop('disabled', true);
			$.post(cfg.ajaxUrl, {
				action: 'siteshield_integrity_action',
				nonce: cfg.nonce,
				integrity_action: 'check'
			}, function(response) {
				$btn.prop('disabled', false);
				if (response.success) {
					showToast(response.data.message);
					setTimeout(function() { location.reload(); }, 800);
				}
			});
		});

		$(document).on('click', '#btn-build-baseline', function() {
			const $btn = $(this);
			$btn.prop('disabled', true);
			$.post(cfg.ajaxUrl, {
				action: 'siteshield_integrity_action',
				nonce: cfg.nonce,
				integrity_action: 'build'
			}, function(response) {
				$btn.prop('disabled', false);
				if (response.success) {
					showToast(response.data.message);
					setTimeout(function() { location.reload(); }, 800);
				}
			});
		});

		// 8. Emergency Mode Toggle
		$(document).on('click', '#btn-toggle-emergency', function() {
			if (!confirm('Deseja alternar o Modo de Emergência? Ele ativa restrições máximas contra ataques.')) {
				return;
			}
			$.post(cfg.ajaxUrl, {
				action: 'siteshield_emergency_toggle',
				nonce: cfg.nonce
			}, function(response) {
				if (response.success) {
					showToast(response.data.message);
					setTimeout(function() { location.reload(); }, 800);
				}
			});
		});

		// 9. License Activation
		$(document).on('click', '#btn-activate-license', function(e) {
			e.preventDefault();
			const $btn = $(this);
			const originalText = $btn.html();
			const key = $('#input_license_key').val().trim();

			if (!key) {
				showToast('Por favor, insira uma chave de licença válida.', 'error');
				$('#input_license_key').focus();
				return;
			}

			// Previne duplo clique e atualiza estado visual
			$btn.prop('disabled', true).text('A verificar...');

			$.post(cfg.ajaxUrl, {
				action: 'siteshield_activate_license',
				nonce: cfg.nonce,
				license_key: key
			}, function(response) {
				if (response && response.success) {
					$btn.text('✓ Ativada!');
					showToast(response.data.message || 'Licença ativada com sucesso! Redirecionando...');
					setTimeout(function() {
						window.location.href = window.location.pathname + '?page=siteshield-license&activated=1';
					}, 1200);
				} else {
					$btn.prop('disabled', false).html(originalText);
					const errMsg = (response && response.data && response.data.message) ? response.data.message : 'Chave de licença inválida ou limite atingido.';
					showToast(errMsg, 'error');
				}
			}).fail(function() {
				$btn.prop('disabled', false).html(originalText);
				showToast('Não foi possível conectar ao SiteShield Cloud. Verifique sua conexão com o servidor.', 'error');
			});
		});

		// 10. JSON Backup & Restore
		$(document).on('click', '#btn-export-settings-json', function() {
			$.post(cfg.ajaxUrl, {
				action: 'siteshield_export_backup',
				nonce: cfg.nonce
			}, function(response) {
				if (response.success && response.data.json) {
					$('#export-json-output').val(response.data.json).slideDown(200);
					showToast('Backup JSON gerado com sucesso!');
				}
			});
		});

		$(document).on('click', '#btn-import-settings-json', function() {
			const json = $('#import-json-input').val();
			if (!json) {
				showToast('Cole o conteúdo JSON do backup.', 'error');
				return;
			}
			$.post(cfg.ajaxUrl, {
				action: 'siteshield_import_backup',
				nonce: cfg.nonce,
				json_data: json
			}, function(response) {
				if (response.success) {
					showToast(response.data.message);
					setTimeout(function() { location.reload(); }, 1000);
				} else {
					showToast(response.data.message, 'error');
				}
			});
		});

		// 11. Setup Wizard 7-Step Navigation
		function goToStep(step) {
			$('.wizard-step-pane').hide();
			$('#step-pane-' + step).fadeIn(200);
			$('.siteshield-step-item').removeClass('active completed');

			$('.siteshield-step-item').each(function() {
				const itemStep = parseInt($(this).data('step'), 10);
				if (itemStep === step) {
					$(this).addClass('active');
				} else if (itemStep < step) {
					$(this).addClass('completed');
				}
			});
		}

		$(document).on('click', '.btn-wizard-next', function() {
			const nextStep = parseInt($(this).data('next'), 10);
			const formData = $('#siteshield-wizard-form').serializeArray();
			const settings = {};
			formData.forEach(function(item) {
				settings[item.name] = item.value;
			});

			$.post(cfg.ajaxUrl, {
				action: 'siteshield_save_wizard_step',
				nonce: cfg.nonce,
				step: nextStep,
				settings: settings
			}, function(response) {
				if (response.success) {
					goToStep(nextStep);
				}
			});
		});

		$(document).on('click', '.btn-wizard-prev', function() {
			const prevStep = parseInt($(this).data('prev'), 10);
			goToStep(prevStep);
		});

		// Wizard Step 2: Progressive Security Configuration Animation
		$(document).on('click', '.btn-run-security-setup', function() {
			const $btn = $(this);
			$btn.prop('disabled', true);
			$('#wizard-process-box').fadeIn(200);
			const $log = $('#process-log-list');
			$log.empty();

			const steps = [
				'✓ Checking WordPress environment',
				'✓ Initializing security modules',
				'✓ Configuring login protection',
				'✓ Configuring security headers',
				'✓ Initializing malware scanner',
				'✓ Configuring firewall',
				'✓ Creating security baseline',
				'Security setup completed.'
			];

			let i = 0;
			function logNext() {
				if (i < steps.length) {
					$log.append('<li>' + steps[i] + '</li>');
					i++;
					setTimeout(logNext, 300);
				} else {
					setTimeout(function() {
						goToStep(3);
					}, 500);
				}
			}
			logNext();
		});

		// CSP Toggle
		$(document).on('change', '#headers_csp_mode', function() {
			if ($(this).val() === 'custom') {
				$('#custom-csp-box').slideDown(150);
			} else {
				$('#custom-csp-box').slideUp(150);
			}
		});

		// 12. Quick Language Switcher
		$(document).on('change', '#ss-quick-language-select', function() {
			const locale = $(this).val();
			$.post(cfg.ajaxUrl, {
				action: 'siteshield_change_language',
				nonce: cfg.nonce,
				locale: locale
			}, function(response) {
				if (response.success) {
					showToast(response.data.message || 'Idioma atualizado!');
					setTimeout(function() { location.reload(); }, 600);
				}
			});
		});

		// 13. Re-run Diagnostics
		$(document).on('click', '#btn-re-run-diagnostics', function() {
			const $btn = $(this);
			$btn.prop('disabled', true);
			showToast('Executando diagnósticos profundos de segurança...');
			$.post(cfg.ajaxUrl, {
				action: 'siteshield_run_diagnostics',
				nonce: cfg.nonce
			}, function(response) {
				$btn.prop('disabled', false);
				if (response.success) {
					showToast(response.data.message);
					setTimeout(function() { location.reload(); }, 800);
				}
			});
		});

		// 14. Run Real-Time Protection Self-Test
		$(document).on('click', '#btn-run-self-test', function() {
			const $btn = $(this);
			$btn.prop('disabled', true);
			showToast('Executando autoteste de proteções em tempo real...');

			$.post(cfg.ajaxUrl, {
				action: 'siteshield_run_protection_test',
				nonce: cfg.nonce
			}, function(response) {
				$btn.prop('disabled', false);
				if (response.success && response.data.results) {
					showToast(response.data.message);
					const r = response.data.results;
					let html = '<div class="ss-grid-2">';
					const tests = [r.waf_test, r.login_test, r.rate_limit_test, r.headers_test, r.anti_clone_test, r.integrity_test, r.visitor_test];
					tests.forEach(function(t) {
						if (t) {
							const badgeClass = (t.status === 'PASS') ? 'ss-badge-primary' : 'ss-badge-warning';
							html += '<div style="padding: 12px; border: 1px solid var(--ss-border); border-radius: var(--ss-radius-sm);">';
							html += '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;"><strong>' + t.title + '</strong><span class="ss-badge ' + badgeClass + '">' + t.status + '</span></div>';
							html += '<p style="margin: 0; font-size: 12px; color: #64748B;">' + t.details + '</p>';
							html += '</div>';
						}
					});
					html += '</div>';
					$('#self-test-results-body').html(html);
					$('#self-test-results-box').slideDown(200);
				}
			});
		});

		// 15. Destroy Other Sessions
		$(document).on('click', '#btn-destroy-other-sessions', function() {
			if (!confirm('Deseja encerrar todas as outras sessões ativas?')) {
				return;
			}
			$.post(cfg.ajaxUrl, {
				action: 'siteshield_destroy_other_sessions',
				nonce: cfg.nonce
			}, function(response) {
				if (response.success) {
					showToast(response.data.message);
					setTimeout(function() { location.reload(); }, 800);
				}
			});
		});

		// 16. Add WAF Exception
		$(document).on('click', '#btn-add-waf-exception', function() {
			const val = $('#waf-exc-url').val();
			const reason = $('#waf-exc-reason').val();
			if (!val) {
				showToast('Informe o caminho da rota ou IP.', 'error');
				return;
			}
			$.post(cfg.ajaxUrl, {
				action: 'siteshield_add_waf_exception',
				nonce: cfg.nonce,
				type: 'url',
				value: val,
				reason: reason
			}, function(response) {
				if (response.success) {
					showToast(response.data.message);
					setTimeout(function() { location.reload(); }, 600);
				} else {
					showToast(response.data.message, 'error');
				}
			});
		});

		// 17. Remove WAF Exception
		$(document).on('click', '.btn-remove-waf-exc', function() {
			const id = $(this).data('id');
			$.post(cfg.ajaxUrl, {
				action: 'siteshield_remove_waf_exception',
				nonce: cfg.nonce,
				id: id
			}, function(response) {
				if (response.success) {
					showToast(response.data.message);
					setTimeout(function() { location.reload(); }, 600);
				}
			});
		});

		// 18. Protect Uploads (.htaccess)
		$(document).on('click', '#btn-protect-uploads', function() {
			$.post(cfg.ajaxUrl, {
				action: 'siteshield_protect_uploads',
				nonce: cfg.nonce
			}, function(response) {
				if (response.success) {
					showToast(response.data.message);
					setTimeout(function() { location.reload(); }, 800);
				} else {
					showToast(response.data.message, 'error');
				}
			});
		});
	});
})(jQuery);
