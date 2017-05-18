<?php
/*
echo "<pre>";
print_r($results);
echo "</pre>";
*/

if(Configure::read('App.root')!='/var/www/next.portalgas') {
	echo $this->element('boxMsg',array('class_msg' => 'notice resultsNotFonud', 'msg' => Configure::read('sys_function_not_implement')));
}
else {

echo '<div class="orders">';
echo '<h2 class="ico-bookmarkes-articles">';		
echo __('ProdGasPromotions');
echo '<div class="actions-img">';			
echo '	<ul>';
echo '		<li>'.$this->Html->link(__('New ProdGasPromotion'), array('action' => 'add'),array('class' => 'action actionAdd','title' => __('New ProdGasPromotion'))).'</li>';
echo '	</ul>';
echo '</div>';
echo '</h2>';

if(!empty($results)) {

	echo '<table cellpadding="0" cellspacing="0">';
	echo '<tr>';
	echo '	<th>'.__('N').'</th>';
	echo '	<th colspan="2">'.__('Name').'</th>';
	echo '	<th>';
	echo 		__('Data inizio');
	echo '		<br />';
	echo 		__('Data fine');
	echo '	</th>';
	echo '	<th>'.__('Aperto/Chiuso').'</th>';
	echo '  <th>'.__('stato_elaborazione').'</th>';
	echo '	<th>'.__('importo_scontato').'</th>';
	
	echo '	<th class="actions">'.__('Actions').'</th>';
	
	echo '	<th colspan="2">'.$this->Paginator->sort('organization_id').'</th>';
	echo '	<th>'.__('Trasport').'</th>';
	echo '	<th>'.__('CostMore').'</th>';
	echo '	<th>'.__('Order').'</th>';
	echo '  <th>'.__('stato_elaborazione').'</th>';
	echo '	<th class="actions">'.__('Actions').'</th>';	
	echo '</tr>';

	foreach ($results as $i => $result):

		$numRow = ((($this->Paginator->counter(array('format'=>'{:page}'))-1) * $SqlLimit) + $i+1); 
		
		if(isset($result['ProdGasPromotionsOrganization']))
			$rowspan = count($result['ProdGasPromotionsOrganization']);
		else
			$rowspan = 1;
		
		echo '<tr class="view">';
		echo '	<td rowspan="'.$rowspan.'">'.$numRow.'</td>';
		echo '	<td rowspan="'.$rowspan.'">';
		if(!empty($result['ProdGasPromotion']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_promotions').DS.$result['ProdGasPromotion']['supplier_id'].DS.$result['ProdGasPromotion']['img1'])) {
			echo '<img width="50" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.prod_gas_promotions').'/'.$result['ProdGasPromotion']['supplier_id'].'/'.$result['ProdGasPromotion']['img1'].'" />';
		}			
		echo '</td>';
		echo '	<td rowspan="'.$rowspan.'">';
		echo $result['ProdGasPromotion']['name'];
		echo '</td>';
						
		echo '	<td rowspan="'.$rowspan.'" style="white-space:nowrap;">';
		echo $this->Time->i18nFormat($result['ProdGasPromotion']['data_inizio'],"%A %e %B %Y").'<br />';
		echo $this->Time->i18nFormat($result['ProdGasPromotion']['data_fine'],"%A %e %B %Y");
		echo '	</td>';
		echo '	<td rowspan="'.$rowspan.'" style="white-space:nowrap;">';
		echo $this->App->utilsCommons->getOrderTime($result['ProdGasPromotion']);
		echo '	</td>';
		
		echo '<td rowspan="'.$rowspan.'">';		 
		echo $this->App->drawProdGasPromotionsStateDiv($result);

		echo '&nbsp;';
		echo __($result['ProdGasPromotion']['state_code'].'-label');
		echo '</td>';
		
		echo '	<td rowspan="'.$rowspan.'"><span style="text-decoration: line-through;">'.$result['ProdGasPromotion']['importo_originale_e'].'</span><br />'.$result['ProdGasPromotion']['importo_scontato_e'].'</td>';
		
		/*
		 * action su ProdGasPromotion
		 */
		echo '<td rowspan="'.$rowspan.'" class="actions-table-img-3">';
		switch($result['ProdGasPromotion']['state_code']) {
			case "WORKING":
				echo $this->Html->link(null, array('controller' => 'ProdGasPromotions', 'action' => 'edit', $result['ProdGasPromotion']['id']), array('class' => 'action actionEdit','title' => __('Edit')));
				echo $this->Html->link(null, array('controller' => 'ProdGasPromotions', 'action' => 'delete', $result['ProdGasPromotion']['id']), array('class' => 'action actionDelete','title' => __('Delete')));
				echo $this->Html->link(null, array('controller' => 'ProdGasPromotions', 'action' => 'trasmission_to_gas', $result['ProdGasPromotion']['id']), array('class' => 'action actionMail','title' => __('ProdGasPromotionTrasmissionToGas')));			
			break;
			case "TRASMISSION-TO-GAS":
				echo $this->Html->link(null, array('controller' => 'ProdGasPromotions', 'action' => 'change_state_code', $result['ProdGasPromotion']['id'], 'next_code=WORKING'), array('class' => 'action actionOpen','title' => __('ChangeStateProdGasPromotion')));
			break;
			case "FINISH":
			break;
			case "PRODGASPROMOTION-CLOSE":
			break;
		}
			
		echo '</td>';

		if(isset($result['ProdGasPromotionsOrganization'])) {
			foreach($result['ProdGasPromotionsOrganization'] as $numResult2 => $prodGasPromotionsOrganization) {

				if($numResult2>0)
					echo '<tr>';
				
				echo '	<td>';
				if(!empty($prodGasPromotionsOrganization['Organization']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$prodGasPromotionsOrganization['Organization']['img1']))
					echo ' <img width="50" class="userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$prodGasPromotionsOrganization['Organization']['img1'].'" alt="'.$prodGasPromotionsOrganization['Organization']['name'].'" /> ';
				echo '	</td>';
				echo '	<td>'.$prodGasPromotionsOrganization['Organization']['name'].'</td>';
				echo '	<td>'.$prodGasPromotionsOrganization['trasport_e'].'</td>';
				echo '	<td>'.$prodGasPromotionsOrganization['cost_more_e'].'</td>';
				
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
							echo $this->Html->link(null, array('controller' => 'Docs', 'action' => 'prodGasSupplierDocsExport', null, 'organization_id='.$prodGasPromotionsOrganization['Order']['organization_id'].'&delivery_id='.$prodGasPromotionsOrganization['Order']['delivery_id'].'&order_id='.$prodGasPromotionsOrganization['Order']['id']), array('class' => 'action actionPrinter','title' => __('Print Order')));
						break;
					}
				}
				echo '</td>';
		
				if($numResult2>0)
					echo '</tr>';				
			}					
		}
		else
			echo '	<td colspan="5"></td>';
		
		echo '</tr>';

