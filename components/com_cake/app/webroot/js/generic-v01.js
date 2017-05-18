var debug = false;

/*
 * parametri in float 10.10 utilizzando numberToJs()
 */
function sottrazione(a, b) {
	var risultato = 0;
	var n = parseFloat(a)-parseFloat(b);
	risultato = Math.round( n * 100 ) / 100;
	
	return risultato;
}

/*
 * gestione del tasto espandi
 */
function actionTrView(obj) {
	
	jQuery(obj).css('display','block');  /* rendo visibile il tasto espandi per i dettagli ajax */
	
	jQuery(obj).click(function() {

		/* backoffice get Class element <a href="" class="actionTrView openTrView" action="users-670"></a> */
		/* front-end get Class element <a href="" class="actionTrView openTrView" action="users-670-99OPEN"></a> */
		dataElement = jQuery(this).attr('action');
		dataElementArray = dataElement.split('-');
		var model = dataElementArray[0];
		var idElement = dataElementArray[1];
		
		/* aggiunto per il front-end il terzo parametro -$order_id$[Cart]stato per avere unicita' con dispensa e articolo loccati */
		if(dataElementArray[2]!=null)  
			idElementRow = idElement+"-"+dataElementArray[2];
		else
			idElementRow = idElement;
		
		if(jQuery(this).hasClass('openTrView')) {

			/* chiudo tutti gli eventuali aperti */
			jQuery('td[id^=tdViewId-]').html('');
			jQuery('.actionTrView').each(function () {
				jQuery(this).removeClass('closeTrView'); 
				jQuery(this).addClass('openTrView');
			});
			jQuery('.trView').each(function () {
				jQuery(this).css('display', 'none');
			});

			jQuery(this).removeClass('openTrView'); 
			jQuery(this).addClass('closeTrView'); 

			/* se e' gia aperto lo chiudo */
			if(jQuery('#trViewId-'+idElementRow).css('display')=='table-row')  {
				jQuery('#trViewId-'+idElementRow).css('display', 'none');
				return false;
			}
			
			jQuery('#tdViewId-'+idElementRow).html('');
			jQuery('#trViewId-'+idElementRow).css('min-height', '50px');
			jQuery('#trViewId-'+idElementRow).toggle('slow');
			jQuery('#tdViewId-'+idElementRow).css('background', 'url("'+app_img+'/ajax-loader.gif") no-repeat scroll center 0 transparent');

			if(isBackoffice())
				url = "/administrator/index.php?option=com_cake&controller=Ajax&action=view_"+model+"&id="+idElement+"&evidenzia=&format=notmpl";
			else
				url = "/?option=com_cake&controller=Ajax&action=view_"+model+"&id="+idElement+"&evidenzia=&format=notmpl";
			
			jQuery.ajax({
				type: "get", 
				url: url,
				data: "",
				success: function(response) {
					jQuery('#tdViewId-'+idElementRow).css('background', 'none repeat scroll 0 0 transparent');
					jQuery('#tdViewId-'+idElementRow).html(response);
				},
				error:function (XMLHttpRequest, textStatus, errorThrown) {
					jQuery('#tdViewId-'+idElementRow).css('background', 'none repeat scroll 0 0 transparent');
					jQuery('#tdViewId-'+idElementRow).html(textStatus);
				}
			});
		}
		else {
			jQuery(this).addClass('openTrView');
			jQuery(this).removeClass('closeTrView'); 
			jQuery('#trViewId-'+idElementRow).toggle("fast");
			jQuery('#tdViewId-'+idElementRow).html('');
		}

		return false;
	});
}	

