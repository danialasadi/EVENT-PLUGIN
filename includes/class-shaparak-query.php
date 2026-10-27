<?php
/**
 * Event queries and state helpers.
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Shaparak_Query
 */
class Shaparak_Query {

	/**
	 * Hooks.
	 */
	public static function init() {
		// Reserved for future hooks (e.g. REST fields).
	}

	/**
	 * Start timestamp of an event.
	 *
	 * @param int $post_id Post ID.
	 * @return int 0 when not set.
	 */
	public static function start_timestamp( $post_id ) {
		$raw = get_post_meta( $post_id, Shaparak_Meta::$fields['start'], true );
		if ( '' !== $raw && null !== $raw && false !== $raw ) {
			return (int) $raw;
		}
		// Fallback: the Shamsi date fields, or the publish date.
		$date = get_post_meta( $post_id, Shaparak_Meta::$fields['start_jalali'], true );
		$time = get_post_meta( $post_id, Shaparak_Meta::$fields['start_time'], true );
		if ( $date ) {
			list( $y, $m, $d ) = array_map( 'intval', explode( '/', $date ) );
			list( $hh, $mm )   = array_map( 'intval', explode( ':', $time ? $time : '00:00' ) );
			return Shaparak_Jalali::timestamp( $y, $m, $d, $hh, $mm );
		}
		$post = get_post( $post_id );
		if ( ! $post ) {
			return 0;
		}
		// post_date is site-local wall-clock time; strtotime() reads it as UTC.
		$offset = (int) round( (float) get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
		return (int) strtotime( $post->post_date ) - $offset;
	}

	/**
	 * End timestamp of an event (defaults to the end of the start day).
	 *
	 * @param int $post_id Post ID.
	 * @return int
	 */
	public static function end_timestamp( $post_id ) {
		$raw = get_post_meta( $post_id, Shaparak_Meta::$fields['end'], true );
		if ( '' !== $raw && null !== $raw && false !== $raw ) {
			return (int) $raw;
		}
		$start = self::start_timestamp( $post_id );
		return $start ? $start + ( DAY_IN_SECONDS - 1 ) : 0;
	}

	/**
	 * Event state: current | upcoming | past.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	public static function state( $post_id ) {
		$now   = time();
		$start = self::start_timestamp( $post_id );
		$end   = self::end_timestamp( $post_id );

		if ( ! $start ) {
			return 'past';
		}
		if ( $now < $start ) {
			return 'upcoming';
		}
		if ( $now > $end ) {
			return 'past';
		}
		return 'current';
	}

	/**
	 * Meta query part for a state.
	 *
	 * @param string $state current|past|upcoming|any.
	 * @return array|null
	 */
	private static function state_query( $state ) {
		$now = time();
		$s   = Shaparak_Meta::$fields['start'];
		$e   = Shaparak_Meta::$fields['end'];
		$day = DAY_IN_SECONDS - 1;

		switch ( $state ) {
			case 'current':
				return array(
					'relation' => 'AND',
					array(
						'key'     => $s,
						'value'   => $now,
						'compare' => '<=',
						'type'    => 'NUMERIC',
					),
					array(
						'relation' => 'OR',
						array(
							'key'     => $e,
							'value'   => $now,
							'compare' => '>=',
							'type'    => 'NUMERIC',
					),
						array(
							'key'     => $e,
							'value'   => 0,
							'compare' => 'NOT EXISTS',
						),
						array(
							'key'     => $s,
							'value'   => $now + $day - ( $now % DAY_IN_SECONDS ),
							'compare' => '>=',
							'type'    => 'NUMERIC',
						),
					),
				);

			case 'upcoming':
				return array(
					'key'     => $s,
					'value'   => $now,
					'compare' => '>',
					'type'    => 'NUMERIC',
				);

			case 'past':
				return array(
					'relation' => 'OR',
					array(
						'relation' => 'AND',
						array(
							'key'     => $e,
							'value'   => $now,
							'compare' => '<',
							'type'    => 'NUMERIC',
						),
					),
					array(
						'relation' => 'AND',
						array(
							'key'     => $e,
							'value'   => 0,
							'compare' => 'NOT EXISTS',
						),
						array(
							'key'     => $s,
							'value'   => $now - $day,
							'compare' => '<',
							'type'    => 'NUMERIC',
						),
					),
				);
		}

		return null;
	}

	/**
	 * Run an event query.
	 *
	 * @param array $args {
	 *     @type string $state     current|past|upcoming|any. Default any.
	 *     @type int    $number    Posts per page. Default 12.
	 *     @type string $order     ASC|DESC. Default DESC.
	 *     @type string $orderby   Field. Default meta_value_num on start.
	 *     @type string $category  Category slug.
	 *     @type bool   $featured  Only featured events.
	 *     @type int    $week_start Start of the current Shamsi week (timestamp) to filter from.
	 *     @type int    $week_end   End of the current Shamsi week (timestamp).
	 *     @type bool   $search    Use the main query search term.
	 * }
	 * @return WP_Post[]
	 */
	public static function get_events( $args = array() ) {
		$defaults = array(
			'state'      => 'any',
			'number'     => 12,
			'order'      => 'DESC',
			'orderby'    => 'meta_value_num',
			'category'   => '',
			'featured'   => false,
			'week_start' => 0,
			'week_end'   => 0,
			'search'     => '',
		);
		$args     = wp_parse_args( $args, $defaults );

		$meta_query = array();
		$state_q    = self::state_query( $args['state'] );
		if ( $state_q ) {
			$meta_query[] = $state_q;
		}
		if ( $args['featured'] ) {
			$meta_query[] = array(
				'key'   => Shaparak_Meta::$fields['featured'],
				'value' => '1',
			);
		}
		if ( $args['week_start'] && $args['week_end'] ) {
			$meta_query[] = array(
				'relation' => 'AND',
				array(
					'key'     => Shaparak_Meta::$fields['start'],
					'value'   => $args['week_start'],
					'compare' => '>=',
					'type'    => 'NUMERIC',
				),
				array(
					'key'     => Shaparak_Meta::$fields['start'],
					'value'   => $args['week_end'],
					'compare' => '<=',
					'type'    => 'NUMERIC',
				),
			);
		}

		$query_args = array(
			'post_type'      => Shaparak_Post_Types::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => (int) $args['number'],
			'orderby'        => $args['orderby'],
			'order'          => $args['order'],
		);

		if ( ! empty( $meta_query ) ) {
			$query_args['meta_query'] = array_merge( array( 'relation' => 'AND' ), $meta_query ); // phpcs:ignore WordPress.DB.SlowDBQuery
			$query_args['orderby']    = array( 'meta_value_num' => $args['order'], 'date' => $args['order'] );
			$query_args['meta_key']   = Shaparak_Meta::$fields['start']; // phpcs:ignore WordPress.DB.SlowDBQuery
		}

		if ( ! empty( $args['category'] ) ) {
			$query_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
				array(
					'taxonomy' => Shaparak_Post_Types::CATEGORY,
					'field'    => 'slug',
					'terms'    => $args['category'],
				),
			);
		}

		if ( ! empty( $args['search'] ) ) {
			$query_args['s'] = $args['search'];
		}

		$query = new WP_Query( $query_args );
		return $query->posts;
	}

