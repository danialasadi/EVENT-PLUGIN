<?php
/**
 * Elementor widget: featured (hero) event card.
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SHAPARAK_EVENTS_PATH . 'widgets/widget-base.php';

/**
 * Class Shaparak_Widget_Event_Featured
 */
class Shaparak_Widget_Event_Featured extends Shaparak_Widget_Base {

	/**
	 * Widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'shaparak-event-featured';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'رویداد شاخص (کارت بالای صفحه)';
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-banner';
	}

	/**
	 * Widget keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'رویداد', 'شاخص', 'فعلی', 'hero', 'بنر', 'شاپرک' );
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
			'mode',
			array(
				'label'   => 'نحوه انتخاب',
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'auto',
				'options' => array(
					'auto'     => 'خودکار (رویداد فعلی یا آخرین)',
					'current'  => 'فقط رویداد در حال برگزاری',
					'specific' => 'رویداد مشخص',
				),
			)
		);

		$this->add_control(
			'event_id',
			array(
				'label'     => 'رویداد',
				'type'      => \Elementor\Controls_Manager::SELECT2,
				'options'   => self::event_options(),
				'label_block' => true,
				'condition' => array( 'mode' => 'specific' ),
			)
		);

		$this->add_control(
			'empty_text',
			array(
				'label'   => 'متن وقتی رویدادی نیست',
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'در حال حاضر رویداد شاخصی ثبت نشده است.',
			)
		);

		$this->end_controls_section();

		$this->common_style_controls();
	}

	/**
	 * Published events for the picker.
	 *
	 * @return array
	 */
	protected static function event_options() {
		$posts = get_posts(
			array(
				'post_type'      => Shaparak_Post_Types::POST_TYPE,
				'posts_per_page' => 100,
				'post_status'    => 'publish',
			)
		);
		$out   = array();
		foreach ( $posts as $post ) {
			$out[ $post->ID ] = $post->post_title;
		}
		return $out;
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();

		$this->open_wrapper();

		$post = null;
		if ( 'specific' === $s['mode'] && $s['event_id'] ) {
			$post = get_post( (int) $s['event_id'] );
		} elseif ( 'current' === $s['mode'] ) {
			$post = Shaparak_Query::get_current_event();
		} else {
			$post = Shaparak_Query::get_featured_event();
		}

		if ( $post && Shaparak_Post_Types::POST_TYPE === $post->post_type ) {
			Shaparak_Template::part(
				'card-featured',
				array(
					'post' => $post,
				)
			);
		} else {
			printf(
				'<p class="shaparak-empty">%s</p>',
				esc_html( $s['empty_text'] )
			);
		}

		echo '</div>';
	}
}
