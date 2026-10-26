<?php
/**
 * Single-event hero banner: cover image, gradient, badges, title, meta.
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

$id      = $post->ID;
$state   = Shaparak_Query::state( $id );
$badge   = Shaparak_Event_Data::badge( $state );
$date    = Shaparak_Query::date_label( $id );
$venue   = Shaparak_Query::venue_label( $id );
$cover   = Shaparak_Event_Data::cover_url( $id );
$terms   = get_the_terms( $id, Shaparak_Post_Types::CATEGORY );
$term    = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
?>
<section class="shaparak-hero<?php echo $cover ? '' : ' shaparak-hero--empty'; ?>"
	<?php echo $cover ? 'style="--shaparak-hero-image:url(' . esc_url( $cover ) . ')"' : ''; ?>>
	<div class="shaparak-hero__inner">
		<p class="shaparak-featured__badges">
			<?php if ( $term ) : ?>
				<span class="shaparak-pill shaparak-pill--glass"><?php echo esc_html( $term->name ); ?></span>
			<?php endif; ?>
			<span class="shaparak-pill shaparak-pill--green"><?php echo esc_html( $badge['label'] ); ?></span>
		</p>

		<h1 class="shaparak-hero__title"><?php echo esc_html( get_the_title( $id ) ); ?></h1>

		<p class="shaparak-hero__meta">
			<?php if ( $venue ) : ?>
				<span class="shaparak-meta-item">
					<span class="shaparak-icon shaparak-icon--pin" aria-hidden="true"></span>
					<?php echo esc_html( $venue ); ?>
				</span>
			<?php endif; ?>
			<?php if ( $date ) : ?>
				<span class="shaparak-meta-item">
					<span class="shaparak-icon shaparak-icon--calendar" aria-hidden="true"></span>
					<?php echo esc_html( $date ); ?>
				</span>
			<?php endif; ?>
		</p>
	</div>
</section>
