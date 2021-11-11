<?php 
/*
 * results dati articolo
 * isUserPermissionArticlesOrder se l'utente e' abilitato a gestire gli articoli associati agli ordini 
 * context article (order Article.stato sempre Y)
 */

$msg = '<div class="legenda legenda-ico-info" style="float:none;">';
if($context=='article') {
	if($isUserPermissionArticlesOrder) {   // l'utente gestisce l'associazione degli articoli con l'ordine
		$msg .= "Valorizzando i campi<ul><li><b>".__('Stato')."</b> a <span style=\"color:red\">No</span>, l'articolo <b>non</b> sarà visibile agli utenti</li><li><b>".__('FlagPresenteArticlesorders')."</b> a <span style=\"color:red\">No</span>, l'articolo <b>non</b> potrà essere associato agli ordini.";
	}
	else 
	if(!$isUserPermissionArticlesOrder)  {  // l'utente non gestisce l'associazione degli articoli con l'ordine
		$msg .= "Valorizzando i campi<ul><li><b>".__('Stato')."</b> a <span style=\"color:red\">No</span>, l'articolo <b>non</b> sarà visibile agli utenti</li><li><b>".__('FlagPresenteArticlesorders')."</b> a <span style=\"color:red\">No</span>, l'articolo <b>non</b> potrà essere associato agli ordini.";
	}
}
else 
if($context=='order') { 
	// mai!!
}
$msg .= '</div>';

echo $msg;
?>