function actionTrConfig(obj) {
	
	jQuery(obj).css('display','block');  /* rendo visibile il tasto espandi per i dettagli ajax */
	
	jQuery(obj).click(function() {

		/* backoffice get Class element <a href="" class="actionTrConfig openTrConfig" action="userData"></a> */
		/* front-end get Class element <a href="" class="actionTrConfig openTrConfig" action="userData"></a> */
		idElementRow = jQuery(this).attr('action');
		
		if(debug) console.log("idElementRow "+idElementRow);
		
		if(jQuery(this).hasClass('openTrConfig')) {
			
			if(debug) console.log("hasClass openTrConfig");
			
			/* chiudo tutti gli eventuali aperti */
			jQuery('.actionTrConfig').each(function () {
				jQuery(this).removeClass('closeTrConfig'); 
				jQuery(this).addClass('openTrConfig');
			});
			jQuery('.trConfig').each(function () {
				jQuery(this).css('display', 'none');
			});

			jQuery(this).removeClass('openTrConfig'); 
			jQuery(this).addClass('closeTrConfig'); 

			/* se e' gia aperto lo chiudo */
			if(jQuery('#trConfigId-'+idElementRow).css('display')=='table-row')  {
				jQuery('#trConfigId-'+idElementRow).css('display', 'none');
				return false;
			}
			
			jQuery('#trConfigId-'+idElementRow).toggle('slow');

		}
		else {
			if(debug) console.log("hasClass closeTrConfig");
			
			jQuery(this).addClass('openTrConfig');
			jQuery(this).removeClass('closeTrConfig'); 
			jQuery('#trConfigId-'+idElementRow).toggle("fast");
		}

		return false;
	});
}

function actionNotaView(obj) {
	
	jQuery(obj).css('display','block');  /* rendo visibile il tasto espandi per i dettagli ajax */
	
	jQuery(obj).click(function() {

		/* backoffice get Id element <a href="" class="actionTrView openNotaView" id="actionNotaView-$idElementRow"></a> per ricavare Action da actionTrView */
		/* front-end get Id element <a href="" class="actionTrView openNotaView" id="actionNotaView-$idElementRow"></a> per ricavare Action da actionTrView */
		dataElement = jQuery(this).attr('id');
		dataElementArray = dataElement.split('-');
		var rowId = dataElementArray[1];
		
		var objActionTrView = jQuery('#actionTrView-'+rowId);
		var action = jQuery('#actionTrView-'+rowId).attr('action');


		actionArray = action.split('-');
		var model = actionArray[0];
		var idElement = actionArray[1];
		
		/* aggiunto per il front-end il terzo parametro -$order_id$[Cart]stato per avere unicita' con dispensa e articolo loccati */
		if(actionArray[2]!=null)  
			idElementRow = idElement+"-"+actionArray[2];
		else
			idElementRow = idElement;
		
		
		if(jQuery(objActionTrView).hasClass('openTrView')) {
			jQuery(objActionTrView).removeClass('openTrView'); 
			jQuery(objActionTrView).addClass('closeTrView'); 

			/* se e' gia aperto lo chiudo */
			if(jQuery('#trViewId-'+idElementRow).css('display')=='table-row')  {
				jQuery('#trViewId-'+idElementRow).css('display', 'none');
				return false;
			}

			/* chiudo tutti gli eventuali aperti */
			jQuery('.trView').each(function () {
				jQuery(this).css('display', 'none');
			});

			jQuery('#tdViewId-'+idElementRow).html('');
			jQuery('#trViewId-'+idElementRow).css('min-height', '50px');
			jQuery('#trViewId-'+idElementRow).toggle('slow');
			jQuery('#tdViewId-'+idElementRow).css('background', 'url("'+app_img+'/ajax-loader.gif") no-repeat scroll center 0 transparent');
			
			if(isBackoffice())
				url = "/administrator/index.php?option=com_cake&controller=Ajax&action=view_"+model+"&id="+idElement+"&evidenzia=nota&format=notmpl";
			else
				url = "/index.php?option=com_cake&controller=Ajax&action=view_"+model+"&id="+idElement+"&evidenzia=nota&format=notmpl";

			jQuery.ajax({
				type: "get", 
				url: url,
				data: "", 
				success: function(response) {
					jQuery('#tdViewId-'+idElementRow).css('background', 'none repeat scroll 0 0 transparent');
					jQuery('#tdViewId-'+idElementRow).html(response);
				},
				error:function (XMLHttpRequest, textStatus, errorThrown) {
					jQuery('#tdViewId-'+idElementRow).css('background', 'none repeat scroll 0 0 transparent');
					jQuery('#tdViewId-'+idElementRow).html(textStatus);
				}
			});
		}
		else {
			jQuery(objActionTrView).addClass('openTrView');
			jQuery(objActionTrView).removeClass('closeTrView'); 
			jQuery('#trViewId-'+idElementRow).toggle("fast");
			jQuery('#tdViewId-'+idElementRow).html('');
		}

		return false;
	});
}	

