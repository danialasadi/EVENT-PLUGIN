<?php
/**
 * Elementor widget: events list / grid.
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SHAPARAK_EVENTS_PATH . 'widgets/widget-base.php';

/**
 * Class Shaparak_Widget_Events_List
 */
class Shaparak_Widget_Events_List extends Shaparak_Widget_Base {

	/**
	 * Widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'shaparak-events-list';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'رویدادها (فهرست)';
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-post-list';
	}

	/**
	 * Widget keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'رویداد', 'رویدادها', 'ایونت', 'event', 'فهرست', 'گذشته', 'شاپرک' );
	}

	/**
	 * Register controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			array(
				'label' => 'محتوا',
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->common_controls( true );
		$this->end_controls_section();

		$this->common_style_controls();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();

		$this->open_wrapper();
		$this->section_title( $s['title'] );

		$posts = Shaparak_Query::get_events(
			array(
				'state'    => $s['state'] ? $s['state'] : 'any',
				'number'   => (int) $s['number'],
				'category' => $s['category'],
			)
		);

		if ( ! $posts ) {
			$this->empty_state();
		} else {
			$this->render_cards( $posts, $s['layout'] );
		}

		echo '</div>';
	}
}
