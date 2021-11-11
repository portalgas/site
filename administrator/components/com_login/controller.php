<?php
/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

defined('_JEXEC') or die;

/**
 * Login Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_login
 * @since		1.5
 */
class LoginController extends JControllerLegacy
{
	/**
	 * Typical view method for MVC based architecture
	 *
	 * This function is provide as a default implementation, in most cases
	 * you will need to override it in your own controllers.
	 *
	 * @param	boolean			If true, the view output will be cached
	 * @param	array			An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 * @return	JController		This object to support chaining.
	 * @since	1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		// Special treatment is required for this plugin, as this view may be called
		// after a session timeout. We must reset the view and layout prior to display
		// otherwise an error will occur.

		JRequest::setVar('view', 'login');
		JRequest::setVar('layout', 'default');

		parent::display();
	}

	/**
	 * Method to log in a user.
	 *
	 * @return	void
	 */
	public function login()
	{
		// Check for request forgeries.
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();

		$model = $this->getModel('login');
		$credentials = $model->getState('credentials');
		$return = $model->getState('return');

		$result = $app->login($credentials, array('action' => 'core.login.admin'));
		
		/*
		 * fractis
		 * se Organization.stato = N blocco l'accesso al backoffice
		 */
		$user	= JFactory::getUser(); 
		if(!empty($user)) {
			$sql = "SELECT
					Organization.stato 
				FROM 
					k_organizations as Organization 
				WHERE
					Organization.id = ".$user->get('organization_id');
			// echo '<br />'.$sql;
			$db = JFactory::getDbo();
			$db->setQuery($sql);
			$results = $db->loadObject();
			if(!empty($results)) {
				$stato = $results->stato;			
				if($stato=='N') {
					$app = JFactory::getApplication(); 
					$result = $app->logout($user->get('id'));
					
					jexit('<h1 style="text-align:center;margin-top:20%;">G.A.S. non autorizzato ad accedere al backoffice!</h1>');
					
					// $app->redirect('index.php', JText::_('Accesso negato'));
				}
			}
		}
		
		if (!($result instanceof Exception)) {
			$app->redirect($return);
		}

		parent::display();
	}

	/**
	 * Method to log out a user.
	 *
	 * @return	void
	 */
	public function logout()
	{
		JSession::checkToken('request') or jexit(JText::_('JInvalid_Token'));

		$app = JFactory::getApplication();

		$userid = JRequest::getInt('uid', null);

		$options = array(
			'clientid' => ($userid) ? 0 : 1
		);

		$result = $app->logout($userid, $options);

		if (!($result instanceof Exception)) {
			$model 	= $this->getModel('login');
			$return = $model->getState('return');
			$app->redirect('/logout.php');
			// $app->redirect($return);
		}

		parent::display();
	}
}
