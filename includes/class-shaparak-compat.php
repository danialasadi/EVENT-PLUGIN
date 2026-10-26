<?php
/**
 * Compatibility shims for third-party code that misbehaves in the admin.
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Shaparak_Compat
 */
class Shaparak_Compat {

	/**
	 * Hook everything.
	 */
	public static function init() {
		add_filter( 'doing_it_wrong_trigger_error', array( __CLASS__, 'filter_porsline_menu_notice' ), 10, 2 );
	}

	/**
	 * Silence the add_menu_page notice coming from the porsline plugin.
	 *
	 * Porsline passes a non-numeric seventh argument to add_menu_page() in
	 * wp-content/plugins/porsline/admin_menu.php. WordPress falls back to the
	 * default menu position in that case, so the notice is only debug noise.
	 *
	 * The real fix belongs in porsline (pass an integer or drop the argument).
	 * This filter only matches calls that really come from that folder, so
	 * every other _doing_it_wrong() notice keeps working as usual.
	 *
	 * @param bool   $trigger       Whether to trigger the error.
	 * @param string $function_name Function that was called incorrectly.
	 * @return bool
	 */
	public static function filter_porsline_menu_notice( $trigger, $function_name ) {
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
}
