<?php
/**
 * Regression tests for the Shamsi date helpers used by the event meta box.
 *
 * Run with any PHP CLI:  php tools/test-date.php
 *
 * WordPress sets PHP's timezone to UTC and reports the site offset through
 * gmt_offset, so the harness mirrors that setup. It locks down the three
 * bugs that used to corrupt event dates:
 *   1. jalali_to_gregorian() was two days ahead of the real calendar;
 *   2. timestamp() ignored gmt_offset, shifting the printed time by the
 *      site offset (and moving late-evening events to the next day);
 *   3. days_in_month() returned 29 for the Esfand of leap years, so
 *      "Esfand 30" could never be saved.
 *
 * @package ShaparakEvents
 */

define( 'ABSPATH', __DIR__ . '/' );
define( 'HOUR_IN_SECONDS', 3600 );
define( 'DAY_IN_SECONDS', 86400 );
date_default_timezone_set( 'UTC' );

$GLOBALS['stub_gmt_offset'] = 0;

/**
 * Minimal stand-in for WordPress' get_option().
 *
 * @param string $key Option name.
 * @return mixed
 */
function get_option( $key ) {
	return 'gmt_offset' === $key ? $GLOBALS['stub_gmt_offset'] : false;
}

require __DIR__ . '/../includes/class-shaparak-jalali.php';
require __DIR__ . '/../includes/class-shaparak-meta.php';

$pass = 0;
$fail = 0;

/**
 * Record one assertion.
 *
 * @param string $label Label.
 * @param bool   $cond  Result.
 * @param string $extra Failure detail.
 */
function check( $label, $cond, $extra = '' ) {
	global $pass, $fail;
	if ( $cond ) {
		$pass++;
		echo "ok   {$label}\n";
	} else {
		$fail++;
		echo "FAIL {$label} {$extra}\n";
	}
}

// --- Jalali -> Gregorian (anchors: Nowruz dates, Revolution day) ---------
$backward = array(
	// Verified against khamenei.ir: "the first of Farvardin, 1403 (March 20, 2024)".
	'1403/01/01 = 2024-03-20' => array( array( 1403, 1, 1 ), array( 2024, 3, 20 ) ),
	'1404/01/01 = 2025-03-21' => array( array( 1404, 1, 1 ), array( 2025, 3, 21 ) ),
	'1405/01/01 = 2026-03-21' => array( array( 1405, 1, 1 ), array( 2026, 3, 21 ) ),
	'1357/11/22 = 1979-02-11' => array( array( 1357, 11, 22 ), array( 1979, 2, 11 ) ),
	'1395/06/31 = 2016-09-21' => array( array( 1395, 6, 31 ), array( 2016, 9, 21 ) ),
);
foreach ( $backward as $label => $pair ) {
	list( $j, $g ) = $pair;
	$got = Shaparak_Jalali::jalali_to_gregorian( $j[0], $j[1], $j[2] );
	check( $label, $got === $g, 'got ' . implode( '-', $got ) );
}

// --- Gregorian -> Jalali ------------------------------------------------
$forward = array(
	'2024-03-20 = 1403/01/01' => array( array( 2024, 3, 20 ), array( 1403, 1, 1 ) ),
	'2025-03-21 = 1404/01/01' => array( array( 2025, 3, 21 ), array( 1404, 1, 1 ) ),
	'2026-03-21 = 1405/01/01' => array( array( 2026, 3, 21 ), array( 1405, 1, 1 ) ),
	'1979-02-11 = 1357/11/22' => array( array( 1979, 2, 11 ), array( 1357, 11, 22 ) ),
	'2026-10-26 = 1405/08/04' => array( array( 2026, 10, 26 ), array( 1405, 8, 4 ) ),
);
foreach ( $forward as $label => $pair ) {
	list( $g, $j ) = $pair;
	$got = Shaparak_Jalali::gregorian_to_jalali( $g[0], $g[1], $g[2] );
	check( $label, $got === $j, 'got ' . implode( '/', $got ) );
}

