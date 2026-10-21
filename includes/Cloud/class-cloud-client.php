<?php
/**
 * Cliente de Integração com o SiteShield Cloud e Gestão de Licenças Fail-Safe
 * Comunicação segura HTTPS com Local License Cache e Tolerância a Falhas
 *
 * @package SiteShield Security
 * @author Mauriciokaleya
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
	const DEFAULT_CLOUD_URL = 'https://ngolabiz.it.ao/cloud';

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
			'site_id'         => (int) $site_id,
			'installation_id' => $installation_id,
			'domain'          => wp_parse_url( get_site_url(), PHP_URL_HOST ),
			'home_url'        => get_site_url(),
			'plugin_version'  => defined( 'SITESHIELD_VERSION' ) ? SITESHIELD_VERSION : '2.0.0',
			'wordpress_ver'   => get_bloginfo( 'version' ),
			'php_version'     => PHP_VERSION,
		);
	}

	/**
	 * Retorna as informações completas do plano e estado da licença atual
	 *
	 * @return array
	 */
	public static function get_license_info() {
		$key         = Settings::get( 'license_key', '' );
		$plan        = Settings::get( 'license_plan', 'FREE' );
		$status      = Settings::get( 'license_status', 'FREE' );
		$expires_at  = Settings::get( 'license_expires_at', null );
		$ver_id      = Settings::get( 'verification_id', '' );
		$account_id  = Settings::get( 'account_id', 0 );
		$site_id     = Settings::get( 'site_id', 0 );

		$days_remaining = null;
		$is_expired     = false;
		$in_grace       = false;

		if ( ! empty( $expires_at ) ) {
			$exp_time = strtotime( $expires_at );
			$now      = time();
			$diff     = $exp_time - $now;
			$days_remaining = (int) ceil( $diff / 86400 );

			if ( $days_remaining < 0 ) {
				$is_expired = true;
				// 7 dias de tolerância (Grace Period)
				if ( abs( $days_remaining ) <= 7 ) {
					$in_grace = true;
					$status   = 'IN_GRACE';
				} else {
					$status   = 'EXPIRED';
				}
			}
		}

		$is_free = empty( $key ) || 'FREE' === strtoupper( $plan );

		return array(
			'plan'                => strtoupper( $plan ),
			'plan_slug'           => strtolower( $plan ),
			'is_free'             => $is_free,
			'is_active'           => 'ACTIVE' === strtoupper( $status ) || $in_grace,
			'license_key'         => $key,
			'license_key_masked'  => $key ? substr( $key, 0, 7 ) . '••••••••' . substr( $key, -4 ) : 'Plano Gratuito (Sem Chave)',
			'status'              => $is_free ? 'FREE' : strtoupper( $status ),
			'expires_at'          => $expires_at,
			'days_remaining'      => $days_remaining,
			'is_expired'          => $is_expired,
			'in_grace'            => $in_grace,
			'verification_id'     => $ver_id,
			'site_id'             => $site_id,
			'account_id'          => $account_id,
			'cloud_connected'     => ! $is_free && ( 'ACTIVE' === strtoupper( $status ) || $in_grace ),
		);
	}

	/**
	 * Ativa uma License Key no SiteShield Cloud via API HTTPS
	 *
	 * @param string $license_key
	 * @return array
	 */
	public static function activate_license( $license_key ) {
		$key = trim( sanitize_text_field( $license_key ) );

		if ( empty( $key ) ) {
			return array(
				'success' => false,
				'message' => __( 'Por favor, insira uma chave de licença válida.', 'siteshield' ),
			);
		}

		$identity = self::get_identity();
		$endpoint = self::get_cloud_url() . '/api/v1/license/activate';

		$payload = array(
			'license_key'       => $key,
			'domain'            => $identity['domain'],
			'installation_id'   => $identity['installation_id'],
			'plugin_version'    => $identity['plugin_version'],
			'wordpress_version' => $identity['wordpress_ver'],
			'php_version'       => $identity['php_version'],
			'nonce'             => wp_create_nonce( 'ss_activate_' . $identity['installation_id'] ),
			'timestamp'         => time(),
		);

		$response = wp_remote_post( $endpoint, array(
			'timeout'   => 15,
			'sslverify' => true,
			'headers'   => array(
				'Content-Type' => 'application/json',
				'Accept'       => 'application/json',
			),
			'body'      => wp_json_encode( $payload ),
		) );

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => __( 'Não foi possível conectar ao SiteShield Cloud. Verifique sua conexão com a internet ou tente novamente em alguns instantes.', 'siteshield' ),
			);
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		$data       = ( is_array( $body ) && isset( $body['data'] ) && is_array( $body['data'] ) ) ? $body['data'] : ( is_array( $body ) ? $body : array() );
		$is_success = ( 200 === $code ) && ( ! empty( $body['success'] ) || ! empty( $data['success'] ) );

		if ( ! $is_success ) {
			$msg = ! empty( $data['message'] ) ? $data['message'] : ( ! empty( $body['message'] ) ? $body['message'] : __( 'Chave de licença inválida ou limite de websites atingido.', 'siteshield' ) );
			return array(
				'success' => false,
				'message' => $msg,
			);
		}

		$res = isset( $data['result'] ) ? $data['result'] : ( isset( $body['result'] ) ? $body['result'] : $data );
		$plan_slug = isset( $res['plan_slug'] ) ? strtoupper( $res['plan_slug'] ) : 'PRO';

		// Atualiza configurações locais
		Settings::update( array(
			'license_key'        => $key,
			'license_plan'       => $plan_slug,
			'license_status'     => 'ACTIVE',
			'license_expires_at' => isset( $res['expires_at'] ) ? $res['expires_at'] : null,
			'verification_id'    => isset( $res['verification_id'] ) ? $res['verification_id'] : '',
			'site_id'            => isset( $res['site_id'] ) ? (int) $res['site_id'] : 0,
			'account_id'         => isset( $res['account_id'] ) ? (int) $res['account_id'] : 0,
			'cloud_connected'    => true,
		) );

		// Salva no cache local (transient de 12 horas e option persistente)
		set_transient( 'siteshield_license_cache', $res, 12 * HOUR_IN_SECONDS );
		update_option( 'siteshield_license_cached_state', $res );

		Event_Center::record_event(
			'license_activated',
			Event_Center::SEV_INFO,
			sprintf( 'Licença %s ativada com sucesso pelo SiteShield Cloud.', $plan_slug )
		);

		return array(
			'success' => true,
			'plan'    => $plan_slug,
			'message' => sprintf(
				/* translators: %s: nome do plano */
				__( 'Licença %s ativada com sucesso!', 'siteshield' ),
				$plan_slug
			),
		);
	}

	/**
	 * Valida a licença com o Cloud utilizando Local License Cache Fail-Safe
	 *
	 * @param bool $force_refresh
	 * @return array
	 */
	public static function validate_license( $force_refresh = false ) {
		$license = self::get_license_info();

		if ( $license['is_free'] ) {
			return array( 'valid' => true, 'plan' => 'FREE', 'status' => 'FREE' );
		}

		// Se não for forçado e houver cache válido, retorna o cache
		if ( ! $force_refresh ) {
			$cached = get_transient( 'siteshield_license_cache' );
			if ( ! empty( $cached ) && is_array( $cached ) ) {
				return array_merge( array( 'valid' => true ), $cached );
			}
		}

		$identity = self::get_identity();
		$endpoint = self::get_cloud_url() . '/api/v1/license/validate';

		$payload = array(
			'license_key'     => $license['license_key'],
			'domain'          => $identity['domain'],
			'installation_id' => $identity['installation_id'],
			'timestamp'       => time(),
		);

		$response = wp_remote_post( $endpoint, array(
			'timeout'   => 10,
			'sslverify' => true,
			'headers'   => array( 'Content-Type' => 'application/json' ),
			'body'      => wp_json_encode( $payload ),
		) );

		// SE O CLOUD ESTIVER OFFLINE: USA O CACHE PERSISTENTE (FAIL-SAFE)
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$fallback = get_option( 'siteshield_license_cached_state' );
			if ( ! empty( $fallback ) && is_array( $fallback ) ) {
				return array_merge( array( 'valid' => true, 'from_fallback' => true ), $fallback );
			}
			// Se mesmo sem fallback, mantém o status local sem travar o WordPress
			return array(
				'valid'     => true,
				'plan'      => $license['plan'],
				'status'    => $license['status'],
				'in_grace'  => $license['in_grace'],
				'offline'   => true,
			);
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		$data = ( is_array( $body ) && isset( $body['data'] ) && is_array( $body['data'] ) ) ? $body['data'] : ( is_array( $body ) ? $body : array() );

		if ( empty( $body['success'] ) && ( ! isset( $data['success'] ) || empty( $data['success'] ) ) ) {
			// Licença realmente revogada ou expirada além do período de tolerância
			self::handle_license_expiration();
			return array(
				'valid'   => false,
				'message' => isset( $data['message'] ) ? $data['message'] : ( isset( $body['message'] ) ? $body['message'] : 'Licença expirada.' ),
			);
		}

		// Atualiza cache
		set_transient( 'siteshield_license_cache', $data, 12 * HOUR_IN_SECONDS );
		update_option( 'siteshield_license_cached_state', $data );

		return array_merge( array( 'valid' => true ), $data );
	}

	/**
	 * Desativa a licença no Cloud e reverte o site para o plano FREE
	 *
	 * @return array
	 */
	public static function deactivate_license() {
		$license  = self::get_license_info();
		$identity = self::get_identity();

		if ( ! empty( $license['license_key'] ) ) {
			$endpoint = self::get_cloud_url() . '/api/v1/license/deactivate';
			@wp_remote_post( $endpoint, array(
				'timeout' => 5,
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode( array(
					'license_key'     => $license['license_key'],
					'installation_id' => $identity['installation_id'],
				) ),
			) );
		}

		// Limpa dados locais e reverte para FREE
		Settings::update( array(
			'license_key'        => '',
			'license_plan'       => 'FREE',
			'license_status'     => 'FREE',
			'license_expires_at' => null,
			'verification_id'    => '',
			'cloud_connected'    => false,
			'badge_enabled'      => false, // Desativa o selo ao desativar a licença
		) );

		delete_transient( 'siteshield_license_cache' );
		delete_option( 'siteshield_license_cached_state' );

		Event_Center::record_event(
			'license_deactivated',
			Event_Center::SEV_INFO,
			'Licença desativada. Website revertido para o plano FREE.'
		);

		return array(
			'success' => true,
			'message' => __( 'Licença desativada com sucesso. O website retornou ao plano FREE.', 'siteshield' ),
		);
	}

	/**
	 * Heartbeat periódico e sincronização de notificações via WP-Cron
	 *
	 * @return bool
	 */
	public static function send_heartbeat() {
		$identity = self::get_identity();
		$license  = self::get_license_info();

		$endpoint = self::get_cloud_url() . '/api/v1/license/heartbeat';

		$payload = array(
			'installation_id'   => $identity['installation_id'],
			'domain'            => $identity['domain'],
			'license_key'       => $license['license_key'],
			'plugin_version'    => $identity['plugin_version'],
			'wordpress_version' => $identity['wordpress_ver'],
			'php_version'       => $identity['php_version'],
			'timestamp'         => time(),
		);

		$response = wp_remote_post( $endpoint, array(
			'timeout'   => 8,
			'sslverify' => true,
			'headers'   => array( 'Content-Type' => 'application/json' ),
			'body'      => wp_json_encode( $payload ),
		) );

		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$body   = json_decode( wp_remote_retrieve_body( $response ), true );
			$data   = ( is_array( $body ) && isset( $body['data'] ) && is_array( $body['data'] ) ) ? $body['data'] : ( is_array( $body ) ? $body : array() );
			$notifs = ! empty( $data['notifications'] ) ? $data['notifications'] : ( ! empty( $body['notifications'] ) ? $body['notifications'] : array() );

			if ( ! empty( $notifs ) && is_array( $notifs ) ) {
				update_option( 'siteshield_cloud_notifications', $notifs );
			}
			return true;
		}

		return false;
	}

	/**
	 * Trata a expiração da licença revertendo suavemente para FREE sem interromper o site
	 */
	public static function handle_license_expiration() {
		Settings::update( array(
			'license_plan'   => 'FREE',
			'license_status' => 'EXPIRED',
			'badge_enabled'  => false,
		) );

		delete_transient( 'siteshield_license_cache' );

		Event_Center::record_event(
			'license_expired',
			Event_Center::SEV_MEDIUM,
			'A licença expirou. As proteções locais essenciais continuam ativas no plano FREE.'
		);
	}
}
