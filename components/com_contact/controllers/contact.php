<?php
/**
 * @package        Joomla.Site
 * @subpackage     Contact
 * @copyright      Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

class ContactControllerContact extends JControllerForm
{
	public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, array('ignore_request' => false));
	}

	public function submit()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app    = JFactory::getApplication();
		$model  = $this->getModel('contact');
		$params = JComponentHelper::getParams('com_contact');
		$stub   = JRequest::getString('id');
		$id     = (int) $stub;

		// Get the data from POST
		$data = JRequest::getVar('jform', array(), 'post', 'array');

		/*
		 * fractis
		 */		 
		$debug = false;

		$userIP = $_SERVER["REMOTE_ADDR"];
		$recaptchaResponse = $_POST['g-recaptcha-response'];
		$g_recaptcha_server =$app->getCfg('g_recaptcha_server');
		$url = "https://www.google.com/recaptcha/api/siteverify?secret={$g_recaptcha_server}&response={$recaptchaResponse}&remoteip={$userIP}";
		if($debug) {
			echo '<br />url '.$url;
			echo "<pre>";
			print_r($requestGoolge);
			echo "</pre>";	
		}
		$requestGoolge = file_get_contents($url);
		if(!strstr($requestGoolge, "true")){
			if($debug) echo "NOPE (Failed Verification)";
			JError::raiseError(500, $model->getError());
			if($debug) 
				exit;
			else
				return false;			
		}
		else {
			if($debug) 
				echo '<br />Code verify';		
		}

		$contactOrganizationId  = 0;
		if(isset($data['contact_contactOrganizationId']))
			$contactOrganizationId = $data['contact_contactOrganizationId'];
		if($debug) {
			echo "<pre>";
			print_r($data);
			echo "</pre>";			
			echo '<h1>com_contact  '.$contactOrganizationId.'</h1>';
		}

		$contact = $model->getItem($id);

        /*
         * spam
         * se il messaggio contiene https://t.me/ non lo invio
         * se l'oggetto inizia per "PortAlGas: "
         */
        $subject = strtolower($data['contact_subject']);
        if(strpos($data['contact_message'], 'https://t.me/')!==false ||
            strpos($subject, 'hallo,')!==false ||
            strpos($subject, 'hi,')!==false ||
            strpos($subject, 'hello,')!==false ||
            strpos($subject, 'aloha,')!==false ||
            strpos($subject, 'hallo ')!==false ||
            // strpos($subject, 'hi ')!==false ||
            strpos($subject, 'hello ')!==false ||
            strpos($subject, 'aloha ')!==false) {
            $msg = JText::_('COM_CONTACT_EMAIL_THANKS');

            // Flush the data from the session
            $app->setUserState('com_contact.contact.data', null);
            // Redirect if it is set in the parameters, otherwise redirect back to where we came from
            if ($contact->params->get('redirect'))
            {
                $this->setRedirect($contact->params->get('redirect'), $msg);
            }
            else
            {
                $this->setRedirect(JRoute::_('index.php?option=com_contact&view=contact&id=' . $stub.'&contactOrganizationId='.$contactOrganizationId, false), $msg);
            }
            return true;
        } // end spam

		$params->merge($contact->params);

		// Check for a valid session cookie
		if ($params->get('validate_session', 0))
		{
			if (JFactory::getSession()->getState() != 'active')
			{
				JError::raiseWarning(403, JText::_('COM_CONTACT_SESSION_INVALID'));

				// Save the data in the session.
				$app->setUserState('com_contact.contact.data', $data);

				// Redirect back to the contact form.
				$this->setRedirect(JRoute::_('index.php?option=com_contact&view=contact&id=' . $stub .'&contactOrganizationId='.$contactOrganizationId, false)); // fractis

				return false;
			}
		}

		// Contact plugins
		JPluginHelper::importPlugin('contact');
		$dispatcher = JDispatcher::getInstance();

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			JError::raiseError(500, $model->getError());

			return false;
		}

		$validate = $model->validate($form, $data);

		if ($validate === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Save the data in the session.
			$app->setUserState('com_contact.contact.data', $data);

			// Redirect back to the contact form.
			$this->setRedirect(JRoute::_('index.php?option=com_contact&view=contact&id=' . $stub.'&contactOrganizationId='.$contactOrganizationId, false)); // fractis

			return false;
		}

		// Validation succeeded, continue with custom handlers
		$results = $dispatcher->trigger('onValidateContact', array(&$contact, &$data));

		foreach ($results as $result)
		{
			if ($result instanceof Exception)
			{
				return false;
			}
		}

		// Passed Validation: Process the contact plugins to integrate with other applications
		$results = $dispatcher->trigger('onSubmitContact', array(&$contact, &$data));

		// Send the email
		$sent = false;

		if (!$params->get('custom_reply'))
		{
			$sent = $this->_sendEmail($data, $contact, $params->get('show_email_copy'));
		}

		// Set the success message if it was a success
		if (!($sent instanceof Exception))
		{
			$msg = JText::_('COM_CONTACT_EMAIL_THANKS');
		}
		else
		{
			$msg = '';
		}

		// Flush the data from the session
		$app->setUserState('com_contact.contact.data', null);

		// Redirect if it is set in the parameters, otherwise redirect back to where we came from
		if ($contact->params->get('redirect'))
		{
			$this->setRedirect($contact->params->get('redirect'), $msg);
		}
		else
		{
			$this->setRedirect(JRoute::_('index.php?option=com_contact&view=contact&id=' . $stub.'&contactOrganizationId='.$contactOrganizationId, false), $msg);
		}

		return true;
	}

	private function _sendEmail($data, $contact, $copy_email_activated)
	{
		$app    = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_contact');

		if ($contact->email_to == '' && $contact->user_id != 0)
		{
			$contact_user      = JUser::getInstance($contact->user_id);
			$contact->email_to = $contact_user->get('email');
		}

		$mailfrom = $app->getCfg('mailfrom');
		$fromname = $app->getCfg('fromname');
		$sitename = $app->getCfg('sitename');
		$copytext = JText::sprintf('COM_CONTACT_COPYTEXT_OF', $contact->name, $sitename);

		$name    = $data['contact_name'];
		$email   = $data['contact_email'];
		$subject = $data['contact_subject'];
		$body    = $data['contact_message'];

		// Prepare email body
		$prefix = JText::sprintf('COM_CONTACT_ENQUIRY_TEXT', JURI::base());
		$body   = $prefix . "\n" . $name . ' <' . $email . '>' . "\r\n\r\n" . stripslashes($body);

		$mail = JFactory::getMailer();
		
		/*
		 * fractis
		 */	
		$contactOrganizationId  = 0;
		if(isset($data['contact_contactOrganizationId'])) {
			$contactOrganizationId = $data['contact_contactOrganizationId'];
			
			$db = JFactory::getDbo();
			$rows = array();
			
			$sql = "SELECT 
						Organization.id, Organization.name, Organization.j_seo, 
						Organization.localita, Organization.provincia, Organization.mail    
					FROM
						k_organizations Organization
					WHERE
						Organization.stato = 'Y' and 
						Organization.id = ".$contactOrganizationId." 
					ORDER BY Organization.name";
			// echo '<br />'.$sql;
			$db->setQuery($sql);
			if ($db->query())
				$organization = $db->loadAssoc();
			/*
				echo "<pre>";
				print_r($organization);
				echo "</pre>";
			*/
			
			if(isset($organization['mail']) && !empty($organization['mail'])) 
				$mail_to = $organization['mail'];
			else
				$mail_to = $contact->email_to;
		}
		else
			$mail_to = $contact->email_to;
		$mail->addRecipient($mail_to);
		/*
		 * fractis
		 */
		 
		$mail->addReplyTo(array($email, $name));
		$mail->setSender(array($mailfrom, $fromname));
		$mail->setSubject($sitename . ': ' . $subject);
		$mail->setBody($body);
		
		
		/*
		 * fractis 
		 * validate email domain
		 */
		 $continua = false;
		 if(isset($email) && !empty($email)) {
			$emails = explode('.', $email);
			if(!empty($emails)) {
				$email_domain = end($emails); 
				$email_domain = strtolower($email_domain);
				// echo 'email_domain '.$email_domain.'<br />';
				if($email_domain!='ru' && $email_domain!='cn') 
					$continua = true;
			}
		 }

		 if($continua) {
		
				$sent = $mail->Send();

				// If we are supposed to copy the sender, do so.
				// Check whether email copy function activated
				if ($copy_email_activated == true && isset($data['contact_email_copy']))
				{
					$copytext = JText::sprintf('COM_CONTACT_COPYTEXT_OF', $contact->name, $sitename);
					$copytext .= "\r\n\r\n" . $body;
					$copysubject = JText::sprintf('COM_CONTACT_COPYSUBJECT_OF', $subject);

					$mail = JFactory::getMailer();
					$mail->addRecipient($email);
					$mail->addReplyTo(array($email, $name));
					$mail->setSender(array($mailfrom, $fromname));
					$mail->setSubject($copysubject);
					$mail->setBody($copytext);
					$sent = $mail->Send();
				}
		}
		else {
			// echo "Not send";
			// exit;
		}
		 
		return $sent;
	}
}
