<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.5
 */

defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
?>

<h2>Login</h2>

<div class="login<?php echo $this->pageclass_sfx?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
	<h1>
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
	<?php endif; ?>

	<?php if (($this->params->get('logindescription_show') == 1 && str_replace(' ', '', $this->params->get('login_description')) != '') || $this->params->get('login_image') != '') : ?>
	<div class="login-description">
	<?php endif ; ?>

		<?php if($this->params->get('logindescription_show') == 1) : ?>
			<?php echo $this->params->get('login_description'); ?>
		<?php endif; ?>

		<?php if (($this->params->get('login_image')!='')) :?>
			<img src="<?php echo $this->escape($this->params->get('login_image')); ?>" class="login-image" alt="<?php echo JTEXT::_('COM_USER_LOGIN_IMAGE_ALT')?>"/>
		<?php endif; ?>

	<?php if (($this->params->get('logindescription_show') == 1 && str_replace(' ', '', $this->params->get('login_description')) != '') || $this->params->get('login_image') != '') : ?>
	</div>
	<?php endif ; ?>

	<div class="container">
	<div class="col-xs-3"></div>	
	<div class="col-xs-6">		
	<form class="form-horizontal" action="<?php echo JRoute::_('index.php?option=com_users&task=user.login'); ?>" method="post">

		<fieldset>
		
			<div class="form-group">
	            <label for="inputPassword" class="control-label col-xs-2">Username</label>
            	<div class="col-xs-10">
				  	<input type="text" placeholder="Userame" id="username" name="username" class="form-control">
				</div>
			</div>

			<div class="form-group">
	            <label for="inputPassword" class="control-label col-xs-2">Password</label>
            	<div class="col-xs-10">
					  <input type="password" placeholder="Password" value="" id="password" name="password" class="form-control">
				</div>
			</div>
			
			
			<?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
			<div class="form-group">
	            <div class="col-xs-offset-2 col-xs-10">
					<label id="remember-lbl" for="remember"><?php echo JText::_('JGLOBAL_REMEMBER_ME') ?></label>
					<input id="remember" type="checkbox" name="remember" class="inputbox" value="yes"  alt="<?php echo JText::_('JGLOBAL_REMEMBER_ME') ?>" />
				</div>
			</div>
			<?php endif; ?>
			
			<div class="form-group">
            	<div class="col-xs-offset-2 col-xs-10">
            			<button type="submit" class="btn btn-success"><?php echo JText::_('JLOGIN'); ?></button>
						<input type="hidden" name="return" value="<?php echo base64_encode($this->params->get('login_redirect_url', $this->form->getValue('return'))); ?>" />
						<?php echo JHtml::_('form.token'); ?>			
            	</div>
        	</div>			
			
		</fieldset>
	</form>
	
	
	<div>
	<ul>
		<li>
			<a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>">
			<?php echo JText::_('COM_USERS_LOGIN_RESET'); ?></a>
		</li>
		<li>
			<a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>">
			<?php echo JText::_('COM_USERS_LOGIN_REMIND'); ?></a>
		</li>
		<?php
		$usersConfig = JComponentHelper::getParams('com_users');
		if ($usersConfig->get('allowUserRegistration')) : ?>
		<li>
			<a href="<?php echo JRoute::_('index.php?option=com_users&view=registration'); ?>">
				<?php echo JText::_('COM_USERS_LOGIN_REGISTER'); ?></a>
		</li>
		<?php endif; ?>
	</ul>
	</div>
	
	</div>
	<div class="col-xs-3"></div>
	</div> <!-- class="container" -->
	
</div>
