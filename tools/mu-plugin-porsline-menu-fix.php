<?php
/**
 * Plugin Name: Shaparak — Porsline menu fix (mu-plugin)
 * Description: نوتیس «پارامتر هفتم add_menu_page» را فقط زمانی که از افزونه porsline می‌آید خاموش می‌کند.
 * Version:     1.0.0
 *
 * این یک راه‌حل موقت (workaround) است، نه اصلاح ریشه‌ای.
 * اصلاح واقعی: در wp-content/plugins/porsline/admin_menu.php خط ۳،
 * پارامتر هفتم add_menu_page() را یک عدد کنید (مثلاً 81) یا آن را حذف کنید.
 *
 * نصب: فایل را در wp-content/mu-plugins/ بگذارید.
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Silence only the add_menu_page notice that originates from the porsline plugin.
 *
 * The filter is asked before WordPress calls trigger_error(), and the backtrace
 * tells us whether the offending call really came from porsline.
 *
 * @param bool   $trigger       Whether to trigger the error.
 * @param string $function_name Function that was called incorrectly.
 * @return bool
 */
function shaparak_porsline_silence_menu_notice( $trigger, $function_name ) {
	if ( 'add_menu_page' !== $function_name ) {
		return $trigger;
	}

	foreach ( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 15 ) as $frame ) {
		if ( ! empty( $frame['file'] ) && false !== strpos( $frame['file'], '/plugins/porsline/' ) ) {
			return false;
		}
	}

	return $trigger;
}

add_filter( 'doing_it_wrong_trigger_error', 'shaparak_porsline_silence_menu_notice', 10, 2 );
