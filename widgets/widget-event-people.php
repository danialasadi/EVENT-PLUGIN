<?php
/**
 * Elementor widget: judges and mentors.
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SHAPARAK_EVENTS_PATH . 'widgets/widget-base.php';

/**
 * Class Shaparak_Widget_Event_People
 */
class Shaparak_Widget_Event_People extends Shaparak_Widget_Base {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'shaparak-event-people';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'داوران و منتورها';
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-person';
	}

	/**
	 * Keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'داور', 'منتور', 'اساتید', 'افراد', 'تیم' );
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
		$this->add_control(
			'title',
			array(
				'label'   => 'عنوان بخش',
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'داوران و منتورها',
			)
		);

		$this->source_controls();
		$this->add_control(
			'columns',
			array(
				'label'   => 'تعداد ستون (دسکتاپ)',
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '4',
				'options' => array(
					'2' => '۲',
					'3' => '۳',
					'4' => '۴',
					'5' => '۵',
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
		$post  = $this->current_event();
		$people = $post ? Shaparak_Event_Data::people( $post->ID ) : array();
		if ( ! $people ) {
			return;
		}

		$s = $this->get_settings_for_display();

		$this->open_wrapper();
		$s = $this->get_settings_for_display();
		$this->section_title( $s['title'] );
		echo '<section class="shaparak-comp shaparak-people" style="--shaparak-cols:' . esc_attr( (int) $s['columns'] ) . '">';
		echo '<div class="shaparak-people__grid">';
		foreach ( $people as $person ) {
			$avatar = $person['avatar'] ? wp_get_attachment_image_url( $person['avatar'], 'medium' ) : '';
			echo '<article class="shaparak-person">';
			echo $avatar
				? '<img src="' . esc_url( $avatar ) . '" alt="' . esc_attr( $person['name'] ) . '" loading="lazy">'
				: '<span class="shaparak-person__avatar shaparak-person__avatar--empty" aria-hidden="true"></span>';
			printf( '<strong class="shaparak-person__name">%s</strong>', esc_html( $person['name'] ) );
			printf( '<span class="shaparak-person__role">%s</span>', esc_html( $person['role'] ) );
			echo '</article>';
		}
		echo '</div></section>';
		echo '</div>';
	}
}
