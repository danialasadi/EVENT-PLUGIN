<?php
/**
 * Elementor widget: event axes (chips).
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SHAPARAK_EVENTS_PATH . 'widgets/widget-base.php';

/**
 * Class Shaparak_Widget_Event_Axes
 */
class Shaparak_Widget_Event_Axes extends Shaparak_Widget_Base {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'shaparak-event-axes';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'محورهای رویداد';
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-tags';
	}

	/**
	 * Keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'محور', 'چیپ', 'برچسب', 'موضوعات', 'محورها' );
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
		$axes = $post ? Shaparak_Event_Data::axes( $post->ID ) : array();
		if ( ! $axes ) {
			return;
		}

		$this->open_wrapper();
		echo '<section class="shaparak-comp shaparak-axes"><div class="shaparak-axes__list">';
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
