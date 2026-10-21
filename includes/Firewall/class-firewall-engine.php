<?php
/**
 * Motor de Firewall de Aplicação Web (WAF) do SiteShield
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Firewall
 */

namespace SiteShield\Firewall;

use SiteShield\Core\Settings;
use SiteShield\Core\Database;
use SiteShield\Core\Compatibility;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Firewall_Engine
 */
class Firewall_Engine {

	/**
	 * Padrões de ataque SQL Injection
	 *
	 * @var array
	 */
	private static $sqli_patterns = array(
		'/\b(union\s+all\s+select|union\s+select)\b/i',
		'/\b(select\s+.*\s+from\s+.*\s+where)\b/i',
		'/\b(concat\s*\(|group_concat\s*\(|load_file\s*\()/i',
		'/(\b(sleep|benchmark)\s*\(\s*\d+\s*\))/i',
		'/(\b(insert\s+into|delete\s+from|drop\s+table|alter\s+table)\b)/i',
		'/(\'|\")\s*(or|and)\s*(\'|\"|\d+)\s*=\s*(\'|\"|\d+)/i',
		'/(\-\-|\#|\/\*).*$/i',
	);

	/**
	 * Padrões de ataque Cross-Site Scripting (XSS)
	 *
	 * @var array
	 */
	private static $xss_patterns = array(
		'/<script\b[^>]*>(.*?)<\/script>/is',
		'/(javascript|vbscript|data):/i',
		'/(onload|onerror|onclick|onmouseover|onfocus|onblur)\s*=/i',
		'/<iframe\b[^>]*>/i',
		'/<object\b[^>]*>/i',
		'/<embed\b[^>]*>/i',
		'/document\.(cookie|location|write)/i',
	);

	/**
	 * Padrões de Path Traversal e LFI/RFI
	 *
	 * @var array
	 */
	private static $traversal_patterns = array(
		'/(\.\.\/|\.\.\\\\)/',
		'/(php:\/\/filter|php:\/\/input|data:\/\/text|zip:\/\/)/i',
		'/\b(\/etc\/passwd|\/etc\/shadow|\/proc\/self\/environ|win\.ini|boot\.ini)\b/i',
	);

	/**
	 * User Agents maliciosos e ferramentas de ataque conhecidas
	 *
	 * @var array
	 */
	private static $bad_user_agents = array(
		'sqlmap', 'nikto', 'acunetix', 'havij', 'w3af', 'masscan', 'zgrab', 'nmap',
		'dirbuster', 'gobuster', 'wfuzz', 'hydra', 'metasploit',
	);

	/**
	 * Inicializa a inspeção do Firewall
	 */
	public static function init() {
		add_action( 'plugins_loaded', array( __CLASS__, 'inspect_request' ), 1 );
	}

	/**
	 * Inspeciona os dados da requisição HTTP
	 */
	public static function inspect_request() {
		$mode = Settings::get( 'waf_mode', 'MONITOR' );
		if ( 'OFF' === $mode ) {
			return;
		}

		// Se for requisição de exceção de rota/IP ou compatibilidade com page builders
		if ( WAF_Exceptions::is_request_exempt() || Compatibility::is_page_builder_request() || Compatibility::is_woocommerce_safe_endpoint() ) {
			return;
		}

		$client_ip = Compatibility::get_client_ip();

		// 1. Verifica se o IP está explicitamente na lista branca (Allowlist)
		if ( IP_Manager::is_ip_allowed( $client_ip ) ) {
			return;
		}

		// 2. Verifica se o IP está bloqueado
		if ( IP_Manager::is_ip_blocked( $client_ip ) ) {
			self::execute_block( $client_ip, 'IP bloqueado na lista de bloqueios', 'high', 'IP_BLOCKLIST' );
			return;
		}

		// 3. Inspeção de User Agent
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		if ( Settings::get( 'waf_block_bad_user_agents', true ) && ! empty( $user_agent ) ) {
			foreach ( self::$bad_user_agents as $bad_agent ) {
				if ( false !== stripos( $user_agent, $bad_agent ) ) {
					self::handle_threat( $client_ip, 'User Agent Malicioso Detectado', $user_agent, 'high', 'BAD_USER_AGENT' );
					return;
				}
			}
		}

		// 4. Coleta todos os inputs de entrada para análise (URI, GET, POST, COOKIE)
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$payloads    = array(
			'URI'    => $request_uri,
			'GET'    => self::flatten_input( $_GET ),
			'POST'   => self::flatten_input( $_POST ),
			'COOKIE' => self::flatten_input( $_COOKIE ),
		);

		foreach ( $payloads as $source => $value ) {
			if ( empty( $value ) ) {
				continue;
			}

			// Verificação de SQLi
			if ( Settings::get( 'waf_block_sqli', true ) ) {
				foreach ( self::$sqli_patterns as $pattern ) {
					if ( preg_match( $pattern, $value ) ) {
						self::handle_threat( $client_ip, "Padrão de SQL Injection em {$source}", $value, 'critical', 'SQL_INJECTION' );
						return;
					}
				}
			}

			// Verificação de XSS
			if ( Settings::get( 'waf_block_xss', true ) ) {
				foreach ( self::$xss_patterns as $pattern ) {
					if ( preg_match( $pattern, $value ) ) {
						self::handle_threat( $client_ip, "Padrão de Cross-Site Scripting (XSS) em {$source}", $value, 'high', 'XSS_ATTACK' );
						return;
					}
				}
			}

			// Verificação de Path Traversal / LFI
			if ( Settings::get( 'waf_block_path_traversal', true ) ) {
				foreach ( self::$traversal_patterns as $pattern ) {
					if ( preg_match( $pattern, $value ) ) {
						self::handle_threat( $client_ip, "Tentativa de Path Traversal / LFI em {$source}", $value, 'critical', 'PATH_TRAVERSAL' );
						return;
					}
				}
			}
		}
	}

