<?php
/**
 * Read-side accessors for everything an event carries.
 *
 * All repeatable data is stored as JSON in a single post meta key so the
 * admin UI, the Elementor widgets and the shortcodes share one source of truth.
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Shaparak_Event_Data
 */
class Shaparak_Event_Data {

	/**
	 * Meta keys used by the plugin.
	 *
	 * @return array<string,string>
	 */
	public static function keys() {
		return array(
			'venue'      => '_event_venue',
			'city'       => '_event_city',
			'address'    => '_event_address',
			'map'        => '_event_map',
			'directions' => '_event_directions',
			'link'       => '_event_link',
			'featured'   => '_event_featured',
			'axes'       => '_event_axes',
			'agenda'     => '_event_agenda',
			'prizes'     => '_event_prizes',
			'people'     => '_event_people',
			'faq'        => '_event_faq',
			'winners'    => '_event_winners',
			'report'     => '_event_report',
			'highlights' => '_event_highlights',
			'report_pdf' => '_event_report_pdf',
			'gallery'    => '_event_gallery',
			'stats'      => '_event_stats',
		);
	}

	/**
	 * Raw meta value.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Key from keys().
	 * @return string
	 */
	private static function raw( $post_id, $key ) {
		$keys = self::keys();
		if ( ! isset( $keys[ $key ] ) ) {
			return '';
		}
		$value = get_post_meta( $post_id, $keys[ $key ], true );
		return is_string( $value ) ? $value : '';
	}

	/**
	 * Plain text meta.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Key.
	 * @return string
	 */
	public static function text( $post_id, $key ) {
		return trim( self::raw( $post_id, $key ) );
	}

