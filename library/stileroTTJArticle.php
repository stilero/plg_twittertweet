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
        $tempClass->image = $this->image($article);
        $tempClass->firstContentImage = $this->firstContentImage($article);
        $tempClass->introImage = $this->introImage($article);
        $tempClass->fullTextImage = $this->fullTextImage($article);
        $tempClass->imageArray = $this->images($article);
        $tempClass->url = $this->url($article);
        $tempClass->tags = $this->tags($article);
        $tempClass->component = JRequest::getCmd('option');
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
        $isPublState = false;
        if(isset($article->state)){
            $isPublState = $article->state;
        }
        if(!$isPublState){
            return FALSE;
        }
        $publishUp = isset($article->publish_up) ? $article->publish_up : '';
        $publishDown = isset($article->publish_down) ? $article->publish_down : '';
        if($publishUp == '' ){
            return TRUE;
        }
        $date = JFactory::getDate();
        $currentDate = $date->toSql(TRUE);
        if ( ($publishUp > $currentDate) ){
            return FALSE;
        }else if($publishDown < $currentDate && $publishDown != '0000-00-00 00:00:00' && $publishDown!=""){
            return FALSE;
        }else {
            return TRUE;
        }
    }
    
    /**
     * Checks if the article is native article
     * @return boolean true if article
     */
    public function isArticle(){
        $hasID = isset($this->Article->id) ? TRUE : FALSE;
        $hasTitle = isset($this->Article->title) ? TRUE : FALSE;
        if($hasID && $hasTitle){
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Returns the image from the article
     * @param stdClass $Article
     * @return string image url
     */
    protected function image($Article){
        return StileroTTJArticleImageHelper::image($Article);
    }

    /**
     * Returns the first image extracted from the content
     * @param stdClass $Article
     * @return string image url
     */
    protected function firstContentImage($Article){
        return StileroTTJArticleImageHelper::firstImageInContent($Article);
    }
    
    /**
     * Returns an image used as intro image
     * @param stdClass $Article
     * @return string image url
     */
    protected function introImage($Article){
        return StileroTTJArticleImageHelper::imageFromTextType($Article, StileroTTJArticleImageHelper::IMAGE_TYPE_INTRO);
    }
    
    /**
     * Returns an image from the article full text
     * @param stdClass $Article
     * @return string image url
     */
    protected function fullTextImage($Article){
        return StileroTTJArticleImageHelper::imageFromTextType($Article, StileroTTJArticleImageHelper::IMAGE_TYPE_FULL);
    }
    
    /**
     * Returns an array with all images found in article
     * @param stdClass $Article
     * @return Array images urls
     */
    protected function images($Article){
        return StileroTTJArticleImageHelper::imagesInContent($Article);
    }
    
    /**
     * Returns an array with tags extracted from meta tags
     * @param stdClass $Article
     * @return Array tags
     */
    protected function tags($Article){
        return StileroTTTagsHelper::tags($Article->metakey);
    }
}

class StileroTTK2Article extends StileroTTJArticle {

    public function __construct($article) {
        parent::__construct($article);
    }
    
    /**
     * Extracts and returns the category title
     * @param stdClass $article Joomla article object
     * @return string Category title
     */
    public function categoryTitle($article){
        $category_title = '';
        if(isset($article->category->name)){
            $category_title = $article->category->name;
        }
        return $category_title;
    }
    
    /**
     * Returns the intro image from the article
     * @param \stdClass $Article
     * @return string image url
     */
    protected function introImage($Article) {
        return StileroTTK2ImageHelper::introImage($Article);
    }
    
    /**
     * Returns the first image from the article text
     * @param \stdClass $Article
     * @return string image url
     */
    protected function fullTextImage($Article) {
        return StileroTTK2ImageHelper::fullTextImage($Article);
    }
    
    /**
     * Returns an array with all images found in the article
     * @param \stdClass $Article
     * @return Array image urls
     */
    protected function images($Article) {
        return StileroTTK2ImageHelper::imagesInContent($Article);        
    }


    /**
     * Returns the article URL
     * @param stdClass $Article
     * @return string full article url
     */
    public function url($Article) {
        return StileroTTK2UrlHelper::sefURL($Article);
    }
    
    /**
     * Checks if an article is published
     * @param Object $article Article object
     * @return boolean True if published
     */
    public function isPublished($article){
        if(JDEBUG) JFactory::getApplication()->enqueueMessage( var_dump($article));
        $isPublState = false;
        if(isset($article->published)){
            $isPublState = $article->published;
        }
        if(!$isPublState){
            return FALSE;
        }
        $publishUp = isset($article->publish_up) ? $article->publish_up : '';
        $publishDown = isset($article->publish_down) ? $article->publish_down : '';
        if($publishUp == '' ){
            return TRUE;
        }
        $date = JFactory::getDate();
        $currentDate = $date->toSql(TRUE);
        if ( ($publishUp > $currentDate) ){
            return FALSE;
        }else if($publishDown < $currentDate && $publishDown != '0000-00-00 00:00:00' && $publishDown!=""){
            return FALSE;
        }else {
            return TRUE;
        }
    }

}