<?php
/**
 * Motor de Proteção Contra Bots Maliciosos e Anti-Scraping do SiteShield
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage AntiBot
 */

namespace SiteShield\AntiBot;

use SiteShield\Core\Settings;
use SiteShield\Core\Compatibility;
use SiteShield\Firewall\IP_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe AntiBot_Engine
 */
class AntiBot_Engine {

	/**
	 * Lista de assinaturas de bots legítimos e motores de busca (Allowlist)
	 *
	 * @var array
	 */
	private static $good_bots = array(
		'Googlebot', 'bingbot', 'Baiduspider', 'YandexBot', 'DuckDuckBot',
		'facebookexternalhit', 'Twitterbot', 'Pinterestbot', 'LinkedInBot',
		'WhatsApp', 'TelegramBot', 'Applebot',
	);

	/**
	 * Inicializa o motor Anti-Bot
	 */
	public static function init() {
		if ( ! Settings::get( 'antibot_enabled', true ) ) {
			return;
		}

		add_action( 'init', array( __CLASS__, 'analyze_traffic' ), 2 );
	}

	/**
	 * Analisa padrões de tráfego para identificar bots maliciosos e scrapers
	 */
	public static function analyze_traffic() {
		$is_cron = function_exists( 'wp_doing_cron' ) ? wp_doing_cron() : ( defined( 'DOING_CRON' ) && DOING_CRON );
		if ( is_admin() || $is_cron || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}

		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		$client_ip  = Compatibility::get_client_ip();

		// 1. Permite bots legítimos de busca
		foreach ( self::$good_bots as $good_bot ) {
			if ( false !== stripos( $user_agent, $good_bot ) ) {
				return;
			}
		}

		// 2. Bloqueia requisições sem User-Agent (típico de scrapers amadores)
		if ( empty( $user_agent ) ) {
			self::handle_bot( $client_ip, 'Requisição suspeita sem cabeçalho User-Agent' );
			return;
		}

		// 3. Rate limiting contra scraping rápido por IP
		$limit = intval( Settings::get( 'antibot_rate_limit_req', 30 ) );
		$key   = 'ss_bot_' . md5( $client_ip );
		$count = intval( get_transient( $key ) );

		if ( $count >= $limit ) {
			self::handle_bot( $client_ip, 'Comportamento automatizado de scraping detectado (muitas páginas/segundo)' );
			return;
		}

		set_transient( $key, $count + 1, 30 );
	}

	/**
	 * Tratea ação para o bot identificado
	 */
	private static function handle_bot( $ip, $reason ) {
		$mode = Settings::get( 'antibot_mode', 'MONITOR' );

		if ( 'BLOCK' === $mode ) {
			IP_Manager::block_ip_temporary( $ip, $reason, 30 );
			status_header( 403 );
			nocache_headers();
			wp_die( '<h1>403 Acesso Negado</h1><p>Acesso automatizado bloqueado pelo SiteShield Anti-Bot.</p>', '403 Bot Blocked', array( 'response' => 403 ) );
			exit;
		}
	}
}
