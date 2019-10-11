<?php
if($results['OrganizationsCash']['cashLimit']=='LIMIT-CASH-USER') 
	$colspan = 9;
else
	$colspan = 7;

if($isCassiere)
	$colspan++;
else
	$colspan--;

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('OrganizationsCashCtrl'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="organizations">';

echo $this->Form->input('cashLimit',array('label' => __('CashLimit'), 'value' => __($results['OrganizationsCash']['cashLimit']), 'disabled' => 'disabled'));

if($results['OrganizationsCash']['cashLimit']=='LIMIT-CASH-AFTER') {
	$limitCashAfter = number_format($results['OrganizationsCash']['limitCashAfter'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	echo $this->Form->input('limitCashAfter',array('label' => __('LimitCashAfter'), 'value' => $limitCashAfter, 'disabled' => 'disabled', 'after' => $this->App->drawTooltip(null,__('tooLimitCashAfter'),$type='HELP')));
}

/*
 * utenti
 */
echo '<div>';

	echo '<div class="table-responsive"><table class="table table-hover">';
	echo '<tr>';
	echo '<th></th>';
	echo '<th colspan="2">'.__('Nominative').'</th>';
	if($results['OrganizationsCash']['cashLimit']=='LIMIT-CASH-USER') {
		echo '<th>'.__('CashLimit').'</th>';
		echo '<th>'.__('LimitCashAfter').'</th>';
	}
	echo '<th>'.__('totImportoUserCash').'</th>';
	echo '<th>'.__('totImportoUserAcquistato').'</th>';
	echo '<th style="width:1px"></th>';
	echo '<th>'.__('Delta').'</th>';
	if($isCassiere)
		echo '<th>'.__('Actions').'</th>';	
	echo '</tr>';

	$i=0;
	foreach($results['User'] as $user) {

		if(!isset($user['limit_type']))
			$user['limit_type'] = 'LIMIT-NO';
		
		if(!isset($user['limit_after'])) {
			$user['limit_after'] = '0.00';
			$user['limit_after_'] = '0,00';
		}
		echo '<tr>';
		echo '<td>';
		echo '<a data-toggle="collapse" href="#ajax_details-'.$user['id'].'" title="'.__('Href_title_expand').'"><i class="fa fa-3x fa-search-plus" aria-hidden="true"></i></a>';
		echo '</td>';		
		echo '<td>'.$this->App->drawUserAvatar($user, $user['id'], $user).'</td>';
		echo '<td>'.$user['name'];
		if(!empty($user['email'])) echo '<br /><a title="'.__('Email send').'" target="_blank" href="mailto:'.$user['email'].'">'.$user['email'].'</a>';
		echo '</td>';
		if($results['OrganizationsCash']['cashLimit']=='LIMIT-CASH-USER') {
			echo '<td>';
			echo __($user['limit_type']);
			echo '</td>';
			echo '<td>';
			if($user['limit_type']=='LIMIT-CASH-AFTER')
				echo $user['limit_after_e'];
			echo '</td>';
		}
		echo '<td>';
		echo $user['user_cash_e'];
		echo '</td>';
		echo '<td>';
		echo $user['user_tot_importo_acquistato_e'];
		echo '</td>';
		
		echo '<td style="background-color:'.$user['ctrl_limit']['stato'].'">';
		echo '</td>';
		echo '<td>';
		echo $user['ctrl_limit']['importo_e'];
		echo '</td>';		
		
		if($isCassiere) {
			echo '<td class="actions-table-img">';
			echo $this->Html->link(__('Edit User Cash'), ['controller' => 'Cashs', 'action' => 'edit_by_user_id', $user['id']], ['class' => 'btn btn-primary','title' => __('Edit User Cash')]);
			echo '</td>';		
		}
		
		echo '</tr>';
		echo '<tr data-attr-action="orders_cashes_limit_users-'.$user['id'].'" class="collapse ajax_details" id="ajax_details-'.$user['id'].'">';
		echo '	<td colspan="2"></td>'; 
		echo '	<td colspan="'.$colspan.'" id="ajax_details_content-'.$user['id'].'"></td>';
		echo '</tr>';		
	} // loops users
	echo '</table></div>';
echo '</div>';

echo '</div>'; 
?>