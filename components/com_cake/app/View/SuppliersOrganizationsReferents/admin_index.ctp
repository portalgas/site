<div class="users">

	<h2 class="ico-suppliersOrganizationsReferents">
		<?php echo $userGroups[$group_id]['name'].' <small><i>('.$userGroups[$group_id]['descri'].')</i></small>';?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('Gest').' '.$userGroups[$group_id]['name'], array('action' => 'edit', null, 'supplier_organization_id=0', 'group_id='.$group_id),array('class' => 'action actionEdit','title' => __('Gest').' '.$userGroups[$group_id]['name'])); ?></li>
			</ul>
		</div>
	</h2>
	
	<?php echo $this->Form->create('FilterSupplierOrganization',array('id'=>'formGasFilter','type'=>'get'));?>
		<fieldset class="filter">
			<legend><?php echo __('Filter Supplier Organization'); ?></legend>
			<table>
				<tr>
					<td style="width:30%;">
						<?php	echo $this->Ajax->autoComplete('FilterSuppliersOrganizationsReferentUserName', 
									   Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteUsers_name&format=notmpl',
										array('label' => 'Nominativo','name'=>'FilterSuppliersOrganizationsReferentUserName','value'=>$FilterSuppliersOrganizationsReferentUserName,'size'=>'50','escape' => false));
						?>
					</td>
					<td style="width:30%;">
						<?php echo $this->Form->input('users',array('label' => false,'options' => $users, 'empty' => 'Tutti gli utenti','name'=>'FilterSuppliersOrganizationsReferentUserId','default'=>$FilterSuppliersOrganizationsReferentUserId,'escape' => false,
														'class'=> 'selectpicker', 'data-live-search' => true)); ?>
					</td>
					<td style="width:30%;">
						<?php echo $this->Form->input('supplier_organization_id',array('label' => false,'options' => $ACLsuppliersOrganization,'empty' => 'Tutti i produttori','name'=>'FilterSuppliersOrganizationsReferentId','default'=>$FilterSuppliersOrganizationsReferentId,'escape' => false,
														'class'=> 'selectpicker', 'data-live-search' => true)); ?>
					</td>
					<td style="width:5%;">
						<?php echo $this->Form->reset('Reset', array('value' => 'Reimposta','class' => 'reset')); ?>
					</td>
					<td style="width:5%;">
						<?php echo $this->Form->end(array('label' => __('Filter'), 'class' => 'filter', 'div' => array('class' => 'submit filter', 'style' => 'display:none'))); ?>
					</td>
				</tr>	
			</table>
		</fieldset>					
			
<?php
if(!empty($results)) {
?>
	
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo __('N');?></th>
			<th colspan="2"><?php echo $this->Paginator->sort('supplier_organization_id');?></th>
			<th>Frequenza</th>
			<th></th>
			<th colspan="2"><?php echo $this->Paginator->sort('user_id');?></th>
			<th><?php echo $this->Paginator->sort('type');?></th>
			<th><?php echo $this->Paginator->sort('created');?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	foreach ($results as $i => $result):
		$numRow = ((($this->Paginator->counter(array('format'=>'{:page}'))-1) * $SqlLimit) + $i+1); ?>
	<tr>
		<td><?php echo $numRow; ?></td>
		<td><?php 
			if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
				echo ' <img width="50" class="userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" alt="'.$result['SupplierOrganization']['name'].'" /> ';	
			?>
		</td>		
		<td>		
			<?php echo $result['SuppliersOrganization']['name']; ?>
		</td>	
		<td>		
			<?php echo $result['SuppliersOrganization']['frequenza']; ?>
		</td>
		<td><?php echo $this->App->drawUserAvatar($user, $result['User']['id'], $result['User']); ?></td>
		<td>
			<?php echo $result['User']['name']; ?>
		</td>
		<td>
			<?php 
			if(!empty($result['User']['email']))
				echo '<a title="'.__('Email send').'" target="_blank" href="mailto:'.$result['User']['email'].'">'.$result['User']['email'].'</a>'; 
			?>
		</td>
		<td>		
			<?php echo $this->App->traslateEnum($result['SuppliersOrganizationsReferent']['type']); ?>
		</td>
		<td style="white-space: nowrap;"><?php echo $this->Time->i18nFormat($result['SuppliersOrganizationsReferent']['created']); ?></td>
		<td class="actions-table-img">
			<?php 
			if($result['SuppliersOrganizationsReferent']['IsReferente']=='Y') {
			    echo $this->Html->link(null, array('action' => 'edit', null, 'supplier_organization_id='.$result['SuppliersOrganizationsReferent']['supplier_organization_id'], 
			    															 'group_id='.$group_id),  array('class' => 'action actionEdit','title' => __('Gest Suppliers Organizations Referents')));
			    
				echo $this->Html->link(null, array('action' => 'delete', null, 'user_id='.$result['SuppliersOrganizationsReferent']['user_id'], 
																			   'supplier_organization_id='.$result['SuppliersOrganizationsReferent']['supplier_organization_id'],
																			   'group_id='.$group_id,
																			   'type='.$result['SuppliersOrganizationsReferent']['type']),array('class' => 'action actionDelete','title' => __('Delete'))); 																   
			}
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
	
	<script type="text/javascript">
	jQuery(document).ready(function() {
	
		jQuery(".actionDelete").each(function () {
			jQuery(this).click(function() {
				if(!confirm("Sei sicuro di voler eliminare il <?php echo $userGroups[$group_id]['name'];?> associato al produttore?"))
					return false;
				else
					return true;
			});
		});
	});
	</script>
	
<?php
} // end if(!empty($results) 
else 
if($resultsFound=='N') 
	echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFonud', 'msg' => __('msg_search_not_result')));
	
?>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('.reset').click(function() {
		jQuery('#FilterSupplierOrganizationSupplierOrganizationId').prop('selectedIndex',0);
		
        var element = jQuery('#FilterSupplierOrganizationUsers').find('option:selected').removeAttr('selected'); 
        
		jQuery('#FilterSupplierOrganizationUsers').prop('selectedIndex',0);

		jQuery('#FilterSuppliersOrganizationsReferentUserName').val("");	
	});
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