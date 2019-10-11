<?php
$this->App->d([$results, $organizationResults]);

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List ProdGasPromotions'), array('controller' => 'ProdGasPromotions', 'action' => 'index'));
$this->Html->addCrumb(__('Edit ProdGasPromotion'), array('controller' => 'ProdGasPromotions', 'action' => 'edit', $prod_gas_promotion_id));
$this->Html->addCrumb(__('ProdGasPromotionTrasmissionToGas'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="orders form">';

echo $this->Form->create('ProdGasPromotionsOrganization', ['type' => 'post']);
echo '<fieldset>';
echo '<legend>';
echo __('ProdGasPromotionTrasmissionToGas');
echo '</legend>';

echo $this->Element('boxProdGasPromotionOrganizations', ['results' => $results]);

if(!empty($organizationResults)) {
	
		echo '<h3>Trasmetterai la promozione ai referenti del G.A.S.</h3>';
		echo '<div class="table-responsive"><table class="table table-hover">';
		echo '<tr>';
		echo '<th colspan="2">'.__('Name').'</th>';
		echo '<th>Localit&agrave;</th>';
		echo '<th>'.__('Trasport').'</th>';
		echo '<th>'.__('CostMore').'</th>';
		echo '<th>'.__('Nota').'</th>';
		echo '</tr>';
		
		foreach ($organizationResults as $result) {
		
			if(isset($result['ProdGasPromotionsOrganization'])) {
			
				echo '<tr class="view" id="row-org-'.$result['ProdGasPromotionsOrganization']['id'].'">';
				
				echo '<td>';
				echo ' <img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Organization']['img1'].'" alt="'.$result['Organization']['name'].'" /> ';	
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
				echo '<td>';
				echo $this->Form->input('nota_supplier', ['label' => false, 'name' => 'data[ProdGasPromotionsOrganization][nota_supplier]['.$result['Organization']['id'].']']);
				echo '</td>';	
				echo '</tr>';
				
			}
			
		} // end loops
		
	echo '</table></div>';
}

echo '</fieldset>';
echo $this->Form->hidden('id', ['value' => $results['ProdGasPromotion']['id']]);
echo $this->Form->end(__('ProdGasPromotionTrasmissionToGas'));
echo '</div>';
?>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List ProdGasPromotions'), array('controller' => 'ProdGasPromotions', 'action' => 'index'),array('class'=>'action actionReload'));?></li>
		<li><?php echo $this->Html->link(__('Edit ProdGasPromotion'), array('controller' => 'ProdGasPromotions', 'action' => 'edit', $prod_gas_promotion_id),array('class'=>'action actionEdit'));?></li>
	</ul>	
</div>