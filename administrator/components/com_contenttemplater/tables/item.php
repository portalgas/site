<?php
/**
 * Item Table
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

/**
 * Item Table
 */
class ContentTemplaterTableItem extends JTable
{
	/**
	 * Constructor
	 *
	 * @param    object    Database object
	 *
	 * @return    void
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__contenttemplater', 'id', $db);
	}

	/**
	 * Overloaded check function
	 *
	 * @return boolean
	 */
	public function check()
	{
		$this->name = trim($this->name);

		// Check for valid name
		if (empty($this->name)) {
			$this->setError(JText::_('CT_THE_ITEM_MUST_HAVE_A_NAME'));
			return 0;
		}

		return 1;
	}
}
