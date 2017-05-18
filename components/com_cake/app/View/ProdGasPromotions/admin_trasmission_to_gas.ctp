<?php
/*
echo "<pre>Dati results \n";
print_r($results);
echo "</pre>";
echo "<pre>Dati results \n";
print_r($organizationResults);
echo "</pre>";
*/

$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List ProdGasPromotions'), array('controller' => 'ProdGasPromotions', 'action' => 'index'));
$this->Html->addCrumb(__('Edit ProdGasPromotion'), array('controller' => 'ProdGasPromotions', 'action' => 'edit', $prod_gas_promotion_id));
$this->Html->addCrumb(__('ProdGasPromotionTrasmissionToGas'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="orders form">';

echo $this->Form->create('ProdGasPromotion', array('type' => 'post'));
echo '<fieldset>';
echo '<legend>';
echo __('ProdGasPromotionTrasmissionToGas');
echo '</legend>';

echo '<table cellpadding="0" cellspacing="0">';
echo '<tr>';
echo '	<td>';
if(!empty($results['ProdGasPromotion']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_promotions').DS.$results['ProdGasPromotion']['supplier_id'].DS.$results['ProdGasPromotion']['img1'])) {
	echo '<img width="50" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.prod_gas_promotions').'/'.$results['ProdGasPromotion']['supplier_id'].'/'.$results['ProdGasPromotion']['img1'].'" />';
}			
echo '</td>';
echo '	<td>';
echo $results['ProdGasPromotion']['name'];
echo '</td>';	
echo '	<td><b>'.__('Data inizio').'</b><br />';
echo $this->Time->i18nFormat($results['ProdGasPromotion']['data_inizio'],"%A %e %B %Y");
echo '	</td>';
echo '	<td><b>'.__('Data fine').'</b><br /> ';
echo $this->Time->i18nFormat($results['ProdGasPromotion']['data_fine'],"%A %e %B %Y");
echo '	</td>';
echo '	<td><b>'.__('importo_scontato').'</b><br />';
echo '<span style="text-decoration: line-through;">'.$results['ProdGasPromotion']['importo_originale_e'].'</span><br />'.$results['ProdGasPromotion']['importo_scontato_e'];
echo '	</td>';
echo '</tr>';
echo '</table>';

if(!empty($organizationResults)) {
	
	echo '<h3>Trasmetterai la promozione ai gestori del G.A.S.</h3>';
		echo '<table cellpadding="0" cellspacing="0">';
		echo '<tr>';
		echo '<th colspan="2">'.__('Name').'</th>';
		echo '<th>Localit&agrave;</th>';
		echo '<th>'.__('Trasport').'</th>';
		echo '<th>'.__('CostMore').'</th>';
		echo '</tr>';
		
		foreach ($organizationResults as $result):
		
			if(isset($result['ProdGasPromotionsOrganization'])) {
			
				echo '<tr class="view" id="row-org-'.$result['ProdGasPromotionsOrganization']['id'].'">';
				
				echo '<td>';
				echo ' <img width="50" class="userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Organization']['img1'].'" alt="'.$result['Organization']['name'].'" /> ';	
				echo '</td>';
				
				echo '<td>';
					echo $result['Organization']['name']; 
					if(!empty($result['Organization']['descrizione'])) echo '<div class="small">'.$result['Organization']['descrizione'].'</div>';
				echo '</td>';
				echo '<td>';
					   if(!empty($result['Organization']['localita'])) echo $result['Organization']['localita'].'&nbsp;';
					   if(!empty($result['Organization']['cap'])) echo $result['Organization']['cap'].'&nbsp;';
					   if(!empty($result['Organization']['provincia'])) echo '('.h($result['Organization']['provincia']).')'; 
				echo '</td>';

				echo '<td>';
				echo $result['ProdGasPromotionsOrganization']['trasport_e'];
				echo '</td>';
				echo '<td>';
				echo $result['ProdGasPromotionsOrganization']['cost_more_e'];
				echo '</td>';	
				echo '</tr>';
			}
			
		endforeach;		
	echo '</table>';
}

echo '</fieldset>';
echo $this->Form->hidden('id',array('value' => $results['ProdGasPromotion']['prod_gas_promotion_id']));
echo $this->Form->end(__('ProdGasPromotionTrasmissionToGas'));
?>
</div>

<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List ProdGasPromotions'), array('controller' => 'ProdGasPromotions', 'action' => 'index'),array('class'=>'action actionReload'));?></li>
		<li><?php echo $this->Html->link(__('Edit ProdGasPromotion'), array('controller' => 'ProdGasPromotions', 'action' => 'edit', $prod_gas_promotion_id),array('class'=>'action actionEdit'));?></li>
	</ul>
	
</div>


<style type="text/css">
.cakeContainer div.form, .cakeContainer div.index, .cakeContainer div.view {
    width: 74%;
}
.cakeContainer div.actions {
    width: 25%;
}
</style>