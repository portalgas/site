"use strict";

function My() {

    if (!(this instanceof My)) {
        throw new TypeError("My constructor cannot be called as a function.");
    }

    this.init();
};

My.prototype = {
    constructor: My, //costruttore

    bindEvents: function () {
        var _this = this;

        console.log("My bindEvents");
		
		/*
		 * + / - accordion 
		 */	
		$('.collapse').on('shown.bs.collapse', function(){
			$(this).parent().find(".fa-plus").removeClass("fa-plus").addClass("fa-minus");
		}).on('hidden.bs.collapse', function(){
			$(this).parent().find(".fa-minus").removeClass("fa-minus").addClass("fa-plus");
		});

		/*
		 * + / - dettagli
		 */
		$('.ajax_details').on('hidden.bs.collapse', function(e){
			$(this).prev().find(".fa-search-minus").removeClass("fa-search-minus").addClass("fa-search-plus");
			
			var action = $(this).attr('data-attr-action');
			var dataElementArray = action.split('-');
			var model = dataElementArray[0];
			var id = dataElementArray[1];
			
			$('#ajax_details_content-'+id).html('');
		}).on('shown.bs.collapse', function(e){
			$(this).prev().find(".fa-search-plus").removeClass("fa-search-plus").addClass("fa-search-minus");

			var action = $(this).attr('data-attr-action');
			var dataElementArray = action.split('-');
			var model = dataElementArray[0];
			var id = dataElementArray[1];
			
			$('#ajax_details_content-'+id).html('');
			$('#ajax_details_content-'+id).css('min-height', '50px');
			$('#ajax_details_content-'+id).css('background', 'url("'+app_img+'/ajax-loader.gif") no-repeat scroll center 0 transparent');
			
			var url = '';
			if(isBackoffice())
				url = "/administrator/index.php?option=com_cake&controller=Ajax&action=view_"+model+"&id="+id+"&evidenzia=&format=notmpl";
			else
				url = "/?option=com_cake&controller=Ajax&action=view_"+model+"&id="+id+"&evidenzia=&format=notmpl";
		
			$.ajax({
				type: "get", 
				url: url,
				data: "",
				success: function(response) {
					$('#ajax_details_content-'+id).css('background', 'none repeat scroll 0 0 transparent');
					$('#ajax_details_content-'+id).html(response);
				},
				error:function (XMLHttpRequest, textStatus, errorThrown) {
					$('#ajax_details_content-'+id).css('background', 'none repeat scroll 0 0 transparent');
					$('#ajax_details_content-'+id).html(textStatus);
				}
			});		
		})
		
		/*
		 * gestione btm con menu profilato (ex ordini)
		 */
		$('.btn-menu').click(function(event) {
		
			event.preventDefault();
			
		    var url = $(this).attr("data-attr-url");
		
			var header = $(this).attr("data-attr-header");
			var size = $(this).attr("data-attr-size");
			
			var opts = new Array();
			opts = {"header": header , "size": size};
		
			if (typeof url == 'undefined' || url == '') 
				console.log("error - url undefined!");
			else
				apriPopUpBootstrap(url, opts);
				
		});
				 
		/*
		 * tasto reset su select/options
		 */	
		$('form').on('reset', function(event){
		  $('.selectpicker',this).each(function(index, element){
			var $this = $(this);
			setTimeout(function(){
			  $this.selectpicker('val',$this.val());
			},0);
		  });
		});	

		$.datepicker.setDefaults($.datepicker.regional['it']);
		
		$('.double').focusout(function() {setNumberFormat(this);});  /*	applicato a tutti i campi prezzo */
		$('.double').focus(function() {$(this).select();});
		$('.onFocusAllSelect').focus(function() {$(this).select();});

		$(".blank").attr("target","_blank");
		
		CKEDITOR.stylesSet.add( 'my_styles', [
			/* Block-level styles. */
			{ name: 'Titolo', element: 'h2', styles: { } },
			{ name: 'Sotto-titolo',  element: 'h3', styles: { } },
		
			/* Inline styles. */
			{ name: 'MyStyle', element: 'span', attributes: { 'class': 'my_style' } },
			{ name: 'Rosso', element: 'span', styles: { 'background-color': 'red' } }
		]);
			
		/* CKEditor, MyToolBar in /cake/components/com_cake/app/webroot/js/ckeditor/config.js */
		var CKconfig = {
				 filebrowserWindowWidth : '100%',
				 filebrowserWindowHeight : '100%',
				 toolbar : 'MyToolBar',
				 enterMode : CKEDITOR.ENTER_BR,
				 shiftEnterMode: CKEDITOR.ENTER_P,
				 stylesSet: 'my_styles'
			 };
			 
		$("textarea[class!='noeditor form-control']").ckeditor(CKconfig);
		
		$('.filter').show('low');        /* rendo visibile il tasto submit del filtro */
		$('.filter').click(function() {  /* devo ripulire il campo hidden che inizia per page perche' dopo la prima pagina sbaglia la ricerca con filtri */
			$("input[name^='page']").val('');
			$("input[name^='page']").attr("name", "");
		});		
		$('.actionTrView').css('display','inline-block');  /* rendo visibile il tasto espandi per i dettagli ajax */
		$('.actionNotaView').css('display','inline-block');  /* rendo visibile il tasto espandi per i dettagli ajax */
		
		$('.actionTrView').each(function () {
			actionTrView(this);
		});

		$('.actionTrConfig').css('display','inline-block');  /* rendo visibile il tasto espandi per i dettagli ajax */
		$('.actionTrConfig').each(function () {
			actionTrConfig(this);
		});
						
		$('.actionNotaView').each(function () {
			actionNotaView(this); 
		});
					
		/* riscrivo F O R M */
		$(".cakeContainer form").each(function() {

			/* method G E T */
			if($(this).attr('method')=='get') {
			
				var actionForm = $(location).attr('href'); /* $(this).attr('action'); */
				
				if(actionForm!=null && actionForm.indexOf('?')>0) {
				   actionForm = actionForm.substring(actionForm.indexOf("?")+1,actionForm.length);
				   var actionFormArr = actionForm.split('&');
					   
				   /* 
					per ogni key=value creo input type=hidden 
					tranne per i key che iniziano per Filter...
				   */    
				   for (var k in actionFormArr){
						if (actionFormArr.hasOwnProperty(k) && 
							actionFormArr[k].indexOf('Filter')==-1 && 
							actionFormArr[k].indexOf('_method')==-1 ) { 
							var actionFormArr2 = actionFormArr[k].split('=');
							  $('<input>').attr({
									type: 'hidden',
									id: actionFormArr2[0],
									name: actionFormArr2[0],
									value: actionFormArr2[1] 
								}).appendTo(this);               
						 }
					   }
				   } 
			}
		});
		
		/* torna in alto */
		$("body").append("<div id=\"scroll_to_top\"><a href=\"#top\">Torna su</a></div>");
		$("#scroll_to_top a").css({	'display' : 'none', 'z-index' : '9', 'position' : 'fixed', 'top' : '100%', 'width' : '110px', 'margin-top' : '-30px', 'right' : '50%', 'margin-left' : '-50px', 'height' : '20px', 'padding' : '3px 5px', 'font-size' : '14px', 'text-align' : 'center', 'padding' : '3px', 'color' : '#FFFFFF', 'background-color' : '#625043', '-moz-border-radius' : '5px', '-khtml-border-radius' : '5px', '-webkit-border-radius' : '5px', 'opacity' : '.8', 'text-decoration' : 'none'});
		$('#scroll_to_top a').click(function(){
			$('html, body').animate({scrollTop:0}, 'slow');
		});

		$('#help'). mouseenter(function () {
			$(this).animate({right: '-15px'}, 500);
		});	
		$('#help').mouseleave(function () {
			$(this).animate({right: '-100px'}, 500);
		});	
		$('.logo').click(function () {
			var url = '/administrator/index.php?option=com_cake&controller=Manuals&action=index';
			window.location.href = url;
		});	
		
		var scroll_timer;
		var displayed = false;
		var top = $(document.body).children(0).position().top;
		$(window).scroll(function () {
			window.clearTimeout(scroll_timer);
			scroll_timer = window.setTimeout(function () {
				if($(window).scrollTop() <= top)
				{
					displayed = false;
					$('#scroll_to_top a').fadeOut(500);
				}
				else if(displayed == false)
				{
					displayed = true;
					$('#scroll_to_top a').stop(true, true).show().click(function () { $('#scroll_to_top a').fadeOut(500); });
				}
			}, 100);
		});
			
		/* menu laterale */
		$('.navbar-toggler').on('click', function(event) {
			event.preventDefault();
			$(this).closest('.navbar-minimal').toggleClass('open');
		})		

    },
    
    number_format: function (number, decimals, dec_point, thousands_sep) {
        /* da 1000.5678 in 1.000,57 */
        /* da 1000 in 1.000,00 */

        var n = number, c = isNaN(decimals = Math.abs(decimals)) ? 2 : decimals;
        var d = dec_point == undefined ? "." : dec_point;
        var t = thousands_sep == undefined ? "," : thousands_sep, s = n < 0 ? "-" : "";
        var i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", j = (j = i.length) > 3 ? j % 3 : 0;

        return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
    },
        
    init: function () {
        this.year = new Date().getFullYear();
        
        console.log("My.init");
                
        this.bindEvents();
    }    
};
	