// --- round-trip: what save() stores must be what the site prints --------
foreach ( array( 0, 3.5 ) as $offset ) {
	$GLOBALS['stub_gmt_offset'] = $offset;
	$bad = array();

	for ( $m = 1; $m <= 12; $m++ ) {
		foreach ( array( 1, 15, 28 ) as $d ) {
			foreach ( array( array( 0, 0 ), array( 18, 30 ), array( 23, 59 ) ) as $hm ) {
				$ts = Shaparak_Jalali::timestamp( 1405, $m, $d, $hm[0], $hm[1] );
				$p  = Shaparak_Jalali::parts( $ts );
				if ( 1405 !== $p['jy'] || $m !== $p['jm'] || $d !== $p['jd'] || (int) $p['h'] !== $hm[0] || (int) $p['i'] !== $hm[1] ) {
					$bad[] = "1405/{$m}/{$d} {$hm[0]}:{$hm[1]} -> {$p['jy']}/{$p['jm']}/{$p['jd']} {$p['h']}:{$p['i']}";
				}
			}
		}
	}
	check( "timestamp round-trip at gmt_offset={$offset}", ! $bad, implode( ' | ', array_slice( $bad, 0, 4 ) ) );
}

// --- the stored epoch must be the real moment (for state()/countdown) ---
$GLOBALS['stub_gmt_offset'] = 3.5;
$noon_tehran                = Shaparak_Jalali::timestamp( 1405, 8, 4, 12, 0 );
check(
	'12:00 Tehran is stored as 08:30 UTC',
	gmdate( 'Y-m-d G:i', $noon_tehran ) === '2026-10-26 8:30',
	gmdate( 'Y-m-d G:i', $noon_tehran )
);

// --- Persian formatting -------------------------------------------------
check(
	'date format uses Persian digits',
	Shaparak_Jalali::format( Shaparak_Jalali::timestamp( 1405, 7, 27, 18, 30 ), 'j F Y' ) === '۲۷ مهر ۱۴۰۵',
	Shaparak_Jalali::format( Shaparak_Jalali::timestamp( 1405, 7, 27, 18, 30 ), 'j F Y' )
);

// --- meta box input normalising ----------------------------------------
$date_cases = array(
	'1405/07/27'   => '1405/07/27',
	'۱۴۰۵/۰۷/۲۷'   => '1405/07/27',
	'1405/7/27'    => '1405/07/27',
	'1405-07-27'   => '1405/07/27',
	' 1405/07/27 ' => '1405/07/27',
	'1403/12/30'   => '1403/12/30', // Esfand 30 of a leap year must be accepted
	'1405/12/30'   => '',           // 1405 is not a leap year
	'1405/13/01'   => '',
	'1405/07/32'   => '',
	'2026/10/26'   => '',           // Gregorian must be rejected
	'1405/07'      => '',
	''             => '',
	'رویداد'       => '',
);
foreach ( $date_cases as $in => $want ) {
	$got = Shaparak_Meta::normalize_date( $in );
	check( 'normalize_date(' . var_export( $in, true ) . ')', $got === $want, 'got ' . var_export( $got, true ) );
}

$time_cases = array(
	'18:30'  => '18:30',
	'۱۸:۳۰'  => '18:30',
	'9:5'    => '09:05',
	'25:70'  => '23:59',
	''       => '',
	'رویداد' => '',
);
foreach ( $time_cases as $in => $want ) {
	$got = Shaparak_Meta::normalize_time( $in );
	check( 'normalize_time(' . var_export( $in, true ) . ')', $got === $want, 'got ' . var_export( $got, true ) );
}

// --- month lengths ------------------------------------------------------
check( 'days_in_month(1403,12) = 30 (leap)', 30 === Shaparak_Jalali::days_in_month( 1403, 12 ), (string) Shaparak_Jalali::days_in_month( 1403, 12 ) );
check( 'days_in_month(1405,12) = 29', 29 === Shaparak_Jalali::days_in_month( 1405, 12 ), (string) Shaparak_Jalali::days_in_month( 1405, 12 ) );
check( 'days_in_month(1405,6) = 31', 31 === Shaparak_Jalali::days_in_month( 1405, 6 ), (string) Shaparak_Jalali::days_in_month( 1405, 6 ) );
check( 'days_in_month(1405,7) = 30', 30 === Shaparak_Jalali::days_in_month( 1405, 7 ), (string) Shaparak_Jalali::days_in_month( 1405, 7 ) );

echo "\npassed: {$pass}, failed: {$fail}\n";
exit( $fail ? 1 : 0 );
