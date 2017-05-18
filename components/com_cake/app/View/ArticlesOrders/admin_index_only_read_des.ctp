<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Orders'),array('controller' => 'Orders', 'action' => 'index'));
if(isset($order['Order']['id']) && !empty($order['Order']['id']))
	$this->Html->addCrumb(__('Order home DES'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order['Order']['id']));
$this->Html->addCrumb(__('View ArticlesOrder DES'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $user->organization['Organization']['hasFieldArticleAlertToQta']=='Y')
	$colspan = '13';
else
if($user->organization['Organization']['hasFieldArticleCodice']=='N' && $user->organization['Organization']['hasFieldArticleAlertToQta']=='N')
	$colspan = '12';
else
	$colspan = '13';
?>
		
<h2 class="ico-edit-cart">
	<?php echo __('View ArticlesOrder DES').' ('.count($results).')';?>
	<div class="actions-img">
		<ul>
			<li><?php echo $this->Html->link(__('Order home'), array('controller' => 'Orders', 'action' => 'home', null, 'order_id='.$order['Order']['id']),array('class' => 'action actionWorkflow','title' => __('Order home'))); ?></li>
		</ul>
	</div>	
</h2>
<?php 
echo '<div class="contentMenuLaterale">';

	echo $this->element('boxDesOrder', array('results' => $desOrdersResults));		
	
	echo '<div style="clear:both"></div>';
	
	if(count($results)>0) {
	?>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th></th>
			<th colspan="2"><?php echo __('N');?></th>
			<?php
			if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
				echo '<th>'.__('codice').'</th>';
			?>
			<th>Nome prodotto</th>
			<th><?php echo __('Prezzo');?></th>
			<th><?php echo __('pezzi_confezione');?></th>
			<th><?php echo __('qta_minima');?></th>
			<th><?php echo __('qta_massima');?></th>
			<th><?php echo __('qta_multipli');?></th>
			<th><?php echo __('qta_minima_order');?></th>
			<th><?php echo __('qta_massima_order');?></th>
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
		if(!empty($result['Cart'])) 
			$articleJustInCart=true;
		else
			$articleJustInCart=false;									
	?>
	<tr class="view">
		<td><a action="article_carts-0_<?php echo $result['Article']['organization_id']; ?>_<?php echo $result['Article']['id']; ?>" class="actionTrView openTrView" href="#" title="<?php echo __('Href_title_expand');?>"></a></td>
		<td><?php echo ($i+1);?></td>
		<?php
		echo '<td ';
		if($articleJustInCart) echo 'style="background-color:red;" title="Articolo già acquistato"';
		echo '>';
		echo '</td>';
		
		if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
			echo '<td>'.$result['Article']['codice'].'</td>';
		?>					
		<td><?php echo $result['Article']['name']; ?>&nbsp;
			 <?php if(!empty($result['Article']['nota'])) echo '<div class="small">'.strip_tags($result['Article']['nota']).'</div>'; ?>
		</td>
		<td nowrap><?php echo $result['ArticlesOrder']['prezzo_e'];?></td>
		<td style="text-align:center;"><?php echo $result['ArticlesOrder']['pezzi_confezione'];?></td>
		<td style="text-align:center;"><?php echo $result['ArticlesOrder']['qta_minima'];?></td>
		<td style="text-align:center;"><?php echo $result['ArticlesOrder']['qta_massima'];?></td>
		<td style="text-align:center;"><?php echo $result['ArticlesOrder']['qta_multipli'];?></td>
		<td style="text-align:center;"><?php echo $result['ArticlesOrder']['qta_minima_order'];?></td>
		<td style="text-align:center;"><?php echo $result['ArticlesOrder']['qta_massima_order'];?></td>
		<?php
		if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y')
			echo '<td style="text-align:center;">'.$result['ArticlesOrder']['alert_to_qta'].'</td>';
				
		echo '<td ';
		echo 'title="'.$this->App->traslateArticlesOrderStato($result).'" ';	
		if(strtolower($result['ArticlesOrder']['stato'])=='qtamaxorder')
			$stato = 'qtamax';
		else
			$stato = $result['ArticlesOrder']['stato'];
		echo ' class="stato_'.strtolower($stato).'">';
		echo '</td>';
		
	echo '</tr>';
	echo '<tr class="trView" id="trViewId-0_'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'">';
	echo '<td colspan="2"></td>';
	echo '<td colspan="'.$colspan.'" id="tdViewId-0_'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'"></td>';
	echo '</tr>';
	
	endforeach;
	
	echo '</table>';
	} 
	else // if(count($results)>0)
		echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Non ci sono ancora articoli associati all'ordine."));
	
echo '</div>';

echo '<div class="clearfix"></div>';
echo $this->element('legendaArticlesOrderStato');

$options = [];
echo $this->MenuOrders->drawWrapper($order_id, $options);
?>