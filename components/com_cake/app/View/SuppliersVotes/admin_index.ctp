<?php
/*
echo "<pre>";
print_r($results);
echo "</pre>";
*/
?>			
<div class="suppliersVote">
	<h2 class="ico-bookmarkes-articles">
		<?php echo __('SuppliersVotes');?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('New Suppliers Votes'), array('action' => 'edit'),array('class' => 'action actionAdd','title' => __('New Suppliers Votes'))); ?></li>
			</ul>
		</div>
	</h2>

<?php
if($isSuperReferente) {
?>
	<?php echo $this->Form->create('FilterSuppliersVote',array('id'=>'formGasFilter','type'=>'get'));?>
		<fieldset class="filter">
			<legend><?php echo __('Filter SuppliersVotes'); ?></legend>
			<table>
				<tr>
					<?php 
					if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') {
						echo '<td>';
						echo $this->Form->input('category_supplier_id',array('label' => '&nbsp;','options' => $categories,'empty' => 'Filtra per categoria','name' => 'FilterSuppliersVoteCategoryId','default' => $FilterSuppliersVoteCategoryId,'escape' => false)); 
						echo '</td>';
					}
					?>
					</td>
					<td>
						<?php echo $this->Ajax->autoComplete('FilterSuppliersVoteName', 
															Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteSuppliersOrganizations_name&format=notmpl',
								   							array('label' => 'Nome','name'=>'FilterSuppliersVoteName','value'=>$FilterSuppliersVoteName,'size'=>'75','escape' => false)); 
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
}

if(!empty($results)) {
?>

	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
	<th><?php echo __('N');?></th>
	<?php
		if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') 
			echo '<th>'.__('Category').'</th>';
	
	echo '<th colspan="2">'.$this->Paginator->sort('name',__('Business name')).'</th>';
	echo '<th style="text-align:center;">'.__('User').'</th>';
	echo '<th style="text-align:center;">'.__('Vote').'</th>';
	echo '<th>'.__('Body').'</th>';
	echo '<th>Altri G.A.S.</th>';
	echo '<th class="actions">'.__('Actions').'</th>';
	echo '</thead></tr><tbody>';

	foreach ($results as $i => $result):
			$numRow = ((($this->Paginator->counter(array('format'=>'{:page}'))-1) * $SqlLimit) + $i+1);
			 
			echo '<tr class="view">';
			echo '<td>'.$numRow.'</td>';
			if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') 
				echo '<td>'.$result['CategoriesSupplier']['name'].'</td>';

			echo '<td>';
			if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
				echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" />';	
			echo '</td>';
			echo '<td>';
			echo $result['Supplier']['name']; 
			echo '</td>';
			echo '<td style="text-align:center;">';
			if(!empty($result['User']))	
				echo $result['User']['name'];
			echo '</td>';
			echo '<td style="text-align:center;">';
			if(!empty($result['SuppliersVote']))
				echo $this->App->drawVote($result['SuppliersVote']['voto']);
			echo '</td>';
			echo '<td>';
			if(!empty($result['SuppliersVote']))	
				echo $result['SuppliersVote']['nota'];
			echo '</td>';
			
			/*
			 * voto altri GAS 
			 */
			echo '<td style="white-space: nowrap">';			
			if(!empty($result['SuppliersVoteOrganization'])) {
				foreach ($result['SuppliersVoteOrganization'] as $suppliersVoteOrganization) {
				
					/*
					 * escludo proprio GAS
					 */
					if($suppliersVoteOrganization['SuppliersVote']['organization_id']!=$user->organization['Organization']['id']) {
						  echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$suppliersVoteOrganization['Organization']['img1'].'" alt="'.$suppliersVoteOrganization['Organization']['name'].'" /> ';
						  echo $suppliersVoteOrganization['Organization']['name'].' '; 
						  echo $this->App->drawVote($suppliersVoteOrganization['SuppliersVote']['voto'], $suppliersVoteOrganization['SuppliersVote']['nota']);
					}
				}
			}
			echo '</td>';
			
			echo '<td class="actions-table-img">';			
			echo $this->Html->link(null, array('action' => 'edit', $result['SuppliersOrganization']['id'],'sort:'.$sort,'direction:'.$direction,'page:'.$page),array('class' => 'action actionEdit','title' => __('Edit')));
			echo '</td>';
		echo '</tr>';
	endforeach; 
	echo '</tbody></table>';


	echo '<p>';
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	echo '</p>';

		echo '<div class="paging">';
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	 	echo '</div>';
	} 
	else 
		echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => "Non ci sono ancora produttori registrati"));
		
echo '</div>';
?>