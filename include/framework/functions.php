<?php
/**
 * Copyright (C) 2009-2012 Peter Lind
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/gpl.html>.
 *
 * PHP version 5
 *
 * @package   Framework
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2009-2012 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * replaces output names from date() with Danish names of same length
 *
 * @param string $string String with names to replace
 *
 * @return string
 */
function danishDayNames($string){
    return str_ireplace(
        array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'),
        array('Mandag', 'Tirsdag', 'Onsdag', 'Torsdag', 'Fredag', 'Lørdag', 'Søndag', 'Man', 'Tir', 'Ons', 'Tor', 'Fre', 'Lør', 'Søn'),
        $string
    );
}

function danishMonthNames($string){
    return str_ireplace(
        ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december', 'oct'],
        ['Januar', 'Februar', 'Marts', 'April', 'Maj', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'December', 'Okt'],
        $string
    );
}

function getWeekDay($day, $english = false) {
    if ($english) {
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    } else {
        $days = ['Søndag', 'Mandag', 'Tirsdag', 'Onsdag', 'Torsdag', 'Fredag', 'Lørdag', 'Søndag'];
    }
    return $days[$day];
}

function getWeekDayShort($day, $english = false) {
    return substr(getWeekDay($day, $english), 0, 3);
}

function getMonthText($month, $english = false) {
    if ($english) {
        $months = ['December', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    } else {
        $months = ['December', 'Januar', 'Februar', 'Marts', 'April', 'Maj', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'December'];
    }
    return $months[$month];
}

function getMonthTextShort($month, $english = false) {
    return substr(getMonthText($month, $english), 0, 3);
}

/**
 * returns a descriptive phrase of
 * a given time period, i.e. months, day, etc
 *
 * @param int $seconds Seconds to convert
 *
 * @return string
 */
function makeSensibleTimeperiod($seconds)
{
    $months  = floor($seconds / (3600 * 24 * 30));
    $rest    = $seconds - (3600 * 24 * 30 * $months);
    $days    = floor($rest / (3600 * 24));
    $rest    = $rest - (3600 * 24 * $days);
    $hours   = floor($rest / 3600);
    $rest    = $rest - (3600 * $hours);
    $minutes = floor($rest / 60);
    $rest    = $rest - (60 * $minutes);

    $return = '';
    if ($months) {
        $return .= $months . " måned" . ($months != 1 ? "er" : "") . ", ";
    }

    if ($days || $return) {
        $return .= $days . " dag" . ($days != 1 ? "e" : "") . ", ";
    }

    if ($hours || $return) {
        $return .= $hours . " time" . ($hours != 1 ? "r" : "") . ", ";
    }

    if ($minutes || $return) {
        $return .= $minutes . " minut" . ($minutes != 1 ? "ter" : "") . ", ";
    }

    $return .= $rest . " sekund" . ($rest != 1 ? "er" : "") . ".";
    return $return;
}

/**
 * wrapper function for htmlspecialchars
 *
 * @param string $text   Text to escape
 * @param bool   $quotes Leave quotes or not
 *
 * @return string
 */
function e($text, $quotes = null)
{
    return htmlspecialchars($text, $quotes ? $quotes : ENT_QUOTES);
}

/**
 * generate an EAN13 number from an input source
 *
 * @param string|int $input
 *
 * @return string
 */
function numberToEAN13($input) {
    $int = intval($input);
    if (strlen($int) < 12) {
        $treated = str_pad($int, 12, '0', STR_PAD_LEFT);
    } elseif (strlen($int) > 12) {
        $treated = substr($int, 0, 12);
    } else {
        $treated = $int;
    }

    $odd = $even = 0;
    for ($i = 1; $i < 12; ++$i) {
        $odd += 3 * $treated[$i++];
        $even += empty($treated[$i]) ? 0 : $treated[$i];
    }

    $control = (10 - (($odd + $even) % 10));
    return $treated . ($control < 10 ? $control : 0);
}

/**
 * generate an EAN13 number from an input source
 *
 * @param string|int $input
 *
 * @return string
 */
function numberToEAN8($input) {
    $int = intval($input);
    if (strlen($int) < 7) {
        $treated = str_pad($int, 7, '0', STR_PAD_LEFT);
    } elseif (strlen($int) > 7) {
        $treated = substr($int, 0, 7);
    } else {
        $treated = (string) $int;
    }

    $odd = $even = 0;
    for ($i = 0; $i < 7; ++$i) {
        $odd  += 3 * $treated[$i++];
        $even += empty($treated[$i]) ? 0 : $treated[$i];
    }

    $control = (10 - (($odd + $even) % 10));
    return $treated . ($control < 10 ? $control : 0);
}

/**
 * converts an ean13 number to it's content or fails
 *
 * @param string $input
 *
 * @return int
 */
function EAN8ToNumber($input) {
    if (!is_string($input)) {
        return false;
    }

    if (strlen($input) == 7) {
        $sub = substr($input, 0, 7);

        if (substr(numberToEAN8($sub), 1) != $input) {
            return false;
        }

        if (substr($sub, 0, 2) == date('y')) {
            return intval(substr($sub, 2));
        }

        return intval($sub);
    }

    $sub = substr($input, 0, 7);

    if (numberToEAN8($sub) !== $input) {
        return false;
    }

    if (substr($sub, 0, 2) == date('y')) {
        return intval(substr($sub, 2));
    }

    return intval($sub);
}

/**
 * converts an ean13 number to it's content or fails
 *
 * @param string $input
 *
 * @return int
 */
function EAN13ToNumber($input) {
    if (!is_string($input)) {
        return false;
    }

    if (strlen($input) == 12) {
        $sub = substr($input, 0, 11);
        if (substr(numberToEAN13($sub), 1) != $input) {
            return false;
        }
        return intval($sub);
    }

    $sub = substr($input, 0, 12);
    if (numberToEAN13($sub) !== $input) {
        return false;
    }

    return intval($sub);
}

/**
 * tries to convert an input string using either EAN13 or EAN8
 *
 * @param string $input Input number to convert
 *
 * @return false|int
 */
function EANToNumber($input)
{
    if (strlen($input) == 13 && EAN13ToNumber($input)) {
        return EAN13ToNumber($input);
    }

    if (strlen($input) == 8 && EAN8ToNumber($input)) {
        return EAN8ToNumber($input);
    }

    return false;
}

/**
 * adds a timestamp to a filename, if the file exists
 *
 * @param string $filename Filename to check
 *
 * @access public
 * @return string
 */
function staticLink($filename)
{
    if (!($stat = stat(PUBLIC_PATH . $filename))) {
        return $filename;
    }

    return $filename . (mb_strpos($filename, '?') !== false ? '&' : '?') . 'random=' . $stat['mtime'];
}

if (file_exists(LIB_FOLDER . 'markdown/markdown.php')) {
    require LIB_FOLDER . 'markdown/markdown.php';
}

if (!function_exists('imagerotate')) {
    function imagerotate($imgSrc, $angle)
    { 
        // ensuring we got really RightAngle (if not we choose the closest one) 
        $angle = min( ( (int)(($angle+45) / 90) * 90), 270 ); 

        // no need to fight 
        if( $angle == 0 ) 
            return( $imgSrc ); 

        // dimenstion of source image 
        $srcX = imagesx( $imgSrc ); 
        $srcY = imagesy( $imgSrc ); 

        switch( $angle ) 
            { 
            case 90: 
                $imgDest = imagecreatetruecolor( $srcY, $srcX ); 
                for( $x=0; $x<$srcX; $x++ ) 
                    for( $y=0; $y<$srcY; $y++ ) 
                        imagecopy($imgDest, $imgSrc, $srcY-$y-1, $x, $x, $y, 1, 1); 
                break; 

            case 180: 
                $imgDest = ImageFlip( $imgSrc, IMAGE_FLIP_BOTH ); 
                break; 

            case 270: 
                $imgDest = imagecreatetruecolor( $srcY, $srcX ); 
                for( $x=0; $x<$srcX; $x++ ) 
                    for( $y=0; $y<$srcY; $y++ ) 
                        imagecopy($imgDest, $imgSrc, $y, $srcX-$x-1, $x, $y, 1, 1); 
                break; 
            } 

        return( $imgDest ); 
    }
}

/**
 * translate an activity type to a readable
 * alternative
 *
 * returns the input if it cant be translated
 *
 * @param string $type Type to translate
 *
 * @return string
 */
function tt($type)
{
    if (!is_string($type)) {
        return '';
    }

    switch (strtolower($type)) {
    case 'rolle':
        return 'Rollespil';

    case 'braet':
        return 'Brætspil';

    case 'live':
        return 'Live';

    case 'figur':
        return 'Figurspil';

    case 'workshop':
        return 'Workshop';

    case 'ottoviteter':
        return 'Ottovitet';

    case 'magic':
        return 'Magic: The Gathering';

    case 'junior':
        return 'Fastaval Junior';

    default:
        return $type;
    }
}

/**
 * returns the latter part of a url,
 * after the domain
 *
 * @param string $url URL to get postfix of
 *
 * @return string
 */
function get_url_postfix($url)
{
    return preg_replace('#^https?://[^/]+/#i', '', $url);
}

/**
 * returns the domain part of a url
 *
 * @param string $url URL to get postfix of
 *
 * @return string
 */
function get_url_domain($url)
{
    return preg_replace('#^(https?://)?([^/]+)/.*#i', '$1', $url);
}

/**
 * generates a semirandom string of the given length
 *
 * @param int $length Length of string wanted
 *
 * @return string
 */
function makeRandomString($length)
{
    $string = '';
    $strlen = 0;

    while ($strlen < $length) {

        $rand = mt_rand(1, 62);

        if ($rand <= 10) {
            $string .= chr($rand + 0x2f);

        } elseif ($rand <= 36) {
            $string .= chr($rand + 0x40 - 10);

        } else {
            $string .= chr($rand + 0x60 - 36);
        }

        $strlen++;
    }

    return $string;
}
