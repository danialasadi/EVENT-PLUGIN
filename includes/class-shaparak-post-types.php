<?php
/**
 * Custom post type + taxonomies for events.
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Shaparak_Post_Types
 */
class Shaparak_Post_Types {

	const POST_TYPE = 'event';
	const CATEGORY  = 'event_category';
	const PROVINCE  = 'event_province';
	const SUBSCRIBER = 'event_notify';

	/**
	 * Hook everything.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'init', array( __CLASS__, 'seed_provinces' ), 20 );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( __CLASS__, 'column_content' ), 10, 2 );
		add_action( 'admin_notices', array( __CLASS__, 'flush_notice' ) );
	}

	/**
	 * Register post type and taxonomy.
	 */
	public static function register() {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'             => array(
					'name'               => 'رویدادها',
					'singular_name'      => 'رویداد',
					'menu_name'          => 'رویدادها',
					'add_new'            => 'افزودن رویداد',
					'add_new_item'       => 'افزودن رویداد جدید',
					'edit_item'          => 'ویرایش رویداد',
					'new_item'           => 'رویداد جدید',
					'view_item'          => 'مشاهده رویداد',
					'view_items'         => 'مشاهده رویدادها',
					'search_items'       => 'جستجوی رویدادها',
					'not_found'          => 'رویدادی پیدا نشد',
					'not_found_in_trash' => 'رویدادی در سطل زباله نیست',
					'all_items'          => 'همه رویدادها',
					'featured_image'     => 'تصویر شاخص رویداد',
					'set_featured_image' => 'تنظیم تصویر شاخص',
					'remove_featured_image' => 'حذف تصویر شاخص',
				),
				'public'             => true,
				'has_archive'        => true,
				'rewrite'            => array(
					'slug'       => 'events',
					'with_front' => false,
				),
				'menu_position'      => 5,
				'menu_icon'          => 'dashicons-calendar-alt',
				'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
				'show_in_rest'       => true,
				'taxonomies'         => array( self::CATEGORY, self::PROVINCE ),
			)
		);

		register_post_type(
			self::SUBSCRIBER,
			array(
				'labels'       => array(
					'name'          => 'اشتراک‌های اطلاع‌رسانی',
					'singular_name' => 'اشتراک',
					'menu_name'     => 'اشتراک‌های اطلاع‌رسانی',
					'edit_item'     => 'ویرایش اشتراک',
					'search_items'  => 'جستجوی اشتراک‌ها',
					'not_found'     => 'اشتراکی ثبت نشده',
				),
				'public'        => false,
				'show_ui'       => true,
				'show_in_menu'  => Shaparak_Post_Types::POST_TYPE,
				'show_in_rest'  => false,
				'supports'      => array( 'title' ),
				'capabilities'  => array(
					'create_posts' => 'do_not_allow',
				),
				'map_meta_cap'  => true,
			)
		);

		register_taxonomy(
			self::CATEGORY,
			self::POST_TYPE,
			array(
				'labels'            => array(
					'name'          => 'دسته‌های رویداد',
					'singular_name' => 'دسته رویداد',
					'menu_name'     => 'دسته‌ها',
					'add_new_item'  => 'افزودن دسته رویداد',
					'edit_item'     => 'ویرایش دسته رویداد',
					'search_items'  => 'جستجوی دسته‌ها',
					'not_found'     => 'دسته‌ای پیدا نشد',
				),
				'hierarchical'      => true,
				'public'            => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array(
					'slug' => 'event-category',
				),
			)
		);

		register_taxonomy(
			self::PROVINCE,
			self::POST_TYPE,
			array(
				'labels'            => array(
					'name'          => 'استان‌ها',
					'singular_name' => 'استان',
					'menu_name'     => 'استان‌ها',
					'all_items'     => 'همه استان‌ها',
					'edit_item'     => 'ویرایش استان',
					'add_new_item'  => 'افزودن استان',
					'search_items'  => 'جستجوی استان‌ها',
					'not_found'     => 'استانی پیدا نشد',
				),
				'hierarchical'      => true,
				'public'            => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array(
					'slug' => 'event-province',
				),
			)
		);
	}

	/**
	 * The 31 provinces of Iran: Persian name => stable slug.
	 *
	 * @return array<string,string>
	 */
	public static function provinces() {
		return array(
			'آذربایجان شرقی'      => 'east-azerbaijan',
			'آذربایجان غربی'      => 'west-azerbaijan',
			'اردبیل'              => 'ardabil',
			'اصفهان'              => 'isfahan',
			'البرز'               => 'alborz',
			'ایلام'               => 'ilam',
			'بوشهر'               => 'bushehr',
			'تهران'               => 'tehran',
			'چهارمحال و بختیاری'  => 'chaharmahal-bakhtiari',
			'خراسان جنوبی'        => 'south-khorasan',
			'خراسان رضوی'         => 'razavi-khorasan',
			'خراسان شمالی'        => 'north-khorasan',
			'خوزستان'             => 'khuzestan',
			'زنجان'               => 'zanjan',
			'سمنان'               => 'semnan',
			'سیستان و بلوچستان'   => 'sistan-baluchestan',
			'فارس'                => 'fars',
			'قزوین'               => 'qazvin',
			'قم'                  => 'qom',
			'کردستان'             => 'kurdistan',
			'کرمان'               => 'kerman',
			'کرمانشاه'            => 'kermanshah',
			'کهگیلویه و بویراحمد' => 'kohgiluyeh-buyer-ahmad',
			'گلستان'              => 'golestan',
			'گیلان'               => 'gilan',
			'لرستان'              => 'lorestan',
			'مازندران'            => 'mazandaran',
			'مرکزی'               => 'markazi',
			'هرمزگان'             => 'hormozegan',
			'همدان'               => 'hamedan',
			'یزد'                 => 'yazd',
		);
	}

	/**
	 * Create the province terms once (31 of them, in the canonical order).
	 */
	public static function seed_provinces() {
		if ( ! taxonomy_exists( self::PROVINCE ) || get_option( 'shaparak_events_provinces' ) ) {
			return;
		}

		$order = 0;
		foreach ( self::provinces() as $name => $slug ) {
			if ( ! term_exists( $slug, self::PROVINCE ) ) {
				wp_insert_term(
					$name,
					self::PROVINCE,
					array(
						'slug'       => $slug,
						'term_group' => $order,
					)
				);
			}
			$order++;
		}

		update_option( 'shaparak_events_provinces', 1 );
	}

	/**
	 * All province terms in the canonical order.
	 *
	 * @return \WP_Term[]
	 */
	public static function province_terms() {
		$terms = get_terms(
			array(
				'taxonomy'   => self::PROVINCE,
				'hide_empty' => false,
			)
		);
		if ( is_wp_error( $terms ) || ! $terms ) {
			return array();
		}

		usort(
			$terms,
			function ( $a, $b ) {
				$ga = (int) $a->term_group;
				$gb = (int) $b->term_group;
				return $ga === $gb ? strcmp( $a->name, $b->name ) : ( $ga <=> $gb );
			}
		);
		return $terms;
	}

	/**
	 * Ask for a rewrite flush once, after activation.
	 */
	public static function flush_notice() {
		if ( get_option( 'shaparak_events_flush' ) ) {
			delete_option( 'shaparak_events_flush' );
			flush_rewrite_rules();
		}
	}

	/**
	 * Activation callback.
	 */
	public static function activate() {
		self::register();
		flush_rewrite_rules();
		update_option( 'shaparak_events_flush', 1 );
	}

	/**
	 * Admin list columns.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public static function columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['event_when'] = 'تاریخ برگزاری (شمسی)';
				$new['event_state'] = 'وضعیت';
			}
		}
		return $new;
	}

	/**
	 * Admin list column values.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 */
	public static function column_content( $column, $post_id ) {
		if ( 'event_when' === $column ) {
			$start = Shaparak_Query::start_timestamp( $post_id );
			$end   = Shaparak_Query::end_timestamp( $post_id );
			if ( ! $start ) {
				echo esc_html__( 'تاریخ تعیین نشده', 'shaparak-events' );
				return;
			}
			$html = Shaparak_Jalali::date( $start ) . '، ' . Shaparak_Jalali::time( $start );
			if ( $end && $end > $start ) {
				$html .= ' تا ' . Shaparak_Jalali::date( $end ) . '، ' . Shaparak_Jalali::time( $end );
			}
			echo esc_html( $html );
		}
		if ( 'event_state' === $column ) {
			$state = Shaparak_Query::state( $post_id );
			$map   = array(
				'current' => array( 'در حال برگزاری', '#16a34a' ),
				'upcoming'=> array( 'پیش رو', '#2563eb' ),
				'past'    => array( 'گذشته', '#64748b' ),
			);
			if ( isset( $map[ $state ] ) ) {
				printf(
					'<span style="color:%s;font-weight:700;">%s</span>',
					esc_attr( $map[ $state ][1] ),
					esc_html( $map[ $state ][0] )
				);
			}
		}
	}
}
