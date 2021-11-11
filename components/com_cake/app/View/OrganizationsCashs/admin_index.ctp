<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('OrganizationsCash'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="organizations">';

echo $this->Form->create('OrganizationsCash',array('id' => 'formGas'));

echo '<fieldset>';
echo '<legend>'.__('OrganizationsCash').'</legend>';
echo '</fieldset>'; 

$options = array('options' => $cashLimits, 'value' => $results['OrganizationsCash']['cashLimit'], 'label'=>__('CashLimit'), 'required'=> true);
echo $this->App->drawFormRadio('OrganizationsCash','cashLimit', $options);

echo '<div style="display:none;" id="limitCashAfter">';
$limitCashAfter = number_format($results['OrganizationsCash']['limitCashAfter'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
echo $this->Form->input('limitCashAfter',array('label' => __('LimitCashAfter'), 'value' => $limitCashAfter, 'required' => 'required', 'class' => 'double', 'after' => $this->App->drawTooltip(null,__('tooLimitCashAfter'),$type='HELP')));
echo '</div>';

/*
 * utenti
 */
echo '<div style="display:none;" id="limitCashUser">';

	echo '<div class="table-responsive"><table class="table table-hover">';
	echo '<tr>';
	echo '<th></th>';
	echo '<th>'.__('Nominative').'</th>';
	echo '<th>'.__('RegisterDate').'</th>';
	echo '<th>'.__('LastvisitDate').'</th>';
	echo '<th>'.__('CashLimit');
	echo '<select name="limit_type_all" size="1" class="form-control">';
	echo '<option value="" selected>Applica a tutti</option>';
	foreach($limit_type as $key => $limit) 
		echo '<option value="'.$key.'">'.__($limit).'</option>';
	echo '</select>';	
	echo '</th>';
	echo '<th>'.__('LimitCashAfter').'</th>';
	echo '</tr>';

	$i=0;
	foreach($results['User'] as $user) {
		
		if(!empty($user['lastvisitDate']) && $user['lastvisitDate']!=Configure::read('DB.field.datetime.empty')) 
			$lastvisitDate = $this->Time->i18nFormat($user['lastvisitDate'],"%e %b %Y");
		else 
			$lastvisitDate = "";

		if(!isset($user['limit_type']))
			$user['limit_type'] = 'LIMIT-NO';
		
		if(!isset($user['limit_after'])) {
			$user['limit_after'] = '0.00';
			$user['limit_after_'] = '0,00';
		}
		echo '<tr>';
		echo '<td>'.$this->App->drawUserAvatar($user, $user['id'], $user).'</td>';
		echo '<td>'.$user['name'];
		if(!empty($user['email'])) echo '<br /><a title="'.__('Email send').'" target="_blank" href="mailto:'.$user['email'].'">'.$user['email'].'</a>';
		echo '</td>';
		echo '<td>'.$this->Time->i18nFormat($user['registerDate'],"%e %b %Y").'</td>';
		echo '<td>'.$lastvisitDate.'</td>';	
		echo '<td>';
		$options = ['options' => $limit_type, 'value' => $user['limit_type'], 'label'=> false, 'class' => 'user_limit_type', 'data-attr-id' => $user['id'], 'separator' => '<br />', 'required'=> true];
		echo $this->App->drawFormRadio('OrganizationsCash', '[limit_type]['.$user['id'].']', $options);
		echo '</td>';
		echo '<td>';
		echo $this->Form->input('limit_after', ['label' => false, 'value' => $user['limit_after_'], 'name' => 'data[OrganizationsCash][limit_after]['.$user['id'].']', 'required' => false, 'class' => 'double', 'tabindex'=>($i+1)]);
		echo '</td>';
		echo '</tr>';
	} // loops users
	echo '</table></div>';
echo '</div>';

echo $this->Form->end(__('Submit'));
echo '</div>'; 
?>

<script type="text/javascript">
function settingCashLimit(type) {
	switch(type) {
		case 'LIMIT-CASH':
			$('#limitCashAfter').hide();
			$('#limitCashUser').hide();
			break;
		case 'LIMIT-CASH-AFTER':
			$('#limitCashAfter').show();
			$('#limitCashUser').hide();
			break;
		case 'LIMIT-CASH-USER':
			$('#limitCashAfter').hide();
			$('#limitCashUser').show();
			break;
		case 'LIMIT-NO':
			$('#limitCashAfter').hide();
			$('#limitCashUser').hide();
			break;
	}	
}
function settingUserLimitType(obj) {	
	var user_limit_type = $(obj).filter(':checked').val();
	if(typeof user_limit_type != 'undefined') {
		var user_id = $(obj).attr('data-attr-id');
		
		if(user_limit_type=='LIMIT-CASH-AFTER')	{
			$("input[name='data[OrganizationsCash][limit_after]["+user_id+"]']").show();
		}
		else
			$("input[name='data[OrganizationsCash][limit_after]["+user_id+"]']").hide();
	}
}

$(document).ready(function() {

	$('.double').focusout(function() {validateNumberField(this,'importo');});

	$("input[name='data[OrganizationsCash][cashLimit]']").change(function () {
		var type = $(this).val();
		/* console.log(value); */
		
		settingCashLimit(type);		
	});

	$("select[name='limit_type_all']").change(function () {
		var limit_type = $(this).val();

		if(limit_type!="") {
			$(".user_limit_type").each(function () {
				if($(this).attr('value')==limit_type) 
					$(this).prop('checked',true);
				else
					$(this).prop('checked',false);
				
				settingUserLimitType(this);
			});
		}
	});
	
	$(".user_limit_type").change(function () {
		settingUserLimitType(this);
	});
	
	$(".user_limit_type").each(function( index ) {
		var user_limit_type = $(this).val();
		/* console.log(index+') '+user_limit_type); */
		settingUserLimitType(this);
	});
	
	$('#formGas').submit(function() {
		return true;
	});

	settingCashLimit('<?php echo $results['OrganizationsCash']['cashLimit'];?>');		
});
</script>