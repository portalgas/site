$(document).ready(function() {	$('.btn-value-edit').click(function () {		// creo <input name="data[Article][712][ArticlesOrderQtaMassima]" value="0" class="form-control" type="text">		var model = $(this).attr('data-attr-model');		var id_name = $(this).attr('data-attr-id-name');	    var id = $(this).attr('data-attr-id');		var value = $(this).attr('data-attr-value');		/* console.log('model '+model+' id_name '+id_name+' id '+id+' value '+value); */		var html = '<input name="data['+model+']['+id+']['+id_name+']" value="'+value+'" class="form-control" type="text">';				$(this).html('');		$(this).removeClass('btn-value-edit').removeClass('btn');		$(this).append(html);		$(this).unbind('click');	});});var article_id_selected = '';function ctrlArticlesOrders() {	var debug=false;	var qta_massima = 0;	var qta_multipli = 1;	if(debug) console.log("selezionati articolo id "+$("input[name='article_id_selected']:checked").length+" da aggiungere");	for(i = 0; i < $("input[name='article_id_selected']:checked").length; i++) {		qta_massima = 0;		qta_multipli = 1;		article_id_selected += $("input[name='article_id_selected']:checked").eq(i).val()+',';				var article_id = $("input[name='article_id_selected']:checked").eq(i).val();			if(debug) console.log("tratto articolo id "+article_id);		if($("input[name='data[Article]["+article_id+"][ArticlesOrderPrezzo]']").length>0) {			var prezzo = $("input[name='data[Article]["+article_id+"][ArticlesOrderPrezzo]']").val(); 			if(prezzo=='' || prezzo==null || prezzo=='0,00' || prezzo=='0.00' || prezzo=='0') {				alert("Devi indicare l'importo per gli articoli che desideri associare all'ordine");				$("input[name='data[Article]["+article_id+"][ArticlesOrderPrezzo]']").focus();				return false;						}		}		if($("input[name='data[Article]["+article_id+"][ArticlesOrderPezziConfezione]']").length>0) {			var pezzi_confezione = $("input[name='data[Article]["+article_id+"][ArticlesOrderPezziConfezione]']").val(); 			if(pezzi_confezione=='' || pezzi_confezione==null || !isFinite(pezzi_confezione)) {				alert("Devi indicare il numero di pezzi per confezione per gli articoli che desideri associare all'ordine");				$("input[name='data[Article]["+article_id+"][ArticlesOrderPezziConfezione]']").focus();				return false;						}			pezzi_confezione = parseInt(pezzi_confezione);			if(pezzi_confezione <= 0) {				alert("Il numero di pezzi per confezione per gli articoli che desideri associare all'ordine deve essere > di zero");				$("input[name='data[Article]["+article_id+"][ArticlesOrderPezziConfezione]']").focus();				return false;						}		}		if($("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMinima]']").length>0) {			var qta_minima = $("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMinima]']").val(); 			if(qta_minima=='' || qta_minima==null || !isFinite(qta_minima)) {				alert("Devi indicare la quantità minima che un gasista può acquistare");				$("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMinima]']").focus();				return false;						}			if(qta_minima <= 0) {				alert("La quantità minima che un gasista può acquistare deve essere > di zero");				$("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMinima]']").focus();				return false;						}		}		if($("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMassima]']").length>0) {			qta_massima = $("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMassima]']").val(); 			if(qta_massima=='' || qta_massima==null || !isFinite(qta_massima)) {				alert("Devi indicare la quantità massima che un gasista può acquistare: di default 0");				$("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMassima]']").focus();				return false;						}		}		if($("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMinimaOrder]']").length>0) {			var qta_minima_order = $("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMinimaOrder]']").val(); 			if(qta_minima_order=='' || qta_minima_order==null || !isFinite(qta_minima_order)) {				alert("Devi indicare la quantità minima rispetto a tutti gli acquisti dell'ordine");				$("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMinimaOrder]']").focus();				return false;						}			}				if($("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMassimaOrder]']").length>0) {			var qta_massima_order = $("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMassimaOrder]']").val(); 			if(qta_massima_order=='' || qta_massima_order==null || !isFinite(qta_massima_order)) {				alert("Devi indicare la quantità massima rispetto a tutti gli acquisti dell'ordine");				$("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMassimaOrder]']").focus();				return false;						}			qta_massima_order = parseInt(qta_massima_order);			if(qta_massima_order > 0 && qta_massima_order < pezzi_confezione) {				alert("La quantità massima rispetto a tutti gli acquisti dell'ordine è inferiore al numero di pezzi in una confezione");				$("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMassimaOrder]']").focus();				return false;						}		}		if($("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMultipli]']").length>0) {			qta_multipli = $("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMultipli]']").val(); 			if(qta_multipli=='' || qta_multipli==null || !isFinite(qta_multipli)) {				alert("Devi indicare di che multiplo dev'essere la quantità per gli articoli associati all'ordine");				$("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMultipli]']").focus();				return false;						}			qta_multipli = parseInt(qta_multipli);			if(qta_multipli <= 0) {				alert("Il multiplo per gli articoli che desideri associare all'ordine deve essere > di zero");				$("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMultipli]']").focus();				return false;						}		}		if((qta_massima) > 0 && (qta_massima < qta_multipli)) {			alert("La quantità massima che un gasista può acquistare non può essere inferiore della quantità multipla");			$("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMassima]']").focus();			return false;		}			if(OrganizationHasFieldArticleAlertToQta=='Y') {			if($("input[name='data[Article]["+article_id+"][ArticlesOrderAlertToQta]']").length>0) {							var alert_to_qta = $("input[name='data[Article]["+article_id+"][ArticlesOrderAlertToQta]']").val(); 				if(alert_to_qta=='' || alert_to_qta==null || !isFinite(alert_to_qta)) {					alert("Devi indicare quando avvisare raggiunta una certa quantità per gli articoli associati all'ordine");					$("input[name='data[Article]["+article_id+"][ArticlesOrderAlertToQta]']").focus();					return false;							}				if(alert_to_qta <= 0) {					alert("La quantità che indica quando avvisare per gli articoli che desideri associare all'ordine deve essere > di zero");					$("input[name='data[Article]["+article_id+"][ArticlesOrderAlertToQta]']").focus();					return false;							}			}		} // end if(OrganizationHasFieldArticleAlertToQta=='Y')	} /* end loop articles */	return true;}