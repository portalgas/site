function viewOrderSottoMenu(order_id, position_img) {
	if(order_id==null)  return;
	
	if(position_img==null) position_img = 'bgLeft'; 
	
	/*
	 * in Orders::index ho + sottoMenu
	 * quando e' laterale ne ho solo uno e ID cambia se cambio l'ordine dal menu a tendina (vecchia gestione) 
	 */
	if ($('#order-sotto-menu-'+order_id).length==0)
		idSelector = '.order-sotto-menu-unique';
	else
		idSelector = '#order-sotto-menu-'+order_id;
	
	$(idSelector).css('min-height', '35px');
	$(idSelector).css('background', 'url("'+app_img+'/ajax-loader.gif") no-repeat scroll center 0 transparent');
	
	$.ajax({
		type: "get", 
		url: "/administrator/index.php?option=com_cake&controller=Orders&action=sotto_menu&order_id="+order_id+"&position_img="+position_img+"&format=notmpl",
		data: "",
		success: function(response) {
			$(idSelector).css('background', 'none repeat scroll 0 0 transparent');
			$(idSelector).html(response);
		},
		error:function (XMLHttpRequest, textStatus, errorThrown) {
			$(idSelector).css('background', 'none repeat scroll 0 0 transparent');
			$(idSelector).html(textStatus);
		}
	});
	return;
}
function viewOrderSottoMenuBootstrap(order_id) {

	if(order_id==null)  return;
	
	/*
	 * in Orders::index ho + sottoMenu
	 * quando e' laterale ne ho solo uno e ID cambia se cambio l'ordine dal menu a tendina (vecchia gestione) 
	 */
	if ($('#order-sotto-menu-'+order_id).length==0)
		idSelector = '.order-sotto-menu-unique';
	else
		idSelector = '#order-sotto-menu-'+order_id;
	
	$.ajax({
		type: "get", 
		url: "/administrator/index.php?option=com_cake&controller=Orders&action=sotto_menu_bootstrap&order_id="+order_id+"&format=notmpl",
		data: "",
		success: function(response) {
			$(idSelector).html(response);
		},
		error:function (XMLHttpRequest, textStatus, errorThrown) {
			$(idSelector).html(textStatus);
		}
	});
	return;
}
function viewDesOrderSottoMenu(des_order_id, position_img) {
	if(des_order_id==null)  return;
	
	if(position_img==null) position_img = 'bgLeft'; 
	
	/*
	 * in DesOrders::index ho + sottoMenu
	 * quando e' laterale ne ho solo uno e ID cambia se cambio l'ordine dal menu a tendina (vecchia gestione) 
	 */
	if ($('#des-order-sotto-menu-'+des_order_id).length==0)
		idSelector = '.des-order-sotto-menu-unique';
	else
		idSelector = '#des-order-sotto-menu-'+des_order_id;
	
	$(idSelector).css('min-height', '35px');
	$(idSelector).css('background', 'url("'+app_img+'/ajax-loader.gif") no-repeat scroll center 0 transparent');
	
	$.ajax({
		type: "get", 
		url: "/administrator/index.php?option=com_cake&controller=DesOrders&action=sotto_menu&des_order_id="+des_order_id+"&position_img="+position_img+"&format=notmpl",
		data: "",
		success: function(response) {
			$(idSelector).css('background', 'none repeat scroll 0 0 transparent');
			$(idSelector).html(response);
		},
		error:function (XMLHttpRequest, textStatus, errorThrown) {
			$(idSelector).css('background', 'none repeat scroll 0 0 transparent');
			$(idSelector).html(textStatus);
		}
	});
	return;
}


function viewTesoriereSottoMenu(delivery_id, position_img) {
	if(delivery_id==null)  return;
	
	if(position_img==null) position_img = 'bgLeft'; 
	
	idSelector = '.order-sotto-menu-unique';


	$(idSelector).css('min-height', '35px');
	$(idSelector).css('background', 'url("'+app_img+'/ajax-loader.gif") no-repeat scroll center 0 transparent');

	$.ajax({
		type: "get", 
		url: "/administrator/index.php?option=com_cake&controller=Tesoriere&action=sotto_menu_tesoriere&delivery_id="+delivery_id+"&position_img="+position_img+"&format=notmpl",
		data: "",
		success: function(response) {
			$(idSelector).css('background', 'none repeat scroll 0 0 transparent');
			$(idSelector).html(response);
		},
		error:function (XMLHttpRequest, textStatus, errorThrown) {
			$(idSelector).css('background', 'none repeat scroll 0 0 transparent');
			$(idSelector).html(textStatus);
		}
	});
	return;
}

