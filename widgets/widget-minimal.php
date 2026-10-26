<?php
/**
 * Temporary diagnostic widget: metadata only, no controls, ASCII strings.
 *
 * Enabled by define( 'SHAPARAK_MINIMAL_WIDGET', true ); in wp-config.php.
 * If the editor panel opens with this widget registered, the real widgets'
 * payload is at fault; if it still hangs, registering any widget is enough
 * to break the boot and the problem sits elsewhere.
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
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'shaparak-minimal';
	}

	/**
	 * Diagnostic stage: 1 = ASCII strings (known good),
	 * 2 = Persian title/keywords like the real widgets,
	 * 3 = plus real-style controls (SELECT2 fed by get_posts).
	 *
	 * @return int
	 */
	private static function stage() {
		return defined( 'SHAPARAK_MINIMAL_STAGE' ) ? (int) SHAPARAK_MINIMAL_STAGE : 1;
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
	 * Category: always a built-in one so the custom category is out of scope.
	 *
	 * @return array
	 */
	public function get_categories() {
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
	 * Controls: only from stage 3 on.
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

		$this->end_controls_section();
	}

	/**
	 * Published events for the picker (same shape as the real widgets).
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
	 * Terms for the picker (same shape as the real widgets).
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

	/**
	 * Empty render.
	 */
	public function render() {
		echo '';
	}
}
