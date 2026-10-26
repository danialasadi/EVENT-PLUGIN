<?php
/**
 * Elementor widget: Shamsi month calendar.
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SHAPARAK_EVENTS_PATH . 'widgets/widget-base.php';

/**
 * Class Shaparak_Widget_Events_Calendar
 */
class Shaparak_Widget_Events_Calendar extends Shaparak_Widget_Base {

	/**
	 * Widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'shaparak-events-calendar';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'تقویم رویدادها (شمسی)';
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-calendar';
	}

	/**
	 * Widget keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'تقویم', 'ماه', 'شمسی', 'تاریخ', 'calendar' );
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

		$this->add_control(
			'title',
			array(
				'label'   => 'عنوان بخش',
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'تقویم رویدادها',
			)
		);

		$this->add_control(
			'month',
			array(
				'label'       => 'ماه (شمسی)',
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => '1403-07',
				'default'     => '',
				'description' => 'خالی = ماه جاری',
			)
		);

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
		echo Shaparak_Template::calendar_html( $s['month'] ); // phpcs:ignore WordPress.Security.EscapeOutput
		echo '</div>';
	}
}
