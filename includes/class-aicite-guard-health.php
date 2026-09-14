<?php
/**
 * Site Health Guardian: bloat, conflicts, CWV hints, security surface.
 *
 * @package Aicite_Guard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lightweight plugin and site-health overview.
 */
class Aicite_Guard_Health {

	/**
	 * Build and cache a health report.
	 *
	 * @param bool $force Bypass cache.
	 * @return array<string, mixed>
	 */
	public function report( $force = false ) {
		if ( ! $force ) {
			$cached = get_transient( 'aicite_guard_health_cache' );
			if ( is_array( $cached ) ) {
				return $cached;
			}
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all      = get_plugins();
		$active   = (array) get_option( 'active_plugins', array() );
		$network  = is_multisite() ? array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ) ) : array();
		$active   = array_values( array_unique( array_merge( $active, $network ) ) );
		$updates  = get_site_transient( 'update_plugins' );
		$update   = ( $updates && isset( $updates->response ) && is_array( $updates->response ) ) ? $updates->response : array();

		$plugins = array();
		$unused  = array();
		$heavy   = array();
		$stale   = array();
		$outdated = array();

		foreach ( $all as $file => $data ) {
			$path    = trailingslashit( WP_PLUGIN_DIR ) . $file;
			$folder  = dirname( $path );
			$size    = is_dir( $folder ) ? $this->dir_size( $folder ) : 0;
			$is_on   = in_array( $file, $active, true );
			$mtime   = file_exists( $path ) ? (int) filemtime( $path ) : 0;
			$age     = $mtime ? time() - $mtime : 0;
			$has_upd = isset( $update[ $file ] );

			$row = array(
				'file'        => $file,
				'name'        => $data['Name'],
				'version'     => $data['Version'],
				'active'      => $is_on,
				'size'        => $size,
				'size_label'  => size_format( $size ),
				'age_days'    => $mtime ? (int) floor( $age / DAY_IN_SECONDS ) : 0,
				'update'      => $has_upd,
				'new_version' => $has_upd ? (string) $update[ $file ]->new_version : '',
			);

			$plugins[] = $row;

			if ( ! $is_on ) {
				$unused[] = $row;
			}

			if ( $size >= 5 * MB_IN_BYTES ) {
				$heavy[] = $row;
			}

			if ( $is_on && $age > ( 730 * DAY_IN_SECONDS ) ) {
				$stale[] = $row;
			}

			if ( $has_upd ) {
				$outdated[] = $row;
			}
		}

		usort(
			$plugins,
			function ( $a, $b ) {
				return $b['size'] <=> $a['size'];
			}
		);

		$conflicts = $this->conflicts( $active );
		$cwv       = $this->cwv_overview( count( $active ), $plugins );
		$security  = $this->security_surface( $outdated, $stale );
		$score     = $this->score( $unused, $heavy, $conflicts, $outdated, $stale, count( $active ) );
		$label     = $this->label( $score );

		$report = array(
			'score'       => $score,
			'label'       => $label,
			'summary'     => $this->summary( $score, $unused, $conflicts, $outdated ),
			'plugins'     => $plugins,
			'unused'      => $unused,
			'heavy'       => $heavy,
			'stale'       => $stale,
			'outdated'    => $outdated,
			'conflicts'   => $conflicts,
			'cwv'         => $cwv,
			'security'    => $security,
			'active'      => count( $active ),
			'installed'   => count( $all ),
			'calculated'  => time(),
		);

		set_transient( 'aicite_guard_health_cache', $report, HOUR_IN_SECONDS );
		update_option( 'aicite_guard_health_report', $report, false );

		$scores           = get_option( 'aicite_guard_scores', array() );
		$scores           = is_array( $scores ) ? $scores : array();
		$scores['health'] = array(
			'score'      => $score,
			'label'      => $label,
			'summary'    => $report['summary'],
			'calculated' => time(),
		);
		update_option( 'aicite_guard_scores', $scores, false );

