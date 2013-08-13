<?php
/**
 * Client Class for Twitter Library. Also referred to as Consumer.
 *
 * @version  1.0
 * @package Stilero
 * @subpackage Class Twitter
 * @author Daniel Eliasson (joomla@stilero.com)
 * @copyright  (C) 2013-jan-06 Stilero Webdesign (www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class StileroTTOauthClient{
    
    public $key;
    public $secret;
    
    public function __construct($key, $secret) {
        $this->key = $key;
        $this->secret = $secret;
    }
}
