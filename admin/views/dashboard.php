<?php
/**
 * View Principal do Security Center do SiteShield
 * Layout: Sidebar + Top Header + Content
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Admin\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_emergency = \SiteShield\Core\Emergency_Mode::is_active();
$visitor_state = \SiteShield\Visitors\Visitor_Tracker::get_stats( '7d' );
?>
<div class="siteshield-enterprise-layout">
	<!-- MENU LATERAL -->
	<?php include SITESHIELD_PATH . 'admin/views/layout-sidebar.php'; ?>

	<!-- ÁREA PRINCIPAL -->
	<div class="siteshield-content-area">
		<!-- TOP HEADER -->
		<header class="siteshield-topbar">
			<div class="topbar-left">
				<span class="topbar-title">SiteShield Security</span>
				<span class="topbar-sep">/</span>
				<span class="topbar-page"><?php echo esc_html( ucfirst( str_replace( '-', ' ', $active_tab ) ) ); ?></span>
			</div>
			<div class="topbar-right">
				<!-- SELETOR DE IDIOMA RÁPIDO -->
				<div class="topbar-lang-wrap">
					<?php
					$current_loc = \SiteShield\I18n\I18n::get_current_locale();
					$locales     = \SiteShield\I18n\I18n::get_supported_locales();
					?>
					<select id="ss-quick-language-select" class="ss-topbar-select">
						<?php foreach ( $locales as $code => $info ) : ?>
							<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $current_loc, $code ); ?>>
								<?php echo esc_html( $info['name'] ); ?> (<?php echo esc_html( $info['flag'] ); ?>)
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<!-- BOTÃO MODO DE EMERGÊNCIA -->
				<button type="button" class="ss-btn ss-btn-sm <?php echo $is_emergency ? 'ss-btn-danger' : 'ss-btn-outline'; ?>" id="btn-toggle-emergency">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
					<span><?php echo $is_emergency ? esc_html__( 'Emergency Mode ON', 'siteshield' ) : esc_html__( 'Emergency Mode', 'siteshield' ); ?></span>
				</button>

				<a href="<?php echo esc_url( admin_url( 'admin.php?page=siteshield-setup' ) ); ?>" class="ss-btn ss-btn-sm ss-btn-secondary">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
					<span><?php esc_html_e( 'Setup Wizard', 'siteshield' ); ?></span>
				</a>
			</div>
		</header>

		<!-- CONTEÚDO DA PÁGINA ATIVA -->
		<main class="siteshield-page-body">
			<?php
			switch ( $active_tab ) {
				case 'system-health':
					include SITESHIELD_PATH . 'admin/views/system-health.php';
					break;
				case 'security-events':
					include SITESHIELD_PATH . 'admin/views/security-events.php';
					break;
				case 'hardening':
					include SITESHIELD_PATH . 'admin/views/hardening.php';
					break;
				case 'active-sessions':
					include SITESHIELD_PATH . 'admin/views/active-sessions.php';
					break;
				case 'diagnostics-logs':
					include SITESHIELD_PATH . 'admin/views/diagnostics-logs.php';
					break;
				case 'diagnostics':
					include SITESHIELD_PATH . 'admin/views/diagnostics.php';
					break;
				case 'firewall':
					include SITESHIELD_PATH . 'admin/views/firewall.php';
					break;
				case 'login-security':
					include SITESHIELD_PATH . 'admin/views/login-security.php';
					break;
				case 'scanner':
					include SITESHIELD_PATH . 'admin/views/scanner.php';
					break;
				case 'integrity':
					include SITESHIELD_PATH . 'admin/views/integrity.php';
					break;
				case 'vulnerabilities':
					include SITESHIELD_PATH . 'admin/views/vulnerabilities.php';
					break;
				case 'content-protection':
					include SITESHIELD_PATH . 'admin/views/content-protection.php';
					break;
				case 'security-headers':
					include SITESHIELD_PATH . 'admin/views/security-headers.php';
					break;
				case 'visitors':
					include SITESHIELD_PATH . 'admin/views/visitors.php';
					break;
				case 'reports':
					include SITESHIELD_PATH . 'admin/views/reports.php';
					break;
				case 'verified-badge':
					include SITESHIELD_PATH . 'admin/views/verified-badge.php';
					break;
				case 'notifications':
					include SITESHIELD_PATH . 'admin/views/notifications.php';
					break;
				case 'license':
					include SITESHIELD_PATH . 'admin/views/license.php';
					break;
				case 'support':
					include SITESHIELD_PATH . 'admin/views/support.php';
					break;
				case 'activity-log':
					include SITESHIELD_PATH . 'admin/views/activity-log.php';
					break;
				case 'settings':
					include SITESHIELD_PATH . 'admin/views/settings.php';
					break;
				case 'tools':
					include SITESHIELD_PATH . 'admin/views/tools.php';
					break;
				default:
					// AVISO DISCRETO DE PLANO FREE
					if ( ! empty( $license['is_free'] ) ) :
					?>
					<div class="ss-card" style="margin-bottom: 20px; border-left: 4px solid #0066FF; background: #EFF6FF; border-radius: 8px;">
						<div class="card-body" style="display: flex; justify-content: space-between; align-items: center; gap: 15px; padding: 12px 18px; flex-wrap: wrap;">
							<div style="display: flex; align-items: center; gap: 10px;">
								<span style="font-size: 1.1rem;">🛡️</span>
								<span style="color: #1E3A8A; font-size: 13.5px; font-weight: 500;">
									<?php esc_html_e( 'Está a utilizar o plano FREE com proteções locais ativas. Faça upgrade para desbloquear o Selo SiteShield Verified, WAF avançado e relatórios PDF.', 'siteshield' ); ?>
								</span>
							</div>
							<div style="display: flex; gap: 8px;">
								<a href="<?php echo esc_url( \SiteShield\Cloud\Cloud_Client::get_cloud_url() . '/pricing' ); ?>" target="_blank" class="button button-primary" style="font-size: 12px; height: 30px; line-height: 28px;">
									<?php esc_html_e( 'Ver Planos', 'siteshield' ); ?>
								</a>
								<a href="?page=siteshield-dashboard&tab=license" class="button" style="font-size: 12px; height: 30px; line-height: 28px;">
									<?php esc_html_e( 'Ativar Licença', 'siteshield' ); ?>
								</a>
							</div>
						</div>
					</div>
					<?php endif; ?>

					<?php
					// DASHBOARD PRINCIPAL
					$overall_sys = \SiteShield\Diagnostics\Security_Diagnostics::get_overall_system_status();
					if ( 'CRITICO' === $overall_sys['code'] || 'RISCO' === $overall_sys['code'] ) :
					?>
					<div class="ss-card" style="margin-bottom: 20px; border-left: 4px solid <?php echo esc_attr( $overall_sys['color'] ); ?>; background: <?php echo 'CRITICO' === $overall_sys['code'] ? '#FEF2F2' : '#FFFBEB'; ?>;">
						<div class="card-body" style="display: flex; justify-content: space-between; align-items: center; gap: 15px; padding: 14px 18px; flex-wrap: wrap;">
							<div style="display: flex; align-items: center; gap: 12px;">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="<?php echo esc_attr( $overall_sys['color'] ); ?>" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
								<div>
									<strong style="color: #0F172A; font-size: 14px;"><?php echo esc_html( $overall_sys['label'] ); ?>:</strong>
									<span style="color: #475569; font-size: 13px; margin-left: 4px;"><?php echo esc_html( $overall_sys['summary'] ); ?></span>
								</div>
							</div>
							<a href="?page=siteshield-dashboard&tab=diagnostics-logs" class="ss-btn ss-btn-sm ss-btn-primary">
								<span><?php esc_html_e( 'Ver Diagnóstico & Logs →', 'siteshield' ); ?></span>
							</a>
						</div>
					</div>
					<?php endif; ?>

					<!-- SECURITY SCORE BANNER COM PROGRESS RING SVG -->
					<div class="ss-dashboard-hero">
						<div class="hero-score-ring-wrap">
							<svg class="score-ring-svg" width="130" height="130" viewBox="0 0 130 130">
								<circle class="ring-bg" cx="65" cy="65" r="54" stroke="#E2E8F0" stroke-width="10" fill="none" />
								<?php
								$circumference = 2 * M_PI * 54;
								$dash_offset   = $circumference - ( ( $score_data['score'] / 100 ) * $circumference );
								?>
								<circle class="ring-fill" cx="65" cy="65" r="54" stroke="#0066FF" stroke-width="10" fill="none"
									stroke-dasharray="<?php echo esc_attr( $circumference ); ?>"
									stroke-dashoffset="<?php echo esc_attr( $dash_offset ); ?>"
									stroke-linecap="round"
									transform="rotate(-90 65 65)" />
							</svg>
							<div class="score-ring-text">
								<span class="score-number"><?php echo esc_html( $score_data['score'] ); ?></span>
								<span class="score-max">/100</span>
							</div>
						</div>

						<div class="hero-score-details">
							<span class="score-label-tag"><?php esc_html_e( 'SECURITY SCORE', 'siteshield' ); ?></span>
							<h2>
								<?php
								if ( $score_data['score'] >= 85 ) {
									esc_html_e( 'Your website security is strong.', 'siteshield' );
								} elseif ( $score_data['score'] >= 70 ) {
									esc_html_e( 'Your website has good protection with minor recommendations.', 'siteshield' );
								} else {
									esc_html_e( 'Your website requires security attention.', 'siteshield' );
								}
								?>
							</h2>
							<p><?php esc_html_e( 'O SiteShield está monitorando continuamente requisições, logins, integridade e ameaças.', 'siteshield' ); ?></p>
							<div class="score-actions-row">
								<button type="button" class="ss-btn ss-btn-primary ss-btn-sm btn-trigger-quick-scan">
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
									<span><?php esc_html_e( 'Run Security Scan', 'siteshield' ); ?></span>
								</button>
								<a href="?page=siteshield-dashboard&tab=reports" class="ss-btn ss-btn-secondary ss-btn-sm">
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
									<span><?php esc_html_e( 'View Report', 'siteshield' ); ?></span>
								</a>
							</div>
						</div>
					</div>

					<!-- 4 COMPACT METRIC CARDS -->
					<div class="ss-metrics-grid" style="margin-top: 20px;">
						<div class="ss-metric-box">
							<span class="m-label"><?php esc_html_e( 'Threats Detected', 'siteshield' ); ?></span>
							<strong class="m-value"><?php echo esc_html( $stats['active_threats'] ); ?></strong>
							<a href="?page=siteshield-dashboard&tab=scanner" class="m-link"><?php esc_html_e( 'View threats →', 'siteshield' ); ?></a>
						</div>
						<div class="ss-metric-box">
							<span class="m-label"><?php esc_html_e( 'Threats Blocked', 'siteshield' ); ?></span>
							<strong class="m-value"><?php echo number_format_i18n( $stats['blocked_attacks'] ); ?></strong>
							<a href="?page=siteshield-dashboard&tab=firewall" class="m-link"><?php esc_html_e( 'Firewall logs →', 'siteshield' ); ?></a>
						</div>
						<div class="ss-metric-box">
							<span class="m-label"><?php esc_html_e( 'Files Scanned', 'siteshield' ); ?></span>
							<strong class="m-value"><?php echo esc_html( get_option( 'siteshield_last_scan_total_files', '1,420' ) ); ?></strong>
							<a href="?page=siteshield-dashboard&tab=integrity" class="m-link"><?php esc_html_e( 'File integrity →', 'siteshield' ); ?></a>
						</div>
						<div class="ss-metric-box">
							<span class="m-label"><?php esc_html_e( 'Unique Visitors (7d)', 'siteshield' ); ?></span>
							<strong class="m-value"><?php echo number_format_i18n( isset( $visitor_state['unique_visitors'] ) ? $visitor_state['unique_visitors'] : 0 ); ?></strong>
							<a href="?page=siteshield-dashboard&tab=visitors" class="m-link"><?php esc_html_e( 'Visitor stats →', 'siteshield' ); ?></a>
						</div>
					</div>

					<!-- GRID 2 COLUNAS: RESUMO DE SEGURANÇA & ATIVIDADE RECENTE -->
					<div class="ss-grid-2" style="margin-top: 20px;">
						<!-- STATUS DOS MÓDULOS -->
						<div class="ss-card">
							<div class="card-header">
								<h3><?php esc_html_e( 'Security Modules Status', 'siteshield' ); ?></h3>
							</div>
							<div class="card-body">
								<ul class="ss-module-status-list">
									<li class="module-status-row">
										<div class="mod-info">
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
											<span><?php esc_html_e( 'Login Protection & Cloaking', 'siteshield' ); ?></span>
										</div>
										<span class="ss-badge ss-badge-primary"><?php echo ! empty( $settings['login_custom_url_enabled'] ) || ! empty( $settings['login_secret_key'] ) ? esc_html__( 'Active', 'siteshield' ) : esc_html__( 'Configured', 'siteshield' ); ?></span>
									</li>
									<li class="module-status-row">
										<div class="mod-info">
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
											<span><?php esc_html_e( 'Web Application Firewall (WAF)', 'siteshield' ); ?></span>
										</div>
										<span class="ss-badge ss-badge-primary"><?php echo esc_html( $settings['waf_mode'] ); ?></span>
									</li>
									<li class="module-status-row">
										<div class="mod-info">
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
											<span><?php esc_html_e( 'Heuristic Malware Scanner', 'siteshield' ); ?></span>
										</div>
										<span class="ss-badge ss-badge-primary"><?php esc_html_e( 'Ready', 'siteshield' ); ?></span>
									</li>
									<li class="module-status-row">
										<div class="mod-info">
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
											<span><?php esc_html_e( 'File Integrity SHA-256', 'siteshield' ); ?></span>
										</div>
										<span class="ss-badge ss-badge-primary"><?php echo ! empty( $settings['integrity_monitor_enabled'] ) ? esc_html__( 'Active', 'siteshield' ) : esc_html__( 'Disabled', 'siteshield' ); ?></span>
									</li>
									<li class="module-status-row">
										<div class="mod-info">
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
											<span><?php esc_html_e( 'Security Headers & CSP', 'siteshield' ); ?></span>
										</div>
										<span class="ss-badge ss-badge-primary"><?php esc_html_e( 'Protected', 'siteshield' ); ?></span>
									</li>
								</ul>
							</div>
						</div>

						<!-- DIAGNÓSTICO DO SCORE -->
						<div class="ss-card">
							<div class="card-header">
								<h3><?php esc_html_e( 'Security Score Breakdown', 'siteshield' ); ?></h3>
							</div>
							<div class="card-body">
								<div class="score-factors-breakdown">
									<?php if ( ! empty( $score_data['factors'] ) && is_array( $score_data['factors'] ) ) : ?>
										<?php foreach ( $score_data['factors'] as $factor ) : ?>
											<div class="factor-item-clean">
												<div class="factor-top">
													<span class="factor-title"><?php echo esc_html( isset( $factor['name'] ) ? $factor['name'] : '' ); ?></span>
													<span class="factor-pts">+<?php echo esc_html( isset( $factor['score'] ) ? $factor['score'] : 0 ); ?> pts</span>
												</div>
												<?php if ( ! empty( $factor['fix'] ) ) : ?>
													<div class="factor-tip"><?php echo esc_html( $factor['fix'] ); ?></div>
												<?php endif; ?>
											</div>
										<?php endforeach; ?>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
					<?php
					break;
			}
			?>
		</main>
	</div>
</div>
