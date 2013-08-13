<?php
/**
 * StileroTTURL Class
 * 
 * URL helper class
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

class StileroTTUrlHelper{
    
//    protected $_articleSlug;
//    protected $_categorySlug;
//    protected $_siteURL;
//
//    public function __construct($articleSlug, $categorySlug) {
//        $this->_articleSlug = $articleSlug;
//        $this->_categorySlug = $categorySlug;
//        $this->_siteURL = substr(JURI::root(), 0, -1);
//    }
//
//    protected function _setApplicationInstance(){
//        if(JPATH_BASE == JPATH_ADMINISTRATOR) {
//            // In the back end we need to set the application to the site app instead
//            JFactory::$application = JApplication::getInstance('site');
//        }
//    }
//    
//    protected function _articleRoute(){
//        $articleRoute = JRoute::_( 
//                ContentHelperRoute::getArticleRoute($articleSlug, $categorySlug) 
//                );
//        $sefURI = str_replace(JURI::base(true), '', $articleRoute);
//        if(JPATH_BASE == JPATH_ADMINISTRATOR) {
//            $siteURL = str_replace($siteURL.DS.'administrator', '', $siteURL);
//            JFactory::$application = JApplication::getInstance('administrator');
//        }
//        $sefURL = $siteURL.$sefURI;
//        return $sefURL;
//    }
    /**
     * Returns a SEF URL from the article and Category Slug
     * @param string $articleSlug
     * @param string $categorySlug
     * @return string SefURL - Search Engine Friendly URL
     */
    public static function sefURL($articleSlug, $categorySlug){
        require_once(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');
        $siteURL = substr(JURI::root(), 0, -1);
        if(JPATH_BASE == JPATH_ADMINISTRATOR) {
            // In the back end we need to set the application to the site app instead
            JFactory::$application = JApplication::getInstance('site');
        }
        $articleRoute = JRoute::_( ContentHelperRoute::getArticleRoute($articleSlug, $categorySlug) );
        $sefURI = str_replace(JURI::base(true), '', $articleRoute);
        if(JPATH_BASE == JPATH_ADMINISTRATOR) {
            $siteURL = str_replace($siteURL.DS.'administrator', '', $siteURL);
            JFactory::$application = JApplication::getInstance('administrator');
        }
        $sefURL = $siteURL.$sefURI;
        return $sefURL;
    }
    
    
    
}
