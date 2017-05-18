<?php
if(($orderResult['Order']['state_code']=='OPEN' || $orderResult['Order']['state_code']=='RI-OPEN-VALIDATE') && 
   ($orderResult['Order']['qta_massima']>0 || $orderResult['Order']['importo_massimo']>0)) {
?>
	<div class="box-message" style="margin:0 20px;">

	<table cellpadding="0" cellspacing="0">
		<?php
		if($orderResult['Order']['qta_massima']>0) {
			
			/*
			echo '<br >qta_massima_um '.$orderResult['Order']['qta_massima_um'];
			echo '<br >qta_massima_current '.$orderResult['Order']['qta_massima_current'];
			echo '<br >qta_massima '.$orderResult['Order']['qta_massima'];
			*/
			
			if($orderResult['Order']['qta_massima_um']!='PZ')
				$totQuantita = ($orderResult['Order']['qta_massima_current']/1000);
			else
				$totQuantita = $orderResult['Order']['qta_massima_current'];
		?>	
		<tr>
			<th style="width:20%">Limite imposto alla <b>quantità</b>: <?php echo $orderResult['Order']['qta_massima'];?> <?php echo $orderResult['Order']['qta_massima_um'];?></th>
			<td>Quantità attualmente raggiunta: <b><?php echo $totQuantita;?></b> <?php echo $orderResult['Order']['qta_massima_um'];?></td>
		</tr>
		<?php
		}
		if($orderResult['Order']['importo_massimo']>0) {
		?>	
		<tr>
			<th style="width:20%">Limite imposto all'<b>importo</b>: <?php echo $orderResult['Order']['importo_massimo'];?> &euro;</th>
			<td>Importo attualmente raggiunto: <b><?php echo $orderResult['Order']['importo_massimo_current'];?></b> &euro;</td>
		</tr>
		<?php
		}
		?>
	</table>

	</div>
<?php
}
?>