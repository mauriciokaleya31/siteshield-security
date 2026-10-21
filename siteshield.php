<?php
/**
 * Plugin Name: SiteShield Security & Anti-Clone — Enterprise Suite (Edição Demo Gratuita - 1.ª Versão)
 * Plugin URI: https://github.com/mauriciokaleya31/siteshield-security
 * Description: [1.ª Versão — Modelo Demo 100% Gratuito & Desbloqueado] Suíte corporativa de segurança para WordPress criada por Maurício Kaleya: WAF Inteligente com 4 Níveis, 2FA TOTP, Heurística de Malware com Quarentena Segura, File Integrity SHA-256, System Health Center, Autoteste de Proteções, Hardening, Sessões Ativas, Estatísticas de Visitantes, Relatórios PDF/CSV/JSON, Anti-Clone, Cabeçalhos HTTP e Selo SiteShield Verified.
 * Version: 2.4.0
 * Author: Maurício Kaleya (mauriciokaleya31)
 * Author URI: https://github.com/mauriciokaleya31
 * Text Domain: siteshield
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: GPLv2 or later
 *
 * @package SiteShield_Security
 * @author Maurício Kaleya <kaleyapt@gmail.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Impede execução direta
}

// 1. Constantes Globais
if ( ! defined( 'SITESHIELD_VERSION' ) ) {
	define( 'SITESHIELD_VERSION', '2.4.0' );
}
if ( ! defined( 'SITESHIELD_FILE' ) ) {
	define( 'SITESHIELD_FILE', __FILE__ );
}
if ( ! defined( 'SITESHIELD_PATH' ) ) {
	define( 'SITESHIELD_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'SITESHIELD_URL' ) ) {
	define( 'SITESHIELD_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'SITESHIELD_COOKIE_NAME' ) ) {
	define( 'SITESHIELD_COOKIE_NAME', 'siteshield_auth_token' );
}
if ( ! defined( 'SITESHIELD_AUTHOR' ) ) {
	define( 'SITESHIELD_AUTHOR', 'Mauriciokaleya' );
}

// 2. Autoloader PSR-4 para o namespace SiteShield
spl_autoload_register( function ( $class ) {
	if ( 0 !== strpos( $class, 'SiteShield\\' ) ) {
		return;
	}

	$relative_class = substr( $class, 11 ); // remove 'SiteShield\'
	$parts          = explode( '\\', $relative_class );
	$class_name     = array_pop( $parts );

	// Converte Class_Name para class-class-name.php
	$file_name = 'class-' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';

	if ( empty( $parts ) ) {
		$path = SITESHIELD_PATH . 'includes/' . $file_name;
	} elseif ( 'Admin' === $parts[0] ) {
		$path = SITESHIELD_PATH . 'admin/' . $file_name;
	} else {
		$path = SITESHIELD_PATH . 'includes/' . implode( '/', $parts ) . '/' . $file_name;
	}

	if ( file_exists( $path ) ) {
		require_once $path;
	}
} );

// 3. Inclusão protegida e granular dos módulos do sistema
$siteshield_modules = array(
	'includes/I18n/class-i18n.php',
	'includes/Core/class-database.php',
	'includes/Core/class-settings.php',
	'includes/Core/class-feature-manager.php',
	'includes/Core/class-security-score.php',
	'includes/Core/class-emergency-mode.php',
	'includes/Core/class-compatibility.php',
	'includes/Events/class-event-center.php',
	'includes/Health/class-system-health.php',
	'includes/Testing/class-protection-tester.php',
	'includes/Alerts/class-alert-center.php',
	'includes/Hardening/class-wp-hardening.php',
	'includes/Performance/class-perf-monitor.php',
	'includes/Diagnostics/class-log-engine.php',
	'includes/Diagnostics/class-error-knowledge-base.php',
	'includes/Diagnostics/class-debug-log-parser.php',
	'includes/Diagnostics/class-security-diagnostics.php',
	'includes/Visitors/class-visitor-tracker.php',
	'includes/Reports/class-report-generator.php',
	'includes/Export/class-exporter.php',
	'includes/Cloud/class-cloud-client.php',
	'includes/Badge/class-verification-badge.php',
	'includes/Firewall/class-ip-manager.php',
	'includes/Firewall/class-waf-exceptions.php',
	'includes/Firewall/class-firewall-engine.php',
	'includes/Firewall/class-rate-limiter.php',
	'includes/Login/class-login-security.php',
	'includes/Login/class-two-factor.php',
	'includes/Login/class-session-manager.php',
	'includes/Scanner/class-malware-scanner.php',
	'includes/Scanner/class-quarantine.php',
	'includes/Scanner/class-scan-scheduler.php',
	'includes/Integrity/class-file-integrity.php',
	'includes/AntiBot/class-antibot-engine.php',
	'includes/AntiClone/class-content-protection.php',
	'includes/Headers/class-security-headers.php',
	'includes/Vulnerabilities/class-vulnerability-monitor.php',
	'includes/Audit/class-audit-logger.php',
	'includes/Notifications/class-notifier.php',
	'includes/Recovery/class-backup-recovery.php',
	'includes/API/class-rest-api.php',
	'admin/class-admin-controller.php',
	'admin/class-setup-wizard.php',
	'includes/Core/class-plugin.php',
);

foreach ( $siteshield_modules as $mod ) {
	$full_path = SITESHIELD_PATH . $mod;
	if ( file_exists( $full_path ) ) {
		try {
			require_once $full_path;
		} catch ( \Throwable $e ) {
			error_log( "[SiteShield Security Error] Falha ao carregar {$mod}: " . $e->getMessage() . ' no arquivo ' . $e->getFile() . ':' . $e->getLine() );
		}
	}
}

/**
 * Funções de despacho seguras para menus do WordPress (Anti-Fatal Callback)
 */
