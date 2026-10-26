<?php
/**
 * Shortcodes — usable on any page, with or without Elementor.
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Shaparak_Shortcodes
 */
class Shaparak_Shortcodes {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_shortcode( 'shaparak_events', array( __CLASS__, 'list' ) );
		add_shortcode( 'shaparak_event_featured', array( __CLASS__, 'featured' ) );
		add_shortcode( 'shaparak_events_week', array( __CLASS__, 'week' ) );
		add_shortcode( 'shaparak_event_card', array( __CLASS__, 'card' ) );
		add_shortcode( 'shaparak_event_countdown', array( __CLASS__, 'countdown' ) );
		add_shortcode( 'shaparak_events_calendar', array( __CLASS__, 'calendar' ) );
		add_shortcode( 'shaparak_component', array( __CLASS__, 'component' ) );
	}

	/**
	 * [shaparak_events state="current|past|upcoming|any" number="6" title="…"]
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public static function list( $atts ) {
		$atts = shortcode_atts(
			array(
				'state'  => 'past',
				'number' => 6,
				'title'  => '',
				'layout' => 'grid',
			),
			$atts,
			'shaparak_events'
		);

		$posts = Shaparak_Query::get_events(
			array(
				'state'  => sanitize_key( $atts['state'] ),
				'number' => (int) $atts['number'],
			)
		);

		ob_start();
		?>
		<section class="shaparak-events shaparak-events--list">
			<?php if ( $atts['title'] ) : ?>
				<h2 class="shaparak-section__title"><?php echo esc_html( $atts['title'] ); ?></h2>
			<?php endif; ?>
			<?php if ( ! $posts ) : ?>
				<p class="shaparak-empty"><?php echo esc_html__( 'رویدادی پیدا نشد.', 'shaparak-events' ); ?></p>
			<?php else : ?>
				<div class="shaparak-grid shaparak-grid--<?php echo esc_attr( $atts['layout'] ); ?>">
					<?php
					foreach ( $posts as $post ) {
						Shaparak_Template::part(
							'card-event',
							array(
								'post'   => $post,
								'layout' => $atts['layout'],
							)
						);
					}
					?>
				</div>
			<?php endif; ?>
		</section>
		<?php
		return ob_get_clean();
	}

	/**
	 * [shaparak_event_featured]
	 *
	 * @return string
	 */
	public static function featured() {
		$post = Shaparak_Query::get_featured_event();
		if ( ! $post ) {
			return '';
		}
		ob_start();
		Shaparak_Template::part(
			'card-featured',
			array(
				'post' => $post,
			)
		);
		return ob_get_clean();
	}

	/**
	 * [shaparak_events_week number="6"]
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public static function week( $atts ) {
		$atts  = shortcode_atts( array( 'number' => 6 ), $atts, 'shaparak_events_week' );
		$posts = Shaparak_Query::get_week_events( (int) $atts['number'] );
		if ( ! $posts ) {
			return '';
		}
		ob_start();
		?>
		<section class="shaparak-events shaparak-events--week">
			<h2 class="shaparak-section__title">
				<span class="dot"></span><?php echo esc_html__( 'این هفته · نشست‌ها', 'shaparak-events' ); ?>
			</h2>
			<div class="shaparak-week">
				<?php
				foreach ( $posts as $post ) {
					Shaparak_Template::part( 'card-compact', array( 'post' => $post ) );
				}
				?>
			</div>
		</section>
		<?php
		return ob_get_clean();
	}

	/**
	 * [shaparak_event_card id="123"]
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public static function card( $atts ) {
		$atts = shortcode_atts( array( 'id' => 0 ), $atts, 'shaparak_event_card' );
		$post = get_post( (int) $atts['id'] );
		if ( ! $post || Shaparak_Post_Types::POST_TYPE !== $post->post_type ) {
			return '';
		}
		ob_start();
		Shaparak_Template::part(
			'card-event',
			array(
				'post'   => $post,
				'layout' => 'grid',
			)
		);
		return ob_get_clean();
	}

	/**
	 * [shaparak_event_countdown id="123"] — Shamsi remaining time.
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public static function countdown( $atts ) {
		$atts = shortcode_atts(
			array(
				'id'    => 0,
				'label' => '',
			),
			$atts,
			'shaparak_event_countdown'
		);
		$post = get_post( (int) $atts['id'] );
		if ( ! $post ) {
			$post = get_the_ID() ? get_post( get_the_ID() ) : null;
		}
		if ( ! $post ) {
			return '';
		}
		$html = Shaparak_Template::countdown_html( $post->ID, $atts['label'] );
		return $html;
	}

	/**
	 * [shaparak_events_calendar month="1403-07"]
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public static function calendar( $atts ) {
		$atts = shortcode_atts( array( 'month' => '' ), $atts, 'shaparak_events_calendar' );
		return Shaparak_Template::calendar_html( $atts['month'] );
	}

	/**
	 * [shaparak_component name="hero|featured|card|compact" id="123"]
	 *
	 * Renders one of the shared templates outside Elementor. The remaining
	 * components (timeline, prizes, people, winners, FAQ, gallery, venue, report,
	 * axes) ship as Elementor widgets in the "شاپرک · رویدادها" panel.
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public static function component( $atts ) {
		$atts = shortcode_atts(
			array(
				'name'   => '',
				'id'     => 0,
				'layout' => 'grid',
			),
			$atts,
			'shaparak_component'
		);

		$map = array(
			'hero'     => 'hero',
			'featured' => 'card-featured',
			'card'     => 'card-event',
			'compact'  => 'card-compact',
		);

		if ( ! isset( $map[ $atts['name'] ] ) ) {
			return '';
		}

		$post = Shaparak_Event_Data::resolve( (int) $atts['id'] );
		if ( ! $post ) {
			return '';
		}

		ob_start();
		echo '<div class="shaparak-events shaparak-events--component">';
		Shaparak_Template::part(
			$map[ $atts['name'] ],
			array(
				'post'   => $post,
				'layout' => sanitize_key( $atts['layout'] ),
			)
		);
		echo '</div>';
		return ob_get_clean();
	}
}