		return $report;
	}

	/**
	 * Known plugin-family conflicts.
	 *
	 * @param string[] $active Active plugin files.
	 * @return array<int, array<string, string>>
	 */
	private function conflicts( $active ) {
		$groups = array(
			array(
				'id'      => 'seo',
				'label'   => __( 'Multiple SEO plugins', 'aicite-guard' ),
				'detail'  => __( 'AIcite Guard works with Yoast and Rank Math, but running two SEO plugins at once often duplicates schema and sitemaps. Keep one as the SEO source of truth.', 'aicite-guard' ),
				'plugins' => array(
					'wordpress-seo/wp-seo.php',
					'seo-by-rank-math/rank-math.php',
					'all-in-one-seo-pack/all_in_one_seo_pack.php',
					'wp-seopress/seopress.php',
				),
			),
			array(
				'id'      => 'cache',
				'label'   => __( 'Multiple caching plugins', 'aicite-guard' ),
				'detail'  => __( 'Two page caches can serve stale or broken pages. Keep a single caching plugin.', 'aicite-guard' ),
				'plugins' => array(
					'litespeed-cache/litespeed-cache.php',
					'wp-super-cache/wp-cache.php',
					'w3-total-cache/w3-total-cache.php',
					'wp-fastest-cache/wpFastestCache.php',
					'wp-rocket/wp-rocket.php',
				),
			),
			array(
				'id'      => 'security',
				'label'   => __( 'Multiple security suites', 'aicite-guard' ),
				'detail'  => __( 'Overlapping firewalls increase load and can lock you out. One well-configured suite is enough.', 'aicite-guard' ),
				'plugins' => array(
					'wordfence/wordfence.php',
					'sucuri-scanner/sucuri.php',
					'better-wp-security/better-wp-security.php',
					'aiowps/all-in-one-wp-security-and-firewall.php',
				),
			),
			array(
				'id'      => 'optimize',
				'label'   => __( 'Multiple asset optimizers', 'aicite-guard' ),
				'detail'  => __( 'Two minifiers often break CSS/JS. Use one optimizer and test the front end.', 'aicite-guard' ),
				'plugins' => array(
					'autoptimize/autoptimize.php',
					'litespeed-cache/litespeed-cache.php',
					'sg-cachepress/sg-cachepress.php',
					'perfmatters/perfmatters.php',
				),
			),
		);

		$found = array();

		foreach ( $groups as $group ) {
			$hits = array_values( array_intersect( $group['plugins'], $active ) );
			if ( count( $hits ) >= 2 ) {
				$names = array();
				foreach ( $hits as $file ) {
					$data = get_plugin_data( trailingslashit( WP_PLUGIN_DIR ) . $file, false, false );
					$names[] = $data['Name'] ? $data['Name'] : $file;
				}

				$found[] = array(
					'id'      => $group['id'],
					'label'   => $group['label'],
					'detail'  => $group['detail'],
					'plugins' => $names,
				);
			}
		}

		return $found;
	}

	/**
	 * Lightweight Core Web Vitals impact overview (no third-party API).
	 *
	 * @param int                                $active_count Active plugins.
	 * @param array<int, array<string, mixed>>   $plugins      Plugin rows.
	 * @return array<string, mixed>
	 */
	private function cwv_overview( $active_count, $plugins ) {
		$autoload = $this->autoload_size();
		$has_cache = $this->has_cache_plugin();
		$https     = is_ssl() || 'https' === wp_parse_url( home_url(), PHP_URL_SCHEME );

		$hints = array();

		if ( $active_count > 25 ) {
			$hints[] = array(
				'status' => 'warn',
				'text'   => __( 'More than 25 active plugins often hurts LCP and INP. Deactivate anything unused.', 'aicite-guard' ),
			);
		} elseif ( $active_count > 15 ) {
			$hints[] = array(
				'status' => 'info',
				'text'   => __( 'Plugin count is moderate. Review heavy plugins below before adding more.', 'aicite-guard' ),
			);
		} else {
			$hints[] = array(
				'status' => 'ok',
				'text'   => __( 'Active plugin count is in a healthy range for most sites.', 'aicite-guard' ),
			);
		}

		if ( $autoload > 1024 * 1024 ) {
			$hints[] = array(
				'status' => 'warn',
				'text'   => __( 'Autoloaded options exceed 1 MB. That delays every page (TTFB). Clean unused options.', 'aicite-guard' ),
			);
		} else {
			$hints[] = array(
				'status' => 'ok',
				'text'   => __( 'Autoloaded options are within a reasonable size.', 'aicite-guard' ),
			);
		}

		$hints[] = array(
			'status' => $has_cache ? 'ok' : 'info',
			'text'   => $has_cache
				? __( 'A caching plugin is active. That usually helps Largest Contentful Paint.', 'aicite-guard' )
				: __( 'No caching plugin detected. Page caching is one of the highest-impact CWV wins.', 'aicite-guard' ),
		);

		$hints[] = array(
			'status' => $https ? 'ok' : 'warn',
			'text'   => $https
				? __( 'HTTPS is on. Browsers can use modern performance features.', 'aicite-guard' )
				: __( 'Serve the site over HTTPS so browsers do not throttle or warn visitors.', 'aicite-guard' ),
		);

		return array(
			'active_plugins'  => $active_count,
			'autoload_bytes'  => $autoload,
			'autoload_label'  => size_format( $autoload ),
			'has_cache'       => $has_cache,
			'insights_url'    => 'https://pagespeed.web.dev/analysis?url=' . rawurlencode( home_url( '/' ) ),
			'hints'           => $hints,
		);
	}

	/**
	 * High-risk reminders.
	 *
	 * @param array<int, array<string, mixed>> $outdated Plugins with updates.
	 * @param array<int, array<string, mixed>> $stale    Very old active plugins.
	 * @return array<int, array<string, string>>
	 */
	private function security_surface( $outdated, $stale ) {
		$items = array();

		if ( $outdated ) {
			$items[] = array(
				'status' => 'warn',
				'title'  => __( 'Plugin updates waiting', 'aicite-guard' ),
				'detail' => sprintf(
					/* translators: %d: number of plugins */
					_n( '%d plugin has an update available.', '%d plugins have updates available.', count( $outdated ), 'aicite-guard' ),
					count( $outdated )
				),
			);
		} else {
			$items[] = array(
				'status' => 'ok',
				'title'  => __( 'No plugin updates queued', 'aicite-guard' ),
				'detail' => __( 'WordPress does not currently list pending plugin updates.', 'aicite-guard' ),
			);
		}

		if ( $stale ) {
			$items[] = array(
				'status' => 'warn',
				'title'  => __( 'Possibly abandoned plugins', 'aicite-guard' ),
				'detail' => __( 'Some active plugins have not changed in about two years. Treat them as a higher risk and look for maintained alternatives.', 'aicite-guard' ),
			);
		}

		if ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ) {
			$items[] = array(
				'status' => 'ok',
				'title'  => __( 'Theme/plugin file editor is disabled', 'aicite-guard' ),
				'detail' => __( 'DISALLOW_FILE_EDIT is on. That reduces the damage if an admin account is compromised.', 'aicite-guard' ),
			);
		} else {
			$items[] = array(
				'status' => 'info',
				'title'  => __( 'Consider disabling the file editor', 'aicite-guard' ),
				'detail' => __( 'Add DISALLOW_FILE_EDIT to wp-config.php so attackers cannot edit PHP from wp-admin.', 'aicite-guard' ),
			);
		}

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ) {
			$items[] = array(
				'status' => 'warn',
				'title'  => __( 'Debug display is on', 'aicite-guard' ),
				'detail' => __( 'Turn off WP_DEBUG_DISPLAY on a live site so errors are not shown to visitors.', 'aicite-guard' ),
			);
		}

		return $items;
	}

	/**
	 * Overall health score.
	 *
	 * @param array<int, mixed> $unused    Unused plugins.
	 * @param array<int, mixed> $heavy     Heavy plugins.
	 * @param array<int, mixed> $conflicts Conflicts.
	 * @param array<int, mixed> $outdated  Updates.
	 * @param array<int, mixed> $stale     Stale plugins.
	 * @param int               $active    Active count.
	 * @return int
	 */
	private function score( $unused, $heavy, $conflicts, $outdated, $stale, $active ) {
		$score  = 100;
		$score -= min( 25, count( $unused ) * 4 );
		$score -= min( 15, count( $heavy ) * 3 );
		$score -= min( 20, count( $conflicts ) * 10 );
		$score -= min( 20, count( $outdated ) * 5 );
		$score -= min( 15, count( $stale ) * 7 );

		if ( $active > 30 ) {
			$score -= 15;
		} elseif ( $active > 20 ) {
			$score -= 8;
		}

		return max( 10, min( 100, $score ) );
	}

	/**
	 * Score label.
	 *
	 * @param int $score Score.
	 * @return string
	 */
	private function label( $score ) {
		if ( $score >= 80 ) {
			return __( 'Healthy', 'aicite-guard' );
		}
		if ( $score >= 55 ) {
			return __( 'Watch closely', 'aicite-guard' );
		}

		return __( 'Overloaded', 'aicite-guard' );
	}

	/**
	 * Plain-English summary.
	 *
	 * @param int               $score     Score.
	 * @param array<int, mixed> $unused    Unused.
	 * @param array<int, mixed> $conflicts Conflicts.
	 * @param array<int, mixed> $outdated  Updates.
	 * @return string
	 */
	private function summary( $score, $unused, $conflicts, $outdated ) {
		if ( $score >= 80 ) {
			return __( 'The install looks lean. Re-scan after you add plugins or skip updates for a while.', 'aicite-guard' );
		}

		$parts = array();
		if ( $unused ) {
			$parts[] = sprintf(
				/* translators: %d: unused plugin count */
				_n( '%d inactive plugin can be removed', '%d inactive plugins can be removed', count( $unused ), 'aicite-guard' ),
				count( $unused )
			);
		}
		if ( $conflicts ) {
			$parts[] = __( 'overlapping plugins were detected', 'aicite-guard' );
		}
		if ( $outdated ) {
			$parts[] = __( 'updates are waiting', 'aicite-guard' );
		}

		if ( ! $parts ) {
			return __( 'A few items are adding weight. Review the lists below.', 'aicite-guard' );
		}

		return ucfirst( implode( ', ', $parts ) ) . '.';
	}

	/**
	 * Recursively measure a directory with a hard file cap.
	 *
	 * @param string $dir Directory.
	 * @return int
	 */
	private function dir_size( $dir ) {
		$size  = 0;
		$count = 0;

		try {
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS )
			);
		} catch ( Exception $e ) {
			return 0;
		}

		foreach ( $iterator as $file ) {
			if ( ++$count > 4000 ) {
				break;
			}
			if ( $file->isFile() ) {
				$size += (int) $file->getSize();
			}
		}

		return $size;
	}

	/**
	 * Autoloaded options payload size.
	 *
	 * @return int
	 */
	private function autoload_size() {
		global $wpdb;

		$bytes = $wpdb->get_var( "SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options} WHERE autoload IN ('yes','on')" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

		return $bytes ? (int) $bytes : 0;
	}

	/**
	 * Whether a common cache plugin is active.
	 *
	 * @return bool
	 */
	private function has_cache_plugin() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$candidates = array(
			'litespeed-cache/litespeed-cache.php',
			'wp-super-cache/wp-cache.php',
			'w3-total-cache/w3-total-cache.php',
			'wp-fastest-cache/wpFastestCache.php',
			'wp-rocket/wp-rocket.php',
			'sg-cachepress/sg-cachepress.php',
			'cache-enabler/cache-enabler.php',
		);

		foreach ( $candidates as $file ) {
			if ( is_plugin_active( $file ) ) {
				return true;
			}
		}

		return false;
	}
}
