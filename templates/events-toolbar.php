<?php
/**
 * Events page toolbar: title, breadcrumb, search and list/calendar toggle.
 *
 * @package ShaparakEvents
 *
 * @var string  $title
 * @var string  $subtitle
 * @var bool    $show_breadcrumb
 * @var bool    $show_search
 * @var bool    $show_toggle
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title    = isset( $title ) ? (string) $title : '';
$subtitle = isset( $subtitle ) ? (string) $subtitle : '';
$search   = get_search_query();
?>
<header class="shaparak-toolbar">
	<div class="shaparak-toolbar__head">
		<?php if ( $title ) : ?>
			<h1 class="shaparak-section__title"><span class="dot"></span><?php echo esc_html( $title ); ?></h1>
		<?php endif; ?>

		<?php if ( $show_breadcrumb ) : ?>
			<nav class="shaparak-toolbar__crumb" aria-label="مسیر راهنما">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html__( 'خانه', 'shaparak-events' ); ?></a>
				<span aria-hidden="true">/</span>
				<span><?php echo esc_html__( 'رویدادها', 'shaparak-events' ); ?></span>
			</nav>
		<?php endif; ?>

		<?php if ( $subtitle ) : ?>
			<p class="shaparak-toolbar__subtitle"><?php echo esc_html( $subtitle ); ?></p>
		<?php endif; ?>
	</div>

	<?php if ( $show_search ) : ?>
		<form class="shaparak-toolbar__search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="shaparak-visually-hidden" for="shaparak-toolbar-s-<?php echo (int) get_the_ID(); ?>">
				<?php echo esc_html__( 'جستجوی رویداد', 'shaparak-events' ); ?>
			</label>
			<input id="shaparak-toolbar-s-<?php echo (int) get_the_ID(); ?>" type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php echo esc_attr__( 'جستجوی رویداد…', 'shaparak-events' ); ?>">
			<input type="hidden" name="post_type" value="<?php echo esc_attr( Shaparak_Post_Types::POST_TYPE ); ?>">
			<button class="shaparak-toolbar__submit" type="submit" aria-label="<?php echo esc_attr__( 'جستجو', 'shaparak-events' ); ?>">
				<span class="shaparak-icon shaparak-icon--search" aria-hidden="true"></span>
			</button>
		</form>
	<?php endif; ?>

	<?php if ( $show_toggle ) : ?>
		<div class="shaparak-toolbar__toggle">
			<a href="#shaparak-events" class="is-active">
				<span class="shaparak-icon shaparak-icon--eye" aria-hidden="true"></span>
				<?php echo esc_html__( 'فهرست', 'shaparak-events' ); ?>
			</a>
			<a href="#shaparak-calendar">
				<span class="shaparak-icon shaparak-icon--calendar" aria-hidden="true"></span>
				<?php echo esc_html__( 'تقویم', 'shaparak-events' ); ?>
			</a>
		</div>
	<?php endif; ?>
</header>