/*
 * setExportDocsParameters() non implementato per 
 *              Ajax::admin_view_tesoriere_export_docs.ctp 	 
 *              Pages:admin_home.ctp
 */
function setExportDocsParameters(doc_options) {

    var parametersFilter = '';

    var a = '';
    var b = '';
    var c = '';
    var d = '';
    var e = '';
    var f = '';
    var g = '';
    var h = '';
    var i = '';

    if (doc_options == 'to-users-all-modify') {
        if ($("input[name='trasportAndCost1']").length > 0)
            a = $("input[name='trasportAndCost1']:checked").val();
    } else
    if (doc_options == 'to-users') {
        a = $("input[name='user_phone1']:checked").val();
        b = $("input[name='user_email1']:checked").val();
        c = $("input[name='user_address1']:checked").val();
        d = $("input[name='totale_per_utente']:checked").val();
        if ($("input[name='trasportAndCost2']").length > 0)
            e = $("input[name='trasportAndCost2']:checked").val();
        else
            e = 'N';
        f = $("input[name='user_avatar1']:checked").val();
        g = $("input[name='dettaglio_per_utente']:checked").val();
        h = $("input[name='note1']:checked").val();
        i = $("input[name='delete_to_referent1']:checked").val();
    } else
    if (doc_options == 'to-users-label') {
        a = $("input[name='user_phone']:checked").val();
        b = $("input[name='user_email']:checked").val();
        c = $("input[name='user_address']:checked").val();
        if ($("input[name='trasportAndCost3']").length > 0)
            d = $("input[name='trasportAndCost3']:checked").val();
        else
            d = 'N';
        e = $("input[name='user_avatar2']:checked").val();
        f = $("input[name='delete_to_referent2']:checked").val();
    } else
    if (doc_options == 'to-articles') {
        if ($("input[name='trasportAndCost4']").length > 0)
            a = $("input[name='trasportAndCost4']:checked").val();
        else
            a = 'N';
        b = $("input[name='codice2']:checked").val();
        b = $("input[name='codice2']:checked").val();
        if ($("input[name='pezzi_confezione1']").length > 0)
            c = $("input[name='pezzi_confezione1']:checked").val();
        else
            c = 'N';
    } else
    if (doc_options == 'to-articles-details') {
        a = $("input[name='acquistato_il']:checked").val();
        if ($("input[name='article_img']").length > 0)
            b = $("input[name='article_img']:checked").val();
        else
            b = 'N';
        if ($("input[name='trasportAndCost5']").length > 0)
            c = $("input[name='trasportAndCost5']:checked").val();
        else
            c = 'N';
        d = $("input[name='totale_per_articolo']:checked").val();
        e = $("input[name='codice1']:checked").val();
    } else
    if (doc_options == 'des-referent-to-supplier') {
        a = $("input[name='codice1']:checked").val();
        b = '';
        c = '';
        d = '';
        e = '';
        f = '';
        g = '';
        h = '';
        i = '';
    } else
    if (doc_options == 'des-referent-to-supplier-monitoring') {
        a = $("input[name='codice5']:checked").val();
        b = '';
        c = '';
        d = '';
        e = '';
        f = '';
        g = '';
        h = '';
        i = '';
    } else
    if (doc_options == 'des-referent-to-supplier-details') {
        a = $("input[name='codice2']:checked").val();
        b = '';
        c = '';
        d = '';
        e = '';
        f = '';
        g = '';
        h = '';
        i = '';
    } else
    if (doc_options == 'des-referent-to-supplier-split-org') {
        a = $("input[name='codice3']:checked").val();
        b = '';
        c = '';
        d = '';
        e = '';
        f = '';
        g = '';
        h = '';
        i = '';
    } else
    if (doc_options == 'des-referent-to-supplier-split-org-monitoring') {
        a = $("input[name='codice4']:checked").val();
        b = '';
        c = '';
        d = '';
        e = '';
        f = '';
        g = '';
        h = '';
        i = '';
    }

    parametersFilter = 'a=' + a + '&b=' + b + '&c=' + c + '&d=' + d + '&e=' + e + '&f=' + f + '&g=' + g + '&h=' + h + '&i=' +i; 

    return parametersFilter;
}

