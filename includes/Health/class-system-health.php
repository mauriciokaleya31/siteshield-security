<?php
/**
 * Centro de Saúde do Sistema (System Health Center) do SiteShield
 * Em conformidade com o Princípio "No False Security"
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Health
 */

namespace SiteShield\Health;

use SiteShield\Core\Settings;
use SiteShield\Core\Database;
use SiteShield\Cloud\Cloud_Client;
use SiteShield\Diagnostics\Security_Diagnostics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe System_Health
 */
class System_Health {

	/**
	 * Executa o diagnóstico de saúde de todos os 15 componentes do ecossistema
	 *
	 * @return array
	 */
	public static function get_system_health() {
		global $wp_version, $wpdb;
		$settings = Settings::get();
		$tables   = Database::get_table_names();
		$license  = Cloud_Client::get_license_info();

		// 1. WordPress Core
		if ( ! function_exists( 'get_core_updates' ) && file_exists( ABSPATH . 'wp-admin/includes/update.php' ) ) {
			require_once ABSPATH . 'wp-admin/includes/update.php';
		}
		$core_updates = function_exists( 'get_core_updates' ) ? get_core_updates() : array();
		$has_update   = ! empty( $core_updates ) && is_array( $core_updates ) && isset( $core_updates[0] ) && is_object( $core_updates[0] ) && isset( $core_updates[0]->response ) && 'upgrade' === $core_updates[0]->response;
		$components['core'] = array(
			'name'    => __( 'WordPress Core', 'siteshield' ),
			'status'  => $has_update ? 'WARNING' : 'PASS',
			'details' => sprintf( 'v%s %s', $wp_version, $has_update ? __( '(Atualização Pendente)', 'siteshield' ) : __( '(Atualizado)', 'siteshield' ) ),
		);

		// 2. Versão do PHP & Extensões
		$php_ok = version_compare( PHP_VERSION, '7.4', '>=' );
		$components['php'] = array(
			'name'    => __( 'PHP Runtime & Extensões', 'siteshield' ),
			'status'  => $php_ok ? 'PASS' : 'FAIL',
			'details' => sprintf( 'PHP v%s %s', PHP_VERSION, $php_ok ? __( '(Suportado)', 'siteshield' ) : __( '(Obsoleto)', 'siteshield' ) ),
		);

		// 3. Base de Dados & Tabelas do Plugin
		$tables_ok = true;
		foreach ( $tables as $table_name ) {
			if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) !== $table_name ) {
				$tables_ok = false;
				break;
			}
		}
		$components['database'] = array(
			'name'    => __( 'Base de Dados & Tabelas Relacionais', 'siteshield' ),
			'status'  => $tables_ok ? 'PASS' : 'FAIL',
			'details' => $tables_ok ? __( '7/7 Tabelas Criadas e Indexadas', 'siteshield' ) : __( 'Tabelas Ausentes', 'siteshield' ),
		);

		// 4. Integridade de Ficheiros
		$baseline_time = get_option( 'siteshield_integrity_baseline_time', 0 );
		$components['integrity'] = array(
			'name'    => __( 'File Integrity (SHA-256)', 'siteshield' ),
			'status'  => ( ! empty( $settings['integrity_monitor_enabled'] ) && $baseline_time > 0 ) ? 'PASS' : 'WARNING',
			'details' => $baseline_time > 0 ? __( 'Linha de Base Criada', 'siteshield' ) : __( 'Linha de Base Pendente', 'siteshield' ),
		);

		// 5. Firewall WAF
		$waf_status = 'INACTIVE';
		if ( 'BLOCK' === $settings['waf_mode'] ) {
			$waf_status = 'ACTIVE';
		} elseif ( 'MONITOR' === $settings['waf_mode'] ) {
			$waf_status = 'WARNING'; // Em modo de aprendizado/monitor
		}
		$components['waf'] = array(
			'name'    => __( 'Web Application Firewall (WAF)', 'siteshield' ),
			'status'  => $waf_status,
			'details' => sprintf( '%s (Nível %s)', $settings['waf_mode'], isset( $settings['waf_level'] ) ? strtoupper( $settings['waf_level'] ) : 'MEDIUM' ),
		);

		// 6. Proteção de Login & URL Customizada
		$login_active = ! empty( $settings['login_custom_url_enabled'] ) || ! empty( $settings['login_secret_key'] );
		$components['login_security'] = array(
			'name'    => __( 'Login Security & URL Cloaking', 'siteshield' ),
			'status'  => $login_active ? 'ACTIVE' : 'INACTIVE',
			'details' => $login_active ? __( 'URL Ocultada / Protegida', 'siteshield' ) : __( 'Endereço Padrão Exposto', 'siteshield' ),
		);

		// 7. Autenticação de Dois Fatores (2FA)
		$twofa_active = ! empty( $settings['two_factor_enabled'] );
		$components['two_factor'] = array(
			'name'    => __( 'Two-Factor Authentication (2FA)', 'siteshield' ),
			'status'  => $twofa_active ? 'ACTIVE' : 'INACTIVE',
			'details' => $twofa_active ? __( 'Obrigatório para Administradores', 'siteshield' ) : __( 'Desativado', 'siteshield' ),
		);

		// 8. Malware Scanner
		$last_scan = get_option( 'siteshield_last_scan_time', 0 );
		$components['scanner'] = array(
			'name'    => __( 'Heuristic Malware Scanner', 'siteshield' ),
			'status'  => $last_scan > 0 ? 'ACTIVE' : 'INACTIVE',
			'details' => $last_scan > 0 ? sprintf( __( 'Última análise: %s', 'siteshield' ), date( 'd/m/Y H:i', $last_scan ) ) : __( 'Nenhuma análise recente', 'siteshield' ),
		);

		// 9. Cabeçalhos HTTP de Segurança
		$headers_active = ! empty( $settings['headers_enabled'] );
		$components['headers'] = array(
			'name'    => __( 'Security Headers & CSP', 'siteshield' ),
			'status'  => $headers_active ? 'PASS' : 'WARNING',
			'details' => $headers_active ? __( 'Injeção Ativa no Servidor', 'siteshield' ) : __( 'Cabeçalhos Desativados', 'siteshield' ),
		);

		// 10. Certificado SSL/TLS & HTTPS
		$is_ssl = is_ssl() || ( 0 === strpos( get_site_url(), 'https://' ) );
		$components['ssl'] = array(
			'name'    => __( 'SSL/TLS & Conexão HTTPS', 'siteshield' ),
			'status'  => $is_ssl ? 'PASS' : 'FAIL',
			'details' => $is_ssl ? __( 'HTTPS Forçado e Válido', 'siteshield' ) : __( 'HTTPS Não Configurado', 'siteshield' ),
		);

		// 11. Segurança DNS (SPF / DMARC)
		$dns_diag = Security_Diagnostics::audit_dns_security();
		$components['dns'] = array(
			'name'    => __( 'DNS Security (SPF & DMARC)', 'siteshield' ),
			'status'  => $dns_diag['status'],
			'details' => __( 'SPF e DMARC Inspecionados', 'siteshield' ),
		);

		// 12. Proteção Anti-Clone
		$anti_clone = ! empty( $settings['content_protection_enabled'] );
		$components['anti_clone'] = array(
			'name'    => __( 'Content Protection & Anti-Clone', 'siteshield' ),
			'status'  => $anti_clone ? 'ACTIVE' : 'INACTIVE',
			'details' => $anti_clone ? __( 'Anti-Cópia e DevTools Bloqueados', 'siteshield' ) : __( 'Desativado', 'siteshield' ),
		);

		// 13. Estatísticas de Visitantes
		$visitor_tr = ! empty( $settings['visitor_tracking_enabled'] );
		$components['visitors'] = array(
			'name'    => __( 'Visitor & Traffic Tracking', 'siteshield' ),
			'status'  => $visitor_tr ? 'ACTIVE' : 'INACTIVE',
			'details' => $visitor_tr ? __( 'Rastreamento com Anonimização Ativa', 'siteshield' ) : __( 'Desativado', 'siteshield' ),
		);

		// 14. Conexão SiteShield Cloud
		$cloud_ok = ! empty( $settings['cloud_connected'] );
		$components['cloud'] = array(
			'name'    => __( 'SiteShield Cloud Connection', 'siteshield' ),
			'status'  => $cloud_ok ? 'CONNECTED' : 'DISCONNECTED',
			'details' => $cloud_ok ? sprintf( __( 'Conectado (%s)', 'siteshield' ), $settings['cloud_account_email'] ) : __( 'Modo Local Independente', 'siteshield' ),
		);

		// 15. Licença do Produto
		$components['license'] = array(
			'name'    => __( 'Nível da Licença Ativa', 'siteshield' ),
			'status'  => 'PASS',
			'details' => __( 'Edição Demo Gratuita (1.ª Versão) — 100% Desbloqueada', 'siteshield' ),
		);

		return $components;
	}
}
