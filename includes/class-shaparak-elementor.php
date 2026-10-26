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
		add_action( 'elementor/categories/register', array( __CLASS__, 'register_category' ) );
		add_action( 'elementor/widgets/register', array( __CLASS__, 'register_widgets' ) );
		add_filter( 'elementor/editor/allowed_attributes', array( __CLASS__, 'allow_attributes' ) );
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
