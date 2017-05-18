<?php
if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
	$colspan = 8;
else
	$colspan = 7;
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Articles'), array('controller' => 'Articles', 'action' => 'context_articles_index'));
$this->Html->addCrumb(__('Edit Articles Prices Rate'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>


<div class="articles">
	<h2 class="ico-articles">
		<?php echo __('Edit Articles Prices Rate');?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('Edit Articles Prices'), array('action' => 'index_edit_prices_default'),array('class' => 'action actionPrice','title' => __('Edit Articles Prices'))); ?></li>
			</ul>
		</div>		
	</h2>

	<?php echo $this->Form->create('FilterArticle',array('id'=>'formGasFilter','type'=>'get'));?>
		<fieldset class="filter">
			<legend><?php echo __('Filter Articles'); ?></legend>
			<table>
				<tr>
					<td>
						<?php 
						$options = array(
								 'data-placeholder' => 'Filtra per produttore',
								 'label' => false,
								 'name'=>'FilterArticleSupplierId',								 
								 'options' => $ACLsuppliersOrganization, 
								 'default'=>$FilterArticleSupplierId,
								 'required' => 'false',
								 'empty' => 'Filtra per produttore',
								 'escape' => false);
						if(count($ACLsuppliersOrganization) > Configure::read('HtmlSelectWithSearchNum')) 
							$options += array('class'=> 'selectpicker', 'data-live-search' => true); 				
						echo $this->Form->input('supplier_organization_id',$options); ?>
					</td>
					<td>
						<?php echo $this->Form->reset('Reset', array('value' => 'Reimposta','class' => 'reset')); ?>
					</td>
					<td>
						<?php echo $this->Form->end(array('label' => __('Filter'), 'class' => 'filter', 'div' => array('class' => 'submit filter', 'style' => 'display:none'))); ?>
					</td>
				</tr>	
			</table>
		</fieldset>		
				
				
		<div class="legenda">
			<?php 	echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => Configure::read('sys_function_not_implement')));?>
		</div>				


		
	<?php
	if(count($results)>0) {
		echo $this->Form->create('Article',array('id' => 'formGas'));
	?>
	<table cellpadding="0" cellspacing="0">
	<tr>
		<th>Articoli</th>
		<th></th>
		<th>Diminuzione %</th>
		<th>Aumento %</th>
		<th></th>
	</tr>	
	<tr class="view">
		<td>Tutti gli articolo</td>
		<td>
		<?php 
		echo $this->App->drawFormRadio('Article','stato',array('options' => $stato, 
															   'value'=>'Y', 'label'=>'Attivi'));
		?>
		</td>
		<td>
			<input type="text" name="prezzo_diminuzione_all" value="" />
		</td>
		<td>
			<input type="text" name="prezzo_diminuzione_all" value="" />
		</td>
		<td></td>
	</tr>
	</table>
	
	<br />
	
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th></th>
			<th><?php echo __('N');?></th>
			<th><?php echo $this->Paginator->sort('supplier_id');?></th>
			<?php 
			if($user->organization['Organization']['hasFieldArticleCodice']=='Y') 
				echo '<th>'.$this->Paginator->sort('codice').'</th>';
			?>
			<th><?php echo $this->Paginator->sort('name','Nome prodotto');?></th>
			<th><?php echo $this->Paginator->sort('Prezzo');?></th>
			<th>Diminuzione %</th>
			<th>Aumento %</th>
			<th>Prezzo nuovo</th>
			<th><?php echo $this->Paginator->sort('stato',__('Stato'));?></th>
	</tr>
	<?php
	$tabindex = 1;
	foreach ($results as $i => $result):
	?>
	<tr class="view">
		<td><a action="articles-<?php echo $result['Article']['id']; ?>" class="actionTrView openTrView" href="#" title="<?php echo __('Href_title_expand');?>"></a></td>
		<td><?php echo ($i+1);?></td>
		<td><?php echo $result['SuppliersOrganization']['name']; ?></td>
		<?php 
		if($user->organization['Organization']['hasFieldArticleCodice']=='Y') 
			echo '<td>'.$result['Article']['codice'].'</td>'; 
		?>
		<td><?php echo $result['Article']['name']; ?></td>
		<td><?php echo $result['Article']['prezzo_']; ?>&nbsp;&euro;</td>
		<td>		
			<input type="text" tabindex="<?php echo $tabindex++;?>" name="data[Article][prezzo_diminuzione][<?php echo $result['Article']['id'];?>]" value="" />
		</td>
		<td>
			<input type="text" tabindex="<?php echo $tabindex++;?>" name="data[Article][prezzo_aumento][<?php echo $result['Article']['id'];?>]" value="" />
		</td>
		<td><?php echo $result['Article']['prezzo']; ?>&nbsp;&euro;</td>
		<td title="<?php echo __('toolTipStato');?>" class="stato_<?php echo $this->App->traslateEnum($result['Article']['stato']); ?>"></td>
	</tr>
	<tr class="trView" id="trViewId-<?php echo $result['Article']['id'];?>">
		<td colspan="2"></td>
		<td colspan="<?php echo $colspan;?>" id="tdViewId-<?php echo $result['Article']['id'];?>"></td>
	</tr>	
<?php endforeach; 
	    echo '</table>';
	   
		echo $this->Form->hidden('FilterArticleSupplierId',array('id' => 'FilterArticleSupplierId','value' => $FilterArticleSupplierId));
		echo $this->Form->hidden('updateArticlesOrder',array('id' => 'update_articles_order','value' => 'N'));
		
		echo $this->Form->submit("Aggiorna i prezzi agli articoli",array('id' => 'updateArticlesOrder_N', 'div'=> 'submitMultiple'));

		if($this->App->isUserPermissionArticlesOrder($user)) 
			echo $this->Form->submit("Aggiorna i prezzi agli articoli e anche agli articolo associati agli ordini",array('id' => 'updateArticlesOrder_Y', 'div'=> 'submitMultiple', 'class' => 'buttonBlu'));
		
		echo $this->Form->end();
	}
	?>	
</div>

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#updateArticlesOrder_N').click(function() {	
		jQuery('#update_articles_order').val('N');

		alert("<?php echo Configure::read('sys_function_not_implement');?>");
		return false;
	});
	jQuery('#updateArticlesOrder_Y').click(function() {
		if(!confirm("Sei sicuro di voler modificare anche il prezzo degli articoli associati agli ordini?"))
			return false;
			
		jQuery('#update_articles_order').val('Y');

		alert("<?php echo Configure::read('sys_function_not_implement');?>");
		return false;
	});
	
	<?php
	if(!empty($FilterArticleSupplierId) && empty($results)) {
	?>
	jQuery('#formGasFilter').submit();
	<?php
	}
	?>		

	<?php 
	/*
	 * devo ripulire il campo hidden che inizia per page perche' dopo la prima pagina sbaglia la ricerca con filtri
	 */
	?>
	jQuery('.filter').click(function() {
		jQuery("input[name^='page']").val('');
	});
	
});		
</script>