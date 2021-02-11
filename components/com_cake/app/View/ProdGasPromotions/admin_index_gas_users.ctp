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
			case "OPEN":
				echo $this->Html->link(null, ['controller' => 'ProdGasPromotions', 'action' => 'edit_gas_users', $result['ProdGasPromotion']['id']], ['class' => 'action actionEdit','title' => __('Edit')]);
				echo $this->Html->link(null, ['controller' => 'ProdGasPromotions', 'action' => 'delete', $result['ProdGasPromotion']['id'], null, 'type=GAS-USERS'], ['class' => 'action actionDelete','title' => __('Delete')]);				
			break;
			case "PRODGASPROMOTION-CLOSE":
				echo $this->Html->link(null, ['controller' => 'ProdGasPromotions', 'action' => 'view_gas_users', $result['ProdGasPromotion']['id']], ['class' => 'action actionView','title' => __('View')]);			
			break;
		}

		echo $this->Html->link(null, ['controller' => 'Docs', 'action' => 'ProdGasPromotionGasUsersDocsExport', $result['ProdGasPromotion']['id']], ['class' => 'action actionPrinter','title' => __('Print Promotion')]);		
		echo '</td>';		
		
		/*
		 * GAS
		 */
		if(isset($result['ProdGasPromotionsOrganization'])) {
		
			echo '<tr>';
			echo '	<th></th>';
			echo '	<th></th>';
			echo '	<th></th>';
			echo '	<th></th>';
			echo '	<th colspan="7">'.$this->Paginator->sort('organization_id').'</th>';
			echo '</tr>';
					
			foreach($result['ProdGasPromotionsOrganization'] as $numResult2 => $prodGasPromotionsOrganization) {

				echo '<tr>';
				echo '	<td></td>';
				echo '	<td></td>';
				echo '	<td></td>';
				echo '	<td></td>';
				echo '	<td>';
				if(!empty($prodGasPromotionsOrganization['Organization']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$prodGasPromotionsOrganization['Organization']['img1']))
					echo ' <img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$prodGasPromotionsOrganization['Organization']['img1'].'" alt="'.$prodGasPromotionsOrganization['Organization']['name'].'" /> ';
				echo '	</td>';
				echo '	<td colspan="6">'.$prodGasPromotionsOrganization['Organization']['name'].'</td>';				
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

});
</script>