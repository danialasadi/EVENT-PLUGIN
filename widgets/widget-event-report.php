<?php
/**
 * Elementor widget: event report (text + achievements).
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SHAPARAK_EVENTS_PATH . 'widgets/widget-base.php';

/**
 * Class Shaparak_Widget_Event_Report
 */
class Shaparak_Widget_Event_Report extends Shaparak_Widget_Base {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'shaparak-event-report';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'گزارش رویداد';
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-document';
	}

	/**
	 * Keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'گزارش', 'دستاورد', 'خلاصه' );
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

		$id        = $post->ID;
		$text      = Shaparak_Event_Data::text( $id, 'report' );
		$highlights = Shaparak_Event_Data::lines( $id, 'highlights' );

		if ( ! $text && ! $highlights ) {
			return;
		}

		$this->open_wrapper();
		echo '<section class="shaparak-comp shaparak-report">';

		if ( $text ) {
			echo '<div class="shaparak-report__text">' . wp_kses_post( wpautop( $text ) ) . '</div>';
		}

		if ( $highlights ) {
			echo '<strong class="shaparak-report__subtitle">' . esc_html__( 'دستاوردها', 'shaparak-events' ) . '</strong>';
			echo '<ul class="shaparak-checklist">';
			foreach ( $highlights as $line ) {
				printf( '<li><span class="shaparak-icon shaparak-icon--check" aria-hidden="true"></span>%s</li>', esc_html( $line ) );
			}
			echo '</ul>';
		}

		echo '</section>';
		echo '</div>';
	}
}
