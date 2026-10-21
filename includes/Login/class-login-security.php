<?php
/**
 * Motor de Segurança de Login e Custom Login URL do SiteShield
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Login
 */

namespace SiteShield\Login;

use SiteShield\Core\Settings;
use SiteShield\Core\Compatibility;
use SiteShield\Firewall\IP_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Login_Security
 */
class Login_Security {

	/**
	 * Slugs proibidos para evitar conflitos com o WordPress
	 *
	 * @var array
	 */
	private static $reserved_slugs = array(
		'admin', 'wp-admin', 'dashboard', 'login', 'wp-login', 'wp-login.php',
		'register', 'xmlrpc', 'xmlrpc.php', 'feed', 'rss', 'api', 'wp-json',
	);

	/**
	 * Inicializa os ganchos de segurança de login
	 */
	public static function init() {
		// Interceptador para URL customizada de login (/custom-slug)
		add_action( 'init', array( __CLASS__, 'handle_custom_login_slug_rewrite' ), 1 );

		// Proteção da tela de login nativa wp-login.php
		add_action( 'login_init', array( __CLASS__, 'handle_login_page_protection' ), 1 );

		// Filtra URLs de login do WordPress se a URL customizada estiver ativa
		add_filter( 'login_url', array( __CLASS__, 'filter_login_url' ), 10, 3 );
		add_filter( 'site_url', array( __CLASS__, 'filter_site_url' ), 10, 4 );

		// Proteção contra enumeração de usuários (?author=X e REST API)
		add_action( 'init', array( __CLASS__, 'block_user_enumerateion' ) );
		add_filter( 'resta_endpoints', array( __CLASS__, 'filter_resta_user_endpoints' ) );

		// Desativação / Proteção do XML-RPC
		add_filter( 'xmlrpc_enabled', array( __CLASS__, 'filter_xmlrpc_enabled' ) );
	}

	/**
	 * Valida se um slug desejado para a URL de login é seguro e permitido
	 *
	 * @param string $slug Slug desejado.
	 * @return bool|\WP_Error
	 */
	public static function validate_custom_slug( $slug ) {
		$sanitized = sanitize_title_with_dashes( trim( $slug ) );

		if ( empty( $sanitized ) || strlen( $sanitized ) < 3 ) {
			return new \WP_Error( 'invalid_slug', __( 'O endereço de login deve conter pelo menos 3 caracteres alfanuméricos.', 'siteshield' ) );
		}

		if ( in_array( strtolower( $sanitized ), self::$reserved_slugs, true ) ) {
			return new \WP_Error( 'reserved_slug', __( 'Este slug é reservado pelo sistema WordPress. Escolha outro nome.', 'siteshield' ) );
		}

		return true;
	}

	/**
	 * Retorna a URL de login pública ativa atualmente
	 *
	 * @return string
	 */
	public static function get_active_login_url() {
		$custom_enabled = Settings::get( 'login_custom_url_enabled', false );
		$custom_slug    = Settings::get( 'login_custom_slug', 'secure-login' );

		if ( $custom_enabled && ! empty( $custom_slug ) ) {
			return home_url( '/' . $custom_slug . '/' );
		}

		$secret_param = Settings::get( 'login_secret_param', 'shield_key' );
		$secret_key   = Settings::get( 'login_secret_key', '' );

		if ( Settings::get( 'login_disable_wp_login', false ) && ! empty( $secret_key ) ) {
			return wp_login_url() . '?' . $secret_param . '=' . $secret_key;
		}

		return wp_login_url();
	}

