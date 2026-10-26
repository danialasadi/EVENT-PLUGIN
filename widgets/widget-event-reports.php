<?php
/**
 * Elementor widget: held-events "گزارش‌ها و برندگان" slider.
 *
 * A swipeable row of report cards for past events. Each card shows a photo
 * collage, a "برگزار شد" badge with the date, the title, a stats summary and
 * boxes, and a link to the full report. No countdown.
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SHAPARAK_EVENTS_PATH . 'widgets/widget-base.php';

/**
 * Class Shaparak_Widget_Event_Reports
 */
class Shaparak_Widget_Event_Reports extends Shaparak_Widget_Base {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'shaparak-event-reports';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'گزارش‌ها و برندگان';
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
		return array( 'گزارش', 'برندگان', 'برگزارشده', 'گذشته', 'اسناد', 'عکس', 'اسلایدر', 'report' );
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
				'label'   => 'عنوان بخش',
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'برگزارشده · گزارش‌ها و برندگان',
			)
		);

		$this->add_control(
			'number',
			array(
				'label'   => 'تعداد رویدادها',
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 20,
				'default' => 6,
			)
		);

		$this->add_control(
			'show_stats',
			array(
				'label'   => 'نمایش آمار (جعبه‌ها و خلاصه)',
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
		$s = $this->get_settings_for_display();

		$posts = Shaparak_Query::get_events(
			array(
				'state'  => 'past',
				'number' => (int) $s['number'],
			)
		);

		$this->open_wrapper();

		$heading = isset( $s['heading'] ) ? trim( (string) $s['heading'] ) : '';
		if ( '' !== $heading ) {
			printf( '<h2 class="shaparak-reports__head">%s</h2>', esc_html( $heading ) );
		}

		if ( ! $posts ) {
			$this->empty_state();
			echo '</div>';
			return;
		}

		$show_stats = ! empty( $s['show_stats'] ) && 'yes' === $s['show_stats'];

		echo '<div class="shaparak-reports__track">';
		foreach ( $posts as $post ) {
			$this->render_card( $post, $show_stats );
		}
		echo '</div>';

		echo '</div>';
	}

	/**
	 * One report card.
	 *
	 * @param WP_Post $post       Event.
	 * @param bool    $show_stats Whether to render the stats summary + boxes.
	 */
	protected function render_card( $post, $show_stats ) {
		$id      = $post->ID;
		$gallery = Shaparak_Event_Data::gallery( $id );
		$stats   = $show_stats ? Shaparak_Event_Data::rows( $id, 'stats' ) : array();
		$url     = get_permalink( $id );

		// Short Shamsi date, e.g. "۱۸ شهریور".
		$p    = Shaparak_Jalali::parts( Shaparak_Query::start_timestamp( $id ) );
		$date = Shaparak_Jalali::to_fa( $p['jd'] ) . ' ' . Shaparak_Jalali::MONTHS[ $p['jm'] ];

		// Up to three collage photos + a "+N عکس" tile.
		$shots = array();
		foreach ( array_slice( $gallery, 0, 3 ) as $att_id ) {
			$src = wp_get_attachment_image_url( $att_id, 'medium' );
			if ( $src ) {
				$shots[] = $src;
			}
		}
		$more = max( 0, count( $gallery ) - 3 );

		// Stats summary line, e.g. "۲ قرارداد · ۲۴۰ ایده · ۱۸ تیم".
		$summary_parts = array();
		foreach ( $stats as $stat ) {
			$value = isset( $stat['value'] ) ? trim( (string) $stat['value'] ) : '';
			$label = isset( $stat['label'] ) ? trim( (string) $stat['label'] ) : '';
			if ( '' !== $value ) {
				$summary_parts[] = Shaparak_Jalali::to_fa( $value ) . ( '' !== $label ? ' ' . $label : '' );
			}
		}
		$summary = implode( ' · ', $summary_parts );
		?>
		<article class="shaparak-repcard">
			<?php if ( $shots || $more > 0 ) : ?>
				<div class="shaparak-repcard__gallery">
					<?php foreach ( $shots as $src ) : ?>
						<figure class="shaparak-repcard__shot">
							<img src="<?php echo esc_url( $src ); ?>" alt="" loading="lazy">
						</figure>
					<?php endforeach; ?>
					<?php if ( $more > 0 ) : ?>
						<span class="shaparak-repcard__more"><?php echo esc_html( Shaparak_Jalali::to_fa( $more ) . '+ عکس' ); ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="shaparak-repcard__body">
				<span class="shaparak-pill shaparak-pill--soft">
					<?php echo esc_html( 'برگزار شد · ' . $date ); ?>
				</span>

				<h3 class="shaparak-repcard__title">
					<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( get_the_title( $id ) ); ?></a>
				</h3>

				<?php if ( $show_stats && '' !== $summary ) : ?>
					<p class="shaparak-repcard__summary"><?php echo esc_html( $summary ); ?></p>
				<?php endif; ?>

				<?php if ( $stats ) : ?>
					<div class="shaparak-repcard__stats">
						<?php foreach ( array_slice( $stats, 0, 3 ) as $stat ) : ?>
							<div class="shaparak-stat">
								<strong><?php echo esc_html( Shaparak_Jalali::to_fa( isset( $stat['value'] ) ? $stat['value'] : '' ) ); ?></strong>
								<span><?php echo esc_html( isset( $stat['label'] ) ? $stat['label'] : '' ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<a class="shaparak-repcard__cta" href="<?php echo esc_url( $url ); ?>">
					<span>گزارش کامل و تصاویر</span>
					<span class="shaparak-icon shaparak-icon--arrow" aria-hidden="true"></span>
				</a>
			</div>
		</article>
		<?php
	}
}
