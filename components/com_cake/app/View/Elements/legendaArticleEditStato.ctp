<?php 
/*
 * results dati articolo
 * resultsAssociateArticlesOrder eventuali associazioni con gli ordini
 * isUserPermissionArticlesOrder se l'utente e' abilitato a gestire gli articoli associati agli ordini 
 * context article/order
 * 
 * se da stato.Y a stato.N 
 * 		cancello l'associazione con gli ordini
 * 		cancello gli acquisti effettuati
 * 
 * se da stato.N a stato.Y 
 * 		se isUserPermissionArticlesOrder
 * 			creo associazione con gli ordine
 * 		se !isUserPermissionArticlesOrder
 * 			lo potrò associare
 */

/*
 * messaggio articolo associato
 */
$msgAssociato = "L'articolo è associato ";if(count($resultsAssociateArticlesOrder)==1)	$msgAssociato .= "ad un <b>ordine</b>";else	$msgAssociato .= "a ".count($resultsAssociateArticlesOrder)." <b>ordini</b>";if($isArticleInCart>0)	$msgAssociato .= ' e sono già stati effettuati degli <b>acquisti</b>';$msgAssociato .= '.';


$msg = '<div class="legenda legenda-ico-info" style="float:none;">';
if($results['Article']['stato']=='Y') {
	if($context=='article') {
		if(empty($resultsAssociateArticlesOrder))  { // non esiste associazione con 1 o + ordini
			if($isUserPermissionArticlesOrder)       // l'utente gestisce l'associazione degli articoli con l'ordine
				$msg .= "Valorizzando i campi<ul><li><b>".__('Stato')."</b> a <span style=\"color:red\">No</span>, l'articolo <b>non</b> sarà visibile agli utenti</li><li><b>".__('FlagPresenteArticlesorders')."</b> a <span style=\"color:red\">No</span>, l'articolo <b>non</b> potrà essere associato agli ordini.";
			else 
			if(!$isUserPermissionArticlesOrder)     // l'utente non gestisce l'associazione degli articoli con l'ordine				$msg .= "Valorizzando i campi<ul><li><b>".__('Stato')."</b> a <span style=\"color:red\">No</span>, l'articolo <b>non</b> sarà visibile agli utenti</li><li><b>".__('FlagPresenteArticlesorders')."</b> a <span style=\"color:red\">No</span>, l'articolo <b>non</b> potrà essere associato agli ordini.";
		}
		else
		if(!empty($resultsAssociateArticlesOrder))  { // esiste associazione con 1 o + ordini
		
			if($isArticleInCart) {
				$msg .= $msgAssociato;
				$msg .= "<br />Al momento non è possibile settare lo stato a <span style=\"color:red\">No</span>";
			}
			else {
				$msg .= $msgAssociato;
				$msg .= "<br />Valorizzando i campi <ul><li>".__('Stato')."</li><li>".__('FlagPresenteArticlesorders')."</li></ul> a <span style=\"color:red\">No</span>, ";
				if($isUserPermissionArticlesOrder)  {    // l'utente gestisce l'associazione degli articoli con l'ordine
					$msg .= "l'articolo <b>non</b> sarà più acquistabile";
					if($isArticleInCart) $msg .= ' ma <b>non</b> saranno <b>cancellelati</b> gli acquisti degli utenti';
					
				}
				else 
				if(!$isUserPermissionArticlesOrder) {  // l'utente non gestisce l'associazione degli articoli con l'ordine
					$msg .= "l'articolo <b>non</b> sarà più acquistabile";
					if($isArticleInCart) $msg .= ' ma <b>non</b> saranno <b>cancellelati</b> gli acquisti degli utenti';
				}			
			}		}			 
	}
	else 
	if($context=='order') {		if(empty($resultsAssociateArticlesOrder))  { // non esiste associazione con 1 o + ordini
			// mai!!!
		}
		else
		if(!empty($resultsAssociateArticlesOrder))  { // esiste associazione con 1 o + ordini
		
			if($isArticleInCart) {
				$msg .= $msgAssociato;
				$msg .= "<br />Al momento non è possibile settare lo stato a <span style=\"color:red\">No</span>";
			}
			else {		
				$msg .= $msgAssociato;				$msg .= "<br />Valorizzando i campi <ul><li>".__('Stato')."</li><li>".__('FlagPresenteArticlesorders')."</li></ul> a <span style=\"color:red\">No</span>, ";				if($isUserPermissionArticlesOrder)  {    // l'utente gestisce l'associazione degli articoli con l'ordine					$msg .= "l'articolo <b>non</b> sarà più acquistabile";					if($isArticleInCart) $msg .= ' ma <b>non</b> saranno <b>cancellelati</b> gli acquisti degli utenti';								}				else					if(!$isUserPermissionArticlesOrder) {  // l'utente non gestisce l'associazione degli articoli con l'ordine					$msg .= "l'articolo <b>non</b> sarà più acquistabile";					if($isArticleInCart) $msg .= ' ma <b>non</b> saranno <b>cancellelati</b> gli acquisti degli utenti';				}	
			}
		}
	}			
}
else 
if($results['Article']['stato']=='N') {	if($context=='article') {
		
		$msg .= "Valorizzando i campi <ul><li>".__('Stato')."</li><li>".__('FlagPresenteArticlesorders')."</li></ul> a <span style=\"color:green\">Si</span>, ";
				if($isUserPermissionArticlesOrder)       // l'utente gestisce l'associazione degli articoli con l'ordine			$msg .= "l'articolo potrà essere associato agli ordini.";		else		if(!$isUserPermissionArticlesOrder)     // l'utente non gestisce l'associazione degli articoli con l'ordine			$msg .= "l'articolo sarà visibile dagli utenti e potrà essere acquistato.";	}	else	if($context=='order') {
		// mai!!	}}
$msg .= '</div>';

echo $msg;
?>