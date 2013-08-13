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
JHTML::addIncludePath(TT_HELPERS);
JLoader::register('StileroTTOauthClient', TT_LIBRARY.'oauth-client.php');
JLoader::register('StileroTTOauthUser', TT_LIBRARY.'oauth-user.php');
JLoader::register('StileroTTOauthCommunicator', TT_LIBRARY.'oauth-communicator.php');
JLoader::register('StileroTTOauthServer', TT_LIBRARY.'oauth-server.php');
JLoader::register('StileroTTTweets', TT_LIBRARY.'twitter-tweets.php');
JLoader::register('StileroTTJArticle', TT_LIBRARY.'stileroTTJArticle.php');

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


    var $k2Item;
    var $config;
    var $twitterFormToken;
    var $fullTweet;
    var $inBackend;
    var $errorOccured;
    var $CheckClass;
    const HTTP_STATUS_FOUND = '302';
    const HTTP_STATUS_OK = '200';
    const HTTP_STATUS_UNAUTHORIZED = '401'; //Returned from Twitter on wrong OAuth details
    const HTTP_STATUS_FORBIDDEN = '403'; //Returned from Twitter on duplicate tweets

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
        
        $this->errorOccured = FALSE;
//        
//        
//        
//        $this->config = array(
//            'shareLogTableName'     =>      '#__twittertweet_tweeted',
//            'twitterUsername'       =>      $this->params->def('username'),
//            'twitterPassword'       =>      $this->params->def('password'),
//            'oauthConsumerKey'      =>      $this->params->def('oauth_consumer_key'),
//            'oauthConsumerSecret'   =>      $this->params->def('oauth_consumer_secret'),
//            'oauthUserKey'          =>      $this->params->def('oauth_user_key'),
//            'oauthUserSecret'       =>      $this->params->def('oauth_user_secret'),
//            'twitterMaxHashTags'    =>      3,
//            'useOauth'              =>      $this->params->def('oauth_enabled'),
//            'twControllerClassName' =>      'stlTweetControllerClass',
//            'categoriesToShare'     =>      $this->params->def('section_id'),
//            'shareDelay'            =>      $this->params->def('delay'),
//            'articlesNewerThan'     =>      $this->params->def('items_newer_than'),
//            'useMetaAsHashTag'      =>      $this->params->def('metahash'),
//            'defaultHashTag'        =>      $this->params->def('default_hash'),
//            'postOnSave'            =>      $this->params->def('post_on_save')
//        ); 
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
    
