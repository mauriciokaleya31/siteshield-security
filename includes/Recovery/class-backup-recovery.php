<?php
/**
 * Gerenciador de Backup, Restauração e Exportação de Configurações do SiteShield
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Recovery
 */

namespace SiteShield\Recovery;

use SiteShield\Core\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Backup_Recovery
 */
class Backup_Recovery {

	/**
	 * Cria um backup snapshot das configurações atuais em formato JSON
	 *
	 * @return string JSON das configurações.
	 */
	public static function export_settings_json() {
		$settings = Settings::get();
		$export_data = array(
			'plugin'     => 'SiteShield Security',
			'version'    => SITESHIELD_VERSION,
			'exported_at'=> current_time( 'mysql' ),
			'site_url'   => get_site_url(),
			'settings'   => $settings,
		);

		return wp_json_encode( $export_data, JSON_PRETTY_PRINT );
	}

	/**
	 * Importa e restaura configurações a partir de uma string JSON
	 *
	 * @param string $json_string Dados exportados.
	 * @return bool|\WP_Error
	 */
	public static function import_settings_json( $json_string ) {
		$data = json_decode( $json_string, true );

		if ( empty( $data ) || ! is_array( $data ) || empty( $data['settings'] ) ) {
			return new \WP_Error( 'invalid_json', __( 'Arquivo JSON de configuração inválido ou corrompido.', 'siteshield' ) );
		}

		$current = Settings::get();
		$merged  = wp_parse_args( $data['settings'], $current );

		return Settings::update( $merged );
	}
}
