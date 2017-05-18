<?php 
$msgOpen = ""; 
$msgClose = "";

$GGMailToAlertOrderOpen = (Configure::read('GGMailToAlertOrderOpen')+1);
if($GGMailToAlertOrderOpen==1)
	$msgOpen = "Il giorno stesso ";
else	$msgOpen = $GGMailToAlertOrderOpen." giorni prima ";
$msgOpen .= "dell'<b>apertura</b> dell'ordine sarà inviata una mail a tutti i gasisti per comunicarne l'apertura.";

if($modalita=='ADD') {
	$msgOpen .= "<ul>";
	$msgOpen .= "<li>- Se indichi il giorno di apertura dell'ordine <b>uguale</b> al giorno odierno la mail a tutti i gasisti sarà inviata domani.</li>";
	$msgOpen .= "<li>- Se indichi il giorno di apertura dell'ordine <b>precedente</b> al giorno odierno non sarà inviata alcuna mail.</li>";
	$msgOpen .= "</ul>";
}
else
	$msgOpen .= "<br />";


$GGMailToAlertOrderClose = (Configure::read('GGMailToAlertOrderClose')+1);
if($GGMailToAlertOrderClose==1)	$msgClose = "Il giorno ";else	$msgClose = $GGMailToAlertOrderClose." giorni ";$msgClose .= "prima della <b>chiusura</b> dell'ordine sarà inviata una mail a tutti i gasisti per comunicarne la chiusura.";
echo '<div class="legenda legenda-ico-mails" style="float:none;">';
echo $msgOpen;
echo '<br />';
echo $msgClose;
echo '</div>';
?>