<?php
/**
 * Elementor widget: events page toolbar (title, breadcrumb, search, list/calendar toggle).
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SHAPARAK_EVENTS_PATH . 'widgets/widget-base.php';

/**
 * Class Shaparak_Widget_Events_Toolbar
 */
class Shaparak_Widget_Events_Toolbar extends Shaparak_Widget_Base {

	/**
	 * Widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'shaparak-events-toolbar';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'هدر صفحه رویدادها';
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-heading';
	}

	/**
	 * Widget keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'هدر', 'عنوان', 'جستجو', 'تقویم', 'فهرست', 'رویدادها' );
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
				'label'   => 'عنوان',
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'رویدادها و نشست‌ها',
			)
		);

		$this->add_control(
			'subtitle',
			array(
				'label'   => 'توضیح زیر عنوان',
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => 'رویدادهای ملی و استانی ایده‌پردازی و نشست‌های هفتگی',
			)
		);

		$this->add_control(
			'show_breadcrumb', array( 'label' => 'مسیر راهنما', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' )
		);
		$this->add_control(
			'show_search', array( 'label' => 'جستجو', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' )
		);
		$this->add_control(
			'show_toggle', array( 'label' => 'کلید فهرست / تقویم', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' )
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
		Shaparak_Template::part(
			'events-toolbar',
			array(
				'post'           => null,
				'title'          => $s['title'],
				'subtitle'       => $s['subtitle'],
				'show_breadcrumb'=> 'yes' === $s['show_breadcrumb'],
				'show_search'    => 'yes' === $s['show_search'],
				'show_toggle'    => 'yes' === $s['show_toggle'],
			)
		);
		echo '</div>';
	}
}