//    protected function _initialChecks(){
//        if(!StileroTTServerRequirementHelper::hasCurlSupport()) throw new Exception('Server Missing Curl support.'); 
//        if(!StileroTTServerRequirementHelper::hasFileGetSupport()) throw new Exception('Server Missing Support for file_get_contents');
//        if(!$this->_Table->isTableFound()){
//            $this->_Table->createTable();
//        }
//        if($this->_Table->isTooEarly($this->_minutesBetweenPosts)) throw new Exception('Sharing too early');
//        $this->_Article->isPublished;
//        $this->_Article->isPublic;
//    }
    
    public function onContentAfterSave($context, &$article, $isNew) {
        $this->_initializePosting(true, $article);
        if($this->_ShareCheck->hasFullChecksPassed()){
            $message = StileroTTTweetHelper::buildTweet($this->_Article->getArticleObj(), 5, $this->_defaultTag);
            $this->_Tweet->update($message);
        }
        
        //$this->setupClasses();
        //$articleObject = $this->getArticleObjectFromJoomlaArticle($article);
        //$this->CheckClass->setArticleObject($articleObject);
        //$this->sendTweet();
        return;
    }

    public function onAfterContentSave( &$article, $isNew ) {
        $this->inBackend = true;
        $this->setupClasses();
        $articleObject = $this->getArticleObjectFromJoomlaArticle($article);
        $this->CheckClass->setArticleObject($articleObject);
        $this->sendTweet();
        return;
    }
    
    function onAfterDisplayContent( $article, & $params, $limitstart) {
        $this->inBackend = false;
        $this->setupClasses();
        $articleObject = $this->getArticleObjectFromJoomlaArticle($article);
        $this->CheckClass->setArticleObject($articleObject);
        $this->sendTweet();
        return;
    }

    function onContentAfterDisplay( $article, & $params, $limitstart) {
        $this->inBackend = false;
        $this->setupClasses();
        $articleObject = $this->getArticleObjectFromJoomlaArticle($article);
        $this->CheckClass->setArticleObject($articleObject);
        $this->sendTweet();
        return;
    }
        
    public function setupClasses() {
        //Load the classes
        JLoader::register('stlTweetControllerClass', dirname(__FILE__).DS.'twittertweetclasses'.DS.'stlTweetControllerClass.php');
        JLoader::register('stlShareControllerClass', dirname(__FILE__).DS.'twittertweetclasses'.DS.'stlShareControllerClass.php');
        JLoader::register('simpleTwitterClass', dirname(__FILE__).DS.'twittertweetclasses'.DS.'simpleTwitterClass.php');
        JLoader::register('stileroOauthTweetClass', dirname(__FILE__).DS.'twittertweetclasses'.DS.'stileroOauthTweetClass.php');
        $this->CheckClass = new $this->config['twControllerClassName']( 
            array(
                'twitterUsername'       =>      $this->config['twitterUsername'],
                'twitterPassword'       =>      $this->config['twitterPassword'],
                'useOauth'              =>      $this->config['useOauth'],
                'oauthConsumerKey'      =>      $this->config['oauthConsumerKey'],
                'oauthConsumerSecret'   =>      $this->config['oauthConsumerSecret'],
                'oauthUserKey'          =>      $this->config['oauthUserKey'],
                'oauthUserSecret'       =>      $this->config['oauthUserSecret'],
                'shareLogTableName'     =>      $this->config['shareLogTableName'],
                'pluginLangPrefix'      =>      'PLG_SYSTEM_TWITTERTWEET_',
                'categoriesToShare'     =>      $this->config['categoriesToShare'],
                'shareDelay'            =>      $this->config['shareDelay'],
                'articlesNewerThan'     =>      $this->config['articlesNewerThan'],
                'useMetaAsHashTag'      =>      $this->config['useMetaAsHashTag'],
                'defaultHashTag'        =>      $this->config['defaultHashTag']
            )
        );
    }
    
    public function sendTweet() {
        if( !$this->isInitialChecksOK() ) {
            $this->displayMessage(JText::_($this->CheckClass->error['message']) , $this->CheckClass->error['type']);
            return;
        }
            if ($this->CheckClass->tweet() ) {
            $tweet = urldecode($this->CheckClass->fullTweet);
            $this->displayMessage(JText::_('Tweeted: ').$tweet);
            return;
        }else{
            $this->displayMessage(JText::_($this->CheckClass->error['message']) , $this->CheckClass->error['type']);
            return;
        }
    }
               
    public function getArticleObjectFromJoomlaArticle($joomlaArticle) {
        $articleObject = new stdClass();
        $articleObject->id = $joomlaArticle->id;
        $articleObject->language= $joomlaArticle->language;
        $articleObject->link = $joomlaArticle->alias;
        $articleObject->full_url = $this->getFullURL($joomlaArticle->id);
        $articleObject->tags = $this->getArticleTagsArray($joomlaArticle->metakey);
        $articleObject->title = $joomlaArticle->title;
        $articleObject->catid = $joomlaArticle->catid;
        $articleObject->access = $joomlaArticle->access;
        $articleObject->publish_up = $joomlaArticle->publish_up;
        $articleObject->published = $joomlaArticle->state; 
        return $articleObject;
    }

    private function getArticleTagsArray($commaSpearatedMetaKeys) {
        if($commaSpearatedMetaKeys == ""){
            return;
        }
       $metaKeyArray = explode(",", $commaSpearatedMetaKeys);
       foreach ($metaKeyArray as $key => $value) {
           $tagsArray[] = trim(str_replace(" ", "", $value));
       }
       return $tagsArray;
    }

    public function displayMessage($msg, $messageType = "") {
        $isSetToDisplayMessages = ($this->params->def('pingmessages')==0)?false:true;
        if( ! $isSetToDisplayMessages || ! $this->inBackend ){
            return;
        }else{
            JFactory::getApplication()->enqueueMessage( $msg, $messageType);
        }
    }

    private function doInitialChecks() {
        $this->CheckClass->isServerSupportingRequiredFunctions();
        $this->CheckClass->isServerSafeModeDisabled();
        if ( $this->config['useOauth'] ){
            $this->CheckClass->isOauthDetailsEntered();
        }else{
            $this->CheckClass->isLoginDetailsEntered();
        }
        $this->CheckClass->isArticleObjectIncluded();
        $this->CheckClass->isItemActive();
        $this->CheckClass->isItemPublished();
        $this->CheckClass->isItemNewEnough();
        $this->CheckClass->isItemPublic();
        $this->CheckClass->isCategoryToShare();
        $this->CheckClass->prepareTables();
        $allwaysPostOnSave = $this->config['postOnSave'];
        if(!$allwaysPostOnSave || !$this->inBackend){
            $this->CheckClass->isSharingToEarly();
            $this->CheckClass->isItemAlreadyShared();
        }
//        if ( !$this->config['postOnSave'] && !$this->inBackend){
//        }
        return $this->CheckClass->error;
    }

    public function isInitialChecksOK() {
        $errorMessage = $this->doInitialChecks();
        if ( $errorMessage != FALSE ) {
            return FALSE;
        }
        return TRUE;
    }

    public function getFullURL($articleID) {
        $urlQuery = "?option=com_content&view=article&id=".$articleID;
        $fullURL = JURI::root()."index.php".$urlQuery;
        return $fullURL;
    }
}

//End Plugin Class

