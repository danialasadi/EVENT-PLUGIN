<?php
/**
 * Elementor widget: event details (Shamsi date, time, venue).
 *
 * Meant for the single-event template built with Elementor Pro Theme Builder,
 * but works anywhere.
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SHAPARAK_EVENTS_PATH . 'widgets/widget-base.php';

/**
 * Class Shaparak_Widget_Event_Meta
 */
class Shaparak_Widget_Event_Meta extends Shaparak_Widget_Base {

	/**
	 * Widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'shaparak-event-meta';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'جزئیات رویداد';
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-table';
	}

	/**
	 * Widget keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'جزئیات', 'تاریخ', 'مکان', 'ساعت', 'meta', 'رویداد' );
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
			'show_date', array( 'label' => 'تاریخ شمسی', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' )
		);
		$this->add_control(
			'show_time', array( 'label' => 'ساعت', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' )
		);
		$this->add_control(
			'show_venue', array( 'label' => 'مکان', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' )
		);
		$this->add_control(
			'show_status', array( 'label' => 'وضعیت رویداد', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' )
		);

		$this->end_controls_section();

		$this->common_style_controls();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s    = $this->get_settings_for_display();
		$post = get_post( get_the_ID() );

		if ( ! $post || Shaparak_Post_Types::POST_TYPE !== $post->post_type ) {
			return;
		}

		$id     = $post->ID;
		$date   = Shaparak_Query::date_label( $id );
		$time   = Shaparak_Query::time_label( $id );
		$venue  = Shaparak_Query::venue_label( $id );
		$state  = Shaparak_Query::state( $id );
		$status = array(
			'current'  => 'در حال برگزاری',
			'upcoming' => 'رویداد پیش رو',
			'past'     => 'رویداد گذشته',
		);

		$rows = array();
		if ( 'yes' === $s['show_date'] && $date ) {
			$rows[] = array( 'icon' => 'calendar', 'label' => 'تاریخ', 'value' => $date );
		}
		if ( 'yes' === $s['show_time'] && $time ) {
			$rows[] = array( 'icon' => 'clock', 'label' => 'ساعت', 'value' => $time );
		}
		if ( 'yes' === $s['show_venue'] && $venue ) {
			$rows[] = array( 'icon' => 'pin', 'label' => 'مکان', 'value' => $venue );
		}
		if ( 'yes' === $s['show_status'] ) {
			$rows[] = array( 'icon' => 'flag', 'label' => 'وضعیت', 'value' => $status[ $state ] );
		}

		if ( ! $rows ) {
			return;
		}

		$this->open_wrapper();
		echo '<ul class="shaparak-event-meta">';
		foreach ( $rows as $row ) {
			printf(
				'<li class="shaparak-event-meta__item shaparak-event-meta__item--%1$s"><span class="shaparak-event-meta__icon" aria-hidden="true"></span><span class="shaparak-event-meta__label">%2$s</span><span class="shaparak-event-meta__value">%3$s</span></li>',
				esc_attr( $row['icon'] ),
				esc_html( $row['label'] ),
				esc_html( $row['value'] )
			);
		}
		echo '</ul>';
		echo '</div>';
	}
}