function siteshield_dispatch_admin_page() {
	if ( class_exists( '\SiteShield\Admin\Admin_Controller' ) ) {
		\SiteShield\Admin\Admin_Controller::render_admin_page();
	} else {
		echo '<div class="wrap"><h1>SiteShield Security</h1><div class="notice notice-error"><p>' . esc_html__( 'SiteShield Security Center em inicialização...', 'siteshield' ) . '</p></div></div>';
	}
}

function siteshield_dispatch_setup_wizard() {
	if ( class_exists( '\SiteShield\Admin\Setup_Wizard' ) ) {
		\SiteShield\Admin\Setup_Wizard::render_wizard();
	} else {
		echo '<div class="wrap"><h1>SiteShield Onboarding</h1><div class="notice notice-error"><p>' . esc_html__( 'Assistente Onboarding em inicialização...', 'siteshield' ) . '</p></div></div>';
	}
}

// 3. Hooks de Ciclo de Vida
register_activation_hook( SITESHIELD_FILE, function() {
	// Instala ou atualiza as tabelas nativas de forma segura
	if ( class_exists( '\SiteShield\Core\Database' ) ) {
		try {
			\SiteShield\Core\Database::install_tables();
		} catch ( \Throwable $e ) {
			error_log( 'SiteShield Database Setup Notice: ' . $e->getMessage() );
		}
	}

	// Transient para redirecionar ação Setup Wizard no primeiro acesso
	set_transient( 'siteshield_activation_redirect', true, 60 );
} );

register_deactivation_hook( SITESHIELD_FILE, function() {
	wp_clear_scheduled_hook( 'siteshield_scheduled_scan_event' );
	wp_clear_scheduled_hook( 'siteshield_cloud_heartbeat_event' );
	delete_transient( 'siteshield_activation_redirect' );
} );

// 4. Redirecionamento seguro para o Setup Wizard no primeiro acesso após ativação
add_action( 'admin_init', function() {
	if ( class_exists( '\SiteShield\Core\Database' ) && ! get_option( 'siteshield_db_version' ) ) {
		try {
			\SiteShield\Core\Database::install_tables();
		} catch ( \Throwable $e ) {
			error_log( 'SiteShield Auto DB Setup: ' . $e->getMessage() );
		}
	}

	if ( get_transient( 'siteshield_activation_redirect' ) ) {
		delete_transient( 'siteshield_activation_redirect' );

		// Previne redirecionamento se estiver em modo rede, chamada AJAX ou sem permissões
		$can_admin = function_exists( 'current_user_can' ) && ( current_user_can( 'manage_options' ) || current_user_can( 'manage_network' ) || current_user_can( 'edit_theme_options' ) || current_user_can( 'administrator' ) || current_user_can( 'read' ) );
		if ( ! is_network_admin() && ! isset( $_GET['activate-multi'] ) && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && $can_admin ) {
			$settings = \SiteShield\Core\Settings::get();
			if ( empty( $settings['setup_completed'] ) && ! headers_sent() ) {
				wp_safe_redirect( admin_url( 'admin.php?page=siteshield-setup' ) );
				exit;
			}
		}
	}
} );

