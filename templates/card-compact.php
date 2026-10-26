<?php
/**
 * Compact session card used by the "this week" strip.
 *
 * @package ShaparakEvents
 *
 * @var WP_Post $post
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! $post ) {
	return;
}

$id    = $post->ID;
$badge = Shaparak_Query::date_badge( $id );
$state = Shaparak_Query::state( $id );
$link  = Shaparak_Event_Data::text( $id, 'link' );
$url   = $link ? $link : get_permalink( $id );
?>
<article class="shaparak-compact" data-se-type="<?php echo esc_attr( Shaparak_Event_Data::type( $id ) ); ?>" data-se-city="<?php echo esc_attr( Shaparak_Event_Data::text( $id, 'city' ) ); ?>>
	<div class="shaparak-compact__date">
		<strong><?php echo esc_html( $badge['day'] ); ?></strong>
		<span><?php echo esc_html( $badge['month'] ); ?></span>
	</div>

	<div class="shaparak-compact__main">
		<span class="shaparak-pill shaparak-pill--soft"><?php echo esc_html( Shaparak_Event_Data::badge( $state )['label'] ); ?></span>
		<h3 class="shaparak-compact__title">
			<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( get_the_title( $id ) ); ?></a>
		</h3>
		<p class="shaparak-compact__meta">
			<?php if ( Shaparak_Query::time_label( $id ) ) : ?>
				<span class="shaparak-icon shaparak-icon--clock" aria-hidden="true"></span>
				<?php echo esc_html( Shaparak_Query::time_label( $id ) ); ?>
			<?php endif; ?>
			<?php if ( Shaparak_Query::venue_label( $id ) ) : ?>
				<span class="shaparak-icon shaparak-icon--pin" aria-hidden="true"></span>
				<?php echo esc_html( Shaparak_Query::venue_label( $id ) ); ?>
			<?php endif; ?>
		</p>
	</div>

	<a class="shaparak-btn shaparak-btn--ghost shaparak-compact__cta" href="<?php echo esc_url( $url ); ?>">
		<span class="shaparak-icon shaparak-icon--eye" aria-hidden="true"></span>
		<?php echo esc_html__( 'مشاهده رویداد', 'shaparak-events' ); ?>
	</a>
</article>
