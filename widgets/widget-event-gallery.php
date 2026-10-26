<?php
/**
 * Elementor widget: photo gallery.
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SHAPARAK_EVENTS_PATH . 'widgets/widget-base.php';

/**
 * Class Shaparak_Widget_Event_Gallery
 */
class Shaparak_Widget_Event_Gallery extends Shaparak_Widget_Base {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'shaparak-event-gallery';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'گالری تصاویر';
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	/**
	 * Keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'گالری', 'تصاویر', 'عکس', 'تصویر', 'گزارش تصویری' );
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
			'columns',
			array(
				'label'   => 'تعداد ستون (دسکتاپ)',
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '2',
				'options' => array(
					'2' => '۲',
					'3' => '۳',
				),
			)
		);
		$this->add_control(
			'lightbox',
			array(
				'label'   => 'نمایش بزرگ با کلیک',
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
		$post   = $this->current_event();
		$ids    = $post ? Shaparak_Event_Data::gallery( $post->ID ) : array();
		if ( ! $ids ) {
			return;
		}

		$s        = $this->get_settings_for_display();
		$lightbox = 'yes' === $s['lightbox'];

		$this->open_wrapper();
		echo '<section class="shaparak-comp shaparak-gallery" style="--shaparak-cols:' . esc_attr( (int) $s['columns'] ) . '">';
		echo '<div class="shaparak-gallery__grid">';
		foreach ( $ids as $att_id ) {
			$full  = wp_get_attachment_image_url( $att_id, 'large' );
			$thumb = wp_get_attachment_image_url( $att_id, 'medium' );
			if ( ! $thumb ) {
				continue;
			}
			printf(
				'<a class="shaparak-gallery__item" href="%s" %s><img src="%s" alt="%s" loading="lazy"></a>',
				esc_url( $lightbox ? $full : $thumb ),
				$lightbox ? 'data-se-lightbox' : '',
				esc_url( $thumb ),
				esc_attr( get_the_title( $att_id ) )
			);
		}
		echo '</div>';
		echo '</section>';
		echo '</div>';
	}
}
