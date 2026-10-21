<?php
/**
 * Gerenciador Central de Configurações do SiteShield
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Core
 */

namespace SiteShield\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Settings
 */
class Settings {

	/**
	 * Nome da opção principal no banco do WordPress
	 */
	const OPTION_KEY = 'siteshield_settings';

	/**
	 * Configurações carregadas em memória
	 *
	 * @var array|null
	 */
	private static $settings = null;

	/**
	 * Retorna a lista completa de opções padrão com valores seguros (Secure Defaults)
	 *
	 * @return array
	 */
	public static function get_defaults() {
		return array(
			// Setup & Onboarding
			'setup_completed'             => false,
			'setup_step'                  => 1,
			'admin_email'                 => get_option( 'admin_email' ),
			'admin_name'                  => '',
			'consent_newsletter'          => false,
			'locale'                      => 'pt_PT', // pt_PT, en_US, es_ES, fr_FR

			// Planos & SiteShield Cloud
			'site_id'                     => 'ss_site_' . substr( md5( get_site_url() . (string) mt_rand() ), 0, 16 ),
			'license_plan'                => 'free', // free, pro, business, agency
			'license_key'                 => '',
			'license_status'              => 'active',
			'cloud_connected'             => false,
			'cloud_account_email'         => '',
			'privacy_mode'                => 'local_only', // local_only, cloud_enabled, anonymous_telemetry, full_monitoring

			// Firewall (WAF)
			'waf_mode'                    => 'MONITOR', // OFF, MONITOR, BLOCK
			'waf_level'                   => 'medium',  // low, medium, high, aggressive
			'waf_learning_mode'           => true,
			'waf_learning_days'           => 14,
			'waf_custom_exceptions'       => array(),
			'waf_block_sqli'              => true,
			'waf_block_xss'               => true,
			'waf_block_path_traversal'    => true,
			'waf_block_lfi_rfi'           => true,
			'waf_block_bad_user_agents'   => true,
			'waf_block_malicious_queries' => true,
			'waf_block_xmlrpc_abuse'      => true,

			// Rate Limiting
			'rate_limit_enabled'          => true,
			'rate_limit_login_attempts'   => 5,
			'rate_limit_login_lockout_min'=> 15,
			'rate_limit_api_max_req'      => 60,
			'rate_limit_general_max_req'  => 120,

			// Login Security & Custom URL
			'login_custom_url_enabled'    => false,
			'login_custom_slug'           => 'secure-login',
			'login_disable_wp_login'      => false,
			'login_secret_param'          => 'shield_key',
			'login_secret_key'            => 'shield_' . substr( md5( uniqid( (string) mt_rand(), true ) ), 0, 8 ),
			'login_cookie_durateion'       => 86400,
			'login_disable_user_enum'     => true,
			'login_disable_xmlrpc'        => false,
			'login_alert_admin_login'     => true,

			// Autenticação de Dois Fatores (2FA TOTP)
			'two_factor_enabled'          => false,
			'two_factor_roles'            => array( 'administrator' ),
			'two_factor_grace_period_days'=> 3,

			// Malware Scanner
			'scanner_scheduled_enabled'   => true,
			'scanner_frequency'           => 'weekly', // daily, weekly, monthly
			'scanner_scan_type'           => 'quick',  // quick, full, custom
			'scanner_quarantine_auto'     => false,
			'scanner_max_file_size_mb'    => 5,

			// File Integrity
			'integrity_monitor_enabled'   => true,
			'integrity_auto_baseline'     => true,

			// Vulnerability Monitor
			'vuln_monitor_enabled'        => true,
			'vuln_auto_alert'             => true,

			// Anti-Bot & Anti-Scraping
			'antibot_enabled'             => true,
			'antibot_mode'                => 'MONITOR', // OFF, MONITOR, BLOCK
			'antibot_rate_limit_req'      => 30,

			// Content Protection (Anti-Clone)
			'content_protection_enabled'  => true,
			'protect_disable_right_click' => true,
			'protect_disable_devtools'    => true,
			'protect_disable_copy_select' => true,
			'protect_disable_drag_images' => true,
			'protect_disable_print'       => false,
			'protect_frame_busting'       => true,
			'protect_anti_hotlink'        => false,
			'protect_hotlink_allowed_domains' => '',

			// Security Headers
			'headers_enabled'             => true,
			'headers_hsts'                => false,
			'headers_hsts_preload'        => false,
			'headers_x_frame_options'     => 'SAMEORIGIN', // SAMEORIGIN, DENY, OFF
			'headers_x_content_type'      => true,
			'headers_x_xss_protection'    => true,
			'headers_referrer_policy'     => 'strict-origin-when-cross-origin',
			'headers_csp_mode'            => 'compatible', // compatible, strict, custom, off
			'headers_csp_custom'          => '',
			'headers_permissions_policy'  => 'camera=(), microphone=(), geolocation=()',

			// Estatísticas de Visitantes & Privacidade
			'visitor_tracking_enabled'    => true,
			'ip_logging_enabled'          => true,
			'ip_anonymization_enabled'    => true,
			'ip_retention_days'           => 30,

			// Notifications
			'notify_email_enabled'        => true,
			'notify_email_critical_only'  => false,
			'notify_webhook_enabled'      => false,
			'notify_webhook_url'          => '',

			// Global & Maintenance
			'emergency_mode_active'       => false,
			'compatibility_mode'          => false,
			'log_retention_days'          => 30,
			'delete_data_on_uninstall'    => false,
		);
	}

