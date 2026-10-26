<?php
/**
 * Solar Hijri (Jalali) date helpers.
 *
 * Pure-PHP conversion based on the standard Birashk/Khayyam arithmetic
 * algorithm — no external extensions required.
 *
 * @package ShaparakEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Shaparak_Jalali
 */
class Shaparak_Jalali {

	const MONTHS = array(
		1  => 'فروردین',
		2  => 'اردیبهشت',
		3  => 'خرداد',
		4  => 'تیر',
		5  => 'مرداد',
		6  => 'شهریور',
		7  => 'مهر',
		8  => 'آبان',
		9  => 'آذر',
		10 => 'دی',
		11 => 'بهمن',
		12 => 'اسفند',
	);

	const WEEKDAYS = array(
		0 => 'یکشنبه',
		1 => 'دوشنبه',
		2 => 'سه‌شنبه',
		3 => 'چهارشنبه',
		4 => 'پنجشنبه',
		5 => 'جمعه',
		6 => 'شنبه',
	);

	/** @var array<string,string> */
	private static $digits = array( '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹' );

	/**
	 * Convert Gregorian to Jalali.
	 *
	 * @param int $gy Year.
	 * @param int $gm Month (1-12).
	 * @param int $gd Day.
	 * @return array{0:int,1:int,2:int} [jy, jm, jd]
	 */
	public static function gregorian_to_jalali( $gy, $gm, $gd ) {
		$g_d_m = array( 0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334 );

		if ( $gy > 1600 ) {
			$jy = 979;
			$gy -= 1600;
		} else {
			$jy = 0;
			$gy -= 621;
		}

		$gy2   = ( $gm > 2 ) ? $gy + 1 : $gy;
		$days  = ( 365 * $gy ) + (int) ( ( $gy2 + 3 ) / 4 ) - (int) ( ( $gy2 + 99 ) / 100 ) + (int) ( ( $gy2 + 399 ) / 400 ) - 80 + $gd + $g_d_m[ $gm - 1 ];
		$jy   += 33 * (int) ( $days / 12053 );
		$days %= 12053;
		$jy   += 4 * (int) ( $days / 1461 );
		$days %= 1461;

		if ( $days > 365 ) {
			$jy   += (int) ( ( $days - 1 ) / 365 );
			$days  = ( $days - 1 ) % 365;
		}

		if ( $days < 186 ) {
			$jm = 1 + (int) ( $days / 31 );
			$jd = 1 + ( $days % 31 );
		} else {
			$jm = 7 + (int) ( ( $days - 186 ) / 30 );
			$jd = 1 + ( ( $days - 186 ) % 30 );
		}

		return array( $jy, $jm, $jd );
	}

	/**
	 * Convert Jalali to Gregorian.
	 *
	 * @param int $jy Year.
	 * @param int $jm Month (1-12).
	 * @param int $jd Day.
	 * @return array{0:int,1:int,2:int} [gy, gm, gd]
	 */
	public static function jalali_to_gregorian( $jy, $jm, $jd ) {
		$jy = (int) $jy;
		$jm = (int) $jm;
		$jd = (int) $jd;

		if ( $jy > 979 ) {
			$gy  = 1600;
			$jy -= 979;
		} else {
			$gy = 621;
		}

		$days = ( 365 * $jy ) + (int) ( ( $jy + 3 ) / 4 ) - (int) ( ( $jy + 99 ) / 100 ) + (int) ( ( $jy + 399 ) / 400 ) + 80 + $jd + ( ( $jm < 7 ) ? ( ( $jm - 1 ) * 31 ) : ( ( ( $jm - 7 ) * 30 ) + 186 ) );
		$gy  += 400 * (int) ( $days / 146097 );
		$days %= 146097;

		if ( $days > 36524 ) {
			$days--;
			$gy  += 100 * (int) ( $days / 36524 );
			$days %= 36524;
			if ( $days >= 365 ) {
				$days++;
			}
		}

		$gy  += 4 * (int) ( $days / 1461 );
		$days %= 1461;

		if ( $days > 365 ) {
			$gy  += (int) ( ( $days - 1 ) / 365 );
			$days = ( $days - 1 ) % 365;
		}

		$gd   = $days + 1;
		$leap = ( ( 0 === ( $gy % 4 ) && 0 !== ( $gy % 100 ) ) || 0 === ( $gy % 400 ) );

		$gm             = 1;
		$month_lengths  = array( 31, ( $leap ? 29 : 28 ), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 );
		foreach ( $month_lengths as $len ) {
			if ( $gd <= $len ) {
				break;
			}
			$gd -= $len;
			$gm++;
		}

		return array( $gy, $gm, $gd );
	}

	/**
	 * Unix timestamp for a Jalali date/time in site-local time.
	 *
	 * @param int $jy Jalali year.
	 * @param int $jm Jalali month.
	 * @param int $jd Jalali day.
	 * @param int $h  Hour.
	 * @param int $i  Minute.
	 * @return int
	 */
	public static function timestamp( $jy, $jm, $jd, $h = 0, $i = 0 ) {
		list( $gy, $gm, $gd ) = self::jalali_to_gregorian( (int) $jy, (int) $jm, (int) $jd );
		return (int) mktime( (int) $h, (int) $i, 0, (int) $gm, (int) $gd, (int) $gy );
	}