/*
 *  il calcolo lo faccio sempre con UM
 *  um_riferimento mi serve per edit checkbox
 */
function setArticlePrezzoUmRiferimento(um, um_riferimento) {

    var debug = false;

    $('.prezzo_um_riferimento').html("");

    var qta = numberToJs($('#qta').val());
    var prezzo = numberToJs($('#prezzo').val());

    if (um == null || um == '') /* la prima volta */
        um = $('#um').val();
    if (um_riferimento == null || um_riferimento == '')
        um_riferimento = um;

    if (qta != "" && prezzo != "" && um != null) {

        if (debug)
            console.log("qta " + qta + " - prezzo " + prezzo + " - um " + um + " um_riferimento " + um_riferimento);

        var prezzo_um_riferimento = (prezzo / qta);

        if (debug)
            console.log("prezzo_um_riferimento (prezzo/qta) " + prezzo_um_riferimento);

        var str = '';
        if (um == 'PZ') {
            str += '<input class="nospace" checked="checked" type="radio" value="PZ" id="ArticleUmRiferimentoPZ" name="data[Article][um_riferimento]">&nbsp;&nbsp;<label class="nospace" for="ArticleUmRiferimentoPZ">' + number_format(prezzo_um_riferimento, 2, ',', '.') + '&nbsp;&euro;&nbsp;al&nbsp;Pezzo</label>';
        } else
        if (um == 'KG') {
            str += '<input class="nospace" type="radio" ';
            (um_riferimento == 'GR') ? str += 'checked="checked" ' : str += ' ';
            str += 'value="GR" id="ArticleUmRiferimentoGR" name="data[Article][um_riferimento]">&nbsp;&nbsp;<label class="nospace" for="ArticleUmRiferimentoGR">' + number_format(prezzo_um_riferimento / 1000, 2, ',', '.') + '&nbsp;&euro;&nbsp;al&nbsp;Grammo</label></br>';

            str += '<input class="nospace" type="radio" ';
            (um_riferimento == 'HG') ? str += 'checked="checked" ' : str += ' ';
            str += 'value="HG" id="ArticleUmRiferimentoHG" name="data[Article][um_riferimento]">&nbsp;&nbsp;<label class="nospace" for="ArticleUmRiferimentoHG">' + number_format(prezzo_um_riferimento / 100, 2, ',', '.') + '&nbsp;&euro;&nbsp;al&nbsp;Ettogrammo</label></br>';

            str += '<input class="nospace" type="radio" ';
            (um_riferimento == 'KG') ? str += 'checked="checked" ' : str += ' ';
            str += 'value="KG" id="ArticleUmRiferimentoKG" name="data[Article][um_riferimento]">&nbsp;&nbsp;<label class="nospace" for="ArticleUmRiferimentoKG">' + number_format(prezzo_um_riferimento, 2, ',', '.') + '&nbsp;&euro;&nbsp;al&nbsp;Chilo</label></br>';
        } else
        if (um == 'HG') {
            str += '<input class="nospace" type="radio" ';
            (um_riferimento == 'GR') ? str += 'checked="checked" ' : str += ' ';
            str += 'value="GR" id="ArticleUmRiferimentoGR" name="data[Article][um_riferimento]">&nbsp;&nbsp;<label class="nospace" for="ArticleUmRiferimentoGR">' + number_format(prezzo_um_riferimento / 100, 2, ',', '.') + '&nbsp;&euro;&nbsp;al&nbsp;Grammo</label></br>';

            str += '<input class="nospace" type="radio" ';
            (um_riferimento == 'HG') ? str += 'checked="checked" ' : str += ' ';
            str += 'value="HG" id="ArticleUmRiferimentoHG" name="data[Article][um_riferimento]">&nbsp;&nbsp;<label class="nospace" for="ArticleUmRiferimentoHG">' + number_format(prezzo_um_riferimento, 2, ',', '.') + '&nbsp;&euro;&nbsp;al&nbsp;Ettogrammo</label></br>';

            str += '<input class="nospace" type="radio" ';
            (um_riferimento == 'KG') ? str += 'checked="checked" ' : str += ' ';
            str += 'value="KG" id="ArticleUmRiferimentoKG" name="data[Article][um_riferimento]">&nbsp;&nbsp;<label class="nospace" for="ArticleUmRiferimentoKG">' + number_format(prezzo_um_riferimento * 10, 2, ',', '.') + '&nbsp;&euro;&nbsp;al&nbsp;Chilo</label></br>';
        } else
        if (um == 'GR') {
            str += '<input class="nospace" type="radio" ';
            (um_riferimento == 'GR') ? str += 'checked="checked" ' : str += ' ';
            str += 'value="GR" id="ArticleUmRiferimentoGR" name="data[Article][um_riferimento]">&nbsp;&nbsp;<label class="nospace" for="ArticleUmRiferimentoGR">' + number_format(prezzo_um_riferimento, 2, ',', '.') + '&nbsp;&euro;&nbsp;al&nbsp;Grammo</label></br>';

            str += '<input class="nospace" type="radio" ';
            (um_riferimento == 'HG') ? str += 'checked="checked" ' : str += ' ';
            str += 'value="HG" id="ArticleUmRiferimentoHG" name="data[Article][um_riferimento]">&nbsp;&nbsp;<label class="nospace" for="ArticleUmRiferimentoHG">' + number_format(prezzo_um_riferimento * 100, 2, ',', '.') + '&nbsp;&euro;&nbsp;al&nbsp;Ettogrammo</label></br>';

            str += '<input class="nospace" type="radio" ';
            (um_riferimento == 'KG') ? str += 'checked="checked" ' : str += ' ';
            str += 'value="KG" id="ArticleUmRiferimentoKG" name="data[Article][um_riferimento]">&nbsp;&nbsp;<label class="nospace" for="ArticleUmRiferimentoKG">' + number_format(prezzo_um_riferimento * 1000, 2, ',', '.') + '&nbsp;&euro;&nbsp;al&nbsp;Chilo</label></br>';
        } else
        if (um == 'LT') {
            str += '<input class="nospace" type="radio" ';
            (um_riferimento == 'ML') ? str += 'checked="checked" ' : str += ' ';
            str += 'value="ML" id="ArticleUmRiferimentoML" name="data[Article][um_riferimento]">&nbsp;&nbsp;<label class="nospace" for="ArticleUmRiferimentoML">' + number_format(prezzo_um_riferimento / 1000, 2, ',', '.') + '&nbsp;&euro;&nbsp;al&nbsp;Millilitro</label></br>';

            str += '<input class="nospace" type="radio" ';
            (um_riferimento == 'DL') ? str += 'checked="checked" ' : str += ' ';
            str += 'value="DL" id="ArticleUmRiferimentoDL" name="data[Article][um_riferimento]">&nbsp;&nbsp;<label class="nospace" for="ArticleUmRiferimentoDL">' + number_format(prezzo_um_riferimento / 10, 2, ',', '.') + '&nbsp;&euro;&nbsp;al&nbsp;Decilitro</label></br>';

            str += '<input class="nospace" type="radio" ';
            (um_riferimento == 'LT') ? str += 'checked="checked" ' : str += ' ';
            str += 'value="LT" id="ArticleUmRiferimentoLT" name="data[Article][um_riferimento]">&nbsp;&nbsp;<label class="nospace" for="ArticleUmRiferimentoLT">' + number_format(prezzo_um_riferimento, 2, ',', '.') + '&nbsp;&euro;&nbsp;al&nbsp;Litro</label></br>';
        } else
        if (um == 'DL') {
            str += '<input class="nospace" type="radio" ';
            (um_riferimento == 'ML') ? str += 'checked="checked" ' : str += ' ';
            str += 'value="ML" id="ArticleUmRiferimentoML" name="data[Article][um_riferimento]">&nbsp;&nbsp;<label class="nospace" for="ArticleUmRiferimentoML">' + number_format(prezzo_um_riferimento / 100, 2, ',', '.') + '&nbsp;&euro;&nbsp;al&nbsp;Millilitro</label></br>';

            str += '<input class="nospace" type="radio" ';
            (um_riferimento == 'DL') ? str += 'checked="checked" ' : str += ' ';
            str += 'value="DL" id="ArticleUmRiferimentoDL" name="data[Article][um_riferimento]">&nbsp;&nbsp;<label class="nospace" for="ArticleUmRiferimentoDL">' + number_format(prezzo_um_riferimento, 2, ',', '.') + '&nbsp;&euro;&nbsp;al&nbsp;Decilitro</label></br>';

            str += '<input class="nospace" type="radio" ';
            (um_riferimento == 'LT') ? str += 'checked="checked" ' : str += ' ';
            str += 'value="LT" id="ArticleUmRiferimentoLT" name="data[Article][um_riferimento]">&nbsp;&nbsp;<label class="nospace" for="ArticleUmRiferimentoLT">' + number_format(prezzo_um_riferimento * 10, 2, ',', '.') + '&nbsp;&euro;&nbsp;al&nbsp;Litro</label></br>';
        } else
        if (um == 'ML') {
            str += '<input class="nospace" type="radio" ';
            (um_riferimento == 'ML') ? str += 'checked="checked" ' : str += ' ';
            str += 'value="ML" id="ArticleUmRiferimentoML" name="data[Article][um_riferimento]">&nbsp;&nbsp;<label class="nospace" for="ArticleUmRiferimentoML">' + number_format(prezzo_um_riferimento, 2, ',', '.') + '&nbsp;&euro;&nbsp;al&nbsp;Millilitro</label></br>';

            str += '<input class="nospace" type="radio" ';
            (um_riferimento == 'DL') ? str += 'checked="checked" ' : str += ' ';
            str += 'value="DL" id="ArticleUmRiferimentoDL" name="data[Article][um_riferimento]">&nbsp;&nbsp;<label class="nospace" for="ArticleUmRiferimentoDL">' + number_format(prezzo_um_riferimento * 100, 2, ',', '.') + '&nbsp;&euro;&nbsp;al&nbsp;Decilitro</label></br>';

            str += '<input class="nospace" type="radio" ';
            (um_riferimento == 'LT') ? str += 'checked="checked" ' : str += ' ';
            str += 'value="LT" id="ArticleUmRiferimentoLT" name="data[Article][um_riferimento]">&nbsp;&nbsp;<label class="nospace" for="ArticleUmRiferimentoLT">' + number_format(prezzo_um_riferimento * 1000, 2, ',', '.') + '&nbsp;&euro;&nbsp;al&nbsp;Litro</label></br>';
        }

        $('.prezzo_um_riferimento').html(str);
    }
}

