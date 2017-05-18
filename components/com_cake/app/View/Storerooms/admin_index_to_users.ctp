<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('Storeroom'), array('controller' => 'Storerooms', 'action' => 'index'));
$this->Html->addCrumb('Cosa è stato acquistato');
echo $this->Html->getCrumbList(array('class'=>'crumbs'));  
?>
<div class="storerooms">

	<h2 class="ico-storerooms">
		<?php echo __('Storeroom');?>
	</h2>


	<?php echo $this->Form->create('FilterStoreroom',array('id'=>'formGasFilter','type'=>'get'));?>
		<fieldset class="filter">
			<legend><?php echo __('Filter Storeroom'); ?></legend>
			<table>
				<tr>
					<td>
						<?php echo $this->Form->input('delivery_id',array('label' => false,'options' => $deliveries,'empty' => 'Filtra per consegne','name'=>'FilterStoreroomDeliveryId','default'=>$FilterStoreroomDeliveryId,'escape' => false)); ?>
					</td>
					<td>
						<div class="input">
							<label for="FilterStoreroomGroupBy">Raggruppa per</label>
								<input type="radio" name="FilterStoreroomGroupBy" id="FilterStoreroomGroupBySUPPLIERS" value="SUPPLIERS" <?php if ($FilterStoreroomGroupBy=='SUPPLIERS') echo 'checked="checked"';?> /> 
							<label style="width:80px !important;margin-left:10px;" for="FilterGroupBySUPPLIERS">Produttori</label>
								<input type="radio" name="FilterStoreroomGroupBy" id="FilterStoreroomGroupByUSERS" value="USERS" <?php if ($FilterStoreroomGroupBy=='USERS') echo 'checked="checked"';?> />
							<label style="width:80px !important;margin-left:10px;" for="FilterStoreroomGroupByUSERS">Utenti</label>
						</div>
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


