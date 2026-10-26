<?php
/**
 * Elementor widget: single-event "درباره رویداد" — description + axes chips.
 *
 * Renders the section from the Figma design: a heading with an info badge,
 * the event description (the post's main content), then an optional list of
 * event axes as chips.
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SHAPARAK_EVENTS_PATH . 'widgets/widget-base.php';

/**
 * Class Shaparak_Widget_Event_About
 */
class Shaparak_Widget_Event_About extends Shaparak_Widget_Base {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'shaparak-event-about';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'درباره رویداد';
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-info-circle';
	}

	/**
	 * Keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'درباره', 'معرفی', 'توضیحات', 'توصیف', 'محورها', 'آشنایی', 'about' );
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
			'title',
			array(
				'label'   => 'عنوان بخش',
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'درباره رویداد',
			)
		);
		$this->add_control(
			'axes_title',
			array(
				'label'   => 'عنوان محورها',
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'محورهای رویداد',
			)
		);
		$this->add_control(
			'show_axes',
			array(
				'label'   => 'نمایش محورها',
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
		if ( ! $post ) {
			return;
		}

		$settings   = $this->get_settings_for_display();
		$content    = trim( (string) $post->post_content );
		$axes       = Shaparak_Event_Data::axes( $post->ID );
		$show_axes  = ! empty( $settings['show_axes'] ) && 'yes' === $settings['show_axes'] && $axes;
		$has_title  = isset( $settings['title'] ) && '' !== trim( (string) $settings['title'] );

		if ( '' === $content && ! $show_axes && ! $has_title ) {
			return;
		}

		$this->open_wrapper();
		echo '<section class="shaparak-comp shaparak-about">';

		if ( $has_title ) {
			printf(
				'<h2 class="shaparak-about__title">%s<span class="shaparak-about__info" aria-hidden="true"><span class="shaparak-icon shaparak-icon--info"></span></span></h2>',
				esc_html( $settings['title'] )
			);
		}

		if ( '' !== $content ) {
			echo '<div class="shaparak-about__text">';
			echo wp_kses_post( wpautop( $content ) );
			echo '</div>';
		}

		if ( $show_axes ) {
			$axes_title      = isset( $settings['axes_title'] ) ? (string) $settings['axes_title'] : '';
			$has_axes_title  = '' !== trim( $axes_title );
			if ( $has_axes_title ) {
				printf( '<h3 class="shaparak-about__subtitle">%s</h3>', esc_html( $axes_title ) );
			}
			echo '<div class="shaparak-axes__list">';
			foreach ( $axes as $axis ) {
				printf(
					'<span class="shaparak-chip"><span class="shaparak-icon shaparak-icon--check" aria-hidden="true"></span>%s</span>',
					esc_html( $axis )
				);
			}
			echo '</div>';
		}

		echo '</section>';
		echo '</div>';
	}
}
