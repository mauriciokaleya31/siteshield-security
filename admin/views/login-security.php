<?php
/**
 * View de Segurança de Login e Custom Login URL do SiteShield
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Admin\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_user_id = function_exists( 'get_current_user_id' ) ? get_current_user_id() : 0;
$current_user    = function_exists( 'wp_get_current_user' ) ? wp_get_current_user() : null;
$user_login      = ( is_object( $current_user ) && isset( $current_user->user_login ) ) ? $current_user->user_login : 'admin';

$is_2fa_active   = $current_user_id > 0 ? get_user_meta( $current_user_id, \SiteShield\Login\Two_Factor::ENABLED_META_KEY, true ) : false;
$secret          = $current_user_id > 0 ? get_user_meta( $current_user_id, \SiteShield\Login\Two_Factor::SECRET_META_KEY, true ) : '';
if ( empty( $secret ) && class_exists( '\SiteShield\Login\Two_Factor' ) ) {
	$secret = \SiteShield\Login\Two_Factor::generate_secret();
	if ( $current_user_id > 0 ) {
		update_user_meta( $current_user_id, \SiteShield\Login\Two_Factor::SECRET_META_KEY, $secret );
	}
}

$otp_url          = 'otpauth://totp/' . rawurlencode( get_bloginfo( 'name' ) ) . ':' . rawurlencode( $user_login ) . '?secret=' . $secret . '&issuer=' . rawurlencode( 'SiteShield' );
$qr_url           = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . rawurlencode( $otp_url );
$active_login_url = class_exists( '\SiteShield\Login\Login_Security' ) ? \SiteShield\Login\Login_Security::get_active_login_url() : wp_login_url();
?>
<div class="ss-content-header">
	<div class="content-header-title">
		<h2><?php esc_html_e( 'Login Security & URL Cloaking', 'siteshield' ); ?></h2>
		<p><?php esc_html_e( 'Proteção da porta de entrada do WordPress, alteração de URL e autenticação de dois fatores (2FA).', 'siteshield' ); ?></p>
	</div>
</div>

<!-- 1. ALTERAR ENDEREÇO DE LOGIN DO WORDPRESS (CUSTOM LOGIN URL) -->
<div class="ss-card">
	<div class="card-header">
		<h3><?php esc_html_e( 'Change WordPress Login URL', 'siteshield' ); ?></h3>
	</div>
	<div class="card-body">
		<p><?php esc_html_e( 'Substitua o endereço padrão /wp-login.php por uma URL personalizada para blindar contra robôs automatizados de força bruta.', 'siteshield' ); ?></p>

		<form id="form-custom-login-url" style="margin-top: 20px;">
			<?php wp_nonce_field( 'siteshield_admin_nonce', 'nonce' ); ?>

			<label class="ss-toggle-card" style="margin-bottom: 20px;">
				<input type="checkbox" name="login_custom_url_enabled" id="login_custom_url_enabled" value="1" <?php checked( $settings['login_custom_url_enabled'] ); ?> />
				<span class="card-info">
					<strong><?php esc_html_e( 'Ativar URL de Login Personalizada', 'siteshield' ); ?></strong>
					<small><?php esc_html_e( 'O endereço padrão /wp-login.php será bloqueado e redirecionará para 404.', 'siteshield' ); ?></small>
				</span>
			</label>

			<div class="ss-input-group" style="max-width: 520px;">
				<label><?php esc_html_e( 'Novo Caminho de Acesso (Slug):', 'siteshield' ); ?></label>
				<div class="custom-url-input-wrap">
					<span class="url-prefix"><?php echo esc_html( home_url( '/' ) ); ?></span>
					<input type="text" name="login_custom_slug" id="login_custom_slug" value="<?php echo esc_attr( $settings['login_custom_slug'] ); ?>" placeholder="secure-login" />
				</div>
				<small style="display:block; margin-top: 6px; color: #64748B;">
					<?php esc_html_e( 'Exemplos recomendados: secure-login, meu-acesso, admin-portal.', 'siteshield' ); ?>
				</small>
			</div>

			<div class="login-url-display-box" style="margin-top: 20px;">
				<div class="display-label"><?php esc_html_e( 'Endereço Atual de Login:', 'siteshield' ); ?></div>
				<div class="display-url-row">
					<code id="active-login-url-preview"><?php echo esc_html( $active_login_url ); ?></code>
					<button type="button" class="ss-btn ss-btn-sm ss-btn-secondary" id="btn-copy-active-login-url">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
						<span><?php esc_html_e( 'Copy URL', 'siteshield' ); ?></span>
					</button>
				</div>
			</div>

			<div style="margin-top: 25px;">
				<button type="submit" class="ss-btn ss-btn-primary" id="btn-save-custom-login">
					<?php esc_html_e( 'Save Login URL', 'siteshield' ); ?>
				</button>
			</div>
		</form>
	</div>
</div>

<!-- 2. FORÇA BRUTA & LIMITES -->
<div class="ss-card" style="margin-top: 25px;">
	<div class="card-header">
		<h3><?php esc_html_e( 'Brute Force & Enumeration Protection', 'siteshield' ); ?></h3>
	</div>
	<div class="card-body">
		<form id="form-login-limits-settings" class="ss-settings-form">
			<?php wp_nonce_field( 'siteshield_admin_nonce', 'nonce' ); ?>
			<div class="ss-grid-2">
				<label class="ss-toggle-card">
					<input type="checkbox" name="rate_limit_enabled" value="1" <?php checked( $settings['rate_limit_enabled'] ); ?> />
					<span class="card-info">
						<strong><?php esc_html_e( 'Limitar Tentativas de Senha com Falha', 'siteshield' ); ?></strong>
						<small><?php esc_html_e( 'Bloqueia o IP temporariamente por 15 minutos após 5 tentativas incorretas.', 'siteshield' ); ?></small>
					</span>
				</label>

				<label class="ss-toggle-card">
					<input type="checkbox" name="login_disable_user_enum" value="1" <?php checked( $settings['login_disable_user_enum'] ); ?> />
					<span class="card-info">
						<strong><?php esc_html_e( 'Bloquear Enumeração de Nomes de Usuários', 'siteshield' ); ?></strong>
						<small><?php esc_html_e( 'Impede bots de descobrirem os nomes dos administradores via ?author=X ou REST API.', 'siteshield' ); ?></small>
					</span>
				</label>
			</div>

			<div style="margin-top: 20px; text-align: right;">
				<button type="submit" class="ss-btn ss-btn-primary"><?php esc_html_e( 'Salvar Configurações de Força Bruta', 'siteshield' ); ?></button>
			</div>
		</form>
	</div>
</div>

<!-- 3. AUTENTICAÇÃO 2FA TOTP -->
<div class="ss-card" style="margin-top: 25px;">
	<div class="card-header">
		<h3><?php esc_html_e( 'Two-Factor Authentication (2FA TOTP)', 'siteshield' ); ?></h3>
	</div>
	<div class="card-body">
		<div class="two-factor-setup-grid">
			<div class="two-factor-qr">
				<img src="<?php echo esc_url( $qr_url ); ?>" alt="QR Code 2FA" width="160" height="160" style="border-radius: 8px; border: 1px solid #E2E8F0;" />
			</div>
			<div class="two-factor-instructions">
				<h4><?php esc_html_e( 'Aplicativo Autenticador (RFC 6238)', 'siteshield' ); ?></h4>
				<p><?php esc_html_e( 'Escaneie o QR Code com o Google Authenticator, Authy, 1Password ou Microsoft Authenticator.', 'siteshield' ); ?></p>
				<p><strong><?php esc_html_e( 'Chave Manual (Base32):', 'siteshield' ); ?></strong> <code><?php echo esc_html( $secret ); ?></code></p>
				<div>
					<?php if ( $is_2fa_active ) : ?>
						<span class="ss-badge ss-badge-primary">✓ <?php esc_html_e( '2FA Ativado para sua conta', 'siteshield' ); ?></span>
					<?php else : ?>
						<span class="ss-badge ss-badge-neutral"><?php esc_html_e( '2FA Pendente de Ativação', 'siteshield' ); ?></span>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>
