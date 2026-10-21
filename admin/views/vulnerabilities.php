<?php
/**
 * View do Monitor de Vulnerabilidades e Diagnóstico de Ambiente
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Admin\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$vulns = \SiteShield\Vulnerabilities\Vulnerability_Monitor::audit();
?>
<div class="ss-content-header">
	<div class="content-header-title">
		<h2><?php esc_html_e( 'Vulnerability Monitor & Audit', 'siteshield' ); ?></h2>
		<p><?php esc_html_e( 'Auditoria de segurança de versões do WordPress, plugins, temas e ambiente PHP.', 'siteshield' ); ?></p>
	</div>
</div>

<div class="ss-card">
	<div class="card-header">
		<h3><?php esc_html_e( 'Environment & Patch Status', 'siteshield' ); ?> (<?php echo count( $vulns ); ?>)</h3>
	</div>
	<div class="card-body">
		<?php if ( empty( $vulns ) ) : ?>
			<div class="empty-statee-card text-center">
				<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2" style="margin: 0 auto 10px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
				<h4><?php esc_html_e( 'No Known Vulnerabilities', 'siteshield' ); ?></h4>
				<p><?php esc_html_e( 'Seu WordPress, plugins, temas e ambiente PHP estão com versões seguras.', 'siteshield' ); ?></p>
			</div>
		<?php else : ?>
			<div class="vuln-cards-list">
				<?php foreach ( $vulns as $v ) : ?>
					<div class="vuln-card">
						<div class="vuln-header">
							<span class="ss-badge ss-badge-primary"><?php echo esc_html( strtoupper( $v['severity'] ) ); ?></span>
							<h4><?php echo esc_html( $v['title'] ); ?></h4>
						</div>
						<div class="vuln-body">
							<p><strong><?php esc_html_e( 'Component:', 'siteshield' ); ?></strong> <?php echo esc_html( $v['name'] ); ?> (v<?php echo esc_html( $v['version'] ); ?>)</p>
							<p><strong><?php esc_html_e( 'Fixed Version:', 'siteshield' ); ?></strong> <?php echo esc_html( $v['fixed_version'] ); ?></p>
							<div class="vuln-action-box">
								<strong><?php esc_html_e( 'Recommended Action:', 'siteshield' ); ?></strong> <?php echo esc_html( $v['action'] ); ?>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
