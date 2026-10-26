<?php
/**
 * Elementor widget: Shamsi countdown.
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SHAPARAK_EVENTS_PATH . 'widgets/widget-base.php';

/**
 * Class Shaparak_Widget_Event_Countdown
 */
class Shaparak_Widget_Event_Countdown extends Shaparak_Widget_Base {

	/**
	 * Widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'shaparak-event-countdown';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'شمارش معکوس شمسی';
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-countdown';
	}

	/**
	 * Widget keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'شمارش', 'معکوس', 'زمان', 'تایمر', 'شمسی', 'countdown' );
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
			'source',
			array(
				'label'   => 'رویداد',
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'current',
				'options' => array(
					'current'  => 'رویداد فعلی صفحه',
					'featured' => 'رویداد شاخص',
					'specific' => 'رویداد مشخص',
				),
			)
		);

		$this->add_control(
			'event_id',
			array(
				'label'       => 'رویداد',
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'options'     => self::event_options(),
				'label_block' => true,
				'condition'   => array( 'source' => 'specific' ),
			)
		);

		$this->add_control(
			'label_text',
			array(
				'label'   => 'متن بالای تایمر',
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => 'خالی = خودکار (تا شروع / تا پایان)',
			)
		);

		$this->end_controls_section();

		$this->common_style_controls();
	}

	/**
	 * Events for the picker.
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
		$s    = $this->get_settings_for_display();
		$post = null;

		if ( 'specific' === $s['source'] && $s['event_id'] ) {
			$post = get_post( (int) $s['event_id'] );
		} elseif ( 'featured' === $s['source'] ) {
			$post = Shaparak_Query::get_featured_event();
		} else {
			$post = get_post( get_the_ID() );
			if ( $post && Shaparak_Post_Types::POST_TYPE !== $post->post_type ) {
				$post = Shaparak_Query::get_featured_event();
			}
		}

		if ( ! $post ) {
			return;
		}

		$this->open_wrapper();
		echo Shaparak_Template::countdown_html( $post->ID, $s['label_text'] ); // phpcs:ignore WordPress.Security.EscapeOutput
		echo '</div>';
	}
}
