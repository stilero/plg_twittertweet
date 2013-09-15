<?php
/**
 * TwitterTweet helper
 * Convenient class for importing dependencies
 *
 * @version  1.0
 * @package Stilero
 * @subpackage plg_twittertweet
 * @author Daniel Eliasson <daniel at stilero.com>
 * @copyright  (C) 2013-aug-15 Stilero Webdesign (http://www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

define('TT_LIBRARY', dirname(__FILE__).DS.'library'.DS);
define('TT_HELPERS', dirname(__FILE__).DS.'helpers'.DS);


class StileroTTHelper{
    
    /**
     * Imports all classes used to the autoloader of Joomla
     */
    public static function importDependencies(){
        //Include Libraries
        JLoader::register('StileroTTOauthClient', TT_LIBRARY.'oauth-client.php');
        JLoader::register('StileroTTOauthUser', TT_LIBRARY.'oauth-user.php');
        JLoader::register('StileroTTOauthCommunicator', TT_LIBRARY.'oauth-communicator.php');
        JLoader::register('StileroTTOauthServer', TT_LIBRARY.'oauth-server.php');
        JLoader::register('StileroTTShareTable', TT_LIBRARY.'stileroTTShareTable.php');
        JLoader::register('StileroTTShareCheck', TT_LIBRARY.'stileroTTShareCheck.php');
        JLoader::register('StileroTTTweets', TT_LIBRARY.'twitter-tweets.php');
        JLoader::register('StileroTTJArticle', TT_LIBRARY.'stileroTTJArticle.php');
        JLoader::register('StileroTTK2Article', TT_LIBRARY.'stileroTTJArticle.php');
        JLoader::register('StileroTTTwitterResponse', TT_LIBRARY.'stileroTTTwitterResponse.php');
        JLoader::register('OauthHelper', TT_LIBRARY.'oauth-helper.php');
        JLoader::register('OauthSignature', TT_LIBRARY.'oauth-signature.php');
        JLoader::register('OauthHeader', TT_LIBRARY.'oauth-header.php');
        //Include Helpers
        JLoader::register('StileroTTArticleHelper', TT_HELPERS.'stileroTTArticleHelper.php');
        JLoader::register('StileroTTCategoryHelper', TT_HELPERS.'stileroTTCategoryHelper.php');
        JLoader::register('StileroTTDBTableHelper', TT_HELPERS.'stileroTTDBTableHelper.php');
        JLoader::register('StileroTTExtensionHelper', TT_HELPERS.'stileroTTExtensionHelper.php');
        JLoader::register('StileroTTJArticleImageHelper', TT_HELPERS.'stileroTTJArticleImageHelper.php');
        JLoader::register('StileroTTK2ImageHelper', TT_HELPERS.'stileroTTJArticleImageHelper.php');
        JLoader::register('StileroTTJVersionHelper', TT_HELPERS.'stileroTTJVersionHelper.php');
        JLoader::register('StileroTTSH404SEFUrlHelper', TT_HELPERS.'stileroTTSH404SEFUrlHelper.php');
        JLoader::register('StileroTTServerRequirementHelper', TT_HELPERS.'stileroTTServerRequirementHelper.php');
        JLoader::register('StileroTTTagsHelper', TT_HELPERS.'stileroTTTagsHelper.php');
        JLoader::register('StileroTTTinyUrlHelper', TT_HELPERS.'stileroTTTinyUrlHelper.php');
        JLoader::register('StileroTTTweetHelper', TT_HELPERS.'stileroTTTweetHelper.php');
        JLoader::register('StileroTTUrlHelper', TT_HELPERS.'stileroTTUrlHelper.php');
        JLoader::register('StileroTTK2UrlHelper', TT_HELPERS.'stileroTTUrlHelper.php');
        JLoader::register('StileroTTMessageHelper', TT_HELPERS.'stileroTTMessageHelper.php');
        JLoader::register('StileroTTContextHelper', TT_HELPERS.'stileroTTContextHelper.php');
    }
}
