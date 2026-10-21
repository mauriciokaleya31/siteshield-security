<?php
/**
 * View de Cabeçalhos HTTP de Segurança e CSP
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Admin\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="ss-content-header">
	<div class="content-header-title">
		<h2><?php esc_html_e( 'Security Headers & CSP', 'siteshield' ); ?></h2>
		<p><?php esc_html_e( 'Injeção de cabeçalhos padronizados para proteção contra Clickjacking, XSS e ataques de MIME.', 'siteshield' ); ?></p>
	</div>
</div>

<form id="form-security-headers" class="ss-settings-form">
	<?php wp_nonce_field( 'siteshield_admin_nonce', 'nonce' ); ?>

	<div class="ss-card">
		<div class="card-header">
			<h3><?php esc_html_e( 'HTTP Response Headers', 'siteshield' ); ?></h3>
		</div>
		<div class="card-body">
			<label class="ss-toggle-card" style="margin-bottom: 20px;">
				<input type="checkbox" name="headers_enabled" value="1" <?php checked( $settings['headers_enabled'] ); ?> />
				<span class="card-info">
					<strong><?php esc_html_e( 'Enable Security Headers Injection', 'siteshield' ); ?></strong>
					<small><?php esc_html_e( 'Adiciona cabeçalhos padronizados às respostas HTTP do servidor.', 'siteshield' ); ?></small>
				</span>
			</label>

			<div class="ss-grid-2">
				<label class="ss-toggle-card">
					<input type="checkbox" name="headers_hsts" value="1" <?php checked( $settings['headers_hsts'] ); ?> />
					<span class="card-info">
						<strong><?php esc_html_e( 'Strict-Transport-Security (HSTS)', 'siteshield' ); ?></strong>
						<small><?php esc_html_e( 'Força conexões exclusivamente criptografadas via HTTPS.', 'siteshield' ); ?></small>
					</span>
				</label>

				<label class="ss-toggle-card">
					<input type="checkbox" name="headers_x_content_type" value="1" <?php checked( $settings['headers_x_content_type'] ); ?> />
					<span class="card-info">
						<strong><?php esc_html_e( 'X-Content-Type-Options: nosniff', 'siteshield' ); ?></strong>
						<small><?php esc_html_e( 'Impede o navegador de interpretar arquivos com tipos MIME incorretos.', 'siteshield' ); ?></small>
					</span>
				</label>

				<label class="ss-toggle-card">
					<input type="checkbox" name="headers_x_xss_protection" value="1" <?php checked( $settings['headers_x_xss_protection'] ); ?> />
					<span class="card-info">
						<strong><?php esc_html_e( 'X-XSS-Protection: 1; mode=block', 'siteshield' ); ?></strong>
						<small><?php esc_html_e( 'Ativa o filtro de prevenção de ataques XSS no navegador.', 'siteshield' ); ?></small>
					</span>
				</label>
			</div>

			<div class="ss-input-group" style="margin-top: 20px; max-width: 440px;">
				<label><?php esc_html_e( 'X-Frame-Options:', 'siteshield' ); ?></label>
				<select name="headers_x_frame_options" class="ss-select">
					<option value="SAMEORIGIN" <?php selected( $settings['headers_x_frame_options'], 'SAMEORIGIN' ); ?>>SAMEORIGIN (Recommended)</option>
					<option value="DENY" <?php selected( $settings['headers_x_frame_options'], 'DENY' ); ?>>DENY (Block all frames)</option>
					<option value="OFF" <?php selected( $settings['headers_x_frame_options'], 'OFF' ); ?>>Disabled</option>
				</select>
			</div>
		</div>
	</div>

	<!-- CONTENT SECURITY POLICY -->
	<div class="ss-card" style="margin-top: 25px;">
		<div class="card-header">
			<h3><?php esc_html_e( 'Content Security Policy (CSP)', 'siteshield' ); ?></h3>
		</div>
		<div class="card-body">
			<div class="ss-input-group" style="max-width: 440px;">
				<label><?php esc_html_e( 'CSP Policy Mode:', 'siteshield' ); ?></label>
				<select name="headers_csp_mode" id="headers_csp_mode" class="ss-select">
					<option value="compatible" <?php selected( $settings['headers_csp_mode'], 'compatible' ); ?>>Compatible (frame-ancestors 'self' - Safe)</option>
					<option value="strict" <?php selected( $settings['headers_csp_mode'], 'strict' ); ?>>Strict (default-src 'self' https:)</option>
					<option value="custom" <?php selected( $settings['headers_csp_mode'], 'custom' ); ?>>Custom Directives</option>
				</select>
			</div>

			<div class="ss-input-group" id="custom-csp-box" style="margin-top: 15px; <?php echo 'custom' === $settings['headers_csp_mode'] ? '' : 'display:none;'; ?>">
				<label><?php esc_html_e( 'Custom CSP Directive:', 'siteshield' ); ?></label>
				<textarea name="headers_csp_custom" rows="3" placeholder="default-src 'self';"><?php echo esc_textarea( $settings['headers_csp_custom'] ); ?></textarea>
			</div>

			<div style="margin-top: 20px; text-align: right;">
				<button type="submit" class="ss-btn ss-btn-primary"><?php esc_html_e( 'Salvar Cabeçalhos de Segurança', 'siteshield' ); ?></button>
			</div>
		</div>
	</div>
</form>