/* 
 * il valore dovrebbe essere numerico 
 * 1
 * 1.000,50
 * per isNumer dev'essere 1000.50
 */
function validateNumberField(field,label) {
	var value = jQuery(field).val();
	if(value.length>0) {
		value = numberToJs(value); /* in 1000.50 */
		if(!isNumber(value)) {
			alert("Il campo "+label+" dev'essere indicato con un valore numerico");
			jQuery(field).val('');
			jQuery(field).focus();
			return false;
		}
		return true;
	}
	return false;
}

/* 
 * il valore dovrebbe essere numerico positivo
 * 1
 * 1.000,50
 * per isNumer dev'essere 1000.50
 */
function validateNumberPositiveField(field,label) {
	var value = jQuery(field).val();
	if(value.length>0) {
		value = numberToJs(value); /* in 1000.50 */
		if(!isNumber(value)) {
			alert("Il campo "+label+" dev'essere indicato con un valore numerico");
			jQuery(field).val('');
			jQuery(field).focus();
			return false;
		}
		
		if(value<0) {
			alert("Il campo "+label+" NON può essere indicato con un valore negativo");
			jQuery(field).val('');
			jQuery(field).focus();
			return false;			
		}
		return true;
	}
	return false;
}

/*
 * da 1000.50 in 1.000,50
 */
function setNumberFormat(field) {
	var value = jQuery(field).val();
	if(value.length>0) {
		 value = numberToJs(value);
		 jQuery(field).val(number_format(value,2,',','.'));
	}
}

/* 
 * valore in 1000.50
 */
function numberToJs(number) {
	
	if(number==undefined) return '0.00';
	
	/* elimino le migliaia */
	number = number.replace('.','');

	/* converto eventuali decimanali */
	number = number.replace(',','.');	
	
	return number; 
}

function isNumber(n) {
	  return !isNaN(parseFloat(n)) && isFinite(n);
}

function arrotondaNumero(numero,nDecimali){
	var numero_arrotondato = Math.round(numero*Math.pow(10,nDecimali))/Math.pow(10,nDecimali);
	return numero_arrotondato;
} 

/* 
 * copy to AppController::importoToDatabase()
 * 
 * in View (.ctp,.js) tutti gli importi in 1.000,50
 * 	php  number_format(num,2,separatoreDecimali,separatoreMigliaia) = da 1000.50 in 1.000,50
 * 	js   number_format(num,2,separatoreDecimali,separatoreMigliaia) = da 1000.50 in 1.000,50
 * 
 * per il database o .js tutti gli importi in 1000.50
 * 	js  numberToJs         = da 1.000,50 in 1000.50 
 * 	php importoToDatabase  = da 1.000,50 in 1000.50
 * 
 *  php number_format     call /Helper/TabsHelper.php input[text][importo_forzato]
 *  					  call ArticlesController::admin_edit() per data['prezzo'] da visualizzare in admin_edit.ctp
 *  js  number_format     call View/Storerooms/admin_storeromm_to_user.ctp per input[text][prezzo]
 *  					  call ecommRowsBackOffice.js jQuery(this).find('.importo_forzato').change(function() 
 *  php importoToDatabase call ArticlesController::admin_edit() per data['prezzo'] da inserire in database
 *  
 */
function number_format( number, decimals, dec_point, thousands_sep ) {
	 /* da 1000.5678 in 1.000,57 */
	 /* da 1000 in 1.000,00 */
	 
	 var n = number, c = isNaN(decimals = Math.abs(decimals)) ? 2 : decimals;
	 var d = dec_point == undefined ? "." : dec_point;
	 var t = thousands_sep == undefined ? "," : thousands_sep, s = n < 0 ? "-" : "";
	 var i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", j = (j = i.length) > 3 ? j % 3 : 0;
	 
	 return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
}