function viewReferenteTesoriereSottoMenu(delivery_id, position_img) {
	if(delivery_id==null)  return;
	
	if(position_img==null) position_img = 'bgLeft'; 
	
	idSelector = '.order-sotto-menu-unique';


	$(idSelector).css('min-height', '35px');
	$(idSelector).css('background', 'url("'+app_img+'/ajax-loader.gif") no-repeat scroll center 0 transparent');

	$.ajax({
		type: "get", 
		url: "/administrator/index.php?option=com_cake&controller=Tesoriere&action=sotto_menu_referentetesoriere&delivery_id="+delivery_id+"&position_img="+position_img+"&format=notmpl",
		data: "",
		success: function(response) {
			$(idSelector).css('background', 'none repeat scroll 0 0 transparent');
			$(idSelector).html(response);
		},
		error:function (XMLHttpRequest, textStatus, errorThrown) {
			$(idSelector).css('background', 'none repeat scroll 0 0 transparent');
			$(idSelector).html(textStatus);
		}
	});
	return;
}

function viewTesoriereRequestPaymentSottoMenu(delivery_id, request_payment_id, position_img) {
	if(delivery_id==null || request_payment_id==null)  return;
	
	if(position_img==null) position_img = 'bgLeft'; 
	
	idSelector = '.order-sotto-menu-unique';


	$(idSelector).css('min-height', '35px');
	$(idSelector).css('background', 'url("'+app_img+'/ajax-loader.gif") no-repeat scroll center 0 transparent');

	$.ajax({
		type: "get", 
		url: "/administrator/index.php?option=com_cake&controller=Tesoriere&action=sotto_menu_tesoriere_request_payment&delivery_id="+delivery_id+"&request_payment_id="+request_payment_id+"&position_img="+position_img+"&format=notmpl",
		data: "",
		success: function(response) {
			$(idSelector).css('background', 'none repeat scroll 0 0 transparent');
			$(idSelector).html(response);
		},
		error:function (XMLHttpRequest, textStatus, errorThrown) {
			$(idSelector).css('background', 'none repeat scroll 0 0 transparent');
			$(idSelector).html(textStatus);
		}
	});
	return;
}

function viewReferenteTesoriereRequestPaymentSottoMenu(delivery_id, request_payment_id, position_img) {
	if(delivery_id==null || request_payment_id==null)  return;
	
	if(position_img==null) position_img = 'bgLeft'; 
	
	idSelector = '.order-sotto-menu-unique';


	$(idSelector).css('min-height', '35px');
	$(idSelector).css('background', 'url("'+app_img+'/ajax-loader.gif") no-repeat scroll center 0 transparent');

	$.ajax({
		type: "get", 
		url: "/administrator/index.php?option=com_cake&controller=Tesoriere&action=sotto_menu_referentetesoriere_request_payment&delivery_id="+delivery_id+"&request_payment_id="+request_payment_id+"&position_img="+position_img+"&format=notmpl",
		data: "",
		success: function(response) {
			$(idSelector).css('background', 'none repeat scroll 0 0 transparent');
			$(idSelector).html(response);
		},
		error:function (XMLHttpRequest, textStatus, errorThrown) {
			$(idSelector).css('background', 'none repeat scroll 0 0 transparent');
			$(idSelector).html(textStatus);
		}
	});
	return;
}

function choiceDelivery() {
	var div_contenitore = $('#delivery_id').parent().parent().attr('id');  /* deliveries */
	delivery_id = $('#delivery_id').val();
	if(debugLocal) alert("choiceDelivery - div_contenitore "+div_contenitore+", delivery_id "+delivery_id);
	if(delivery_id=='') {
		showHideBox(div_contenitore,call_child=false);
		return;
	}
	
	showHideBox(div_contenitore,call_child=true);
	AjaxCallToOrders(delivery_id); /* chiamata Ajax per elenco ordini */	
}
function choiceOrder() {
	var div_contenitore = $('#order_id').parent().parent().attr('id');  /* orders-result */
	order_id = $("#order_id").val();	
	if(debugLocal) alert("choiceOrder - div_contenitore "+div_contenitore+", order_id "+order_id);
	if(order_id=='') {
		showHideBox(div_contenitore,call_child=false);
		return;
	}

	showHideBox(div_contenitore,call_child=true);	
	AjaxCallToOrderDetails(order_id);	/* chiamata Ajax per dettaglio ordine */
	
	viewOrderSottoMenu(order_id, "bgRight");
}
function choiceOrderDetails() {
	var div_contenitore = 'order-details';
	order_id = $("#order_id").val();	
	if(debugLocal) alert("choiceOrderDetails - div_contenitore "+div_contenitore+", order_id "+order_id);
	
	showHideBox(div_contenitore,call_child=true);  
	AjaxCallToOrderPermission(order_id);	/* chiamata Ajax per permessi sull'ordine */
}


/*
 * History, Delivery.stato_elaborazione == CLOSE
 */
