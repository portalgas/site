<?php 
$msg = ""; 

if($results['ArticlesOrder']['qta_cart']==0)
	$msg .= "Finora l'articolo <b>non</b> è stato ancora acquistato";
else
if($results['ArticlesOrder']['qta_cart']==1)	$msg .= "Finora l'articolo è stato <b>acquistato ".$results['ArticlesOrder']['qta_cart']." volta</b>";
else
	$msg .= "Finora l'articolo è stato <b>acquistato ".$results['ArticlesOrder']['qta_cart']." volte</b>";

$msg .= "<br />Se la quantità massima dovesse essere inferiore alla quantità ordinata, l'articolo non potrà più essere acquistato ";
$msg .= '<span class="stato_lock">-</span>';

echo '<div class="legenda legenda-ico-info">';
echo $msg;
echo '</div>';
?>