/* 
 * creo array con ID dei box che mostro/nascondo
 */
function popolaArrayIdBox() {
    var arrayIdBox = new Array();

    var i = 0;
    $('fieldset').children().each(function () {

        /*
         * tratto tutti i DIV di primo livello per estrarre l'ID
         * */
        var idBox = $(this).attr('id');
        if (idBox != null) {
            arrayIdBox[i] = idBox;
            i++;
        } else {
            /*
             * tratto tutti i DIV di secondo livello per estrarre l'ID
             * 		alcuni DIV di primo livello non hanno l'ID perche' sono solo per posizionamento
             * */
            $(this).children().each(function () {
                var idBox = $(this).attr('id');
                if (idBox != null) {
                    arrayIdBox[i] = idBox;
                    i++;
                }
            });
        }
    });
    /* console.log(arrayIdBox); */

    return arrayIdBox;
}
/* 
 * caller: box chiamante
 * call_child true/false
 *		 true: attivo il box successivo e chiamata ajax
 *       false: nascondo anche il box successivo (il chiamante e' null)
 */
function showHideBox(caller, call_child) {

    var debug = false;
	var i = 0;

    if ($('#' + caller).length == 0)
        alert("Nella pagina il div con ID " + caller + " non esiste");

    var arrayIdBox = popolaArrayIdBox();
    var arrayIdBoxNew = new Array();

    if (debug)
        alert("arrayIdBox.length " + arrayIdBox.length);

    /* elimino i box precedenti al chiamante */
    for (i = 0; i < arrayIdBox.length; i++) {
        if (debug)
            alert(i + " elimino i box precedenti al chiamante  " + arrayIdBox[i] + " = " + caller);
        if (caller == arrayIdBox[i])
            arrayIdBoxNew = arrayIdBox.splice(i + 1, arrayIdBox.length);
    }
    if (debug)
        alert("dopo aver eliminato i padri: arrayIdBoxNew.length " + arrayIdBoxNew.length);

    for (i = 0; i < arrayIdBoxNew.length; i++) {
        if (debug)
            alert(i + " tratto arrayIdBoxNew[i] " + arrayIdBoxNew[i]);

        if (i == 0 && call_child) { /* se call_child carico il box sottostante e faccio la chiamata ajax */
            $('#' + arrayIdBoxNew[i]).css('display', 'block');
            $('#' + arrayIdBoxNew[i]).css('min-height', '35px');
            var urlCss = 'url(' + app_img + '/ajax-loader.gif) no-repeat scroll center 0 transparent';
            $('#' + arrayIdBoxNew[i]).css('background', urlCss);

            $('#' + arrayIdBoxNew[i] + ' *> input[type=radio]').removeAttr("checked");

            if (debug)
                alert(" lo attivo e parte ajax-loader ");

            $('#' + arrayIdBoxNew[i]).css('background', 'none repeat scroll 0 0 transparent');
        } else {
            $('#' + arrayIdBoxNew[i]).css('background', 'none repeat scroll 0 0 transparent');
            $('#' + arrayIdBoxNew[i]).css('display', 'none');
            $('#' + arrayIdBoxNew[i] + ' *> input[type=radio]').removeAttr("checked");

            if (debug)
                alert(" lo nascondo");
        }
    }
}

function ajaxCallBox(url, idDivTarget) {

    var urlAjax = 'url("' + app_img + '/ajax-loader.gif") no-repeat scroll center 0 transparent';
    $('#' + idDivTarget).html('');
    $('#' + idDivTarget).css('background', urlAjax);

    $.ajax({
        type: "get",
        url: url,
        data: "",
		cache: false,
        success: function (response) {
            $('#' + idDivTarget).css('background', 'none repeat scroll 0 0 transparent');
            $('#' + idDivTarget).html(response);
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            $('#' + idDivTarget).css('background', 'none repeat scroll 0 0 transparent');
            $('#' + idDivTarget).html(textStatus);
        }
    });
} 	