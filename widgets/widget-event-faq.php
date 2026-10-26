<?php
/**
 * Elementor widget: FAQ accordion.
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SHAPARAK_EVENTS_PATH . 'widgets/widget-base.php';

/**
 * Class Shaparak_Widget_Event_Faq
 */
class Shaparak_Widget_Event_Faq extends Shaparak_Widget_Base {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'shaparak-event-faq';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'پرسش‌های پرتکرار';
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-faq';
	}

	/**
	 * Keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'سوال', 'پرسش', 'پاسخ', 'accordion', 'متداول' );
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
		$this->add_control(
			'first_open',
			array(
				'label'   => 'باز بودن پرسش اول',
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);
		$this->end_controls_section();

		$this->common_style_controls();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$post = $this->current_event();
		$faq  = $post ? Shaparak_Event_Data::faq( $post->ID ) : array();
		if ( ! $faq ) {
			return;
		}

		$s = $this->get_settings_for_display();

		$this->open_wrapper();
		echo '<section class="shaparak-comp shaparak-faq">';
		foreach ( $faq as $i => $item ) {
			$open = ( 0 === $i && 'yes' === $s['first_open'] );
			printf(
				'<details class="shaparak-faq__item %s" %s><summary class="shaparak-faq__q">%s<span class="shaparak-faq__chevron" aria-hidden="true"></span></summary><div class="shaparak-faq__a">%s</div></details>',
				$open ? 'is-open' : '',
				$open ? 'open' : '',
				esc_html( $item['q'] ),
				wp_kses_post( wpautop( $item['a'] ) )
			);
		}
		echo '</section>';
		echo '</div>';
	}
}
