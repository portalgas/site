<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Orders'),array('controller' => 'Orders', 'action' => 'index'));
if(isset($order['Order']['id']) && !empty($order['Order']['id']))
	$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order['Order']['id']));
$this->Html->addCrumb(__('BackupArticlesOrders'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $user->organization['Organization']['hasFieldArticleAlertToQta']=='Y')
	$colspan = '13';
else
if($user->organization['Organization']['hasFieldArticleCodice']=='N' && $user->organization['Organization']['hasFieldArticleAlertToQta']=='N')
	$colspan = '11';
else
	$colspan = '12';
	
echo '<div class="contentMenuLaterale">';
?>
	<h2>
		Elenco degli articoli associati all'ordine (<?php echo count($results);?>) estratti dal <b>backup</b> del giorno precedente 
	</h2>
	
	
	<?php 
	include('box_order_detail.ctp');
	
	if(count($results)>0) {
	
		echo $this->Form->create('BackupArticlesOrder',array('id' => 'formGas'));
		?>
		<fieldset>
				<table cellpadding="0" cellspacing="0">
				<tr>
						<th></th>
						<th><?php echo __('N');?></th>
						<th colspan="<?php echo ($user->organization['Organization']['hasFieldArticleCodice']=='Y') ? '3' :'2';?>">
						</th>
						<th></th>
						<th><?php echo __('Prezzo');?></th>
						<th><?php echo __('pezzi_confezione');?></th>
						<th><?php echo __('qta_minima_short');?></th>
						<th><?php echo __('qta_massima_short');?></th>
						<th><?php echo __('qta_multipli');?></th>
						<th><?php echo __('qta_minima_order_short');?></th>
						<th><?php echo __('qta_massima_order_short');?></th>
						<?php
						if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y')
							echo '<th>'.__('alert_to_qta').'</th>';
						?>
						<th>Stato</th>			
				</tr>
				<?php
				foreach ($results as $i => $result):
				
					/*
				     * ctrl se l'articolo e' gia' stato acquaitato
					 */
					if(!empty($result['BackupCart'])) 
						$articleJustInCart=true;
					else
						$articleJustInCart=false;
				
					echo '<tr class="view">';
					
					echo '<td><a action="backup_articles_order_carts-'.$order['Order']['id'].'_'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
					
					echo '<td>'.($i+1).'</td>';
					echo '<td ';
					if($articleJustInCart) echo 'style="background-color:red;" title="Articolo già acquistato"';
					echo '>';
					echo '</td>';

					if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
						echo '<td idArticlesOrderCheckbox="'.$order['Order']['id'].'_'.$result['Article']['id'].'" class="bindCheckbox">'.$result['Article']['codice'].'</td>';
					
					echo '<td idArticlesOrderCheckbox="'.$order['Order']['id'].'_'.$result['Article']['id'].'" class="bindCheckbox">';
					echo $result['Article']['name'].'&nbsp;';
					// confezione
					echo $this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']).'&nbsp;';
			
					if(!empty($result['Article']['nota'])) echo '<div class="small">'.strip_tags($result['Article']['nota']).'</div>';
					echo '</td>';
					echo '<td>';
					if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
						echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';	
					}
					echo '</td>';
					echo '<td nowrap>'.$result['BackupArticlesOrder']['prezzo_e'].'</td>';
					echo '<td>'.$result['BackupArticlesOrder']['pezzi_confezione'].'</td>';
					echo '<td>'.$result['BackupArticlesOrder']['qta_minima'].'</td>';
					echo '<td>'.$result['BackupArticlesOrder']['qta_massima'].'</td>';
					echo '<td>'.$result['BackupArticlesOrder']['qta_multipli'].'</td>';
					echo '<td>'.$result['BackupArticlesOrder']['qta_minima_order'].'</td>';
					echo '<td>'.$result['BackupArticlesOrder']['qta_massima_order'].'</td>';
					
					if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y')
						echo '<td>'.$result['BackupArticlesOrder']['alert_to_qta'].'</td>';
						
					echo '<td ';
					echo 'title="'.$this->App->traslateArticlesOrderStato($result).'" ';			
					echo ' class="stato_'.strtolower($result['BackupArticlesOrder']['stato']).'">';
					echo '</td>';

				echo '</tr>';
				echo '<tr class="trView" id="trViewId-'.$order['Order']['id'].'_'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'">';
				echo '<td colspan="2"></td>';
				echo '<td colspan="'.$colspan.'" id="tdViewId-'.$order['Order']['id'].'_'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'"></td>';
				echo '</tr>';
				
				endforeach;
				
				echo '</table>';

		echo '</fieldset>';

		echo $this->Form->hidden('order_id',array('id' => 'order_id', 'value'=> $order['Order']['id']));
		echo $this->Form->hidden('delivery_id',array('id' => 'delivery_id', 'value'=> $order['Order']['delivery_id']));
		echo $this->Form->end(__('Ripristina i dati'));				
	} 
	else // if(count($results)>0)
		echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Non ci sono articoli associati ad un ordine precedente."));

echo '</div>';

$options = [];
echo $this->MenuOrders->drawWrapper($order_id, $options);
?>