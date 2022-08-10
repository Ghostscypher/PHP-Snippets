<?php

if(!function_exists('dd')){
    function dd(... $args){
        dump($args);
        exit;
    }
}

/**
 * @link https://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
 */
function strStartsWith($haystack, $needle)
{
    $length = strlen($needle);

    return substr($haystack, 0, $length) === $needle;
}

/**
 * @link https://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
 */
function strEndsWith($haystack, $needle)
{
    $length = strlen($needle);

    if (! $length) {
        return true;
    }

    return substr($haystack, -$length) === $needle;
}

/**
 * @link https://stackoverflow.com/questions/2034687/regex-get-string-value-between-two-characters
 */
function get_string_between($string, $start, $end)
{
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) {
        return '';
    }
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;

    return substr($string, $ini, $len);
}

function get_profile_field_data2(){
    return '';
}

/**
 * @see https://stackoverflow.com/questions/19099922/array-shift-but-preserve-keys
 */
// returns value
function array_shift_assoc( &$arr ){
$val = reset( $arr );
unset( $arr[ key( $arr ) ] );
return $val; 
}

/**
 * @see https://stackoverflow.com/questions/19099922/array-shift-but-preserve-keys
 */
// returns [ key, value ]
function array_shift_assoc_kv( &$arr ){
$val = reset( $arr );
$key = key( $arr );
$ret = array( $key => $val );
unset( $arr[ $key ] );
return $ret; 
}