function choiceDeliveryHistory() {
	var div_contenitore = $('#delivery_id').parent().parent().attr('id');  /* deliveries */
	delivery_id = $('#delivery_id').val();
	if(debugLocal) alert("choiceDelivery - div_contenitore "+div_contenitore+", delivery_id "+delivery_id);
	if(delivery_id=='') {
		showHideBox(div_contenitore,call_child=false);
		return;
	}
	
	showHideBox(div_contenitore,call_child=true);
	AjaxCallToOrdersHistory(delivery_id); /* chiamata Ajax per elenco ordini */	
}
function choiceOrderHistory() {
	var div_contenitore = $('#order_id').parent().parent().attr('id');  /* orders-result */
	order_id = $("#order_id").val();	
	if(debugLocal) alert("choiceOrder - div_contenitore "+div_contenitore+", order_id "+order_id);
	if(order_id=='') {
		showHideBox(div_contenitore,call_child=false);
		return;
	}

	showHideBox(div_contenitore,call_child=true);
	AjaxCallToDocOptions();  /* chiamata Ajax per opzioni Documento */
}

function AjaxCallToOrdersHistory(delivery_id) {
	if(delivery_id==0) return false;

	var url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_orders_history&delivery_id="+delivery_id+"&order_id="+order_id+"&format=notmpl";
	var idDivTarget = 'orders-result';
	ajaxCallBox(url, idDivTarget);
}


/*
 * chiamata Ajax per elenco ordini
 */
function AjaxCallToOrders(delivery_id) {
	if(delivery_id==0) return false;

	var url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_orders&delivery_id="+delivery_id+"&order_id="+order_id+"&format=notmpl";
	var idDivTarget = 'orders-result';
	ajaxCallBox(url, idDivTarget);
}
/*
 * chiamata Ajax per dettaglio ordine
 */
function AjaxCallToOrderDetails(order_id) {
	if(order_id==0) return false;

	var url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_order_details&order_id="+order_id+"&format=notmpl";
	var idDivTarget = 'order-details';
	ajaxCallBox(url, idDivTarget);
}
/*
 * chiamata Ajax per permessi ordine in base alla data_fine / stato_elaborazione
 */
function AjaxCallToOrderPermission(order_id) {
	if(order_id==0) return false;

	var call_action = ""; /* prima nel .ctp chiamante era $this->action */
	var url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_order_permission&order_id="+order_id+"&call_action="+call_action+"&format=notmpl";
	var idDivTarget = 'order-permission';
	ajaxCallBox(url, idDivTarget);
}
/* 
 * elenco   A R T I C L E S   to   E X P O R T - D O C
 */
function referentDocsExportAnteprima(doc_options) {

		var delivery_id = $('#delivery_id').val();
		var order_id = $('#order_id').val(); /* estraggo info di delivery_id e supplier_id */

		if(delivery_id == "" || order_id=="" || doc_options=="") {
			$('#articles-result').css('background', 'none repeat scroll 0 0 transparent');
			$('#articles-result').css('display', 'none');
			$('#print-doc').css('background', 'none repeat scroll 0 0 transparent');
			$('#print-doc').css('display', 'none');
			return;
		}
		$('#articles-result').css('display', 'block');
		$('#articles-result').css('background', 'url("'+app_img+'/ajax-loader.gif") no-repeat scroll center 0 transparent');

		$.ajax({
			type: "get", 
			url: "/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToReferent&delivery_id="+delivery_id+"&order_id="+order_id+"&doc_options="+doc_options+"&doc_formato=PREVIEW&format=notmpl",
			data: "",  
			success: function(response) {
				$('#articles-result').css('background', 'none repeat scroll 0 0 transparent');
				$("#articles-result").html(response);
				$('#print-doc').css('display', 'block');
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
				$('#articles-result').css('background', 'none repeat scroll 0 0 transparent');
				$('#articles-result').html(textStatus);
			}
		});
		return false;
}

function tesoriereDocsExportAnteprima(order_id_selected,doc_options) {

		var delivery_id = $('#delivery_id').val();

		if(delivery_id == "" || order_id_selected=="" || doc_options=="") {
			$('#articles-result').css('background', 'none repeat scroll 0 0 transparent');
			$('#articles-result').css('display', 'none');
			$('#print-doc').css('background', 'none repeat scroll 0 0 transparent');
			$('#print-doc').css('display', 'none');
			return;
		}
		$('#articles-result').css('display', 'block');
		$('#articles-result').css('background', 'url("'+app_img+'/ajax-loader.gif") no-repeat scroll center 0 transparent');

		$.ajax({
			type: "get", 
			url: "/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToTesoriere&delivery_id="+delivery_id+"&order_id_selected="+order_id_selected+"&doc_options="+doc_options+"&doc_formato=PREVIEW&format=notmpl",
			data: "",  
			success: function(response) {
				$('#articles-result').css('background', 'none repeat scroll 0 0 transparent');
				$("#articles-result").html(response);
				$('#print-doc').css('display', 'block');
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
				$('#articles-result').css('background', 'none repeat scroll 0 0 transparent');
				$('#articles-result').html(textStatus);
			}
		});
		return false;
}

