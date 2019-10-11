<?php 
echo '<div class="articles">';

/*
 * $actionToEditOrder = '' se Delivery.isVisibleBackOffice == 'N' || Order.isVisibleBackOffice == 'N' || $results['Order']['prod_gas_promotion_id']!=0
 */
if(!empty($actionToEditOrder) && empty($des_supplier_id)) {
	echo '<div class="actions-img">';
	echo '<ul>';
	echo '<li>';
	echo $this->Html->link($actionToEditOrder['title'], array('controller' => $actionToEditOrder['controller'],														'action' => $actionToEditOrder['action'],null,														'delivery_id='.$results['Order']['delivery_id'], 'order_id='.$results['Order']['id']) ,array('class' => 'action actionEdit','title' => $actionToEditOrder['title']));	echo '</li>';
	echo '</ul>';
	echo '</div>';
}
	
if(isset($results['Article'])):
	echo '<div class="table-responsive"><table class="table table-hover">';
	echo '<tr>';
	echo '<th>'.__('N').'</th>';
	echo '<th>'.__('Bio').'</th>';
	echo '<th></th>';
	echo '<th>'.__('Name').'</th>';
	echo '<th>'.__('pezzi_confezione').'</th>';
	echo '<th>'.__('PrezzoUnita').'</th>';
	echo '<th>'.__('Prezzo/UM').'</th>';
	echo '<th>'.__('OrderDati').'</th>';
	echo '<th>'.__('Stato').'</th>';
	echo '<th>'.__('Created').'</th>';
	/*
	 * action = '' se Delivery.isVisibleBackOffice == 'N' || Order.isVisibleBackOffice == 'N' || $results['Order']['prod_gas_promotion_id']!=0
	 * 			   se Order.state_code >= 'WAIT-PROCESSED-TESORIERE'
	 */ 
	if(!empty($actionToEditArticle)) 
		echo '<th class="actions">'.__('Actions').'</th>';
		
	echo '</tr>';
	
	foreach ($results['Article'] as $numResult => $result): 

		echo '<tr>';
		echo '<td>'.($numResult+1).'</td>';
		echo '<td>';
		if($result['bio']=='Y') echo '<span class="bio" title="'.Configure::read('bio').'"></span>';
		echo '</td>';
			
		echo '<td>';
		if(!empty($result['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['organization_id'].DS.$result['img1'])) {
			echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['organization_id'].'/'.$result['img1'].'" />';	
		}
		echo '</td>';

		echo '<td>';
		echo $result['name'].'&nbsp;';
			if(!empty($result['nota'])) echo '<div class="small">'.$result['nota'].'</div>'; 
		echo '</td>';
			
		echo '</td>';
		echo '<td>'.$this->App->getArticleConf($result['qta'], $result['um']).'</td>';
		echo '<td>'.$result['ArticlesOrder']['prezzo_e'].'</td>';
		echo '<td>'.$this->App->getArticlePrezzoUM($result['ArticlesOrder']['prezzo'], $result['qta'], $result['um'], $result['um_riferimento']).'</td>';
		echo '<td style="white-space: nowrap;">';
		if($result['ArticlesOrder']['pezzi_confezione']>1) echo __('pezzi_confezione').':&nbsp;'.$result['ArticlesOrder']['pezzi_confezione'].'<br />';
		if($result['ArticlesOrder']['qta_minima']>1)   echo __('qta_minima_short').':&nbsp;'.$result['ArticlesOrder']['qta_minima'].'<br />';
		if($result['ArticlesOrder']['qta_massima']>1)   echo __('qta_massima_short').':&nbsp;'.$result['ArticlesOrder']['qta_massima'].'<br />';
		if($result['ArticlesOrder']['qta_multipli']>1) echo __('qta_multipli').'&nbsp;'.$result['ArticlesOrder']['qta_multipli'].'<br />';
		if($result['ArticlesOrder']['qta_minima_order']>1)   echo __('qta_minima_order_short').':&nbsp;'.$result['ArticlesOrder']['qta_minima_order'].'<br />';
		if($result['ArticlesOrder']['qta_massima_order']>1)  echo __('qta_massima_order_short').':&nbsp;'.$result['ArticlesOrder']['qta_massima_order'].'<br />';
		if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y' && $result['ArticlesOrder']['alert_to_qta']>1) echo sprintf(__('alert_to_qta_num'),'&nbsp;'.$result['ArticlesOrder']['alert_to_qta']);	
		echo '</td>';
		
		echo '<td ';
		echo 'title="'.$this->App->traslateArticlesOrderStato($result).'" ';			
		echo ' class="stato_'.strtolower($result['ArticlesOrder']['stato']).'">';
		echo '</td>';
			
		echo '<td style="white-space: nowrap;">'.$this->App->formatDateCreatedModifier($result['created']).'</td>';

		/*
		 * $actionToEditArticle = '' se Delivery.isVisibleBackOffice == 'N' || Order.isVisibleBackOffice == 'N' || $results['Order']['prod_gas_promotion_id']!=0
		 * 			   se Order.state_code >= 'WAIT-PROCESSED-TESORIERE'
		 */ 
		if(!empty($actionToEditArticle) &&
		  $user->organization['Organization']['id']==$result['organization_id']) { 
			echo '<td>';			echo $this->Html->link(null, array('controller' => $actionToEditArticle['controller'],
											   'action' => $actionToEditArticle['action'], null,
												'order_id='.$result['ArticlesOrder']['order_id'],'article_organization_id='.$result['ArticlesOrder']['article_organization_id'],'article_id='.$result['ArticlesOrder']['article_id']) ,array('class' => 'action actionEdit','title' => $actionToEditArticle['title'])); 
			echo '</td>';
		}	 			
		echo '</tr>';
	endforeach;
	
	echo '</table></div>';
else: 
	echo $this->element('boxMsg',array('class_msg' => 'notice', 'msg' => "Non ci sono articoli associati"));
endif; 

echo '<h3 class="title_details">'.__('Related Suppliers Organizations Referents Suppliers').'</h3>';
if(!empty($suppliersOrganizationsReferent)) {
	echo '<div class="table-responsive"><table class="table table-hover TableDettaglio">';
	echo "\n\r";
	echo '<tr>';
	echo '<td>';
	echo $this->App->drawListSuppliersOrganizationsReferents($user,$suppliersOrganizationsReferent);
	echo '</td>';
	echo '</tr>';
	echo '</table></div>';
}
else
	echo $this->element('boxMsg',array('class_msg' => 'notice', 'msg' => "Non ci sono referenti associati"));	

echo '</div>';	
?>