<div class="storerooms">
<?php	
if(!empty($results )) {
	if($FilterStoreroomGroupBy=='SUPPLIERS') {
		$delivery_id_old = 0;
		$supplier_organization_id_old = 0;
		$count=0;
		foreach ($results as $i => $result): 
	
			if($result['Storeroom']['delivery_id']!=$delivery_id_old) {
				$count=1;
				if($i>0) echo '</table>';
			
				echo "<h2>".__('Delivery')." ";
				if($result['Delivery']['sys']=='N')
					echo $result['Delivery']['luogoData'];
				else 
					echo $result['Delivery']['luogo'];
				echo '</h2>';
				
				if($result['Delivery']['isToStoreroom']=='N')
						echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "La consegna non è abilitata per gestire la dispensa!")); 
				?>
				<table cellpadding="0" cellspacing="0">
				<tr>
					<th><?php echo __('N');?></th>
					<th>Utente</th>
					<th><?php echo __('Name');?></th>
					<th><?php echo __('Conf');?></th>
					<th><?php echo __('PrezzoUnita');?></th>
					<th><?php echo __('Prezzo/UM');?></th>
					<th><?php echo __('Acquistati');?></th>
					<th><?php echo __('Importo');?></th>
					<th><?php echo __('Created');?></th>					
					<th class="actions"><?php echo __('Actions');?></th>
				</tr>
			
			<?php
			}
			else 
				$count++;
	

			if($result['Article']['supplier_organization_id']!=$supplier_organization_id_old) {
				echo '<tr>';
				echo '<td colspan="10" class="trGroup">'.__('Supplier').': '.$result['SuppliersOrganization']['SuppliersOrganization']['name'];
				echo '</td>';
				echo '</tr>';
			}
		?>
		<tr>
			<td><?php echo ($count); ?></td>
			<td><?php echo $result['User']['name']; ?></td>
			<td><?php 
				if($result['Storeroom']['stato']=='LOCK') echo '<span class="stato_lock"></span> ';
				echo $result['Storeroom']['name']; ?></td>
			<td><?php echo $this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']);?></td>
			<td><?php echo $result['Storeroom']['prezzo_e'];?></td>
			<td><?php echo $this->App->getArticlePrezzoUM($result['Article']['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']);?></td>
			<?php
				echo '<td>';
				if($result['Storeroom']['qta']==1)
					echo $result['Storeroom']['qta'].' acquistato';
				else
					echo $result['Storeroom']['qta'].' acquistati';
				echo '</td>';
			?>
			<td><?php echo $this->App->getArticleImporto($result['Storeroom']['prezzo'], $result['Storeroom']['qta']);?></td>
			<td style="white-space: nowrap;"><?php echo $this->App->formatDateCreatedModifier($result['Storeroom']['created']); ?></td>
			<td class="actions-table-img">
				<?php
				if($result['SuppliersOrganization']['IsReferente']=='Y') 
					echo $this->Html->link(null, array('action' => 'edit', $result['Storeroom']['id']),array('class' => 'action actionEdit','title' => 'Modifica')); ?>
			</td>
		</tr>
	<?php 
		$delivery_id_old=$result['Storeroom']['delivery_id'];
		$supplier_organization_id_old=$result['Article']['supplier_organization_id'];
		endforeach; ?>
		</table>

	<?php
	}
	else 
	if($FilterStoreroomGroupBy=='USERS') {
		$delivery_id_old = 0;
		$user_id_old = 0;
		$count=0;
		foreach ($results as $i => $result): 
	
			if($result['Storeroom']['delivery_id']!=$delivery_id_old) {
				$count=1;
				if($i>0) echo '</table>';
				
				echo "<h2>".__('Delivery')." ";
				if($result['Delivery']['sys']=='N')
					echo $result['Delivery']['luogoData'];
				else
					echo $result['Delivery']['luogo'];
				echo '</h2>';
				
				if($result['Delivery']['isToStoreroom']=='N')
						echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "La consegna non è abilitata per gestire la dispensa!")); 
				?>
				<table cellpadding="0" cellspacing="0">
				<tr>
						<th><?php echo __('N');?></th>
						<th><?php echo __('Supplier');?></th>
						<th><?php echo __('Name');?></th>
						<th><?php echo __('Conf');?></th>
						<th><?php echo __('PrezzoUnita');?></th>
						<th><?php echo __('Prezzo/UM');?></th>
						<th><?php echo __('qta');?></th>
						<th><?php echo __('Importo');?></th>
						<th><?php echo __('Created');?></th>
						<th class="actions"><?php echo __('Actions');?></th>
				</tr>
			
			<?php
			}
			else 
				$count++;
	
		
			if($result['Storeroom']['user_id']!=$user_id_old) {
				echo '<tr>';
				echo '<td colspan="10" class="trGroup">Utente: '.$result['User']['name'];
				echo '</td>';
				echo '</tr>';
			}
		?>
		<tr>
			<td><?php echo ($count); ?></td>
			<td><?php 
				echo $result['SuppliersOrganization']['SuppliersOrganization']['name']; ?></td>
			<td><?php 
				if($result['Storeroom']['stato']=='LOCK') echo '<span class="stato_lock"></span> ';
				echo $result['Storeroom']['name']; ?></td>
			<td><?php echo $this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']);?></td>
			<td><?php echo $result['Storeroom']['prezzo_e'];?></td>
			<td><?php echo $this->App->getArticlePrezzoUM($result['Storeroom']['prezzo'], $result['Article']['prezzo'], $result['Article']['um'], $result['Article']['um_riferimento']);?></td>
			<?php
				echo '<td>';
				if($result['Storeroom']['qta']==1)
					echo $result['Storeroom']['qta'].' acquistato';
				else
					echo $result['Storeroom']['qta'].' acquistati';
				echo '</td>';
			?>
			<td><?php echo $this->App->getArticleImporto($result['Storeroom']['prezzo'], $result['Storeroom']['qta']);?></td>
			<td style="white-space: nowrap;"><?php echo $this->App->formatDateCreatedModifier($result['Storeroom']['created']); ?></td>
			<td class="actions-table-img">
				<?php echo $this->Html->link(null, array('action' => 'edit', $result['Storeroom']['id']),array('class' => 'action actionEdit','title' => 'Modifica')); ?>
			</td>
		</tr>
	<?php 
		$delivery_id_old=$result['Storeroom']['delivery_id'];
		$user_id_old=$result['Storeroom']['user_id'];
		endforeach; ?>
		</table>	
	<?php
	}
} //  if(empty($results ))
else
	echo $this->element('boxMsg',array('class_msg' => 'message'));	
?>	
	</div>
</div>
<script type="text/javascript">
jQuery(document).ready(function() {
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