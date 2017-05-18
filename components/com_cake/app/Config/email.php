<?php
/**
 *
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Config
 * @since         CakePHP(tm) v 2.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
/**
 * This is email configuration file.
 *
 * Use it to configure email transports of CakePHP.
 *
 * Email configuration class.
 * You can specify multiple configurations for production, development and testing.
 *
 * transport => The name of a supported transport; valid options are as follows:
 *		Mail 		- Send using PHP mail function
 *		Smtp		- Send using SMTP
 *		Debug		- Do not send the email, just return the result
 *
 * You can add custom transports (or override existing transports) by adding the
 * appropriate file to app/Network/Email.  Transports should be named 'YourTransport.php',
 * where 'Your' is the name of the transport.
 *
 * from =>
 * The origin email. See CakeEmail::from() about the valid values
 *
 * http://book.cakephp.org/2.0/en/core-utility-libraries/email.html
 */
class EmailConfig {

	public $localhost = array(
			'host' => 'localhost',
			'port' => 25,  
			'timeout' => 45,
			'auth' => false,
			'username' => 'info@portalgas.it',
			'transport' => 'Smtp',
			'emailFormat' => 'both',
			'log' => false
	);

	public $gmail = array(
			'host' => 'ssl://smtp.gmail.com', // smtp.gmail.com 'tls' => true
			'port' => 465,
			'timeout' => 45,
			'auth' => true,
			'username' => 'francesco.actis@gmail.com',
			'password' => 'Barrett.1',
			'transport' => 'Smtp',
			'emailFormat' => 'both',
			'log' => false
	);
	
	public $glesys = array(
			'host' => 'mail.glesys.se',
			'port' => 25,  // 465
			'timeout' => 45,
			'auth' => true,
			'username' => 'info@portalgas.it',
			'password' => 'Barrett.1',
			'transport' => 'Smtp',
			'emailFormat' => 'both',
			'log' => false
	);	
	
	public $fast = array(
			'from' => 'you@localhost',
			'sender' => null,
			'to' => null,
			'cc' => null,
			'bcc' => null,
			'replyTo' => null,
			'readReceipt' => null,
			'returnPath' => null,
			'messageId' => true,
			'subject' => null,
			'message' => null,
			'headers' => null,
			'viewRender' => null,
			'template' => false,
			'layout' => false,
			'viewVars' => null,
			'attachments' => null,
			'emailFormat' => null,
			'transport' => 'Smtp',
			'host' => 'localhost',
			'port' => 25,
			'timeout' => 30,
			'username' => 'user',
			'password' => 'secret',
			'client' => null,
			'log' => false,
			//'charset' => 'utf-8',
			//'headerCharset' => 'utf-8',
	);	
}