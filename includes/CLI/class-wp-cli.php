<?php
/**
 * Comandos de Linha de Comando (WP-CLI) do SiteShield
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage CLI
 */

namespace SiteShield\CLI;

use SiteShield\Core\Security_Score;
use SiteShield\Core\Emergency_Mode;
use SiteShield\Scanner\Malware_Scanner;
use SiteShield\Integrity\File_Integrity;
use SiteShield\Firewall\IP_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

/**
 * Comandos WP-CLI do SiteShield Security
 */
class WP_CLI_Commands {

	/**
	 * Exibe o status geral e pontuação de segurança do site.
	 *
	 * ## EXAMPLES
	 *
	 *     wp siteshield status
	 */
	public function status( $args, $assoc_args ) {
		$calc = Security_Score::calculate();
		\WP_CLI::log( "----------------------------------------" );
		\WP_CLI::log( "🛡️ SiteShield Security Center" );
		\WP_CLI::log( "----------------------------------------" );
		\WP_CLI::log( "Security Score: {$calc['score']} / 100 ({$calc['label']})" );
		\WP_CLI::log( "Modo de Emergência: " . ( Emergency_Mode::is_active() ? "ATIVADO" : "Inativo" ) );
		\WP_CLI::log( "----------------------------------------" );
	}

	/**
	 * Executa o scanner de malware heurístico.
	 *
	 * ## OPTIONS
	 *
	 * [--full]
	 * : Executa varredura profunda em todo o WordPress.
	 *
	 * ## EXAMPLES
	 *
	 *     wp siteshield scan
	 *     wp siteshield scan --full
	 */
	public function scan( $args, $assoc_args ) {
		$type = isset( $assoc_args['full'] ) ? 'full' : 'quick';
		\WP_CLI::log( "Iniciando varredura heurística ({$type} scan)..." );
		$results = Malware_Scanner::execute_scan( $type );

		\WP_CLI::success( "Varredura concluída! {$results['total_files']} arquivos auditados." );
		if ( $results['threats_count'] > 0 ) {
			\WP_CLI::warning( "Foram detectadas {$results['threats_count']} ameaças potenciais!" );
		} else {
			\WP_CLI::success( "Nenhum malware detectado. Site limpo!" );
		}
	}

	/**
	 * Executa o monitor de integridade de arquivos.
	 *
	 * ## EXAMPLES
	 *
	 *     wp siteshield integrity
	 */
	public function integrity( $args, $assoc_args ) {
		\WP_CLI::log( "Verificando integridade de arquivos em relação à linha de base..." );
		$results = File_Integrity::check_integrity();
		\WP_CLI::success( "Auditoria de integridade finalizada! {$results['checked']} arquivos verificados." );
		\WP_CLI::log( "Modificados: " . count( $results['modified'] ) . " | Criados: " . count( $results['created'] ) . " | Deletados: " . count( $results['deleted'] ) );
	}

	/**
	 * Gerencia o Modo de Emergência.
	 *
	 * ## OPTIONS
	 *
	 * <action>
	 * : 'enable' para ativar ou 'disable' para desativar.
	 *
	 * ## EXAMPLES
	 *
	 *     wp siteshield emergency enable
	 *     wp siteshield emergency disable
	 */
	public function emergency( $args, $assoc_args ) {
		$action = isset( $args[0] ) ? $args[0] : 'status';

		if ( 'enable' === $action ) {
			Emergency_Mode::enable( 'Acionamento via WP-CLI' );
			\WP_CLI::success( "Modo de Emergência ATIVADO com sucesso!" );
		} elseif ( 'disable' === $action ) {
			Emergency_Mode::disable();
			\WP_CLI::success( "Modo de Emergência DESATIVADO com sucesso." );
		} else {
			\WP_CLI::log( "Status do Modo de Emergência: " . ( Emergency_Mode::is_active() ? "ATIVADO" : "Inativo" ) );
		}
	}
}

\WP_CLI::add_command( 'siteshield', __NAMESPACE__ . '\\WP_CLI_Commands' );
