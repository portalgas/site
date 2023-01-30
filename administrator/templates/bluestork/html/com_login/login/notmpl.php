<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	mod_login
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');

$app = JFactory::getApplication('site');
$neo_portalgas_url  = $app->getCfg('NeoPortalgasUrl');
// echo 'neo_portalgas_url '.$neo_portalgas_url;
?>
<!-- 
 pagina gia' inclusa in neo 
 viene richimata con una chiamata ajax da neo e su portalgas la sessione e' scaduta
 http://portalgas.local/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToReferent&delivery_id=10012&order_id=33032&doc_options=to-articles&doc_formato=PREVIEW&a=N&b=Y&c=&d=&e=&f=&g=&h=&scope=neo&format=notmpl 
-->
<!--  
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
<script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
-->

<div class="container">
	<div id="boxLogin">
		<h2 class="form-signin-heading visible-lg visible-md visible-sm">Richiesta d'identificazione</h2>

		<div id="loginEsito" class="alert alert-danger" style="display:none;"></div>

		<form id="formAuthJ" class="form-inline" action="/action_page.php" method="post">
			<fieldset>
				<div class="form-group">
					<div class="col-12">
						<div class="input-group">
							<span class="input-group-addon"><i class="glyphicon glyphicon-user" aria-hidden="true"></i></span>
							<input class="form-control" type="text" class="form-control" name="username" id="mod-login-username" placeholder="Username" required>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="col-12">
						<div class="input-group">
							<span class="input-group-addon"><i class="glyphicon glyphicon-lock" aria-hidden="true"></i></span>
							<input type="password" class="form-control" name="passwd" id="mod-login-password" placeholder="Password" required>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="col-12">
						<button id="sumbitAuthJ" class="btn btn-lg btn-primary btn-block" type="button">Autenticati</button>

						<input type="hidden" name="lang" id="lang" value="it-IT" />
						<input type="hidden" name="option" value="com_login" />
						<input type="hidden" name="task" value="login" />
						<input type="hidden" name="return" value="" />
						<?php echo JHtml::_('form.token'); ?>
					</div>
				</div>

			</fieldset>
		</form>
	</div>
	<div id="boxAfterLogin" style="display: none">
		<div class="alert alert-info">
			Autenticazione avvenuta con successo: effettua nuovamente l'operazione
		</div>
	</div>
</div>

<script>
$(function () {
    $('#sumbitAuthJ').on('click', function (e) {
		e.preventDefault();

		let username = $('#mod-login-username').val();
		let password = $('#mod-login-password').val();
		if(typeof username === 'undefined' || username=='' || password=='' || typeof password === 'undefined') {
			alert("Username e password obbligatori!");
			return false;
		}


		$('#loginEsito').hide();
		$('#loginEsito').html('');

		let params = {
			username: username,
			password: password,
		}
		let ajaxUrl = '/?option=com_cake&controller=Rests&action=autentication&format=notmpl';
		/* console.log(ajaxUrl, 'ajaxUrl'); */
		
		$.ajax({url: ajaxUrl,
			type: 'POST',
			data: params, 
			dataType: 'json',
			cache: false,
	        success: function (response) {
    	        console.log(response, 'responseText');
				if(response.esito)	{
					$('#boxLogin').hide();
					$('#boxAfterLogin').show();
				}
				else {
					$('#boxLogin').show();
					$('#boxAfterLoginhide').hide();	
					$('#loginEsito').show();
					$('#loginEsito').html(response.msg);
				}
			},
			error: function (e) {
				console.error(e, ajaxUrl);
			},
			complete: function (e) {

			}
        });
    });
});	
</script>