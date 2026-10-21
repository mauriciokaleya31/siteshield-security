<?php
/**
 * Agendador de Varreduras em Background (WP-Cron) do SiteShield
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Scanner
 */

namespace SiteShield\Scanner;

use SiteShield\Core\Settings;
use SiteShield\Notifications\Notifier;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Scan_Scheduler
 */
class Scan_Scheduler {

	/**
	 * Hook do WP-Cron
	 */
	const CRON_HOOK = 'siteshield_scheduled_scan_event';

	/**
	 * Inicializa o agendamento
	 */
	public static function init() {
		add_filter( 'cron_schedules', array( __CLASS__, 'add_cron_schedules' ) );
		add_action( self::CRON_HOOK, array( __CLASS__, 'run_scheduled_scan' ) );
		add_action( 'init', array( __CLASS__, 'check_schedule' ), 20 );
	}

	/**
	 * Registra frequências customizadas no WP-Cron
	 *
	 * @param array $schedules Lista de agendamentos.
	 * @return array
	 */
	public static function add_cron_schedules( $schedules ) {
		$has_init = function_exists( 'did_action' ) && did_action( 'init' );
		if ( ! isset( $schedules['weekly'] ) ) {
			$schedules['weekly'] = array(
				'interval' => 7 * DAY_IN_SECONDS,
				'display'  => $has_init ? __( 'Uma vez por semana', 'siteshield' ) : 'Uma vez por semana',
			);
		}
		if ( ! isset( $schedules['monthly'] ) ) {
			$schedules['monthly'] = array(
				'interval' => 30 * DAY_IN_SECONDS,
				'display'  => $has_init ? __( 'Uma vez por mês', 'siteshield' ) : 'Uma vez por mês',
			);
		}
		return $schedules;
	}

	/**
	 * Garante que o evento de cron esteja agendado se ativado nas configurações
	 */
	public static function check_schedule() {
		$enabled = Settings::get( 'scanner_scheduled_enabled', true );

		if ( $enabled && ! wp_next_scheduled( self::CRON_HOOK ) ) {
			$frequency = Settings::get( 'scanner_frequency', 'weekly' );
			$recurrence = 'weekly';
			if ( 'daily' === $frequency ) {
				$recurrence = 'daily';
			} elseif ( 'monthly' === $frequency ) {
				$recurrence = 'monthly';
			}

			wp_schedule_event( time() + HOUR_IN_SECONDS, $recurrence, self::CRON_HOOK );
		} elseif ( ! $enabled && wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_clear_scheduled_hook( self::CRON_HOOK );
		}
	}

	/**
	 * Executa a varredura agendada em background
	 */
	public static function run_scheduled_scan() {
		$scan_type = Settings::get( 'scanner_scan_type', 'quick' );
		$results   = Malware_Scanner::execute_scan( $scan_type );

		// Se encontrar ameaças, envia alerta por e-mail/webhook
		if ( ! empty( $results['threats_count'] ) && $results['threats_count'] > 0 ) {
			Notifier::send_alert(
				'Ameaças Detectadas na Varredura Agendada',
				sprintf(
					/* translators: %d: quantidade de ameaças */
					__( 'O Scanner de Malware do SiteShield detectou %d arquivos suspeitos durante a varredura automática.', 'siteshield' ),
					$results['threats_count']
				),
				'high'
			);
		}
	}
}
