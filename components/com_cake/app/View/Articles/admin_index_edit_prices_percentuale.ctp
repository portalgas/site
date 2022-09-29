<?php
if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
	$colspan = 8;
else
	$colspan = 7;
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
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

	<?php echo $this->Form->create('FilterArticle', ['id'=>'formGasFilter','type'=>'get']);?>
		<fieldset class="filter">
			<legend><?php echo __('Filter Articles'); ?></legend>
			<table>
				<tr>
					<td>
						<?php 
						$options = [
								 'label' => '&nbsp;',
								 'name'=>'FilterArticleSupplierId',								 
								 'options' => $ACLsuppliersOrganization, 
								 'default'=>$FilterArticleSupplierId,
								 'required' => 'false',
								 'escape' => false];
						if(count($ACLsuppliersOrganization) > 1) 
							$options += ['data-placeholder'=> __('FilterToSuppliers'), 'empty' => __('FilterToSuppliers')];								 
						if(count($ACLsuppliersOrganization) > Configure::read('HtmlSelectWithSearchNum')) 
							$options += array('class'=> 'selectpicker', 'data-live-search' => true); 				
						echo $this->Form->input('supplier_organization_id',$options); ?>
					</td>
					<td>
						<?php echo $this->Form->reset('Reset', ['value' => 'Reimposta','class' => 'reset']); ?>
					</td>
					<td>
						<?php echo $this->Form->end(['label' => __('Filter'), 'class' => 'filter', 'div' => ['class' => 'submit filter', 'style' => 'display:none']]); ?>
					</td>
				</tr>	
			</table>
		</fieldset>		
				

	<?php
	if(count($results)>0) {
		echo $this->Form->create('Article', ['id' => 'formGas']);
    /*
	<table cellpadding="0" cellspacing="0">
	<tr>
		<th>Articoli</th>
		<th></th>
		<th>Diminuzione %</th>
        <th>Aumento %</th>
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
			<input type="text" name="prezzo_diminuzione_all" class="form-control" value="" />
		</td>
		<td>
			<input type="text" name="prezzo_diminuzione_all" class="form-control" value="" />
		</td>
	</tr>
	</table>
    <br />
	*/
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
        <th>Diminuzione %</th>
        <th>Aumento %</th>
        <th>Percentuale</th>
        <th>Prezzo nuovo</th>
        <th><?php echo $this->Paginator->sort('stato',__('Stato'));?></th>
	</tr>
	<?php
        $tabindex = 1;
        foreach ($results as $i => $result) {
            echo '<tr class="view">';
            echo '<td><a action="articles-'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
            echo '<td>'.($i+1).'</td>';
            echo '<td>'.$result['SuppliersOrganization']['name'].'</td>';

            if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
                echo '<td>'.$result['Article']['codice'].'</td>';
            echo '<td>';
            echo $result['Article']['name'];
            echo '</td>';
            echo '<td>';
            echo $result['Article']['prezzo_e'];
            echo '</td>';
            echo '<td>';
            if($result['Article']['owner'])
                echo '<input type="text" data-attr-id="'.$result['Article']['id'].'" class="perc-less double form-control" tabindex="'.$tabindex++.'" name="prezzo_diminuzione['.$result['Article']['id'].']" value="" />';
            echo '</td>';
            echo '<td>';
            if($result['Article']['owner'])
                echo '<input type="text" data-attr-id="'.$result['Article']['id'].'" class="perc-more double form-control" tabindex="'.$tabindex++.'" name="prezzo_aumento['.$result['Article']['id'].']" value="" />';
            echo '</td>';
            echo '<td><div class="percentuale-'.$result['Article']['id'].'"></div></td>';
            echo '<td style="white-space: nowrap;">';
            if($result['Article']['owner']) {
                echo '<input class="double" type="hidden" name="data[Article][prezzo_old]['.$result['Article']['id'].']" value="'.$result['Article']['prezzo_'].'" />';
                echo '<input class="double" type="hidden" name="data[Article][prezzo_old_js]['.$result['Article']['id'].']" value="'.$result['Article']['prezzo'].'" />';
                echo '<input class="double form-control" type="text" tabindex="'.$tabindex++.'" name="data[Article][prezzo]['.$result['Article']['id'].']" value="" style="display:inline" />&nbsp;&euro;';
            }
            else {
                echo '<span class="label label-info">'.__('OwnerArticle').': '.__('ArticlesOwnerSUPPLIER').'</span>';
                // echo $result['Article']['prezzo_e'];
            }
            echo '</td>';
            echo '<td title="'.__('toolTipStato').'" class="stato_'.$this->App->traslateEnum($result['Article']['stato']).'"></td>';
            echo '</tr>';
            echo '<tr class="trView" id="trViewId-'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'">';
            echo '<td colspan="2"></td>';
            echo '<td colspan="'.$colspan.'" id="tdViewId-'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'"></td>';
            echo '</tr>';
        } // end for
        echo '</table>';
	   
		echo $this->Form->hidden('FilterArticleSupplierId', ['id' => 'FilterArticleSupplierId','value' => $FilterArticleSupplierId]);
		echo $this->Form->hidden('updateArticlesOrder', ['id' => 'update_articles_order','value' => 'N']);
		
		echo $this->Form->submit("Aggiorna i prezzi agli articoli", ['id' => 'updateArticlesOrder_N', 'div'=> 'submitMultiple']);
		
		if($this->App->isUserPermissionArticlesOrder($user)) 
			echo $this->Form->submit("Aggiorna i prezzi agli articoli e anche agli articolo associati agli ordini", ['id' => 'updateArticlesOrder_Y', 'div'=> 'submitMultiple', 'class' => 'buttonBlu']);
		
		echo $this->Form->end();
	} // end if(count($results)>0)
	?>	
</div>

<script type="text/javascript">
$(document).ready(function() {

    $('.double').focusout(function() {validateNumberField(this,'percentuale');});

    $('.perc-less, .perc-more').change(function() {
        let perc = Number($(this).val());

        // console.log(perc, 'perc');
        // console.log(operation, 'operation');
        let article_id = $(this).attr('data-attr-id');
        // console.log(article_id, 'article_id');

        let prezzo = Number($('input[name="data[Article][prezzo_old_js]['+article_id+']"').val());
        // console.log(prezzo, 'prezzo');

        let prezzo_perc = (prezzo / 100 * perc);
        // console.log(prezzo_perc, 'prezzo_perc');


        if($(this).hasClass('perc-less')) {
            if(perc>=100) {
                alert("La percentuale è troppo alta!");
                $(this).val('');
                $(this).focus();
                return;
            }
            prezzo = (prezzo - prezzo_perc);
        }
        else {
            prezzo = (prezzo + prezzo_perc);
        }
        // console.log(prezzo, 'prezzo +- prezzo_perc');

        $('input[name="data[Article][prezzo]['+article_id+']"').val(number_format(prezzo,2,',','.'));
        $('.percentuale-'+article_id).html(number_format(prezzo_perc,2,',','.')+' €');
    });

	<?php
	if(!empty($FilterArticleSupplierId) && empty($results)) {
	?>
	$('#formGasFilter').submit();
	<?php
	}
	?>
});		
</script>