	/**
	 * The featured (hero) event: a current one first, then upcoming, then the newest.
	 *
	 * @return WP_Post|null
	 */
	public static function get_featured_event() {
		foreach ( array( 'current', 'upcoming', 'past' ) as $state ) {
			$posts = self::get_events(
				array(
					'state'   => $state,
					'number'  => 20,
					'featured'=> true,
				)
			);
			if ( $posts ) {
				return $posts[0];
			}
		}
		return null;
	}

	/**
	 * Events happening in the current Shamsi week (Saturday → Friday).
	 *	 * @param int    $number Limit.
	 * @param string $category Optional category slug to limit the strip to.
	 * @return WP_Post[]
	 */
	public static function get_week_events( $number = 6, $category = '' ) {
		$now  = Shaparak_Jalali::parts();
		$today_start = Shaparak_Jalali::timestamp( $now['jy'], $now['jm'], $now['jd'], 0, 0 );
		$weekday     = ( (int) gmdate( 'w', $today_start + (int) round( (float) get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) + 1 ) % 7; // 0 = شنبه.
		$week_start  = $today_start - ( $weekday * DAY_IN_SECONDS );
		$week_end    = $week_start + ( 7 * DAY_IN_SECONDS ) - 1;

		return self::get_events(
			array(
				'state'      => 'any',
				'number'     => $number,
				'category'   => $category,
				'week_start' => $week_start,
				'week_end'   => $week_end,
				'order'      => 'ASC',
			)
		);
	}

	/**
	 * Next event after now (used by "رویداد فعلی" when nothing is running).
	 *
	 * @return WP_Post|null
	 */
	public static function get_current_event() {
		$posts = self::get_events(
			array(
				'state'  => 'current',
				'number' => 1,
			)
		);
		return $posts ? $posts[0] : null;
	}

	/**
	 * Short Shamsi date range label, e.g. "۲۲ تا ۲۴ آذر ۱۴۰۳".
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	public static function date_label( $post_id ) {
		$start = self::start_timestamp( $post_id );
		$end   = self::end_timestamp( $post_id );
		if ( ! $start ) {
			return '';
		}
		$p1 = Shaparak_Jalali::parts( $start );
		$p2 = Shaparak_Jalali::parts( $end );

		if ( $end <= $start || ( $p1['jy'] === $p2['jy'] && $p1['jm'] === $p2['jm'] && $p1['jd'] === $p2['jd'] ) ) {
			return Shaparak_Jalali::date( $start );
		}
		if ( $p1['jy'] === $p2['jy'] && $p1['jm'] === $p2['jm'] ) {
			return Shaparak_Jalali::to_fa( $p1['jd'] ) . ' تا ' . Shaparak_Jalali::format( $end, 'j F Y' );
		}
		return Shaparak_Jalali::date( $start ) . ' تا ' . Shaparak_Jalali::date( $end );
	}

	/**
	 * Time range label, e.g. "۱۸:۳۰ تا ۲۰:۰۰".
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	public static function time_label( $post_id ) {
		$start = self::start_timestamp( $post_id );
		$end   = get_post_meta( $post_id, Shaparak_Meta::$fields['end_time'], true );
		if ( ! $start ) {
			return '';
		}
		$label = Shaparak_Jalali::time( $start );
		if ( $end ) {
			$label .= ' تا ' . Shaparak_Jalali::to_fa( $end );
		}
		return $label;
	}

	/**
	 * Day-of-month + month badge used by the design's compact cards.
	 *
	 * @param int $post_id Post ID.
	 * @return array{day:string,month:string}
	 */
	public static function date_badge( $post_id ) {
		$p    = Shaparak_Jalali::parts( self::start_timestamp( $post_id ) );
		return array(
			'day'   => Shaparak_Jalali::to_fa( $p['jd'] ),
			'month' => Shaparak_Jalali::MONTHS[ $p['jm'] ],
		);
	}

	/**
	 * Venue label ("مکان، شهر").
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	public static function venue_label( $post_id ) {
		$venue = get_post_meta( $post_id, Shaparak_Meta::$fields['venue'], true );
		$city  = get_post_meta( $post_id, Shaparak_Meta::$fields['city'], true );
		return trim( $venue . ( $city ? ( $venue ? '، ' : '' ) . $city : '' ) );
	}
}
