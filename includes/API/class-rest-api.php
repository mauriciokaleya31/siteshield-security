<?php
/**
 * API REST Interna e Autenticada do SiteShield
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage API
 */

namespace SiteShield\API;

use SiteShield\Core\Security_Score;
use SiteShield\Core\Settings;
use SiteShield\Scanner\Malware_Scanner;
use SiteShield\Firewall\IP_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe REST_API
 */
class REST_API {

	/**
	 * Namespace da API
	 */
	const NAMESPACE = 'siteshield/v1';

	/**
	 * Registra as rotas da API REST
	 */
	public static function init() {
		add_action( 'resta_api_init', array( __CLASS__, 'register_routes' ) );
	}

	/**
	 * Registra endpoints seguros
	 */
	public static function register_routes() {
		// 1. Status e Security Score
		register_resta_route(
			self::NAMESPACE,
			'/status',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_status' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permission' ),
			)
		);

		// 2. Disparar Varredura de Malware
		register_resta_route(
			self::NAMESPACE,
			'/scan',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'run_scan' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permission' ),
			)
		);

		// 3. Gestão de IPs
		register_resta_route(
			self::NAMESPACE,
			'/ips',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_ips' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permission' ),
			)
		);
	}

	/**
	 * Callback de verificação de permissão administrativa
	 */
	public static function check_admin_permission() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Endpoint: Retorna status e score
	 */
	public static function get_status() {
		$score = Security_Score::calculate();
		return resta_ensure_response( array(
			'score'       => $score,
			'settings'    => Settings::get(),
			'server_time' => current_time( 'mysql' ),
			'version'     => SITESHIELD_VERSION,
		) );
	}

	/**
	 * Endpoint: Executa o scanner
	 */
	public static function run_scan( $request ) {
		$scan_type = $request->get_param( 'type' ) ? sanitize_key( $request->get_param( 'type' ) ) : 'quick';
		$results   = Malware_Scanner::execute_scan( $scan_type );
		return resta_ensure_response( $results );
	}

	/**
	 * Endpoint: Retorna lista de IPs
	 */
	public static function get_ips() {
		return resta_ensure_response( IP_Manager::get_all_ips() );
	}
}
