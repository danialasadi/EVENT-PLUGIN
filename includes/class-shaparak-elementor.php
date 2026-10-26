<?php
/**
 * Elementor integration: widget category + widget registration.
 *
 * Works with Elementor (free) and Elementor Pro. Because the event post type is
 * public and shows in REST, Elementor Pro's Theme Builder automatically exposes
 * "تکی رویداد" / "بایگانی رویداد" so full templates can be built visually.
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Shaparak_Elementor
 */
class Shaparak_Elementor {

	const CATEGORY = 'shaparak-events';

	/**
	 * Hooks.
	 */
	public static function init() {
		// Temporary diagnostics: define in wp-config.php to bisect an editor panel that hangs.
		if ( defined( 'SHAPARAK_DISABLE_ELEMENTOR' ) && SHAPARAK_DISABLE_ELEMENTOR ) {
			return;
		}

		if ( ! ( defined( 'SHAPARAK_DISABLE_CATEGORY' ) && SHAPARAK_DISABLE_CATEGORY ) ) {
			add_action( 'elementor/categories/register', array( __CLASS__, 'register_category' ) );
		}

		add_filter( 'elementor/editor/allowed_attributes', array( __CLASS__, 'allow_attributes' ) );

		if ( ! ( defined( 'SHAPARAK_DISABLE_ACE' ) && SHAPARAK_DISABLE_ACE ) ) {
			add_filter( 'script_loader_src', array( __CLASS__, 'local_ace_src' ), 20, 2 );
		}

		if ( defined( 'SHAPARAK_DISABLE_WIDGETS' ) && SHAPARAK_DISABLE_WIDGETS ) {
			return;
		}

		add_action( 'elementor/widgets/register', array( __CLASS__, 'register_widgets' ) );
	}

	/**
	 * Serve the Ace code editor from this plugin instead of cdn.jsdelivr.net.
	 *
	 * The Elementor editor registers ace.min.js / ext-language_tools.js from
	 * jsDelivr, which is unreachable from Iran. Two blocking requests then fail
	 * with ERR_CONNECTION_RESET and the editor stalls on the loading screen.
	 * Any ace-builds file shipped in assets/ace/ is rewritten to a local URL;
	 * everything else (versions, modes we do not ship) keeps its original URL.
	 *
	 * @param string $src   Script URL.
	 * @param string $handle Script handle.
	 * @return string
	 */
	public static function local_ace_src( $src, $handle = '' ) {
		if ( ! is_string( $src ) || false === strpos( $src, 'ace-builds' ) ) {
			return $src;
		}

		$marker = 'src-min-noconflict/';
		$pos    = strpos( $src, $marker );
		if ( false === $pos ) {
			return $src;
		}

		$file = preg_replace( '/\?.*$/', '', substr( $src, $pos + strlen( $marker ) ) );
		$path = SHAPARAK_EVENTS_PATH . 'assets/ace/' . $file;
		if ( ! $file || ! file_exists( $path ) ) {
			return $src;
		}

		return SHAPARAK_EVENTS_URL . 'assets/ace/' . $file . '?ver=1.43.2';
	}

	/**
	 * Is Elementor active?
	 *
	 * @return bool
	 */
	public static function active() {
		return did_action( 'elementor/loaded' );
	}

	/**
	 * Register our widget category.
	 *
	 * @param \Elementor\Widget_Manager $widgets Widget manager.
	 */
	public static function register_category( $widgets ) {
		$widgets->add_category(
			self::CATEGORY,
			array(
				'title' => 'شاپرک · رویدادها',
				'icon'  => 'eicon-calendar',
			)
		);
	}

	/**
	 * Register widgets.
	 *
	 * @param \Elementor\Widget_Manager $widgets Widget manager.
	 */
	public static function register_widgets( $widgets ) {
		$files = array(
			'widget-events-list.php'       => 'Shaparak_Widget_Events_List',
			'widget-event-featured.php'    => 'Shaparak_Widget_Event_Featured',
			'widget-events-week.php'       => 'Shaparak_Widget_Events_Week',
			'widget-event-countdown.php'   => 'Shaparak_Widget_Event_Countdown',
			'widget-event-meta.php'        => 'Shaparak_Widget_Event_Meta',
			'widget-events-calendar.php'   => 'Shaparak_Widget_Events_Calendar',
			'widget-events-toolbar.php'    => 'Shaparak_Widget_Events_Toolbar',
			'widget-events-notify.php'     => 'Shaparak_Widget_Events_Notify',
			'widget-event-hero.php'        => 'Shaparak_Widget_Event_Hero',
			'widget-event-timeline.php'    => 'Shaparak_Widget_Event_Timeline',
			'widget-event-prizes.php'      => 'Shaparak_Widget_Event_Prizes',
			'widget-event-people.php'      => 'Shaparak_Widget_Event_People',
			'widget-event-winners.php'     => 'Shaparak_Widget_Event_Winners',
			'widget-event-faq.php'         => 'Shaparak_Widget_Event_Faq',
			'widget-event-gallery.php'     => 'Shaparak_Widget_Event_Gallery',
			'widget-event-venue.php'       => 'Shaparak_Widget_Event_Venue',
			'widget-event-report.php'      => 'Shaparak_Widget_Event_Report',
			'widget-event-axes.php'        => 'Shaparak_Widget_Event_Axes',
		);

		foreach ( $files as $file => $class ) {
			$path = SHAPARAK_EVENTS_PATH . 'widgets/' . $file;
			if ( ! file_exists( $path ) ) {
				continue;
			}
			require_once $path;
			if ( class_exists( $class ) ) {
				$widgets->register( new $class() );
			}
		}
	}

	/**
	 * Nothing extra needed today; kept as an extension point.
	 *
	 * @param array $attributes Attributes.
	 * @return array
	 */
	public static function allow_attributes( $attributes ) {
		return $attributes;
	}
}
