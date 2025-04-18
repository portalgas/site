<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	mod_login
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

$scope = '';
if(isset($_GET['scope']))
    $scope = $_GET['scope'];

// No direct access.
defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
?>
<h2 class="form-signin-heading visible-lg visible-md visible-sm">Richiesta d'identificazione</h2>

<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" id="form-login">
<fieldset class="loginform">

		<div class="form-group">
			<label for="name" class="col-2 control-label">Username</label>
			<div class="col-10">
				<div class="input-group">
					<span class="input-group-addon"><i class="glyphicon glyphicon-user" aria-hidden="true"></i></span>
					<input class="form-control" type="text" class="form-control" name="username" id="mod-login-username" placeholder="Username" required>
				</div>
			</div>
		</div>
		
		<div class="form-group">
			<label for="password" class="col-2 control-label">Password</label>
			<div class="col-10">
				<div class="input-group">
					<span class="input-group-addon"><i class="glyphicon glyphicon-lock" aria-hidden="true"></i></span>
					<input type="password" class="form-control" name="passwd" id="mod-login-password" placeholder="Password" required>
				</div>
			</div>
		</div>


		<button class="btn btn-lg btn-primary btn-block" type="submit"><?php echo JText::_( 'MOD_LOGIN_LOGIN' ); ?></button>
		
		<input type="hidden" name="lang" id="lang" value="it-IT" />
		<input type="hidden" name="option" value="com_login" />
		<input type="hidden" name="task" value="login" />
		<input type="hidden" name="return" value="<?php echo $return; ?>" />
		<?php echo JHtml::_('form.token'); ?>
</fieldset>
</form>
