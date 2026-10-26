<?php
/**
 * Plugin Name:       شاپرک رویداد — Shaparak Events
 * Plugin URI:        https://shaparak.example.com/
 * Description:       افزونه مدیریت رویدادها با تقویم شمسی؛ نمایش رویداد فعلی و رویدادهای گذشته به همراه ویجت‌های اختصاصی المنتور.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Shaparak
 * License:           GPL-2.0-or-later
 * Text Domain:       shaparak-events
 * Domain Path:       /languages
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SHAPARAK_EVENTS_VERSION', '1.0.0' );
define( 'SHAPARAK_EVENTS_FILE', __FILE__ );
define( 'SHAPARAK_EVENTS_PATH', plugin_dir_path( __FILE__ ) );
define( 'SHAPARAK_EVENTS_URL', plugin_dir_url( __FILE__ ) );

require_once SHAPARAK_EVENTS_PATH . 'includes/class-shaparak-jalali.php';
require_once SHAPARAK_EVENTS_PATH . 'includes/class-shaparak-event-data.php';
require_once SHAPARAK_EVENTS_PATH . 'includes/class-shaparak-post-types.php';
require_once SHAPARAK_EVENTS_PATH . 'includes/class-shaparak-meta.php';
require_once SHAPARAK_EVENTS_PATH . 'includes/class-shaparak-query.php';
require_once SHAPARAK_EVENTS_PATH . 'includes/class-shaparak-template.php';
require_once SHAPARAK_EVENTS_PATH . 'includes/class-shaparak-shortcodes.php';
require_once SHAPARAK_EVENTS_PATH . 'includes/class-shaparak-assets.php';
require_once SHAPARAK_EVENTS_PATH . 'includes/class-shaparak-elementor.php';

/**
 * Main plugin container.
 */
final class Shaparak_Events {

	/** @var Shaparak_Events|null */
	private static $instance = null;

	/**
	 * Singleton accessor.
	 *
	 * @return Shaparak_Events
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		Shaparak_Post_Types::init();
		Shaparak_Meta::init();
		Shaparak_Query::init();
		Shaparak_Template::init();
		Shaparak_Shortcodes::init();
		Shaparak_Assets::init();
		Shaparak_Elementor::init();

		add_action( 'init', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Load translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'shaparak-events', false, dirname( plugin_basename( SHAPARAK_EVENTS_FILE ) ) . '/languages' );
	}
}

Shaparak_Events::instance();
