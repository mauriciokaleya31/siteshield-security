<?php
/**
 * Cliente de Integração com o SiteShield Cloud e Gestão de Licenças Fail-Safe
 * Edição Demo Gratuita (Primeira Versão) — 100% Desbloqueada
 *
 * @package SiteShield_Security
 * @author Maurício Kaleya <kaleyapt@gmail.com>
 */

namespace SiteShield\Cloud;

use SiteShield\Core\Settings;
use SiteShield\Events\Event_Center;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cloud_Client {

	/**
	 * URL base oficial da API do SiteShield Cloud
	 */
	const DEFAULT_CLOUD_URL = 'https://github.com/mauriciokaleya31/siteshield-security';

	/**
	 * Retorna a URL base configurada para o SiteShield Cloud
	 *
	 * @return string
	 */
	public static function get_cloud_url() {
		$url = Settings::get( 'cloud_api_url', '' );
		if ( empty( $url ) ) {
			$url = defined( 'SITESHIELD_CLOUD_URL' ) ? SITESHIELD_CLOUD_URL : self::DEFAULT_CLOUD_URL;
		}
		return apply_filters( 'siteshield_cloud_url', untrailingslashit( $url ) );
	}

	/**
	 * Retorna os identificadores únicos desta instalação WordPress
	 *
	 * @return array
	 */
	public static function get_identity() {
		$installation_id = get_option( 'siteshield_installation_id' );
		if ( empty( $installation_id ) ) {
			$uuid = function_exists( 'wp_generate_uuid4' ) ? wp_generate_uuid4() : md5( uniqid( (string) mt_rand(), true ) );
			$installation_id = 'inst_' . hash( 'sha256', $uuid . get_site_url() );
			update_option( 'siteshield_installation_id', $installation_id );
		}

		$site_id = Settings::get( 'site_id', 0 );

		return array(
			'site_id'         => $site_id ? $site_id : 'ss_demo_1',
			'installation_id' => $installation_id,
			'domain'          => wp_parse_url( get_site_url(), PHP_URL_HOST ),
			'home_url'        => get_site_url(),
			'plugin_version'  => defined( 'SITESHIELD_VERSION' ) ? SITESHIELD_VERSION : '2.4.0',
			'wordpress_ver'   => get_bloginfo( 'version' ),
			'php_version'     => PHP_VERSION,
		);
	}

	/**
	 * Retorna as informações completas do plano e estado da licença atual.
	 * Na Primeira Versão (Modelo Demo Gratuito), todos os recursos vêm 100% liberados sem custo.
	 *
	 * @return array
	 */
	public static function get_license_info() {
		$key        = Settings::get( 'license_key', 'SS-DEMO-FREE-UNLOCKED' );
		$plan       = Settings::get( 'license_plan', 'FREE_DEMO' );
		$ver_id     = Settings::get( 'verification_id', '' );
		$account_id = Settings::get( 'account_id', 1 );
		$site_id    = Settings::get( 'site_id', 1 );

		if ( empty( $ver_id ) ) {
			$ver_id = 'SSV-DEMO-' . strtoupper( substr( md5( get_site_url() ), 0, 8 ) );
		}

		if ( empty( $plan ) || 'FREE' === strtoupper( $plan ) ) {
			$plan = 'DEMO FREE (1.ª VERSÃO)';
		}

		return array(
			'plan'                => strtoupper( $plan ),
			'plan_slug'           => 'free_demo',
			'is_free'             => true,
			'is_active'           => true,
			'license_key'         => $key ? $key : 'SS-DEMO-FREE-UNLOCKED',
			'license_key_masked'  => 'EDIÇÃO DEMO GRATUITA — 100% DESBLOQUEADO',
			'status'              => 'ACTIVE',
			'expires_at'          => null,
			'days_remaining'      => null,
			'is_expired'          => false,
			'in_grace'            => false,
			'verification_id'     => $ver_id,
			'site_id'             => $site_id,
			'account_id'          => $account_id,
			'cloud_connected'     => true,
		);
	}

	/**
	 * Ativa uma License Key (Na Edição Demo 1ª Versão, qualquer chave ou ativação gratuita desbloqueia tudo)
	 *
	 * @param string $license_key
	 * @return array
	 */
	public static function activate_license( $license_key = '' ) {
		$key = trim( sanitize_text_field( (string) $license_key ) );
		if ( empty( $key ) ) {
			$key = 'SS-DEMO-FREE-UNLOCKED';
		}

		$ver_id = 'SSV-DEMO-' . strtoupper( substr( md5( get_site_url() . $key ), 0, 8 ) );

		Settings::update( array(
			'license_key'        => $key,
			'license_plan'       => 'DEMO FREE (1.ª VERSÃO)',
			'license_status'     => 'ACTIVE',
			'license_expires_at' => null,
			'verification_id'    => $ver_id,
			'cloud_connected'    => true,
		) );

		Event_Center::record_event(
			'license_activated',
			Event_Center::SEV_INFO,
			'Edição Demo Gratuita (1.ª Versão) ativada com todas as funcionalidades liberadas.'
		);

		return array(
			'success' => true,
			'plan'    => 'DEMO FREE (1.ª VERSÃO)',
			'message' => __( 'Edição Demo Gratuita (1.ª Versão) ativa! Todas as funcionalidades estão 100% liberadas.', 'siteshield' ),
		);
	}

	/**
	 * Conecta o e-mail da conta ao SiteShield Cloud / Local Profile
	 *
	 * @param string $email
	 * @return array
	 */
	public static function connect_cloud_account( $email ) {
		$clean_email = sanitize_email( $email );
		if ( empty( $clean_email ) || ! is_email( $clean_email ) ) {
			return array(
				'success' => false,
				'message' => __( 'Por favor, informe um endereço de e-mail válido.', 'siteshield' ),
			);
		}

		Settings::update( array(
			'cloud_account_email' => $clean_email,
			'cloud_connected'     => true,
		) );

		return array(
			'success' => true,
			'message' => __( 'Conta vinculada com sucesso na Edição Demo Gratuita!', 'siteshield' ),
		);
	}

	/**
	 * Valida a licença (Sempre válida na Edição Demo Gratuita)
	 *
	 * @param bool $force_refresh
	 * @return array
	 */
	public static function validate_license( $force_refresh = false ) {
		return array(
			'valid'  => true,
			'plan'   => 'DEMO FREE (1.ª VERSÃO)',
			'status' => 'ACTIVE',
		);
	}

	/**
	 * Mantém a versão Demo Gratuita ativa mesmo ao resetar a chave
	 *
	 * @return array
	 */
	public static function deactivate_license() {
		Settings::update( array(
			'license_key'        => 'SS-DEMO-FREE-UNLOCKED',
			'license_plan'       => 'DEMO FREE (1.ª VERSÃO)',
			'license_status'     => 'ACTIVE',
			'license_expires_at' => null,
		) );

		return array(
			'success' => true,
			'message' => __( 'Configuração redefinida para a Edição Demo Gratuita (100% Desbloqueada).', 'siteshield' ),
		);
	}

	/**
	 * Heartbeat periódico (No-op seguro no modo Demo Gratuito)
	 *
	 * @return bool
	 */
	public static function send_heartbeat() {
		return true;
	}

	/**
	 * Trata expiração (Não aplicável na Edição Demo Gratuita)
	 */
	public static function handle_license_expiration() {
		return;
	}
}
