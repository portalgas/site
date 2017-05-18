<?php
/**
 * Item Controller
 *
 * @package         Content Templater
 * @version         4.6.3
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2013 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Item Controller
 */
class ContentTemplaterControllerItem extends JControllerForm
{
	/**
	 * @var        string    The prefix to use with controller messages.
	 */
	protected $text_prefix = 'NN';
	// Parent class access checks are sufficient for this controller.
}
