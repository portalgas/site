<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Orders'),array('controller' => 'Orders', 'action' => 'index'));
$this->Html->addCrumb(__('Order'),array('controller'=>'Orders','action'=>'home',$orderResults['Order']['id']));
$this->Html->addCrumb(__('Add ArticlesOrder'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));


echo '<div class="articles">';

echo $this->Form->create('ProdGasArticlesOrder', array('id' => 'formGas'));
?>
<fieldset>
	<legend></legend>

	<div class="legenda">
		<table cellpadding="0" cellspacing="0">
		<tr>
			<th><?php echo __('Delivery');?></th>
			<th><?php echo __('Supplier');?></th>
			<th><?php echo __('Order');?></th>
		</tr>
		<tr class="view">
			<td><?php
			if($orderResults['Delivery']['sys']=='N')
				echo $orderResults['Delivery']['luogoData'];
			else 
				echo $orderResults['Delivery']['luogo'];
			?></td>
			<td><?php echo $orderResults['SuppliersOrganization']['name']; ?></td>
			<td><?php echo $orderResults['Order']['name']; ?>
		</tr>
		</table>
	</div>

	<h2>Elenco degli articoli già associati all'ordine</h2>
	
	<div class="articlesOrders">
	
		<?php
		echo '<div class="table-responsive"><table class="table table-hover">';
		echo '<tr>';
		echo '<th></th>';
		echo '<th>'.__('N').'</th>';
		echo '<th colspan="';
		echo ($user->organization['Organization']['hasFieldArticleCodice']=='Y') ? '3' :'2';
		echo '">';
		echo '</th>';
		echo '<th></th>';					
		echo '<th style="text-align:center;">'.__('Prezzo').'</th>';
		echo '<th style="text-align:center;">'.__('pezzi_confezione').'</th>';
		echo '<th style="text-align:center;">'.__('qta_minima_short').'</th>';
		echo '<th style="text-align:center;">'.__('qta_massima_short').'</th>';
		echo '<th style="text-align:center;">'.__('qta_multipli').'</th>';
		echo '<th style="text-align:center;">'.__('qta_minima_order_short').'</th>';
		echo '<th style="text-align:center;">'.__('qta_massima_order_short').'</th>';
		echo '<th>Stato</th>';		
		echo '<th class="actions">';
		echo __('Actions');
		echo '</th>';
		echo '</tr>';
		
		foreach ($results as $numResult => $result) {
			
			
					/*
				     * ctrl se l'articolo e' gia' stato acquaitato
					 */
					if(!empty($result['Cart'])) 
						$articleJustInCart=true;
					else
						$articleJustInCart=false;
				
					echo '<tr class="view">';
					
					echo '<td>';
					echo '<a action="prodgas_articles_order_carts-'.$organization_id.'_'.$orderResults['Order']['id'].'_'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a>';
					echo '</td>';
					
					echo '<td>'.((int)$numResult+1).'</td>';
					echo '<td ';
					if($articleJustInCart) echo 'style="background-color:red;" title="Articolo già acquistato"';
					echo '>';
					echo '</td>';

					if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
						echo '<td idArticlesOrderCheckbox="'.$orderResults['Order']['id'].'_'.$result['Article']['id'].'" class="bindCheckbox">'.$result['Article']['codice'].'</td>';
					
					echo '<td idArticlesOrderCheckbox="'.$orderResults['Order']['id'].'_'.$result['Article']['id'].'" class="bindCheckbox">';
					echo $result['ArticlesOrder']['name'].'&nbsp;';
					// confezione
					echo $this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']).'&nbsp;';
			
					if(!empty($result['Article']['nota'])) echo '<div class="small">'.strip_tags($result['Article']['nota']).'</div>';
					echo '</td>';
					echo '<td>';
					if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
						echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';	
					}
					echo '</td>';
					echo '<td nowrap>'.$result['ArticlesOrder']['prezzo_e'].'</td>';
					echo '<td style="text-align:center;">'.$result['ArticlesOrder']['pezzi_confezione'].'</td>';
					echo '<td style="text-align:center;">'.$result['ArticlesOrder']['qta_minima'].'</td>';
					echo '<td style="text-align:center;">'.$result['ArticlesOrder']['qta_massima'].'</td>';
					echo '<td style="text-align:center;">'.$result['ArticlesOrder']['qta_multipli'].'</td>';
					echo '<td style="text-align:center;">'.$result['ArticlesOrder']['qta_minima_order'].'</td>';
					echo '<td style="text-align:center;">'.$result['ArticlesOrder']['qta_massima_order'].'</td>';
					
					echo '<td ';
					echo 'title="'.$this->App->traslateArticlesOrderStato($result).'" ';
					if(strtolower($result['ArticlesOrder']['stato'])=='qtamaxorder')
						$stato = 'qtamax';
					else
						$stato = $result['ArticlesOrder']['stato'];
					echo ' class="stato_'.strtolower($stato).'">';
					echo '</td>';

					echo '<td class="actions-table-img">';
					echo $this->Html->link(null, ['action' => 'edit', null, 'order_id='.$result['ArticlesOrder']['order_id'], 'article_organization_id='.$result['ArticlesOrder']['article_organization_id'], 'article_id='.$result['ArticlesOrder']['article_id']], ['class' => 'action actionEdit','title' => __('Edit')]);
					echo '</td>';
					echo '</tr>';
					echo '<tr class="trView" id="trViewId-'.$organization_id.'_'.$orderResults['Order']['id'].'_'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'">';
					echo '<td colspan="2"></td>';
					echo '<td colspan="';
					echo ($user->organization['Organization']['hasFieldArticleCodice']=='Y') ? '13' :'14';
					echo '" id="tdViewId-'.$organization_id.'_'.$orderResults['Order']['id'].'_'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'"></td>';
					echo '</tr>';
					
			/*
			$opts = ['label' => false, 'type' => 'text'];
			if(!$canEdit) $opts += ['disabled' => 'disabled'];
			
			echo '<tr class="view">';
			echo '<td>'.((int)$numResult+1).'</td>';
			echo '<td><a action="articles-'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a>';		
			echo '</td>';
			
			if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
				echo '<td idArticleCheckbox="'.$result['Article']['id'].'" class="bindCheckbox">'.$result['Article']['codice'].'</td>';
			echo '<td idArticleCheckbox="'.$result['Article']['id'].'" class="bindCheckbox">';
			echo $result['Article']['name'].'&nbsp;';
			if(!empty($result['Article']['nota'])) echo '<div class="small">'.strip_tags($result['Article']['nota']).'</div>';
			echo '</td>';
			echo '<td>';
			if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
				echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';	
			}
			echo '</td>';
			echo '<td style="white-space: nowrap;">'.$this->Form->input('prezzo', array_merge(['name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderPrezzo]', 'style' => 'display:inline', 'value' => $result['Article']['prezzo_'], 'tabindex'=>((int)$numResult+1),'after'=>'&nbsp;&euro;', 'class'=>'double'], $opts)).'</td>';
			echo '<td>'.$this->Form->input('pezzi_confezione', array_merge(['name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderPezziConfezione]','value' => $result['Article']['pezzi_confezione'], 'tabindex'=>((int)$numResult+1)], $opts)).'</td>';
			echo '<td>'.$this->Form->input('qta_minima', array_merge(['name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderQtaMinima]', 'value' => $result['Article']['qta_minima'], 'tabindex'=>((int)$numResult+1)], $opts)).'</td>';
			echo '<td>'.$this->Form->input('qta_massima', array_merge(['name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderQtaMassima]', 'value' => $result['Article']['qta_massima'], 'tabindex'=>((int)$numResult+1)], $opts)).'</td>';
			echo '<td>'.$this->Form->input('qta_multipli', array_merge(['name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderQtaMultipli]', 'value' => $result['Article']['qta_multipli'], 'tabindex'=>((int)$numResult+1)], $opts)).'</td>';
			echo '<td>'.$this->Form->input('qta_minima_order', array_merge(['name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderQtaMinimaOrder]', 'value' => $result['Article']['qta_minima_order'], 'tabindex'=>((int)$numResult+1)], $opts)).'</td>';
			echo '<td>'.$this->Form->input('qta_massima_order', array_merge(['name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderQtaMassimaOrder]', 'value' => $result['Article']['qta_massima_order'], 'tabindex'=>((int)$numResult+1)], $opts)).'</td>';
			echo '<td class="actions-table-img">';
			echo $this->Html->link(null, ['action' => 'edit', null, 'order_id='.$result['ArticlesOrder']['order_id'], 'article_organization_id='.$result['ArticlesOrder']['article_organization_id'], 'article_id='.$result['ArticlesOrder']['article_id']], ['class' => 'action actionEdit','title' => __('Edit')]);
			echo '</td>';
			echo '</tr>';
			echo '<tr class="trView" id="trViewId-'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'">';
			echo '<td colspan="2"></td>';
			echo '<td colspan="'.$colspan.'" id="tdViewId-'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'"></td>';
			echo '</tr>';
			*/
		
		} // end foreach ($results as $numResult => $result)
	
		echo '</table></div>';
	
	echo '</div>';
	echo '</fieldset>';

	echo $this->Form->end();
?>
</div>
<style type="text/css">
.bindCheckbox {cursor:pointer;}
</style>