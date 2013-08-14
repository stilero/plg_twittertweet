<?php
/**
 * Tweet Helper class, builds tweets ready for take off.
 *
 * @version  1.0
 * @package Stilero
 * @subpackage plg_twittertweet
 * @author Daniel Eliasson <daniel at stilero.com>
 * @copyright  (C) 2013-aug-13 Stilero Webdesign (http://www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

class StileroTTTweetHelper{
    
    /**
     * Builds and returns a tweet by combining title, hashtags and url
     * @param Object $Article Article Object returned from JArticle Class
     * @param int $numTags Number of tags to use
     * @param string $defaultTag A default tag to use
     * @return string Full Tweet
     */
    public static function buildTweet($Article, $numTags=5, $defaultTag=''){
        $title = $Article->title;
        $hashtagString = StileroTTTagsHelper::hashTagString($Article->tags, $numTags, $defaultTag);
        $articleSlug = StileroTTArticleHelper::slugFromId($Article->id);
        $categorySlug = StileroTTCategoryHelper::slugFromId($Article->catid);
        if(StileroTTExtensionHelper::isInstalled('com_sh404sef')){
            $url = StileroTTSH404SEFUrlHelper::sefURL($articleSlug, $categorySlug);
        }else{
            $url = StileroTTUrlHelper::sefURL($articleSlug, $categorySlug);
        }
        $tinyUrl = StileroTTTinyUrlHelper::tinyUrl($url);
        $tweet = $title.' '.$tinyUrl.' '.$hashtagString;
        return $tweet;
    }
}