function apriPopUp(url) {

	jQuery('body').append('<div class="popupWrap"></div><div class="popup"></div>');

	jQuery('.popup').css('background', 'url("'+app_img+'/ajax-loader.gif") no-repeat scroll center center transparent');
	
	jQuery('.popup').load(url, function(response, status, XMLHttpRequest) {
	
		  if ( status == "error" ) {
		  }
		  if ( status == "success") {
			  jQuery('.popup').css('background', 'none repeat scroll 0 0 #efefef');
		  }
		  
		  jQuery('.button-close').on('click', 
			  function() { 
		  		var close_popup = function() {
		  			jQuery('.popupWrap').remove();
		  			jQuery('.popup').remove();
		  	  }	
					
			  jQuery('.popupWrap').css('display','hide');		
			  jQuery('.popupWrap').animate( {opacity:0}, 200, close_popup );
		   });
	});
}

function getHost() {
	url = window.location.href;

	var arr = url.split("/");

	var host = arr[0] + "//" + arr[2];
	
	/* console.log(host); */
	
	return host;
}

function isBackoffice() {
	url = window.location.href;

	var arr = url.split("/");

	if(arr[3]!=null && arr[3]=='administrator')
		return true;
	else
		return false;	
}

/*
 *   paragona la data (yyyymmgg o yyyy-mm-gg) con la data odierna
 *   return < = >
 */
function compare_date_today(data) {

	 /*
	  * ctrl se data e' yyyymmgg o yyyy-mm-gg
	  */
	 if(data!=undefined && data.indexOf("-") > 0) {
		 esito = true;
		 arr = data.split("-");
		 if(arr[0]!= undefined) anno = arr[0];
		 else esito = false;
		 if(arr[1]!= undefined) mese = arr[1];
		 else esito = false;
		 if(arr[2]!= undefined) giorno = arr[2];
		 else esito = false;
		 data = anno+mese+giorno;
	 }

	  /*
	   * data odierna in yyyymmgg
	   */
    var oggi = new Date();
    var oggi_giorno = ''+oggi.getDate();
    if(oggi_giorno.length==1) oggi_giorno = "0"+oggi_giorno;

    var oggi_mese = ""+(oggi.getMonth()+1);

    if(oggi_mese.length==1) oggi_mese = "0"+oggi_mese;

    var oggi_anno = ""+oggi.getFullYear();

    oggi = oggi_anno + oggi_mese + oggi_giorno;

    /*console.log(data + ' - '+ oggi);*/
     
    return compare_date(data, oggi);
}
 
/*
 *   paragona la data1 (yyyymmgg) con la data2 (yyyymmgg) 
 *   return < = >
 */ 
function compare_date(data1, data2) {
	    if(data1 < data2) 
	    	return "<";
	     else 
	     if(data1 == data2) 
	         return "=";
	     else 
	         return ">";
}

function validateEmail(email) { 
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
} 

/*
 * Utente 	        lettere, numeri, e i segni . _ – 	/^([a-zA-Z0-9\.\_\-])+$/
 * Password 	    min 6, max 12 di caratteri, numeri, _ * – + ! ? , : ; . e lettere accentate 	/^[a-zA-Z0-9\_\*\-\+\!\?\,\:\;\.\xE0\xE8\xE9\xF9\xF2\xEC\x27]{6,12}/
 * Nome 	        caratteri, lettere accentate apostrofo e un solo spazio fra le parole 	/^([a-zA-Z\xE0\xE8\xE9\xF9\xF2\xEC\x27]\s?)+$/
 * C.A.P. 	        5 numeri 	/^\d{5}$/
 * E-mail 	        caratteri e . _ % – + @ + caratteri compreso . + . + min 2, max 4 caratteri 	/^[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/
 * Data 	        formato mm/gg/aaaa o mm-gg-aaaa o mm.gg.aaaa 	/^(0[1-9]|[12][0-9]|3[01])[- /.](0[1-9]|1[012])[-/.](19|20)\d\d/
 * Codice fiscale 	vedi regole su Wikipedia 	/^[a-zA-Z]{6}\d\d[a-zA-Z]\d\d[a-zA-Z]\d\d\d[a-zA-Z]/
 */
function checkPatternChars(nm,vlu,pattern,required){
  if ( required === undefined ) {
      required = false;
   } 
  if(!required && vlu==""){
    return true;
  }
  if (!pattern.test(vlu)){
	  if(nm=='File')
		alert("Il nome del file contiene caratteri strani!");
	else
		alert("Il campo "+nm+" non e\' valido!");
    return false;
  }
  else { 
    return true; 
  }
}