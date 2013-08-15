<?php

/**
 * A Factory Class for creating standardised article objects 
 *
 * @version  3.0
 * @package Stilero
 * @subpackage plg_twittertweet
 * @author Daniel Eliasson <daniel at stilero.com>
 * @copyright  (C) 2013-aug-12 Stilero Webdesign (http://www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class StileroTTJArticle {
    
    var $Article;
    
    /**
     * Constructor for the Class
     * @param Object $article A Joomla Article object
     */
    public function __construct($article) {
        if(!isset($article) || empty($article)){
            return;
        }
        $tempClass = new stdClass();
        foreach ($article as $property => $value) {
            $tempClass->$property = $value;
        }
        $tempClass->jVersion = StileroTTJVersionHelper::jVersion();
        $tempClass->category_title = $this->categoryTitle($article);
        $tempClass->description = $this->description($article);
        $tempClass->isPublished = $this->isPublished($article);
        $tempClass->isPublic = $this->isPublic($article);
        $tempClass->image = StileroTTJArticleImageHelper::image($article);
        $tempClass->firstContentImage = StileroTTJArticleImageHelper::firstImageInContent($article);
        $tempClass->introImage = StileroTTJArticleImageHelper::imageFromTextType($article, StileroTTJArticleImageHelper::IMAGE_TYPE_INTRO);
        $tempClass->fullTextImage = StileroTTJArticleImageHelper::imageFromTextType($article, StileroTTJArticleImageHelper::IMAGE_TYPE_FULL);
        $tempClass->imageArray = StileroTTJArticleImageHelper::imagesInContent($article);
        $tempClass->url = $this->url($article);
        $tempClass->tags = StileroTTTagsHelper::tags($article->metakey);
        $this->Article = $tempClass;
    }
    
    /**
     * Returns the article object
     * @return Object Article object
     */
    public function getArticleObj(){
        return $this->Article;
    }
    
    /**
     * Returns the Category Title based on the current Joomla version
     * @param Object $article Article Object
     * @return string Category Title
     */
    public function categoryTitle($article){
        if (StileroTTJVersionHelper::jVersion() == StileroTTJVersionHelper::JOOMLA_VERSION_15){
            $category_title = isset($article->category) ? $article->category : '';
        }else{
            $category_title = isset($article->category_title) ? $article->category_title : '';
        }
        return $category_title;
    }
    
    /**
     * Returns a description from either text, introtext or metadesc
     * @param Object $article Article object
     * @return string Article Description
     */
    public function description($article){
        $descText = '';
        if(isset($article->text)){
            $descText = $article->text;
        }
        //$description = $article->text!="" ? $article->text : '';
        if(isset($article->introtext) && $article->introtext!=""){
            $descText = $article->introtext;
        }elseif (isset($article->metadesc) && $article->metadesc!="" ) {
            $descText = $article->metadesc;
        }
        $descNeedles = array("\n", "\r", "\"", "'");
        str_replace($descNeedles, " ", $descText );
        $description = substr(htmlspecialchars( strip_tags($descText), ENT_COMPAT, 'UTF-8'), 0, 250);
        return $description;
    }
    
    /**
     * Generates a SEF URL
     * @param Object $article
     * @return string SEF URL
     */
    public function url($article){
        $articleSlug = StileroTTArticleHelper::slugFromId($article->id);
        $categorySlug = StileroTTCategoryHelper::slugFromId($article->catid);
        $hasSH404SEF = StileroTTExtensionHelper::isInstalled('com_sh404sef');
        if($hasSH404SEF){
            $url = StileroTTSH404SEFUrlHelper::sefURL($articleSlug, $categorySlug);
        }else{
            $url = StileroTTUrlHelper::sefURL($articleSlug, $categorySlug);
        }
        return $url;
    }

    /**
     * Checks if an article is publicly available
     * @param Object $article Article Object
     * @return boolean True if public
     */
    public function isPublic($article){
        if(!isset($article->access)){
            return FALSE;
        }
        if(StileroTTJVersionHelper::jVersion() == StileroTTJVersionHelper::JOOMLA_VERSION_15){
            $isPublic = $article->access=='0' ? TRUE : FALSE;
        }else{
            $isPublic = $article->access=='1' ? TRUE : FALSE;
        }
        return $isPublic;
    }
    
    /**
     * Checks if an article is published
     * @param Object $article Article object
     * @return boolean True if published
     */
    public function isPublished($article){
        if(JDEBUG) JFactory::getApplication()->enqueueMessage( var_dump($article));
        $isPublState = $article->state == '1' ? true : false;
        if(!$isPublState){
            return FALSE;
        }
        $publishUp = isset($article->publish_up) ? $article->publish_up : '';
        $publishDown = isset($article->publish_down) ? $article->publish_down : '';
        if($publishUp == '' ){
            return false;
        }
        $date = JFactory::getDate();
        $currentDate = $date->toSql();
        if ( ($publishUp > $currentDate) ){
            return FALSE;
        }else if($publishDown < $currentDate && $publishDown != '0000-00-00 00:00:00' && $publishDown!=""){
            return FALSE;
        }else {
            return TRUE;
        }
    }
    
    public function isArticle(){
        $hasID = isset($this->Article->id) ? TRUE : FALSE;
        $hasTitle = isset($this->Article->title) ? TRUE : FALSE;
        if($hasID && $hasTitle){
            return TRUE;
        }
        return FALSE;
    }

}