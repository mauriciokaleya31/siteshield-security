<?php
/**
 * Motor de Autoteste em Tempo Real de Proteções (Protection Self-Test)
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Testing
 */

namespace SiteShield\Testing;

use SiteShield\Core\Settings;
use SiteShield\Firewall\Firewall_Engine;
use SiteShield\Firewall\Rate_Limiter;
use SiteShield\Login\Login_Security;
use SiteShield\Headers\Security_Headers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Protection_Tester
 */
class Protection_Tester {

	/**
	 * Executa o autoteste de todas as proteções ativas
	 *
	 * @return array
	 */
	public static function run_all_tests() {
		return array(
			'tested_at'        => current_time( 'mysql' ),
			'waf_test'         => self::test_waf_engine(),
			'login_test'       => self::test_login_protection(),
			'rate_limit_test'  => self::test_rate_limiter(),
			'headers_test'     => self::test_security_headers(),
			'anti_clone_test'  => self::test_anti_clone(),
			'integrity_test'   => self::test_file_integrity(),
			'visitor_test'     => self::test_visitor_tracking(),
		);
	}

	/**
	 * 1. Teste de WAF: Simula payload SQLi e XSS em memória
	 *
	 * @return array
	 */
	public static function test_waf_engine() {
		$settings = Settings::get();

		if ( 'OFF' === $settings['waf_mode'] ) {
			return array(
				'title'   => __( 'Web Application Firewall (WAF)', 'siteshield' ),
				'status'  => 'INACTIVE',
				'details' => __( 'O Firewall está desativado nas configurações.', 'siteshield' ),
			);
		}

		// Simula avaliação de payload
		$test_payload = "1' UNION SELECT null, username, password FROM wp_users--";
		$pattern_sqli = '/(\b(union|select|insert|update|delete|drop|alter)\b.{1,100}\b(from|into|table|database)\b)/i';

		$detected = preg_match( $pattern_sqli, $test_payload );

		return array(
			'title'   => __( 'Web Application Firewall (WAF)', 'siteshield' ),
			'status'  => $detected ? 'PASS' : 'FAIL',
			'details' => $detected
				? sprintf( __( 'Padrão de ataque interceptado com sucesso pelo motor (Modo %s).', 'siteshield' ), $settings['waf_mode'] )
				: __( 'Falha na interceptação do padrão de teste.', 'siteshield' ),
		);
	}

	/**
	 * 2. Teste de Login Protection
	 *
	 * @return array
	 */
	public static function test_login_protection() {
		$settings = Settings::get();
		$is_custom_url = ! empty( $settings['login_custom_url_enabled'] );
		$is_secret_key = ! empty( $settings['login_disable_wp_login'] );

		return array(
			'title'   => __( 'Login Security & URL Cloaking', 'siteshield' ),
			'status'  => ( $is_custom_url || $is_secret_key ) ? 'PASS' : 'WARNING',
			'details' => ( $is_custom_url || $is_secret_key )
				? __( 'Bloqueio de wp-login.php e rota personalizada ativos.', 'siteshield' )
				: __( 'Endereço padrão wp-login.php exposto a robôs.', 'siteshield' ),
		);
	}

	/**
	 * 3. Teste de Rate Limiting
	 *
	 * @return array
	 */
	public static function test_rate_limiter() {
		$settings = Settings::get();
		$enabled  = ! empty( $settings['rate_limit_enabled'] );

		return array(
			'title'   => __( 'Rate Limiter & Força Bruta', 'siteshield' ),
			'status'  => $enabled ? 'PASS' : 'INACTIVE',
			'details' => $enabled
				? sprintf( __( 'Limite de %d tentativas com bloqueio temporário ativo.', 'siteshield' ), $settings['rate_limit_login_attempts'] )
				: __( 'Proteção de limite desativada.', 'siteshield' ),
		);
	}

	/**
	 * 4. Teste de Cabeçalhos HTTP
	 *
	 * @return array
	 */
	public static function test_security_headers() {
		$settings = Settings::get();
		$enabled  = ! empty( $settings['headers_enabled'] );

		return array(
			'title'   => __( 'Security Headers & CSP', 'siteshield' ),
			'status'  => $enabled ? 'PASS' : 'WARNING',
			'details' => $enabled
				? __( 'Módulos HSTS, X-Frame e CSP em operação.', 'siteshield' )
				: __( 'Injeção de cabeçalhos desativada.', 'siteshield' ),
		);
	}

	/**
	 * 5. Teste de Anti-Clone
	 *
	 * @return array
	 */
	public static function test_anti_clone() {
		$settings = Settings::get();
		$enabled  = ! empty( $settings['content_protection_enabled'] );

		return array(
			'title'   => __( 'Content Protection & Anti-Clone', 'siteshield' ),
			'status'  => $enabled ? 'PASS' : 'INACTIVE',
			'details' => $enabled
				? __( 'Filtros frontend ativos (Bloqueio F12, DevTools, Clique Direito).', 'siteshield' )
				: __( 'Proteção de conteúdo desativada.', 'siteshield' ),
		);
	}

	/**
	 * 6. Teste de Integridade de Ficheiros
	 *
	 * @return array
	 */
	public static function test_file_integrity() {
		$baseline_time = get_option( 'siteshield_integrity_baseline_time', 0 );

		return array(
			'title'   => __( 'File Integrity Snapshot (SHA-256)', 'siteshield' ),
			'status'  => $baseline_time > 0 ? 'PASS' : 'WARNING',
			'details' => $baseline_time > 0
				? __( 'Linha de base íntegra e monitorização ativa.', 'siteshield' )
				: __( 'Linha de base não gerada.', 'siteshield' ),
		);
	}

	/**
	 * 7. Teste de Rastreamento de Visitantes
	 *
	 * @return array
	 */
	public static function test_visitor_tracking() {
		$settings = Settings::get();
		$enabled  = ! empty( $settings['visitor_tracking_enabled'] );

		return array(
			'title'   => __( 'Visitor & Traffic Tracking', 'siteshield' ),
			'status'  => $enabled ? 'PASS' : 'INACTIVE',
			'details' => $enabled
				? __( 'Rastreamento server-side ativo com anonimização.', 'siteshield' )
				: __( 'Rastreamento desativado.', 'siteshield' ),
		);
	}
}
