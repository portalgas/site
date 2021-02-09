<?php
$this->App->d($results);

echo '<div class="orders">';
echo '<h2 class="ico-bookmarkes-articles">';		
echo __('ProdGasPromotions'.$type);
echo '<div class="actions-img">';			
echo '	<ul>';
if($type=='GAS')
	echo '<li>'.$this->Html->link(__('New ProdGasPromotion'), ['action' => 'add_gas', null], ['class' => 'action actionAdd','title' => __('New ProdGasPromotion')]).'</li>';
if($type=='GAS-USERS')
	echo '<li>'.$this->Html->link(__('New ProdGasPromotion'), ['action' => 'add_gas_users', null], ['class' => 'action actionAdd','title' => __('New ProdGasPromotion')]).'</li>';
echo '	</ul>';
echo '</div>';
echo '</h2>';

if(!empty($results)) {

	foreach ($results as $i => $result) {

		echo '<div class="table-responsive"><table class="table table">';
		echo '<tr>';
		echo '	<th>'.__('N').'</th>';
		echo '	<th colspan="4">'.__('Name').'</th>';
		echo '	<th>'.__('DataInizio').'</th>';
		echo '	<th>'.__('DataFine').'</th>';
		echo '	<th>'.__('OpenClose').'</th>';
		echo '  <th>'.__('StatoElaborazione').'</th>';
		echo '	<th>'.__('Importo_scontato').'</th>';
		echo '	<th class="actions">'.__('Actions').'</th>';
		echo '</tr>';

		$numRow = ((($this->Paginator->counter(['format'=>'{:page}'])-1) * $SqlLimit) + $i+1); 
		
		echo '<tr class="view">';
		echo '	<td>'.$numRow.'</td>';
		echo '	<td>';
		/*
		if(!empty($result['ProdGasPromotion']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_promotions').DS.$result['ProdGasPromotion']['supplier_id'].DS.$result['ProdGasPromotion']['img1'])) {
			echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.prod_gas_promotions').'/'.$result['ProdGasPromotion']['supplier_id'].'/'.$result['ProdGasPromotion']['img1'].'" />';
		}
		*/		
		echo '</td>';
		echo '	<td colspan="3">';
		echo $result['ProdGasPromotion']['name'];
		echo '</td>';
						
		echo '	<td style="white-space:nowrap;">'.$this->Time->i18nFormat($result['ProdGasPromotion']['data_inizio'],"%A %e %B %Y").'</td>';
		echo '	<td style="white-space:nowrap;">'.$this->Time->i18nFormat($result['ProdGasPromotion']['data_fine'],"%A %e %B %Y").'</td>';
		echo '	<td style="white-space:nowrap;">';
		echo $this->App->utilsCommons->getOrderTime($result['ProdGasPromotion']);
		echo '	</td>';
		
		echo '<td>';		 
		echo $this->App->drawProdGasPromotionsStateDiv($result);
		echo '&nbsp;';
		echo __($result['ProdGasPromotion']['state_code'].'-label');
		echo '</td>';
		
		echo '	<td><span style="text-decoration: line-through;">'.$result['ProdGasPromotion']['importo_originale_e'].'</span><br />'.$result['ProdGasPromotion']['importo_scontato_e'].'</td>';
		
		/*
		 * action su ProdGasPromotion
		 */
		echo '<td class="actions-table-img-3">';
		switch($result['ProdGasPromotion']['state_code']) {
			case "WORKING":
				echo $this->Html->link(null, ['controller' => 'ProdGasPromotions', 'action' => 'edit', $result['ProdGasPromotion']['id']], ['class' => 'action actionEdit','title' => __('Edit')]);
				echo $this->Html->link(null, ['controller' => 'ProdGasPromotions', 'action' => 'delete', $result['ProdGasPromotion']['id']], ['class' => 'action actionDelete','title' => __('Delete')]);
				echo $this->Html->link(null, ['controller' => 'ProdGasPromotions', 'action' => 'trasmission_to_gas', $result['ProdGasPromotion']['id']], ['class' => 'action actionMail','title' => __('ProdGasPromotionTrasmissionToGas')]);			
			break;
			case "TRASMISSION-TO-GAS":
				echo $this->Html->link(null, ['controller' => 'ProdGasPromotions', 'action' => 'view', $result['ProdGasPromotion']['id']], ['class' => 'action actionView','title' => __('View')]);			
				echo $this->Html->link(null, ['controller' => 'ProdGasPromotions', 'action' => 'change_state_code', $result['ProdGasPromotion']['id'], 'next_code=WORKING'], ['class' => 'action actionOpen','title' => __('ChangeStateProdGasPromotion')]);
			break;
			case "FINISH":
				echo $this->Html->link(null, ['controller' => 'ProdGasPromotions', 'action' => 'view', $result['ProdGasPromotion']['id']], ['class' => 'action actionView','title' => __('View')]);			
			break;
			case "PRODGASPROMOTION-CLOSE":
				echo $this->Html->link(null, ['controller' => 'ProdGasPromotions', 'action' => 'view', $result['ProdGasPromotion']['id']], ['class' => 'action actionView','title' => __('View')]);			
			break;
		}
			
		echo '</td>';

		if(isset($result['ProdGasPromotionsOrganization'])) {
		
			echo '<tr>';
			echo '	<th></th>';
			echo '	<th></th>';
			echo '	<th colspan="2">'.$this->Paginator->sort('organization_id').'</th>';
			echo '	<th>'.__('Trasport').'</th>';
			echo '	<th>'.__('CostMore').'</th>';
			echo '	<th>'.__('ProdGasPromotionNotaSupplier').'</th>';
			echo '	<th>'.__('ProdGasPromotionNotaUser').'</th>';
			echo '	<th>'.__('Order').'</th>';
			echo '  <th>'.__('StatoElaborazione').'</th>';
			echo '	<th class="actions">'.__('Actions').'</th>';	
			echo '</tr>';
					
			foreach($result['ProdGasPromotionsOrganization'] as $numResult2 => $prodGasPromotionsOrganization) {

				echo '<tr>';
				echo '	<td></td>';
				echo '	<td></td>';
				echo '	<td>';
				if(!empty($prodGasPromotionsOrganization['Organization']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$prodGasPromotionsOrganization['Organization']['img1']))
					echo ' <img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$prodGasPromotionsOrganization['Organization']['img1'].'" alt="'.$prodGasPromotionsOrganization['Organization']['name'].'" /> ';
				echo '	</td>';
				echo '	<td>'.$prodGasPromotionsOrganization['Organization']['name'].'</td>';
				echo '	<td>'.$prodGasPromotionsOrganization['trasport_e'].'</td>';
				echo '	<td>'.$prodGasPromotionsOrganization['cost_more_e'].'</td>';
							
				/*
				 *  campo nota_supplier
				 */
				echo '<td class="hidden-xs">';
				if(!empty($prodGasPromotionsOrganization['nota_supplier'])) {
					
					echo '<button type="button" class="btn btn-info" data-toggle="modal" data-target="#nota_supplier'.$prodGasPromotionsOrganization['id'].'"><i class="fa fa-2x fa-info-circle" aria-hidden="true"></i></button>';
					echo '<div id="nota_supplier'.$prodGasPromotionsOrganization['id'].'" class="modal fade" role="dialog">';
					echo '<div class="modal-dialog">';
					echo '<div class="modal-content">';
					echo '<div class="modal-header">';
					echo '<button type="button" class="close" data-dismiss="modal">&times;</button>';
					echo '<h4 class="modal-title">'.__('ProdGasPromotionNotaFromSupplier').'</h4>';
					echo '</div>';
					echo '<div class="modal-body"><p>'.$prodGasPromotionsOrganization['nota_supplier'].'</p>';
					echo '</div>';
					echo '<div class="modal-footer">';
					echo '<button type="button" class="btn btn-primary" data-dismiss="modal">'.__('Close').'</button>';
					echo '</div>';
					echo '</div>';
					echo '</div>';
					echo '</div>';			
					
				} // end if(!empty($prodGasPromotionsOrganization['nota_supplier']))	
				echo '</td>';
			
				/*
				 *  campo nota_user
				 */
				echo '<td class="hidden-xs">';
				if(!empty($prodGasPromotionsOrganization['nota_user'])) {
					
					echo '<button type="button" class="btn btn-info" data-toggle="modal" data-target="#nota_user'.$prodGasPromotionsOrganization['id'].'"><i class="fa fa-2x fa-info-circle" aria-hidden="true"></i></button>';
					echo '<div id="nota_user'.$prodGasPromotionsOrganization['id'].'" class="modal fade" role="dialog">';
					echo '<div class="modal-dialog">';
					echo '<div class="modal-content">';
					echo '<div class="modal-header">';
					echo '<button type="button" class="close" data-dismiss="modal">&times;</button>';
					echo '<h4 class="modal-title">'.__('ProdGasPromotionNotaUser').'</h4>';
					echo '</div>';
					echo '<div class="modal-body"><p>'.$prodGasPromotionsOrganization['nota_user'].'</p>';
					echo '</div>';
					echo '<div class="modal-footer">';
					echo '<button type="button" class="btn btn-primary" data-dismiss="modal">'.__('Close').'</button>';
					echo '</div>';
					echo '</div>';
					echo '</div>';
					echo '</div>';			
					
				} // end if(!empty($prodGasPromotionsOrganization['nota_user']))	
				echo '</td>'; 

				if(isset($prodGasPromotionsOrganization['Order']) && !empty($prodGasPromotionsOrganization['Order'])) {	
					echo '<td>';
					echo $prodGasPromotionsOrganization['Order']['data_inizio_'].'<br />';
					echo $prodGasPromotionsOrganization['Order']['data_fine_'];
					echo '</td>';
					echo '<td>'.__($prodGasPromotionsOrganization['Order']['state_code'].'-label').'</td>';
				}
				else
					echo '<td colspan="2">Non ancora creato</td>';
				
				echo '<td class="actions-table-img">';
				if(isset($prodGasPromotionsOrganization['Order']) && !empty($prodGasPromotionsOrganization['Order'])) {
					switch($result['ProdGasPromotion']['state_code']) {
						case "WORKING":
						break;
						case "TRASMISSION-TO-GAS":
						case "FINISH":
						case "PRODGASPROMOTION-CLOSE":
							echo $this->Html->link(null, ['controller' => 'Docs', 'action' => 'prodGasSupplierDocsExport', null, 'organization_id='.$prodGasPromotionsOrganization['Order']['organization_id'].'&delivery_id='.$prodGasPromotionsOrganization['Order']['delivery_id'].'&order_id='.$prodGasPromotionsOrganization['Order']['id']], ['class' => 'action actionPrinter','title' => __('Print Order')]);
						break;
					}
				}
				echo '</td>';		
				echo '</tr>';				
			}					
		}
		
		echo '</tr>';
		echo '</table></div>';
}

echo '<p>';
echo $this->Paginator->counter([
'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
]);
echo '</p>';

echo '<div class="paging">';
echo $this->Paginator->prev('< ' . __('previous'), [], null, ['class' => 'prev disabled']);
echo $this->Paginator->numbers(['separator' => '']);
echo $this->Paginator->next(__('next') . ' >', [], null, ['class' => 'next disabled']);
echo '</div>';
	
/*
 * legenda profilata
*/
echo $this->App->drawLegenda($user, $prodGasPromotionStates);
} 
else  
	echo $this->element('boxMsg', ['class_msg' => 'notice resultsNotFound', 'msg' => "Non ci sono ancora promozioni registrate"]);

echo '</div>';
?>
<script type="text/javascript">
$(document).ready(function() {
	$(".actionMenu").each(function () {
		$(this).click(function() {

			$('.menuDetails').css('display','none');
			
			var idRow = $(this).attr('id');
			numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
			$('#menuDetails-'+numRow).show();

			viewOrderSottoMenu(numRow,"bgLeft");

			var offset = $(this).offset();
			var newTop = (offset.top - 100);
			var newLeft = (offset.left - 350);

			$('#menuDetails-'+numRow).offset({ top: newTop, left: newLeft});			
		});
	});	

	$(".menuDetailsClose").each(function () {
		$(this).click(function() {
			var idRow = $(this).attr('id');
			numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
			$('#menuDetails-'+numRow).hide('slow');
		});
	});	

	$('.referente_nota').click(function() {
		var id = $(this).attr('id');
		$("#dialog-msg-"+id ).modal();
	});
});
</script>