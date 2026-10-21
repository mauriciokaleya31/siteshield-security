<?php
/**
 * Controlador e Roteador Administrativo do SiteShield Security Center
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Admin
 */

namespace SiteShield\Admin;

use SiteShield\Core\Settings;
use SiteShield\Core\Security_Score;
use SiteShield\Core\Database;
use SiteShield\Core\Emergency_Mode;
use SiteShield\Firewall\IP_Manager;
use SiteShield\Scanner\Malware_Scanner;
use SiteShield\Scanner\Quarantine;
use SiteShield\Integrity\File_Integrity;
use SiteShield\Vulnerabilities\Vulnerability_Monitor;
use SiteShield\Audit\Audit_Logger;
use SiteShield\Recovery\Backup_Recovery;
use SiteShield\Visitors\Visitor_Tracker;
use SiteShield\Reports\Report_Generator;
use SiteShield\Cloud\Cloud_Client;
use SiteShield\Login\Login_Security;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Admin_Controller
 */
class Admin_Controller {

	/**
	 * Inicializa o painel administrativo
	 */
	public static function init() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );

		// Handlers AJAX Seguros
		add_action( 'wp_ajax_siteshield_save_settings', array( __CLASS__, 'ajax_save_settings' ) );
		add_action( 'wp_ajax_siteshield_run_scan', array( __CLASS__, 'ajax_run_scan' ) );
		add_action( 'wp_ajax_siteshield_quarantine_action', array( __CLASS__, 'ajax_quarantine_action' ) );
		add_action( 'wp_ajax_siteshield_ip_action', array( __CLASS__, 'ajax_ip_action' ) );
		add_action( 'wp_ajax_siteshield_integrity_action', array( __CLASS__, 'ajax_integrity_action' ) );
		add_action( 'wp_ajax_siteshield_emergency_toggle', array( __CLASS__, 'ajax_emergency_toggle' ) );
		add_action( 'wp_ajax_siteshield_export_backup', array( __CLASS__, 'ajax_export_backup' ) );
		add_action( 'wp_ajax_siteshield_import_backup', array( __CLASS__, 'ajax_import_backup' ) );
		add_action( 'wp_ajax_siteshield_get_visitor_stats', array( __CLASS__, 'ajax_get_visitor_stats' ) );
		add_action( 'wp_ajax_siteshield_save_custom_login', array( __CLASS__, 'ajax_save_custom_login' ) );
		add_action( 'wp_ajax_siteshield_activate_license', array( __CLASS__, 'ajax_activate_license' ) );
		add_action( 'wp_ajax_siteshield_connect_cloud', array( __CLASS__, 'ajax_connect_cloud' ) );
		add_action( 'wp_ajax_siteshield_change_language', array( __CLASS__, 'ajax_change_language' ) );
		add_action( 'wp_ajax_siteshield_run_diagnostics', array( __CLASS__, 'ajax_run_diagnostics' ) );
		add_action( 'wp_ajax_siteshield_run_protection_test', array( __CLASS__, 'ajax_run_protection_test' ) );
		add_action( 'wp_ajax_siteshield_destroy_other_sessions', array( __CLASS__, 'ajax_destroy_other_sessions' ) );
		add_action( 'wp_ajax_siteshield_add_waf_exception', array( __CLASS__, 'ajax_add_waf_exception' ) );
		add_action( 'wp_ajax_siteshield_remove_waf_exception', array( __CLASS__, 'ajax_remove_waf_exception' ) );
		add_action( 'wp_ajax_siteshield_protect_uploads', array( __CLASS__, 'ajax_protect_uploads' ) );
		add_action( 'wp_ajax_siteshield_run_health_diagnostic', array( __CLASS__, 'ajax_run_health_diagnostic' ) );
		add_action( 'wp_ajax_siteshield_clear_logs', array( __CLASS__, 'ajax_clear_logs' ) );
		add_action( 'wp_ajax_siteshield_mark_notification_read', array( __CLASS__, 'ajax_mark_notification_read' ) );

		// Handlers de Formulários POST (Licença e Selo)
		add_action( 'admin_post_siteshield_save_badge_settings', array( __CLASS__, 'handle_save_badge_settings' ) );
		add_action( 'admin_post_siteshield_activate_license', array( __CLASS__, 'handle_activate_license' ) );
		add_action( 'admin_post_siteshield_deactivate_license', array( __CLASS__, 'handle_deactivate_license' ) );

		// Downloads e Exportações (PDF, CSV, JSON)
		add_action( 'admin_init', array( __CLASS__, 'handle_export_actions' ) );

		// Modal "Ver detalhes" e links da listagem de plugins
		add_filter( 'plugin_row_meta', array( __CLASS__, 'filter_plugin_row_meta' ), 10, 2 );
		add_filter( 'plugins_api', array( __CLASS__, 'filter_plugins_api' ), 20, 3 );
	}

	/**
	 * Retorna a capacidade mínima requerida de forma adaptativa para qualquer tipo de administrador
	 *
	 * @return string
	 */
	public static function get_required_capability() {
		$caps = array(
			'manage_options',
			'manage_network',
			'manage_network_options',
			'edit_theme_options',
			'administrator',
			'edit_plugins',
			'activate_plugins',
			'read',
		);

		foreach ( $caps as $cap ) {
			if ( function_exists( 'current_user_can' ) && current_user_can( $cap ) ) {
				return $cap;
			}
		}

		return 'manage_options';
	}

	/**
	 * Registra o menu principal do SiteShield
	 */
	public static function register_menu() {
		static $registered = false;
		if ( $registered ) {
			return;
		}
		$registered = true;

		$cap = self::get_required_capability();

		// Menu Principal
		add_menu_page(
			__( 'SiteShield Security Center', 'siteshield' ),
			__( 'SiteShield', 'siteshield' ),
			$cap,
			'siteshield-dashboard',
			array( __CLASS__, 'render_admin_page' ),
			'dashicons-shield-alt',
			58
		);

		// Submenus Diretos
		add_submenu_page(
			'siteshield-dashboard',
			__( 'Dashboard & Security Score', 'siteshield' ),
			__( 'Dashboard', 'siteshield' ),
			$cap,
			'siteshield-dashboard',
			array( __CLASS__, 'render_admin_page' )
		);

		add_submenu_page(
			'siteshield-dashboard',
			__( 'System Health Center', 'siteshield' ),
			__( 'System Health', 'siteshield' ),
			$cap,
			'siteshield-health',
			array( __CLASS__, 'render_admin_page' )
		);

		add_submenu_page(
			'siteshield-dashboard',
			__( 'Firewall WAF & IP Manager', 'siteshield' ),
			__( 'Firewall (WAF)', 'siteshield' ),
			$cap,
			'siteshield-firewall',
			array( __CLASS__, 'render_admin_page' )
		);

		add_submenu_page(
			'siteshield-dashboard',
			__( 'Segurança de Login & 2FA', 'siteshield' ),
			__( 'Login & 2FA', 'siteshield' ),
			$cap,
			'siteshield-login',
			array( __CLASS__, 'render_admin_page' )
		);

		add_submenu_page(
			'siteshield-dashboard',
			__( 'Scanner de Malware & Quarentena', 'siteshield' ),
			__( 'Scanner Malware', 'siteshield' ),
			$cap,
			'siteshield-scanner',
			array( __CLASS__, 'render_admin_page' )
		);

		add_submenu_page(
			'siteshield-dashboard',
			__( 'Integridade de Arquivos (SHA-256)', 'siteshield' ),
			__( 'Integridade', 'siteshield' ),
			$cap,
			'siteshield-integrity',
			array( __CLASS__, 'render_admin_page' )
		);

		add_submenu_page(
			'siteshield-dashboard',
			__( 'Eventos de Segurança & Severidade', 'siteshield' ),
			__( 'Eventos & Logs', 'siteshield' ),
			$cap,
			'siteshield-events',
			array( __CLASS__, 'render_admin_page' )
		);

		add_submenu_page(
			'siteshield-dashboard',
			__( 'WordPress Hardening & Blindagem', 'siteshield' ),
			__( 'Hardening', 'siteshield' ),
			$cap,
			'siteshield-hardening',
			array( __CLASS__, 'render_admin_page' )
		);

		add_submenu_page(
			'siteshield-dashboard',
			__( 'Sessões Administrativas Ativas', 'siteshield' ),
			__( 'Sessões Ativas', 'siteshield' ),
			$cap,
			'siteshield-sessions',
			array( __CLASS__, 'render_admin_page' )
		);

		add_submenu_page(
			'siteshield-dashboard',
			__( 'Proteção de Conteúdo & Anti-Clone', 'siteshield' ),
			__( 'Anti-Clone', 'siteshield' ),
			$cap,
			'siteshield-protection',
			array( __CLASS__, 'render_admin_page' )
		);

		add_submenu_page(
			'siteshield-dashboard',
			__( 'Cabeçalhos HTTP de Segurança & CSP', 'siteshield' ),
			__( 'Cabeçalhos (Headers)', 'siteshield' ),
			$cap,
			'siteshield-headers',
			array( __CLASS__, 'render_admin_page' )
		);

		add_submenu_page(
			'siteshield-dashboard',
			__( 'Diagnósticos Avançados & DNS', 'siteshield' ),
			__( 'Diagnósticos', 'siteshield' ),
			$cap,
			'siteshield-diagnostics',
			array( __CLASS__, 'render_admin_page' )
		);

		add_submenu_page(
			'siteshield-dashboard',
			__( 'Estatísticas Reais de Visitantes', 'siteshield' ),
			__( 'Visitantes', 'siteshield' ),
			$cap,
			'siteshield-visitors',
			array( __CLASS__, 'render_admin_page' )
		);

		add_submenu_page(
			'siteshield-dashboard',
			__( 'Relatórios Executivos em PDF', 'siteshield' ),
			__( 'Relatórios PDF', 'siteshield' ),
			$cap,
			'siteshield-reports',
			array( __CLASS__, 'render_admin_page' )
		);

		add_submenu_page(
			'siteshield-dashboard',
			__( 'SiteShield Verified — Selo de Segurança', 'siteshield' ),
			__( 'Selo Verificado', 'siteshield' ),
			$cap,
			'siteshield-verified-badge',
			array( __CLASS__, 'render_admin_page' )
		);

		add_submenu_page(
			'siteshield-dashboard',
			__( 'Notificações & Comunicados Cloud', 'siteshield' ),
			__( 'Notificações', 'siteshield' ),
			$cap,
			'siteshield-notifications',
			array( __CLASS__, 'render_admin_page' )
		);

		add_submenu_page(
			'siteshield-dashboard',
			__( 'Licenciamento & Planos', 'siteshield' ),
			__( 'Licença & Planos', 'siteshield' ),
			$cap,
			'siteshield-license',
			array( __CLASS__, 'render_admin_page' )
		);

		add_submenu_page(
			'siteshield-dashboard',
			__( 'Central de Suporte & Ajuda', 'siteshield' ),
			__( 'Suporte', 'siteshield' ),
			$cap,
			'siteshield-support',
			array( __CLASS__, 'render_admin_page' )
		);

		add_submenu_page(
			'siteshield-dashboard',
			__( 'Configurações Gerais & Privacy Center', 'siteshield' ),
			__( 'Configurações', 'siteshield' ),
			$cap,
			'siteshield-settings',
			array( __CLASS__, 'render_admin_page' )
		);

		add_submenu_page(
			'siteshield-dashboard',
			__( 'Assistente Onboarding (Setup Wizard)', 'siteshield' ),
			__( 'Assistente Onboarding', 'siteshield' ),
			$cap,
			'siteshield-setup',
			array( '\SiteShield\Admin\Setup_Wizard', 'render_wizard' )
		);

		// Alias oculto para compatibilidade com a rota 'siteshield'
		add_submenu_page(
			null,
			__( 'SiteShield Security', 'siteshield' ),
			__( 'SiteShield Security', 'siteshield' ),
			$cap,
			'siteshield',
			array( __CLASS__, 'render_admin_page' )
		);
	}

	/**
	 * Enfileira os scripts e estilos da administração
	 */
	public static function enqueue_assets( $hook ) {
		try {
			if ( false === strpos( $hook, 'siteshield' ) ) {
				return;
			}

			wp_enqueue_style(
				'siteshield-admin-css',
				SITESHIELD_URL . 'assets/css/admin.css',
				array(),
				SITESHIELD_VERSION
			);

			wp_enqueue_script(
				'siteshield-admin-js',
				SITESHIELD_URL . 'assets/js/admin.js',
				array( 'jquery' ),
				SITESHIELD_VERSION,
				true
			);

			$settings = Settings::get();

			wp_localize_script(
				'siteshield-admin-js',
				'SiteShieldAdmin',
				array(
					'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
					'nonce'        => wp_create_nonce( 'siteshield_admin_nonce' ),
					'dashboardUrl' => admin_url( 'admin.php?page=siteshield-dashboard' ),
					'siteLoginUrl' => Login_Security::get_active_login_url(),
					'siteUrl'      => home_url(),
					'currentKey'   => ! empty( $settings['login_secret_key'] ) ? $settings['login_secret_key'] : '',
					'currentParam' => ! empty( $settings['login_secret_param'] ) ? $settings['login_secret_param'] : 'shield_key',
					'currentSlug'  => ! empty( $settings['login_custom_slug'] ) ? $settings['login_custom_slug'] : 'secure-login',
					'strings'      => array(
						'copied'        => __( 'URL copiada para a área de transferência!', 'siteshield' ),
						'saving'        => __( 'Salvando configurações...', 'siteshield' ),
						'saved'         => __( 'Configurações salvas com sucesso!', 'siteshield' ),
						'scanStarting'  => __( 'Iniciando varredura heurística...', 'siteshield' ),
						'scanComplete'  => __( 'Varredura finalizada com sucesso!', 'siteshield' ),
						'confirmDelete' => __( 'Tem certeza que deseja executar esta ação?', 'siteshield' ),
					),
				)
			);
		} catch ( \Throwable $e ) {
			error_log( 'SiteShield Assets Notice: ' . $e->getMessage() );
		}
	}

	/**
	 * Intercepta requisições de download/exportação (PDF, CSV, JSON)
	 */
	public static function handle_export_actions() {
		if ( ! current_user_can( self::get_required_capability() ) ) {
			return;
		}

		if ( isset( $_GET['siteshield_action'] ) ) {
			$action = sanitize_key( wp_unslash( $_GET['siteshield_action'] ) );

			if ( 'print_report' === $action ) {
				check_admin_referer( 'siteshield_report_nonce' );
				Report_Generator::render_printable_report();
			} elseif ( 'export_events_csv' === $action ) {
				check_admin_referer( 'siteshield_export_nonce' );
				\SiteShield\Export\Exporter::export_events_csv();
			} elseif ( 'export_visitors_csv' === $action ) {
				check_admin_referer( 'siteshield_export_nonce' );
				\SiteShield\Export\Exporter::export_visitors_csv();
			} elseif ( 'export_diagnostics_csv' === $action ) {
				check_admin_referer( 'siteshield_export_nonce' );
				\SiteShield\Export\Exporter::export_diagnostics_csv();
			} elseif ( 'export_diagnostics_json' === $action ) {
				check_admin_referer( 'siteshield_export_nonce' );
				\SiteShield\Export\Exporter::export_diagnostics_json();
			}
		}
	}

	/**
	 * Renderiza o Security Center e despacha a visualização das abas no layout Sidebar + Content
	 */
	public static function render_admin_page() {
		try {
			$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : 'siteshield-dashboard';
			$tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';

			if ( ! empty( $tab ) ) {
				$active_tab = $tab;
			} else {
				switch ( $page ) {
					case 'siteshield-health':           $active_tab = 'system-health'; break;
					case 'siteshield-firewall':         $active_tab = 'firewall'; break;
					case 'siteshield-login':            $active_tab = 'login-security'; break;
					case 'siteshield-scanner':          $active_tab = 'scanner'; break;
					case 'siteshield-integrity':        $active_tab = 'integrity'; break;
					case 'siteshield-events':           $active_tab = 'security-events'; break;
					case 'siteshield-hardening':        $active_tab = 'hardening'; break;
					case 'siteshield-sessions':         $active_tab = 'active-sessions'; break;
					case 'siteshield-protection':       $active_tab = 'content-protection'; break;
					case 'siteshield-headers':          $active_tab = 'security-headers'; break;
					case 'siteshield-diagnostics-logs': $active_tab = 'diagnostics-logs'; break;
					case 'siteshield-diagnostics':      $active_tab = 'diagnostics'; break;
					case 'siteshield-visitors':         $active_tab = 'visitors'; break;
					case 'siteshield-reports':          $active_tab = 'reports'; break;
					case 'siteshield-verified-badge':   $active_tab = 'verified-badge'; break;
					case 'siteshield-notifications':    $active_tab = 'notifications'; break;
					case 'siteshield-license':          $active_tab = 'license'; break;
					case 'siteshield-support':          $active_tab = 'support'; break;
					case 'siteshield-settings':         $active_tab = 'settings'; break;
					default:                            $active_tab = 'dashboard'; break;
				}
			}

			$score_data = Security_Score::calculate();
			$settings   = Settings::get();
			$license    = Cloud_Client::get_license_info();

			global $wpdb;
			$tables = Database::get_table_names();

			// Contadores métricos para os cartões
			$stats = array(
				'blocked_attacks'  => 0,
				'active_threats'   => 0,
				'suspicious_files' => 0,
				'failed_logins'    => 0,
				'total_visitors'   => 0,
				'unique_visitors'  => 0,
				'last_scan'        => get_option( 'siteshield_last_scan_time' ),
			);

			if ( $wpdb->get_var( "SHOW TABLES LIKE '{$tables['events']}'" ) === $tables['events'] ) {
				$stats['blocked_attacks'] = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$tables['events']} WHERE action_taken = 'BLOCK'" ) );
			}
			if ( $wpdb->get_var( "SHOW TABLES LIKE '{$tables['scan_findings']}'" ) === $tables['scan_findings'] ) {
				$stats['active_threats']   = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$tables['scan_findings']} WHERE status = 'active' AND severity IN ('high', 'critical')" ) );
				$stats['suspicious_files'] = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$tables['scan_findings']} WHERE status = 'active'" ) );
			}
			if ( $wpdb->get_var( "SHOW TABLES LIKE '{$tables['ips']}'" ) === $tables['ips'] ) {
				$stats['failed_logins'] = intval( $wpdb->get_var( "SELECT SUM(attempts) FROM {$tables['ips']} WHERE list_type = 'blocked'" ) );
			}
			if ( $wpdb->get_var( "SHOW TABLES LIKE '{$tables['visitors']}'" ) === $tables['visitors'] ) {
				$stats['total_visitors']  = intval( $wpdb->get_var( "SELECT SUM(requests_count) FROM {$tables['visitors']}" ) );
				$stats['unique_visitors'] = intval( $wpdb->get_var( "SELECT COUNT(DISTINCT ip_address) FROM {$tables['visitors']}" ) );
			}

			// Carrega a view principal do layout moderno
			include SITESHIELD_PATH . 'admin/views/dashboard.php';
		} catch ( \Throwable $e ) {
			echo '<div class="wrap"><div class="notice notice-error"><p><strong>SiteShield Security Center:</strong> Ocorreu um aviso durante a renderização: ' . esc_html( $e->getMessage() ) . ' em ' . esc_html( $e->getFile() ) . ':' . esc_html( $e->getLine() ) . '</p></div></div>';
		}
	}

	/**
	 * Handler AJAX: Salva configurações
	 */
	public static function ajax_save_settings() {
		check_ajax_referer( 'siteshield_admin_nonce', 'nonce' );
		if ( ! current_user_can( self::get_required_capability() ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissão negada.', 'siteshield' ) ) );
		}

		$submitted = isset( $_POST['settings'] ) && is_array( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : array();
		Settings::update( $submitted );
		Audit_Logger::log( 'settings_updated', 'system', 'security_settings', 'Configurações de segurança atualizadas' );

		wp_send_json_success( array(
			'message' => __( 'Configurações salvas com sucesso!', 'siteshield' ),
			'score'   => Security_Score::calculate(),
		) );
	}

	/**
	 * Handler AJAX: Salva URL customizada de login
	 */
	public static function ajax_save_custom_login() {
		check_ajax_referer( 'siteshield_admin_nonce', 'nonce' );
		if ( ! current_user_can( self::get_required_capability() ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissão negada.', 'siteshield' ) ) );
		}

		$enabled = ! empty( $_POST['enabled'] );
		$slug    = isset( $_POST['slug'] ) ? sanitize_title_with_dashes( wp_unslash( $_POST['slug'] ) ) : 'secure-login';

		if ( $enabled ) {
			$validation = Login_Security::validate_custom_slug( $slug );
			if ( is_wp_error( $validation ) ) {
				wp_send_json_error( array( 'message' => $validation->get_error_message() ) );
			}
		}

		Settings::update( array(
			'login_custom_url_enabled' => $enabled,
			'login_custom_slug'        => $slug,
		) );

		Audit_Logger::log( 'login_url_updated', 'login', $slug, 'URL de login alterada: ' . ( $enabled ? home_url( '/' . $slug . '/' ) : wp_login_url() ) );

		wp_send_json_success( array(
			'message'   => __( 'Endereço de login atualizado com sucesso!', 'siteshield' ),
			'login_url' => Login_Security::get_active_login_url(),
		) );
	}

	/**
	 * Handler AJAX: Executa o scanner
	 */
	public static function ajax_run_scan() {
		check_ajax_referer( 'siteshield_admin_nonce', 'nonce' );
		if ( ! current_user_can( self::get_required_capability() ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissão negada.', 'siteshield' ) ) );
		}

		$type    = isset( $_POST['scan_type'] ) ? sanitize_key( wp_unslash( $_POST['scan_type'] ) ) : 'quick';
		$results = Malware_Scanner::execute_scan( $type );
		Audit_Logger::log( 'scanner_executed', 'system', 'malware_scanner', sprintf( 'Varredura (%s) executada. %d ameaças detectadas.', $type, $results['threats_count'] ) );

		wp_send_json_success( array(
			'message' => __( 'Varredura concluída!', 'siteshield' ),
			'results' => $results,
		) );
	}

	/**
	 * Handler AJAX: Quarentena e Restauração
	 */
	public static function ajax_quarantine_action() {
		check_ajax_referer( 'siteshield_admin_nonce', 'nonce' );
		if ( ! current_user_can( self::get_required_capability() ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissão negada.', 'siteshield' ) ) );
		}

		$action = isset( $_POST['quarantine_action'] ) ? sanitize_key( wp_unslash( $_POST['quarantine_action'] ) ) : '';
		$file   = isset( $_POST['file_path'] ) ? sanitize_text_field( wp_unslash( $_POST['file_path'] ) ) : '';
		$id     = isset( $_POST['quarantine_id'] ) ? intval( $_POST['quarantine_id'] ) : 0;

		if ( 'quarantine' === $action && ! empty( $file ) ) {
			$res = Quarantine::quarantine_file( $file );
			if ( is_wp_error( $res ) ) {
				wp_send_json_error( array( 'message' => $res->get_error_message() ) );
			}
			Audit_Logger::log( 'file_quarantined', 'file', $file, 'Arquivo movido para quarentena' );
			wp_send_json_success( array( 'message' => __( 'Arquivo movido para quarentena com sucesso!', 'siteshield' ) ) );
		} elseif ( 'restore' === $action && $id > 0 ) {
			$res = Quarantine::restore_file( $id );
			if ( is_wp_error( $res ) ) {
				wp_send_json_error( array( 'message' => $res->get_error_message() ) );
			}
			Audit_Logger::log( 'file_restored', 'quarantine', (string) $id, 'Arquivo restaurado da quarentena' );
			wp_send_json_success( array( 'message' => __( 'Arquivo restaurado com sucesso!', 'siteshield' ) ) );
		} elseif ( 'delete' === $action && $id > 0 ) {
			Quarantine::delete_permanently( $id );
			Audit_Logger::log( 'file_deleted', 'quarantine', (string) $id, 'Arquivo excluído permanentemente da quarentena' );
			wp_send_json_success( array( 'message' => __( 'Arquivo excluído definitivamente.', 'siteshield' ) ) );
		}

		wp_send_json_error( array( 'message' => __( 'Ação inválida.', 'siteshield' ) ) );
	}

	/**
	 * Handler AJAX: Gestão de IPs
	 */
	public static function ajax_ip_action() {
		check_ajax_referer( 'siteshield_admin_nonce', 'nonce' );
		if ( ! current_user_can( self::get_required_capability() ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissão negada.', 'siteshield' ) ) );
		}

		$action = isset( $_POST['ip_action'] ) ? sanitize_key( wp_unslash( $_POST['ip_action'] ) ) : '';
		$ip     = isset( $_POST['ip_address'] ) ? sanitize_text_field( wp_unslash( $_POST['ip_address'] ) ) : '';
		$reason = isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : 'Ação administrativa';

		if ( 'block' === $action ) {
			IP_Manager::block_ip_permanent( $ip, $reason );
			Audit_Logger::log( 'ip_blocked', 'ip', $ip, 'IP bloqueado manualmente: ' . $reason );
			wp_send_json_success( array( 'message' => __( 'IP bloqueado com sucesso!', 'siteshield' ) ) );
		} elseif ( 'allow' === $action ) {
			IP_Manager::allow_ip( $ip, $reason );
			Audit_Logger::log( 'ip_allowed', 'ip', $ip, 'IP adicionado à lista permitida: ' . $reason );
			wp_send_json_success( array( 'message' => __( 'IP adicionado à lista branca!', 'siteshield' ) ) );
		} elseif ( 'unblock' === $action ) {
			IP_Manager::unblock_ip( $ip );
			Audit_Logger::log( 'ip_unblocked', 'ip', $ip, 'IP removido da lista' );
			wp_send_json_success( array( 'message' => __( 'IP desbloqueado com sucesso!', 'siteshield' ) ) );
		}

		wp_send_json_error( array( 'message' => __( 'Ação inválida.', 'siteshield' ) ) );
	}

	/**
	 * Handler AJAX: Ações de Integridade
	 */
	public static function ajax_integrity_action() {
		check_ajax_referer( 'siteshield_admin_nonce', 'nonce' );
		if ( ! current_user_can( self::get_required_capability() ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissão negada.', 'siteshield' ) ) );
		}

		$action = isset( $_POST['integrity_action'] ) ? sanitize_key( wp_unslash( $_POST['integrity_action'] ) ) : '';

		if ( 'build' === $action ) {
			$res = File_Integrity::build_baseline();
			Audit_Logger::log( 'integrity_baseline_built', 'system', 'file_integrity', 'Nova linha de base de integridade gerada' );
			wp_send_json_success( array( 'message' => __( 'Linha de base criada com sucesso!', 'siteshield' ), 'data' => $res ) );
		} elseif ( 'check' === $action ) {
			$res = File_Integrity::check_integrity();
			Audit_Logger::log( 'integrity_check', 'system', 'file_integrity', 'Verificação de integridade de arquivos realizada' );
			wp_send_json_success( array( 'message' => __( 'Auditoria de integridade concluída!', 'siteshield' ), 'data' => $res ) );
		}

		wp_send_json_error( array( 'message' => __( 'Ação inválida.', 'siteshield' ) ) );
	}

	/**
	 * Handler AJAX: Alternar Modo de Emergência
	 */
	public static function ajax_emergency_toggle() {
		check_ajax_referer( 'siteshield_admin_nonce', 'nonce' );
		if ( ! current_user_can( self::get_required_capability() ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissão negada.', 'siteshield' ) ) );
		}

		$is_active = Emergency_Mode::is_active();
		if ( $is_active ) {
			Emergency_Mode::disable();
			wp_send_json_success( array( 'message' => __( 'Modo de Emergência DESATIVADO.', 'siteshield' ), 'active' => false ) );
		} else {
			Emergency_Mode::enable( 'Acionamento manual no painel' );
			wp_send_json_success( array( 'message' => __( 'Modo de Emergência ATIVADO!', 'siteshield' ), 'active' => true ) );
		}
	}

	/**
	 * Handler AJAX: Obter estatísticas de visitantes filtradas
	 */
	public static function ajax_get_visitor_stats() {
		check_ajax_referer( 'siteshield_admin_nonce', 'nonce' );
		if ( ! current_user_can( self::get_required_capability() ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissão negada.', 'siteshield' ) ) );
		}

		$period = isset( $_POST['period'] ) ? sanitize_key( wp_unslash( $_POST['period'] ) ) : '7d';
		$stats  = Visitor_Tracker::get_stats( $period );

		wp_send_json_success( array( 'stats' => $stats ) );
	}

	/**
	 * Handler AJAX: Ativar Licença
	 */
	public static function ajax_activate_license() {
		check_ajax_referer( 'siteshield_admin_nonce', 'nonce' );
		if ( ! current_user_can( self::get_required_capability() ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissão negada.', 'siteshield' ) ) );
		}

		$key = isset( $_POST['license_key'] ) ? wp_unslash( $_POST['license_key'] ) : '';
		$res = Cloud_Client::activate_license( $key );

		if ( $res['success'] ) {
			wp_send_json_success( $res );
		} else {
			wp_send_json_error( $res );
		}
	}

	/**
	 * Handler AJAX: Conectar Conta Cloud
	 */
	public static function ajax_connect_cloud() {
		check_ajax_referer( 'siteshield_admin_nonce', 'nonce' );
		if ( ! current_user_can( self::get_required_capability() ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissão negada.', 'siteshield' ) ) );
		}

		$email = isset( $_POST['email'] ) ? wp_unslash( $_POST['email'] ) : '';
		$res   = Cloud_Client::connect_cloud_account( $email );

		if ( $res['success'] ) {
			wp_send_json_success( $res );
		} else {
			wp_send_json_error( $res );
		}
	}

	/**
	 * Handler AJAX: Exportar Configurações JSON
	 */
	public static function ajax_export_backup() {
		check_ajax_referer( 'siteshield_admin_nonce', 'nonce' );
		if ( ! current_user_can( self::get_required_capability() ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissão negada.', 'siteshield' ) ) );
		}

		$json = Backup_Recovery::export_settings_json();
		wp_send_json_success( array( 'json' => $json ) );
	}

	/**
	 * Handler AJAX: Importar Configurações JSON
	 */
	public static function ajax_import_backup() {
		check_ajax_referer( 'siteshield_admin_nonce', 'nonce' );
		if ( ! current_user_can( self::get_required_capability() ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissão negada.', 'siteshield' ) ) );
		}

		$json = isset( $_POST['json_data'] ) ? wp_unslash( $_POST['json_data'] ) : '';
		$res  = Backup_Recovery::import_settings_json( $json );

		if ( is_wp_error( $res ) ) {
			wp_send_json_error( array( 'message' => $res->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => __( 'Configurações restauradas com sucesso!', 'siteshield' ) ) );
	}

	/**
	 * Handler AJAX: Alterar Idioma
	 */
	public static function ajax_change_language() {
		check_ajax_referer( 'siteshield_admin_nonce', 'nonce' );
		if ( ! current_user_can( self::get_required_capability() ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissão negada.', 'siteshield' ) ) );
		}

		$locale = isset( $_POST['locale'] ) ? sanitize_text_field( wp_unslash( $_POST['locale'] ) ) : 'pt_PT';
		\SiteShield\I18n\I18n::set_locale( $locale );

		wp_send_json_success( array(
			'message' => __( 'Idioma atualizado com sucesso!', 'siteshield' ),
			'locale'  => $locale,
		) );
	}

	/**
	 * Handler AJAX: Executar Diagnósticos Avançados de Segurança
	 */
	public static function ajax_run_diagnostics() {
		check_ajax_referer( 'siteshield_admin_nonce', 'nonce' );
		if ( ! current_user_can( self::get_required_capability() ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissão negada.', 'siteshield' ) ) );
		}

		$results = \SiteShield\Diagnostics\Security_Diagnostics::run_full_audit();

		wp_send_json_success( array(
			'message' => __( 'Auditoria de diagnósticos concluída!', 'siteshield' ),
			'data'    => $results,
		) );
	}

	/**
	 * Handler AJAX: Executar Autoteste em Tempo Real de Proteções
	 */
	public static function ajax_run_protection_test() {
		check_ajax_referer( 'siteshield_admin_nonce', 'nonce' );
		if ( ! current_user_can( self::get_required_capability() ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissão negada.', 'siteshield' ) ) );
		}

		$results = \SiteShield\Testing\Protection_Tester::run_all_tests();

		wp_send_json_success( array(
			'message' => __( 'Autoteste de proteções concluído com sucesso!', 'siteshield' ),
			'results' => $results,
		) );
	}

	/**
	 * Handler AJAX: Desconectar Outras Sessões Ativas
	 */
	public static function ajax_destroy_other_sessions() {
		check_ajax_referer( 'siteshield_admin_nonce', 'nonce' );
		if ( ! current_user_can( self::get_required_capability() ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissão negada.', 'siteshield' ) ) );
		}

		\SiteShield\Login\Session_Manager::destroy_other_sessions( get_current_user_id() );

		wp_send_json_success( array(
			'message' => __( 'Todas as outras sessões foram encerradas com sucesso!', 'siteshield' ),
		) );
	}

	/**
	 * Handler AJAX: Adicionar Exceção de WAF
	 */
	public static function ajax_add_waf_exception() {
		check_ajax_referer( 'siteshield_admin_nonce', 'nonce' );
		if ( ! current_user_can( self::get_required_capability() ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissão negada.', 'siteshield' ) ) );
		}

		$type   = isset( $_POST['type'] ) ? sanitize_key( wp_unslash( $_POST['type'] ) ) : 'url';
		$value  = isset( $_POST['value'] ) ? sanitize_text_field( wp_unslash( $_POST['value'] ) ) : '';
		$reason = isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : '';

		if ( empty( $value ) ) {
			wp_send_json_error( array( 'message' => __( 'Por favor, informe o valor da exceção.', 'siteshield' ) ) );
		}

		\SiteShield\Firewall\WAF_Exceptions::add_exception( $type, $value, $reason );

		wp_send_json_success( array(
			'message' => __( 'Exceção de WAF adicionada com sucesso!', 'siteshield' ),
		) );
	}

	/**
	 * Handler AJAX: Remover Exceção de WAF
	 */
	public static function ajax_remove_waf_exception() {
		check_ajax_referer( 'siteshield_admin_nonce', 'nonce' );
		if ( ! current_user_can( self::get_required_capability() ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissão negada.', 'siteshield' ) ) );
		}

		$id = isset( $_POST['id'] ) ? sanitize_key( wp_unslash( $_POST['id'] ) ) : '';
		\SiteShield\Firewall\WAF_Exceptions::remove_exception( $id );

		wp_send_json_success( array(
			'message' => __( 'Exceção removida com sucesso!', 'siteshield' ),
		) );
	}

	/**
	 * Handler AJAX: Proteger pasta de uploads (.htaccess)
	 */
	public static function ajax_protect_uploads() {
		check_ajax_referer( 'siteshield_admin_nonce', 'nonce' );
		if ( ! current_user_can( self::get_required_capability() ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissão negada.', 'siteshield' ) ) );
		}

		$res = \SiteShield\Hardening\WP_Hardening::protect_uploads_directory();

		if ( $res ) {
			wp_send_json_success( array( 'message' => __( 'Bloqueio de execução de PHP na pasta Uploads ativado!', 'siteshield' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Não foi possível gravar o arquivo .htaccess na pasta Uploads. Verifique as permissões de escrita.', 'siteshield' ) ) );
		}
	}

	/**
	 * Handler AJAX: Executar Diagnóstico de Saúde dos Componentes
	 */
	public static function ajax_run_health_diagnostic() {
		check_ajax_referer( 'siteshield_admin_nonce', 'nonce' );
		if ( ! current_user_can( self::get_required_capability() ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissão negada.', 'siteshield' ) ) );
		}

		$results = \SiteShield\Diagnostics\Security_Diagnostics::run_full_system_health_check();
		$status  = \SiteShield\Diagnostics\Security_Diagnostics::get_overall_system_status();

		\SiteShield\Diagnostics\Log_Engine::log( 'system', 'health_check_executed', 'Auditoria de saúde do sistema executada pelo administrador.', 'INFO', array( 'status' => $status['code'] ) );

		wp_send_json_success( array(
			'message' => __( 'Diagnóstico de saúde executado com sucesso!', 'siteshield' ),
			'results' => $results,
			'status'  => $status,
		) );
	}

	/**
	 * Handler AJAX: Limpar Logs de Diagnóstico
	 */
	public static function ajax_clear_logs() {
		check_ajax_referer( 'siteshield_admin_nonce', 'nonce' );
		if ( ! current_user_can( self::get_required_capability() ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissão negada.', 'siteshield' ) ) );
		}

		// 1. Limpa logs internos do SiteShield
		\SiteShield\Diagnostics\Log_Engine::clear_logs( 'all' );

		// 2. Se debug.log existir e for gravável, trunca de forma segura
		$debug_log_path = \SiteShield\Diagnostics\Debug_Log_Parser::get_debug_log_path();
		if ( file_exists( $debug_log_path ) && is_writable( $debug_log_path ) ) {
			@file_put_contents( $debug_log_path, '' );
		}

		\SiteShield\Diagnostics\Log_Engine::log( 'system', 'logs_cleared', 'Arquivos de log limpos pelo administrador.', 'INFO' );

		wp_send_json_success( array(
			'message' => __( 'Todos os logs foram limpos com sucesso!', 'siteshield' ),
		) );
	}

	/**
	 * Adiciona links úteis na listagem de plugins do WordPress
	 *
	 * @param array  $links Links padrão.
	 * @param string $file Caminho do plugin.
	 * @return array
	 */
	public static function filter_plugin_row_meta( $links, $file ) {
		if ( $file === plugin_basename( SITESHIELD_FILE ) ) {
			$details_url = add_query_arg(
				array(
					'tab'       => 'plugin-information',
					'plugin'    => 'siteshield-security',
					'TB_iframe' => 'true',
					'width'     => '772',
					'height'    => '600',
				),
				admin_url( 'plugin-install.php' )
			);

			$row_meta = array(
				'details' => '<a href="' . esc_url( $details_url ) . '" class="thickbox open-plugin-details-modal" aria-label="' . esc_attr__( 'Mais informações sobre SiteShield Security', 'siteshield' ) . '">' . esc_html__( 'Ver detalhes', 'siteshield' ) . '</a>',
				'support' => '<a href="' . esc_url( admin_url( 'admin.php?page=siteshield-dashboard&tab=diagnostics' ) ) . '">' . esc_html__( 'Diagnósticos', 'siteshield' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}
		return $links;
	}

	/**
	 * Fornece as informações completas para o modal nativo "Ver detalhes" do WordPress
	 *
	 * @param false|object|array $res Resultado da API.
	 * @param string             $action Ação requisitada.
	 * @param object             $args Argumentos.
	 * @return object
	 */
	public static function filter_plugins_api( $res, $action, $args ) {
		if ( 'plugin_information' !== $action || ! is_object( $args ) || empty( $args->slug ) || 'siteshield-security' !== $args->slug ) {
			return $res;
		}

		$res = new \stdClass();
		$res->name           = 'SiteShield Security & Anti-Clone — Enterprise Suite';
		$res->slug           = 'siteshield-security';
		$res->version        = SITESHIELD_VERSION;
		$res->author         = '<a href="https://siteshield.security">Mauriciokaleya</a>';
		$res->author_profile = 'https://siteshield.security';
		$res->homepage       = 'https://siteshield.security';
		$res->requires       = '5.8';
		$res->tested         = '6.6';
		$res->requires_php   = '7.4';
		$res->last_updated   = '2026-08-30';
		$res->active_installs= 1000;
		$res->rateing         = 100;
		$res->num_rateings    = 58;

		$res->sections = array(
			'description'  => '<h3>Suíte Corporateiva de Segurança e Anti-Clone</h3><p>O <strong>SiteShield Security</strong> desenvolvido por <strong>Mauriciokaleya</strong> é uma solução completa de cibersegurança para WordPress com WAF inteligente, 2FA TOTP, Quarentena Segura, Heurística de Malware, File Integrity SHA-256, Anti-Cópia/Anti-Scraping e Monitor de Tráfego.</p><h4>Principais Recursos:</h4><ul><li><strong>Firewall WAF:</strong> 4 níveis de sensibilidade contra SQLi, XSS e exploits.</li><li><strong>Login Security:</strong> URL personalizada de login, proteção contra força bruta e 2FA TOTP.</li><li><strong>Quarentena Segura:</strong> Isolamento protegido de arquivos suspeitos com backup.</li><li><strong>System Health:</strong> Diagnóstico de 15 componentes sem falso sentimento de segurança.</li><li><strong>Anti-Clone:</strong> Proteção de conteúdo contra cópia casual e ferramentas de inspeção.</li></ul>',
			'installation' => '<h4>Como Instalar:</h4><ol><li>Envie o arquivo <code>siteshield-security.zip</code> em <strong>Plugins > Adicionar Novo > Enviar Plugin</strong>.</li><li>Clique em <strong>Ativar Plugin</strong>.</li><li>Siga as 7 etapas do <strong>Assistente Onboarding</strong> para configurar as proteções do seu site.</li></ol>',
			'faq'          => '<h4>Perguntas Frequentes</h4><p><strong>O plugin funciona se a Cloud estiver offline?</strong><br>Sim, o SiteShield possui modo Fail-Safe e todas as proteções essenciais locais continuam funcionando perfeitamente.</p><p><strong>Como recupero o acesso se esquecer a URL de login?</strong><br>Adicione <code>define(\'SITESHIELD_DISABLE_ALL\', true);</code> no seu <code>wp-config.php</code>.</p>',
			'changelog'    => '<h4>2.4.0</h4><ul><li>Padronização autoral de Mauriciokaleya.</li><li>Adição do modal completo "Ver detalhes".</li><li>Otimização de ativação e prevenção de erros fatais.</li></ul>',
		);

		return $res;
	}

	/**
	 * Salva as configurações do Selo de Verificação com validação de plano
	 */
	public static function handle_save_badge_settings() {
		check_admin_referer( 'siteshield_save_badge_settings', 'siteshield_badge_nonce' );
		if ( ! current_user_can( self::get_required_capability() ) ) {
			wp_die( esc_html__( 'Permissão negada.', 'siteshield' ) );
		}

		if ( ! \SiteShield\Core\Feature_Manager::can_use( 'verification_badge' ) ) {
			wp_die( esc_html__( 'O Selo SiteShield Verified é um recurso exclusivo dos planos PRO, Business e Agency.', 'siteshield' ) );
		}

		$enabled    = ! empty( $_POST['badge_enabled'] );
		$position   = isset( $_POST['badge_position'] ) ? sanitize_key( $_POST['badge_position'] ) : 'bottom-right';
		$size       = isset( $_POST['badge_size'] ) ? sanitize_key( $_POST['badge_size'] ) : 'medium';
		$theme      = isset( $_POST['badge_theme'] ) ? sanitize_key( $_POST['badge_theme'] ) : 'light';
		$margin     = isset( $_POST['badge_margin'] ) ? max( 5, min( 100, intval( $_POST['badge_margin'] ) ) ) : 20;
		$opacity    = isset( $_POST['badge_opacity'] ) ? max( 0.3, min( 1.0, floatval( $_POST['badge_opacity'] ) ) ) : 1.0;
		$display_on = isset( $_POST['badge_display_on'] ) && is_array( $_POST['badge_display_on'] ) ? array_map( 'sanitize_key', $_POST['badge_display_on'] ) : array( 'all' );
		$exclusions = isset( $_POST['badge_exclusions'] ) ? sanitize_text_field( $_POST['badge_exclusions'] ) : '';

		\SiteShield\Core\Settings::update( array(
			'badge_enabled'    => $enabled,
			'badge_position'   => $position,
			'badge_size'       => $size,
			'badge_theme'      => $theme,
			'badge_margin'     => $margin,
			'badge_opacity'    => $opacity,
			'badge_display_on' => $display_on,
			'badge_exclusions' => $exclusions,
		) );

		wp_safe_redirect( admin_url( 'admin.php?page=siteshield-verified-badge&saved=1' ) );
		exit;
	}

	/**
	 * Processa a ativação da licença
	 */
	public static function handle_activate_license() {
		check_admin_referer( 'siteshield_activate_license', 'siteshield_license_nonce' );
		if ( ! current_user_can( self::get_required_capability() ) ) {
			wp_die( esc_html__( 'Permissão negada.', 'siteshield' ) );
		}

		$key = isset( $_POST['license_key'] ) ? sanitize_text_field( $_POST['license_key'] ) : '';
		$res = \SiteShield\Cloud\Cloud_Client::activate_license( $key );

		if ( $res['success'] ) {
			wp_safe_redirect( admin_url( 'admin.php?page=siteshield-license&activated=1' ) );
		} else {
			wp_safe_redirect( admin_url( 'admin.php?page=siteshield-license&error=' . urlencode( $res['message'] ) ) );
		}
		exit;
	}

	/**
	 * Processa a desativação da licença
	 */
	public static function handle_deactivate_license() {
		check_admin_referer( 'siteshield_deactivate_license', 'siteshield_deactivate_nonce' );
		if ( ! current_user_can( self::get_required_capability() ) ) {
			wp_die( esc_html__( 'Permissão negada.', 'siteshield' ) );
		}

		\SiteShield\Cloud\Cloud_Client::deactivate_license();
		wp_safe_redirect( admin_url( 'admin.php?page=siteshield-license&deactivated=1' ) );
		exit;
	}

	/**
	 * Marca uma notificação como lida via AJAX
	 */
	public static function ajax_mark_notification_read() {
		check_ajax_referer( 'siteshield_admin_nonce', 'nonce' );
		if ( ! current_user_can( self::get_required_capability() ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissão negada.', 'siteshield' ) ) );
		}

		$notif_id = isset( $_POST['notif_id'] ) ? intval( $_POST['notif_id'] ) : 0;
		$notifications = get_option( 'siteshield_cloud_notifications', array() );

		if ( is_array( $notifications ) ) {
			foreach ( $notifications as $k => $notif ) {
				if ( isset( $notif['id'] ) && (int) $notif['id'] === $notif_id ) {
					$notifications[ $k ]['is_read'] = 1;
				}
			}
			update_option( 'siteshield_cloud_notifications', $notifications );
		}

		wp_send_json_success();
	}
}
