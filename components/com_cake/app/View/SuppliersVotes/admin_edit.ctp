<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Suppliers Organization'), array('controller' => 'SuppliersOrganizations', 'action' => 'index'));
$this->Html->addCrumb(__('List Suppliers Votes'), array('controller' => 'SuppliersVotes', 'action' => 'index'));
$this->Html->addCrumb(__('Edit Suppliers Votes'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>

<div class="suppliers form">
<?php echo $this->Form->create('SuppliersVote',array('id' => 'formGas'));?>
    <fieldset>
        <legend><?php echo __('Edit Suppliers Votes'); ?></legend>

		<?php
			/*
			 * Supplier
			 */
			if(!empty($ACLsuppliersOrganization)) {
				$options = array('id' => 'supplier_organization_id', 
								 'data-placeholder' => 'Scegli un produttore',
								 'options' => $ACLsuppliersOrganization, 
								// 'default' => $supplier_organization_id, 
								 'required' => 'false', 
								 'after' => '<div style="float:right;" id="suppliers_organization_details"></div>',
								 'empty' => Configure::read('option.empty'));
				if(count($ACLsuppliersOrganization) > Configure::read('HtmlSelectWithSearchNum')) 
					$options += array('class'=> 'selectpicker', 'data-live-search' => true); 
				
				echo $this->Form->input('supplier_organization_id', $options);
			}
			else
				echo $this->Form->input('id', array('value' => $results['SuppliersOrganization']['id']));
	
			$options = array('required' => 'false');
			if(isset($results['SuppliersVote'])) 
				$options += array('value' => $results['SuppliersVote']['nota']); 
			echo $this->Form->input('nota', $options);

			$options = array('options' => $votos);
			if(isset($results['SuppliersVote'])) 
				$options += array('value' => $results['SuppliersVote']['voto']);
			else
				$options += array('value' => 2);
			echo $this->App->drawFormRadio('SuppliersVote', 'voto', $options);
			
			
			/*
			 * voto altri GAS 
			 */
				
			if(!empty($suppliersVotesOrganizationsResults)) {
				
				echo '<table cellpadding="0" cellspacing="0">';
				echo '<thead><tr>';
				echo '<th colspan="3">Giudizio altri G.A.S.</th>';
				echo '</thead><tr><tbody>';	
				foreach ($suppliersVotesOrganizationsResults as $suppliersVoteOrganization) {
				
					echo '<tr>';
					echo '<td style="width:75px">';
					echo '<img width="50" class="userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$suppliersVoteOrganization['Organization']['img1'].'" alt="'.$suppliersVoteOrganization['Organization']['name'].'" /> ';
					echo '</td>';
					echo '<td>';
					echo $suppliersVoteOrganization['Organization']['name'];
					echo '</td>';
					echo '<td>';
					echo $this->App->drawVote($suppliersVoteOrganization['SuppliersVote']['voto'], $suppliersVoteOrganization['SuppliersVote']['nota']);
					echo '</td>';
					echo '</tr>';
				}
				
				echo '</tbody></table>';	
			}
					
	?>

    </fieldset>
<?php 
echo $this->Form->end(__('Submit'));
?>
</div>
<div class="actions">
    <h3><?php echo __('Actions'); ?></h3>
    <ul>
        <li><?php echo $this->Html->link(__('List Suppliers Votes'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
    </ul>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {
	
	jQuery('#formGas').submit(function() {
	
		<?php
		if(!empty($ACLsuppliersOrganization)) {
		?>
		var supplier_organization_id = jQuery('#supplier_organization_id').val();
		if(supplier_organization_id=='' || supplier_organization_id==undefined) {
			alert("<?php echo __('jsAlertSupplierRequired');?>");
			jQuery('.tabs').tabs('option', 'active',0);
			jQuery('#supplier_organization_id').focus();
			return false;
		}
		<?php
		}
		?>
		
		return true;
	});
});
</script>