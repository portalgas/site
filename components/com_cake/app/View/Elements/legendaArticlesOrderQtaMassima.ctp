<?php 
$msg = ""; 

if(!empty($desOrdersResults))
	$msg_des = " tra tutti i G.A.S. dell'ordine D.E.S.";
else
	$msg_des = '';

if($results['ArticlesOrder']['qta_cart']==0)
	$msg .= "Finora l'articolo <b>non</b> è stato ancora acquistato".$msg_des;
else
if($results['ArticlesOrder']['qta_cart']==1)
	$msg .= "Finora l'articolo è stato <b>acquistato ".$results['ArticlesOrder']['qta_cart']." volta</b>".$msg_des;
else
	$msg .= "Finora l'articolo è stato <b>acquistato ".$results['ArticlesOrder']['qta_cart']." volte</b>".$msg_des;

$msg .= "<br />Quando la quantità ordinata raggiungerà la quantità massima, l'articolo non potrà più essere acquistato ";
$msg .= '<span class="stato_lock">-</span>';

echo '<div class="legenda legenda-ico-info">';
echo $msg;
echo '</div>';
?>