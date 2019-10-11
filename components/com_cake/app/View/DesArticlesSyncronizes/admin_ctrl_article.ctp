<?php
echo '<table>';
echo '<tr>';
echo '<th>'.__('DesArticlesDiffShort').'</th>';
echo '<th>'.__('DesSupplierInArticlesOrder').'</th>';
echo '<th>'.__('DesSupplierInCart').'</th>';
echo '<th>'.__('FlagPresenteArticlesorders').' *</th>';
echo '<th class="actions">'.__('Actions').'</th>';
echo '</tr>';

echo '<tr>';
echo '<td>';  
echo $this->Html->tag('a', '', array('id' => 'diff-'.$id_articles_confronto , 'class' => 'action actionJContent ', 'title' => __('DesArticlesDiff'), 'data-toggle' => 'modal', 'data-target' => '#modal-diff-'.$id_articles_confronto));
echo '</td>';
if($results['Article']['ArticlesOrder'])
	echo '<td class="stato_si" title="'.__('si').'" >';		
else
	echo '<td class="stato_no" title="'.__('no').'" >';	

if($results['Article']['isArticleInCart']) 
	echo '<td class="stato_si" title="'.__('si').'" >';		
else
	echo '<td class="stato_no" title="'.__('no').'" >';	

if($results['Article']['flag_presente_articlesorders']=='Y') 
	echo '<td class="orderStatoPROCESSED-POST-DELIVERY" title="'.__('si').'" >';		
else
	echo '<td class="orderStatoCLOSE" title="'.__('no').'" >';

echo '<td class="actions-table-img">';
if(!$results['Article']['isArticleInCart'])
	echo $this->Html->link(null, array('action' => 'syncronize_flag_presente_articlesorders', null, 'master_organization_id='.$master_organization_id.'&supplier_id='.$supplier_id.'&article_id='.$results['Article']['id']), array('class' => 'action actionClose', 'title' => __('DesSyncronizeFlagPresenteArticlesordersButton')));
else {
	echo $this->Html->link(null, array('action' => 'syncronize_update', null, 'master_organization_id='.$master_organization_id.'&supplier_id='.$supplier_id.'&master_article_id='.$master_article_id.'&article_id='.$results['Article']['id']), array('class' => 'action actionCopy', 'title' => __('DesSyncronizeUpdate')));			
}
echo '</td>';
echo '</tr>';
echo '</table>';

/*
 * dialog
 */
echo '<div id="modal-diff-'.$id_articles_confronto.'" class="modal fade" role="dialog">';
echo '<div class="modal-dialog">';
echo '<div class="modal-content">';
echo '<div class="modal-header">';
echo '<button type="button" class="close" data-dismiss="modal">&times;</button>';
echo '<h4 class="modal-title">Confronto articoli</h4>';
echo '</div>';
echo '<div class="modal-body">';
?>
		<table cellpadding="0" cellspacing="0">
		<tr>
			<th></th>
			<th><?php echo __('DesSupplierMasterOrganization');?></th>
			<th><?php echo __('DesSupplierMyOrganization');?></th>
		</tr>
		
		<tr>
			<td></td>
			<td>
			<?php
			if(!empty($masterResults['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$masterResults['Article']['organization_id'].DS.$masterResults['Article']['img1'])) {
				echo '<img class="img-responsive-disabled" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$masterResults['Article']['organization_id'].'/'.$masterResults['Article']['img1'].'" />';
			}	
			?>			
			</td>
			<td>
			<?php
			if(!empty($results['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$results['Article']['organization_id'].DS.$results['Article']['img1'])) {
				echo '<img class="img-responsive-disabled" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$results['Article']['organization_id'].'/'.$results['Article']['img1'].'" />';
			}	
			?>			
			</td>
		</tr>
		<tr>
			<td><?php echo __('Name');?></td>
			<td><?php echo $masterResults['Article']['name'];?></td>
			<td><?php echo $results['Article']['name'];?></td>	
		</tr>
		<?php
		if($user->organization['Organization']['hasFieldArticleCodice']=='Y') {
		?>
			<tr>
				<td><?php echo __('Code');?></td>
				<td><?php echo $masterResults['Article']['codice'];?></td>
				<td><?php echo $results['Article']['codice'];?></td>	
			</tr>
		<?php
		}
		?>
		<?php
		if($user->organization['Organization']['hasFieldArticleIngredienti']=='Y') {
		?>
			<tr>
				<td><?php echo __('Nota');?></td>
				<td><?php echo $masterResults['Article']['ingredienti'];?></td>
				<td><?php echo $results['Article']['ingredienti'];?></td>	
			</tr>
		<?php
		}
		?>
		<tr>
			<td><?php echo __('Package');?></td>
			<td><?php echo $this->App->getArticleConf($masterResults['Article']['qta'], $masterResults['Article']['um']); ?></td>
			<td><?php echo $this->App->getArticleConf($results['Article']['qta'], $results['Article']['um']); ?></td>	
		</tr>
		<tr>
			<td><?php echo __('Prezzo');?></td>
			<td><?php echo $masterResults['Article']['prezzo_e'];?></td>
			<td><?php echo $results['Article']['prezzo_e'];?></td>	
		</tr>
		<tr>
			<td><?php echo __('Prezzo/UM');?></td>
			<td><?php echo $this->App->getArticlePrezzoUM($masterResults['Article']['prezzo'], $masterResults['Article']['qta'], $masterResults['Article']['um'], $masterResults['Article']['um_riferimento']);?></td>
			<td><?php echo $this->App->getArticlePrezzoUM($results['Article']['prezzo'], $results['Article']['qta'], $results['Article']['um'], $results['Article']['um_riferimento']);?></td>	
		</tr>
		<tr>
			<td><?php echo __('qta_minima');?></td>
			<td><?php echo $masterResults['Article']['qta_minima'];?></td>
			<td><?php echo $results['Article']['qta_minima'];?></td>	
		</tr>
		<tr>
			<td><?php echo __('qta_massima');?></td>
			<td><?php echo $masterResults['Article']['qta_massima'];?></td>
			<td><?php echo $results['Article']['qta_massima'];?></td>	
		</tr>
		<tr>
			<td><?php echo __('pezzi_confezione');?></td>
			<td><?php echo $masterResults['Article']['pezzi_confezione'];?></td>
			<td><?php echo $results['Article']['pezzi_confezione'];?></td>	
		</tr>
		<tr>
			<td><?php echo __('qta_multipli');?></td>
			<td><?php echo $masterResults['Article']['qta_multipli'];?></td>
			<td><?php echo $results['Article']['qta_multipli'];?></td>	
		</tr>
		<tr>
			<td><?php echo __('qta_minima_order');?></td>
			<td><?php echo $masterResults['Article']['qta_minima_order'];?></td>
			<td><?php echo $results['Article']['qta_minima_order'];?></td>	
		</tr>
		<tr>
			<td><?php echo __('qta_massima_order');?></td>
			<td><?php echo $masterResults['Article']['qta_massima_order'];?></td>
			<td><?php echo $results['Article']['qta_massima_order'];?></td>	
		</tr>
		<tr>
			<td><?php echo __('qta_minima');?></td>
			<td><?php echo $masterResults['Article']['qta_minima'];?></td>
			<td><?php echo $results['Article']['qta_minima'];?></td>	
		</tr>
	</table> 
<?php
echo '</div>';
echo '<div class="modal-footer">';
echo '<button type="button" class="btn btn-primary" data-dismiss="modal">'.__('Close').'</button>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
?>