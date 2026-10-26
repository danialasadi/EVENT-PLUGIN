<?php
/**
 * Front-end asset loading.
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Shaparak_Assets
 */
class Shaparak_Assets {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ), 15 );
	}

	/**
	 * Enqueue the plugin stylesheet and script.
	 *
	 * Loaded on every front-end view: the widgets and shortcodes can appear on any
	 * page (including Elementor canvases), and the stylesheet is scoped to
	 * `.shaparak-events` / `.shaparak-single-event` so it never leaks.
	 */
	public static function enqueue() {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return;
		}

		wp_enqueue_style(
			'shaparak-events',
			SHAPARAK_EVENTS_URL . 'assets/css/events.css',
			array(),
			SHAPARAK_EVENTS_VERSION
		);

		wp_enqueue_script(
			'shaparak-events',
			SHAPARAK_EVENTS_URL . 'assets/js/events.js',
			array(),
			SHAPARAK_EVENTS_VERSION,
			true
		);

		self::fonts();
	}

	/**
	 * Register the Persian webfont used by the design (Vazirmatn).
	 *
	 * @return void
	 */
	private static function fonts() {
		$url = apply_filters( 'shaparak_events_fonts_url', 'https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap' );
		if ( ! $url ) {
			return;
		}
		wp_register_style( 'shaparak-events-font', $url, array(), null );
		wp_enqueue_style( 'shaparak-events-font' );
	}
}
