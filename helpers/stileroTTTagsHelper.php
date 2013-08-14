<?php
/**
 * Tags helper class
 *
 * @version  1.0
 * @package Stilero
 * @subpackage plg_twittertweet
 * @author danieleliasson
 * @copyright  (C) 2013-aug-12 Expression company is undefined on line 9, column 30 in Templates/Joomla/name.php.
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

class StileroTTTagsHelper{
    
    /**
     * Takes a string with comma separated tags and returns an array of tags
     * @param string $commaSepStringOfTags Tags separated by comma, for example "tag1, tag2".
     * @return array tags
     */
    public static function tags($commaSepStringOfTags){
        if($commaSepStringOfTags == ""){
            return;
        }
       $tags = explode(",", $commaSepStringOfTags);
       foreach ($tags as $value) {
           $trimmedTags[] = trim(str_replace(" ", "", $value));
       }
       return $trimmedTags;
    }
    
    /**
     * Returns a string of hash tags
     * @param array $tags Tags in array
     * @param int $numTags number of tags to use
     * @param string $defaultTag A default tag to use
     * @return string hashtag string
     */
    public static function hashTagString($tags, $numTags = 5, $defaultTag=""){
        $hashTagString = '';
        $hasDefaultTag = $defaultTag != '' ? TRUE : FALSE;
        if($hasDefaultTag){
            $sanitizedDefaultTag = str_replace('#', '', $defaultTag);
            if(count($tags) == 0){
                $tags[]=$sanitizedDefaultTag;
            }else{
                array_unshift($tags, $sanitizedDefaultTag);
            }
        }
        if(is_array($tags) || !empty($tags)){
            $i = 0;
            foreach ($tags as $value) {
                if($i++ < $numTags ){
                    $trimmedTags[] = trim($value);
                }
            }
            $hashTagString = " #".implode(" #", $trimmedTags);
        }
              
        return $hashTagString;
    }
}
