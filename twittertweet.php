<?php
/**
 * Description of twittertweet
 *
 * @version 2.10
 * @author danieleliasson Stilero AB - http://www.stilero.com
 * @copyright 2011-dec-31 Stilero AB
 * @license	GPLv2
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
define('TT_LIBRARY', dirname(__FILE__).DS.'library'.DS);
define('TT_HELPERS', dirname(__FILE__).DS.'helpers'.DS);

// Import library dependencies
jimport('joomla.plugin.plugin');
//Include Libraries
JLoader::register('StileroTTOauthClient', TT_LIBRARY.'oauth-client.php');
JLoader::register('StileroTTOauthUser', TT_LIBRARY.'oauth-user.php');
JLoader::register('StileroTTOauthCommunicator', TT_LIBRARY.'oauth-communicator.php');
JLoader::register('StileroTTOauthServer', TT_LIBRARY.'oauth-server.php');
JLoader::register('StileroTTShareTable', TT_LIBRARY.'stileroTTShareTable.php');
JLoader::register('StileroTTShareCheck', TT_LIBRARY.'stileroTTShareCheck.php');
JLoader::register('StileroTTTweets', TT_LIBRARY.'twitter-tweets.php');
JLoader::register('StileroTTJArticle', TT_LIBRARY.'stileroTTJArticle.php');
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
JLoader::register('StileroTTJVersionHelper', TT_HELPERS.'stileroTTJVersionHelper.php');
JLoader::register('StileroTTSH404SEFUrlHelper', TT_HELPERS.'stileroTTSH404SEFUrlHelper.php');
JLoader::register('StileroTTServerRequirementHelper', TT_HELPERS.'stileroTTServerRequirementHelper.php');
JLoader::register('StileroTTTagsHelper', TT_HELPERS.'stileroTTTagsHelper.php');
JLoader::register('StileroTTTinyUrlHelper', TT_HELPERS.'stileroTTTinyUrlHelper.php');
JLoader::register('StileroTTTweetHelper', TT_HELPERS.'stileroTTTweetHelper.php');
JLoader::register('StileroTTUrlHelper', TT_HELPERS.'stileroTTUrlHelper.php');

class plgSystemTwittertweet extends JPlugin {
    protected $_OauthClient;
    protected $_OauthUser;
    protected $_Tweet;
    protected $_Article;
    protected $_ShareCheck;
    protected $_Table;
    protected $_minutesBetweenPosts;
    protected $_dateLimit;
    protected $_catList;
    protected $_allwaysPostOnSave;
    protected $_defaultTag;
    
    const TABLE_NAME = '#__twittertweet_tweeted';

    public function plgSystemTwittertweet( &$subject, $config ) {
        parent::__construct( $subject, $config );
        $language = JFactory::getLanguage();
        $language->load('plg_system_twittertweet', JPATH_ADMINISTRATOR, 'en-GB', true);
        $language->load('plg_system_twittertweet', JPATH_ADMINISTRATOR, null, true);
        $this->_minutesBetweenPosts = $this->params->def('delay');
        $this->_dateLimit = $this->params->def('items_newer_than');
        $this->_catList = $this->params->def('section_id');
        $this->_allwaysPostOnSave = $this->params->def('post_on_save');
        $this->_defaultTag = $this->params->def('default_hash');
    }
    
    /**
     * Initializes all oauth classes for the plugin
     */
    protected function _initializeClasses(){
        $oauthClientKey = $this->params->def('oauth_consumer_key');
        $oauthClientSecret = $this->params->def('oauth_consumer_secret');
        $accessToken = $this->params->def('oauth_user_key');
        $tokenSecret = $this->params->def('oauth_user_secret');
        $this->_OauthClient = new StileroTTOauthClient($oauthClientKey, $oauthClientSecret);
        $this->_OauthUser = new StileroTTOauthUser($accessToken, $tokenSecret);
        $this->_Tweet = new StileroTTTweets($this->_OauthClient, $this->_OauthUser);
        $this->_Table = new StileroTTShareTable(self::TABLE_NAME);
    }
    
    /**
     * Initializes all before posting
     * @param boolean $inBackend True if posted from backend
     * @param Object $article Joomla article Object
     */
    protected function _initializePosting($inBackend, $article){
        $this->_initializeClasses();
        $this->_Article = new StileroTTJArticle($article);
        $this->_ShareCheck = new StileroTTShareCheck($this->_Article->getArticleObj(), $this->_Table, $this->_minutesBetweenPosts, $this->_dateLimit, $this->_catList, $this->_allwaysPostOnSave, $inBackend);
        
    }
    
    /**
     * Prepares and sends a tweet. Displays messages after tweeting.
     * @param Object $article Joomla article Object
     */
    protected function _sendTweet($article){
        $app = JFactory::getApplication();
        $this->_initializePosting(true, $article);
        if($this->_ShareCheck->hasFullChecksPassed()){
            $message = StileroTTTweetHelper::buildTweet($this->_Article->getArticleObj(), 5, $this->_defaultTag);
            $response = $this->_Tweet->update($message);
            $TwitterResponse = new StileroTTTwitterResponse($response);
            if($TwitterResponse->hasID()){
                $app->enqueueMessage('Tweeted Successfully: '.$message);
            }else if($TwitterResponse->hasError()){
                $message = 'TwitterError: ('.$TwitterResponse->errorCode.') '.$TwitterResponse->errorMsg;
                $app->enqueueMessage($message, 'error');
            }else{
                $app->enqueueMessage('Unknown error', 'error');
            }
        }else{
            $messageType = 'message';
            $message = 'Failed Checks';
        }
    }
    
 
    public function onContentAfterSave($context, &$article, $isNew) {
        $this->_sendTweet($article);
        return;
    }


    function onContentAfterDisplay( $article, & $params, $limitstart) {
        $this->_sendTweet($article);
        return;
    }
        


//    private function doInitialChecks() {
//        $this->CheckClass->isServerSupportingRequiredFunctions();
//        $this->CheckClass->isServerSafeModeDisabled();
//        if ( $this->config['useOauth'] ){
//            $this->CheckClass->isOauthDetailsEntered();
//        }else{
//            $this->CheckClass->isLoginDetailsEntered();
//        }
//        $this->CheckClass->isArticleObjectIncluded();
//        $this->CheckClass->isItemActive();
//        $this->CheckClass->isItemPublished();
//        $this->CheckClass->isItemNewEnough();
//        $this->CheckClass->isItemPublic();
//        $this->CheckClass->isCategoryToShare();
//        $this->CheckClass->prepareTables();
//        $allwaysPostOnSave = $this->config['postOnSave'];
//        if(!$allwaysPostOnSave || !$this->inBackend){
//            $this->CheckClass->isSharingToEarly();
//            $this->CheckClass->isItemAlreadyShared();
//        }
////        if ( !$this->config['postOnSave'] && !$this->inBackend){
////        }
//        return $this->CheckClass->error;
//    }


}

//End Plugin Class

