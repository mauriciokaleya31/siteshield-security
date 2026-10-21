<?php
/**
 * Container Principal e Inicializador de Serviços do SiteShield
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Core
 */

namespace SiteShield\Core;

use SiteShield\Firewall\Firewall_Engine;
use SiteShield\Firewall\Rate_Limiter;
use SiteShield\Login\Login_Security;
use SiteShield\Login\Two_Factor;
use SiteShield\Scanner\Scan_Scheduler;
use SiteShield\AntiBot\AntiBot_Engine;
use SiteShield\AntiClone\Content_Protection;
use SiteShield\Headers\Security_Headers;
use SiteShield\Visitors\Visitor_Tracker;
use SiteShield\Audit\Audit_Logger;
use SiteShield\API\REST_API;
use SiteShield\Admin\Admin_Controller;
use SiteShield\Admin\Setup_Wizard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Plugin
 */
class Plugin {

	/**
	 * Instância única (Singleton)
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Retorna a instância única
	 *
	 * @return Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Construtor protegido
	 */
	private function __construct() {
		$this->load_modules();
	}

	/**
	 * Carrega e inicializa todos os subsistemas modulares com isolamento contra exceções
	 */
	private function load_modules() {
		// 0. Internacionalização e Idiomas
		try {
			\SiteShield\I18n\I18n::init();
		} catch ( \Throwable $e ) {
			error_log( 'SiteShield I18n Init Notice: ' . $e->getMessage() );
		}

		// 1. Auditoria e Logs
		try {
			Audit_Logger::init();
		} catch ( \Throwable $e ) {
			error_log( 'SiteShield Audit Init Notice: ' . $e->getMessage() );
		}

		// 2. Estatísticas de Visitantes & Privacidade
		try {
			Visitor_Tracker::init();
		} catch ( \Throwable $e ) {
			error_log( 'SiteShield Visitor Init Notice: ' . $e->getMessage() );
		}

		// 3. Firewall WAF & Rate Limiting
		try {
			Firewall_Engine::init();
			Rate_Limiter::init();
		} catch ( \Throwable $e ) {
			error_log( 'SiteShield Firewall Init Notice: ' . $e->getMessage() );
		}

		// 4. Segurança de Login & 2FA
		try {
			Login_Security::init();
			Two_Factor::init();
		} catch ( \Throwable $e ) {
			error_log( 'SiteShield Login Init Notice: ' . $e->getMessage() );
		}

		// 5. Anti-Bot & Proteção de Conteúdo
		try {
			AntiBot_Engine::init();
			Content_Protection::init();
		} catch ( \Throwable $e ) {
			error_log( 'SiteShield AntiBot Init Notice: ' . $e->getMessage() );
		}

		// 6. Cabeçalhos HTTP de Segurança & Hardening
		try {
			Security_Headers::init();
			\SiteShield\Hardening\WP_Hardening::init();
		} catch ( \Throwable $e ) {
			error_log( 'SiteShield Headers Init Notice: ' . $e->getMessage() );
		}

		// 7. Agendamento do Scanner em Background
		try {
			Scan_Scheduler::init();
		} catch ( \Throwable $e ) {
			error_log( 'SiteShield Scanner Init Notice: ' . $e->getMessage() );
		}

		// 8. API REST
		try {
			REST_API::init();
		} catch ( \Throwable $e ) {
			error_log( 'SiteShield REST Init Notice: ' . $e->getMessage() );
		}

		// 9. Painel Administrativo & Onboarding
		try {
			Admin_Controller::init();
			Setup_Wizard::init();
		} catch ( \Throwable $e ) {
			error_log( 'SiteShield Admin Init Notice: ' . $e->getMessage() );
		}

		// 10. Selo de Verificação SiteShield Verified
		try {
			\SiteShield\Badge\Verification_Badge::init();
		} catch ( \Throwable $e ) {
			error_log( 'SiteShield Badge Init Notice: ' . $e->getMessage() );
		}

		// 11. WP-CLI (se acionado via terminal)
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			try {
				require_once SITESHIELD_PATH . 'includes/CLI/class-wp-cli.php';
			} catch ( \Throwable $e ) {
				error_log( 'SiteShield CLI Init Notice: ' . $e->getMessage() );
			}
		}
	}
}
