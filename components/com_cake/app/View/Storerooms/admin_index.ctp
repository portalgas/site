<div class="storerooms">

	<h2 class="ico-storerooms">
		Cosa c'è in dispensa
		<div class="actions-img">
			<ul>
				<?php 
				if($user->organization['Organization']['hasStoreroomFrontEnd']=='Y')
					echo '<li>'.$this->Html->link(__('Add Storeroom'), array('action' => 'add'),array('class' => 'action actionAdd','title' => __('Management Articles in Storeroom'))).'</li>';
				?>
			</ul>
		</div>
	</h2>


	<?php echo $this->Form->create('FilterStoreroom',array('id'=>'formGasFilter','type'=>'get'));?>
		<fieldset class="filter">
			<legend><?php echo __('Filter Storeroom'); ?></legend>
			<table>
				<tr>
					<td>
						<?php 
							$options = array('label' => false,
											 'escape' => false,
											 'id' => 'supplier_organization_id',
											 'name'=>'FilterStoreroomSupplierId',
											 'default'=>$FilterStoreroomSupplierId,
											 'onChange' => 'javascript:add_list_articles(this);',
											 'empty'=> 'Filtra per produttore',
											 'label'=> '&nbsp;',
											 'options' => $suppliersOrganization);
							if(count($suppliersOrganization) > Configure::read('HtmlSelectWithSearchNum')) 
								$options += array('class'=> 'selectpicker', 'data-live-search' => true); 

							echo $this->Form->input('supplier_organization_id',$options);
						?>
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
if(!empty($results)) {
?>
<div class="storerooms">
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th colspan="2"><?php echo __('N');?></th>
			<th colspan="2"><?php echo __('Name');?></th>
			<th><?php echo __('Conf');?></th>
			<th><?php echo __('PrezzoUnita');?></th>
			<th><?php echo __('Prezzo/UM');?></th>
			<th style="text-align:center;"><?php echo __('StoreroomQta');?></th>
			<th style="text-align:center;"><?php echo __('StoreroomArticleToBooked');?></th>
			<th style="text-align:center;"><?php echo __('StoreroomArticleJustBooked');?></th>
			<th><?php echo __('Importo');?></th>
			<th><?php echo __('Created');?></th>
			<?php 
			echo '<th class="actions" colspan="2">'.__('Actions').'</th>';

	echo '</tr>';
	
	$supplier_organization_id_old = 0;
	foreach ($results as $numResult => $result): 
		if($result['SuppliersOrganization']['id']!=$supplier_organization_id_old) {
			echo '<tr>';
			echo '<td colspan="14" class="trGroup">'.__('Supplier').': ';
			echo '	<span>';
			if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
				echo ' <img width="50" class="userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" alt="'.$result['SupplierOrganization']['name'].'" /> ';
			echo '	</span>';			
			echo $result['SuppliersOrganization']['name'];
			echo '</td>';
			echo '</tr>';
		}
	
		echo '<tr>';
		echo '<td>'.($numResult+1).'</td>';
		echo '<td><a action="storeroom_just_booked-'.$result['Storeroom']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
		echo '<td>';
		if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
			echo '<img width="50" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';
		}		
		echo '</td>';		
		echo '<td>';
		if($result['Storeroom']['stato']=='LOCK') 
			echo '<span class="stato_lock"></span> ';
		echo $result['Storeroom']['name'].'</td>';
		echo '<td>'.$this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']).'</td>';
		echo '<td>'.number_format($result['Storeroom']['prezzo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).' &euro;</td>';
		echo '<td>'.$this->App->getArticlePrezzoUM($result['Storeroom']['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']).'</td>';
		echo '<td style="text-align:center;">';
		echo $result['Storeroom']['qtaTot'];
		echo '</td>';
		echo '<td style="text-align:center;">';
		echo $result['Storeroom']['qtaToBooked'];
		echo '</td>';		
		echo '<td style="text-align:center;">';
		echo $result['Storeroom']['qtaJustBooked'];
		echo '</td>';
		
		echo '<td>'.$this->App->getArticleImporto($result['Storeroom']['prezzo'], $result['Storeroom']['qta']).'</td>';
		echo '<td style="white-space: nowrap;">'.$this->App->formatDateCreatedModifier($result['Storeroom']['created']).'</td>';
		
		echo '<td class="actions-table-img">';
		if($isUserCurrentStoreroom || $result['SuppliersOrganization']['IsReferente']=='Y') 
			echo $this->Html->link(null, array('action' => 'add', $result['Storeroom']['id'], 'supplier_organization_id='.$result['SuppliersOrganization']['id']),array('class' => 'action actionEdit', 'title' => __('Edit')));
		echo '</td>';
		echo '<td class="actions-table">';
		if($user->organization['Organization']['hasStoreroomFrontEnd']=='Y' && $result['SuppliersOrganization']['IsReferente']=='Y')
			echo $this->Html->link(__('Storeroom To User'), array('action' => 'storeroomToUser', $result['Storeroom']['id']),array('title' => __('Storeroom To User')));
		echo '</td>';
		echo '</tr>';
		echo '<tr class="trView" id="trViewId-'.$result['Storeroom']['id'].'">';
		echo '<td colspan="2"></td>';
		echo '<td colspan="12" id="tdViewId-'.$result['Storeroom']['id'].'"></td>';
		echo '</tr>	 ';
		 
		$supplier_organization_id_old=$result['SuppliersOrganization']['id'];
	endforeach; 
		
	echo '</table>';

	echo '</div>';
}
else {
 	if($iniCallPage)
		echo $this->element('boxMsg',array('class_msg' => 'success resultsNotFonud', 'msg' => __('msg_search_no_parameter')));
	else
		echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFonud', 'msg' => "Non ci sono ancora articoli in dispensa"));
}
echo '</div>';
?>
<script type="text/javascript">
$(document).ready(function() {
	<?php 
	/*
	 * devo ripulire il campo hidden che inizia per page perche' dopo la prima pagina sbaglia la ricerca con filtri
	 */
	?>
	$('.filter').click(function() {
		$("input[name^='page']").val('');
	});
	
});		
</script>