<?php
/**
 * Event meta box: Shamsi dates, venue and every repeatable block the design uses.
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Shaparak_Meta
 */
class Shaparak_Meta {

	const NONCE = 'shaparak_event_meta';

	/** @var array<string,string> */
	public static $fields = array(
		'start_jalali' => '_event_start_jalali',
		'start_time'   => '_event_start_time',
		'start'        => '_event_start',
		'end_jalali'   => '_event_end_jalali',
		'end_time'     => '_event_end_time',
		'end'          => '_event_end',
		'venue'        => '_event_venue',
		'city'         => '_event_city',
		'address'      => '_event_address',
		'map'          => '_event_map',
		'directions'   => '_event_directions',
		'link'         => '_event_link',
		'featured'     => '_event_featured',
		'report'       => '_event_report',
		'highlights'   => '_event_highlights',
		'report_pdf'   => '_event_report_pdf',
		'gallery'      => '_event_gallery',
	);

	/** @var array<string,string> Repeatable (JSON) blocks. */
	public static $blocks = array(
		'agenda'  => '_event_agenda',
		'prizes'  => '_event_prizes',
		'people'  => '_event_people',
		'faq'     => '_event_faq',
		'winners' => '_event_winners',
		'stats'   => '_event_stats',
		'axes'    => '_event_axes',
	);

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_box' ) );
		add_action( 'save_post_' . Shaparak_Post_Types::POST_TYPE, array( __CLASS__, 'save' ), 10, 2 );
		add_action( 'admin_footer-post.php', array( __CLASS__, 'admin_script' ) );
		add_action( 'admin_footer-post-new.php', array( __CLASS__, 'admin_script' ) );
	}

	/**
	 * Register the meta box.
	 */
	public static function add_box() {
		add_meta_box(
			'shaparak_event_details',
			'جزئیات رویداد',
			array( __CLASS__, 'render' ),
			Shaparak_Post_Types::POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Field definitions per repeatable block.
	 *
	 * @param string $key Block key.
	 * @return array<int,array<string,string>>
	 */
	private static function block_fields( $key ) {
		$map = array(
			'agenda'  => array(
				array(
					'name'  => 'day',
					'label' => 'عنوان روز (مثلا: روز اول · ۲۴ مهر) — فقط در ردیف اول هر روز',
					'type'  => 'text',
				),
				array(
					'name'  => 'time',
					'label' => 'ساعت (۰۸:۳۰)',
					'type'  => 'text',
				),
				array(
					'name'  => 'title',
					'label' => 'عنوان برنامه',
					'type'  => 'text',
				),
				array(
					'name'  => 'note',
					'label' => 'توضیح / مکان',
					'type'  => 'text',
				),
			),
			'prizes'  => array(
				array(
					'name'  => 'amount',
					'label' => 'جایزه (مثلا: ۵۰۰ میلیون ریال)',
					'type'  => 'text',
				),
				array(
					'name'  => 'desc',
					'label' => 'توضیح (مثلا: استارتاپ برتر)',
					'type'  => 'text',
				),
			),
			'people'  => array(
				array(
					'name'  => 'name',
					'label' => 'نام و نام خانوادگی',
					'type'  => 'text',
				),
				array(
					'name'  => 'role',
					'label' => 'سمت / تخصص',
					'type'  => 'text',
				),
				array(
					'name'  => 'avatar',
					'label' => 'تصویر',
					'type'  => 'media',
				),
			),
			'faq'     => array(
				array(
					'name'  => 'q',
					'label' => 'پرسش',
					'type'  => 'text',
				),
				array(
					'name'  => 'a',
					'label' => 'پاسخ',
					'type'  => 'textarea',
				),
			),
			'winners' => array(
				array(
					'name'  => 'title',
					'label' => 'عنوان طرح / تیم',
					'type'  => 'text',
				),
				array(
					'name'  => 'desc',
					'label' => 'توضیح (تیم یا استارتاپ …)',
					'type'  => 'text',
				),
				array(
					'name'  => 'image',
					'label' => 'تصویر',
					'type'  => 'media',
				),
			),
			'stats'   => array(
				array(
					'name'  => 'value',
					'label' => 'عدد (۲۸)',
					'type'  => 'text',
				),
				array(
					'name'  => 'label',
					'label' => 'برچسب (تیم)',
					'type'  => 'text',
				),
			),
			'axes'    => array(
				array(
					'name'  => 'title',
					'label' => 'عنوان محور',
					'type'  => 'text',
				),
			),
		);

		return isset( $map[ $key ] ) ? $map[ $key ] : array();
	}

	/**
	 * Render the meta box.
	 *
	 * @param WP_Post $post Post.
	 */
	public static function render( $post ) {
		wp_nonce_field( self::NONCE, self::NONCE );
		$placeholder_date = Shaparak_Jalali::format( null, 'Y/m/d', false );
		$get              = static function ( $key ) use ( $post ) {
			return get_post_meta( $post->ID, self::$fields[ $key ], true );
		};
		?>
		<style>
			.se-tabs{display:flex;gap:4px;border-bottom:1px solid #e2e8f0;margin-bottom:18px;flex-wrap:wrap}
			.se-tabs button{background:none;border:none;border-bottom:2px solid transparent;padding:9px 14px;cursor:pointer;font-weight:700;color:#64748b;font-size:13px}
			.se-tabs button.is-active{color:#16a34a;border-bottom-color:#16a34a}
			.se-pane{display:none}
			.se-pane.is-active{display:block}
			.se-grid{display:grid;grid-template-columns:repeat(2,minmax(220px,1fr));gap:16px 24px}
			.se-field{margin-bottom:14px}
			.se-field label{display:block;font-weight:700;margin-bottom:6px;color:#1e293b}
			.se-field input[type=text],.se-field textarea{width:100%;border:1px solid #d7dde7;border-radius:10px;padding:8px 12px;font-size:14px;background:#f8fafc}
			.se-field input:focus,.se-field textarea:focus{outline:none;border-color:#22c55e;background:#fff;box-shadow:0 0 0 3px rgba(34,197,94,.15)}
			.se-hint{color:#64748b;font-size:12px;margin-top:5px}
			.se-check{display:flex;gap:8px;align-items:center;font-weight:700;color:#1e293b}
			.se-block{border:1px solid #e2e8f0;border-radius:12px;padding:14px;margin-bottom:16px;background:#fff}
			.se-block__head{font-weight:800;color:#0f172a;margin-bottom:4px}
			.se-row{position:relative;border:1px dashed #cbd5e1;border-radius:10px;padding:12px;margin-bottom:10px;background:#fcfdfe}
			.se-row__remove{position:absolute;top:6px;left:8px;border:none;background:#fee2e2;color:#b91c1c;border-radius:6px;width:24px;height:24px;line-height:1;cursor:pointer}
			.se-row .se-field{margin-bottom:0}
			.se-row__grid{display:grid;grid-template-columns:repeat(2,minmax(180px,1fr));gap:10px 16px;padding-left:28px}
			.se-media{display:flex;gap:10px;align-items:center}
			.se-media img{width:56px;height:56px;object-fit:cover;border-radius:8px;border:1px solid #e2e8f0}
			.se-gallery{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:10px}
			.se-gallery figure{position:relative;margin:0}
			.se-gallery img{width:72px;height:72px;object-fit:cover;border-radius:8px;border:1px solid #e2e8f0;display:block}
			.se-gallery button{position:absolute;top:-6px;left:-6px;border:none;background:#fee2e2;color:#b91c1c;border-radius:50%;width:20px;height:20px;cursor:pointer;line-height:1}
			@media (max-width:782px){.se-grid,.se-row__grid{grid-template-columns:1fr}}
		</style>

		<div class="se-tabs">
			<button type="button" class="is-active" data-se-tab="time">زمان و مکان</button>
			<button type="button" data-se-tab="agenda">برنامه زمانی</button>
			<button type="button" data-se-tab="prizes">جوایز</button>
			<button type="button" data-se-tab="people">داوران و منتورها</button>
			<button type="button" data-se-tab="winners">برندگان</button>
			<button type="button" data-se-tab="faq">پرسش‌های پرتکرار</button>
			<button type="button" data-se-tab="content">متن و محورها</button>
			<button type="button" data-se-tab="media">گزارش و گالری</button>
		</div>

		<!-- زمان و مکان -->
		<div class="se-pane is-active" data-se-pane="time">
			<div class="se-grid">
				<div class="se-field">
					<label>تاریخ شروع (شمسی)</label>
					<div style="display:flex;gap:10px">
						<input type="text" name="<?php echo esc_attr( self::$fields['start_jalali'] ); ?>" value="<?php echo esc_attr( $get( 'start_jalali' ) ); ?>" placeholder="<?php echo esc_attr( $placeholder_date ); ?>" maxlength="10" inputmode="numeric">
						<input type="text" name="<?php echo esc_attr( self::$fields['start_time'] ); ?>" value="<?php echo esc_attr( $get( 'start_time' ) ); ?>" placeholder="18:30" maxlength="5" style="max-width:90px" inputmode="numeric">
					</div>
					<div class="se-hint">مثال: 1403/07/24 — ساعت ۲۴ ساعته</div>
				</div>

				<div class="se-field">
					<label>تاریخ پایان (شمسی)</label>
					<div style="display:flex;gap:10px">
						<input type="text" name="<?php echo esc_attr( self::$fields['end_jalali'] ); ?>" value="<?php echo esc_attr( $get( 'end_jalali' ) ); ?>" placeholder="<?php echo esc_attr( $placeholder_date ); ?>" maxlength="10" inputmode="numeric">
						<input type="text" name="<?php echo esc_attr( self::$fields['end_time'] ); ?>" value="<?php echo esc_attr( $get( 'end_time' ) ); ?>" placeholder="21:00" maxlength="5" style="max-width:90px" inputmode="numeric">
					</div>
					<div class="se-hint">برای رویداد یک‌روزه خالی بگذارید</div>
				</div>

				<div class="se-field">
					<label>مکان برگزاری</label>
					<input type="text" name="<?php echo esc_attr( self::$fields['venue'] ); ?>" value="<?php echo esc_attr( $get( 'venue' ) ); ?>" placeholder="مرکز نوآوری خراسان">
				</div>
				<div class="se-field">
					<label>شهر</label>
					<input type="text" name="<?php echo esc_attr( self::$fields['city'] ); ?>" value="<?php echo esc_attr( $get( 'city' ) ); ?>" placeholder="مشهد">
				</div>
				<div class="se-field">
					<label>نشانی کامل</label>
					<input type="text" name="<?php echo esc_attr( self::$fields['address'] ); ?>" value="<?php echo esc_attr( $get( 'address' ) ); ?>" placeholder="مشهد، بلوار وکیل‌آباد، نبش وکیل‌آباد ۱۸">
				</div>
				<div class="se-field">
					<label>لینک نقشه (Google Maps)</label>
					<input type="text" dir="ltr" name="<?php echo esc_attr( self::$fields['directions'] ); ?>" value="<?php echo esc_attr( $get( 'directions' ) ); ?>" placeholder="https://maps.google.com/…">
				</div>
				<div class="se-field">
					<label>لینک خارجی رویداد (اختیاری)</label>
					<input type="text" dir="ltr" name="<?php echo esc_attr( self::$fields['link'] ); ?>" value="<?php echo esc_attr( $get( 'link' ) ); ?>" placeholder="https://">
				</div>
				<div class="se-field">
					<label class="se-check">
						<input type="checkbox" name="<?php echo esc_attr( self::$fields['featured'] ); ?>" value="1" <?php checked( $get( 'featured' ), '1' ); ?>>
						رویداد شاخص (کارت بالای صفحه)
					</label>
					<div class="se-hint">وضعیت رویداد (جاری / گذشته) خودکار از روی تاریخ محاسبه می‌شود.</div>
				</div>
			</div>
		</div>

		<!-- برنامه زمانی -->
		<div class="se-pane" data-se-pane="agenda">
			<p class="se-hint">برای شروع هر روز، فقط «عنوان روز» را پر کنید؛ ردیف‌های بعدی همان روز فقط ساعت و عنوان می‌خواهند.</p>
			<?php self::repeater( 'agenda', $post->ID ); ?>
		</div>

		<!-- جوایز -->
		<div class="se-pane" data-se-pane="prizes">
			<?php self::repeater( 'prizes', $post->ID ); ?>
		</div>

		<!-- داوران -->
		<div class="se-pane" data-se-pane="people">
			<?php self::repeater( 'people', $post->ID ); ?>
		</div>

		<!-- برندگان -->
		<div class="se-pane" data-se-pane="winners">
			<?php self::repeater( 'winners', $post->ID ); ?>
		</div>

		<!-- سوالات -->
		<div class="se-pane" data-se-pane="faq">
			<?php self::repeater( 'faq', $post->ID ); ?>
		</div>

		<!-- متن -->
		<div class="se-pane" data-se-pane="content">
			<div class="se-field">
				<label>محورهای رویداد</label>
				<?php self::repeater( 'axes', $post->ID ); ?>
				<div class="se-hint">هر محور یک ردیف است و به صورت چیپ نمایش داده می‌شود.</div>
			</div>
			<div class="se-field">
				<label>گزارش رویداد (برای رویدادهای برگزار شده)</label>
				<textarea rows="5" name="<?php echo esc_attr( self::$fields['report'] ); ?>"><?php echo esc_textarea( $get( 'report' ) ); ?></textarea>
			</div>
			<div class="se-field">
				<label>دستاوردها (هر مورد در یک خط)</label>
				<textarea rows="4" name="<?php echo esc_attr( self::$fields['highlights'] ); ?>"><?php echo esc_textarea( $get( 'highlights' ) ); ?></textarea>
			</div>
			<div class="se-field">
				<label>لینک گزارش کامل (PDF)</label>
				<input type="text" dir="ltr" name="<?php echo esc_attr( self::$fields['report_pdf'] ); ?>" value="<?php echo esc_attr( $get( 'report_pdf' ) ); ?>" placeholder="https://">
			</div>
		</div>

		<!-- گالری -->
		<div class="se-pane" data-se-pane="media">
			<div class="se-block">
				<div class="se-block__head">آمار رویداد (برگزار شده)</div>
				<?php self::repeater( 'stats', $post->ID ); ?>
			</div>
			<div class="se-block">
				<div class="se-block__head">گالری تصاویر</div>
				<div class="se-gallery" data-se-gallery>
					<?php foreach ( Shaparak_Event_Data::gallery( $post->ID ) as $att_id ) : ?>
						<figure data-se-item="<?php echo esc_attr( $att_id ); ?>">
							<img src="<?php echo esc_url( wp_get_attachment_image_url( $att_id, 'thumbnail' ) ); ?>" alt="">
							<button type="button" data-se-gallery-remove aria-label="حذف">×</button>
						</figure>
					<?php endforeach; ?>
				</div>
				<input type="hidden" name="<?php echo esc_attr( self::$fields['gallery'] ); ?>" value="<?php echo esc_attr( $get( 'gallery' ) ); ?>" data-se-gallery-input>
				<button type="button" class="button" data-se-gallery-add>انتخاب تصاویر</button>
			</div>
		</div>
		<?php
	}

	/**
	 * Render one repeatable block.
	 *
	 * @param string $key     Block key.
	 * @param int    $post_id Post ID.
	 */
	private static function repeater( $key, $post_id ) {
		$fields = self::block_fields( $key );
		// Axes may still hold legacy one-per-line text; seed rows from it.
		$rows   = ( 'axes' === $key )
			? Shaparak_Event_Data::axes_rows( $post_id )
			: Shaparak_Event_Data::rows( $post_id, $key );
		$name   = self::$blocks[ $key ];

		echo '<div class="se-repeater" data-se-repeater="' . esc_attr( $key ) . '">';
		echo '<div class="se-repeater__rows">';

		if ( $rows ) {
			foreach ( $rows as $i => $row ) {
				self::repeater_row( $key, $fields, $row, $i, $name );
			}
		}
		echo '</div>';
		echo '<button type="button" class="button" data-se-add>افزودن ردیف</button>';

		// Template cloned by JavaScript.
		echo '<script type="text/template" data-se-template>';
		self::repeater_row( $key, $fields, array(), '__I__', $name );
		echo '</script>';
		echo '</div>';
	}

	/**
	 * One repeatable row.
	 *
	 * @param string       $key  Block key.
	 * @param array        $fields Fields.
	 * @param array        $row   Values.
	 * @param int|string   $i     Index or placeholder.
	 * @param string       $name  Meta key.
	 */
	private static function repeater_row( $key, $fields, $row, $i, $name ) {
		echo '<div class="se-row" data-se-row>';
		echo '<button type="button" class="se-row__remove" data-se-remove aria-label="حذف ردیف">×</button>';
		echo '<div class="se-row__grid">';

		foreach ( $fields as $field ) {
			$value = isset( $row[ $field['name'] ] ) ? $row[ $field['name'] ] : '';
			$input_name = $name . '[' . $i . '][' . $field['name'] . ']';

			echo '<div class="se-field">';
			echo '<label>' . esc_html( $field['label'] ) . '</label>';

			if ( 'textarea' === $field['type'] ) {
				echo '<textarea rows="3" name="' . esc_attr( $input_name ) . '">' . esc_textarea( $value ) . '</textarea>';
			} elseif ( 'media' === $field['type'] ) {
				$att_id = (int) $value;
				echo '<div class="se-media">';
				echo '<img src="' . esc_url( $att_id ? wp_get_attachment_image_url( $att_id, 'thumbnail' ) : '' ) . '" alt="" ' . ( $att_id ? '' : 'style="display:none"' ) . ' data-se-preview>';
				echo '<input type="hidden" name="' . esc_attr( $input_name ) . '" value="' . esc_attr( $value ) . '" data-se-media-input>';
				echo '<button type="button" class="button" data-se-media>انتخاب تصویر</button>';
				echo '</div>';
			} else {
				echo '<input type="text" name="' . esc_attr( $input_name ) . '" value="' . esc_attr( $value ) . '">';
			}

			echo '</div>';
		}

		echo '</div></div>';
	}

	/**
	 * Admin-side behaviour: tabs, repeaters, media pickers, gallery.
	 */
	public static function admin_script() {
		$screen = get_current_screen();
		if ( ! $screen || Shaparak_Post_Types::POST_TYPE !== $screen->post_type ) {
			return;
		}
		?>
		<script>
		(function () {
			var box = document.getElementById('shaparak_event_details');
			if (!box) return;

			/* tabs */
			var tabs = box.querySelectorAll('[data-se-tab]');
			Array.prototype.forEach.call(tabs, function (btn) {
				btn.addEventListener('click', function () {
					Array.prototype.forEach.call(tabs, function (b) { b.classList.remove('is-active'); });
					btn.classList.add('is-active');
					var panes = box.querySelectorAll('[data-se-pane]');
					Array.prototype.forEach.call(panes, function (p) {
						p.classList.toggle('is-active', p.getAttribute('data-se-pane') === btn.getAttribute('data-se-tab'));
					});
				});
			});

			/* repeaters */
			var index = 1000;
			function bindRow(row) {
				var rm = row.querySelector('[data-se-remove]');
				if (rm) rm.addEventListener('click', function () { row.remove(); });

				var mediaBtn = row.querySelector('[data-se-media]');
				if (mediaBtn) {
					mediaBtn.addEventListener('click', function () {
						var frame = wp.media({ title: 'انتخاب تصویر', multiple: false, library: { type: 'image' } });
						frame.on('select', function () {
							var att = frame.state().get('selection').first().toJSON();
							var input = row.querySelector('[data-se-media-input]');
							var preview = row.querySelector('[data-se-preview]');
							if (input) input.value = att.id;
							if (preview) { preview.src = att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url; preview.style.display = ''; }
						});
						frame.open();
					});
				}
			}

			Array.prototype.forEach.call(box.querySelectorAll('[data-se-repeater]'), function (rep) {
				var tpl = rep.querySelector('[data-se-template]');
				var rowsWrap = rep.querySelector('.se-repeater__rows');
				var add = rep.querySelector('[data-se-add]');
				Array.prototype.forEach.call(rep.querySelectorAll('[data-se-row]'), bindRow);
				if (!add || !tpl) return;
				add.addEventListener('click', function () {
					var html = tpl.innerHTML.replace(/__I__/g, 'new' + (index++));
					rowsWrap.insertAdjacentHTML('beforeend', html);
					bindRow(rowsWrap.lastElementChild);
				});
			});

			/* gallery */
			var galleryWrap = box.querySelector('[data-se-gallery]');
			if (galleryWrap) {
				var input = box.querySelector('[data-se-gallery-input]');
				var addBtn = box.querySelector('[data-se-gallery-add]');
				function sync() {
					var ids = [];
					Array.prototype.forEach.call(galleryWrap.querySelectorAll('[data-se-item]'), function (fig) {
						ids.push(fig.getAttribute('data-se-item'));
					});
					if (input) input.value = ids.join(',');
				}
				galleryWrap.addEventListener('click', function (e) {
					if (e.target.hasAttribute('data-se-gallery-remove')) {
						e.target.closest('figure').remove();
						sync();
					}
				});
				if (addBtn) {
					addBtn.addEventListener('click', function () {
						var frame = wp.media({ title: 'انتخاب تصاویر گالری', multiple: true, library: { type: 'image' } });
						frame.on('select', function () {
							frame.state().get('selection').each(function (att) {
								var a = att.toJSON();
								var fig = document.createElement('figure');
								fig.setAttribute('data-se-item', a.id);
								fig.innerHTML = '<img src="' + (a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail.url : a.url) + '" alt="">' +
									'<button type="button" data-se-gallery-remove aria-label="حذف">×</button>';
								galleryWrap.appendChild(fig);
							});
							sync();
						});
						frame.open();
					});
				}
			}
		})();
		</script>
		<?php
	}

	/**
	 * Save every field.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post.
	 */
	public static function save( $post_id, $post ) {
		if ( ! isset( $_POST[ self::NONCE ] ) || ! wp_verify_nonce( sanitize_key( $_POST[ self::NONCE ] ), self::NONCE ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$get = static function ( $field ) {
			return isset( $_POST[ $field ] ) ? wp_unslash( $_POST[ $field ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		};

		// --- dates ------------------------------------------------------
		$start_date = self::normalize_date( $get( self::$fields['start_jalali'] ) );
		$start_time = self::normalize_time( $get( self::$fields['start_time'] ) );
		update_post_meta( $post_id, self::$fields['start_jalali'], $start_date );
		update_post_meta( $post_id, self::$fields['start_time'], $start_time );

		if ( $start_date ) {
			list( $y, $m, $d ) = explode( '/', $start_date );
			list( $hh, $mm )   = explode( ':', $start_time ? $start_time : '00:00' );
			update_post_meta( $post_id, self::$fields['start'], Shaparak_Jalali::timestamp( $y, $m, $d, $hh, $mm ) );
		} else {
			delete_post_meta( $post_id, self::$fields['start'] );
		}

		$end_date = self::normalize_date( $get( self::$fields['end_jalali'] ) );
		$end_time = self::normalize_time( $get( self::$fields['end_time'] ) );
		update_post_meta( $post_id, self::$fields['end_jalali'], $end_date );
		update_post_meta( $post_id, self::$fields['end_time'], $end_time );

		if ( $end_date ) {
			list( $y, $m, $d ) = explode( '/', $end_date );
			list( $hh, $mm )   = explode( ':', $end_time ? $end_time : '23:59' );
			update_post_meta( $post_id, self::$fields['end'], Shaparak_Jalali::timestamp( $y, $m, $d, $hh, $mm ) );
		} else {
			delete_post_meta( $post_id, self::$fields['end'] );
		}

		// --- simple fields ----------------------------------------------
		foreach ( array( 'venue', 'city', 'address', 'link', 'directions', 'report_pdf' ) as $key ) {
			$field = isset( self::$fields[ $key ] ) ? self::$fields[ $key ] : '';
			if ( $field ) {
				update_post_meta( $post_id, $field, sanitize_text_field( $get( $field ) ) );
			}
		}
		update_post_meta( $post_id, self::$fields['map'], sanitize_text_field( $get( self::$fields['map'] ) ) );
		update_post_meta( $post_id, self::$fields['report'], wp_kses_post( $get( self::$fields['report'] ) ) );
		update_post_meta( $post_id, self::$fields['highlights'], wp_kses_post( $get( self::$fields['highlights'] ) ) );
		update_post_meta( $post_id, self::$fields['featured'], isset( $_POST[ self::$fields['featured'] ] ) ? '1' : '' );

		$gallery = sanitize_text_field( $get( self::$fields['gallery'] ) );
		update_post_meta(
			$post_id,
			self::$fields['gallery'],
			implode(
				',',
				array_filter( array_map( 'intval', explode( ',', $gallery ) ) )
			)
		);

		// --- repeatable blocks ------------------------------------------
		foreach ( self::$blocks as $key => $field ) {
			$raw   = isset( $_POST[ $field ] ) ? wp_unslash( $_POST[ $field ] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$rows  = self::sanitize_block( $key, $raw );
			update_post_meta( $post_id, $field, wp_json_encode( $rows, JSON_UNESCAPED_UNICODE ) );
		}
	}

	/**
	 * Sanitize one repeatable block.
	 *
	 * @param string               $key Block key.
	 * @param array<string,mixed>  $raw Raw rows.
	 * @return array<int,array<string,string>>
	 */
	private static function sanitize_block( $key, $raw ) {
		if ( ! is_array( $raw ) ) {
			return array();
		}
		$rows = array();
		foreach ( $raw as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$clean = array();
			foreach ( self::block_fields( $key ) as $field ) {
				$value = isset( $row[ $field['name'] ] ) ? $row[ $field['name'] ] : '';
				if ( 'media' === $field['type'] ) {
					$clean[ $field['name'] ] = (int) $value;
				} elseif ( 'textarea' === $field['type'] ) {
					$clean[ $field['name'] ] = wp_kses_post( $value );
				} else {
					$clean[ $field['name'] ] = sanitize_text_field( $value );
				}
			}
			if ( '' !== implode( '', array_map( 'strval', $clean ) ) ) {
				$rows[] = $clean;
			}
		}
		return $rows;
	}

	/**
	 * Accept Persian or ASCII digits and return "YYYY/MM/DD".
	 *
	 * @param string $value Raw input.
	 * @return string Empty string when invalid.
	 */
	public static function normalize_date( $value ) {
		$value = Shaparak_Jalali::to_en( $value );
		$value = preg_replace( '/[^0-9\/\-\.]/', '', (string) $value );
		$value = str_replace( array( '-', '.' ), '/', $value );
		$parts = explode( '/', $value );
		if ( 3 !== count( $parts ) ) {
			return '';
		}
		list( $y, $m, $d ) = array_map( 'intval', $parts );
		if ( $y < 1200 || $y > 1600 || $m < 1 || $m > 12 || $d < 1 || $d > Shaparak_Jalali::days_in_month( $y, $m ) ) {
			return '';
		}
		return sprintf( '%04d/%02d/%02d', $y, $m, $d );
	}

	/**
	 * Accept Persian or ASCII digits and return "HH:MM".
	 *
	 * @param string $value Raw input.
	 * @return string
	 */
	public static function normalize_time( $value ) {
		$value = Shaparak_Jalali::to_en( $value );
		$value = preg_replace( '/[^0-9:]/', '', (string) $value );
		if ( '' === $value ) {
			return '';
		}
		$parts = explode( ':', $value );
		$hh    = isset( $parts[0] ) ? (int) $parts[0] : 0;
		$mm    = isset( $parts[1] ) ? (int) $parts[1] : 0;
		return sprintf( '%02d:%02d', max( 0, min( 23, $hh ) ), max( 0, min( 59, $mm ) ) );
	}
}