	/**
	 * Local-time offset in seconds.
	 *
	 * @return int
	 */
	private static function offset() {
		return (int) round( (float) get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
	}

	/**
	 * Jalali parts for a timestamp.
	 *
	 * @param int|null $ts Timestamp, defaults to now.
	 * @return array{jy:int,jm:int,jd:int,h:int,i:int,weekday:int,ts:int}
	 */
	public static function parts( $ts = null ) {
		$ts    = null === $ts ? time() : (int) $ts;
		$local = $ts + self::offset();

		$gy  = (int) gmdate( 'Y', $local );
		$gm  = (int) gmdate( 'n', $local );
		$gd  = (int) gmdate( 'j', $local );
		$h   = (int) gmdate( 'G', $local );
		$min = (int) gmdate( 'i', $local );
		$w   = (int) gmdate( 'w', $local );

		list( $jy, $jm, $jd ) = self::gregorian_to_jalali( $gy, $gm, $gd );

		return array(
			'jy'      => $jy,
			'jm'      => $jm,
			'jd'      => $jd,
			'h'       => $h,
			'i'       => $min,
			'weekday' => $w,
			'ts'      => $ts,
		);
	}

	/**
	 * Format a timestamp in the Jalali calendar.
	 *
	 * Tokens: Y y m n d j F l H g i A
	 *
	 * @param int|null $ts       Timestamp.
	 * @param string   $format   Format string.
	 * @param bool     $fa_digits Use Persian digits.
	 * @return string
	 */
	public static function format( $ts = null, $format = 'Y/m/d', $fa_digits = true ) {
		$p = self::parts( $ts );

		$map = array(
			'Y' => (string) $p['jy'],
			'y' => substr( (string) $p['jy'], -2 ),
			'm' => str_pad( (string) $p['jm'], 2, '0', STR_PAD_LEFT ),
			'n' => (string) $p['jm'],
			'd' => str_pad( (string) $p['jd'], 2, '0', STR_PAD_LEFT ),
			'j' => (string) $p['jd'],
			'F' => self::MONTHS[ $p['jm'] ],
			'l' => self::WEEKDAYS[ $p['weekday'] ],
			'H' => str_pad( (string) $p['h'], 2, '0', STR_PAD_LEFT ),
			'g' => (string) ( ( 0 === $p['h'] % 12 ) ? 12 : $p['h'] % 12 ),
			'i' => str_pad( (string) $p['i'], 2, '0', STR_PAD_LEFT ),
			'A' => ( $p['h'] < 12 ) ? 'ق.ظ' : 'ب.ظ',
		);

		$out = '';
		$len = strlen( $format );
		for ( $i = 0; $i < $len; $i++ ) {
			$ch   = $format[ $i ];
			$out .= array_key_exists( $ch, $map ) ? $map[ $ch ] : $ch;
		}

		return $fa_digits ? self::to_fa( $out ) : $out;
	}

	/**
	 * Human date, e.g. "۲۲ آذر ۱۴۰۳".
	 *
	 * @param int|null $ts            Timestamp.
	 * @param bool     $with_weekday  Prefix weekday name.
	 * @return string
	 */
	public static function date( $ts = null, $with_weekday = false ) {
		return self::format( $ts, $with_weekday ? 'l، j F Y' : 'j F Y' );
	}

	/**
	 * Human time, e.g. "۱۸:۳۰".
	 *
	 * @param int|null $ts Timestamp.
	 * @return string
	 */
	public static function time( $ts = null ) {
		return self::format( $ts, 'H:i' );
	}

	/**
	 * Date and time together.
	 *
	 * @param int|null $ts Timestamp.
	 * @return string
	 */
	public static function date_time( $ts = null ) {
		return self::date( $ts ) . '، ساعت ' . self::time( $ts );
	}

	/**
	 * Translate ASCII digits to Persian digits.
	 *
	 * @param string $value Input.
	 * @return string
	 */
	public static function to_fa( $value ) {
		return strtr( (string) $value, self::$digits );
	}

	/**
	 * Translate Persian/Arabic-Indic digits to ASCII.
	 *
	 * @param string $value Input.
	 * @return string
	 */
	public static function to_en( $value ) {
		$value = strtr( (string) $value, array_flip( self::$digits ) );
		return str_replace( array( '٬', '٫', '،' ), array( '', '.', '' ), $value );
	}

	/**
	 * Days in a Jalali month (leap aware).
	 *
	 * @param int $jy Year.
	 * @param int $jm Month.
	 * @return int
	 */
	public static function days_in_month( $jy, $jm ) {
		$jm = (int) $jm;
		if ( $jm <= 6 ) {
			return 31;
		}
		if ( $jm <= 11 ) {
			return 30;
		}
		list( $gy, $gm, $gd ) = self::jalali_to_gregorian( (int) $jy + 1, 1, 1 );
		list( , , $last )     = self::gregorian_to_jalali( $gy, $gm, $gd - 1 );
		return ( 29 === $last ) ? 30 : 29;
	}

	/**
	 * Is a Jalali year a leap year?
	 *
	 * @param int $jy Year.
	 * @return bool
	 */
	public static function is_leap_year( $jy ) {
		return 366 === self::year_length( $jy );
	}

	/**
	 * Length of a Jalali year in days.
	 *
	 * @param int $jy Year.
	 * @return int
	 */
	public static function year_length( $jy ) {
		$start = self::timestamp( (int) $jy, 1, 1 );
		$next  = self::timestamp( (int) $jy + 1, 1, 1 );
		return (int) round( ( $next - $start ) / DAY_IN_SECONDS );
	}
}
