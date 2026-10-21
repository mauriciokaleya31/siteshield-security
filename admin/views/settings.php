<?php
/**
 * View de Configurações Gerais e Privacidade do SiteShield
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
		<h2><?php esc_html_e( 'System Settings & Privacy Policy', 'siteshield' ); ?></h2>
		<p><?php esc_html_e( 'Configurações de retenção de dados, anonimização de IPs e compatibilidade.', 'siteshield' ); ?></p>
	</div>
</div>

<form id="form-general-settings" class="ss-settings-form">
	<?php wp_nonce_field( 'siteshield_admin_nonce', 'nonce' ); ?>

	<!-- IDIOMA E INTERNACIONALIZAÇÃO -->
	<div class="ss-card" style="margin-bottom: 25px;">
		<div class="card-header">
			<h3><?php esc_html_e( 'Language & Localization', 'siteshield' ); ?></h3>
		</div>
		<div class="card-body">
			<div class="ss-input-group" style="max-width: 320px;">
				<label><?php esc_html_e( 'Select Interface Language:', 'siteshield' ); ?></label>
				<?php
				$current_loc = \SiteShield\I18n\I18n::get_current_locale();
				$locales     = \SiteShield\I18n\I18n::get_supported_locales();
				?>
				<select name="locale" id="settings_locale_select" class="ss-select">
					<?php foreach ( $locales as $code => $info ) : ?>
						<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $current_loc, $code ); ?>>
							<?php echo esc_html( $info['native'] ); ?> (<?php echo esc_html( $info['name'] ); ?>)
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
	</div>

	<!-- PRIVACY CENTER & SITE IDENTITY -->
	<div class="ss-card" style="margin-bottom: 25px;">
		<div class="card-header">
			<h3><?php esc_html_e( 'Privacy Center & Site Identity', 'siteshield' ); ?></h3>
		</div>
		<div class="card-body">
			<div class="ss-grid-2">
				<div class="ss-input-group">
					<label><?php esc_html_e( 'Unique Site ID (Identificador do Site):', 'siteshield' ); ?></label>
					<input type="text" readonly value="<?php echo esc_attr( isset( $settings['site_id'] ) ? $settings['site_id'] : 'ss_site_' . substr( md5( get_site_url() ), 0, 12 ) ); ?>" class="ss-input" style="background:#F1F5F9;" />
					<small style="color:#64748B;"><?php esc_html_e( 'Identificador único gerado para vinculação segura com o SiteShield Cloud.', 'siteshield' ); ?></small>
				</div>

				<div class="ss-input-group">
					<label><?php esc_html_e( 'Cloud Data Transmission Level:', 'siteshield' ); ?></label>
					<select name="privacy_mode" class="ss-select">
						<option value="local_only" <?php selected( isset( $settings['privacy_mode'] ) ? $settings['privacy_mode'] : 'local_only', 'local_only' ); ?>>Local Only (Nenhum dado externo enviado)</option>
						<option value="cloud_enabled" <?php selected( isset( $settings['privacy_mode'] ) ? $settings['privacy_mode'] : 'local_only', 'cloud_enabled' ); ?>>Cloud Enabled (Apenas validação de licença)</option>
						<option value="anonymous_telemetry" <?php selected( isset( $settings['privacy_mode'] ) ? $settings['privacy_mode'] : 'local_only', 'anonymous_telemetry' ); ?>>Anonymous Telemetry (Métricas agregadas anônimas)</option>
						<option value="full_monitoring" <?php selected( isset( $settings['privacy_mode'] ) ? $settings['privacy_mode'] : 'local_only', 'full_monitoring' ); ?>>Full Monitoring (Sincronização remota completa)</option>
					</select>
					<small style="color:#64748B;"><?php esc_html_e( 'Dados privados, senhas ou conteúdos do site nunca são transmitidos.', 'siteshield' ); ?></small>
				</div>
			</div>
		</div>
	</div>

	<!-- PRIVACIDADE & VISITANTES -->
	<div class="ss-card">
		<div class="card-header">
			<h3><?php esc_html_e( 'Privacy & Visitor Tracking', 'siteshield' ); ?></h3>
		</div>
		<div class="card-body">
			<div class="ss-grid-2">
				<label class="ss-toggle-card">
					<input type="checkbox" name="visitor_tracking_enabled" value="1" <?php checked( $settings['visitor_tracking_enabled'] ); ?> />
					<span class="card-info">
						<strong><?php esc_html_e( 'Enable Visitor Statistics', 'siteshield' ); ?></strong>
						<small><?php esc_html_e( 'Rastreamento leve e não intrusivo de tráfego e requisições no frontend.', 'siteshield' ); ?></small>
					</span>
				</label>

				<label class="ss-toggle-card">
					<input type="checkbox" name="ip_anonymization_enabled" value="1" <?php checked( $settings['ip_anonymization_enabled'] ); ?> />
					<span class="card-info">
						<strong><?php esc_html_e( 'Anonymize IP Addresses (GDPR / LGPD)', 'siteshield' ); ?></strong>
						<small><?php esc_html_e( 'Mascara o último octeto dos IPs armazenados para proteger a privacidade.', 'siteshield' ); ?></small>
					</span>
				</label>
			</div>

			<div class="ss-input-group" style="margin-top: 20px; max-width: 320px;">
				<label><?php esc_html_e( 'IP & Log Retention Period:', 'siteshield' ); ?></label>
				<select name="ip_retention_days" class="ss-select">
					<option value="7" <?php selected( $settings['ip_retention_days'], 7 ); ?>>7 Days</option>
					<option value="30" <?php selected( $settings['ip_retention_days'], 30 ); ?>>30 Days (Recommended)</option>
					<option value="90" <?php selected( $settings['ip_retention_days'], 90 ); ?>>90 Days</option>
					<option value="180" <?php selected( $settings['ip_retention_days'], 180 ); ?>>180 Days</option>
				</select>
			</div>
		</div>
	</div>

	<!-- COMPATIBILIDADE -->
	<div class="ss-card" style="margin-top: 25px;">
		<div class="card-header">
			<h3><?php esc_html_e( 'Page Builder & Cache Compatibility', 'siteshield' ); ?></h3>
		</div>
		<div class="card-body">
			<label class="ss-toggle-card">
				<input type="checkbox" name="compatibility_mode" value="1" <?php checked( $settings['compatibility_mode'] ); ?> />
				<span class="card-info">
					<strong><?php esc_html_e( 'Enable High Compatibility Mode', 'siteshield' ); ?></strong>
					<small><?php esc_html_e( 'Reduz restrições de headers e WAF durante sessões do Elementor, Divi e WooCommerce.', 'siteshield' ); ?></small>
				</span>
			</label>
		</div>
	</div>

	<!-- DESINSTALAÇÃO LIMPA -->
	<div class="ss-card" style="margin-top: 25px;">
		<div class="card-header">
			<h3><?php esc_html_e( 'Uninstallation & Data Cleanup', 'siteshield' ); ?></h3>
		</div>
		<div class="card-body">
			<label class="ss-toggle-card">
				<input type="checkbox" name="delete_data_on_uninstall" value="1" <?php checked( $settings['delete_data_on_uninstall'] ); ?> />
				<span class="card-info">
					<strong><?php esc_html_e( 'Delete all SiteShield data on uninstall', 'siteshield' ); ?></strong>
					<small><?php esc_html_e( 'Se marcado, todas as tabelas, logs e quarentena serão permanentemente removidos.', 'siteshield' ); ?></small>
				</span>
			</label>

			<div style="margin-top: 20px; text-align: right;">
				<button type="submit" class="ss-btn ss-btn-primary"><?php esc_html_e( 'Salvar Configurações Gerais', 'siteshield' ); ?></button>
			</div>
		</div>
	</div>
</form>
