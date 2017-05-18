<?php 
/*
 * results dati articolo
 * isUserPermissionArticlesOrder se l'utente e' abilitato a gestire gli articoli associati agli ordini 
 * context article (order Article.stato sempre Y)
 */

$msg = '<div class="legenda legenda-ico-info" style="float:none;">';
if($context=='article') {
	if($isUserPermissionArticlesOrder) {   // l'utente gestisce l'associazione degli articoli con l'ordine
		$msg .= "Se si valorizza il campo Stato a <span style=\"color:green\">Si</span>, l'articolo potrà essere associato agli ordini.";
		$msg .= "<br />";
		$msg .= "Se si valorizza il campo Stato a <span style=\"color:red\">No</span>, l'articolo <b>non</b> potrà essere associato ad alcun ordine.";
	}
	else 
	if(!$isUserPermissionArticlesOrder)  {  // l'utente non gestisce l'associazione degli articoli con l'ordine
		$msg .= "Se si valorizza il campo Stato a <span style=\"color:green\">Si</span>, l'articolo sarà visibile dagli utenti e potrà essere acquistato.";
		$msg .= "<br />";
		$msg .= "Se si valorizza il campo Stato a <span style=\"color:red\">No</span>, l'articolo <b>non</b> sarà visibile dagli utenti e <b>non</b> potrà essere acquistato.";		
	}
}
else 
if($context=='order') { 
	// mai!!
}
$msg .= '</div>';

echo $msg;
?>