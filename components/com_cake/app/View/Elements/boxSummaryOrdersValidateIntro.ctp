<?php
echo '<div class="legenda legenda-ico-info" style="float:none;">';
echo "PortAlGas aggrega gli importi degli acquisti dei gasisti <span class='tooltip-help-img' id='active_example_aggregate' style='cursor:pointer;' title='Clicca per visualizzare l'esempio'></span> quando";
?>
<div style="display:none;background-color: #fff;" id="example_aggregate">
	<h2>Esempio</h2>
	<table>
		<tr>
			<th>Gasista</th>
			<th>ha ordinato</th>
			<th>con l'importo</th>
			<th>Gestito la somma degli importi aggregandoli</th>
		</tr>
		<tr>
			<td rowspan="2">Rossi Mario</th>
			<td>2 orate</th>
			<td>10,00&nbsp;&euro;</th>
			<td rowspan="2">10,00&nbsp;&euro; + 5,00&nbsp;&euro; = <b>15,00</b>&nbsp;&euro;</td>
		</tr>
		<tr>
			<td>1 branzino</th>
			<td>5,00&nbsp;&euro;</th>
		</tr>
	</table>

</div>
<?php
echo "<h3>Si utilizza il modulo</h3>";
echo '<ul class="menuLateraleItems">';
echo '<li><div title="'.__('Management Carts Group By Users').'" class="bgLeft actionEditDbGroupByUsers">'.__('Management Carts Group By Users').'</div></li>';
echo '<li><div title="'.__('Management trasport').'" class="bgLeft actionTrasport">'.__('Management trasport').'</div></li>';
echo '<li><div title="'.__('Management cost_more').'" class="bgLeft actionCostMore">'.__('Management cost_more').'</div></li>';
echo '<li><div title="'.__('Management cost_less').'" class="bgLeft actionCostLess">'.__('Management cost_less').'</div></li>';
echo '</ul>';
echo "<h3>Si passa l'ordine</h3>";
echo '<ul class="menuLateraleItems">';
if($user->organization['Template']['payToDelivery']=='ON' || $user->organization['Template']['payToDelivery']=='ON-POST')
	echo '<li><div title="'.__('OrdersReferenteInProcessedOnDelivery').'" class="bgLeft actionFromRefToTes">'.__('OrdersReferenteInProcessedOnDelivery').'</div></li>';
if($user->organization['Template']['payToDelivery']=='POST' || $user->organization['Template']['payToDelivery']=='ON-POST')
	echo '<li><div title="'.__('OrdersReferenteInWaitProcessedTesoriere').'" class="bgLeft actionFromRefToTes">'.__('OrdersReferenteInWaitProcessedTesoriere').'</div></li>';
echo '</ul>';
echo '</div>';
?>


<script type="text/javascript">
$(document).ready(function() { 
	$('#active_example_aggregate').click(function() {
		if($('#example_aggregate').css('display')=='none')
			$('#example_aggregate').show();
		else
			$('#example_aggregate').hide();
	});
});
</script>