<?php
/**
 * Item Model
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

jimport('joomla.application.component.modeladmin');

/**
 * Item Model
 */
class ContentTemplaterModelItem extends JModelAdmin
{
	/**
	 * Constructor.
	 *
	 * @param    array    An optional associative array of configuration settings.
	 *
	 * @see        JController
	 */
	public function __construct()
	{
		// Load plugin parameters
		require_once JPATH_PLUGINS . '/system/nnframework/helpers/parameters.php';
		$this->parameters = NNParameters::getInstance();
		$this->_config = $this->parameters->getComponentParams('contenttemplater');

		parent::__construct();
	}

	/**
	 * @var        string    The prefix to use with controller messages.
	 */
	protected $text_prefix = 'NN';

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param    object    $record    A record object.
	 *
	 * @return    boolean    True if allowed to delete the record. Defaults to the permission set in the component.
	 */
	protected function canDelete($record)
	{
		if ($record->published != -2) {
			return false;
		}
		$user = JFactory::getUser();
		return $user->authorise('core.admin', 'com_contenttemplater');
	}

	/**
	 * Method to test whether a record can have its state edited.
	 *
	 * @param    object    $record    A record object.
	 *
	 * @return    boolean    True if allowed to change the state of the record. Defaults to the permission set in the component.
	 */
	protected function canEditState($record)
	{
		$user = JFactory::getUser();

		// Check the component since there are no categories or other assets.
		return $user->authorise('core.admin', 'com_contenttemplater');
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param    type      The table type to instantiate
	 * @param    string    A prefix for the table class name. Optional.
	 * @param    array     Configuration array for model. Optional.
	 *
	 * @return    JTable    A database object
	 */
	public function getTable($type = 'Item', $prefix = 'ContentTemplaterTable', $config = array())
	{
		JTable::addIncludePath(dirname(__DIR__) . '/tables');
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param    array      $data        Data for the form.
	 * @param    boolean    $loadData    True if the form is to load its own data ( default case ), false if not.
	 *
	 * @return    JForm    A JForm object on success, false on failure
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			'com_contenttemplater.item', 'item', array(
				'control' => 'jform',
				'load_data' => $loadData
			)
		);
		if (empty($form)) {
			return false;
		}

		// Modify the form based on access controls.
		if ($this->canEditState((object ) $data) != true) {
			// Disable fields for display.
			$form->setFieldAttribute('published', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is a record you can edit.
			$form->setFieldAttribute('published', 'filter', 'unset');
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return    mixed    The data for the form.
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_contenttemplater.edit.item.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @return  mixed    Object on success, false on failure.
	 */
	public function getItem($pk = null, $getform = 0, $getdefaults = 0)
	{
		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
		$table = $this->getTable();

		if ($pk > 0) {
			// Attempt to load the row.
			$return = $table->load($pk);

			// Check for a table object error.
			if ($return === false && $table->getError()) {
				$this->setError($table->getError());
				return false;
			}
		}

		// Convert to the JObject before adding other data.
		$properties = $table->getProperties(1);
		$item = JArrayHelper::toObject($properties, 'JObject');

		$isini = ((substr($item->params, 0, 1) != '{') && (substr($item->params, -1, 1) != '}'));
		$params = $this->parameters->getParams($item->params, JPATH_ADMINISTRATOR . '/components/com_contenttemplater/item_params.xml');
		foreach ($params as $key => $val) {
			if (!isset($item->$key) && !is_object($val)) {
				$item->$key = $val;
			}
		}

		unset($item->params);

		if ($isini) {
			foreach ($item as $key => $val) {
				if (is_string($val)) {
					$item->$key = stripslashes($val);
				}
			}
		}

		if ($getform) {
			$xmlfile = JPATH_ADMINISTRATOR . '/components/com_contenttemplater/item_params.xml';
			$params = new JForm('jform', array('control' => 'jform'));
			$params->loadFile($xmlfile, 1, '//config');
			$params->bind($item);
			$item->form = $params;
		}

		if ($getdefaults) {
			$item->defaults = $this->parameters->getParams('', JPATH_ADMINISTRATOR . '/components/com_contenttemplater/item_params.xml');

			// set default category to 'Undefined'
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('id')
				->from('#__categories')
				->where('parent_id = 1')
				->where('level = 1')
				->where('extension = ' . $db->quote('com_content'));
			$db->setQuery($query, 0, 1);
			$item->defaults->jform_catid = $db->loadResult();
		}

		return $item;
	}

	/**
	 * Method to activate list.
	 *
	 * @param    array     An array of item ids.
	 * @param    string    The new URL to set for the contenttemplater.
	 * @param    string    A comment for the contenttemplater list.
	 *
	 * @return    boolean    Returns true on success, false on failure.
	 */
	public function activate(&$pks, $name)
	{
		// Initialise variables.
		$user = JFactory::getUser();
		$db = $this->getDbo();

		// Sanitize the ids.
		$pks = ( array ) $pks;
		JArrayHelper::toInteger($pks);

		// Access checks.
		if (!$user->authorise('core.admin', 'com_contenttemplater')) {
			$pks = array();
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));
			return false;
		}

		if (!empty($pks)) {
			// Update the item rows.
			$db->setQuery(
				'UPDATE `#__contenttemplater`' .
					' SET `name` = ' . $db->quote($name) . ', `published` = 1' .
					' WHERE `id` IN ( ' . implode(',', $pks) . ' )'
			);
			$db->execute();

			// Check for a database error.
			if ($error = $this->_db->getErrorMsg()) {
				$this->setError($error);
				return false;
			}
		}
		return true;
	}

	/**
	 * Method to validate form data.
	 */
	public function validate($form, $data, $group = null)
	{
		// Check for valid name
		if (empty($data['name'])) {
			$this->setError(JText::_('CT_THE_ITEM_MUST_HAVE_A_NAME'));
			return 0;
		}

		$newdata = array();
		$params = array();
		$this->_db->setQuery('SHOW COLUMNS FROM #__contenttemplater');
		$dbkeys = $this->_db->loadObjectList('Field');
		$dbkeys = array_keys($dbkeys);

		foreach ($data as $key => $val) {
			if (in_array($key, $dbkeys)) {
				$newdata[$key] = $val;
			} else {
				$params[$key] = $val;
			}
		}

		$registry = new JRegistry;
		$registry->loadArray($params);
		$newdata['params'] = (string) $registry;

		return $newdata;
	}

	/**
	 * Method to copy an item
	 *
	 * @access    public
	 * @return    boolean    True on success
	 */
	function copy($id)
	{
		$item = $this->getItem($id);

		unset($item->_errors);
		$item->id = 0;
		$item->published = 0;
		$item->name = JText::sprintf('NN_COPY_OF', $item->name);

		$item = $this->validate(null, (array) $item);

		return ($this->save($item));
	}

	function replaceVars(&$str)
	{
		if (!(strpos($str, '[[user:') === false)) {
			if (preg_match_all('#\[\[user\:([^\]]+)\]\]#', $str, $matches, PREG_SET_ORDER) > 0) {
				$user = JFactory::getUser();
				$contact = null;
				foreach ($matches as $match) {
					if ($match['1'] == 'password' || $match['1']['0'] == '_') {
						$str = str_replace($match['0'], '', $str);
					} else if (isset($user->{$match['1']})) {
						$str = str_replace($match['0'], $user->{$match['1']}, $str);
					} else {
						if (!$contact) {
							$db = JFactory::getDBO();
							$query = $db->getQuery(true)
								->select('c.*')
								->from('#__' . $this->_config->contact_table . ' as c')
								->where('c.user_id = ' . (int) $user->id);
							$db->setQuery($query);
							$contact = $db->loadObject();
						}
						if (isset($contact->{$match['1']})) {
							$str = str_replace($match['0'], $contact->{$match['1']}, $str);
						} else {
							$str = str_replace($match['0'], '', $str);
						}
					}
				}
			}
		}
		if (!(strpos($str, '[[date:') === false)) {
			if (preg_match_all('#\[\[date\:([^\]]+)\]\]#', $str, $matches, PREG_SET_ORDER) > 0) {
				require_once JPATH_PLUGINS . '/system/nnframework/helpers/text.php';
				foreach ($matches as $match) {
					if ($match['1'] && !(strpos($match['1'], '%') === false)) {
						$match['1'] = NNText::dateToDateFormat($match['1']);
					}
					$replace = JHtml::_('date', time(), $match['1']);
					$str = str_replace($match['0'], $replace, $str);
				}
			}
		}
		if (!(strpos($str, '[[random:') === false)) {
			while (preg_match('#\[\[random\:([0-9]+)-([0-9]+)\]\]#', $str, $match)) {
				$search = '#' . preg_quote($match['0'], "#") . '#';
				$replace = rand((int) $match['1'], (int) $match['2']);
				$str = preg_replace($search, $replace, $str, 1);
			}
		}
	}
}
