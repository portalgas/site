<?php
echo $this->Html->script('genericBackOfficeGasDes.min');

$this->App->d($results);

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('Des'),array('controller' => 'Des', 'action' => 'index'));
$this->Html->addCrumb(__('List DesOrders'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="orders">';
echo '<h2 class="ico-orders">';		
echo __('DesOrders');
echo '<div class="actions-img">';			
echo '	<ul>';
if($isTitolareDesSupplier)
	echo '<li>'.$this->Html->link(__('New DesOrder'), array('action' => 'add'),array('class' => 'action actionAdd','title' => __('New DesOrder'))).'</li>';
echo '	</ul>';
echo '</div>';
echo '</h2>';


if(!empty($results)) {

	echo '<div class="table-responsive"><table class="table table-hover">';
	echo '<tr>';
	echo '<th>'.__('N').'</th>';
	echo '<th colspan="2">'.__('Supplier').'</th>';
	echo '<th colspan="2">'.__('OwnOrganization').'</th>';
	echo '<th>'.__('DesDelivery').'</th>';
	echo '<th>'.__('DataFineMax').'</th>';
	echo '<th>'.__('Orders').'</th>';
	echo '<th>'.__('StatoElaborazione').'</th>';			
	echo '<th class="actions">'.__('Actions').'</th>';
	echo '</tr>';

	foreach ($results as $numResult => $result) {

		echo '<tr class="view-2">';
		echo '<td>'.((int)$numResult+1).'</td>';
		
		echo '<td>';
		if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
			echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" />';	
		echo '</td>';			
		echo '<td>'.$result['Supplier']['name'];
		echo '</td>';
		/*
		echo '<td>';
		echo $result['Supplier']['descrizione'];
		echo '</td>';
		*/
		echo '<td>';
		echo '<img width="50" src="'.Configure::read('App.web.img.upload.content').'/'.$result['OwnOrganization']['img1'].'" alt="'.$result['OwnOrganization']['name'].'" />';
		echo '</td>';
		echo '<td>'.$result['OwnOrganization']['name'].'</td>';

		echo '<td>';
		echo $result['DesOrder']['luogo'];
		echo '</td>';	
		echo '<td>';
		echo $this->Time->i18nFormat($result['DesOrder']['data_fine_max'],"%A %e %B %Y");
		echo '</td>';

		echo '<td>';
		echo count($result['DesOrdersOrganizations']);
		echo '</td>';
		
		echo '<td>';
		echo $this->App->drawDesOrdersStateDiv($result);
		echo '&nbsp;';
		echo __($result['DesOrder']['state_code'].'-label');
		echo '</td>';
		
	
		echo '<td class="actions-table-img-3">';
		if($result['DesOrder']['isTitolareDesSupplier']) {
			echo $this->Html->link(null, ['controller' => 'DesOrders', 'action' => 'edit', $result['DesOrder']['id']], ['class' => 'action actionEdit','title' => __('Edit DesOrder')]);
			echo $this->Html->link(null, ['controller' => 'DesOrders', 'action' => 'delete', $result['DesOrder']['id']], ['class' => 'action actionDelete','title' => __('Delete')]);
			echo $this->Html->link(null, ['controller' => 'DesOrdersOrganizations', 'action' => 'index', $result['DesOrder']['id']], ['class' => 'action actionDes','title' => __('List DesOrdersOrganizations')]);
		}
		else
			echo $this->Html->link(__('List DesOrdersOrganizations'), ['controller' => 'DesOrdersOrganizations', 'action' => 'index', $result['DesOrder']['id']], ['class' => 'btn btn-primary', 'title' => __('List DesOrdersOrganizations')]);		
		/*
		echo '<a id="actionMenu-'.$result['DesOrder']['id'].'" class="action actionMenu" title="'.__('Expand menu').'"></a>';
		echo '<div class="menuDetails" id="menuDetails-'.$result['DesOrder']['id'].'" style="display:none;">';
		echo '	<a class="menuDetailsClose" id="menuDetailsClose-'.$result['DesOrder']['id'].'"></a>';
		echo '<div id="des-order-sotto-menu-'.$result['DesOrder']['id'].'"></div>';
		echo '</div>';
		
		echo $this->Html->link(null, array('controller' => 'SummaryDesOrders', 'action' => 'index', $result['DesOrder']['id']), array('class' => 'action actionEditDbGroupByUsers','title' => __('Management Des Order Group By Gas')));

		echo $this->Html->link(null, array('controller' => 'SummaryDesOrders', 'action' => 'index', $result['DesOrder']['id']), array('class' => 'action actionTrasport','title' => __('Management Des trasport')));
		echo $this->Html->link(null, array('controller' => 'SummaryDesOrders', 'action' => 'index', $result['DesOrder']['id']), array('class' => 'action actionCostMore','title' => __('Management Des cost_less')));
		echo $this->Html->link(null, array('controller' => 'SummaryDesOrders', 'action' => 'index', $result['DesOrder']['id']), array('class' => 'action actionCostLess','title' => __('Management Des cost_more')));

		echo $this->Html->link(null, array('controller' => 'DesOrders', 'action' => 'index', $result['DesOrder']['id']), array('class' => 'action actionReload','title' => __('ChangeStateDesOrder')));
		*/
		echo '</td>';
	echo '</tr>';


/* **************************** 
 codice ripetuto in DesOrdersOrganization::index
 
	echo '<tr>';
	echo '<td colspan="2">';
	echo '<td colspan="11">';
	
	if(!empty($result['DesOrdersOrganizations'])) {

		echo '<table>';
		echo '<tr>';
		echo '<th>'.__('N').'</th>';
		echo '<th colspan="3">'.__('GasOrganizations').'</th>';
		echo '<th>'.__('DataInizio').'</th>';
		echo '<th>'.__('DataFine').'</th>';
		echo '<th>'.__('OpenClose').'</th>';
		echo '<th>'.__('StatoElaborazione').'</th>';	
		echo '</tr>';
	
		foreach ($result['DesOrdersOrganizations'] as $numResult2 => $resultDesOrdersOrganization) {
		
			echo '<tr class="view-2">';
			echo '<td>'.($numResult2+1).'</td>';
			
			echo '<td>';
			echo '<img width="50" src="'.Configure::read('App.web.img.upload.content').'/'.$resultDesOrdersOrganization['Organization']['img1'].'" alt="'.$resultDesOrdersOrganization['Organization']['name'].'" />';
			echo '</td>';
			echo '<td colspan="2">'.$resultDesOrdersOrganization['Organization']['name'].'</td>';
			
			if(!empty($resultDesOrdersOrganization['Order']['id'] )) {
				echo '	<td style="white-space:nowrap;">';
				echo $this->Time->i18nFormat($resultDesOrdersOrganization['Order']['data_inizio'],"%A %e %B %Y").'<br />';
				echo '	</td>';
				
				echo '	<td style="white-space:nowrap;">';
				echo $this->Time->i18nFormat($resultDesOrdersOrganization['Order']['data_fine'],"%A %e %B %Y");
				if($resultDesOrdersOrganization['Order']['data_fine_validation']!=Configure::read('DB.field.date.empty'))
					echo '<br />Riaperto fino a '.$this->Time->i18nFormat($resultDesOrdersOrganization['Order']['data_fine_validation'],"%A %e %B %Y");
				echo '	</td>';
				
				echo '	<td style="white-space:nowrap;">';
				echo $this->App->utilsCommons->getOrderTime($resultDesOrdersOrganization['Order']);
				echo '	</td>';
		
				echo '<td>';
				echo $this->App->drawOrdersStateDiv($resultDesOrdersOrganization);
				echo '&nbsp;';
				echo __($resultDesOrdersOrganization['Order']['state_code'].'-label');
				echo '</td>';	
			}
			else {
				echo '<td></td>';
				echo '<td></td>';
				echo '<td></td>';
				echo '<td></td>';
			}
			
			echo '</tr>';
			
		} // loop DesOrdersOrganizations
		
		echo '</table>';
	} 
	else {
		echo '<div class="box-message"><div class="message">L\'ordine condiviso non ha ordini associati del GAS!</div></div>';
	}
	echo '</td>';
	echo '<td></td>';
	echo '</tr>';

**************************** */

	} // ed loops

	echo '</table></div>';
		
} 
else  
	echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => "Non ci sono ancora ordini registrati"));


/*
 * legenda profilata
*/
echo $this->App->drawLegenda($user, $desOrderStatesToLegenda);

	
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

			viewDesOrderSottoMenu(numRow,"bgLeft");

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

	$('.actionDelete').click(function() {

		if(!confirm("Sei sicuro di voler eliminare definitivamente l'ordine condiviso?")) {
			return false;
		}		
		return true;
	});		
});
</script>