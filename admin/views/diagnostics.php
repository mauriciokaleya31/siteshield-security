<?php
/**
 * View de Diagnósticos Avançados de Segurança do SiteShield
 * Em conformidade com OWASP e Princípio "No False Security"
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Admin\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$diag = \SiteShield\Diagnostics\Security_Diagnostics::run_full_audit();
?>
<div class="ss-content-header">
	<div class="content-header-title">
		<h2><?php esc_html_e( 'Security Diagnostics & Standards Audit', 'siteshield' ); ?></h2>
		<p><?php esc_html_e( 'Auditoria profunda de WordPress Core, Permissões, Debug Mode, SSL/TLS e Segurança DNS (SPF/DMARC).', 'siteshield' ); ?></p>
	</div>
	<div class="content-header-actions">
		<button type="button" class="ss-btn ss-btn-primary" id="btn-re-run-diagnostics">
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
			<span><?php esc_html_e( 'Reexecutar Diagnósticos', 'siteshield' ); ?></span>
		</button>
	</div>
</div>

<!-- GRADE DE DIAGNÓSTICOS -->
<div class="ss-grid-2">
	<!-- 1. WORDPRESS CORE & EXTENSÕES -->
	<div class="ss-card">
		<div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
			<h3><?php echo esc_html( $diag['core_security']['title'] ); ?></h3>
			<span class="ss-badge <?php echo 'PASSED' === $diag['core_security']['status'] ? 'ss-badge-primary' : 'ss-badge-neutral'; ?>">
				<?php echo esc_html( $diag['core_security']['status'] ); ?>
			</span>
		</div>
		<div class="card-body">
			<p><?php echo esc_html( $diag['core_security']['details'] ); ?></p>
			<ul class="diag-details-list">
				<li><span>Versão do WordPress:</span> <strong>v<?php echo esc_html( $diag['core_security']['wp_version'] ); ?></strong></li>
				<li><span>Plugins com Atualização:</span> <strong><?php echo esc_html( $diag['core_security']['plugins_out'] ); ?></strong></li>
				<li><span>Temas com Atualização:</span> <strong><?php echo esc_html( $diag['core_security']['themes_out'] ); ?></strong></li>
			</ul>
		</div>
	</div>

	<!-- 2. PERMISSÕES DE FICHEIROS CRÍTICOS -->
	<div class="ss-card">
		<div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
			<h3><?php echo esc_html( $diag['file_permissions']['title'] ); ?></h3>
			<span class="ss-badge <?php echo 'PASSED' === $diag['file_permissions']['status'] ? 'ss-badge-primary' : 'ss-badge-neutral'; ?>">
				<?php echo esc_html( $diag['file_permissions']['status'] ); ?>
			</span>
		</div>
		<div class="card-body">
			<p><?php echo esc_html( $diag['file_permissions']['details'] ); ?></p>
			<ul class="diag-details-list">
				<?php if ( isset( $diag['file_permissions']['items']['wp_config'] ) ) : ?>
					<li>
						<span>wp-config.php (Permissões: <?php echo esc_html( $diag['file_permissions']['items']['wp_config']['perms'] ); ?>):</span>
						<strong><?php echo esc_html( $diag['file_permissions']['items']['wp_config']['status'] ); ?></strong>
					</li>
				<?php endif; ?>
				<?php if ( isset( $diag['file_permissions']['items']['htaccess'] ) ) : ?>
					<li>
						<span>.htaccess (Permissões: <?php echo esc_html( $diag['file_permissions']['items']['htaccess']['perms'] ); ?>):</span>
						<strong><?php echo esc_html( $diag['file_permissions']['items']['htaccess']['status'] ); ?></strong>
					</li>
				<?php endif; ?>
			</ul>
		</div>
	</div>

	<!-- 3. CONTAS DE ADMINISTRADOR -->
	<div class="ss-card">
		<div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
			<h3><?php echo esc_html( $diag['user_security']['title'] ); ?></h3>
			<span class="ss-badge <?php echo 'PASSED' === $diag['user_security']['status'] ? 'ss-badge-primary' : 'ss-badge-neutral'; ?>">
				<?php echo esc_html( $diag['user_security']['status'] ); ?>
			</span>
		</div>
		<div class="card-body">
			<p><?php echo esc_html( $diag['user_security']['details'] ); ?></p>
		</div>
	</div>

	<!-- 4. DEBUG MODE -->
	<div class="ss-card">
		<div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
			<h3><?php echo esc_html( $diag['debug_mode']['title'] ); ?></h3>
			<span class="ss-badge <?php echo 'PASSED' === $diag['debug_mode']['status'] ? 'ss-badge-primary' : 'ss-badge-neutral'; ?>">
				<?php echo esc_html( $diag['debug_mode']['status'] ); ?>
			</span>
		</div>
		<div class="card-body">
			<p><?php echo esc_html( $diag['debug_mode']['details'] ); ?></p>
		</div>
	</div>

	<!-- 5. CRIPTOGRAFIA SSL / TLS -->
	<div class="ss-card">
		<div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
			<h3><?php echo esc_html( $diag['ssl_tls']['title'] ); ?></h3>
			<span class="ss-badge <?php echo 'PASSED' === $diag['ssl_tls']['status'] ? 'ss-badge-primary' : 'ss-badge-neutral'; ?>">
				<?php echo esc_html( $diag['ssl_tls']['status'] ); ?>
			</span>
		</div>
		<div class="card-body">
			<p><?php echo esc_html( $diag['ssl_tls']['details'] ); ?></p>
			<ul class="diag-details-list">
				<li><span>Protocolo de Criptografia:</span> <strong><?php echo esc_html( $diag['ssl_tls']['cert_info']['protocol'] ); ?></strong></li>
				<li><span>Emissor do Certificado:</span> <strong><?php echo esc_html( $diag['ssl_tls']['cert_info']['issuer'] ); ?></strong></li>
			</ul>
		</div>
	</div>

	<!-- 6. SEGURANÇA DNS (SPF / DMARC / MX) -->
	<div class="ss-card">
		<div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
			<h3><?php echo esc_html( $diag['dns_security']['title'] ); ?></h3>
			<span class="ss-badge <?php echo 'PASSED' === $diag['dns_security']['status'] ? 'ss-badge-primary' : 'ss-badge-neutral'; ?>">
				<?php echo esc_html( $diag['dns_security']['status'] ); ?>
			</span>
		</div>
		<div class="card-body">
			<p><?php echo esc_html( $diag['dns_security']['details'] ); ?></p>
			<ul class="diag-details-list">
				<li><span>SPF (Sender Policy Framework):</span> <strong><?php echo esc_html( $diag['dns_security']['dns_data']['spf']['status'] ); ?></strong></li>
				<li><span>DMARC (Domain-based Auth):</span> <strong><?php echo esc_html( $diag['dns_security']['dns_data']['dmarc']['status'] ); ?></strong></li>
				<li><span>Registos MX (Servidores de Correio):</span> <strong><?php echo esc_html( $diag['dns_security']['dns_data']['mx']['status'] ); ?></strong></li>
			</ul>
		</div>
	</div>
</div>
