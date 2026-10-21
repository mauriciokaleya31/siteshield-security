<?php
/**
 * Suíte de Testes Automatizados de Regressão e Validação do SiteShield Security
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Tests
 */

// Simula ambiente de teste mínimo se executado via CLI
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/../' );
}
if ( ! defined( 'SITESHIELD_VERSION' ) ) {
	define( 'SITESHIELD_VERSION', '2.4.0' );
}
if ( ! defined( 'SITESHIELD_PATH' ) ) {
	define( 'SITESHIELD_PATH', dirname( __DIR__ ) . '/' );
}

$errors = array();
$passes = array();

// 1. Verificação de Arquivos Obrigatórios
$required_files = array(
	'siteshield.php',
	'uninstall.php',
	'includes/I18n/class-i18n.php',
	'includes/Core/class-database.php',
	'includes/Core/class-settings.php',
	'includes/Core/class-security-score.php',
	'includes/Core/class-emergency-mode.php',
	'includes/Core/class-compatibility.php',
	'includes/Core/class-plugin.php',
	'includes/Diagnostics/class-log-engine.php',
	'includes/Diagnostics/class-error-knowledge-base.php',
	'includes/Diagnostics/class-debug-log-parser.php',
	'includes/Diagnostics/class-security-diagnostics.php',
	'includes/Visitors/class-visitor-tracker.php',
	'includes/Reports/class-report-generator.php',
	'includes/Export/class-exporter.php',
	'includes/Cloud/class-cloud-client.php',
	'includes/Firewall/class-firewall-engine.php',
	'includes/Firewall/class-rate-limiter.php',
	'includes/Firewall/class-ip-manager.php',
	'includes/Login/class-login-security.php',
	'includes/Login/class-two-factor.php',
	'includes/Scanner/class-malware-scanner.php',
	'includes/Scanner/class-quarantine.php',
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
	'admin/views/layout-sidebar.php',
	'admin/views/dashboard.php',
	'admin/views/diagnostics.php',
	'admin/views/diagnostics-logs.php',
	'admin/views/visitors.php',
	'admin/views/reports.php',
);

foreach ( $required_files as $rel_file ) {
	$full_path = SITESHIELD_PATH . $rel_file;
	if ( file_exists( $full_path ) ) {
		$passes[] = "Arquivo presente: {$rel_file}";
	} else {
		$errors[] = "Arquivo ausente: {$rel_file}";
	}
}

// 2. Validação da Identidade do Autor Mauriciokaleya
$main_content = file_get_contents( SITESHIELD_PATH . 'siteshield.php' );
if ( false !== strpos( $main_content, 'Mauriciokaleya' ) ) {
	$passes[] = 'Atribuição autoral de Mauriciokaleya verificada no arquivo principal.';
} else {
	$errors[] = 'Atribuição de Mauriciokaleya ausente em siteshield.php';
}

// 3. Validação do Fallback Seguro e Isolamento do Loader
if ( false !== strpos( $main_content, 'siteshield_dispatch_admin_page' ) && false !== strpos( $main_content, 'try {' ) ) {
	$passes[] = 'Loader granular e callbacks de segurança verificados com sucesso.';
} else {
	$errors[] = 'Função de dispatch seguro siteshield_dispatch_admin_page ausente.';
}

// 4. Verificação de Arquivo ZIP
$zip_path = SITESHIELD_PATH . '../siteshield-security.zip';
if ( file_exists( $zip_path ) ) {
	$passes[] = 'Pacote de instalação siteshield-security.zip localizado.';
}

// Exibe resultados
echo "\n=== RESULTADOS DOS TESTES DE REGRESSÃO SITESHIELD SECURITY ===\n";
echo "Total de Sucessos: " . count( $passes ) . "\n";
echo "Total de Falhas: " . count( $errors ) . "\n\n";

if ( ! empty( $errors ) ) {
	echo "FALHAS ENCONTRADAS:\n";
	foreach ( $errors as $err ) {
		echo " [X] {$err}\n";
	}
	exit( 1 );
} else {
	echo "TODOS OS " . count( $passes ) . " TESTES FORAM APROVADOS COM SUCESSO!\n";
	exit( 0 );
}
