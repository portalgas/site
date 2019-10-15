function callUpdateDate(organization_id, user_id, field_db, data_db) {

	$("#submitEcomm-" + user_id).animate({opacity: 1});

	var url = '';
	url = "/administrator/index.php?option=com_cake&controller=Users&action=index_date_update&format=notmpl";

	$.ajax({
		type: "POST",
		url: url,
		data: "organization_id="+organization_id+"&user_id=" + user_id + "&field_db=" + encodeURIComponent(field_db) + "&data_db=" + data_db,
		success: function (response) {
			$("#submitEcomm-" + user_id).attr("src", app_img + "/actions/32x32/bookmark.png");
			$("#msgEcomm-" + user_id).html("Salvato!");
			$("#submitEcomm-" + user_id).delay(1000).animate({
				opacity: 0
			}, 1500);
			$("#msgEcomm-" + user_id).delay(1000).animate({
				opacity: 0
			}, 1500);
		},
		error: function (XMLHttpRequest, textStatus, errorThrown) {
			$('#msgEcomm-' + user_id).html(textStatus);
			$('#submitEcomm-' + user_id).attr('src', app_img + '/blank32x32.png');
		}
	});
}

$(document).ready(function() {
	
	$('.reset').click(function() {
		$('#FilterUserUsername').val('');	
		$('#FilterUserName').val('');	
	});
	
	$('.actionUserFlagPrivacy').click(function() {
		if(!confirm(jsAlertConfirmUserFlagPrivacy))
			return false;
			
		var organization_id = $(this).data('attr-organization-id');
		var url = "/administrator/index.php?option=com_cake&controller=UserProfiles&action=userFlagPrivacyN&organization_id="+organization_id+"&action_back_controller="+action_back_controller+"&action_back_action="+action_back_action;
		/* console.log(url); */ 
		window.location.href = url;			
	});
	 
	$('.actionUserRegistrationExpire').click(function() {
		if(!confirm(jsAlertConfirmUserRegistrationExpire))
			return false;	
		
		var organization_id = $(this).data('attr-organization-id');
		var url = "/administrator/index.php?option=com_cake&controller=UserProfiles&action=userRegistrationExpireN&organization_id="+organization_id+"&action_back_controller="+action_back_controller+"&action_back_action="+action_back_action;
		/* console.log(url); */
		window.location.href = url;				
	});
	 
	$('.userProfileUpdate').click(function() {

		if($(this).hasClass('stato_no')) {
			$(this).removeClass('stato_no');
			$(this).addClass('stato_si');
		}
		else {
			$(this).removeClass('stato_si');
			$(this).addClass('stato_no');
		}

		$(this).attr('title', '');
		
		var user_id = $(this).data('attr-user-id');
		var organization_id = $(this).data('attr-organization-id');
		var field = $(this).data('attr-field');
		var url = "/administrator/index.php?option=com_cake&controller=UserProfiles&action=inverseValue&organization_id="+organization_id+"&user_id="+user_id+"&field="+field+"&format=notmpl";
		/* console.log(url); */
		
		$.ajax({
			type: "GET",
			url: url,
			success: function(response){
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
				$(this).html("Error!");
			}
		});
		return false;		
	});	
	
	$('.userUpdate').click(function() {

		if($(this).hasClass('stato_no')) {
			$(this).removeClass('stato_no');
			$(this).addClass('stato_si');
		}
		else {
			$(this).removeClass('stato_si');
			$(this).addClass('stato_no');
		}

		var user_id = $(this).data('attr-user-id');
		var organization_id = $(this).data('attr-organization-id');
		var field = $(this).data('attr-field');
		var url = "/administrator/index.php?option=com_cake&controller=Users&action=inverseValue&organization_id="+organization_id+"&user_id="+user_id+"&field="+field+"&format=notmpl";
		/* console.log(url); */
		
		$.ajax({
			type: "GET",
			url: url,
			success: function(response){
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
				$(this).html("Error!");
			}
		});
		return false;		
	});
	
	$('.callUpdateDate').change(function () {
		
		var organization_id = $(this).attr('data-attr-organization_id');
		var user_id = $(this).attr('data-attr-user_id');
		var field_db = $(this).attr('data-attr-field_db');
		
		var data_db = "";
		if($('#' + field_db + 'Db_' + user_id).length>0)
			data_db = $('#' + field_db + 'Db_' + user_id).val(); /* datepicker */
		else
			data_db = $(this).val(); /* campo testo */
		
		console.log("organization_id "+organization_id+" - user_id " + user_id + " - field_db " + field_db + " data_db " + data_db);

		callUpdateDate(organization_id, user_id, field_db, data_db);
		return false;

	});
		
	$('.notaUser').click(function() {
		var user_id = $(this).data('attr-user-id');
		var organization_id = $(this).data('attr-organization-id');
		
		$("#dialogmodal").data('user_id', user_id); 
		$("#dialogmodal").data('organization_id', organization_id); 
		$('#dialogmodal').modal('show');  
		return false;		
	});
	
    $('#dialogmodal').on('shown.bs.modal', function() {
		var numRowData = $("#dialogmodal");
		user_id = numRowData.data('user_id');
		organization_id = numRowData.data('organization_id');
		
		$('#notaUser').val("");
				
		$.ajax({
			type: "GET",
			url: "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=getNotaUser&organization_id="+organization_id+"&user_id="+user_id+"&format=notmpl",
			data: "",
			success: function(response){
				$('#notaUser').val(response);
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
			}
		});
		return false;
    })
	.on('hidden.bs.modal', function() {

		var numRowData = $("#dialogmodal");
		user_id = numRowData.data('user_id');
		organization_id = numRowData.data('organization_id');
			
		var notaUser = encodeURIComponent($('#notaUser').val());
		
		$.ajax({
			type: "POST",
			url: "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=setNotaUser&organization_id="+organization_id+"&user_id="+user_id+"&format=notmpl",
			data: "notaUser="+notaUser,
			success: function(response){
				if(notaUser=="")
					$('#notaUser-'+user_id).attr('src', app_img+'/actions/32x32/filenew.png');					
				else	
					$('#notaUser-'+user_id).attr('src', app_img+'/actions/32x32/playlist.png');
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
			}
		});
		return false;
    });	
});