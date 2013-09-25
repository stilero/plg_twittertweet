<?php
/**
 * Tiny URL Helper class
 *
 * @version  1.0
 * @package Stilero
 * @subpackage plg_twittertweet
 * @author Daniel Eliasson <daniel at stilero.com>
 * @copyright  (C) 2013-aug-12 Stilero Webdesign (http://www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

class StileroTTTinyUrlHelper{
    
    const API_URL_TINY_URL = 'http://tinyurl.com/api-create.php?url=';
    const HTTP_STATUS_OK = 200;
    const HTTP_STATUS_FOUND = 302;
    
    /**
     * Shortens and returns an url using native PHP file_get_contents
     * @param string $fullURL Full url to shorten
     * @return string Shortened url
     */
    public static function tinyUrl($fullURL){
        $encodedFullURL = urlencode($fullURL);
        $apiCallForTinyURL = self::API_URL_TINY_URL.$encodedFullURL;
        $tinyURL = @file_get_contents($apiCallForTinyURL);
        if($tinyURL == ""){
            $tinyURL = self::tinyUrlUsinCurl($fullURL);
        }
        $tinyUrlLength = strlen($tinyURL);
        $fullUrlLength = strlen($fullURL);
        if( ($fullUrlLength < $tinyUrlLength ) || $tinyURL == "" ){
            $tinyURL = $fullURL;
        }
        return $tinyURL;
    }
    
    /**
     * Shortens an url using CURL and TinyUrl
     * @param string $fullURL Full URL to shorten
     * @return string/boolean TinyUrl on success, false on fail
     */
    public static function tinyUrlUsinCurl($fullURL){
        //$apiCallForTinyURL = self::API_URL_TINY_URL . $fullURL;
        $params = array('url' => $fullURL );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::API_URL_TINY_URL); 	 //The auth page to visit
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params)); //Build a query from the postarray
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        $response = curl_exec ($ch);							//Go get the page and put the result in a variable
        $results = curl_getinfo($ch);
        curl_close ($ch);
        if($results['http_code']==self::HTTP_STATUS_OK || $results['http_code']==self::HTTP_STATUS_FOUND){
            return $response;
        }
        return false;
    }
}
