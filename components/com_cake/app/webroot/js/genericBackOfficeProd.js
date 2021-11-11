function viewProdDeliverySottoMenu(prod_delivery_id, position_img) {
	if(prod_delivery_id==null)  return;
	
	if(position_img==null) position_img = 'bgLeft'; 
	
	/*
	 * in ProdDelivery::index ho + sottoMenu
	 * quando e' laterale ne ho solo uno e ID cambia se cambio la consegna dal menu a tendina (vecchia gestione) 
	 */
	if (jQuery('#prod-delivery-sotto-menu-'+prod_delivery_id).length==0)
		idSelector = '.prod-delivery-sotto-menu-unique';
	else
		idSelector = '#prod-delivery-sotto-menu-'+prod_delivery_id;
	
	jQuery(idSelector).css('min-height', '35px');
	jQuery(idSelector).css('background', 'url("'+app_img+'/ajax-loader.gif") no-repeat scroll center 0 transparent');
	
	jQuery.ajax({
		type: "get", 
		url: "/administrator/index.php?option=com_cake&controller=ProdDeliveries&action=sotto_menu&prod_delivery_id="+prod_delivery_id+"&position_img="+position_img+"&format=notmpl",
		data: "",
		success: function(response) {
			jQuery(idSelector).css('background', 'none repeat scroll 0 0 transparent');
			jQuery(idSelector).html(response);
		},
		error:function (XMLHttpRequest, textStatus, errorThrown) {
			jQuery(idSelector).css('background', 'none repeat scroll 0 0 transparent');
			jQuery(idSelector).html(textStatus);
		}
	});
	return;
}

function activeSubmitEcomm(obj) {		
	/*
	 * submit
	 */
	jQuery(obj).find('.submitEcomm').click(function() {
		/* get id da id="xxx-1"  */
		var idRow = jQuery(this).attr('id');
		var numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);

		/* chiudo eventuali dettagli */ 
		jQuery('.trView').hide(); 
		jQuery('.actionTrView').removeClass('closeTrView'); 
		jQuery('.actionTrView').addClass('openTrView'); 

		jQuery('#submitEcomm-'+numRow).attr('src',app_img+'/ajax-loader.gif');
		
		var prod_delivery_id = jQuery('#prod_delivery_id-'+numRow).val();
		var article_organization_id = jQuery('#article_organization_id-'+numRow).val();
		var article_id = jQuery('#article_id-'+numRow).val();
		var qta = jQuery('#qta-'+numRow).html();

		if(!ecommRowsValidation(numRow, backOffice=true)) return false;

		var user_id = '';
		var reportOptionsSub='';
		var reportOptions = jQuery("input[name='report-options']:checked").val(); 
		if(reportOptions=='report-users-cart' || reportOptions=='report-users-all') {
			user_id = jQuery('#user_id').val();
			if(user_id=='ALL') {
				user_id = jQuery('#user_id-'+numRow).val();
				reportOptionsSub = 'ALL';
			}
		}	
		else
		if(reportOptions=='report-articles-details')
			user_id = jQuery('#user_id-'+numRow).val(); 
		
		if(user_id=="" || user_id==0)   {
			alert("Utente non selezionato!");
			return false;
		}
		
		var url = '';
		url = '/administrator/index.php?option=com_cake&controller=AjaxProdCarts&action=managementCart&row_id='+numRow+'&prod_delivery_id='+prod_delivery_id+'&article_organization_id='+article_organization_id+'&article_id='+article_id+'&user_id='+user_id+'&qta='+qta+'&reportOptions='+reportOptions+'&reportOptionsSub='+reportOptionsSub+'&format=notmpl';

		jQuery.ajax({
			type: "GET",
			url: url,
			data: "",
			success: function(response){
				 jQuery('#row-'+numRow).html(response);
				 /* jQuery('#msgEcomm-'+numRow).html(response); */
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
				 jQuery('#msgEcomm-'+numRow).html(textStatus);
				 jQuery('#submitEcomm-'+numRow).attr('src',app_img+'/blank32x32.png');
			}
		});
		return false;
	});
}

function activeImportoForzato(obj) {
	/* 
	 * importo_forzato
	 */
	jQuery(obj).find('.importo_forzato').change(function() {
		/* get id da id="xxx-1"  */
		var idRow = jQuery(this).attr('id');
		var numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);

		/* importo_forzato */
		var importo_forzato = jQuery('#importo_forzato-'+numRow).val();
		if(!validateNumberPositiveField('#importo_forzato-'+numRow,'Importo forzato')) return false;
	
		importo_forzato = numberToJs(importo_forzato);   /* in 1000.50 */
		importo_forzato = number_format(importo_forzato,2,',','.');  /* in 1.000,50 */
		jQuery('#importo_forzato-'+numRow).val(importo_forzato);
		importo_forzato = jQuery('#importo_forzato-'+numRow).val();

		var prod_delivery_id = jQuery('#prod_delivery_id-'+numRow).val();
		var article_organization_id = jQuery('#article_organization_id-'+numRow).val();
		var article_id = jQuery('#article_id-'+numRow).val();
		var user_id = jQuery('#user_id-'+numRow).val();
		if(user_id==0) {
			alert("L'utente non ha ancora acquistato l'articolo");
			jQuery('#importo_forzato-'+numRow).val('0,00');
			return false;
		}
		
		var key = prod_delivery_id+"_"+article_organization_id+"_"+article_id+"_"+user_id;
		
		jQuery.ajax({
			type: "GET",
			url: "/administrator/index.php?option=com_cake&controller=AjaxProdCodes&action=setImportoForzato&row_id="+numRow+"&key="+key+"&importo_forzato="+importo_forzato+"&format=notmpl",
			data: "",
			success: function(response){
				 jQuery('#msgEcomm-'+numRow).html(response);
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
				 jQuery('#msgEcomm-'+numRow).html(textStatus);
				 jQuery('#submitEcomm-'+numRow).attr('src',app_img+'/blank32x32.png');
			}
		});
		return false;
	});
}

function activeNotaEcomm(obj) {
	/*
	 * buttonSubmitNota
	 */
	jQuery(obj).find('.notaEcomm').click(function() {
		/* get id della TD  <td id="xxx-1">  */
		var idRow = jQuery(this).parent().attr('id');
		var numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
	
		jQuery("#dialogmodal").data('numRow', numRow); 
		jQuery('#dialogmodal').dialog('open');  /* definito in ProdCarts/admin_management_carts.ctp */
		return false;
	});
}	