<?php
/**
 * Elementor widget: single-event axes chips.
 *
 * Renders only the event's axes as chips. The heading, description and axes
 * subtitle were removed at the client's request so the widget shows just the
 * محورهای رویداد chips.
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SHAPARAK_EVENTS_PATH . 'widgets/widget-base.php';

/**
 * Class Shaparak_Widget_Event_About
 */
class Shaparak_Widget_Event_About extends Shaparak_Widget_Base {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'shaparak-event-about';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'درباره رویداد';
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-info-circle';
	}

	/**
	 * Keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'درباره', 'معرفی', 'توضیحات', 'توصیف', 'محورها', 'آشنایی', 'about' );
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			array(
				'label' => 'محتوا',
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->source_controls();
		$this->end_controls_section();

		$this->common_style_controls();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$post = $this->current_event();
		if ( ! $post ) {
			return;
		}

		$axes = Shaparak_Event_Data::axes( $post->ID );
		if ( ! $axes ) {
			return;
		}

		$this->open_wrapper();
		echo '<section class="shaparak-comp shaparak-about"><div class="shaparak-axes__list">';
		foreach ( $axes as $axis ) {
			printf(
				'<span class="shaparak-chip"><span class="shaparak-icon shaparak-icon--check" aria-hidden="true"></span>%s</span>',
				esc_html( $axis )
			);
		}
		echo '</div></section>';
		echo '</div>';
	}
}
