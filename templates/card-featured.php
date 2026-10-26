<?php
/**
 * Featured (hero) event card.
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
$time    = Shaparak_Query::time_label( $id );
$venue   = Shaparak_Query::venue_label( $id );
$cover   = Shaparak_Event_Data::cover_url( $id );
$excerpt = $post->post_excerpt ? $post->post_excerpt : wp_trim_words( wp_strip_all_tags( $post->post_content ), 28 );
$link    = Shaparak_Event_Data::text( $id, 'link' );
$url     = $link ? $link : get_permalink( $id );
$stats   = Shaparak_Event_Data::stats( $id );
?>
<div class="shaparak-featured">
	<div class="shaparak-featured__body">
		<p class="shaparak-featured__badges">
			<span class="shaparak-pill shaparak-pill--navy"><?php echo esc_html( $badge['label'] ); ?></span>
			<?php if ( $state !== 'past' ) : ?>
				<span class="shaparak-pill shaparak-pill--green"><?php echo esc_html__( 'بازدید آزاد', 'shaparak-events' ); ?></span>
			<?php endif; ?>
		</p>

		<h2 class="shaparak-featured__title">
			<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( get_the_title( $id ) ); ?></a>
		</h2>

		<?php if ( $excerpt ) : ?>
			<p class="shaparak-featured__text"><?php echo esc_html( $excerpt ); ?></p>
		<?php endif; ?>

		<p class="shaparak-featured__meta">
			<?php if ( $date ) : ?>
				<span class="shaparak-meta-item">
					<span class="shaparak-icon shaparak-icon--calendar" aria-hidden="true"></span>
					<?php echo esc_html( $date ); ?>
				</span>
			<?php endif; ?>
			<?php if ( $venue ) : ?>
				<span class="shaparak-meta-item">
					<span class="shaparak-icon shaparak-icon--pin" aria-hidden="true"></span>
					<?php echo esc_html( $venue ); ?>
				</span>
			<?php endif; ?>
		</p>

		<?php echo Shaparak_Template::countdown_html( $id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>

		<?php if ( $stats ) : ?>
			<p class="shaparak-featured__note">
				<?php
				$parts = array();
				foreach ( $stats as $stat ) {
					$parts[] = Shaparak_Jalali::to_fa( $stat['value'] ) . ' ' . $stat['label'];
				}
				echo esc_html( implode( '، ', $parts ) );
				?>
			</p>
		<?php endif; ?>

		<div class="shaparak-featured__actions">
			<a class="shaparak-btn shaparak-btn--primary" href="<?php echo esc_url( $url ); ?>">
				<span class="shaparak-icon shaparak-icon--eye" aria-hidden="true"></span>
				<?php echo esc_html__( 'مشاهده رویداد', 'shaparak-events' ); ?>
			</a>
		</div>
	</div>

	<a class="shaparak-featured__media" href="<?php echo esc_url( $url ); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( $cover ) : ?>
			<img src="<?php echo esc_url( $cover ); ?>" alt="" loading="lazy">
		<?php else : ?>
			<span class="shaparak-card__placeholder"></span>
		<?php endif; ?>
	</a>
</div>
