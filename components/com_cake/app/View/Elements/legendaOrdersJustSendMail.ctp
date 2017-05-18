<?php 
if($mail_open_data!='0000-00-00 00:00:00')
	$msg = "Il giorno <i>".$this->Time->i18nFormat($mail_open_data,"%A %e %B %Y")."</i> è stata inviata la mail a tutti i gasisti per comunicarne l'<span style=\"color:green;\">apertura</span>.";

if($mail_close_data!='0000-00-00 00:00:00')
	$msg .= "<br /><br />Il giorno <i>".$this->Time->i18nFormat($mail_close_data,"%A %e %B %Y")."</i> è stata inviata la mail a tutti i gasisti per comunicarne la <span style=\"color:red;\">chiusura</span>.";

echo '<div class="legenda legenda-ico-mails" style="float:none;">';
echo $msg;
echo '</div>';
?>