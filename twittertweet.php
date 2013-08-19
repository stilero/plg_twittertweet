<?php
/**
 * Joomla Plugin TwitterTweet. Updates your twitter status.
 *
 * @version 2.12
 * @author danieleliasson Stilero AB - http://www.stilero.com
 * @copyright 2011-dec-31 Stilero AB
 * @license	GPLv2
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Import library dependencies
jimport('joomla.plugin.plugin');
JLoader::register('StileroTTHelper', dirname(__FILE__).DS.'helper.php');

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
    protected $_isBackend;
    protected $_useMetaAsHash;
    
    const TABLE_NAME = '#__twittertweet_tweeted';
    const LANG_PREFIX = 'PLG_SYSTEM_TWITTERTWEET_';

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
        $this->_useMetaAsHash = $this->params->def('metahash');
    }
    
    /**
     * Initializes all oauth classes for the plugin
     */
    protected function _initializeClasses(){
        $oauthClientKey = $this->params->def('oauth_consumer_key');
        $oauthClientSecret = $this->params->def('oauth_consumer_secret');
        $accessToken = $this->params->def('oauth_user_key');
        $tokenSecret = $this->params->def('oauth_user_secret');
        StileroTTHelper::importDependencies();
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
     * Displays a Joomla message in backend.
     * @param string $message The message to display
     * @param string $type The type of message
     */
    protected function _showMessage($message, $type='message'){
        if($this->_isBackend){
            StileroTTMessageHelper::show($message, $type);
        }
    }
    
    /**
     * Prepares and sends a tweet. Displays messages after tweeting.
     * @param Object $article Joomla article Object
     */
    protected function _sendTweet($article){
        $this->_initializePosting(true, $article);
        $Article = $this->_Article->getArticleObj();
        $hasChecksPassed = $this->_ShareCheck->hasFullChecksPassed();
        $isInLog = $this->_Table->isLogged($Article->id);
        if(!$isInLog || !$this->_allwaysPostOnSave){
            if($hasChecksPassed && !$isInLog){
                $status = StileroTTTweetHelper::buildTweet($Article, 5, $this->_defaultTag, $this->_useMetaAsHash);
                $response = $this->_Tweet->update($status);
                $TwitterResponse = new StileroTTTwitterResponse($response);
                if($TwitterResponse->hasID()){
                    $message = JText::_(self::LANG_PREFIX.'SUCCESS').$status;
                    $this->_showMessage($message);
                    $this->_Table->saveLog($Article->id, $Article->catid, $Article->url, $Article->lang);
                }else if($TwitterResponse->hasError()){
                    $message = JText::_(self::LANG_PREFIX.'ERROR').'('.$TwitterResponse->errorCode.') '.$TwitterResponse->errorMsg;
                    $this->_showMessage($message, StileroTTMessageHelper::TYPE_ERROR);
                }else{
                    $message = JText::_(self::LANG_PREFIX.'UNKNOWN_ERROR');
                    $this->_showMessage($message, StileroTTMessageHelper::TYPE_ERROR);
                }
            }else{
                $message = JText::_(self::LANG_PREFIX.'FAILED_CHECKS');
                //$this->_showMessage($message, StileroTTMessageHelper::TYPE_ERROR);
            }
        }else {
            $message = JText::_(self::LANG_PREFIX.'DUPLICATE_TWEET');
            $this->_showMessage($message, StileroTTMessageHelper::TYPE_NOTICE);
        }
    }
    
    /**
     * Method called after saving an article
     * @param string $context
     * @param Object $article
     * @param boolean $isNew
     */
    public function onContentAfterSave($context, &$article, $isNew) {
        $this->_isBackend = true;
        $this->_sendTweet($article);
        return;
    }
    
    /**
     * Method called after an article is displayed
     * @param string $context
     * @param Object $article
     * @param boolean $isNew
     */
    function onContentAfterDisplay($context, $article, $params, $limitstart = 0) {
        $this->_isBackend = false;
        $this->_sendTweet($article);
        return;
    }
}//End Plugin Class