<?php
/**
 * Despachante de Notificações e Alertas de Segurança do SiteShield
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Notifications
 */

namespace SiteShield\Notifications;

use SiteShield\Core\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Notifier
 */
class Notifier {

	/**
	 * Dispara alerta de segurança via E-mail e/ou Webhook
	 *
	 * @param string $title Título do alerta.
	 * @param string $message Mensagem descritiva.
	 * @param string $severity Severidade ('low', 'medium', 'high', 'critical').
	 */
	public static function send_alert( $title, $message, $severity = 'medium' ) {
		$critical_only = Settings::get( 'notify_email_critical_only', false );

		if ( $critical_only && ! in_array( $severity, array( 'high', 'critical' ), true ) ) {
			return;
		}

		// 1. Envio por E-mail
		if ( Settings::get( 'notify_email_enabled', true ) ) {
			$to      = Settings::get( 'admin_email', get_option( 'admin_email' ) );
			$subject = sprintf( '[SiteShield Security] %s — %s', strtoupper( $severity ), $title );
			$body    = sprintf(
				"Olá,\n\nUm evento de segurança importante ocorreu no seu site (%s):\n\n" .
				"Título: %s\n" .
				"Severidade: %s\n" .
				"Data/Hora: %s\n\n" .
				"Detalhes:\n%s\n\n" .
				"--\nSiteShield Security Center\n%s",
				get_site_url(),
				$title,
				strtoupper( $severity ),
				current_time( 'mysql' ),
				$message,
				admin_url( 'admin.php?page=siteshield-dashboard' )
			);

			@wp_mail( $to, $subject, $body );
		}

		// 2. Envio por Webhook (Discord / Slack / Telegram / Endpoint Personalizado)
		if ( Settings::get( 'notify_webhook_enabled', false ) ) {
			$webhook_url = Settings::get( 'notify_webhook_url', '' );
			if ( ! empty( $webhook_url ) && filter_var( $webhook_url, FILTER_VALIDATE_URL ) ) {
				$payload = array(
					'site_url'   => get_site_url(),
					'title'      => $title,
					'message'    => $message,
					'severity'   => $severity,
					'timestamp'  => current_time( 'mysql' ),
				);

				@wp_remote_post( $webhook_url, array(
					'headers' => array( 'Content-Type' => 'application/json' ),
					'body'    => wp_json_encode( $payload ),
					'timeout' => 5,
				) );
			}
		}
	}
}
