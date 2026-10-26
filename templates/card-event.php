<?php
/**
 * Event card (grid or row layout).
 *
 * @package ShaparakEvents
 *
 * @var WP_Post $post
 * @var string  $layout grid|row
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! $post ) {
	return;
}

$id      = $post->ID;
$state   = Shaparak_Query::state( $id );
$badge   = Shaparak_Event_Data::badge( $state );
$date    = Shaparak_Query::date_label( $id );
$time    = Shaparak_Query::time_label( $id );
$venue   = Shaparak_Query::venue_label( $id );
$cover   = Shaparak_Event_Data::cover_url( $id );
$link    = Shaparak_Event_Data::text( $id, 'link' );
$url     = $link ? $link : get_permalink( $id );
$is_past = 'past' === $state;
$classes = 'shaparak-card shaparak-card--' . ( $layout ? $layout : 'grid' ) . ( $is_past ? ' is-past' : '' );
?>
<article <?php post_class( $classes, $id ); ?> data-se-type="<?php echo esc_attr( Shaparak_Event_Data::type( $id ) ); ?>" data-se-city="<?php echo esc_attr( Shaparak_Event_Data::text( $id, 'city' ) ); ?>>
	<a class="shaparak-card__media" href="<?php echo esc_url( $url ); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( $cover ) : ?>
			<img src="<?php echo esc_url( $cover ); ?>" alt="<?php echo esc_attr( get_the_title( $id ) ); ?>" loading="lazy">
		<?php else : ?>
			<span class="shaparak-card__placeholder"></span>
		<?php endif; ?>
		<span class="shaparak-card__badges">
			<span class="shaparak-pill shaparak-pill--navy"><?php echo esc_html( $badge['label'] ); ?></span>
			<?php if ( $is_past ) : ?>
				<span class="shaparak-pill shaparak-pill--green"><?php echo esc_html__( 'گزارش و تصاویر', 'shaparak-events' ); ?></span>
			<?php else : ?>
				<span class="shaparak-pill shaparak-pill--green"><?php echo esc_html__( 'بازدید آزاد', 'shaparak-events' ); ?></span>
			<?php endif; ?>
		</span>
	</a>

	<div class="shaparak-card__body">
		<h3 class="shaparak-card__title">
			<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( get_the_title( $id ) ); ?></a>
		</h3>

		<?php if ( $date ) : ?>
			<p class="shaparak-card__meta shaparak-card__meta--date">
				<span class="shaparak-icon shaparak-icon--calendar" aria-hidden="true"></span>
				<?php echo esc_html( $date ); ?><?php if ( $time ) : ?><span class="shaparak-card__time"><?php echo esc_html( $time ); ?></span><?php endif; ?>
			</p>
		<?php endif; ?>

		<?php if ( $venue ) : ?>
			<p class="shaparak-card__meta shaparak-card__meta--venue">
				<span class="shaparak-icon shaparak-icon--pin" aria-hidden="true"></span>
				<?php echo esc_html( $venue ); ?>
			</p>
		<?php endif; ?>

		<a class="shaparak-btn shaparak-btn--primary shaparak-card__cta" href="<?php echo esc_url( $url ); ?>">
			<span class="shaparak-icon shaparak-icon--eye" aria-hidden="true"></span>
			<?php echo esc_html__( 'مشاهده رویداد', 'shaparak-events' ); ?>
		</a>
	</div>
</article>
