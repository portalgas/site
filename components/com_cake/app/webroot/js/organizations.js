 $(document).ready(function() {
	choiceType();
	choicePayToDelivery();
		
	$("input[name='data[Organization][type]']").change(function() {	
		choiceType();
		choicePayToDelivery();
	});	

	$("input[name='data[Organization][payToDelivery]']").change(function() {	
		choicePayToDelivery();
	});
});

function choiceType() {	
	var type = $("input[name='data[Organization][type]']:checked").val();

	if(type=='GAS') {
		$('.typeGAS').show();
		$('.typePROD').hide();
		$('.typePACT').hide();
		
		$('#tr_group_id_manager_delivery').show();
		$('#tr_group_id_referent').show();
		$('#tr_group_id_super_referent').show();
		$('#tr_group_id_cassiere').show();
		$('#tr_group_id_referent_tesoriere').show();
		$('#tr_group_id_tesoriere').show();
		$('#tr_group_id_storeroom').show();
	}
	else		
	if(type=='PROD') {
		$('.typeGAS').hide();
		$('.typePROD').show();
		$('.typePACT').hide();
		
		$('#tr_group_id_manager_delivery').hide();
		$('#tr_group_id_referent').hide();
		$('#tr_group_id_super_referent').hide();
		$('#tr_group_id_cassiere').hide();
		$('#tr_group_id_referent_tesoriere').hide();
		$('#tr_group_id_tesoriere').show();
		$('#tr_group_id_storeroom').hide();
	}
	else		
	if(type=='PACT') {
		$('.typeGAS').hide();
		$('.typePROD').hide();
		$('.typePACT').show();
		
		$('#tr_group_id_manager_delivery').hide();
		$('#tr_group_id_referent').hide();
		$('#tr_group_id_super_referent').hide();
		$('#tr_group_id_cassiere').hide();
		$('#tr_group_id_referent_tesoriere').hide();
		$('#tr_group_id_tesoriere').hide();
		$('#tr_group_id_storeroom').hide();
	}
}
function choicePayToDelivery() {	
	var payToDelivery = $("input[name='data[Organization][payToDelivery]']:checked").val();

	if(payToDelivery=='BEFORE') {
		alert("<?php echo Configure::read('sys_function_not_implement');?>");
	}
	else		
	if(payToDelivery=='ON') {
		/*
		 * referente tesoriere (pagamento con richiesta degli utenti dopo consegna)
		 * 		gestisce anche il pagamento del suo produttore
		 */
		$('#tr_group_id_referent_tesoriere').hide();
	}
	else		
	if(payToDelivery=='POST') {
		$('#tr_group_id_referent_tesoriere').show();
		$('#tr_group_id_cassiere').show();
	}
	else		
	if(payToDelivery=='ON-POST') {
		$('#tr_group_id_referent_tesoriere').show();
		$('#tr_group_id_cassiere').show();
	}	
}	