<?php
/**
 * Elementor widget: this week's sessions (compact cards).
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SHAPARAK_EVENTS_PATH . 'widgets/widget-base.php';

/**
 * Class Shaparak_Widget_Events_Week
 */
class Shaparak_Widget_Events_Week extends Shaparak_Widget_Base {

	/**
	 * Widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'shaparak-events-week';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'نشست‌های این هفته';
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-calendar-date';
	}

	/**
	 * Widget keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'نشست', 'هفته', 'رویداد', 'week', 'شاپرک' );
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
				'default' => 'این هفته · نشست‌ها',
			)
		);

		$this->add_control(
			'number',
			array(
				'label'   => 'تعداد',
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 20,
				'default' => 6,
			)
		);

		$this->add_control(
			'category',
			array(
				'label'       => 'دسته‌بندی',
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'multiple'    => false,
				'options'     => self::category_options(),
				'label_block' => true,
				'description' => 'خالی = همهٔ دسته‌بندی‌ها',
			)
		);

		$this->end_controls_section();

		$this->common_style_controls();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s     = $this->get_settings_for_display();
		$posts = Shaparak_Query::get_week_events(
			(int) $s['number'],
			isset( $s['category'] ) ? (string) $s['category'] : ''
		);

		$this->open_wrapper();

		if ( ! $posts ) {
			$this->empty_state();
		} else {
			$this->section_title( $s['title'] );
			echo '<div class="shaparak-week">';
			foreach ( $posts as $post ) {
				Shaparak_Template::part( 'card-compact', array( 'post' => $post ) );
			}
			echo '</div>';
		}

		echo '</div>';
	}
}
