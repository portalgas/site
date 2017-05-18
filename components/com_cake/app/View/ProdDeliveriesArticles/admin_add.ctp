<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List ProdDeliveries'),array('controller' => 'Orders', 'action' => 'home', $prodDelivery['ProdDelivery']['id']));
$this->Html->addCrumb(__('ProdDelivery'),array('controller'=>'ProdDeliveries','action' => 'index'));
$this->Html->addCrumb(__('Add ProdDeliveriesArticle'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $user->organization['Organization']['hasFieldArticleAlertToQta']=='Y')
	$colspan = '11';
else
if($user->organization['Organization']['hasFieldArticleCodice']=='N' && $user->organization['Organization']['hasFieldArticleAlertToQta']=='N')
	$colspan = '9';
else
	$colspan = '10';
	
echo '<div class="articles">';

echo $this->Form->create('ProdDeliveriesArticle',array('id' => 'formGas'));
?>
<fieldset>
	<legend></legend>

	<div class="legenda">
		<table cellpadding="0" cellspacing="0">
		<tr>
			<th><?php echo __('Delivery');?></th>
			<th><?php echo __('Prod Group');?></th>
		</tr>
		<tr class="view">
			<td><?php echo $prodDelivery['ProdDelivery']['name'];?></td>
			<td><?php echo $prodDelivery['ProdGroup']['name']; ?></td>
		</tr>
		</table>
	</div>

	<h2>Elenco degli articoli attivi da associare alla consegna
	<?php
	if(count($results)>0) {

		if(count($results)>10) 		
			echo '<div class="submit" style="float:right;"><input type="submit" value="Associa alla consegna gli articoli selezionati" /></div>';
	?>	
	</h2>
	
	<div class="ProdDeliveriesArticle">
	
		<table cellpadding="0" cellspacing="0">
		<tr>
				<th></th>
				<th><?php echo __('N');?></th>
				<th><?php echo '<input type="checkbox" checked id="article_id_selected_all" name="article_id_selected_all" value="ALL" />';?></th>
				<?php
				if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
					echo '<th>'.$this->Paginator->sort('codice').'</th>';
				?>				
				<th>Nome prodotto</th>
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
		</tr>
		<?php
		foreach ($results as $i => $result):
		?>
		<tr class="view">
			<td><a action="articles-<?php echo $result['Article']['id']; ?>" class="actionTrView openTrView" href="#" title="<?php echo __('Href_title_expand');?>"></a></td>
			<td><?php echo ($i+1);?></td>
			<td><?php echo '<input type="checkbox" checked id="'.$result['Article']['id'].'[article_id_selected]" name="article_id_selected" value="'.$result['Article']['id'].'" />';?></td>
			<?php
			if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
				echo '<td>'.$result['Article']['codice'].'</td>';
			?>	
			<td><?php echo $result['Article']['name']; ?>&nbsp;
				 <?php if(!empty($result['Article']['nota'])) echo '<div class="small">'.strip_tags($result['Article']['nota']).'</div>'; ?>
			</td>
			<td nowrap><?php echo $this->Form->input('prezzo',array('type' => 'text', 'label'=>false,'name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderPrezzo]','value'=>$result['Article']['prezzo_'],'size'=>10,'tabindex'=>($i+1),'after'=>'&euro;','class'=>'double noWidth'));?></td>
			<td><?php echo $this->Form->input('pezzi_confezione',array('type' => 'text', 'label'=>false,'name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderPezziConfezione]','value'=>$result['Article']['pezzi_confezione'],'size'=>3,'class' => 'noWidth','tabindex'=>($i+1)));?></td>
			<td><?php echo $this->Form->input('qta_minima',array('type' => 'text', 'label'=>false,'name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderQtaMinima]','value'=>$result['Article']['qta_minima'],'size'=>3,'class' => 'noWidth','tabindex'=>($i+1)));?></td>
			<td><?php echo $this->Form->input('qta_massima',array('type' => 'text', 'label'=>false,'name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderQtaMassima]','value'=>$result['Article']['qta_massima'],'size'=>3,'class' => 'noWidth','tabindex'=>($i+1)));?></td>
			<td><?php echo $this->Form->input('qta_multipli',array('type' => 'text', 'label'=>false,'name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderQtaMultipli]','value'=>$result['Article']['qta_multipli'],'size'=>3,'class' => 'noWidth','tabindex'=>($i+1)));?></td>
			<td><?php echo $this->Form->input('qta_minima_order',array('type' => 'text', 'label'=>false,'name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderQtaMinimaOrder]','value'=>$result['Article']['qta_minima_order'],'size'=>3,'class' => 'noWidth','tabindex'=>($i+1)));?></td>
			<td><?php echo $this->Form->input('qta_massima_order',array('type' => 'text', 'label'=>false,'name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderQtaMassimaOrder]','value'=>$result['Article']['qta_massima_order'],'size'=>3,'class' => 'noWidth','tabindex'=>($i+1)));?></td>
				<?php 
				if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y') 
					echo '<td>'.$this->Form->input('alert_to_qta',array('type' => 'text', 'label'=>false,'name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderAlertToQta]','value'=>$result['Article']['alert_to_qta'],'size'=>3,'class' => 'noWidth','tabindex'=>($i+1))).'</td>';
				?>
		</tr>
		<tr class="trView" id="trViewId-<?php echo $result['Article']['id'];?>">
			<td colspan="2"></td>
			<td colspan="<?php echo $colspan;?>" id="tdViewId-<?php echo $result['Article']['id'];?>"></td>
		</tr>
	<?php endforeach; ?>
		</table>
	
	</div>
</fieldset>
<?php 
	echo $this->Form->hidden('prod_delivery_id',array('id' => 'prod_delivery_id', 'value' => $prod_delivery_id));
	echo $this->Form->hidden('article_id_selected',array('id' => 'article_id_selected', 'value' => ''));
	echo $this->Form->end("Associa alla consegna gli articoli selezionati");
	
	}
	else  // if(count($results)>0)
		echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Il produttore non ha articoli associati!"));
?>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#article_id_selected_all').click(function () {
		var checked = jQuery("input[name='article_id_selected_all']:checked").val();
		if(checked=='ALL')
			jQuery('input[name=article_id_selected]').prop('checked',true);
		else
			jQuery('input[name=article_id_selected]').prop('checked',false);
	});
	 
	jQuery('#formGas').submit(function() {

		var article_id_selected = '';
		for(i = 0; i < jQuery("input[name='article_id_selected']:checked").length; i++) {
			article_id_selected += jQuery("input[name='article_id_selected']:checked").eq(i).val()+',';
			
			article_id = jQuery("input[name='article_id_selected']:checked").eq(i).val();
			
			prezzo = jQuery("input[name='data[Article]["+article_id+"][ArticlesOrderPrezzo]']").val(); 
			if(prezzo=='' || prezzo==null || prezzo=='0,00' || prezzo=='0.00' || prezzo=='0') {
				alert("Devi indicare l'importo per gli articoli che desideri associare all'ordine");
				jQuery("input[name='data[Article]["+article_id+"][ArticlesOrderPrezzo]']").focus();
				return false;			
			}
			
			pezzi_confezione = jQuery("input[name='data[Article]["+article_id+"][ArticlesOrderPezziConfezione]']").val(); 
			if(pezzi_confezione=='' || pezzi_confezione==null || !isFinite(pezzi_confezione)) {
				alert("Devi indicare il numero di pezzi per confezione per gli articoli che desideri associare all'ordine");
				jQuery("input[name='data[Article]["+article_id+"][ArticlesOrderPezziConfezione]']").focus();
				return false;			
			}
			pezzi_confezione = parseInt(pezzi_confezione);
			if(pezzi_confezione <= 0) {
				alert("Il numero di pezzi per confezione per gli articoli che desideri associare all'ordine deve essere > di zero");
				jQuery("input[name='data[Article]["+article_id+"][ArticlesOrderPezziConfezione]']").focus();
				return false;			
			}
						
			qta_minima = jQuery("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMinima]']").val(); 
			if(qta_minima=='' || qta_minima==null || !isFinite(qta_minima)) {
				alert("Devi indicare la quantità minima per gli articoli associati all'ordine");
				jQuery("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMinima]']").focus();
				return false;			
			}
			if(qta_minima <= 0) {
				alert("La quantità minima per gli articoli che desideri associare all'ordine deve essere > di zero");
				jQuery("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMinima]']").focus();
				return false;			
			}
			
			qta_massima_order = jQuery("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMassimaOrder]']").val(); 
			if(qta_massima_order=='' || qta_massima_order==null || !isFinite(qta_massima_order)) {
				alert("Devi indicare la quantità massima per gli articoli associati all'ordine");
				jQuery("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMassimaOrder]']").focus();
				return false;			
			}
			qta_massima_order = parseInt(qta_massima_order);
			if(qta_massima_order > 0 && qta_massima_order < pezzi_confezione) {
				alert("La quantità massima è inferiore al numero di pezzi in una confezione");
				jQuery("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMassimaOrder]']").focus();
				return false;			
			}
			qta_multipli = jQuery("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMultipli]']").val(); 
			if(qta_multipli=='' || qta_multipli==null || !isFinite(qta_multipli)) {
				alert("Devi indicare di che multiplo dev'essere la quantità per gli articoli associati all'ordine");
				jQuery("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMultipli]']").focus();
				return false;			
			}
			qta_multipli = parseInt(qta_multipli);
			if(qta_multipli <= 0) {
				alert("Il multiplo per gli articoli che desideri associare all'ordine deve essere > di zero");
				jQuery("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMultipli]']").focus();
				return false;			
			}
			
			<?php
			if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y') {
			?>
				alert_to_qta = jQuery("input[name='data[Article]["+article_id+"][ArticlesOrderAlertToQta]']").val(); 
				if(alert_to_qta=='' || alert_to_qta==null || !isFinite(alert_to_qta)) {
					alert("Devi indicare quando avvisare raggiunta una certa quantità per gli articoli associati all'ordine");
					jQuery("input[name='data[Article]["+article_id+"][ArticlesOrderAlertToQta]']").focus();
					return false;			
				}
				if(alert_to_qta <= 0) {
					alert("La quantità che indica quando avvisare per gli articoli che desideri associare all'ordine deve essere > di zero");
					jQuery("input[name='data[Article]["+article_id+"][ArticlesOrderAlertToQta]']").focus();
					return false;			
				}
			<?php
			}
			?>
		}

		if(article_id_selected=='') {
			alert("Devi scegliere almeno un articolo da associare all'ordine");
			return false;
		}	    
		article_id_selected = article_id_selected.substring(0,article_id_selected.length-1);
		
		jQuery('#article_id_selected').val(article_id_selected);

		return true;
	});
});
</script>