// 5. Links de Ação na listagem de plugins
add_filter( 'plugin_action_links_' . plugin_basename( SITESHIELD_FILE ), function( $links ) {
	$custom = array(
		'<a href="' . esc_url( admin_url( 'admin.php?page=siteshield-dashboard' ) ) . '"><strong>' . esc_html__( 'Security Center', 'siteshield' ) . '</strong></a>',
		'<a href="' . esc_url( admin_url( 'admin.php?page=siteshield-setup' ) ) . '">' . esc_html__( 'Assistente Onboarding', 'siteshield' ) . '</a>',
	);
	return array_merge( $custom, $links );
} );

// 6. Inicializa o Plugin Container
function siteshield_core() {
	try {
		if ( class_exists( '\SiteShield\Core\Plugin' ) ) {
			return \SiteShield\Core\Plugin::get_instance();
		}
	} catch ( \Throwable $e ) {
		error_log( '[SiteShield Security Error] Falha no bootstrap: ' . $e->getMessage() );
	}
	return null;
}

if ( did_action( 'plugins_loaded' ) ) {
	siteshield_core();
} else {
	add_action( 'plugins_loaded', 'siteshield_core', 10 );
}

// 7. Registro do Menu Administrativo do SiteShield
add_action( 'admin_menu', function() {
	$cap = 'manage_options';
	if ( function_exists( 'current_user_can' ) && ! current_user_can( 'manage_options' ) ) {
		if ( current_user_can( 'manage_network' ) ) {
			$cap = 'manage_network';
		} elseif ( current_user_can( 'edit_theme_options' ) ) {
			$cap = 'edit_theme_options';
		} elseif ( current_user_can( 'administrator' ) ) {
			$cap = 'administrator';
		} elseif ( current_user_can( 'read' ) ) {
			$cap = 'read';
		}
	}

	add_menu_page(
		__( 'SiteShield Security Center', 'siteshield' ),
		__( 'SiteShield', 'siteshield' ),
		$cap,
		'siteshield-dashboard',
		'siteshield_dispatch_admin_page',
		'dashicons-shield-alt',
		58
	);

	add_submenu_page(
		'siteshield-dashboard',
		__( 'Dashboard & Security Score', 'siteshield' ),
		__( 'Dashboard', 'siteshield' ),
		$cap,
		'siteshield-dashboard',
		'siteshield_dispatch_admin_page'
	);

	add_submenu_page(
		'siteshield-dashboard',
		__( 'System Health Center', 'siteshield' ),
		__( 'System Health', 'siteshield' ),
		$cap,
		'siteshield-health',
		'siteshield_dispatch_admin_page'
	);

	add_submenu_page(
		'siteshield-dashboard',
		__( 'Firewall WAF & IP Manager', 'siteshield' ),
		__( 'Firewall (WAF)', 'siteshield' ),
		$cap,
		'siteshield-firewall',
		'siteshield_dispatch_admin_page'
	);

	add_submenu_page(
		'siteshield-dashboard',
		__( 'Segurança de Login & 2FA', 'siteshield' ),
		__( 'Login & 2FA', 'siteshield' ),
		$cap,
		'siteshield-login',
		'siteshield_dispatch_admin_page'
	);

	add_submenu_page(
		'siteshield-dashboard',
		__( 'Scanner de Malware & Quarentena', 'siteshield' ),
		__( 'Scanner Malware', 'siteshield' ),
		$cap,
		'siteshield-scanner',
		'siteshield_dispatch_admin_page'
	);

	add_submenu_page(
		'siteshield-dashboard',
		__( 'Integridade de Arquivos (SHA-256)', 'siteshield' ),
		__( 'Integridade', 'siteshield' ),
		$cap,
		'siteshield-integrity',
		'siteshield_dispatch_admin_page'
	);

	add_submenu_page(
		'siteshield-dashboard',
		__( 'Eventos de Segurança & Severidade', 'siteshield' ),
		__( 'Eventos & Logs', 'siteshield' ),
		$cap,
		'siteshield-events',
		'siteshield_dispatch_admin_page'
	);

	add_submenu_page(
		'siteshield-dashboard',
		__( 'WordPress Hardening & Blindagem', 'siteshield' ),
		__( 'Hardening', 'siteshield' ),
		$cap,
		'siteshield-hardening',
		'siteshield_dispatch_admin_page'
	);

	add_submenu_page(
		'siteshield-dashboard',
		__( 'Sessões Administrativas Ativas', 'siteshield' ),
		__( 'Sessões Ativas', 'siteshield' ),
		$cap,
		'siteshield-sessions',
		'siteshield_dispatch_admin_page'
	);

	add_submenu_page(
		'siteshield-dashboard',
		__( 'Proteção de Conteúdo & Anti-Clone', 'siteshield' ),
		__( 'Anti-Clone', 'siteshield' ),
		$cap,
		'siteshield-protection',
		'siteshield_dispatch_admin_page'
	);

	add_submenu_page(
		'siteshield-dashboard',
		__( 'Cabeçalhos HTTP de Segurança & CSP', 'siteshield' ),
		__( 'Cabeçalhos (Headers)', 'siteshield' ),
		$cap,
		'siteshield-headers',
		'siteshield_dispatch_admin_page'
	);

	add_submenu_page(
		'siteshield-dashboard',
		__( 'Diagnóstico & Logs', 'siteshield' ),
		__( 'Diagnóstico & Logs', 'siteshield' ),
		$cap,
		'siteshield-diagnostics-logs',
		'siteshield_dispatch_admin_page'
	);

	add_submenu_page(
		'siteshield-dashboard',
		__( 'Diagnósticos Avançados & DNS', 'siteshield' ),
		__( 'Diagnósticos', 'siteshield' ),
		$cap,
		'siteshield-diagnostics',
		'siteshield_dispatch_admin_page'
	);

	add_submenu_page(
		'siteshield-dashboard',
		__( 'Estatísticas Reais de Visitantes', 'siteshield' ),
		__( 'Visitantes', 'siteshield' ),
		$cap,
		'siteshield-visitors',
		'siteshield_dispatch_admin_page'
	);

	add_submenu_page(
		'siteshield-dashboard',
		__( 'Relatórios Executivos em PDF', 'siteshield' ),
		__( 'Relatórios PDF', 'siteshield' ),
		$cap,
		'siteshield-reports',
		'siteshield_dispatch_admin_page'
	);

	add_submenu_page(
		'siteshield-dashboard',
		__( 'SiteShield Verified — Selo de Segurança', 'siteshield' ),
		__( 'Selo Verificado', 'siteshield' ),
		$cap,
		'siteshield-verified-badge',
		'siteshield_dispatch_admin_page'
	);

	add_submenu_page(
		'siteshield-dashboard',
		__( 'Notificações & Comunicados Cloud', 'siteshield' ),
		__( 'Notificações', 'siteshield' ),
		$cap,
		'siteshield-notifications',
		'siteshield_dispatch_admin_page'
	);

	add_submenu_page(
		'siteshield-dashboard',
		__( 'Licenciamento & Planos', 'siteshield' ),
		__( 'Licença & Planos', 'siteshield' ),
		$cap,
		'siteshield-license',
		'siteshield_dispatch_admin_page'
	);

	add_submenu_page(
		'siteshield-dashboard',
		__( 'Configurações Gerais & Privacy Center', 'siteshield' ),
		__( 'Configurações', 'siteshield' ),
		$cap,
		'siteshield-settings',
		'siteshield_dispatch_admin_page'
	);

	add_submenu_page(
		'siteshield-dashboard',
		__( 'Assistente Onboarding (Setup Wizard)', 'siteshield' ),
		__( 'Assistente Onboarding', 'siteshield' ),
		$cap,
		'siteshield-setup',
		'siteshield_dispatch_setup_wizard'
	);

	add_submenu_page(
		null,
		__( 'SiteShield Security', 'siteshield' ),
		__( 'SiteShield Security', 'siteshield' ),
		$cap,
		'siteshield',
		'siteshield_dispatch_admin_page'
	);
} );
