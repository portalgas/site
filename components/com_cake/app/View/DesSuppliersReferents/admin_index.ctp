<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('Des'),array('controller' => 'Des', 'action' => 'index'));
$this->Html->addCrumb(__('List DesUserGroupMaps'),array('controller' => 'DesUserGroupMaps', 'action' => 'intro'));
$this->Html->addCrumb(__('List Users UserGroups').': '.$userGroups[$group_id]['name']);
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>

<div class="users">

	<h2 class="ico-suppliersOrganizationsReferents">
		<?php echo $userGroups[$group_id]['name'].' <small><i>('.$userGroups[$group_id]['descri'].')</i></small>';?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('Gest').' '.$userGroups[$group_id]['name'], array('action' => 'edit', null, 'des_supplier_id=0', 'group_id='.$group_id),array('class' => 'action actionEdit','title' => __('Gest').' '.$userGroups[$group_id]['name'])); ?></li>
			</ul>
		</div>
	</h2>
	
	<?php echo $this->Form->create('FilterDesSupplier',array('id'=>'formGasFilter','type'=>'get'));?>
		<fieldset class="filter">
			<legend><?php echo __('Filter Des Supplier'); ?></legend>
			<table>
				<tr>
					<td>
						<?php 
						$options =  array('label' => false,
										  'empty' => 'Tutti i produttori',
										  'options' => $ACLdesSuppliers,
										  'name' => 'FilterDesSuppliersReferentId',
										  'default'=>$FilterDesSuppliersReferentId,
										  'escape' => false);
						if(count($ACLdesSuppliers) > Configure::read('HtmlSelectWithSearchNum')) 
							$options += array('class'=> 'selectpicker', 'data-live-search' => true); 						
						echo $this->Form->input('supplier_id', $options); ?>
					</td>
					<td>
						<?php
						echo $this->Form->input('flag_gas_all',array('label' => __('Suppliers Organizations Referents'),'options' => $desSuppliersReferentOrganizationId,'name'=>'FilterDesSuppliersReferentOrganizationId','default'=>$FilterDesSuppliersReferentOrganizationIdDefault,'escape' => false)); 
						?>
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
			<th colspan="2"><?php echo $this->Paginator->sort('des_supplier_id',__('DesSuppliers'));?></th>
			<th colspan="2"><?php echo __('Organization');?></th>
			<th colspan="3"><?php echo $this->Paginator->sort('user_id');?></th>
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
			<?php echo $result['Supplier']['name']; ?>
		</td>	
		<td>
			<?php echo '<img width="50" class="userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Organization']['img1'].'" alt="'.$result['Organization']['name'].'" />';?>
		</td>
		<td>		
			<?php echo $result['Organization']['name']; ?>
		</td>		
		<td>
			<?php
			$tmpUser->organization['Organization']['id'] = $result['User']['organization_id'];
			echo $this->App->drawUserAvatar($tmpUser, $result['User']['id'], $result['User']); 
			?>
		</td>
		<td>
			<?php echo $result['User']['name']; ?>
		</td>
		<td>
			<?php 
			if(!empty($result['User']['email']))
				echo '<a title="'.__('Email send').'" target="_blank" href="mailto:'.$result['User']['email'].'">'.$result['User']['email'].'</a>'; 
			?>
		</td>
		<td style="white-space: nowrap;"><?php echo $this->Time->i18nFormat($result['DesSuppliersReferent']['created']); ?></td>
		<td class="actions-table-img">
			<?php 
			if($result['DesSuppliersReferent']['canUserDesGestRole']) {
			    echo $this->Html->link(null, array('action' => 'edit', null, 'des_supplier_id='.$result['DesSuppliersReferent']['des_supplier_id'], 
			    															 'group_id='.$group_id),  array('class' => 'action actionEdit','title' => __('Gest Des Suppliers Referents')));
			    
				echo $this->Html->link(null, array('action' => 'delete', null, 'user_id='.$result['DesSuppliersReferent']['user_id'], 
																			   'des_supplier_id='.$result['DesSuppliersReferent']['des_supplier_id'],
																			   'group_id='.$group_id,
																			   'type='.$result['DesSuppliersReferent']['type']),array('class' => 'action actionDelete','title' => __('Delete'))); 																   
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