<?php
/**
 * Elementor widget: prizes.
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SHAPARAK_EVENTS_PATH . 'widgets/widget-base.php';

/**
 * Class Shaparak_Widget_Event_Prizes
 */
class Shaparak_Widget_Event_Prizes extends Shaparak_Widget_Base {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'shaparak-event-prizes';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'جوایز';
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-trophy';
	}

	/**
	 * Keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'جایزه', 'جوایز', 'رتبه', 'مسابقه', 'پاداش' );
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
				'default' => 'جوایز',
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

		$prizes = $post ? Shaparak_Event_Data::prizes( $post->ID ) : array();
		if ( ! $prizes ) {
			// Inside the editor, explain the empty state instead of vanishing.
			if ( $this->shaparak_edit_mode() ) {
				$this->open_wrapper();
				echo '<p class="shaparak-empty">';
				if ( $post ) {
					printf(
						/* translators: %s: event title. */
						esc_html__( 'برای رویداد «%s» جایزه‌ای ثبت نشده است. از تب «جوایز» در جزئیات رویداد ردیف اضافه کنید.', 'shaparak-events' ),
						esc_html( get_the_title( $post ) )
					);
				} else {
					echo esc_html__( 'رویدادی پیدا نشد — رویداد را در تنظیمات ویجت انتخاب کنید یا یک رویداد شاخص داشته باشید.', 'shaparak-events' );
				}
				echo '</p></div>';
			}
			return;
		}

		$ranks = self::ranks();

		// Three ranks render as one row of three, as in the design.
		$grid_class = 3 === count( $prizes ) ? ' shaparak-prizes__grid--three' : '';

		$this->open_wrapper();
		$s = $this->get_settings_for_display();
		$this->section_title( $s['title'] );
		printf(
			'<section class="shaparak-comp shaparak-prizes"><div class="shaparak-prizes__grid%s">',
			esc_attr( $grid_class )
		);
		foreach ( $prizes as $i => $prize ) {
			$rank   = isset( $ranks[ $i ] ) ? $ranks[ $i ] : $ranks[ count( $ranks ) - 1 ];
			$number = $i + 1;
			printf(
				'<article class="shaparak-prize %1$s"><span class="shaparak-prize__rank shaparak-prize__rank--%2$d" aria-hidden="true">%3$s</span><span class="shaparak-prize__label">%4$s</span><strong class="shaparak-prize__amount">%5$s</strong><span class="shaparak-prize__desc">%6$s</span></article>',
				0 === $i ? 'is-first' : '',
				$number,
				esc_html( Shaparak_Jalali::to_fa( $number ) ),
				esc_html( $rank ),
				esc_html( $prize['amount'] ),
				esc_html( $prize['desc'] )
			);
		}
		echo '</div></section>';
		echo '</div>';
	}

	/**
	 * Persian rank labels.
	 *
	 * @return array
	 */
	private static function ranks() {
		return array(
			Shaparak_Jalali::to_fa( 'رتبه ۱' ),
			Shaparak_Jalali::to_fa( 'رتبه ۲' ),
			Shaparak_Jalali::to_fa( 'رتبه ۳' ),
		);
	}
}
