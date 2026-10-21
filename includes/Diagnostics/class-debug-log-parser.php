<?php
/**
 * Analisador Estruturado e Parser de Alta Performance do debug.log
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Diagnostics
 */

namespace SiteShield\Diagnostics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Debug_Log_Parser
 */
class Debug_Log_Parser {

	/**
	 * Retorna o caminho absoluto do arquivo debug.log do WordPress
	 *
	 * @return string
	 */
	public static function get_debug_log_path() {
		if ( defined( 'WP_CONTENT_DIR' ) && file_exists( WP_CONTENT_DIR . '/debug.log' ) ) {
			return WP_CONTENT_DIR . '/debug.log';
		}
		if ( defined( 'ABSPATH' ) && file_exists( ABSPATH . 'wp-content/debug.log' ) ) {
			return ABSPATH . 'wp-content/debug.log';
		}
		$ini_log = ini_get( 'error_log' );
		if ( ! empty( $ini_log ) && file_exists( $ini_log ) ) {
			return $ini_log;
		}
		return defined( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR . '/debug.log' : '';
	}

	/**
	 * Retorna o status das diretivas de debug do WordPress
	 *
	 * @return array
	 */
	public static function get_debug_status() {
		$log_path   = self::get_debug_log_path();
		$file_exists= file_exists( $log_path );
		$file_size  = $file_exists ? filesize( $log_path ) : 0;

		return array(
			'wp_debug'         => defined( 'WP_DEBUG' ) && WP_DEBUG,
			'wp_debug_log'     => defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG,
			'wp_debug_display' => defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY,
			'log_path'         => $log_path,
			'log_exists'       => $file_exists,
			'log_size'         => $file_size,
			'log_size_human'   => $file_exists ? size_format( $file_size ) : '0 KB',
			'log_writable'     => $file_exists ? is_writable( $log_path ) : ( is_dir( dirname( $log_path ) ) && is_writable( dirname( $log_path ) ) ),
		);
	}

	/**
	 * Lê e processa os eventos do debug.log agrupando erros idênticos
	 *
	 * @param int    $max_lines Quantidade máxima de linhas brutas a ler do final do arquivo.
	 * @param string $severity_filter Filtro de severidade (ALL, CRITICAL, ERROR, WARNING, INFO).
	 * @param string $origin_filter Filtro de origem (ALL, SITESHIELD, WORDPRESS, PHP, PLUGIN, THEME).
	 * @param string $search Termo de pesquisa textual.
	 * @return array
	 */
	public static function parse( $max_lines = 500, $severity_filter = 'ALL', $origin_filter = 'ALL', $search = '' ) {
		$log_path = self::get_debug_log_path();
		if ( ! file_exists( $log_path ) || ! is_readable( $log_path ) ) {
			return array(
				'stats'  => self::get_empty_stats(),
				'groups' => array(),
				'total'  => 0,
			);
		}

		$raw_lines = Log_Engine::read_file_tail_lines( $log_path, $max_lines );
		if ( empty( $raw_lines ) ) {
			return array(
				'stats'  => self::get_empty_stats(),
				'groups' => array(),
				'total'  => 0,
			);
		}

		// Processa as linhas em blocos cronológicos
		$parsed_events = array();
		$current_event = null;

		foreach ( $raw_lines as $line ) {
			$line = trim( $line );
			if ( empty( $line ) ) {
				continue;
			}

			// Padrão de linha de log do WordPress: [30-Aug-2026 08:13:39 UTC] ...
			if ( preg_match( '/^\[([0-9]{2}-[a-zA-Z]{3}-[0-9]{4}\s+[0-9]{2}:[0-9]{2}:[0-9]{2}\s*[^\]]*)\]\s*(.*)$/', $line, $matches ) ) {
				if ( null !== $current_event ) {
					$parsed_events[] = self::finalize_event( $current_event );
				}

				$current_event = array(
					'raw_timestamp' => $matches[1],
					'raw_message'   => $matches[2],
					'stack_trace'   => array(),
				);
			} elseif ( null !== $current_event ) {
				// Linhas adicionais (ex: Stack trace, #0, #1...)
				if ( false !== strpos( $line, 'Stack trace:' ) || preg_match( '/^#[0-9]+/', $line ) || false !== strpos( $line, 'thrown in' ) ) {
					$current_event['stack_trace'][] = $line;
				} else {
					$current_event['raw_message'] .= "\n" . $line;
				}
			}
		}

		if ( null !== $current_event ) {
			$parsed_events[] = self::finalize_event( $current_event );
		}

		// Estatísticas globais
		$stats = array(
			'critical'   => 0,
			'errors'     => 0,
			'warnings'   => 0,
			'notices'    => 0,
			'siteshield' => 0,
			'wordpress'  => 0,
			'total'      => count( $parsed_events ),
			'last_error' => ! empty( $parsed_events ) ? $parsed_events[0]['formatted_time'] : 'Nenhum',
		);

		// Agrupamento e Deduplicação Inteligente
		$groups = array();

		foreach ( $parsed_events as $ev ) {
			// Incrementa contadores
			if ( 'CRITICAL' === $ev['severity'] ) {
				$stats['critical']++;
			} elseif ( 'ERROR' === $ev['severity'] ) {
				$stats['errors']++;
			} elseif ( 'WARNING' === $ev['severity'] ) {
				$stats['warnings']++;
			} else {
				$stats['notices']++;
			}

			if ( 'SiteShield' === $ev['origin'] ) {
				$stats['siteshield']++;
			} elseif ( 'WordPress' === $ev['origin'] ) {
				$stats['wordpress']++;
			}

			// Filtros de visualização
			if ( 'ALL' !== $severity_filter && strtoupper( $ev['severity'] ) !== strtoupper( $severity_filter ) ) {
				continue;
			}
			if ( 'ALL' !== $origin_filter && strtoupper( $ev['origin'] ) !== strtoupper( $origin_filter ) ) {
				continue;
			}
			if ( ! empty( $search ) ) {
				$search_lc = strtolower( $search );
				$haystack  = strtolower( $ev['message'] . ' ' . $ev['file'] . ' ' . $ev['module'] . ' ' . $ev['type'] );
				if ( false === strpos( $haystack, $search_lc ) ) {
					continue;
				}
			}

			$fingerprint = md5( $ev['type'] . '|' . $ev['file'] . '|' . $ev['line'] . '|' . substr( $ev['message'], 0, 80 ) );

			if ( ! isset( $groups[ $fingerprint ] ) ) {
				$groups[ $fingerprint ] = array(
					'fingerprint'  => $fingerprint,
					'type'         => $ev['type'],
					'severity'     => $ev['severity'],
					'origin'       => $ev['origin'],
					'module'       => $ev['module'],
					'file'         => $ev['file'],
					'line'         => $ev['line'],
					'message'      => $ev['message'],
					'explanation'  => $ev['explanation'],
					'occurrences'  => 1,
					'first_seen'   => $ev['formatted_time'],
					'last_seen'    => $ev['formatted_time'],
					'first_ts'     => $ev['timestamp'],
					'last_ts'      => $ev['timestamp'],
					'instances'    => array( $ev ),
				);
			} else {
				$groups[ $fingerprint ]['occurrences']++;
				$groups[ $fingerprint ]['instances'][] = $ev;
				if ( $ev['timestamp'] > $groups[ $fingerprint ]['last_ts'] ) {
					$groups[ $fingerprint ]['last_seen'] = $ev['formatted_time'];
					$groups[ $fingerprint ]['last_ts']   = $ev['timestamp'];
				}
				if ( $ev['timestamp'] < $groups[ $fingerprint ]['first_ts'] ) {
					$groups[ $fingerprint ]['first_seen'] = $ev['formatted_time'];
					$groups[ $fingerprint ]['first_ts']   = $ev['timestamp'];
				}
			}
		}

		// Ordena os grupos do mais recente para o mais antigo
		$sorted_groups = array_values( $groups );
		usort( $sorted_groups, function( $a, $b ) {
			return $b['last_ts'] - $a['last_ts'];
		} );

		return array(
			'stats'  => $stats,
			'groups' => $sorted_groups,
			'total'  => count( $sorted_groups ),
		);
	}

	/**
	 * Converte os dados brutos de um bloco em um evento estruturado
	 *
	 * @param array $raw_event Evento com texto cru.
	 * @return array
	 */
	private static function finalize_event( array $raw_event ) {
		$raw_msg   = $raw_event['raw_message'];
		$ts_str    = $raw_event['raw_timestamp'];
		$timestamp = strtotime( $ts_str );

		$type     = 'PHP Notice';
		$severity = 'INFO';
		$origin   = 'WordPress';
		$file     = '';
		$line     = 0;
		$module   = 'WordPress Core';
		$message  = $raw_msg;

		// 1. Detecção de Erro Específico do SiteShield
		if ( false !== strpos( $raw_msg, '[SiteShield Security Error]' ) || false !== strpos( $raw_msg, 'SiteShield' ) ) {
			$origin   = 'SiteShield';
			$module   = 'SiteShield Security';
			$severity = 'ERROR';

			if ( false !== strpos( $raw_msg, 'Falha ação carregar' ) ) {
				$type     = 'SiteShield Bootstrap Error';
				$severity = 'CRITICAL';
			} elseif ( false !== strpos( $raw_msg, 'WAF' ) ) {
				$module = 'SiteShield WAF';
			} elseif ( false !== strpos( $raw_msg, 'Scanner' ) ) {
				$module = 'SiteShield Scanner';
			}
		}

		// 2. Classificação de Tipos do PHP
		if ( preg_match( '/PHP Fatal error:\s*(.*?)(?:\s+in\s+(.*?)\s+on\s+line\s+([0-9]+))?$/s', $raw_msg, $m ) ) {
			$type     = 'PHP Fatal Error';
			$severity = 'CRITICAL';
			$message  = ! empty( $m[1] ) ? $m[1] : $raw_msg;
			$file     = ! empty( $m[2] ) ? $m[2] : '';
			$line     = ! empty( $m[3] ) ? intval( $m[3] ) : 0;
		} elseif ( preg_match( '/PHP Parse error:\s*(.*?)(?:\s+in\s+(.*?)\s+on\s+line\s+([0-9]+))?$/s', $raw_msg, $m ) ) {
			$type     = 'PHP Parse Error';
			$severity = 'CRITICAL';
			$message  = ! empty( $m[1] ) ? $m[1] : $raw_msg;
			$file     = ! empty( $m[2] ) ? $m[2] : '';
			$line     = ! empty( $m[3] ) ? intval( $m[3] ) : 0;
		} elseif ( preg_match( '/PHP Warning:\s*(.*?)(?:\s+in\s+(.*?)\s+on\s+line\s+([0-9]+))?$/s', $raw_msg, $m ) ) {
			$type     = 'PHP Warning';
			$severity = 'WARNING';
			$message  = ! empty( $m[1] ) ? $m[1] : $raw_msg;
			$file     = ! empty( $m[2] ) ? $m[2] : '';
			$line     = ! empty( $m[3] ) ? intval( $m[3] ) : 0;
		} elseif ( preg_match( '/PHP Notice:\s*(.*?)(?:\s+in\s+(.*?)\s+on\s+line\s+([0-9]+))?$/s', $raw_msg, $m ) ) {
			$type     = 'PHP Notice';
			$severity = 'INFO';
			$message  = ! empty( $m[1] ) ? $m[1] : $raw_msg;
			$file     = ! empty( $m[2] ) ? $m[2] : '';
			$line     = ! empty( $m[3] ) ? intval( $m[3] ) : 0;
		} elseif ( preg_match( '/PHP Deprecated:\s*(.*?)(?:\s+in\s+(.*?)\s+on\s+line\s+([0-9]+))?$/s', $raw_msg, $m ) ) {
			$type     = 'PHP Deprecated';
			$severity = 'INFO';
			$message  = ! empty( $m[1] ) ? $m[1] : $raw_msg;
			$file     = ! empty( $m[2] ) ? $m[2] : '';
			$line     = ! empty( $m[3] ) ? intval( $m[3] ) : 0;
		} elseif ( false !== strpos( $raw_msg, 'WordPress database error' ) ) {
			$type     = 'WordPress Database Error';
			$severity = 'ERROR';
			$origin   = 'WordPress Database';
			$module   = 'MySQL Query';
		}

		// 3. Extração de Arquivo e Linha caso esteja no padrão "in /path/to/file on line 123"
		if ( empty( $file ) && preg_match( '/in\s+([^\s]+)\s+on\s+line\s+([0-9]+)/i', $raw_msg, $m ) ) {
			$file = $m[1];
			$line = intval( $m[2] );
		}

		// 4. Identificação refinada de Origem com base no caminho do arquivo
		if ( ! empty( $file ) ) {
			if ( false !== strpos( $file, 'siteshield' ) ) {
				$origin = 'SiteShield';
				$module = basename( $file, '.php' );
			} elseif ( false !== strpos( $file, 'wp-content/plugins/' ) ) {
				$origin = 'Plugin Externo';
				preg_match( '/plugins\/([^\/]+)/', $file, $pm );
				$module = ! empty( $pm[1] ) ? $pm[1] : 'Plugin';
			} elseif ( false !== strpos( $file, 'wp-content/themes/' ) ) {
				$origin = 'Tema';
				preg_match( '/themes\/([^\/]+)/', $file, $tm );
				$module = ! empty( $tm[1] ) ? $tm[1] : 'Tema';
			} elseif ( false !== strpos( $file, 'wp-includes/' ) || false !== strpos( $file, 'wp-admin/' ) ) {
				$origin = 'WordPress Core';
				$module = basename( $file );
			}
		}

		// Limpa o caminho absoluto para exibição amigável
		$clean_file = $file;
		if ( defined( 'ABSPATH' ) && ! empty( $file ) ) {
			$clean_file = str_replace( ABSPATH, '', $file );
		}

		// Gera a explicação baseada em fatos
		$explanation = Error_Knowledge_Base::explain( $type, $message, $file, $line, $origin );

		return array(
			'timestamp'      => $timestamp ? $timestamp : time(),
			'formatted_time' => $timestamp ? date_i18n( 'd/m/Y H:i:s', $timestamp ) : $ts_str,
			'type'           => $type,
			'severity'       => $severity,
			'origin'         => $origin,
			'module'         => $module,
			'file'           => $clean_file,
			'full_file'      => $file,
			'line'           => $line,
			'message'        => trim( $message ),
			'raw_message'    => $raw_msg,
			'stack_trace'    => $raw_event['stack_trace'],
			'explanation'    => $explanation,
		);
	}

	/**
	 * Retorna a estrutura vazia de estatísticas
	 *
	 * @return array
	 */
	private static function get_empty_stats() {
		return array(
			'critical'   => 0,
			'errors'     => 0,
			'warnings'   => 0,
			'notices'    => 0,
			'siteshield' => 0,
			'wordpress'  => 0,
			'total'      => 0,
			'last_error' => 'Nenhum registro',
		);
	}
}
