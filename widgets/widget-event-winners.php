<?php
/**
 * Elementor widget: winners podium.
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SHAPARAK_EVENTS_PATH . 'widgets/widget-base.php';

/**
 * Class Shaparak_Widget_Event_Winners
 */
class Shaparak_Widget_Event_Winners extends Shaparak_Widget_Base {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'shaparak-event-winners';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'برندگان';
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-trophy-solid';
	}

	/**
	 * Keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'برنده', 'برندگان', 'رتبه', 'پودیوم', 'نتایج' );
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
			'podium',
			array(
				'label'   => 'چیدمان پودیومی (۲، ۱، ۳)',
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
		if ( ! $this->state_gate( 'finished', $post ) ) {
			return;
		}
		$winners = $post ? Shaparak_Event_Data::winners( $post->ID ) : array();
		if ( ! $winners ) {
			return;
		}

		$s     = $this->get_settings_for_display();
		$order = $winners;
		if ( 'yes' === $s['podium'] && count( $winners ) >= 3 ) {
			$order   = array( $winners[1], $winners[0], $winners[2] );
			$ranks   = array( 2, 1, 3 );
		} else {
			$ranks = array();
			for ( $i = 1; $i <= count( $winners ); $i++ ) {
				$ranks[] = $i;
			}
		}

		$this->open_wrapper();
		echo '<section class="shaparak-comp shaparak-winners"><div class="shaparak-winners__grid">';
		foreach ( $order as $i => $winner ) {
			$rank  = isset( $ranks[ $i ] ) ? $ranks[ $i ] : ( $i + 1 );
			$image = $winner['image'] ? wp_get_attachment_image_url( $winner['image'], 'medium' ) : '';
			printf(
				'<article class="shaparak-winner %s"><div class="shaparak-winner__media">%s<span class="shaparak-winner__rank shaparak-winner__rank--%d">%s</span></div><strong class="shaparak-winner__title">%s</strong><span class="shaparak-winner__desc">%s</span></article>',
				1 === $rank ? 'is-first' : '',
				$image ? '<img src="' . esc_url( $image ) . '" alt="' . esc_attr( $winner['title'] ) . '" loading="lazy">' : '',
				(int) $rank,
				esc_html( Shaparak_Jalali::to_fa( $rank ) ),
				esc_html( $winner['title'] ),
				esc_html( $winner['desc'] )
			);
		}
		echo '</div></section>';
		echo '</div>';
	}
}
