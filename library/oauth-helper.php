<?php
/**
 * Class OAuth Helper
 * Contains general static methods that are mainly used by the server calls.
 *
 * @version  1.0
 * @package Stilero
 * @subpackage Class Twitter
 * @author Daniel Eliasson (joomla@stilero.com)
 * @copyright  (C) 2013-aug-02 Stilero Webdesign (www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */
class OauthHelper {
    
    /**
     * Generates an unique token that can be used to identify the request.
     * @param int $length The length of the token
     * @return string
     */
    public static function nonce($length=12){
        $characters = array_merge(range(0,9), range('A','Z'), range('a','z'));
        $length = $length > count($characters) ? count($characters) : $length;
        shuffle($characters);
        $prefix = microtime();
        $nonce = md5(substr($prefix . implode('', $characters), 0, $length));
        return $nonce;
    }
    
    /**
     * Generates and returns a unique timestamp based on the current time.
     * @return int
     */
    public static function timestamp(){
        $timestamp = time();
        return $timestamp;
    }
    
    /**
     * Encodes data in an array and returns an encoded string
     * @param Array/string $data
     * @return string
     */
    public static function safeEncode($data) {
        if (is_array($data)) {
            return array_map('self::safeEncode', $data);
        } else if (is_scalar($data)) {
            return str_ireplace( array('+', '%7E'), array(' ', '~'), rawurlencode($data) );
        } else {
            return '';
        }
    }
    
    /**
     * Decodes data and returns an decoded string
     * @param Array/string $data
     * @return string
     */
    public static function safeDecode($data) {
        if (is_array($data)) {
            return array_map('self::safeDecode', $data);
        } else if (is_scalar($data)) {
            return rawurldecode($data);
        } else {
            return '';
        }
    }
    
    /**
     * Cleans out and sanitizes an url
     * @param string $url
     * @return string sanitized url
     */
    public static function sanitizeURL($url){
        $parts = parse_url($url);
        $port = isset($parts['port']) ? $parts['port'] : '';
        $scheme = $parts['scheme'];
        $host = $parts['host'];
        $path = isset($parts['path']) ? $parts['path'] : '';
        $port or $port = ($scheme == 'https') ? '443' : '80';
        if(($scheme == 'https' && $port != '443') || ($scheme == 'http' && $port != '80')) {
            $host = "$host:$port";
        }
        $sanitizedURL = strtolower("$scheme://$host").$path;
        return $sanitizedURL;
    }
}
?>
