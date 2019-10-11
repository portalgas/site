<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('ProdGasSupplier home'),array('controller' => 'ProdGasSuppliers', 'action' => 'index'));
$this->Html->addCrumb(__('ProdGasOrders'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="table-responsive"><table class="table">';
echo '<td style="width:50px;">';
echo '<img width="50" src="'.Configure::read('App.web.img.upload.content').'/'.$organizations['Organization']['img1'].'" alt="'.$organizations['Organization']['name'].'" />';
echo '</td>';
echo '<td><h3>'.$organizations['Organization']['name'].'</h3></td>';
echo '</table></div>';

echo '<div class="orders">';
echo '<h2 class="ico-orders">';		
echo __('Orders current');
echo '</h2>';

if(!empty($results)) {

	echo '<div class="table-responsive"><table class="table table-hover">';
	echo '<tr>';
	echo '	<th>'.__('N').'</th>';
	echo '	<th>';
	echo 		__('DataInizio');
	echo '	</th>';
	echo '	<th>';
	echo 		__('DataFine');
	echo '	</th>';
	echo '	<th>'.__('OpenClose').'</th>';
	echo '  <th>'.__('StatoElaborazione').'</th>';
	echo '	<th>'.__('Created').'</th>';
	echo '	<th class="actions">'.__('Actions').'</th>';
	echo '</tr>';
	
	$delivery_id_old = 0;
	foreach ($results as $numResult => $result) {

		if($delivery_id_old==0 || $delivery_id_old!=$result['Delivery']['id']) {
			
			echo '<tr><td class="trGroup" colspan="7">';
			
			if($result['Delivery']['sys']=='N') {
				echo __('Delivery').': '.$result['Delivery']['luogoData'];
				echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				if($result['Delivery']['daysToEndConsegna']<0) {
					echo '<span style="color:red;">Chiusa</span>';
				}
				else {
					echo '<span style="color:green;">Aperta ';
					if($result['Delivery']['daysToEndConsegna']==0) echo '(scade oggi)';
					else echo '(mancano ancora '.$result['Delivery']['daysToEndConsegna'].'&nbsp;gg&nbsp;alla&nbsp;consegna)';
					echo '</span>';
				}
			}
			else {
				echo __('Delivery').': '.h($result['Delivery']['luogo']);
			}
			echo '</td></tr>';
		}
		
		echo '<tr class="view">';
		echo '	<td>';
		echo ($numResult+1);
		if($result['Order']['prod_gas_promotion_id']>0)
			echo ' <span class="ico-order-is-prod_gas_promotion" title="'.__('OrderIsProdGasPromotion').'"></span>';
		echo '</td>';					
		echo '	<td style="white-space:nowrap;">';
		echo $this->Time->i18nFormat($result['Order']['data_inizio'],"%A %e %B %Y");
		echo '</td>';					
		echo '	<td style="white-space:nowrap;">';
		echo $this->Time->i18nFormat($result['Order']['data_fine'],"%A %e %B %Y");
		if($result['Order']['data_fine_validation']!=Configure::read('DB.field.date.empty'))
			echo '<br />Riaperto fino a '.$this->Time->i18nFormat($result['Order']['data_fine_validation'],"%A %e %B %Y");
		echo '	</td>';
		echo '	<td style="white-space:nowrap;">';
		echo $this->App->utilsCommons->getOrderTime($result['Order']);
		echo '	</td>';
		
		echo '<td>';		 
		echo $this->App->drawOrdersStateDiv($result);
		echo '&nbsp;';
	    echo __($result['Order']['state_code'].'-label');
		echo '</td>';
		
		echo '<td style="white-space: nowrap;">'.$this->App->formatDateCreatedModifier($result['Order']['created']).'</td>';
		
		echo '<td class="actions-table-img">';
		if($currentOrganization['SuppliersOrganization']['can_view_orders']=='Y' || $currentOrganization['SuppliersOrganization']['can_view_orders_users']=='Y') {
			/*
			 * il produttore non gestisce + li articoli associati agli ordini del GAS
			 * echo $this->Html->link(null, ['controller' => 'ArticlesOrders','action' => 'prodgas_index', null, 'organization_id='.$result['Order']['organization_id'].'&order_id='.$result['Order']['id']], ['class' => 'action actionEditCart','title' => __('Edit ArticlesOrder')]);
			 */
			echo $this->Html->link(null, ['controller' => 'Docs','action' => 'prodGasSupplierDocsExport', null, 'organization_id='.$result['Order']['organization_id'].'&delivery_id='.$result['Order']['delivery_id'].'&order_id='.$result['Order']['id']], ['class' => 'action actionPrinter','title' => __('Print Order')]);
		}
		echo '</td>';
		echo '</tr>';

	$delivery_id_old=$result['Delivery']['id'];
}

echo '</table></div>';
} 
else  
	echo $this->element('boxMsg',array('class_msg' => 'notice resultsNotFound', 'msg' => "Non ci sono ancora ordini registrati"));

echo '</div>';
?>