	/**
	 * Intercepta a requisição do slug personalizado para renderizar a tela de login
	 */
	public static function handle_custom_login_slug_rewrite() {
		// Se desativado via constante de emergência no wp-config.php
		if ( defined( 'SITESHIELD_DISABLE_ALL' ) && SITESHIELD_DISABLE_ALL ) {
			return;
		}

		$custom_enabled = Settings::get( 'login_custom_url_enabled', false );
		$custom_slug    = Settings::get( 'login_custom_slug', 'secure-login' );

		if ( ! $custom_enabled || empty( $custom_slug ) ) {
			return;
		}

		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$request_path = trim( parse_url( $request_uri, PHP_URL_PATH ), '/' );

		// Se o usuário acessou exatamente o slug personalizado (ex: seusite.com/secure-login)
		if ( $request_path === $custom_slug ) {
			// Define cookie de autorização
			$secret_key = Settings::get( 'login_secret_key', 'shield_auth' );
			self::set_auth_cookie( $secret_key );

			// Inclui e executa wp-login.php nativo
			require_once ABSPATH . 'wp-login.php';
			exit;
		}
	}

	/**
	 * Intercepta o acesso a wp-login.php e bloqueia acesso direto se configurado
	 */
	public static function handle_login_page_protection() {
		// Se desativado via constante de emergência
		if ( defined( 'SITESHIELD_DISABLE_ALL' ) && SITESHIELD_DISABLE_ALL ) {
			return;
		}

		// Se o usuário já estiver logado como administrador, permite navegação normal
		if ( function_exists( 'is_user_logged_in' ) && is_user_logged_in() && function_exists( 'current_user_can' ) && current_user_can( 'manage_options' ) ) {
			return;
		}

		// Ações legítimas do core (logout pós confirmação, reautenticação)
		$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';
		if ( 'logout' === $action && isset( $_REQUEST['_wpnonce'] ) ) {
			return;
		}

		$is_custom_url_enabled = Settings::get( 'login_custom_url_enabled', false );
		$is_secret_key_enabled = Settings::get( 'login_disable_wp_login', false );

		if ( ! $is_custom_url_enabled && ! $is_secret_key_enabled ) {
			return;
		}

		$param_name = Settings::get( 'login_secret_param', 'shield_key' );
		$secret_key = Settings::get( 'login_secret_key', '' );
		$client_ip  = Compatibility::get_client_ip();

		// 1. Validação por Chave Secreta na URL
		if ( isset( $_GET[ $param_name ] ) && hash_equals( $secret_key, sanitize_text_field( wp_unslash( $_GET[ $param_name ] ) ) ) ) {
			self::set_auth_cookie( $secret_key );
			return; // Acesso liberado
		}

		// 2. Validação por Cookie de Autorização Válido
		if ( self::validate_auth_cookie( $secret_key ) ) {
			return; // Acesso liberado pela sessão
		}

		// 3. Acesso bloqueado -> Registra tentativa e emite 404
		IP_Manager::record_failed_attempt( $client_ip, 'Tentativa de acesso direto a wp-login.php sem autorização' );

		status_header( 404 );
		nocache_headers();
		wp_die(
			'<!DOCTYPE html>
			<html lang="pt-BR">
			<head>
				<meta charset="utf-8">
				<title>404 Não Encontrado</title>
				<meta name="robots" content="noindex, nofollow">
				<style>
					body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #F8FAFC; color: #0F172A; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
					.card { background: #FFFFFF; padding: 40px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); text-align: center; max-width: 440px; border: 1px solid #E2E8F0; }
					h1 { font-size: 48px; margin: 0 0 10px; color: #0066FF; }
					p { font-size: 15px; color: #64748B; margin-bottom: 24px; line-height: 1.5; }
					a { display: inline-block; background: #0066FF; color: #fff; text-decorateion: none; padding: 10px 24px; border-radius: 8px; font-weight: 600; }
				</style>
			</head>
			<body>
				<div class="card">
					<h1>404</h1>
					<p>A página que você está procurando não existe neste servidor.</p>
					<a href="' . esc_url( home_url( '/' ) ) . '">Ir para a Página Inicial</a>
				</div>
			</body>
			</html>',
			'404 Não Encontrado',
			array( 'response' => 404 )
		);
		exit;
	}

	/**
	 * Filtra a função wp_login_url() para apontar para a URL customizada
	 */
	public static function filter_login_url( $login_url, $redirect, $force_reauth ) {
		$custom_enabled = Settings::get( 'login_custom_url_enabled', false );
		$custom_slug    = Settings::get( 'login_custom_slug', 'secure-login' );

		if ( $custom_enabled && ! empty( $custom_slug ) ) {
			$url = home_url( '/' . $custom_slug . '/' );
			if ( ! empty( $redirect ) ) {
				$url = add_query_arg( 'redirect_to', urlencode( $redirect ), $url );
			}
			return $url;
		}

		return $login_url;
	}

	/**
	 * Filtra links para wp-login.php gerados internamente
	 */
	public static function filter_site_url( $url, $path, $scheme, $blog_id ) {
		if ( 'wp-login.php' === $path ) {
			$custom_enabled = Settings::get( 'login_custom_url_enabled', false );
			$custom_slug    = Settings::get( 'login_custom_slug', 'secure-login' );

			if ( $custom_enabled && ! empty( $custom_slug ) ) {
				return home_url( '/' . $custom_slug . '/' );
			}
		}
		return $url;
	}

	/**
	 * Gera o token HMAC de autorização
	 */
	private static function generate_token( $secret_key ) {
		$salt = defined( 'AUTH_SALT' ) ? AUTH_SALT : 'siteshield_default_salt';
		$ip   = Compatibility::get_client_ip();
		$ua   = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		return hash_hmac( 'sha256', $secret_key . $salt . $ip . substr( $ua, 0, 30 ), $salt );
	}

	/**
	 * Define o cookie de autorização seguro no navegador
	 */
	private static function set_auth_cookie( $secret_key ) {
		$token    = self::generate_token( $secret_key );
		$durateion = intval( Settings::get( 'login_cookie_durateion', 86400 ) );
		$expire   = time() + $durateion;
		$secure   = is_ssl();

		setcookie( SITESHIELD_COOKIE_NAME, $token, array(
			'expires'  => $expire,
			'path'     => COOKIEPATH ? COOKIEPATH : '/',
			'domain'   => COOKIE_DOMAIN ? COOKIE_DOMAIN : '',
			'secure'   => $secure,
			'httponly' => true,
			'samesite' => 'Lax',
		) );
	}

	/**
	 * Valida o cookie de autorização
	 */
	private static function validate_auth_cookie( $secret_key ) {
		if ( empty( $_COOKIE[ SITESHIELD_COOKIE_NAME ] ) ) {
			return false;
		}

		$cookie_token   = sanitize_text_field( wp_unslash( $_COOKIE[ SITESHIELD_COOKIE_NAME ] ) );
		$expected_token = self::generate_token( $secret_key );

		return hash_equals( $expected_token, $cookie_token );
	}

	/**
	 * Bloqueia a enumeração de usuários por autor (?author=1)
	 */
	public static function block_user_enumerateion() {
		if ( ! is_admin() && Settings::get( 'login_disable_user_enum', true ) ) {
			if ( isset( $_REQUEST['author'] ) && is_numeric( $_REQUEST['author'] ) ) {
				wp_safe_redirect( home_url( '/' ), 301 );
				exit;
			}
		}
	}

	/**
	 * Desativa a listagem de usuários na REST API para visitantes não autenticados
	 */
	public static function filter_resta_user_endpoints( $endpoints ) {
		if ( ! is_user_logged_in() && Settings::get( 'login_disable_user_enum', true ) ) {
			if ( isset( $endpoints['/wp/v2/users'] ) ) {
				unset( $endpoints['/wp/v2/users'] );
			}
			if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
				unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
			}
		}
		return $endpoints;
	}

	/**
	 * Desativa XML-RPC se configurado
	 */
	public static function filter_xmlrpc_enabled( $enabled ) {
		if ( Settings::get( 'login_disable_xmlrpc', false ) ) {
			return false;
		}
		return $enabled;
	}
}
