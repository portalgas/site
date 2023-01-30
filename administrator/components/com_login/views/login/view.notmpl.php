<?php
/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Login component
 *
 * @package		Joomla.Administrator
 * @subpackage	com_login
 * @since		1.6
 *
 * fractis notmpl per le chiamate ajax con sessione scaduta
 * administrator\components\com_login\views\login\view.notmpl.php
 *     public function display($tpl = null) {
 *     	self::setLayout('notmpl');           
 *      parent::display();
 * administrator\templates\bluestork\html\com_login\login\notmpl.php
 *		html 
 */
class LoginViewLogin extends JViewLegacy
{
    public function __construct() {
        parent::__construct();
    }

    public function display($tpl = null) {
        self::setLayout('notmpl');           
        parent::display();
    }
}