endforeach; 

echo '</table>';

echo '<p>';
echo $this->Paginator->counter(array(
'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
));
echo '</p>';

echo '<div class="paging">';
echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
echo $this->Paginator->numbers(array('separator' => ''));
echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
echo '</div>';
	
/*
 * legenda profilata
*/
echo $this->App->drawLegenda($user, $prodGasPromotionStates);
} 
else  
	echo $this->element('boxMsg',array('class_msg' => 'notice resultsNotFonud', 'msg' => "Non ci sono ancora promozioni registrate"));

echo '</div>';
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery(".actionMenu").each(function () {
		jQuery(this).click(function() {

			jQuery('.menuDetails').css('display','none');
			
			var idRow = jQuery(this).attr('id');
			numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
			jQuery('#menuDetails-'+numRow).show();

			viewOrderSottoMenu(numRow,"bgLeft");

			var offset = jQuery(this).offset();
			var newTop = (offset.top - 100);
			var newLeft = (offset.left - 350);

			jQuery('#menuDetails-'+numRow).offset({ top: newTop, left: newLeft});			
		});
	});	

	jQuery(".menuDetailsClose").each(function () {
		jQuery(this).click(function() {
			var idRow = jQuery(this).attr('id');
			numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
			jQuery('#menuDetails-'+numRow).hide('slow');
		});
	});	

	jQuery('.referente_nota').click(function() {
		var id = jQuery(this).attr('id');
		jQuery("#dialog-msg-"+id ).dialog("open");
	});
});
</script>
<?php	
}