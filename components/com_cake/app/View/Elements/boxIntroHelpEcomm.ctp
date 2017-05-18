<?php
echo '<div id="introHelp_'.$id.'" class="hidden-xs introHelp jumbotron" style="display:block">';
echo "\n";
echo "<h2>Istruzioni d'uso per gli acquisti</h2>";
echo "\n";
echo "<ol>";
echo "<li>Seleziona il produttore<br />";
echo '<img class="imgExample" alt="produttore di esempio" src="'.Configure::read('App.img.cake').'/help_fornitori.jpg" style="opacity: 0.5;" />';
echo "</li>";
echo "\n";
echo "<li>Vai sulla riga del prodotti da acquistare<br />";
echo '<img alt="riga di esempio" src="'.Configure::read('App.img.cake').'/help_riga.jpg" />';
echo "</li>";
echo "\n";
echo "<li>Aumenta ";
echo '<img alt="compra in +" src="'.Configure::read('App.img.cake').'/button-piu.png" />';
echo " o diminuisci ";
echo '<img alt="compra in -" src="'.Configure::read('App.img.cake').'/button-meno.png" />';
echo " la quantita&grave;</li>";
echo "\n";
echo "<li>Salva ";
echo '<img alt="salva" src="'.Configure::read('App.img.cake').'/apps/32x32/kfloppy.png" />';
echo "</li>";
echo "\n";
echo "<li>Ok, ";
echo '<img alt="ok" src="'.Configure::read('App.img.cake').'/actions/32x32/bookmark.png" />';
echo " l'articolo e&grave; stato aggiornato";
echo "</li>";
echo "</ol>";
echo '</div>';
?>
<script type="text/javascript">
jQuery( document ).ready(function() {
	jQuery('.imgExample').click(function() {
		alert("Sono solo un immagine d'esempio... il menù dei produttori è un pò più sopra!" );
		jQuery("[data-id='order_<?php echo $id;?>']").css('border', '1px solid red');
	});
});
</script>