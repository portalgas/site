<?php 
echo '<label for="order_id">Ordini</label> ';
echo '<div>';

if (!empty($results['Order'])) {
?>
	<div class="table-responsive"><table class="table table-hover">
	<tr>
		<th></th>
		<th colspan="2"><?php echo __('Supplier'); ?></th>
		<th>
			<?php echo __('DataInizio');?><br />
			<?php echo __('DataFine');?>
		</th>
		<th><?php echo __('Referenti'); ?></th>
		<th colspan="3"><?php echo __('Fattura'); ?></th>
		<th style="width:10px"></th>
		<th style="text-align:center">Utenti da passare<br />al tesoriere</th>
		<th><?php echo __('Actions'); ?></th>
	</tr>
	<?php
		$tot_orders = 0;
		foreach ($results['Order'] as $numResult => $order):

			/*
			 * totalSummaryOrderNotPaid (Utenti da passare al tesoriere): totale di tutti i SummaryOrder che non sono stati pagati, se tutti pagati non e' da passare al Tesoriere
			 */
			if($order['PaidUsers']['totalSummaryOrderNotPaid']>0) {
			
				$tot_orders++;
				
				echo '<tr>';
				
				echo '<td>';
				echo '<a action="cassiere_orders-'.$order['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a>';
				echo '</td>';
				
				echo '<td>'; 
				if(!empty($order['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$order['Supplier']['img1']))
					echo ' <img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$order['Supplier']['img1'].'" alt="'.$order['SupplierOrganization']['name'].'" /> ';	
				echo '</td>';
				?>
				<td><?php echo $order['SuppliersOrganization']['name']; ?></td>
				<td style="white-space:nowrap;">
					<?php echo $this->Time->i18nFormat($order['data_inizio'], "%e %b %Y"); ?><br />
					<?php echo $this->Time->i18nFormat($order['data_fine'], "%e %b %Y"); ?>
				</td>
				<?php
				echo '<td>';
				if(isset($order['SuppliersOrganizationsReferent'])) // deve sempre esistere!
					echo $this->App->drawListSuppliersOrganizationsReferents($user, $order['SuppliersOrganizationsReferent']);
				else 
					echo "Nessun referente associato!";
				?>
				</td>
				<?php 
				echo '<td>';
				if(!empty($order['tesoriere_doc1']) && file_exists(Configure::read('App.root').Configure::read('App.doc.upload.tesoriere').DS.$user->organization['Organization']['id'].DS.$order['tesoriere_doc1'])) {
					$ico = $this->App->drawDocumentIco($order['tesoriere_doc1']);
					echo '<a alt="Scarica il documento" title="Scarica il documento" href="'.Configure::read('App.server').Configure::read('App.web.doc.upload.tesoriere').'/'.$user->organization['Organization']['id'].'/'.$order['tesoriere_doc1'].'" target="_blank"><img src="'.$ico.'" /></a>';
				}
				else
					echo "";

				echo '</td>';
				
				echo '<td>';
				if(!empty($order['tesoriere_nota'])) {
								
					echo '<button type="button" class="btn btn-info" data-toggle="modal" data-target="#order_nota_'.$order['id'].'"><i class="fa fa-2x fa-info-circle" aria-hidden="true"></i></button>';
					echo '<div id="order_nota_'.$order['id'].'" class="modal fade" role="dialog">';
					echo '<div class="modal-dialog">';
					echo '<div class="modal-content">';
					echo '<div class="modal-header">';
					echo '<button type="button" class="close" data-dismiss="modal">&times;</button>';
					echo '<h4 class="modal-title">Nota del referente</h4>';
					echo '</div>';
					echo '<div class="modal-body"><p>'.$result['tesoriere_nota'].'</p>';
					echo '</div>';
					echo '<div class="modal-footer">';
					echo '<button type="button" class="btn btn-primary" data-dismiss="modal">'.__('Close').'</button>';
					echo '</div>';
					echo '</div>';
					echo '</div>';
					echo '</div>';
						
				} // end if(!empty($order['tesoriere_nota']))
				echo '</td>';
				echo '<td style="text-align="center;">';
				if($order['tesoriere_fattura_importo']>0)
					echo '<b>'.__('Tesoriere fattura importo Short').'</b> '.number_format($order['tesoriere_fattura_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
				if($order['tot_importo']>0)
					echo '<br /><b>'.__('Importo totale ordine Short').'</b> '.number_format($order['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
				echo '</td>';
				echo '<td style="background-color:';
				if($order['tot_importo']=='0.00') echo '#fff';
				else
				if($order['tot_importo']==$order['tesoriere_fattura_importo']) echo '#fff';
				else 
				if($order['tot_importo']<$order['tesoriere_fattura_importo']) echo 'red';
				else 
				if($order['tot_importo']>$order['tesoriere_fattura_importo']) echo '#006600';
					
				echo '"></td>';
				
				echo '<td style="text-align:center">'.$order['PaidUsers']['totalSummaryOrderNotPaid'].'</td>';
				
				echo '<td class="actions-table-img-4">';
				echo $this->Html->link(null, array('action' => 'order_state_in_WAIT_PROCESSED_TESORIERE', null, 'delivery_id='.$order['delivery_id'].'&order_id='.$order['id']),array('class' => 'action actionFromRefToTes','title' => __('OrdersReferenteInWaitProcessedTesoriere'))); 
				echo '</td>';
			echo '</tr>';
			
			echo '<tr>';
					
			echo '<tr class="trView" id="trViewId-'.$order['id'].'">';
			echo '	<td colspan="2"></td>'; 
			echo '	<td colspan="9" id="tdViewId-'.$order['id'].'"></td>';
			echo '</tr>';
			
		} // end if($order['PaidUsers']['totalSummaryOrderNotPaid']>0) 			
	endforeach;

	echo '</table></div>';
	
	if($tot_orders==0) 
		echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Gli ordini della consegna ha tutti gli importi dei gasisti pagati"));
}	
else { 
	echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Non ci sono ordini associati")); 
}
?>
<script type="text/javascript">
$(document).ready(function() {

	$('.actionTrView').css('display','inline-block');  /* rendo visibile il tasto espandi per i dettagli ajax */
	
	$('.actionTrView').each(function () {
		actionTrView(this);
	});	
});
</script>

</div>