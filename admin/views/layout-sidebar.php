<?php
/**
 * Menu Lateral (Sidebar) com Ícones SVG do SiteShield Security
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Admin\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$plan_label = isset( $license['plan'] ) ? $license['plan'] : 'FREE';
?>
<aside class="siteshield-sidebar">
	<!-- BRAND LOGO -->
	<div class="sidebar-brand">
		<div class="brand-icon">
			<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
				<path d="m9 12 2 2 4-4"></path>
			</svg>
		</div>
		<div class="brand-text">
			<span class="brand-title">SiteShield</span>
			<span class="brand-badge"><?php echo esc_html( $plan_label ); ?></span>
		</div>
	</div>

	<!-- NAVEGAÇÃO -->
	<nav class="sidebar-nav">
		<!-- DASHBOARD -->
		<a href="?page=siteshield-dashboard&tab=dashboard" class="nav-item <?php echo 'dashboard' === $active_tab ? 'active' : ''; ?>">
			<svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<rect x="3" y="3" width="7" height="7"></rect>
				<rect x="14" y="3" width="7" height="7"></rect>
				<rect x="14" y="14" width="7" height="7"></rect>
				<rect x="3" y="14" width="7" height="7"></rect>
			</svg>
			<span><?php esc_html_e( 'Dashboard', 'siteshield' ); ?></span>
		</a>

		<a href="?page=siteshield-dashboard&tab=system-health" class="nav-item <?php echo 'system-health' === $active_tab ? 'active' : ''; ?>">
			<svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
			</svg>
			<span><?php esc_html_e( 'System Health', 'siteshield' ); ?></span>
		</a>

		<!-- PROTECTION GROUP -->
		<div class="nav-group-header"><?php esc_html_e( 'Protection', 'siteshield' ); ?></div>

		<a href="?page=siteshield-dashboard&tab=content-protection" class="nav-item <?php echo 'content-protection' === $active_tab ? 'active' : ''; ?>">
			<svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
			</svg>
			<span><?php esc_html_e( 'Anti-Clone', 'siteshield' ); ?></span>
		</a>

		<a href="?page=siteshield-dashboard&tab=login-security" class="nav-item <?php echo 'login-security' === $active_tab ? 'active' : ''; ?>">
			<svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
				<path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
			</svg>
			<span><?php esc_html_e( 'Login Security', 'siteshield' ); ?></span>
		</a>

		<a href="?page=siteshield-dashboard&tab=active-sessions" class="nav-item <?php echo 'active-sessions' === $active_tab ? 'active' : ''; ?>">
			<svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
				<circle cx="12" cy="7" r="4"></circle>
			</svg>
			<span><?php esc_html_e( 'Active Sessions', 'siteshield' ); ?></span>
		</a>

		<a href="?page=siteshield-dashboard&tab=firewall" class="nav-item <?php echo 'firewall' === $active_tab ? 'active' : ''; ?>">
			<svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
			</svg>
			<span><?php esc_html_e( 'Firewall (WAF)', 'siteshield' ); ?></span>
		</a>

		<a href="?page=siteshield-dashboard&tab=security-headers" class="nav-item <?php echo 'security-headers' === $active_tab ? 'active' : ''; ?>">
			<svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<circle cx="12" cy="12" r="10"></circle>
				<line x1="2" y1="12" x2="22" y2="12"></line>
				<path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
			</svg>
			<span><?php esc_html_e( 'Security Headers', 'siteshield' ); ?></span>
		</a>

		<a href="?page=siteshield-dashboard&tab=hardening" class="nav-item <?php echo 'hardening' === $active_tab ? 'active' : ''; ?>">
			<svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
				<path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
			</svg>
			<span><?php esc_html_e( 'Hardening', 'siteshield' ); ?></span>
		</a>

		<!-- SCANNER GROUP -->
		<div class="nav-group-header"><?php esc_html_e( 'Scanner', 'siteshield' ); ?></div>

		<a href="?page=siteshield-dashboard&tab=scanner" class="nav-item <?php echo 'scanner' === $active_tab ? 'active' : ''; ?>">
			<svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<circle cx="11" cy="11" r="8"></circle>
				<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
			</svg>
			<span><?php esc_html_e( 'Malware Scanner', 'siteshield' ); ?></span>
		</a>

		<a href="?page=siteshield-dashboard&tab=integrity" class="nav-item <?php echo 'integrity' === $active_tab ? 'active' : ''; ?>">
			<svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
				<polyline points="14 2 14 8 20 8"></polyline>
			</svg>
			<span><?php esc_html_e( 'File Integrity', 'siteshield' ); ?></span>
		</a>

		<a href="?page=siteshield-dashboard&tab=vulnerabilities" class="nav-item <?php echo 'vulnerabilities' === $active_tab ? 'active' : ''; ?>">
			<svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<circle cx="12" cy="12" r="10"></circle>
				<line x1="12" y1="8" x2="12" y2="12"></line>
				<line x1="12" y1="16" x2="12.01" y2="16"></line>
			</svg>
			<span><?php esc_html_e( 'Vulnerabilities', 'siteshield' ); ?></span>
		</a>

		<a href="?page=siteshield-dashboard&tab=diagnostics-logs" class="nav-item <?php echo 'diagnostics-logs' === $active_tab ? 'active' : ''; ?>">
			<svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<polyline points="4 17 10 11 4 5"></polyline>
				<line x1="12" y1="19" x2="20" y2="19"></line>
			</svg>
			<span><?php esc_html_e( 'Diagnóstico & Logs', 'siteshield' ); ?></span>
		</a>

		<a href="?page=siteshield-dashboard&tab=diagnostics" class="nav-item <?php echo 'diagnostics' === $active_tab ? 'active' : ''; ?>">
			<svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<circle cx="12" cy="12" r="10"></circle>
				<polyline points="12 6 12 12 14 14"></polyline>
			</svg>
			<span><?php esc_html_e( 'Diagnostics', 'siteshield' ); ?></span>
		</a>

		<!-- VISITORS & MONITORING -->
		<div class="nav-group-header"><?php esc_html_e( 'Visitors & Traffic', 'siteshield' ); ?></div>

		<a href="?page=siteshield-dashboard&tab=visitors" class="nav-item <?php echo 'visitors' === $active_tab ? 'active' : ''; ?>">
			<svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
				<circle cx="9" cy="7" r="4"></circle>
				<path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
				<path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
			</svg>
			<span><?php esc_html_e( 'Visitors & IPs', 'siteshield' ); ?></span>
		</a>

		<a href="?page=siteshield-dashboard&tab=security-events" class="nav-item <?php echo 'security-events' === $active_tab ? 'active' : ''; ?>">
			<svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
			</svg>
			<span><?php esc_html_e( 'Security Events', 'siteshield' ); ?></span>
		</a>

		<a href="?page=siteshield-dashboard&tab=activity-log" class="nav-item <?php echo 'activity-log' === $active_tab ? 'active' : ''; ?>">
			<svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
				<polyline points="14 2 14 8 20 8"></polyline>
				<line x1="16" y1="13" x2="8" y2="13"></line>
				<line x1="16" y1="17" x2="8" y2="17"></line>
				<polyline points="10 9 9 9 8 9"></polyline>
			</svg>
			<span><?php esc_html_e( 'Activity Log', 'siteshield' ); ?></span>
		</a>

		<!-- MANAGEMENT -->
		<div class="nav-group-header"><?php esc_html_e( 'Management & Cloud', 'siteshield' ); ?></div>

		<a href="?page=siteshield-dashboard&tab=verified-badge" class="nav-item <?php echo 'verified-badge' === $active_tab ? 'active' : ''; ?>">
			<svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
				<path d="m9 12 2 2 4-4"></path>
			</svg>
			<span><?php esc_html_e( 'Selo Verificado', 'siteshield' ); ?></span>
		</a>

		<a href="?page=siteshield-dashboard&tab=notifications" class="nav-item <?php echo 'notifications' === $active_tab ? 'active' : ''; ?>">
			<svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
				<path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
			</svg>
			<span><?php esc_html_e( 'Notificações', 'siteshield' ); ?></span>
		</a>

		<a href="?page=siteshield-dashboard&tab=reports" class="nav-item <?php echo 'reports' === $active_tab ? 'active' : ''; ?>">
			<svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
				<polyline points="7 10 12 15 17 10"></polyline>
				<line x1="12" y1="15" x2="12" y2="3"></line>
			</svg>
			<span><?php esc_html_e( 'Reports (PDF)', 'siteshield' ); ?></span>
		</a>

		<a href="?page=siteshield-dashboard&tab=license" class="nav-item <?php echo 'license' === $active_tab ? 'active' : ''; ?>">
			<svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
			</svg>
			<span><?php esc_html_e( 'License & Plans', 'siteshield' ); ?></span>
		</a>

		<a href="?page=siteshield-dashboard&tab=support" class="nav-item <?php echo 'support' === $active_tab ? 'active' : ''; ?>">
			<svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<circle cx="12" cy="12" r="10"></circle>
				<path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
				<line x1="12" y1="17" x2="12.01" y2="17"></line>
			</svg>
			<span><?php esc_html_e( 'Suporte & Ajuda', 'siteshield' ); ?></span>
		</a>

		<a href="?page=siteshield-dashboard&tab=settings" class="nav-item <?php echo 'settings' === $active_tab ? 'active' : ''; ?>">
			<svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<circle cx="12" cy="12" r="3"></circle>
				<path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
			</svg>
			<span><?php esc_html_e( 'Settings', 'siteshield' ); ?></span>
		</a>
	</nav>
</aside>
