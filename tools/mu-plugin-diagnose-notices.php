<?php
/**
 * Plugin Name: Shaparak — Notice Diagnoser (mu-plugin)
 * Description: موقتاً مسیر فراخوانی‌کننده‌ی نوتیس‌های add_menu_page و map_meta_cap را در لاگ می‌نویسد.
 * Version:     1.0.0
 *
 * طریقه استفاده:
 *   ۱) این فایل را در wp-content/mu-plugins/ کپی کنید (اگر پوشه نیست، بسازید).
 *   ۲) پیشخوان یا صفحه‌ای از سایت را یک‌بار باز کنید.
 *   ۳) فایل wp-content/shaparak-debug.log را ببینید؛ مسیر فایل و شماره خطِ
 *      افزونه‌ی مقصر در آن نوشته می‌شود.
 *   ۴) بعد از پیدا کردن افزونه‌ی مقصر، این فایل را از mu-plugins پاک کنید.
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Write a notice + backtrace to the log, once per request.
 *
 * @param string $function_name Function reported by _doing_it_wrong().
 * @param string $message       Notice message.
 */
function shaparak_notice_diagnoser_log( $function_name, $message ) {
	static $seen = array();

	$watched = array( 'add_menu_page', 'add_submenu_page', 'map_meta_cap' );
	if ( ! in_array( $function_name, $watched, true ) ) {
		return;
	}

	// Only care about map_meta_cap noise coming from our post types.
	if ( 'map_meta_cap' === $function_name && false === strpos( $message, 'event' ) ) {
		return;
	}

	$lines = array();
	foreach ( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 12 ) as $frame ) {
		$file = isset( $frame['file'] ) ? $frame['file'] : '';
		if ( '' !== $file ) {
			$file = str_replace(
				array( ABSPATH, WP_PLUGIN_DIR . '/', get_template_directory() . '/' ),
				array( '', 'plugins/', 'theme/' ),
				$file
			);
		}

		$lines[] = sprintf(
			'  %s%s%s()',
			'' !== $file ? $file : '[core]',
			isset( $frame['line'] ) ? ':' . $frame['line'] : '',
			isset( $frame['function'] ) ? ' ' . $frame['function'] : ''
		);
	}

	$entry = sprintf(
		"[%s] %s misuse: %s\n%s\n\n",
		gmdate( 'c' ),
		$function_name,
		wp_strip_all_tags( $message ),
		implode( "\n", $lines )
	);

	$key = md5( $entry );
	if ( isset( $seen[ $key ] ) ) {
		return;
	}
	$seen[ $key ] = true;

	error_log( $entry ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	error_log( $entry, 3, WP_CONTENT_DIR . '/shaparak-debug.log' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
}

add_action( 'doing_it_wrong_run', 'shaparak_notice_diagnoser_log', 10, 2 );
