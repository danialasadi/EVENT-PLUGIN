<?php
/**
 * Elementor widget: standalone PDF download button.
 *
 * Renders only the "دانلود گزارش کامل (PDF)" button, driven by the event's
 * PDF link meta, so it can be placed anywhere on its own.
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SHAPARAK_EVENTS_PATH . 'widgets/widget-base.php';

/**
 * Class Shaparak_Widget_Event_Pdf
 */
class Shaparak_Widget_Event_Pdf extends Shaparak_Widget_Base {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'shaparak-event-pdf';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'دکمه دانلود PDF';
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-document';
	}

	/**
	 * Keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'دانلود', 'PDF', 'فایل', 'گزارش', 'لینک', 'download' );
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
			'button_label',
			array(
				'label'       => 'متن دکمه',
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => 'دانلود گزارش کامل (PDF)',
				'label_block' => true,
			)
		);

		$this->add_control(
			'new_tab',
			array(
				'label'   => 'باز شدن در تب جدید',
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->end_controls_section();

		$this->common_style_controls();
		$this->pdf_style_controls();
	}

	/**
	 * Button gradient colours.
	 */
	protected function pdf_style_controls() {
		$this->start_controls_section(
			'section_button_style',
			array(
				'label' => 'دکمه',
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'color_from',
			array(
				'label'     => 'رنگ ابتدای گرادیان',
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#1b2a63',
				'selectors' => array(
					'{{WRAPPER}} .shaparak-btn--pdf' => '--shaparak-pdf-from: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'color_to',
			array(
				'label'     => 'رنگ انتهای گرادیان',
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#2f7fd1',
				'selectors' => array(
					'{{WRAPPER}} .shaparak-btn--pdf' => '--shaparak-pdf-to: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'button_width',
			array(
				'label'      => 'عرض دکمه',
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 120,
						'max' => 800,
					),
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .shaparak-btn--pdf' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$post = $this->current_event();
		if ( ! $post ) {
			return;
		}

		$s   = $this->get_settings_for_display();
		$pdf = Shaparak_Event_Data::text( $post->ID, 'report_pdf' );

		// Front-end: nothing to link to. In the editor keep the button visible
		// so it can be styled before the PDF URL is filled in.
		if ( ! $pdf && ! $this->is_edit_mode() ) {
			return;
		}

		$label = isset( $s['button_label'] ) ? trim( (string) $s['button_label'] ) : '';
		if ( '' === $label ) {
			$label = 'دانلود گزارش کامل (PDF)';
		}

		$new_tab = isset( $s['new_tab'] ) && 'yes' === $s['new_tab'];

		$this->open_wrapper();
		printf(
			'<a class="shaparak-btn shaparak-btn--pdf" href="%s"%s rel="noopener noreferrer">%s<span class="shaparak-icon shaparak-icon--download" aria-hidden="true"></span></a>',
			$pdf ? esc_url( $pdf ) : '#',
			$new_tab ? ' target="_blank"' : '',
			esc_html( $label )
		);
		echo '</div>';
	}

	/**
	 * Are we rendering inside the Elementor editor?
	 *
	 * @return bool
	 */
	protected function is_edit_mode() {
		if ( ! class_exists( '\Elementor\Plugin' ) || ! isset( \Elementor\Plugin::$instance->editor ) ) {
			return false;
		}
		$editor = \Elementor\Plugin::$instance->editor;
		return is_object( $editor ) ? (bool) $editor->is_edit_mode() : false;
	}
}
