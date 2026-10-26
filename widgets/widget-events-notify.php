<?php
/**
 * Elementor widget: session notification sign-up (stored in the site, no SMS gateway needed).
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SHAPARAK_EVENTS_PATH . 'widgets/widget-base.php';

/**
 * Class Shaparak_Widget_Events_Notify
 */
class Shaparak_Widget_Events_Notify extends Shaparak_Widget_Base {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'shaparak-events-notify';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'اطلاع‌رسانی پیامکی';
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-notification';
	}

	/**
	 * Keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'اطلاعیه', 'پیامک', 'اعلان', 'عضویت', 'خبرنامه' );
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
			'description',
			array(
				'label'   => 'توضیح',
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => 'هر رویداد و پذیرایی نشست هفته را پیامک می‌کنیم.',
			)
		);

		$this->add_control(
			'opt_weekly',
			array(
				'label'   => 'گزینه: نشست‌های هفتگی',
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'نشست‌های هفتگی',
			)
		);

		$this->add_control(
			'opt_province',
			array(
				'label'   => 'گزینه: رویدادهای استان من',
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'رویدادهای استان من',
			)
		);

		$this->add_control(
			'button',
			array(
				'label'   => 'متن دکمه',
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'فعال‌سازی اطلاع‌رسانی',
			)
		);

		$this->end_controls_section();

		$this->common_style_controls();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s      = $this->get_settings_for_display();
		$key    = 'shaparak_notify_' . get_the_ID();
		$sent   = get_transient( $key . '_ok' );
		$error  = get_transient( $key . '_err' );

		$this->open_wrapper();
		?>
		<section class="shaparak-comp shaparak-notify">
			<form method="post" class="shaparak-notify__form">
				<?php wp_nonce_field( 'shaparak_notify', 'shaparak_notify_nonce' ); ?>
				<input type="hidden" name="shaparak_notify" value="1">
				<input type="text" name="shaparak_website" value="" class="shaparak-notify__hp" tabindex="-1" autocomplete="off" aria-hidden="true">

				<?php if ( $s['description'] ) : ?>
					<p class="shaparak-notify__desc"><?php echo esc_html( $s['description'] ); ?></p>
				<?php endif; ?>

				<?php if ( $s['opt_weekly'] ) : ?>
					<label class="shaparak-switch">
						<input type="checkbox" name="shaparak_notify_weekly" value="1" checked>
						<span class="shaparak-switch__track" aria-hidden="true"></span>
						<span class="shaparak-switch__label"><?php echo esc_html( $s['opt_weekly'] ); ?></span>
					</label>
				<?php endif; ?>

				<?php if ( $s['opt_province'] ) : ?>
					<label class="shaparak-switch">
						<input type="checkbox" name="shaparak_notify_province" value="1">
						<span class="shaparak-switch__track" aria-hidden="true"></span>
						<span class="shaparak-switch__label"><?php echo esc_html( $s['opt_province'] ); ?></span>
					</label>
				<?php endif; ?>

				<label class="shaparak-notify__phone">
					<span class="shaparak-notify__phonelabel"><?php echo esc_html__( 'شماره همراه', 'shaparak-events' ); ?></span>
					<input type="tel" name="shaparak_notify_phone" inputmode="numeric" placeholder="۰۹۱۲۳۴۵۶۷۸۹" required>
				</label>

				<?php if ( $sent ) : ?>
					<p class="shaparak-notify__msg is-ok"><?php echo esc_html__( 'ثبت شد؛ به‌زودی اطلاع می‌دهیم.', 'shaparak-events' ); ?></p>
				<?php elseif ( $error ) : ?>
					<p class="shaparak-notify__msg is-error"><?php echo esc_html( $error ); ?></p>
				<?php endif; ?>

				<button type="submit" class="shaparak-btn shaparak-btn--primary shaparak-notify__submit">
					<span class="shaparak-icon shaparak-icon--bell" aria-hidden="true"></span>
					<?php echo esc_html( $s['button'] ? $s['button'] : __( 'فعال‌سازی اطلاع‌رسانی', 'shaparak-events' ) ); ?>
				</button>
			</form>
		</section>
		<?php
		echo '</div>';
	}
}
