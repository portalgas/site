<?php
foreach($syncronizeResults as $organization_id => $syncronizeResult) {

	echo '<h2 class="ico-organizations">'.$syncronizeResult['Organization']['name'].'</h2>';
		
	echo '<div class="table-responsive"><table class="table table-hover">';
	echo '<tr>';
	echo '<th></th>';
	echo '<th></th>';
	echo '<th></th>';
	echo '<th>';
	if(!empty($id))
		echo __('HasProdGasArticleSyncronizeUpdate');
	else
		echo __('HasProdGasArticleSyncronizeInsert');	
	echo '</th>';
	echo '</tr>';
	
	if(!empty($id)) {			
		echo '<tr>';
		echo '<td class="col-md-1"></td>';
		echo '<td><div class="col-md-1 action actionCopy" title="'.__('ProdGasSyncronizeUpdate').'"></div></td>';
		echo '<td></td>';
		echo '<td>';
		echo $this->App->drawFormRadio('ProdGasArticlesSyncronize','syncronize_update-'.$organization_id.'-0', array('options' => $yes_nos, 'value'=> $yes_nos_default, 'label'=> false, 'required'=>'false',
				'after' => $this->App->drawTooltip(null,__('toolTipProdGasArticleSyncronizeUpdate'),$type='HELP')));											
		echo '</td>';
		echo '</tr>';							
	}
	else {
		echo '<tr>';
		echo '<td class="col-md-1"></td>';
		echo '<td><div class="col-md-1 action actionAdd" title="'.__('ProdGasSyncronizeInsert').'"></div></td>';
		echo '<td></td>';
		echo '<td>';
		echo $this->App->drawFormRadio('ProdGasArticlesSyncronize','syncronize_insert-'.$organization_id.'-0', array('options' => $yes_nos, 'value'=> $yes_nos_default, 'label'=> false, 'required'=>'false',
				'after' => $this->App->drawTooltip(null,__('toolTipProdGasArticleSyncronizeInsert'),$type='HELP')));
		echo '</td>';
		echo '</tr>';										
	}
	
	
	if(!empty($syncronizeResult['Order'])) {

		echo '<tr>';
		echo '<th></th>';
		echo '<th></th>';
		echo '<th>'.__('Delivery').'</th>';
		echo '<th>';
		if(!empty($id))
			echo __('HasProdGasArticleSyncronizeArticlesOrdersUpdate');
		else
			echo __('HasProdGasArticleSyncronizeArticlesOrdersInsert');	
		echo '</th>';
		echo '</tr>';
	
		foreach($syncronizeResult['Order'] as $syncronizeResult) {
		
			if(!empty($id)) {
				echo '<tr>';
				echo '<td class="col-md-1"></td>';
				echo '<td><div class="col-md-1 action actionCopy" title="'.__('ProdGasSyncronizeArticlesOrdersUpdate').'"></div></td>';
				echo '<td>'.$syncronizeResult['Delivery']['luogoData'].'</td>';
				echo '<td>';
				echo $this->App->drawFormRadio('ProdGasArticlesSyncronize','syncronize_articles_orders_update-'.$organization_id.'-'.$syncronizeResult['Order']['id'], array('options' => $yes_nos, 'value'=> $yes_nos_default, 'label'=> false, 'required'=>'false',
						'after' => $this->App->drawTooltip(null,__('toolTipProdGasArticleSyncronizeArticlesOrdersUpdate'),$type='HELP')));
				echo '</td>';
				echo '</tr>';						
			}
			else {
				echo '<tr>';
				echo '<td class="col-md-1"></td>';
				echo '<td><div class="col-md-1 action actionAdd" title="'.__('ProdGasSyncronizeArticlesOrdersInsert').'"></div></td>';
				echo '<td>'.$syncronizeResult['Delivery']['luogoData'].'</td>';
				echo '<td>';
				echo $this->App->drawFormRadio('ProdGasArticlesSyncronize','syncronize_articles_orders_insert-'.$organization_id.'-'.$syncronizeResult['Order']['id'], array('options' => $yes_nos, 'value'=> $yes_nos_default, 'label'=> false, 'required'=>'false',
						'after' => $this->App->drawTooltip(null,__('toolTipProdGasArticleSyncronizeArticlesOrdersInsert'),$type='HELP')));
				echo '</td>';
				echo '</tr>';
			}						
		} // loop Order
	}
	echo '</table></div>';
	
	if(empty($syncronizeResult['Order']))
		echo $this->element('boxMsg',array('class_msg' => 'notice resultsNotFonud', 'msg' => "L'articolo non è associato in alcun ordine del GAS")); 
		
} // loop Syncronize				
?>