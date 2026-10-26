<?php
/**
 * Elementor widget: "فیلترها" — province dropdown + category checkboxes.
 *
 * The checkbox list is built from the event_category taxonomy and the dropdown
 * from the event_province taxonomy (31 Iranian provinces), so the panel always
 * mirrors the admin. Changing it hides/shows the event cards of every widget
 * on the page (see assets/js/events.js).
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SHAPARAK_EVENTS_PATH . 'widgets/widget-base.php';

/**
 * Class Shaparak_Widget_Event_Filters
 */
class Shaparak_Widget_Event_Filters extends Shaparak_Widget_Base {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'shaparak-event-filters';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'فیلترها';
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-filter';
	}

	/**
	 * Keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'فیلتر', 'استان', 'دسته‌بندی', 'دسته', 'انتخاب', 'filter', 'province' );
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
			'heading',
			array(
				'label'   => 'عنوان',
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'فیلترها',
			)
		);

		$this->add_control(
			'show_province',
			array(
				'label'   => 'نمایش انتخاب استان',
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'province_label',
			array(
				'label'   => 'متن پیش‌فرض استان',
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'همه استان‌ها',
			)
		);

		$this->add_control(
			'show_categories',
			array(
				'label'   => 'نمایش دسته‌بندی‌ها',
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'categories',
			array(
				'label'       => 'دسته‌بندی‌های نمایش‌داده‌شده',
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => self::category_options(),
				'description' => 'خالی = همهٔ دسته‌بندی‌ها. فهرست از تаксونومی «دسته‌های رویداد» پر می‌شود.',
			)
		);

		$this->end_controls_section();

		$this->common_style_controls();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();

		$heading = isset( $s['heading'] ) ? trim( (string) $s['heading'] ) : '';
		$terms   = $this->category_terms( isset( $s['categories'] ) ? (array) $s['categories'] : array() );
		$provinces = Shaparak_Post_Types::province_terms();

		$show_province = isset( $s['show_province'] ) && 'yes' === $s['show_province'];
		$show_cats     = isset( $s['show_categories'] ) && 'yes' === $s['show_categories'];

		$this->open_wrapper();
		echo '<section class="shaparak-comp shaparak-filters" data-shaparak-filters>';

		if ( '' !== $heading ) {
			printf(
				'<h2 class="shaparak-filters__head">%s<span class="shaparak-icon shaparak-icon--filter" aria-hidden="true"></span></h2>',
				esc_html( $heading )
			);
		}

		if ( $show_province && $provinces ) {
			$default_label = isset( $s['province_label'] ) && '' !== trim( (string) $s['province_label'] )
				? trim( (string) $s['province_label'] )
				: 'همه استان‌ها';
			?>
			<div class="shaparak-filters__province">
				<select class="shaparak-filters__select" data-filter-province>
					<option value=""><?php echo esc_html( $default_label ); ?></option>
					<?php foreach ( $provinces as $term ) : ?>
						<option value="<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_html( $term->name ); ?></option>
					<?php endforeach; ?>
				</select>
				<span class="shaparak-filters__chevron" aria-hidden="true"></span>
			</div>
			<?php
		}

		if ( $show_cats && $terms ) {
			echo '<ul class="shaparak-filters__list">';
			foreach ( $terms as $term ) {
				?>
				<li class="shaparak-filters__item">
					<label class="shaparak-check">
						<input type="checkbox" value="<?php echo esc_attr( $term->slug ); ?>" data-filter-category checked>
						<span class="shaparak-check__box"><span class="shaparak-icon shaparak-icon--check" aria-hidden="true"></span></span>
						<span class="shaparak-check__label"><?php echo esc_html( $term->name ); ?></span>
					</label>
				</li>
				<?php
			}
			echo '</ul>';
		}

		echo '</section>';
		echo '</div>';
	}

	/**
	 * Category terms for the checkbox list, optionally limited by the control.
	 *
	 * @param string[] $keep Slugs to keep; empty = all.
	 * @return \WP_Term[]
	 */
	protected function category_terms( $keep ) {
		$terms = get_terms(
			array(
				'taxonomy'   => Shaparak_Post_Types::CATEGORY,
				'hide_empty' => false,
				'orderby'    => 'name',
			)
		);

		if ( is_wp_error( $terms ) || ! $terms ) {
			return array();
		}

		if ( ! $keep ) {
			return $terms;
		}

		return array_values(
			array_filter(
				$terms,
				function ( $term ) use ( $keep ) {
					return in_array( $term->slug, $keep, true );
				}
			)
		);
	}

	/**
	 * Category options for the picker.
	 *
	 * @return array<string,string>
	 */
	protected static function category_options() {
		$terms = get_terms(
			array(
				'taxonomy'   => Shaparak_Post_Types::CATEGORY,
				'hide_empty' => false,
			)
		);

		$out = array();
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$out[ $term->slug ] = $term->name;
			}
		}
		return $out;
	}
}
