<?php
/**
 * Template loading with theme-overridable files.
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Shaparak_Template
 */
class Shaparak_Template {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
		add_action( 'template_redirect', array( __CLASS__, 'handle_notify' ) );
	}

	/**
	 * Handle the notification sign-up form (no gateway required, stores a private record).
	 */
	public static function handle_notify() {
		if ( empty( $_POST['shaparak_notify'] ) || ! isset( $_POST['shaparak_notify_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_key( $_POST['shaparak_notify_nonce'] ), 'shaparak_notify' ) ) {
			return;
		}
		if ( ! empty( $_POST['shaparak_website'] ) ) {
			return; // honeypot.
		}

		$phone = isset( $_POST['shaparak_notify_phone'] ) ? Shaparak_Jalali::to_en( sanitize_text_field( wp_unslash( $_POST['shaparak_notify_phone'] ) ) ) : '';
		$phone = preg_replace( '/[^0-9]/', '', $phone );

		$redirect = wp_get_referer() ? wp_get_referer() : home_url( '/' );
		$key      = 'shaparak_notify_' . ( get_the_ID() ? get_the_ID() : 0 );

		if ( ! preg_match( '/^(?:98|0)?9[0-9]{9}$/', $phone ) ) {
			set_transient( $key . '_err', __( 'شماره همراه معتبر نیست.', 'shaparak-events' ), 60 );
			wp_safe_redirect( $redirect );
			exit;
		}

		global $wpdb;
		$exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_title = %s LIMIT 1", // phpcs:ignore WordPress.DB.PreparedSQL
				Shaparak_Post_Types::SUBSCRIBER,
				$phone
			)
		);

		if ( ! $exists ) {
			wp_insert_post(
				array(
					'post_type'   => Shaparak_Post_Types::SUBSCRIBER,
					'post_status' => 'private',
					'post_title'  => $phone,
				)
			);
		}

		$event_id = get_the_ID() ? get_the_ID() : 0;
		update_post_meta(
			$event_id,
			'_event_notify_prefs',
			array(
				'weekly'   => ! empty( $_POST['shaparak_notify_weekly'] ),
				'province' => ! empty( $_POST['shaparak_notify_province'] ),
			)
		);

		set_transient( $key . '_ok', 1, 60 );
		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Add a class to <body> on event screens so the stylesheet can scope itself.
	 *
	 * @param array $classes Classes.
	 * @return array
	 */
	public static function body_class( $classes ) {
		if ( is_singular( Shaparak_Post_Types::POST_TYPE ) || is_post_type_archive( Shaparak_Post_Types::POST_TYPE ) || is_tax( Shaparak_Post_Types::CATEGORY ) ) {
			$classes[] = 'shaparak-events-screen';
		}
		return $classes;
	}

	/**
	 * Locate a template: theme override first, then the plugin copy.
	 *
	 * @param string $name File name without extension.
	 * @return string|null
	 */
	public static function locate( $name ) {
		$file = $name . '.php';
		$theme_file = trailingslashit( get_stylesheet_directory() ) . 'shaparak-events/' . $file;
		if ( file_exists( $theme_file ) ) {
			return $theme_file;
		}
		$theme_file = trailingslashit( get_template_directory() ) . 'shaparak-events/' . $file;
		if ( file_exists( $theme_file ) ) {
			return $theme_file;
		}
		$plugin_file = SHAPARAK_EVENTS_PATH . 'templates/' . $file;
		if ( file_exists( $plugin_file ) ) {
			return $plugin_file;
		}
		return null;
	}

	/**
	 * Render a template part (theme override aware).
	 *
	 * @param string $name Part name, e.g. "card-event".
	 * @param array  $args Data exposed as variables.
	 */
	public static function part( $name, $args = array() ) {
		$file = self::locate( $name );
		if ( ! $file ) {
			return;
		}
		$args = array_merge(
			array(
				'post'   => null,
				'layout' => 'grid',
			),
			$args
		);
		extract( $args, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract
		include $file;
	}

	/**
	 * Shamsi countdown markup (shared by the shortcode and the Elementor widget).
	 *
	 * @param int    $post_id Post ID.
	 * @param string $label   Optional custom label.
	 * @return string
	 */
	public static function countdown_html( $post_id, $label = '' ) {
		$state = Shaparak_Query::state( $post_id );
		$start = Shaparak_Query::start_timestamp( $post_id );
		$end   = Shaparak_Query::end_timestamp( $post_id );

		if ( 'past' === $state ) {
			return '<div class="shaparak-countdown shaparak-countdown--done"><span>' . esc_html__( 'این رویداد خاتمه یافته است', 'shaparak-events' ) . '</span></div>';
		}

		$target = ( 'current' === $state ) ? $end : $start;
		$label  = $label ? $label : ( ( 'current' === $state ) ? __( 'تا پایان رویداد', 'shaparak-events' ) : __( 'تا شروع رویداد', 'shaparak-events' ) );
		$diff   = max( 0, $target - time() );

		$days   = (int) floor( $diff / DAY_IN_SECONDS );
		$hours  = (int) floor( ( $diff % DAY_IN_SECONDS ) / HOUR_IN_SECONDS );
		$mins   = (int) floor( ( $diff % HOUR_IN_SECONDS ) / MINUTE_IN_SECONDS );

		$units = array(
			array( $days, __( 'روز', 'shaparak-events' ) ),
			array( $hours, __( 'ساعت', 'shaparak-events' ) ),
			array( $mins, __( 'دقیقه', 'shaparak-events' ) ),
		);

		ob_start();
		?>
		<div class="shaparak-countdown" data-shaparak-countdown data-target="<?php echo esc_attr( $target ); ?>">
			<span class="shaparak-countdown__label"><?php echo esc_html( $label ); ?></span>
			<div class="shaparak-countdown__units">
				<?php foreach ( $units as $unit ) : ?>
					<div class="shaparak-countdown__unit">
						<strong><?php echo esc_html( Shaparak_Jalali::to_fa( $unit[0] ) ); ?></strong>
						<span><?php echo esc_html( $unit[1] ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Shamsi month calendar markup.
	 *
	 * @param string $month Optional "YYYY-MM" in Shamsi digits/ASCII.
	 * @return string
	 */
	public static function calendar_html( $month = '' ) {
		$now = Shaparak_Jalali::parts();

		$month = Shaparak_Jalali::to_en( (string) $month );
		if ( preg_match( '/^(\d{4})[-\/](\d{1,2})$/', $month, $m ) ) {
			$year  = (int) $m[1];
			$mon   = max( 1, min( 12, (int) $m[2] ) );
		} elseif ( isset( $_GET['event_month'] ) && preg_match( '/^(\d{4})[-\/](\d{1,2})$/', Shaparak_Jalali::to_en( sanitize_text_field( wp_unslash( $_GET['event_month'] ) ) ), $m ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$year  = (int) $m[1];
			$mon   = max( 1, min( 12, (int) $m[2] ) );
		} else {
			$year = $now['jy'];
			$mon  = $now['jm'];
		}

		$first_ts = Shaparak_Jalali::timestamp( $year, $mon, 1, 0, 0 );
		$days     = Shaparak_Jalali::days_in_month( $year, $mon );
		$last_ts  = Shaparak_Jalali::timestamp( $year, $mon, $days, 23, 59 ) + 59;

		// Every saved event belongs in the calendar: no state filter, no date
		// filter — bucket by the event's own (fallback-aware) start/end span.
		$events = Shaparak_Query::get_events(
			array(
				'state'  => 'any',
				'number' => 500,
				'order'  => 'ASC',
				'orderby' => 'date',
			)
		);
		$by_day = array();
		foreach ( $events as $ev ) {
			$ev_start = Shaparak_Query::start_timestamp( $ev->ID );
			if ( ! $ev_start ) {
				continue;
			}
			$ev_end = Shaparak_Query::end_timestamp( $ev->ID );
			if ( ! $ev_end || $ev_end < $ev_start ) {
				$ev_end = $ev_start;
			}
			if ( $ev_end < $first_ts || $ev_start > $last_ts ) {
				continue;
			}

			$from     = max( $ev_start, $first_ts );
			$to       = min( $ev_end, $last_ts );
			$from_p   = Shaparak_Jalali::parts( $from );
			$to_p     = Shaparak_Jalali::parts( $to );
			$first_d  = (int) $from_p['jd'];
			$last_d   = (int) $to_p['jd'];

			for ( $d = $first_d; $d <= $last_d && $d <= $days; $d++ ) {
				if ( $d < 1 ) {
					continue;
				}
				$by_day[ $d ][] = $ev;
			}
		}

		$offset  = ( (int) gmdate( 'w', $first_ts + (int) round( (float) get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) + 1 ) % 7; // 0 = شنبه.
		$headers = array( 'شنبه', 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه' );

		$prev_mon = 1 === $mon ? 12 : $mon - 1;
		$prev_yr  = 1 === $mon ? $year - 1 : $year;
		$next_mon = 12 === $mon ? 1 : $mon + 1;
		$next_yr  = 12 === $mon ? $year + 1 : $year;
		$base     = remove_query_arg( array( 'event_month' ) );
		$prev_url = add_query_arg( 'event_month', sprintf( '%04d-%02d', $prev_yr, $prev_mon ), $base );
		$next_url = add_query_arg( 'event_month', sprintf( '%04d-%02d', $next_yr, $next_mon ), $base );

		ob_start();
		?>
		<div class="shaparak-calendar">
			<div class="shaparak-calendar__head">
				<a class="shaparak-calendar__nav" href="<?php echo esc_url( $prev_url ); ?>" rel="prev" aria-label="ماه قبل"></a>
				<div class="shaparak-calendar__title">
					<strong><?php echo esc_html( Shaparak_Jalali::MONTHS[ $mon ] . ' ' . Shaparak_Jalali::to_fa( $year ) ); ?></strong>
				</div>
				<a class="shaparak-calendar__nav shaparak-calendar__nav--next" href="<?php echo esc_url( $next_url ); ?>" rel="next" aria-label="ماه بعد"></a>
			</div>

			<div class="shaparak-calendar__grid">
				<?php foreach ( $headers as $h ) : ?>
					<div class="shaparak-calendar__weekday"><?php echo esc_html( $h ); ?></div>
				<?php endforeach; ?>

				<?php
				for ( $i = 0; $i < $offset; $i++ ) {
					echo '<div class="shaparak-calendar__cell shaparak-calendar__cell--empty"></div>';
				}
				for ( $d = 1; $d <= $days; $d++ ) {
					$is_today  = ( (int) $now['jy'] === $year && (int) $now['jm'] === $mon && (int) $now['jd'] === $d );
					$has       = ! empty( $by_day[ $d ] );
					$weekend   = ( ( $offset + $d - 1 ) % 7 ) === 6; // جمعه.
					printf(
						'<div class="shaparak-calendar__cell %s %s %s">',
						$has ? 'has-event' : '',
						$is_today ? 'is-today' : '',
						$weekend ? 'is-weekend' : ''
					);
					printf( '<span class="shaparak-calendar__day">%s</span>', esc_html( Shaparak_Jalali::to_fa( $d ) ) );
					if ( $has ) {
						echo '<span class="shaparak-calendar__dots">';
						foreach ( array_slice( $by_day[ $d ], 0, 3 ) as $ev ) {
							printf( '<a href="%s" title="%s"></a>', esc_url( get_permalink( $ev ) ), esc_attr( get_the_title( $ev ) ) );
						}
						echo '</span>';
					}
					echo '</div>';
				}
				?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
