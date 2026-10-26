<?php
/**
 * Temporary diagnostic widget: metadata only, no controls, ASCII strings.
 *
 * Enabled by define( 'SHAPARAK_MINIMAL_WIDGET', true ); in wp-config.php.
 * If the editor panel opens with this widget registered, the real widgets'
 * payload is at fault; if it still hangs, registering any widget is enough
 * to break the boot and the problem sits elsewhere.
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Shaparak_Widget_Minimal
 */
class Shaparak_Widget_Minimal extends \Elementor\Widget_Base {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'shaparak-minimal';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'Shaparak Test';
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-calendar';
	}

	/**
	 * Category: always a built-in one so the custom category is out of scope.
	 *
	 * @return array
	 */
	public function get_categories() {
		$builtin = defined( 'SHAPARAK_BUILTIN_CATEGORY' ) && SHAPARAK_BUILTIN_CATEGORY
			? sanitize_key( SHAPARAK_BUILTIN_CATEGORY )
			: 'basic';

		return array( $builtin );
	}

	/**
	 * Keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'shaparak' );
	}

	/**
	 * No controls on purpose.
	 */
	protected function register_controls() {
	}

	/**
	 * Empty render.
	 */
	public function render() {
		echo '';
	}
}