	/**
	 * Retorna todas as configurações salvas ou uma chave específica
	 *
	 * @param string|null $key Chave desejada ou null para todas.
	 * @param mixed       $default Valor padrão caso a chave não exista.
	 * @return mixed
	 */
	public static function get( $key = null, $default = null ) {
		if ( null === self::$settings ) {
			$saved          = get_option( self::OPTION_KEY, array() );
			self::$settings = wp_parse_args( $saved, self::get_defaults() );
		}

		if ( null === $key ) {
			return self::$settings;
		}

		return isset( self::$settings[ $key ] ) ? self::$settings[ $key ] : $default;
	}

	/**
	 * Atualiza uma ou várias configurações no banco
	 *
	 * @param array $new_settings Array associativo de novas configurações.
	 * @return bool
	 */
	public static function update( array $new_settings ) {
		$current        = self::get();
		$sanitized      = self::sanitize_settings( $new_settings, $current );
		self::$settings = array_merge( $current, $sanitized );

		return update_option( self::OPTION_KEY, self::$settings );
	}

	/**
	 * Sanitização rigorosa de todos os inputs de configuração
	 *
	 * @param array $input Configurações enviadas.
	 * @param array $current Configurações existentes.
	 * @return array
	 */
	public static function sanitize_settings( array $input, array $current ) {
		$sanitized = array();

		foreach ( $input as $key => $val ) {
			switch ( $key ) {
				// Strings simples
				case 'admin_name':
				case 'locale':
				case 'site_id':
				case 'privacy_mode':
				case 'waf_level':
				case 'login_custom_slug':
				case 'login_secret_param':
				case 'login_secret_key':
				case 'headers_x_frame_options':
				case 'headers_referrer_policy':
				case 'headers_permissions_policy':
				case 'waf_mode':
				case 'antibot_mode':
				case 'scanner_frequency':
				case 'scanner_scan_type':
				case 'headers_csp_mode':
				case 'license_plan':
				case 'license_key':
				case 'license_status':
					$sanitized[ $key ] = sanitize_text_field( $val );
					break;

				// Arrays
				case 'waf_custom_exceptions':
					$sanitized[ $key ] = is_array( $val ) ? $val : array();
					break;

				// E-mails
				case 'admin_email':
				case 'cloud_account_email':
					$sanitized[ $key ] = sanitize_email( $val );
					break;

				// URLs
				case 'notify_webhook_url':
					$sanitized[ $key ] = esc_url_raw( $val );
					break;

				// Textareas / Blocos de texto
				case 'headers_csp_custom':
				case 'protect_hotlink_allowed_domains':
					$sanitized[ $key ] = sanitize_textarea_field( $val );
					break;

				// Inteiros
				case 'rate_limit_login_attempts':
				case 'rate_limit_login_lockout_min':
				case 'rate_limit_api_max_req':
				case 'rate_limit_general_max_req':
				case 'login_cookie_durateion':
				case 'two_factor_grace_period_days':
				case 'scanner_max_file_size_mb':
				case 'antibot_rate_limit_req':
				case 'log_retention_days':
				case 'ip_retention_days':
				case 'setup_step':
					$sanitized[ $key ] = intval( $val );
					break;

				// Arrays
				case 'two_factor_roles':
					$sanitized[ $key ] = is_array( $val ) ? array_map( 'sanitize_key', $val ) : array( 'administrator' );
					break;

				// Booleanos
				default:
					$sanitized[ $key ] = ! empty( $val );
					break;
			}
		}

		return $sanitized;
	}
}
