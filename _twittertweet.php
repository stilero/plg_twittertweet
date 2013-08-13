<?php
/**
 * Description of twittertweet
 *
 * @version 2.10
 * @author danieleliasson Stilero AB - http://www.stilero.com
 * @copyright 2011-dec-31 Stilero AB
 * @license	GPLv2
 * 
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * 
 * This file is part of TwitterTweet
 * 
 * TwitterTweet is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or 
 * (at your option) any later version.
 * 
 * TwitterTweet is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
 * See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with TwitterTweet.  
 * If not, see <http://www.gnu.org/licenses/>.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Import library dependencies
jimport('joomla.plugin.plugin');

class plgSystemTwittertweet extends JPlugin {
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
        $this->errorOccured = FALSE;
        $this->config = array(
            'shareLogTableName'     =>      '#__twittertweet_tweeted',
            'twitterUsername'       =>      $this->params->def('username'),
            'twitterPassword'       =>      $this->params->def('password'),
            'oauthConsumerKey'      =>      $this->params->def('oauth_consumer_key'),
            'oauthConsumerSecret'   =>      $this->params->def('oauth_consumer_secret'),
            'oauthUserKey'          =>      $this->params->def('oauth_user_key'),
            'oauthUserSecret'       =>      $this->params->def('oauth_user_secret'),
            'twitterMaxHashTags'    =>      3,
            'useOauth'              =>      $this->params->def('oauth_enabled'),
            'twControllerClassName' =>      'stlTweetControllerClass',
            'categoriesToShare'     =>      $this->params->def('section_id'),
            'shareDelay'            =>      $this->params->def('delay'),
            'articlesNewerThan'     =>      $this->params->def('items_newer_than'),
            'useMetaAsHashTag'      =>      $this->params->def('metahash'),
            'defaultHashTag'        =>      $this->params->def('default_hash'),
            'postOnSave'            =>      $this->params->def('post_on_save')
        ); 
    }

    public function onContentAfterSave($context, &$article, $isNew) {
        $this->inBackend = true;
        $this->setupClasses();
        $articleObject = $this->getArticleObjectFromJoomlaArticle($article);
        $this->CheckClass->setArticleObject($articleObject);
        $this->sendTweet();
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