	/**
	 * Trata a ameaça detectada de acordo com o modo do WAF (MONITOR ou BLOCK)
	 *
	 * @param string $ip IP de origem.
	 * @param string $reason Motivo da detecção.
	 * @param string $payload Trecho inspecionado.
	 * @param string $severity Nível de severidade.
	 * @param string $rule_id Identificador da regra.
	 */
	private static function handle_threat( $ip, $reason, $payload, $severity, $rule_id ) {
		$mode = Settings::get( 'waf_mode', 'MONITOR' );

		self::log_event( $ip, $reason, $payload, $severity, $rule_id, $mode );

		if ( 'BLOCK' === $mode ) {
			// Incrementa bloqueio no IP Manager
			IP_Manager::record_failed_attempt( $ip, $reason );
			self::execute_block( $ip, $reason, $severity, $rule_id );
		}
	}

	/**
	 * Interrompe a requisição com código 403 Forbidden e tela segura
	 *
	 * @param string $ip IP de origem.
	 * @param string $reason Motivo do bloqueio.
	 * @param string $severity Severidade.
	 * @param string $rule_id Regra acionada.
	 */
	private static function execute_block( $ip, $reason, $severity, $rule_id ) {
		status_header( 403 );
		nocache_headers();

		wp_die(
			'<!DOCTYPE html>
			<html lang="pt-BR">
			<head>
				<meta charset="utf-8">
				<title>403 Acesso Bloqueado — SiteShield Security</title>
				<meta name="robots" content="noindex, nofollow">
				<style>
					body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #F8FAFC; color: #0F172A; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
					.card { background: #FFFFFF; padding: 40px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0, 102, 255, 0.08); text-align: center; max-width: 480px; border: 1px solid #E2E8F0; }
					.icon { width: 56px; height: 56px; background: #FEF2F2; color: #EF4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 24px; }
					h1 { font-size: 24px; margin: 0 0 10px; color: #0F172A; }
					p { font-size: 14px; color: #64748B; margin-bottom: 20px; line-height: 1.5; }
					.details { background: #F1F5F9; padding: 12px; border-radius: 8px; font-size: 12px; color: #475569; margin-bottom: 24px; text-align: left; }
					a { display: inline-block; background: #0066FF; color: #fff; text-decoration: none; padding: 10px 24px; border-radius: 8px; font-weight: 600; }
				</style>
			</head>
			<body>
				<div class="card">
					<div class="icon">🛡️</div>
					<h1>Acesso Interrompido pelo Firewall</h1>
					<p>Sua requisição foi identificada como potencialmente suspeita e bloqueada pelas políticas de segurança do <strong>SiteShield WAF</strong>.</p>
					<div class="details">
						<strong>Endereço IP:</strong> ' . esc_html( $ip ) . '<br>
						<strong>Código do Evento:</strong> ' . esc_html( $rule_id ) . '<br>
						<strong>Data/Hora:</strong> ' . esc_html( current_time( 'mysql' ) ) . '
					</div>
					<a href="' . esc_url( home_url( '/' ) ) . '">Voltar para o Site</a>
				</div>
			</body>
			</html>',
			'403 Acesso Proibido',
			array( 'response' => 403 )
		);
		exit;
	}

	/**
	 * Registra o evento de firewall no banco de dados
	 */
	private static function log_event( $ip, $reason, $payload, $severity, $rule_id, $action ) {
		global $wpdb;
		$tables = Database::get_table_names();

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$tables['events']}'" ) === $tables['events'] ) {
			$wpdb->insert(
				$tables['events'],
				array(
					'ip_address'     => $ip,
					'event_type'     => 'waf_rule',
					'rule_triggered' => $rule_id . ': ' . substr( $reason, 0, 80 ),
					'severity'       => $severity,
					'action_taken'   => $action,
					'request_method' => isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : 'GET',
					'request_uri'    => isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '',
					'payload'        => substr( $payload, 0, 500 ),
					'user_agent'     => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
					'created_at'     => current_time( 'mysql' ),
				)
			);
		}
	}

	/**
	 * Achata e sanitiza arrays de entrada recursivamente em uma string única para inspeção rápida
	 *
	 * @param array $array Array superglobal.
	 * @return string
	 */
	private static function flatten_input( $array ) {
		if ( empty( $array ) || ! is_array( $array ) ) {
			return '';
		}

		$result = '';
		array_walk_recursive( $array, function( $val, $key ) use ( &$result ) {
			if ( is_scalar( $val ) ) {
				$result .= ' ' . $key . '=' . $val;
			}
		});

		return $result;
	}
}
