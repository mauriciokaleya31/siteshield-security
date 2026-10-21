<?php
/**
 * Motor de Cálculo Inteligente do Security Score do SiteShield
 * Em conformidade com OWASP e Princípio "No False Security"
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Core
 */

namespace SiteShield\Core;

use SiteShield\Diagnostics\Security_Diagnostics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Security_Score
 */
class Security_Score {

	/**
	 * Realiza o cálculo completo e detalhado da pontuação de segurança
	 *
	 * @return array
	 */
	public static function calculate() {
		$score    = 0;
		$factors  = array();
		$settings = Settings::get();

		// 1. Segurança do Login (Peso: 20%)
		$login_score = 0;
		if ( ! empty( $settings['login_custom_url_enabled'] ) || ! empty( $settings['login_secret_key'] ) ) {
			$login_score += 8;
			$factors[]    = array( 'name' => __( 'Login Oculto / URL Protegida', 'siteshield' ), 'score' => 8, 'status' => 'pass' );
		} else {
			$factors[] = array( 'name' => __( 'Login Oculto / URL Protegida', 'siteshield' ), 'score' => 0, 'status' => 'fail', 'fix' => __( 'Ative a URL personalizada de login para mitigar ataques automatizados.', 'siteshield' ) );
		}

		if ( ! empty( $settings['two_factor_enabled'] ) ) {
			$login_score += 7;
			$factors[]    = array( 'name' => __( 'Autenticação 2FA TOTP', 'siteshield' ), 'score' => 7, 'status' => 'pass' );
		} else {
			$factors[] = array( 'name' => __( 'Autenticação 2FA TOTP', 'siteshield' ), 'score' => 0, 'status' => 'warn', 'fix' => __( 'Habilite a autenticação de dois fatores para contas de administrador.', 'siteshield' ) );
		}

		if ( ! empty( $settings['rate_limit_enabled'] ) ) {
			$login_score += 5;
			$factors[]    = array( 'name' => __( 'Proteção Contra Força Bruta', 'siteshield' ), 'score' => 5, 'status' => 'pass' );
		}
		$score += $login_score;

		// 2. Firewall WAF & Rate Limiting (Peso: 25%)
		$waf_score = 0;
		if ( 'BLOCK' === $settings['waf_mode'] ) {
			$waf_score += 20;
			$factors[]  = array( 'name' => __( 'Firewall WAF em Modo de Bloqueio', 'siteshield' ), 'score' => 20, 'status' => 'pass' );
		} elseif ( 'MONITOR' === $settings['waf_mode'] ) {
			$waf_score += 12;
			$factors[]  = array( 'name' => __( 'Firewall WAF em Modo Monitor', 'siteshield' ), 'score' => 12, 'status' => 'warn', 'fix' => __( 'Altere o Firewall para Modo de Bloqueio após calibragem.', 'siteshield' ) );
		} else {
			$factors[] = array( 'name' => __( 'Firewall WAF (Desativado)', 'siteshield' ), 'score' => 0, 'status' => 'fail', 'fix' => __( 'Ative o Firewall WAF para proteção contra SQLi e XSS.', 'siteshield' ) );
		}

		if ( ! empty( $settings['antibot_enabled'] ) ) {
			$waf_score += 5;
			$factors[]  = array( 'name' => __( 'Proteção Anti-Bot & Anti-Scraping', 'siteshield' ), 'score' => 5, 'status' => 'pass' );
		}
		$score += $waf_score;

		// 3. Scanner de Malware & Integridade (Peso: 25%)
		$scan_score = 0;
		global $wpdb;
		$tables = Database::get_table_names();

		$critical_threats = 0;
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$tables['scan_findings']}'" ) === $tables['scan_findings'] ) {
			$critical_threats = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$tables['scan_findings']} WHERE status = 'active' AND severity IN ('high', 'critical')" ) );
		}

		$last_scan = get_option( 'siteshield_last_scan_time', 0 );
		$days_since_scan = $last_scan ? ( ( time() - $last_scan ) / DAY_IN_SECONDS ) : 999;

		if ( 0 === $critical_threats && $days_since_scan <= 7 ) {
			$scan_score += 15;
			$factors[]   = array( 'name' => __( 'Análise Heurística Recente Sem Ameaças', 'siteshield' ), 'score' => 15, 'status' => 'pass' );
		} elseif ( $critical_threats > 0 ) {
			$factors[] = array( 'name' => __( 'Ameaças de Malware Detetadas', 'siteshield' ), 'score' => 0, 'status' => 'fail', 'fix' => sprintf( _n( '%d ficheiro suspeito detetado no scanner.', '%d ficheiros suspeitos detetados no scanner.', $critical_threats, 'siteshield' ), $critical_threats ) );
		} else {
			$factors[] = array( 'name' => __( 'Análise de Malware Pendente', 'siteshield' ), 'score' => 5, 'status' => 'warn', 'fix' => __( 'Execute uma análise no Scanner de Malware.', 'siteshield' ) );
			$scan_score += 5;
		}

		if ( ! empty( $settings['integrity_monitor_enabled'] ) ) {
			$scan_score += 10;
			$factors[]   = array( 'name' => __( 'Monitor de Integridade de Ficheiros (SHA-256)', 'siteshield' ), 'score' => 10, 'status' => 'pass' );
		}
		$score += $scan_score;

		// 4. Cabeçalhos HTTP de Segurança & SSL (Peso: 15%)
		$headers_score = 0;
		if ( is_ssl() ) {
			$headers_score += 5;
			$factors[]      = array( 'name' => __( 'Conexão Criptografada HTTPS / SSL', 'siteshield' ), 'score' => 5, 'status' => 'pass' );
		} else {
			$factors[] = array( 'name' => __( 'Certificado SSL Ausente', 'siteshield' ), 'score' => 0, 'status' => 'fail', 'fix' => __( 'Instale e configure um certificado SSL HTTPS.', 'siteshield' ) );
		}

		if ( ! empty( $settings['headers_enabled'] ) ) {
			$headers_score += 10;
			$factors[]      = array( 'name' => __( 'Cabeçalhos de Segurança (CSP, HSTS, X-Frame)', 'siteshield' ), 'score' => 10, 'status' => 'pass' );
		} else {
			$factors[] = array( 'name' => __( 'Cabeçalhos HTTP de Segurança', 'siteshield' ), 'score' => 0, 'status' => 'warn', 'fix' => __( 'Ative os Cabeçalhos HTTP de Segurança.', 'siteshield' ) );
		}
		$score += $headers_score;

		// 5. Atualização do WordPress & Ambiente (Peso: 15%)
		$env_score = 0;
		if ( version_compare( PHP_VERSION, '7.4', '>=' ) ) {
			$env_score += 5;
			$factors[]  = array( 'name' => __( 'Ambiente PHP Suportado', 'siteshield' ), 'score' => 5, 'status' => 'pass' );
		} else {
			$factors[] = array( 'name' => __( 'Versão do PHP Obsoleta', 'siteshield' ), 'score' => 0, 'status' => 'fail', 'fix' => __( 'Atualize a versão do PHP para 8.0 ou superior.', 'siteshield' ) );
		}

		$updates = get_site_transient( 'update_core' );
		$has_wp_update = false;
		if ( ! empty( $updates ) && is_object( $updates ) && ! empty( $updates->updates ) && is_array( $updates->updates ) ) {
			if ( isset( $updates->updates[0] ) && is_object( $updates->updates[0] ) && isset( $updates->updates[0]->response ) ) {
				$has_wp_update = ( 'upgrade' === $updates->updates[0]->response );
			}
		}
		if ( ! $has_wp_update ) {
			$env_score += 5;
			$factors[]  = array( 'name' => __( 'Núcleo do WordPress Atualizado', 'siteshield' ), 'score' => 5, 'status' => 'pass' );
		} else {
			$factors[] = array( 'name' => __( 'Atualização do WordPress Pendente', 'siteshield' ), 'score' => 0, 'status' => 'warn', 'fix' => __( 'Atualize o WordPress para a versão mais recente.', 'siteshield' ) );
		}

		if ( ! empty( $settings['content_protection_enabled'] ) ) {
			$env_score += 5;
			$factors[]  = array( 'name' => __( 'Proteção de Conteúdo & Anti-Clone', 'siteshield' ), 'score' => 5, 'status' => 'pass' );
		}
		$score += $env_score;

		$final_score = min( 100, max( 0, $score ) );

		$label = __( 'Crítico', 'siteshield' );
		if ( $final_score >= 85 ) {
			$label = __( 'Excelente', 'siteshield' );
		} elseif ( $final_score >= 70 ) {
			$label = __( 'Boa Proteção', 'siteshield' );
		} elseif ( $final_score >= 50 ) {
			$label = __( 'Atenção Necessária', 'siteshield' );
		}

		return array(
			'score'   => $final_score,
			'label'   => $label,
			'factors' => $factors,
		);
	}
}
