<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Articles'),'context_articles_index');
$this->Html->addCrumb(__('Edit Articles Prices'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>

<div class="articles">
	<h2 class="ico-articles">
		<?php echo __('Edit Articles Prices');?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('Edit Articles Prices Rate'), array('action' => 'index_edit_prices_percentuale'),array('class' => 'action actionPrice','title' => __('Edit Articles Prices Rate'))); ?></li>
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
				

	<?php
	if(count($results)>0) {
		echo $this->Form->create('Article',array('id' => 'formGas'));
	?>
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
			<th>Prezzo nuovo</th>
			<th><?php echo $this->Paginator->sort('stato',__('Stato'));?></th>
	</tr>
	<?php
	foreach ($results as $i => $result):
	?>
	<tr class="view">
		<td><a action="articles-<?php echo $result['Article']['id']; ?>" class="actionTrView openTrView" href="#" title="<?php echo __('Href_title_expand');?>"></a></td>
		<td><?php echo ($i+1);?></td>
		<td><?php echo $result['SuppliersOrganization']['name'];?></td>
		<?php
		if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
			echo '<td>'.$result['Article']['codice'].'</td>';
		?>
		<td><?php echo $result['Article']['name']; ?></td>
		<td><?php echo $result['Article']['prezzo_']; ?>&nbsp;&euro;</td>
		<td>
			
			<?php
			/*
			 * se Organization.id == Article.organization_id 
			 *		e' il proprietario degli articoli
			 * se NO, gli articoli sono di ProdGas
			 *
			 * se SuppliersOrganization.owner_articles == 'REFERENT'
			 *		e' il proprietario degli articoli
			 * se NO, gli articoli sono di ProdGasSupplier 
			 */
			if($user->organization['Organization']['id']==$result['Article']['organization_id'] &&
			   $result['SuppliersOrganization']['owner_articles']=='REFERENT') {
				   echo '<input class="double" type="hidden" name="data[Article][prezzo_old]['.$result['Article']['id'].']" value="'.$result['Article']['prezzo_'].'" />';
				   echo '<input class="double" type="text" tabindex="<?php echo ($i+1);?>" name="data[Article][prezzo]['.$result['Article']['id'].']" value="'.$result['Article']['prezzo_'].'" />&nbsp;&euro;';
			   }
			 else
				 echo $result['Article']['prezzo_e'];
			 ?>
		</td>
		<td title="<?php echo __('toolTipStato');?>" class="stato_<?php echo $this->App->traslateEnum($result['Article']['stato']); ?>"></td>
	</tr>
	<tr class="trView" id="trViewId-<?php echo $result['Article']['id'];?>">
		<td colspan="2"></td>
		<td colspan="<?php echo ($user->organization['Organization']['hasFieldArticleCodice']=='Y') ? '6' :'5';?>" id="tdViewId-<?php echo $result['Article']['id'];?>"></td>
	</tr>	
<?php endforeach;
		
		echo '</table>';
		
		echo $this->Form->hidden('FilterArticleSupplierId',array('id' => 'FilterArticleSupplierId','value' => $FilterArticleSupplierId));
		echo $this->Form->hidden('updateArticlesOrder',array('id' => 'update_articles_order','value' => 'N'));
	
		echo $this->Form->submit("Aggiorna i prezzi degli articoli",array('id' => 'updateArticlesOrder_N', 'div'=> 'submitMultiple'));
		
		if($this->App->isUserPermissionArticlesOrder($user)) 
			echo $this->Form->submit("Aggiorna i prezzi degli articoli e anche agli articolo associati agli ordini",array('id' => 'updateArticlesOrder_Y', 'div'=> 'submitMultiple', 'class' => 'buttonBlu'));
		
		echo $this->Form->end();
    ?>
        <script type="text/javascript">
        jQuery(document).ready(function() {
			
			jQuery('.double').focusout(function() {validateNumberField(this,'importo');});
			
			jQuery('.double').focusout(function() {
				var prezzo = jQuery(this).val();

				if(prezzo=='' || prezzo==undefined) {
					alert("Devi indicare il prezzo da associare all'articolo");
					jQuery(this).val("0,00");
					jQuery(this).focus();
					return false;
				}	
				
				if(prezzo=='0,00') {
					alert("Il prezzo dev'essere indicato con un valore maggior di 0");
					jQuery(this).focus();
					return false;
				}				
			});

		
            jQuery('#updateArticlesOrder_N').click(function() {	
                jQuery('#update_articles_order').val('N');
                return true;
            });
            jQuery('#updateArticlesOrder_Y').click(function() {
                if(!confirm("Sei sicuro di voler modificare anche il prezzo degli articoli associati agli ordini?"))
                    return false;

                jQuery('#update_articles_order').val('Y');
                return true;
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
    <?php    
	}
    else
        echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFonud'));    
	?>
</div>