<?php
/**
 * Gerenciador de Internacionalização (i18n) e Idiomas do SiteShield
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage I18n
 */

namespace SiteShield\I18n;

use SiteShield\Core\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe I18n
 */
class I18n {

	/**
	 * Lista de idiomas suportados
	 *
	 * @var array
	 */
	private static $supported_locales = array(
		'pt_PT' => array(
			'name'       => 'Português',
			'native'     => 'Português (Portugal)',
			'flag'       => 'PT',
			'code'       => 'pt',
		),
		'en_US' => array(
			'name'       => 'English',
			'native'     => 'English (US)',
			'flag'       => 'US',
			'code'       => 'en',
		),
		'es_ES' => array(
			'name'       => 'Español',
			'native'     => 'Español (España)',
			'flag'       => 'ES',
			'code'       => 'es',
		),
		'fr_FR' => array(
			'name'       => 'Français',
			'native'     => 'Français (France)',
			'flag'       => 'FR',
			'code'       => 'fr',
		),
	);

	/**
	 * Inicializa o subsistema de idiomas
	 */
	public static function init() {
		// Carrega o domínio de tradução
		add_action( 'init', array( __CLASS__, 'load_textdomain' ), 1 );

		// Filtra o locale para as páginas administrativas do SiteShield
		add_filter( 'plugin_locale', array( __CLASS__, 'filter_plugin_locale' ), 10, 2 );
	}

	/**
	 * Retorna os idiomas disponíveis
	 *
	 * @return array
	 */
	public static function get_supported_locales() {
		return self::$supported_locales;
	}

	/**
	 * Retorna o locale ativo atualmente para o SiteShield
	 *
	 * @return string
	 */
	public static function get_current_locale() {
		try {
			// 1. Preferência do usuário logado
			if ( function_exists( 'get_current_user_id' ) ) {
				$user_id = get_current_user_id();
				if ( $user_id > 0 ) {
					$user_locale = get_user_meta( $user_id, 'siteshield_locale', true );
					if ( ! empty( $user_locale ) && isset( self::$supported_locales[ $user_locale ] ) ) {
						return $user_locale;
					}
				}
			}

			// 2. Configuração global do plugin
			if ( class_exists( '\SiteShield\Core\Settings' ) ) {
				$global_locale = Settings::get( 'locale', 'pt_PT' );
				if ( isset( self::$supported_locales[ $global_locale ] ) ) {
					return $global_locale;
				}
			}
		} catch ( \Throwable $e ) {
			// Fallback seguro
		}

		// 3. Padrão nativo do SiteShield: Português
		return 'pt_PT';
	}

	/**
	 * Define o idioma ativo para o usuário e globalmente
	 *
	 * @param string $locale Código do locale (ex: 'pt_PT', 'en_US').
	 * @return bool
	 */
	public static function set_locale( $locale ) {
		if ( ! isset( self::$supported_locales[ $locale ] ) ) {
			return false;
		}

		if ( function_exists( 'get_current_user_id' ) ) {
			$user_id = get_current_user_id();
			if ( $user_id > 0 ) {
				update_user_meta( $user_id, 'siteshield_locale', $locale );
			}
		}

		if ( class_exists( '\SiteShield\Core\Settings' ) ) {
			Settings::update( array( 'locale' => $locale ) );
		}

		// Recarrega o textdomain no novo idioma
		self::load_textdomain();

		return true;
	}

	/**
	 * Carrega o catálogo de traduções do plugin
	 */
	public static function load_textdomain() {
		try {
			$locale = self::get_current_locale();

			// Descarrega domínio anterior se já carregado
			if ( function_exists( 'unload_textdomain' ) ) {
				unload_textdomain( 'siteshield' );
			}

			// Carrega arquivo .mo específico da pasta languages/
			$mofile = SITESHIELD_PATH . 'languages/siteshield-' . $locale . '.mo';

			if ( file_exists( $mofile ) && function_exists( 'load_textdomain' ) ) {
				load_textdomain( 'siteshield', $mofile );
			} elseif ( function_exists( 'load_plugin_textdomain' ) ) {
				load_plugin_textdomain( 'siteshield', false, dirname( plugin_basename( SITESHIELD_FILE ) ) . '/languages' );
			}
		} catch ( \Throwable $e ) {
			error_log( 'SiteShield I18n Notice: ' . $e->getMessage() );
		}
	}

	/**
	 * Filtra o locale do plugin para corresponder à preferência do SiteShield
	 *
	 * @param string $locale Locale atual.
	 * @param string $domain Domínio de tradução.
	 * @return string
	 */
	public static function filter_plugin_locale( $locale, $domain ) {
		if ( 'siteshield' === $domain ) {
			return self::get_current_locale();
		}
		return $locale;
	}
}
