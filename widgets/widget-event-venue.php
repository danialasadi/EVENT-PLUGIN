<?php
/**
 * Elementor widget: venue (map + address + directions).
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SHAPARAK_EVENTS_PATH . 'widgets/widget-base.php';

/**
 * Class Shaparak_Widget_Event_Venue
 */
class Shaparak_Widget_Event_Venue extends Shaparak_Widget_Base {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'shaparak-event-venue';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'محل برگزاری';
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-map-pin';
	}

	/**
	 * Keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'مکان', 'نقشه', 'آدرس', 'مسیریابی', 'محل' );
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
			'show_route',
			array(
				'label'   => 'دکمه مسیریابی',
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);
		$this->add_control(
			'map_placeholder',
			array(
				'label'     => 'تصویر نقشه (در صورت نبودن لینک گوگل‌مپ)',
				'type'      => \Elementor\Controls_Manager::MEDIA,
				'condition' => array( 'show_route' => 'yes' ),
			)
		);
		$this->end_controls_section();

		$this->common_style_controls();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$post   = $this->current_event();
		if ( ! $post ) {
			return;
		}
		$s      = $this->get_settings_for_display();
		$id     = $post->ID;
		$venue  = Shaparak_Event_Data::text( $id, 'venue' );
		$city   = Shaparak_Event_Data::text( $id, 'city' );
		$addr   = Shaparak_Event_Data::text( $id, 'address' );
		$map    = Shaparak_Event_Data::text( $id, 'directions' );
		$name   = $venue ? $venue : $city;

		if ( ! $name && ! $addr && ! $map ) {
			return;
		}

		$this->open_wrapper();
		echo '<section class="shaparak-comp shaparak-venue">';

		// Text column — on the right in RTL.
		echo '<div class="shaparak-venue__info">';
		printf( '<strong class="shaparak-venue__name">%s</strong>', esc_html( $name ? $name : __( 'محل برگزاری', 'shaparak-events' ) ) );
		if ( $addr ) {
			printf( '<span class="shaparak-venue__address">%s</span>', esc_html( $addr ) );
		}
		if ( 'yes' === $s['show_route'] && $map ) {
			printf(
				'<a class="shaparak-btn shaparak-btn--outline" href="%s" target="_blank" rel="noopener"><span class="shaparak-icon shaparak-icon--route" aria-hidden="true"></span>%s</a>',
				esc_url( $map ),
				esc_html__( 'مسیریابی', 'shaparak-events' )
			);
		}
		echo '</div>';

		// Map column — on the left in RTL (above the text when stacked on mobile).
		echo '<div class="shaparak-venue__map">';
		if ( $map ) {
			printf(
				'<iframe src="%s" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="%s"></iframe>',
				esc_url( $map ),
				esc_attr( $name )
			);
		} elseif ( ! empty( $s['map_placeholder']['url'] ) ) {
			printf( '<img src="%s" alt="" loading="lazy">', esc_url( $s['map_placeholder']['url'] ) );
		} else {
			echo '<span class="shaparak-venue__map-empty" aria-hidden="true"></span>';
		}
		echo '<span class="shaparak-venue__pin" aria-hidden="true"></span>';
		echo '</div>';

		echo '</section>';
		echo '</div>';
	}
}
