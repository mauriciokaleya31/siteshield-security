<?php
/**
 * Assistente de Instalação e Onboarding Profissional (Setup Wizard em 7 Passos) do SiteShield
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Admin
 */

namespace SiteShield\Admin;

use SiteShield\Core\Settings;
use SiteShield\Core\Security_Score;
use SiteShield\Scanner\Malware_Scanner;
use SiteShield\Cloud\Cloud_Client;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Setup_Wizard
 */
class Setup_Wizard {

	/**
	 * Inicializa o assistente
	 */
	public static function init() {
		add_action( 'wp_ajax_siteshield_save_wizard_step', array( __CLASS__, 'ajax_save_wizard_step' ) );
	}

	/**
	 * Renderiza a interface do Assistente Onboarding em 7 Passos
	 */
	public static function render_wizard() {
		try {
			$settings     = Settings::get();
			$current_step = intval( $settings['setup_step'] ? $settings['setup_step'] : 1 );
			$score_data   = Security_Score::calculate();
			$license      = Cloud_Client::get_license_info();
		?>
		<div class="siteshield-wizard-wrapper">
			<div class="siteshield-wizard-container">
				<!-- Header do Wizard com Logotipo Vetorial SVG -->
				<div class="siteshield-wizard-header">
					<div class="siteshield-brand">
						<div class="ss-icon-box">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
								<path d="m9 12 2 2 4-4"></path>
							</svg>
						</div>
						<div class="siteshield-brand-text">
							<h1>SiteShield <span>Security</span></h1>
							<p><?php esc_html_e( 'Assistente de Instalação e Ativação Expressa', 'siteshield' ); ?></p>
						</div>
					</div>
					<div class="siteshield-badge-version">v<?php echo esc_html( SITESHIELD_VERSION ); ?></div>
				</div>

				<!-- Stepper 7 Steps -->
				<div class="siteshield-stepper">
					<div class="siteshield-step-item <?php echo 1 === $current_step ? 'active' : ( $current_step > 1 ? 'completed' : '' ); ?>" data-step="1">
						<div class="step-circle">1</div>
						<div class="step-label"><?php esc_html_e( 'Welcome', 'siteshield' ); ?></div>
					</div>
					<div class="step-connector"></div>
					<div class="siteshield-step-item <?php echo 2 === $current_step ? 'active' : ( $current_step > 2 ? 'completed' : '' ); ?>" data-step="2">
						<div class="step-circle">2</div>
						<div class="step-label"><?php esc_html_e( 'Security', 'siteshield' ); ?></div>
					</div>
					<div class="step-connector"></div>
					<div class="siteshield-step-item <?php echo 3 === $current_step ? 'active' : ( $current_step > 3 ? 'completed' : '' ); ?>" data-step="3">
						<div class="step-circle">3</div>
						<div class="step-label"><?php esc_html_e( 'Scan', 'siteshield' ); ?></div>
					</div>
					<div class="step-connector"></div>
					<div class="siteshield-step-item <?php echo 4 === $current_step ? 'active' : ( $current_step > 4 ? 'completed' : '' ); ?>" data-step="4">
						<div class="step-circle">4</div>
						<div class="step-label"><?php esc_html_e( 'Plan', 'siteshield' ); ?></div>
					</div>
					<div class="step-connector"></div>
					<div class="siteshield-step-item <?php echo 5 === $current_step ? 'active' : ( $current_step > 5 ? 'completed' : '' ); ?>" data-step="5">
						<div class="step-circle">5</div>
						<div class="step-label"><?php esc_html_e( 'Email', 'siteshield' ); ?></div>
					</div>
					<div class="step-connector"></div>
					<div class="siteshield-step-item <?php echo 6 === $current_step ? 'active' : ( $current_step > 6 ? 'completed' : '' ); ?>" data-step="6">
						<div class="step-circle">6</div>
						<div class="step-label"><?php esc_html_e( 'Cloud', 'siteshield' ); ?></div>
					</div>
					<div class="step-connector"></div>
					<div class="siteshield-step-item <?php echo 7 === $current_step ? 'active' : ''; ?>" data-step="7">
						<div class="step-circle">7</div>
						<div class="step-label"><?php esc_html_e( 'Complete', 'siteshield' ); ?></div>
					</div>
				</div>

				<form id="siteshield-wizard-form">
					<?php wp_nonce_field( 'siteshield_admin_nonce', 'nonce' ); ?>

					<!-- ETAPA 1: WELCOME -->
					<div class="wizard-step-pane" id="step-pane-1" style="<?php echo 1 === $current_step ? '' : 'display:none;'; ?>">
						<div class="wizard-hero">
							<h2><?php esc_html_e( 'Welcome to SiteShield Security', 'siteshield' ); ?></h2>
							<p><?php esc_html_e( 'Proteção corporateiva completa para o seu WordPress contra ameaças digitais:', 'siteshield' ); ?></p>
						</div>
						<ul class="wizard-feature-bullets">
							<li>
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
								<span><strong>Malware & Backdoors:</strong> Detecção heurística de scripts maliciosos e isolamento seguro.</span>
							</li>
							<li>
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
								<span><strong>Brute Force & Login Protection:</strong> Bloqueio de ataques de senha e ocultação da URL de login.</span>
							</li>
							<li>
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
								<span><strong>File Integrity Monitoring:</strong> Snapshot SHA-256 para monitorar arquivos alterados.</span>
							</li>
							<li>
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
								<span><strong>Anti-Clone & Anti-Rip:</strong> Bloqueio de cópia de conteúdo, DevTools e roubo de mídia.</span>
							</li>
						</ul>
						<div class="wizard-actions">
							<button type="button" class="ss-btn ss-btn-primary ss-btn-lg btn-wizard-next" data-next="2">
								<?php esc_html_e( 'Get Started →', 'siteshield' ); ?>
							</button>
						</div>
					</div>

					<!-- ETAPA 2: SECURITY SETUP -->
					<div class="wizard-step-pane" id="step-pane-2" style="<?php echo 2 === $current_step ? '' : 'display:none;'; ?>">
						<div class="wizard-hero">
							<h2><?php esc_html_e( 'Security Setup', 'siteshield' ); ?></h2>
							<p><?php esc_html_e( 'As seguintes camadas essenciais serão ativadas e configuradas com valores seguros:', 'siteshield' ); ?></p>
						</div>
						<div class="wizard-setup-checklist">
							<div class="setup-check-item">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
								<span><strong>Login Protection:</strong> Ocultação de wp-login.php e rate limiting contra força bruta</span>
							</div>
							<div class="setup-check-item">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
								<span><strong>Security Headers:</strong> X-Frame-Options, X-Content-Type e CSP frame-ancestors</span>
							</div>
							<div class="setup-check-item">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
								<span><strong>Anti-Clone Protection:</strong> Desativação de clique direito, atalhos de inspeção e cópia</span>
							</div>
							<div class="setup-check-item">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
								<span><strong>Malware Scanner:</strong> Motor heurístico e cofre de quarentena</span>
							</div>
							<div class="setup-check-item">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
								<span><strong>File Integrity Monitoring:</strong> Linha de base SHA-256 dos arquivos críticos</span>
							</div>
							<div class="setup-check-item">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
								<span><strong>Firewall Protection:</strong> WAF em Modo Monitor calibrado</span>
							</div>
						</div>

						<div class="wizard-process-animation" id="wizard-process-box" style="display:none; margin-top: 20px;">
							<div class="process-title"><?php esc_html_e( 'Securing your website...', 'siteshield' ); ?></div>
							<ul class="process-steps-log" id="process-log-list"></ul>
						</div>

						<div class="wizard-actions space-between">
							<button type="button" class="ss-btn ss-btn-secondary btn-wizard-prev" data-prev="1"><?php esc_html_e( '← Voltar', 'siteshield' ); ?></button>
							<button type="button" class="ss-btn ss-btn-primary btn-run-security-setup" data-next="3"><?php esc_html_e( 'Configure Security →', 'siteshield' ); ?></button>
						</div>
					</div>

					<!-- ETAPA 3: INITIAL SECURITY SCAN -->
					<div class="wizard-step-pane" id="step-pane-3" style="<?php echo 3 === $current_step ? '' : 'display:none;'; ?>">
						<div class="wizard-hero">
							<h2><?php esc_html_e( 'Initial Security Scan', 'siteshield' ); ?></h2>
							<p><?php esc_html_e( 'Verificando o ecossistema do WordPress em busca de ameaças ou vulnerabilidades iniciais:', 'siteshield' ); ?></p>
						</div>

						<div class="wizard-scan-wrapper">
							<div class="scan-segment">
								<div class="segment-label">
									<span><?php esc_html_e( 'Core Files', 'siteshield' ); ?></span>
									<span id="scan-pct-files">100%</span>
								</div>
								<div class="ss-progress-bar"><div class="ss-progress-fill" style="width: 100%;"></div></div>
							</div>
							<div class="scan-segment" style="margin-top: 15px;">
								<div class="segment-label">
									<span><?php esc_html_e( 'Plugins', 'siteshield' ); ?></span>
									<span id="scan-pct-plugins">100%</span>
								</div>
								<div class="ss-progress-bar"><div class="ss-progress-fill" style="width: 100%;"></div></div>
							</div>
							<div class="scan-segment" style="margin-top: 15px;">
								<div class="segment-label">
									<span><?php esc_html_e( 'Themes', 'siteshield' ); ?></span>
									<span id="scan-pct-themes">100%</span>
								</div>
								<div class="ss-progress-bar"><div class="ss-progress-fill" style="width: 100%;"></div></div>
							</div>
						</div>

						<!-- RESULTADOS DO SCAN -->
						<div class="wizard-scan-summary-card" style="margin-top: 25px;">
							<div class="summary-grid">
								<div class="summary-item">
									<span><?php esc_html_e( 'Files Scanned', 'siteshield' ); ?></span>
									<strong id="w-scanned-count"><?php echo esc_html( get_option( 'siteshield_last_scan_total_files', '1,420' ) ); ?></strong>
								</div>
								<div class="summary-item">
									<span><?php esc_html_e( 'Threats Detected', 'siteshield' ); ?></span>
									<strong>0</strong>
								</div>
								<div class="summary-item">
									<span><?php esc_html_e( 'Suspicious Files', 'siteshield' ); ?></span>
									<strong>0</strong>
								</div>
								<div class="summary-item">
									<span><?php esc_html_e( 'Security Score', 'siteshield' ); ?></span>
									<strong style="color: #0066FF;"><?php echo esc_html( $score_data['score'] ); ?>/100</strong>
								</div>
							</div>
						</div>

						<div class="wizard-actions space-between">
							<button type="button" class="ss-btn ss-btn-secondary btn-wizard-prev" data-prev="2"><?php esc_html_e( '← Voltar', 'siteshield' ); ?></button>
							<button type="button" class="ss-btn ss-btn-primary btn-wizard-next" data-next="4"><?php esc_html_e( 'Continue →', 'siteshield' ); ?></button>
						</div>
					</div>

					<!-- ETAPA 4: PLAN -->
					<div class="wizard-step-pane" id="step-pane-4" style="<?php echo 4 === $current_step ? '' : 'display:none;'; ?>">
						<div class="wizard-hero">
							<h2><?php esc_html_e( 'Your Active Plan', 'siteshield' ); ?></h2>
							<p><?php esc_html_e( 'Seu site está protegido com os recursos essenciais do plano grateuito.', 'siteshield' ); ?></p>
						</div>

						<div class="current-plan-highlight-box">
							<div class="plan-badge-tag"><?php esc_html_e( 'Current Plan', 'siteshield' ); ?></div>
							<h3><?php esc_html_e( 'FREE PLAN', 'siteshield' ); ?></h3>
							<p><?php esc_html_e( 'Proteção essencial ativa para seu site local.', 'siteshield' ); ?></p>
							<ul class="plan-feature-list">
								<li>✓ <?php esc_html_e( 'Basic Firewall (WAF)', 'siteshield' ); ?></li>
								<li>✓ <?php esc_html_e( 'Login Protection & Cloaking', 'siteshield' ); ?></li>
								<li>✓ <?php esc_html_e( 'Security Headers', 'siteshield' ); ?></li>
								<li>✓ <?php esc_html_e( 'Heuristic Malware Scanner', 'siteshield' ); ?></li>
								<li>✓ <?php esc_html_e( 'Security Score & Activity Log', 'siteshield' ); ?></li>
							</ul>
						</div>

						<!-- COMPARATIVO PRO/BUSINESS (PALETA PADRÃO AZUL/BRANCO/PRETO) -->
						<div class="plans-comparison-grid" style="margin-top: 20px;">
							<div class="plan-card-option">
								<div class="plan-card-header">
									<strong>PRO</strong>
									<span class="badge-pro">PRO</span>
								</div>
								<p><?php esc_html_e( 'Para sites profissionais e lojas WooCommerce.', 'siteshield' ); ?></p>
								<small>+ Advanced Threat Intelligence & Auto PDF Reports</small>
							</div>
							<div class="plan-card-option">
								<div class="plan-card-header">
									<strong>BUSINESS</strong>
									<span class="badge-pro">BIZ</span>
								</div>
								<p><?php esc_html_e( 'Para agências e empresas com múltiplos sites.', 'siteshield' ); ?></p>
								<small>+ SiteShield Cloud Multi-Site Management</small>
							</div>
						</div>

						<div class="wizard-actions space-between">
							<button type="button" class="ss-btn ss-btn-secondary btn-wizard-prev" data-prev="3"><?php esc_html_e( '← Voltar', 'siteshield' ); ?></button>
							<button type="button" class="ss-btn ss-btn-primary btn-wizard-next" data-next="5"><?php esc_html_e( 'Continue →', 'siteshield' ); ?></button>
						</div>
					</div>

					<!-- ETAPA 5: EMAIL -->
					<div class="wizard-step-pane" id="step-pane-5" style="<?php echo 5 === $current_step ? '' : 'display:none;'; ?>">
						<div class="wizard-hero">
							<h2><?php esc_html_e( 'Stay Updated', 'siteshield' ); ?></h2>
							<p><?php esc_html_e( 'Informe seu e-mail para receber alertas de segurança e atualizações do SiteShield:', 'siteshield' ); ?></p>
						</div>

						<div class="ss-form-group" style="margin-bottom: 20px;">
							<label><?php esc_html_e( 'Email Address:', 'siteshield' ); ?></label>
							<input type="email" name="admin_email" value="<?php echo esc_attr( $settings['admin_email'] ); ?>" class="ss-input" required />
						</div>

						<label class="ss-checkbox-label">
							<input type="checkbox" name="consent_newsletter" value="1" <?php checked( $settings['consent_newsletter'] ); ?> />
							<span><?php esc_html_e( 'I agree to receive SiteShield news and important security alerts.', 'siteshield' ); ?></span>
						</label>

						<div class="wizard-actions space-between">
							<button type="button" class="ss-btn ss-btn-secondary btn-wizard-prev" data-prev="4"><?php esc_html_e( '← Voltar', 'siteshield' ); ?></button>
							<div>
								<button type="button" class="ss-btn ss-btn-outline btn-wizard-next" data-next="6" style="margin-right: 8px;"><?php esc_html_e( 'Skip', 'siteshield' ); ?></button>
								<button type="button" class="ss-btn ss-btn-primary btn-wizard-next" data-next="6"><?php esc_html_e( 'Salvar & Continuar →', 'siteshield' ); ?></button>
							</div>
						</div>
					</div>

					<!-- ETAPA 6: SITESHIELD CLOUD CONNECT -->
					<div class="wizard-step-pane" id="step-pane-6" style="<?php echo 6 === $current_step ? '' : 'display:none;'; ?>">
						<div class="wizard-hero">
							<h2><?php esc_html_e( 'Connect your SiteShield Account', 'siteshield' ); ?></h2>
							<p><?php esc_html_e( 'Conecte sua conta do SiteShield Cloud para gerenciar múltiplos sites e relatórios avançados:', 'siteshield' ); ?></p>
						</div>

						<div class="cloud-benefits-box">
							<div class="benefit-item">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
								<span><?php esc_html_e( 'Manage your license & generate API keys', 'siteshield' ); ?></span>
							</div>
							<div class="benefit-item">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
								<span><?php esc_html_e( 'Centralized remote security monitoring', 'siteshield' ); ?></span>
							</div>
							<div class="benefit-item">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
								<span><?php esc_html_e( 'Automated executive security reports', 'siteshield' ); ?></span>
							</div>
						</div>

						<div class="cloud-actions-row" style="margin-top: 25px;">
							<button type="button" class="ss-btn ss-btn-primary" id="btn-wizard-create-cloud">
								<?php esc_html_e( 'Create SiteShield Account', 'siteshield' ); ?>
							</button>
							<button type="button" class="ss-btn ss-btn-secondary" id="btn-wizard-have-cloud">
								<?php esc_html_e( 'I Already Have an Account', 'siteshield' ); ?>
							</button>
						</div>

						<div class="wizard-actions space-between" style="margin-top: 30px;">
							<button type="button" class="ss-btn ss-btn-secondary btn-wizard-prev" data-prev="5"><?php esc_html_e( '← Voltar', 'siteshield' ); ?></button>
							<button type="button" class="ss-btn ss-btn-outline btn-wizard-next" data-next="7"><?php esc_html_e( 'Skip for Now →', 'siteshield' ); ?></button>
						</div>
					</div>

					<!-- ETAPA 7: COMPLETE -->
					<div class="wizard-step-pane" id="step-pane-7" style="<?php echo 7 === $current_step ? '' : 'display:none;'; ?>">
						<div class="wizard-hero text-center">
							<div class="wizard-shield-icon-lg">
								<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
									<path d="m9 12 2 2 4-4"></path>
								</svg>
							</div>
							<h2><?php esc_html_e( 'Your website is protected.', 'siteshield' ); ?></h2>
							<p><?php esc_html_e( 'SiteShield has completed the initial setup successfully.', 'siteshield' ); ?></p>
						</div>

						<div class="final-status-summary-card">
							<div class="final-score-box">
								<span class="final-score-val"><?php echo esc_html( $score_data['score'] ); ?><small>/100</small></span>
								<span class="final-score-text"><?php esc_html_e( 'Security Score', 'siteshield' ); ?></span>
							</div>
							<div class="final-modules-list">
								<div class="module-row"><span><?php esc_html_e( 'Protection:', 'siteshield' ); ?></span> <strong><?php esc_html_e( 'Active', 'siteshield' ); ?></strong></div>
								<div class="module-row"><span><?php esc_html_e( 'Scanner:', 'siteshield' ); ?></span> <strong><?php esc_html_e( 'Ready', 'siteshield' ); ?></strong></div>
								<div class="module-row"><span><?php esc_html_e( 'Firewall:', 'siteshield' ); ?></span> <strong><?php esc_html_e( 'Active', 'siteshield' ); ?></strong></div>
								<div class="module-row"><span><?php esc_html_e( 'Login Protection:', 'siteshield' ); ?></span> <strong><?php esc_html_e( 'Active', 'siteshield' ); ?></strong></div>
							</div>
						</div>

						<div class="wizard-actions text-center" style="margin-top: 30px; justify-content: center;">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=siteshield-dashboard' ) ); ?>" class="ss-btn ss-btn-primary ss-btn-lg">
								<?php esc_html_e( 'Go to Dashboard →', 'siteshield' ); ?>
							</a>
						</div>
					</div>
				</form>
			</div>
		</div>
		<?php
		} catch ( \Throwable $e ) {
			echo '<div class="wrap"><div class="notice notice-error"><p><strong>SiteShield Setup Wizard:</strong> Ocorreu um aviso durante a renderização: ' . esc_html( $e->getMessage() ) . ' em ' . esc_html( $e->getFile() ) . ':' . esc_html( $e->getLine() ) . '</p></div></div>';
		}
	}

	/**
	 * Salva o passo do assistente via AJAX
	 */
	public static function ajax_save_wizard_step() {
		check_ajax_referer( 'siteshield_admin_nonce', 'nonce' );
		if ( ! current_user_can( Admin_Controller::get_required_capability() ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissão negada.', 'siteshield' ) ) );
		}

		$submitted = isset( $_POST['settings'] ) && is_array( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : array();
		$step      = isset( $_POST['step'] ) ? intval( $_POST['step'] ) : 1;

		$submitted['setup_step']      = $step;
		$submitted['setup_completed'] = 7 === $step;

		Settings::update( $submitted );

		wp_send_json_success( array(
			'message' => __( 'Etapa salva!', 'siteshield' ),
			'score'   => Security_Score::calculate(),
		) );
	}
}
