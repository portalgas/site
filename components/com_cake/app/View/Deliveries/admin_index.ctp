<?php
if($user->organization['Organization']['hasStoreroom'] == 'Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y' && $user->organization['Organization']['hasVisibility'] == 'Y')
	$colspan = '12';
else
if(($user->organization['Organization']['hasStoreroom'] == 'N' || $user->organization['Organization']['hasStoreroomFrontEnd']=='N') && $user->organization['Organization']['hasVisibility'] == 'N')
	$colspan = '8';
else
	$colspan = '10';
?>
<div class="deliveries">
	<h2 class="ico-deliveries">
		<?php echo __('Deliveries');?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('New Delivery'), array('action' => 'add'),array('class' => 'action actionAdd','title' => __('New Delivery'))); ?></li>
			</ul>
		</div>
	</h2>
	
	<?php
	if(!empty($results)) {
	?>	
	<table cellpadding="0" cellspacing="0">
		<tr>
			<th></th>
			<th><?php echo __('N');?></th>
			<th><?php echo $this->Paginator->sort('luogo');?></th>
			<th></th>
			<th><?php echo $this->Paginator->sort('data');?></th>
			<th><?php echo __('OpenClose');?></th>
			<?php 
			if($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y') {
				echo '<th>'.$this->Paginator->sort('isToStoreroom','Dispensa').'</th>';
				echo '<th>Dispensa<br />rich. pagam.</th>';
			}
			
			if($user->organization['Organization']['hasVisibility']=='Y') {
				echo '<th>'.$this->Paginator->sort('isVisibleFrontEnd',__('isVisibleFrontEnd')).'</th>';
				echo '<th>'.$this->Paginator->sort('isVisibleBackOffice',__('isVisibleBackOffice')).'</th>';
			}
			?>
			<th style="width50px;"></th>
			<th><?php echo $this->Paginator->sort('stato_elaborazione',__('StatoElaborazione'));?></th>
			<th><?php echo $this->Paginator->sort('Created');?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	foreach ($results as $i => $result) {
		$numRow = ((($this->Paginator->counter(array('format'=>'{:page}'))-1) * $SqlLimit) + $i+1);
		
		echo '<tr class="view">';

		echo '<td>';
		echo '<a data-toggle="collapse" href="#ajax_details-'.$result['Delivery']['id'].'" title="'.__('Href_title_expand').'"><i class="fa fa-3x fa-search-plus" aria-hidden="true"></i></a>';
		echo '</td>';		
		
		echo '<td>'.$numRow.'</td>';
		echo '<td>';
		echo $result['Delivery']['luogo']; 
		if(!empty($result['Delivery']['nota']))  echo '<div class="small">'.$result['Delivery']['nota'].'</div>';
		echo '</td>';
		echo '<td class="nota_evidenza_'.strtolower($result['Delivery']['nota_evidenza']).'">&nbsp;</td>';
		echo '<td style="white-space:nowrap;">'.$this->Time->i18nFormat($result['Delivery']['data'],"%A %e %B %Y").'</td>';
		echo '<td>';

			if($result['Delivery']['daysToEndConsegna']<0) 
				echo '<span style="color:red;">Chiuso</span>';
			else {
					echo '<span style="color:green;">Aperto ';
					if($result['Delivery']['daysToEndConsegna']==0) echo '(scade oggi)';
					else echo '(per ancora '.$result['Delivery']['daysToEndConsegna'].'&nbsp;gg)';
					echo '</span>';
			}
			
		echo '</td>';
			
		if($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y') {
			echo '<td title="'.__('toolTipIsToStoreroom').'" class="stato_'.$this->App->traslateEnum($result['Delivery']['isToStoreroom']).'"></td>';
			echo '<td title="'.__('toolTipIsToStoreroomPay').'" class="stato_'.$this->App->traslateEnum($result['Delivery']['isToStoreroomPay']).'"></td>';
		}
		
		if($user->organization['Organization']['hasVisibility']=='Y') {
			echo '<td title="'.__('toolTipsVisibleFrontEnd').'" class="stato_'.$this->App->traslateEnum($result['Delivery']['isVisibleFrontEnd']).'"></td>';
			echo '<td title="'.__('toolTipVisibleBackOffice').'" class="stato_'.$this->App->traslateEnum($result['Delivery']['isVisibleBackOffice']).'"></td>';		
		}
		echo '<td>';
		if(!empty($result['Delivery']['gcalendar_event_id']))
			echo '<img class="img-responsive-disabled" src="'.Configure::read('App.server').Configure::read('App.img.cake').'/gcalendar.png" title="Inserito in GCalendar con evento num '.$result['Delivery']['gcalendar_event_id'].'" />';
		echo '</td>';
			
		echo '<td title="'.__('toolTipStatoElaborazione').'" class="stato_'.strtolower($result['Delivery']['stato_elaborazione']).'"></td>';
		echo '<td style="white-space: nowrap;">'.$this->App->formatDateCreatedModifier($result['Delivery']['created']).'</td>';
		
		echo '<td>';
		$modal_url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Menus&action=delivery&id='.$result['Delivery']['id'].'&format=notmpl';
		$modal_size = 'sm'; // sm md lg
		$modal_header = __('Delivery').' '.$result['Delivery']['luogo'];
		echo '<button type="button" class="btn btn-primary btn-menu" data-attr-url="'.$modal_url.'" data-attr-size="'.$modal_size.'" data-attr-header="'.$modal_header.'" ><i class="fa fa-2x fa-navicon"></i></button>';
		echo '</td>';
	echo '</tr>';
	
	echo '<tr data-attr-action="deliveries-'.$result['Delivery']['id'].'" class="collapse ajax_details" id="ajax_details-'.$result['Delivery']['id'].'">';
	echo '	<td colspan="2"></td>'; 
	echo '	<td colspan="'.$colspan.'" id="ajax_details_content-'.$result['Delivery']['id'].'"></td>';
	echo '</tr>';
		
}	
		echo '</table>';
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
		echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => "Non ci sono ancora consegne registrate"));
	
echo '</div>';
?>
<script type="text/javascript">
$(document).ready(function() {
	$('.actionCopy').click(function() {

		if(!confirm("Sei sicuro di voler copiare la consegna selezionata?")) {
			return false;
		}		
		return true;
	});		
});
</script>