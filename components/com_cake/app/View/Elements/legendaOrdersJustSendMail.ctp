<?php 
if($mail_open_data!=Configure::read('DB.field.datetime.empty'))
	$msg = "Il giorno <i>".$this->Time->i18nFormat($mail_open_data,"%A %e %B %Y")."</i> è stata inviata la mail a tutti i gasisti per comunicarne l'<span style=\"color:green;\">apertura</span>.";

if($mail_close_data!=Configure::read('DB.field.datetime.empty'))
	$msg .= "<br /><br />Il giorno <i>".$this->Time->i18nFormat($mail_close_data,"%A %e %B %Y")."</i> è stata inviata la mail a tutti i gasisti per comunicarne la <span style=\"color:red;\">chiusura</span>.";

echo '<div class="legenda legenda-ico-mails" style="float:none;">';
echo $msg;
echo '</div>';
?>