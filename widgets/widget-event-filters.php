<?php
/**
 * Elementor widget: "فیلترها" — province dropdown + event-type checkboxes.
 *
 * A filter panel matching the design: a heading with a funnel icon, a province
 * <select> populated from the events' cities, and a list of event-type toggles
 * rendered as green checkboxes.
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
		return array( 'فیلتر', 'استان', 'نوع', 'دسته‌بندی', 'انتخاب', 'filter' );
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
			'types',
			array(
				'label'       => 'موارد فیلتر (نوع رویداد)',
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'title_field' => '{{{ label }}}',
				'fields'      => array(
					array(
						'name'    => 'value',
						'label'   => 'مقدار (نوع رویداد)',
						'type'    => \Elementor\Controls_Manager::SELECT,
						'options' => array(
							''           => '—',
							'national'   => 'ملی (national)',
							'provincial' => 'استانی (provincial)',
							'meeting'    => 'نشست (meeting)',
							'online'     => 'آنلاین (online)',
						),
						'default' => 'national',
					),
					array(
						'name'  => 'label',
						'label' => 'عنوان',
						'type'  => \Elementor\Controls_Manager::TEXT,
					),
					array(
						'name'  => 'on',
						'label' => 'پیش‌فرض: انتخاب‌شده',
						'type'  => \Elementor\Controls_Manager::CHECKBOX,
					),
				),
				'default'     => array(
					array( 'value' => 'national', 'label' => 'رویدادهای ملی', 'on' => 'yes' ),
					array( 'value' => 'provincial', 'label' => 'رویدادهای استانی', 'on' => 'yes' ),
					array( 'value' => 'meeting', 'label' => 'نشست‌ها', 'on' => 'yes' ),
					array( 'value' => 'online', 'label' => 'فقط آنلاین', 'on' => '' ),
				),
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
		$types   = isset( $s['types'] ) && is_array( $s['types'] ) ? $s['types'] : array();

		$this->open_wrapper();
		echo '<section class="shaparak-comp shaparak-filters" data-se-filters>';

		if ( '' !== $heading ) {
			printf(
				'<h2 class="shaparak-filters__head"><span class="shaparak-icon shaparak-icon--filter" aria-hidden="true"></span>%s</h2>',
				esc_html( $heading )
			);
		}

		if ( ! empty( $s['show_province'] ) && 'yes' === $s['show_province'] ) {
			$default_label = isset( $s['province_label'] ) ? (string) $s['province_label'] : 'همه استان‌ها';
			?>
			<div class="shaparak-filters__province">
				<select class="shaparak-filters__select" data-se-filter-province>
					<option value=""><?php echo esc_html( $default_label ); ?></option>
					<?php foreach ( self::provinces() as $province ) : ?>
						<option value="<?php echo esc_attr( $province ); ?>"><?php echo esc_html( $province ); ?></option>
					<?php endforeach; ?>
				</select>
				<span class="shaparak-filters__chevron" aria-hidden="true"></span>
			</div>
			<?php
		}

		if ( $types ) {
			echo '<ul class="shaparak-filters__list">';
			foreach ( $types as $item ) {
				$label = isset( $item['label'] ) ? trim( (string) $item['label'] ) : '';
				$value = isset( $item['value'] ) ? sanitize_html_class( (string) $item['value'] ) : '';
				if ( '' === $label ) {
					continue;
				}
				$on = ! empty( $item['on'] ) && 'yes' === $item['on'];
				?>
				<li class="shaparak-filters__item">
					<label class="shaparak-check">
						<input type="checkbox" data-se-filter-type="<?php echo esc_attr( $value ); ?>" <?php checked( $on ); ?>>
						<span class="shaparak-check__box"><span class="shaparak-icon shaparak-icon--check" aria-hidden="true"></span></span>
						<span class="shaparak-check__label"><?php echo esc_html( $label ); ?></span>
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
	 * Distinct event cities, used as the province list.
	 *
	 * @return string[]
	 */
	protected static function provinces() {
		$out = array();
		$events = Shaparak_Query::get_events(
			array(
				'state'  => 'any',
				'number' => 200,
			)
		);
		foreach ( $events as $event ) {
			$city = Shaparak_Event_Data::text( $event->ID, 'city' );
			if ( '' !== $city ) {
				$out[ $city ] = $city;
			}
		}
		return array_values( $out );
	}
}
