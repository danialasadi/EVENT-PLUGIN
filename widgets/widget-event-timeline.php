<?php
/**
 * Elementor widget: agenda timeline (روزها + آیتم‌های ساعت‌دار).
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SHAPARAK_EVENTS_PATH . 'widgets/widget-base.php';

/**
 * Class Shaparak_Widget_Event_Timeline
 */
class Shaparak_Widget_Event_Timeline extends Shaparak_Widget_Base {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'shaparak-event-timeline';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'برنامه زمانی (تایم‌لاین)';
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-post-list';
	}

	/**
	 * Keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'برنامه', 'تایم‌لاین', 'زمان‌بندی', 'جدول', 'روز', 'اول', 'دوم' );
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
				'default' => 'برنامه زمانی',
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
		if ( ! $this->state_gate( 'unfinished', $post ) ) {
			return;
		}
		if ( ! $post ) {
			return;
		}

		$days = Shaparak_Event_Data::agenda( $post->ID );
		if ( ! $days ) {
			return;
		}

		$this->open_wrapper();
		$s = $this->get_settings_for_display();
		$this->section_title( $s['title'] );
		echo '<section class="shaparak-comp shaparak-timeline">';

		if ( count( $days ) > 1 ) {
			echo '<div class="shaparak-tabs" data-se-tabs>';
			foreach ( $days as $i => $day ) {
				printf(
					'<button type="button" class="shaparak-tabs__tab %s" data-se-tab-target="day-%d">%s</button>',
					0 === $i ? 'is-active' : '',
					(int) $i,
					esc_html( $day['day'] )
				);
			}
			echo '</div>';
		}

		foreach ( $days as $i => $day ) {
			printf(
				'<div class="shaparak-timeline__day %s" data-se-tab-pane="day-%d">',
				0 === $i ? 'is-active' : '',
				(int) $i
			);
			if ( count( $days ) > 1 ) {
				printf( '<h3 class="shaparak-timeline__daytitle">%s</h3>', esc_html( $day['day'] ) );
			}
			echo '<ol class="shaparak-timeline__list">';
			foreach ( $day['items'] as $k => $item ) {
				$is_now = ( 0 === $k && count( $day['items'] ) > 2 );
				printf(
					'<li class="shaparak-timeline__item %s"><time class="shaparak-timeline__time">%s</time><span class="shaparak-timeline__dot" aria-hidden="true"></span><div class="shaparak-timeline__content"><strong>%s</strong>%s</div></li>',
					$is_now ? 'is-current' : '',
					esc_html( Shaparak_Jalali::to_fa( $item['time'] ) ),
					esc_html( $item['title'] ),
					$item['note'] ? '<span>' . esc_html( $item['note'] ) . '</span>' : ''
				);
			}
			echo '</ol></div>';
		}

		echo '</section>';
		echo '</div>';
	}
}
