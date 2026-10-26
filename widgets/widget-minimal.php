<?php
/**
 * Temporary diagnostic widget: metadata only, no controls, ASCII strings.
 *
 * Enabled by define( 'SHAPARAK_MINIMAL_WIDGET', true ); in wp-config.php,
 * then selected with define( 'SHAPARAK_MINIMAL_STAGE', <n> );
 *
 * Stage 1: ASCII title/keywords, no controls, built-in category (known good).
 * Stage 2: Persian title/keywords like the real widgets.
 * Stage 3: content section with TEXT + two SELECT2 fed by get_posts/get_terms.
 * Stage 4: adds a SLIDER control inside the section (range/default).
 * Stage 5: adds a SLIDER control OUTSIDE any section, with selectors.
 * Stage 6: adds a style section (COLOR + SLIDER, both with selectors).
 * Stage 7: uses the custom `shaparak-events` category instead of a built-in one.
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Shaparak_Widget_Minimal
 */
class Shaparak_Widget_Minimal extends \Elementor\Widget_Base {

	/**
	 * Diagnostic stage.
	 *
	 * @return int
	 */
	private static function stage() {
		return defined( 'SHAPARAK_MINIMAL_STAGE' ) ? (int) SHAPARAK_MINIMAL_STAGE : 1;
	}

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'shaparak-minimal';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		if ( self::stage() >= 2 ) {
			return 'بنر رویداد (هدر صفحه رویداد)';
		}

		return 'Shaparak Test';
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-calendar';
	}

	/**
	 * Category: built-in until stage 7.
	 *
	 * @return array
	 */
	public function get_categories() {
		if ( self::stage() >= 7 ) {
			return array( Shaparak_Elementor::CATEGORY );
		}

		$builtin = defined( 'SHAPARAK_BUILTIN_CATEGORY' ) && SHAPARAK_BUILTIN_CATEGORY
			? sanitize_key( SHAPARAK_BUILTIN_CATEGORY )
			: 'basic';

		return array( $builtin );
	}

	/**
	 * Keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		if ( self::stage() >= 2 ) {
			return array( 'بنر', 'هدر', 'کاور', 'تصویر شاخص', 'hero' );
		}

		return array( 'shaparak' );
	}

	/**
	 * Controls, growing with the stage.
	 */
	protected function register_controls() {
		if ( self::stage() < 3 ) {
			return;
		}

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
				'default' => '',
			)
		);

		$this->add_control(
			'event_id',
			array(
				'label'       => 'رویداد',
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'options'     => self::event_options(),
				'label_block' => true,
				'default'     => '',
			)
		);

		$this->add_control(
			'category',
			array(
				'label'       => 'دسته‌بندی',
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'multiple'    => false,
				'options'     => self::category_options(),
				'label_block' => true,
			)
		);

		if ( self::stage() >= 4 ) {
			$this->add_control(
				'height',
				array(
					'label'      => 'ارتفاع',
					'type'       => \Elementor\Controls_Manager::SLIDER,
					'size_units' => array( 'px' ),
					'range'      => array(
						'px' => array(
							'min' => 200,
							'max' => 600,
						),
					),
					'default'    => array(
						'unit'  => 'px',
						'size'  => 380,
					),
				)
			);
		}

		$this->end_controls_section();

		if ( self::stage() >= 5 ) {
			// Deliberately outside any section, mirroring widget-event-hero.
			$this->add_control(
				'hero_height',
				array(
					'label'      => 'ارتفاع بنر',
					'type'       => \Elementor\Controls_Manager::SLIDER,
					'size_units' => array( 'px' ),
					'selectors'  => array(
						'{{WRAPPER}} .shaparak-hero' => 'min-height: {{SIZE}}{{UNIT}};',
					),
				)
			);
		}

		if ( self::stage() >= 6 ) {
			$this->start_controls_section(
				'section_style',
				array(
					'label' => 'ظاهر',
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				)
			);

			$this->add_control(
				'accent',
				array(
					'label'     => 'رنگ سبز سازمانی',
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#16a34a',
					'selectors' => array(
						'{{WRAPPER}} .shaparak-events' => '--shaparak-accent: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'radius',
				array(
					'label'      => 'گردی گوشه‌ها',
					'type'       => \Elementor\Controls_Manager::SLIDER,
					'size_units' => array( 'px' ),
					'range'      => array(
						'px' => array(
							'min' => 0,
							'max' => 32,
						),
					),
					'default'    => array(
						'unit'  => 'px',
						'size'  => 16,
					),
					'selectors'  => array(
						'{{WRAPPER}} .shaparak-events' => '--shaparak-radius: {{SIZE}}px;',
					),
				)
			);

			$this->end_controls_section();
		}
	}

	/**
	 * Empty render.
	 */
	public function render() {
		echo '';
	}

	/**
	 * Published events for the picker.
	 *
	 * @return array
	 */
	private static function event_options() {
		$posts = get_posts(
			array(
				'post_type'      => 'event',
				'posts_per_page' => 100,
				'post_status'    => 'publish',
			)
		);
		$out   = array( '' => 'رویداد فعلی صفحه' );
		foreach ( $posts as $post ) {
			$out[ $post->ID ] = $post->post_title;
		}
		return $out;
	}

	/**
	 * Terms for the picker.
	 *
	 * @return array
	 */
	private static function category_options() {
		$terms = get_terms(
			array(
				'taxonomy'   => 'event_category',
				'hide_empty' => false,
			)
		);
		$out   = array( '' => 'همه دسته‌ها' );
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$out[ $term->slug ] = $term->name;
			}
		}
		return $out;
	}
}
