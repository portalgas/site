<div class="organizations">
	<h2 class="ico-organizations">
		<?php echo __('Organizations');?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('New Organization'), array('action' => 'add'),array('class' => 'action actionAdd','title' => __('New Organization'))); ?></li>
			</ul>
		</div>
	</h2>
	
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('id');?></th>
			<th><?php echo $this->Paginator->sort('type');?></th>
			<th colspan="2"><?php echo $this->Paginator->sort('Name');?></th>
			<th>Localit&agrave;</th>
			<th>Contatti</th>
			<th>Fatturazione</th>
			<th><?php echo $this->Paginator->sort('Stato');?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	foreach ($results as $result):
		echo '<tr valign="top">';
		echo '<td>';
		echo '<a href="" class="viewDetails" id="view-'.$result['Organization']['id'].'">'.$result['Organization']['id'].'</a>';
		echo '</td>';
		echo '<td>';
		echo $result['Organization']['type'];
		if($result['Organization']['type']=='GAS') echo ' (tmpl '.$result['Organization']['template_id'].')';
		else 
		if($result['Organization']['type']=='PROD') echo ' ('.$result['Organization']['prodSupplierOrganizationId'].')';
		echo '</td>';
		
		echo '<td>';
		echo ' <img width="50" class="userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Organization']['img1'].'" alt="'.$result['Organization']['name'].'" /> ';	
		echo '</td>';
		
		echo '<td>';
			echo $result['Organization']['name']; 
			if(!empty($result['Organization']['descrizione'])) echo '<div class="small">'.$result['Organization']['descrizione'].'</div>';
			if(!empty($result['Organization']['j_seo'])) echo '<div class="small">SEO '.$result['Organization']['j_seo'].'</div>';
		echo '</td>';
		echo '<td>';
			   if(!empty($result['Organization']['indirizzo'])) echo $result['Organization']['indirizzo'].'&nbsp;<br />';
			   if(!empty($result['Organization']['localita'])) echo $result['Organization']['localita'].'&nbsp;';
			   if(!empty($result['Organization']['cap'])) echo $result['Organization']['cap'].'&nbsp;';
			   if(!empty($result['Organization']['provincia'])) echo '('.h($result['Organization']['provincia']).')'; 
		echo '</td>';
		echo '<td>';
			    if(!empty($result['Organization']['telefono'])) echo h($result['Organization']['telefono']).'<br />';
			    if(!empty($result['Organization']['telefono2'])) echo  h($result['Organization']['telefono2']).'<br />';
				if(!empty($result['Organization']['mail'])) echo '<a title="'.__('Email send').'" target="_blank" href="mailto:'.h($result['Organization']['mail']).'" class="link_mailto"></a><br />';
				if(!empty($result['Organization']['www'])) echo '<a title="link al sotto-sito di PortAlGas" href="'.$this->App->traslateWww($result['Organization']['www']).'" class="blank link_www"></a><br />';
				if(!empty($result['Organization']['www2'])) echo '<a title="link esterno al sito dell\'organizzazione" href="'.$this->App->traslateWww($result['Organization']['www2']).'" class="blank link_www"></a><br />';
				
				if(!empty($result['Organization']['sede_logistica_1'])) echo $result['Organization']['sede_logistica_1'].'<br />';
				if(!empty($result['Organization']['sede_logistica_2'])) echo $result['Organization']['sede_logistica_2'].'<br />';
				if(!empty($result['Organization']['sede_logistica_3'])) echo $result['Organization']['sede_logistica_3'].'<br />';
				if(!empty($result['Organization']['sede_logistica_4'])) echo $result['Organization']['sede_logistica_4'];
		echo '</td>';
		echo '<td>'; 
			    if(!empty($result['Organization']['cf'])) echo h($result['Organization']['cf']).'<br />';
			    if(!empty($result['Organization']['piva'])) echo h($result['Organization']['piva']).'<br />';
			    if(!empty($result['Organization']['banca'])) echo $result['Organization']['banca'].'<br />';
			    if(!empty($result['Organization']['banca_iban'])) echo h($result['Organization']['banca_iban']);
		echo '</td>';
		echo '<td title="'.__('toolTipStato').'" class="stato_'.$this->App->traslateEnum($result['Organization']['stato']).'"></td>';
		
		/*
		<td style="white-space: nowrap;">
			<?php echo $this->App->formatDateCreatedModifier($result['Organization']['created']); ?> <br />
			<?php echo $this->App->formatDateCreatedModifier($result['Organization']['modified']); ?>
		</td>
		*/
		?>
		<td class="actions-table-img">
			<?php echo $this->Html->link(null, array('action' => 'edit', $result['Organization']['id']),array('class' => 'action actionEdit','title' => __('Edit'))); ?>
			<?php echo $this->Html->link(null, array('action' => 'delete', $result['Organization']['id']),array('class' => 'action actionDelete','title' => __('Delete'))); ?>			
		</td>
	</tr>
	
	<tr class="contentDetails" id="content-<?php echo $result['Organization']['id'];?>" style="display:none;">
		<td></td>
		<td colspan="8">
		
			<table>
				<tr>
					<th>Parametri Ruoli</th>
					<th>Parametri configurazione</th>
					<th>Campi configurazione</th>
				</tr>
				<tr>
					<?php 
					echo '<td style="white-space: nowrap;">';
					echo '<div title="'.__('HasUserGroupsCassiere').'" class="stato_'.$this->App->traslateEnum($result['Organization']['hasUserGroupsCassiere']).'_int">'.__('Cassiere').'</div> <br />';
					echo '<div title="'.__('HasUserGroupsReferentTesoriere').'" class="stato_'.$this->App->traslateEnum($result['Organization']['hasUserGroupsReferentTesoriere']).'_int">'.__('Referente Tesoriere').'</div> <br />';
					echo '<div title="'.__('HasUserGroupsTesoriere').'" class="stato_'.$this->App->traslateEnum($result['Organization']['hasUserGroupsTesoriere']).'_int">'.__('Tesoriere').'</div> <br />';
					echo '<div title="'.__('HasUserGroupsStoreroom').'" class="stato_'.$this->App->traslateEnum($result['Organization']['hasUserGroupsStoreroom']).'_int">'.__('Storeroom').'</div> <br />';
					echo '</td>';
					
					echo '<td style="white-space: nowrap;">';
					echo '<div title="'.__('toolTipHasBookmarsArticles').'" class="stato_'.$this->App->traslateEnum($result['Organization']['hasBookmarsArticles']).'_int">'.__('HasBookmarsArticles').'</div> <br />';
					echo '<div title="'.__('toolTipHasArticlesOrder').'" class="stato_'.$this->App->traslateEnum($result['Organization']['hasArticlesOrder']).'_int">'.__('HasArticlesOrder').'</div> <br />';
					echo '<div title="'.__('toolTipHasVisibility').'" class="stato_'.$this->App->traslateEnum($result['Organization']['hasVisibility']).'_int">'.__('HasVisibility').'</div> <br />';
					echo '<div title="'.__('toolTipHasTrasport').'" class="stato_'.$this->App->traslateEnum($result['Organization']['hasTrasport']).'_int">'.__('HasTrasport').'</div> <br />';
					echo '<div title="'.__('toolTipHasCostMore').'" class="stato_'.$this->App->traslateEnum($result['Organization']['hasCostMore']).'_int">'.__('HasCostMore').'</div> <br />';
					echo '<div title="'.__('toolTipHasCostLess').'" class="stato_'.$this->App->traslateEnum($result['Organization']['hasCostLess']).'_int">'.__('HasCostLess').'</div> <br />';
					echo '<div title="'.__('toolTipHasValidate').'" class="stato_'.$this->App->traslateEnum($result['Organization']['hasValidate']).'_int">'.__('HasValidate').'</div> <br />';
					echo '<div title="'.__('toolTipHasStoreroom').'" class="stato_'.$this->App->traslateEnum($result['Organization']['hasStoreroom']).'_int">'.__('HasStoreroom').'</div> <br />';
					echo '<div title="'.__('toolTipHasStoreroomFrontEnd').'" class="stato_'.$this->App->traslateEnum($result['Organization']['hasStoreroomFrontEnd']).'_int">'.__('HasStoreroomFrontEnd').'</div> <br />';
					echo '<div title="'.__('toolTipPayToDelivery').'">'.__('PayToDelivery').' '.$result['Organization']['payToDelivery'].'</div> <br />';
					echo '<div title="'.__('toolTipHasDes').'" class="stato_'.$this->App->traslateEnum($result['Organization']['hasDes']).'_int">'.__('HasDes').'</div> <br />';
					echo '<div title="'.__('toolTipHasDesReferentAllGas').'" class="stato_'.$this->App->traslateEnum($result['Organization']['hasDesReferentAllGas']).'_int">'.__('HasDesReferentAllGas').'</div> <br />';
					echo '</td>';
					
					echo '<td style="white-space: nowrap;">';
					echo '<div class="stato_'.$this->App->traslateEnum($result['Organization']['hasFieldArticleCodice']).'_int">'.__('HasFieldArticleCodice').'</div> <br />';
					echo '<div class="stato_'.$this->App->traslateEnum($result['Organization']['hasFieldArticleIngredienti']).'_int">'.__('HasFieldArticleIngredienti').'</div> <br />';
					echo '<div class="stato_'.$this->App->traslateEnum($result['Organization']['hasFieldArticleAlertToQta']).'_int">'.__('HasFieldArticleAlertToQta').'</div> <br />';
					echo '<div class="stato_'.$this->App->traslateEnum($result['Organization']['hasFieldPaymentPos']).'_int">'.__('HasFieldPaymentPos');
                    if($result['Organization']['hasFieldPaymentPos']=='Y')
                        echo ' - '.$result['Organization']['paymentPos'].' &euro;';
                    echo '</div> <br />';
					echo '<div class="stato_'.$this->App->traslateEnum($result['Organization']['hasFieldArticleCategoryId']).'_int">'.__('HasFieldArticleCategoryId').'</div> <br />';
					echo '<div class="stato_'.$this->App->traslateEnum($result['Organization']['hasFieldSupplierCategoryId']).'_int">'.__('HasFieldSupplierCategoryId').'</div> <br />';
					echo '<div class="stato_'.$this->App->traslateEnum($result['Organization']['hasFieldFatturaRequired']).'_int">'.__('HasFieldFatturaRequired').'</div>';
					echo '</td>';
					?>				
				</tr>
			</table>
			
			<h3>Pay</h3>
			
			<?php
					echo 'Mail contatto '.$result['Organization']['payMail'];
					echo '<br />Riferimento contatto '.$result['Organization']['payContatto'];
					echo '<br />Intestatario '.$result['Organization']['payIntestatario'];
					echo '<br />Indirizzo '.$result['Organization']['payIndirizzo'];
					echo '<br />Cap '.$result['Organization']['payCap'];
					echo '<br />Città '.$result['Organization']['payCitta'];
					echo '<br />Prov '.$result['Organization']['payProv'];
					echo '<br />Codice Fiscale '.$result['Organization']['payCf'];
					echo '<br />P.iva '.$result['Organization']['payPiva'];
			?>		
		
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>	</p>

	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	?>
	</div>
</div>

<?php 
	echo $this->element('legendaTemplate');
	
	echo $this->element('legendaOrganization');
?>

<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery('.viewDetails').click(function() {	
		var dataElement = jQuery(this).attr('id');
		dataElementArray = dataElement.split('-');
		var idElement = dataElementArray[1];

		if(jQuery('#content-'+idElement).css('display')=='none')  
			jQuery('#content-'+idElement).css('display', 'table-row');
		else
			jQuery('#content-'+idElement).css('display', 'none');

		return false;
	});
		
});
</script>