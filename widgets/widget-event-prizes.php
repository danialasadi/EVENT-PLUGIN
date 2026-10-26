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
		$this->source_controls();
		$this->end_controls_section();

		$this->common_style_controls();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$post   = $this->current_event();
		$prizes = $post ? Shaparak_Event_Data::prizes( $post->ID ) : array();
		if ( ! $prizes ) {
			return;
		}

		$ranks = self::ranks();

		$this->open_wrapper();
		echo '<section class="shaparak-comp shaparak-prizes"><div class="shaparak-prizes__grid">';
		foreach ( $prizes as $i => $prize ) {
			$rank = isset( $ranks[ $i ] ) ? $ranks[ $i ] : $ranks[ count( $ranks ) - 1 ];
			printf(
				'<article class="shaparak-prize %s"><span class="shaparak-prize__rank shaparak-prize__rank--%2$d" aria-hidden="true"></span><span class="shaparak-prize__label">%3$s</span><strong class="shaparak-prize__amount">%4$s</strong><span class="shaparak-prize__desc">%5$s</span></article>',
				0 === $i ? 'is-first' : '',
				( $i + 1 ),
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
