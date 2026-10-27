<?php
/**
 * Shared base for the Elementor widgets.
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Shaparak_Widget_Base
 */
abstract class Shaparak_Widget_Base extends \Elementor\Widget_Base {

	/**
	 * Widget category (our own Elementor panel section).
	 *
	 * Diagnostic: define( 'SHAPARAK_BUILTIN_CATEGORY', 'basic' ) in wp-config.php
	 * to point every widget at a built-in category instead of ours, which tells
	 * whether the custom category is what stalls the panel.
	 *
	 * @return array
	 */
	public function get_categories() {
		if ( defined( 'SHAPARAK_BUILTIN_CATEGORY' ) && SHAPARAK_BUILTIN_CATEGORY ) {
			return array( sanitize_key( SHAPARAK_BUILTIN_CATEGORY ) );
		}

		return array( Shaparak_Elementor::CATEGORY );
	}

	/**
	 * Gate a single-event widget by the event state.
	 *
	 * @param string       $show_for finished (past only), unfinished (current or upcoming), any.
	 * @param WP_Post|null $post     Resolved event.
	 * @return bool True when rendering may continue.
	 */
	protected function state_gate( $show_for, $post ) {
		if ( 'any' === $show_for || ! $post ) {
			return true;
		}

		$finished = 'past' === Shaparak_Query::state( $post->ID );
		$visible  = 'finished' === $show_for ? $finished : ! $finished;
		if ( $visible ) {
			return true;
		}

		// In the editor, say why this section is hidden for the event.
		if ( $this->shaparak_edit_mode() ) {
			$this->open_wrapper();
			echo '<p class="shaparak-empty">';
			if ( 'finished' === $show_for ) {
				echo esc_html__( 'این بخش فقط پس از خاتمه رویداد نمایش داده می‌شود.', 'shaparak-events' );
			} else {
				echo esc_html__( 'این بخش فقط تا خاتمه رویداد نمایش داده می‌شود.', 'shaparak-events' );
			}
			echo '</p></div>';
		}
		return false;
	}

	/**
	 * Are we rendering inside the Elementor editor?
	 *
	 * Prefixed to avoid clashing with any method Elementor may define.
	 *
	 * @return bool
	 */
	protected function shaparak_edit_mode() {
		if ( ! class_exists( '\Elementor\Plugin' ) || ! isset( \Elementor\Plugin::$instance->editor ) ) {
			return false;
		}
		$editor = \Elementor\Plugin::$instance->editor;
		return is_object( $editor ) ? (bool) $editor->is_edit_mode() : false;
	}

	/**
	 * Common content controls: title, state, count, layout.
	 *
	 * @param bool $with_layout Include the layout selector.
	 */
	protected function common_controls( $with_layout = true ) {
		$this->add_control(
			'title',
			array(
				'label'   => 'عنوان بخش',
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => '',
			)
		);

		$this->add_control(
			'state',
			array(
				'label'   => 'وضعیت رویدادها',
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'past',
				'options' => array(
					'past'     => 'رویدادهای گذشته',
					'current'  => 'رویدادهای فعلی (در حال برگزاری)',
					'upcoming' => 'رویدادهای پیش رو',
					'any'      => 'همه رویدادها',
				),
			)
		);

		$this->add_control(
			'number',
			array(
				'label'   => 'تعداد',
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 50,
				'default' => 6,
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

		if ( $with_layout ) {
			$this->add_control(
				'layout',
				array(
					'label'   => 'چیدمان',
					'type'    => \Elementor\Controls_Manager::SELECT,
					'default' => 'grid',
					'options' => array(
						'grid' => 'کارت‌های شبکه‌ای',
						'row'  => 'کارت‌های افقی (لیستی)',
					),
				)
			);
		}
	}

	/**
	 * Shared style controls (accent colour + corner radius).
	 */
	protected function common_style_controls() {
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
				'label'   => 'رنگ سبز سازمانی',
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => '#16a34a',
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
				'default'    => array( 'unit' => 'px', 'size' => 16 ),
				'selectors'  => array(
					'{{WRAPPER}} .shaparak-events' => '--shaparak-radius: {{SIZE}}px;',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Category options for the filter dropdown.
	 *
	 * @return array
	 */
	protected static function category_options() {
		$terms = get_terms(
			array(
				'taxonomy'   => Shaparak_Post_Types::CATEGORY,
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

	/**
	 * Print the shared opening wrapper.
	 *
	 * @param string $extra Extra class names.
	 */
	protected function open_wrapper( $extra = '' ) {
		printf(
			'<div class="shaparak-events %s">',
			esc_attr( trim( 'shaparak-widget shaparak-widget--' . $this->get_name() . ' ' . $extra ) )
		);
	}

	/**
	 * Section heading, matching the design (green dot + title).
	 *
	 * @param string $title Title.
	 */
	protected function section_title( $title ) {
		if ( '' === trim( (string) $title ) ) {
			return;
		}
		printf(
			'<h2 class="shaparak-section__title"><span class="dot"></span>%s</h2>',
			esc_html( $title )
		);
	}

	/**
	 * Shared "which event" control: empty = the event being viewed.
	 */
	protected function source_controls() {
		$this->add_control(
			'event_id',
			array(
				'label'       => 'رویداد',
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'options'     => self::event_options(),
				'label_block' => true,
				'default'     => '',
				'description' => 'خالی = رویداد فعلی صفحه',
			)
		);
	}

	/**
	 * Resolve the event a widget should render.
	 *
	 * @return WP_Post|null
	 */
	protected function current_event() {
		$s = $this->get_settings_for_display();
		return Shaparak_Event_Data::resolve( ! empty( $s['event_id'] ) ? (int) $s['event_id'] : 0 );
	}

	/**
	 * Published events for the picker.
	 *
	 * @return array
	 */
	protected static function event_options() {
		$posts = get_posts(
			array(
				'post_type'      => Shaparak_Post_Types::POST_TYPE,
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
	 * Empty state message.
	 */
	protected function empty_state() {
		echo '<p class="shaparak-empty">' . esc_html__( 'رویدادی پیدا نشد.', 'shaparak-events' ) . '</p>';
	}

	/**
	 * Render a list of events through the shared card template.
	 *
	 * @param WP_Post[] $posts  Events.
	 * @param string    $layout Layout name.
	 */
	protected function render_cards( $posts, $layout = 'grid' ) {
		printf( '<div class="shaparak-grid shaparak-grid--%s">', esc_attr( $layout ) );
		foreach ( $posts as $post ) {
			Shaparak_Template::part(
				'card-event',
				array(
					'post'   => $post,
					'layout' => $layout,
				)
			);
		}
		echo '</div>';
	}
}
