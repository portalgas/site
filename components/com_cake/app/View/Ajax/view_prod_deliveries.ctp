<div class="articles">
	<h3 class="title_details"><?php echo __('Related Articles');?>
	</h3>
	
<?php 
if (isset($results)):?>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo __('N');?></th>
			<th><?php echo __('Bio');?></th>
			<th></th>
			<th><?php echo __('Name');?></th>
			<th><?php echo __('pezzi_confezione');?></th>
			<th><?php echo __('PrezzoUnita');?></th>
			<th><?php echo __('Prezzo/UM');?></th>
			<th><?php echo __('OrderDati');?></th>
			<th><?php echo __('Stato');?></th>
			<th><?php echo __('Created');?></th>
	<?php
	echo '</tr>';
	
	foreach ($results as $numResult => $result): 

	?>
	<tr>
		<td><?php echo ($numResult+1);?></td>
		<td><?php if($result['Article']['bio']=='Y') echo '<span class="bio" title="'.Configure::read('bio').'"></span>';?></td>
		
		<?php
			echo '<td>';
			if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
				echo '<img width="50" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';	
			}
			echo '</td>';
	
			echo '<td>';
			echo $result['Article']['name'].'&nbsp;';
			 	if(!empty($result['Article']['nota'])) echo '<div class="small">'.strip_tags($result['Article']['nota']).'</div>'; 
			echo '</td>';
		 ?>
		</td>
		<td><?php echo $this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']);?></td>
		<td><?php echo $result['ProdDeliveriesArticle']['prezzo_e'];?></td>
		<td><?php echo $this->App->getArticlePrezzoUM($result['ProdDeliveriesArticle']['prezzo'], $result['qta'], $result['Article']['um'], $result['Article']['um_riferimento']);?></td>
		<td style="white-space: nowrap;">
		<?php 
			if($result['ProdDeliveriesArticle']['pezzi_confezione']>1) echo __('pezzi_confezione').':&nbsp;'.$result['ProdDeliveriesArticle']['pezzi_confezione'].'<br />';
			if($result['ProdDeliveriesArticle']['qta_minima']>1)   echo __('qta_minima').':&nbsp;'.$result['ProdDeliveriesArticle']['qta_minima'].'<br />';
			if($result['ProdDeliveriesArticle']['qta_massima']>1)   echo __('qta_massima').':&nbsp;'.$result['ProdDeliveriesArticle']['qta_massima'].'<br />';
			if($result['ProdDeliveriesArticle']['qta_multipli']>1) echo __('qta_multipli').'&nbsp;'.$result['ProdDeliveriesArticle']['qta_multipli'].'<br />';
			if($result['ProdDeliveriesArticle']['qta_massima_order']>1)  echo __('qta_massima_order').':&nbsp;'.$result['ProdDeliveriesArticle']['qta_massima_order'].'<br />';
			if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y' && $result['ProdDeliveriesArticle']['alert_to_qta']>1) echo sprintf(__('alert_to_qta_num'),'&nbsp;'.$result['ProdDeliveriesArticle']['alert_to_qta']);
			
		echo '</td>';
		
		echo '<td ';
		echo 'title="'.$this->App->traslateProdDeliveriesArticleStato($result).'" ';			
		echo ' class="stato_'.strtolower($result['ProdDeliveriesArticle']['stato']).'">';
		echo '</td>';
		
		echo '<td style="white-space: nowrap;">'.$this->App->formatDateCreatedModifier($result['Article']['created']).'</td>'; 			
	echo '</tr>';
	endforeach;
	
	echo '</table>';
else: 
	echo $this->element('boxMsg',array('class_msg' => 'notice', 'msg' => "Non ci sono articoli associati"));
endif; 
?>
</div>