function activeSubmitEcomm(obj) {		
	/*
	 * submit
	 */
	$(obj).find('.submitEcomm').click(function() {
		/* get id da id="xxx-1"  */
		var idRow = $(this).attr('id');
		var numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);

		/* chiudo eventuali dettagli */ 
		$('.trView').hide(); 
		$('.actionTrView').removeClass('closeTrView'); 
		$('.actionTrView').addClass('openTrView'); 

		$('#submitEcomm-'+numRow).attr('src',app_img+'/ajax-loader.gif');
		
		var order_id = $('#order_id-'+numRow).val();
		var article_organization_id = $('#article_organization_id-'+numRow).val();
		var article_id = $('#article_id-'+numRow).val();
		var qta = $('#qta-'+numRow).html();

		if(!ecommRowsValidation(numRow, backOffice=true)) return false;

		var user_id = '';
		var reportOptionsSub='';
		var reportOptions = $("input[name='report-options']:checked").val(); 
		if(reportOptions=='report-users-cart' || reportOptions=='report-users-all') {
			user_id = $('#user_id').val();
			if(user_id=='ALL') {
				user_id = $('#user_id-'+numRow).val();
				reportOptionsSub = 'ALL';
			}
		}	
		else
		if(reportOptions=='report-articles-details')
			user_id = $('#user_id-'+numRow).val(); 
		
		if(user_id=="" || user_id==0)   {
			alert("Utente non selezionato!");
			return false;
		}
		
		var url = '';
		url = '/administrator/index.php?option=com_cake&controller=AjaxGasCarts&action=managementCart&row_id='+numRow+'&order_id='+order_id+'&article_organization_id='+article_organization_id+'&article_id='+article_id+'&user_id='+user_id+'&qta='+qta+'&reportOptions='+reportOptions+'&reportOptionsSub='+reportOptionsSub+'&format=notmpl';

		$.ajax({
			type: "GET",
			url: url,
			data: "",
			success: function(response){
				 $('#row-'+numRow).html(response);
				 /* $('#msgEcomm-'+numRow).html(response); */
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
				 $('#msgEcomm-'+numRow).html(textStatus);
				 $('#submitEcomm-'+numRow).attr('src',app_img+'/blank32x32.png');
			}
		});
		return false;
	});
}

function activeImportoForzato(obj) {
	/* 
	 * importo_forzato
	 */
	$(obj).find('.importo_forzato').change(function() {
		/* get id da id="xxx-1"  */
		var idRow = $(this).attr('id');
		var numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);

		/* importo_forzato */
		var importo_forzato = $('#importo_forzato-'+numRow).val();
		if(!validateNumberPositiveField('#importo_forzato-'+numRow,'Importo forzato')) return false;
	
		importo_forzato = numberToJs(importo_forzato);   /* in 1000.50 */
		importo_forzato = number_format(importo_forzato,2,',','.');  /* in 1.000,50 */
		$('#importo_forzato-'+numRow).val(importo_forzato);
		importo_forzato = $('#importo_forzato-'+numRow).val();

		var order_id = $('#order_id-'+numRow).val();
		var article_organization_id = $('#article_organization_id-'+numRow).val();
		var article_id = $('#article_id-'+numRow).val();
		var user_id = $('#user_id-'+numRow).val();
		if(user_id==0) {
			alert("L'utente non ha ancora acquistato l'articolo");
			$('#importo_forzato-'+numRow).val('0,00');
			return false;
		}
		
		var key = order_id+"_"+article_organization_id+"_"+article_id+"_"+user_id;
		
		$.ajax({
			type: "GET",
			url: "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=setImportoForzato&row_id="+numRow+"&key="+key+"&importo_forzato="+importo_forzato+"&format=notmpl",
			data: "",
			success: function(response){
				 $('#msgEcomm-'+numRow).html(response);
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
				 $('#msgEcomm-'+numRow).html(textStatus);
				 $('#submitEcomm-'+numRow).attr('src',app_img+'/blank32x32.png');
			}
		});
		return false;
	});
}

function activeNotaEcomm(obj) {
	/*
	 * buttonSubmitNota
	 */
	$(obj).find('.notaEcomm').click(function() {
		/* get id della TD  <td id="xxx-1">  */
		var idRow = $(this).parent().attr('id');
		var numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
	
		$("#dialogmodal").data('numRow', numRow); 
		$('#dialogmodal').modal('show');  /* definito in Carts/admin_management_carts.ctp */
		return false;
	});
}	