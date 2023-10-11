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
        if (jQuery("input[name='trasportAndCost1']").length > 0)
            a = jQuery("input[name='trasportAndCost1']:checked").val();
    } else
    if (doc_options == 'to-users') {
        a = jQuery("input[name='user_phone1']:checked").val();
        b = jQuery("input[name='user_email1']:checked").val();
        c = jQuery("input[name='user_address1']:checked").val();
        d = jQuery("input[name='totale_per_utente']:checked").val();
        if (jQuery("input[name='trasportAndCost2']").length > 0)
            e = jQuery("input[name='trasportAndCost2']:checked").val();
        else
            e = 'N';
        f = jQuery("input[name='user_avatar1']:checked").val();
        g = jQuery("input[name='dettaglio_per_utente']:checked").val();
        h = jQuery("input[name='note1']:checked").val();
        i = jQuery("input[name='delete_to_referent1']:checked").val();
    } else
    if (doc_options == 'to-users-label') {
        a = jQuery("input[name='user_phone']:checked").val();
        b = jQuery("input[name='user_email']:checked").val();
        c = jQuery("input[name='user_address']:checked").val();
        if (jQuery("input[name='trasportAndCost3']").length > 0)
            d = jQuery("input[name='trasportAndCost3']:checked").val();
        else
            d = 'N';
        e = jQuery("input[name='user_avatar2']:checked").val();
        f = jQuery("input[name='delete_to_referent2']:checked").val();
    } else
    if (doc_options == 'to-articles') {
        if (jQuery("input[name='trasportAndCost4']").length > 0)
            a = jQuery("input[name='trasportAndCost4']:checked").val();
        else
            a = 'N';
        b = jQuery("input[name='codice2']:checked").val();
        b = jQuery("input[name='codice2']:checked").val();
        if (jQuery("input[name='pezzi_confezione1']").length > 0)
            c = jQuery("input[name='pezzi_confezione1']:checked").val();
        else
            c = 'N';
    } else
    if (doc_options == 'to-articles-details') {
        a = jQuery("input[name='acquistato_il']:checked").val();
        if (jQuery("input[name='article_img']").length > 0)
            b = jQuery("input[name='article_img']:checked").val();
        else
            b = 'N';
        if (jQuery("input[name='trasportAndCost5']").length > 0)
            c = jQuery("input[name='trasportAndCost5']:checked").val();
        else
            c = 'N';
        d = jQuery("input[name='totale_per_articolo']:checked").val();
        e = jQuery("input[name='codice1']:checked").val();
    } else
    if (doc_options == 'des-referent-to-supplier') {
        a = jQuery("input[name='codice1']:checked").val();
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
        a = jQuery("input[name='codice5']:checked").val();
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
        a = jQuery("input[name='codice2']:checked").val();
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
        a = jQuery("input[name='codice3']:checked").val();
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
        a = jQuery("input[name='codice4']:checked").val();
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

    jQuery('.prezzo_um_riferimento').html("");

    var qta = numberToJs(jQuery('#qta').val());
    var prezzo = numberToJs(jQuery('#prezzo').val());

    if (um == null || um == '') /* la prima volta */
        um = jQuery('#um').val();
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
            str += 'value="HG" id="ArticleUmRiferimentoHG" name="data[Article][um_riferimento]">&nbsp;&nbsp;<label class="nospace" for="ArticleUmRiferimentoHG">' + number_format(prezzo_um_riferimento / 10, 2, ',', '.') + '&nbsp;&euro;&nbsp;al&nbsp;Ettogrammo</label></br>';

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

        jQuery('.prezzo_um_riferimento').html(str);
    }
}

/* 
 * creo array con ID dei box che mostro/nascondo
 */
function popolaArrayIdBox() {
    var arrayIdBox = new Array();

    i = 0;
    jQuery('fieldset').children().each(function () {

        /*
         * tratto tutti i DIV di primo livello per estrarre l'ID
         * */
        var idBox = jQuery(this).attr('id');
        if (idBox != null) {
            arrayIdBox[i] = idBox;
            i++;
        } else {
            /*
             * tratto tutti i DIV di secondo livello per estrarre l'ID
             * 		alcuni DIV di primo livello non hanno l'ID perche' sono solo per posizionamento
             * */
            jQuery(this).children().each(function () {
                var idBox = jQuery(this).attr('id');
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

    debug = false;

    if (jQuery('#' + caller).length == 0)
        alert("Nella pagina il div con ID " + caller + " non esiste");

    arrayIdBox = popolaArrayIdBox();
    arrayIdBoxNew = new Array();

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
            jQuery('#' + arrayIdBoxNew[i]).css('display', 'block');
            jQuery('#' + arrayIdBoxNew[i]).css('min-height', '35px');
            urlCss = 'url(' + app_img + '/ajax-loader.gif) no-repeat scroll center 0 transparent';
            jQuery('#' + arrayIdBoxNew[i]).css('background', urlCss);

            jQuery('#' + arrayIdBoxNew[i] + ' *> input[type=radio]').removeAttr("checked");

            if (debug)
                alert(" lo attivo e parte ajax-loader ");

            jQuery('#' + arrayIdBoxNew[i]).css('background', 'none repeat scroll 0 0 transparent');
        } else {
            jQuery('#' + arrayIdBoxNew[i]).css('background', 'none repeat scroll 0 0 transparent');
            jQuery('#' + arrayIdBoxNew[i]).css('display', 'none');
            jQuery('#' + arrayIdBoxNew[i] + ' *> input[type=radio]').removeAttr("checked");

            if (debug)
                alert(" lo nascondo");
        }
    }
}

function ajaxCallBox(url, idDivTarget) {

    var urlAjax = 'url("' + app_img + '/ajax-loader.gif") no-repeat scroll center 0 transparent';
    jQuery('#' + idDivTarget).html('');
    jQuery('#' + idDivTarget).css('background', urlAjax);

    jQuery.ajax({
        type: "get",
        url: url,
        data: "",
		cache: false,
        success: function (response) {
            jQuery('#' + idDivTarget).css('background', 'none repeat scroll 0 0 transparent');
            jQuery('#' + idDivTarget).html(response);
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            jQuery('#' + idDivTarget).css('background', 'none repeat scroll 0 0 transparent');
            jQuery('#' + idDivTarget).html(textStatus);
        }
    });
} 	