	/**
	 * Attributes that let the filter panel hide/show this event's card.
	 *
	 * @param int $post_id Post ID.
	 * @return string Raw attribute string (already escaped).
	 */
	public static function filter_attributes( $post_id ) {
		$slugs = array();
		$terms = get_the_terms( $post_id, Shaparak_Post_Types::CATEGORY );
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$slugs[] = $term->slug;
			}
		}

		$province = '';
		$regions  = get_the_terms( $post_id, Shaparak_Post_Types::PROVINCE );
		if ( $regions && ! is_wp_error( $regions ) && isset( $regions[0] ) ) {
			$province = $regions[0]->slug;
		}

		return sprintf(
			' data-shaparak-card data-categories="%s" data-province="%s"',
			esc_attr( implode( ' ', $slugs ) ),
			esc_attr( $province )
		);
	}

	/**
	 * List meta stored one-per-line (axes, highlights).
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Key.
	 * @return string[]
	 */
	public static function lines( $post_id, $key ) {
		$raw = self::raw( $post_id, $key );
		if ( '' === $raw ) {
			return array();
		}
		$parts = preg_split( '/\r\n|\r|\n/', $raw );
		$parts = array_map( 'trim', (array) $parts );
		return array_values( array_filter( $parts, 'strlen' ) );
	}

	/**
	 * Repeatable rows stored as JSON.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Key.
	 * @return array<int,array<string,string>>
	 */
	public static function rows( $post_id, $key ) {
		$raw = self::raw( $post_id, $key );
		if ( '' === $raw ) {
			return array();
		}
		$decoded = json_decode( $raw, true );
		return is_array( $decoded ) ? array_values( $decoded ) : array();
	}

	/**
	 * Gallery attachment IDs.
	 *
	 * @param int $post_id Post ID.
	 * @return int[]
	 */
	public static function gallery( $post_id ) {
		$raw = self::raw( $post_id, 'gallery' );
		if ( '' === $raw ) {
			return array();
		}
		return array_values( array_filter( array_map( 'intval', explode( ',', $raw ) ) ) );
	}

	/**
	 * Event axes as repeater rows, seeding from the legacy one-per-line format.
	 *
	 * @param int $post_id Post ID.
	 * @return array<int,array<string,string>>
	 */
	public static function axes_rows( $post_id ) {
		$rows = self::rows( $post_id, 'axes' );
		if ( $rows ) {
			return $rows;
		}
		$out = array();
		foreach ( self::lines( $post_id, 'axes' ) as $line ) {
			$out[] = array( 'title' => $line );
		}
		return $out;
	}

	/**
	 * Event axes (chips).
	 *
	 * @param int $post_id Post ID.
	 * @return string[]
	 */
	public static function axes( $post_id ) {
		$out = array();
		foreach ( self::axes_rows( $post_id ) as $row ) {
			$title = isset( $row['title'] ) ? trim( (string) $row['title'] ) : '';
			if ( '' !== $title ) {
				$out[] = $title;
			}
		}
		return $out;
	}

	/**
	 * Agenda grouped by day: [ ['day' => 'روز اول', 'items' => [ ['time','title','note'] ] ], … ].
	 *
	 * @param int $post_id Post ID.
	 * @return array<int,array{day:string,items:array<int,array<string,string>>}>
	 */
	public static function agenda( $post_id ) {
		$rows = self::rows( $post_id, 'agenda' );
		$out  = array();
		$day  = '';

		// Rows are stored flat: a row without a "time" starts a new day.
		foreach ( $rows as $row ) {
			$time = isset( $row['time'] ) ? trim( (string) $row['time'] ) : '';
			$label = isset( $row['day'] ) ? trim( (string) $row['day'] ) : '';
			if ( '' !== $label && '' === $time ) {
				$day = $label;
				$out[] = array(
					'day'   => $day,
					'items' => array(),
				);
				continue;
			}
			if ( empty( $out ) ) {
				$day  = __( 'برنامه', 'shaparak-events' );
				$out[] = array(
					'day'   => $day,
					'items' => array(),
				);
			}
			$out[ count( $out ) - 1 ]['items'][] = array(
				'time'  => $time,
				'title' => isset( $row['title'] ) ? (string) $row['title'] : '',
				'note'  => isset( $row['note'] ) ? (string) $row['note'] : '',
			);
		}

		return $out;
	}

	/**
	 * Prizes.
	 *
	 * @param int $post_id Post ID.
	 * @return array<int,array{amount:string,desc:string}>
	 */
	public static function prizes( $post_id ) {
		$out = array();
		foreach ( self::rows( $post_id, 'prizes' ) as $row ) {
			$out[] = array(
				'amount' => isset( $row['amount'] ) ? (string) $row['amount'] : '',
				'desc'   => isset( $row['desc'] ) ? (string) $row['desc'] : '',
			);
		}
		return $out;
	}

	/**
	 * Judges / mentors.
	 *
	 * @param int $post_id Post ID.
	 * @return array<int,array{name:string,role:string,avatar:int}>
	 */
	public static function people( $post_id ) {
		$out = array();
		foreach ( self::rows( $post_id, 'people' ) as $row ) {
			$out[] = array(
				'name'   => isset( $row['name'] ) ? (string) $row['name'] : '',
				'role'   => isset( $row['role'] ) ? (string) $row['role'] : '',
				'avatar' => isset( $row['avatar'] ) ? (int) $row['avatar'] : 0,
			);
		}
		return $out;
	}

	/**
	 * FAQ.
	 *
	 * @param int $post_id Post ID.
	 * @return array<int,array{q:string,a:string}>
	 */
	public static function faq( $post_id ) {
		$out = array();
		foreach ( self::rows( $post_id, 'faq' ) as $row ) {
			$out[] = array(
				'q' => isset( $row['q'] ) ? (string) $row['q'] : '',
				'a' => isset( $row['a'] ) ? (string) $row['a'] : '',
			);
		}
		return $out;
	}

	/**
	 * Winners.
	 *
	 * @param int $post_id Post ID.
	 * @return array<int,array{title:string,desc:string,image:int}>
	 */
	public static function winners( $post_id ) {
		$out = array();
		foreach ( self::rows( $post_id, 'winners' ) as $row ) {
			$out[] = array(
				'title' => isset( $row['title'] ) ? (string) $row['title'] : '',
				'desc'  => isset( $row['desc'] ) ? (string) $row['desc'] : '',
				'image' => isset( $row['image'] ) ? (int) $row['image'] : 0,
			);
		}
		return $out;
	}

	/**
	 * Stats (value + label pairs).
	 *
	 * @param int $post_id Post ID.
	 * @return array<int,array{value:string,label:string}>
	 */
	public static function stats( $post_id ) {
		$out = array();
		foreach ( self::rows( $post_id, 'stats' ) as $row ) {
			$out[] = array(
				'value' => isset( $row['value'] ) ? (string) $row['value'] : '',
				'label' => isset( $row['label'] ) ? (string) $row['label'] : '',
			);
		}
		return $out;
	}

	/**
	 * Is this event the featured/hero one?
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public static function is_featured( $post_id ) {
		return '1' === get_post_meta( $post_id, '_event_featured', true );
	}

	/**
	 * Resolve the event for a widget: current post, explicit ID, or the featured one.
	 *
	 * @param int $explicit Optional post ID.
	 * @return WP_Post|null
	 */
	public static function resolve( $explicit = 0 ) {
		if ( $explicit ) {
			$post = get_post( $explicit );
			if ( $post && Shaparak_Post_Types::POST_TYPE === $post->post_type ) {
				return $post;
			}
			return null;
		}

		$current = get_post( get_the_ID() );
		if ( $current && Shaparak_Post_Types::POST_TYPE === $current->post_type ) {
			return $current;
		}
		return Shaparak_Query::get_featured_event();
	}

	/**
	 * The single-event hero banner image.
	 *
	 * @param int $post_id Post ID.
	 * @return string URL or empty string.
	 */
	public static function cover_url( $post_id ) {
		$id = get_post_thumbnail_id( $post_id );
		return $id ? (string) wp_get_attachment_image_url( $id, 'full' ) : '';
	}

	/**
	 * Status badge label + tone for a given state.
	 *
	 * @param string $state current|upcoming|past.
	 * @return array{label:string,tone:string}
	 */
	public static function badge( $state ) {
		$map = array(
			'current'  => array(
				'label' => 'رویداد جاری',
				'tone'  => 'navy',
			),
			'upcoming' => array(
				'label' => 'رویداد پیش رو',
				'tone'  => 'green',
			),
			'past'     => array(
				'label' => 'برگزار شد',
				'tone'  => 'navy',
			),
		);
		return isset( $map[ $state ] ) ? $map[ $state ] : $map['past'];
	}

	/**
	 * Number of agenda days.
	 *
	 * @param int $post_id Post ID.
	 * @return int
	 */
	public static function agenda_days( $post_id ) {
		return count( self::agenda( $post_id ) );
	}
}
