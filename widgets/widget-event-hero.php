<?php
/**
 * Elementor widget: single-event hero banner.
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SHAPARAK_EVENTS_PATH . 'widgets/widget-base.php';

/**
 * Class Shaparak_Widget_Event_Hero
 */
class Shaparak_Widget_Event_Hero extends Shaparak_Widget_Base {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'shaparak-event-hero';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'بنر رویداد (هدر صفحه رویداد)';
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-slider-full-screen';
	}

	/**
	 * Keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'بنر', 'هدر', 'کاور', 'تصویر شاخص', 'hero' );
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
			'height',
			array(
				'label'   => 'ارتفاع',
				'type'    => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'   => array(
					'px' => array(
						'min' => 200,
						'max' => 600,
					),
				),
				'default' => array(
					'unit' => 'px',
					'size' => 380,
				),
			)
		);
		$this->add_control(
			'hero_height',
			array(
				'label'      => 'ارتفاع بنر',
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .shaparak-hero' => 'min-height: {{SIZE}}{{UNIT}};',
				),
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
		if ( ! $post ) {
			return;
		}

		$this->open_wrapper();
		Shaparak_Template::part(
			'hero',
			array(
				'post' => $post,
			)
		);
		echo '</div>';
	}
}
