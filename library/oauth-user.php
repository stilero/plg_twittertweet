<?php
/**
 * Class User
 *
 * @version  1.0
 * @package Stilero
 * @subpackage Class Twitter
 * @author Daniel Eliasson (joomla@stilero.com)
 * @copyright  (C) 2013-jan-06 Stilero Webdesign (www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

class StileroTTOauthUser{
    
    public $accessToken;
    public $tokenSecret;
    
    public function __construct($accessToken, $tokenSecret) {
        $this->accessToken = $accessToken;
        $this->tokenSecret = $tokenSecret;
    }
}
