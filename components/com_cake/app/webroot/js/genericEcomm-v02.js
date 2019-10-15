function activeEcommButtonPiu(d) {
    var a = $(d).parent().attr("id");
    var c = a.substring(a.indexOf("-") + 1, a.lenght);
    var e = $("#qta-" + c).html();
    if (e == "") {
        e = 0
    }
    e = parseInt(e);
    var g = parseInt($("#qta_multipli-" + c).val());
    e = (e + (1 * g));
    var b = parseInt($("#qta_minima-" + c).val());
    if (e < b) {
        e = b
    }
    if (!validitationQta(c, e)) {
        return false
    }
    var f = $("#prezzo-" + c).val();
    prezzoNew = number_format((f * e), 2, ",", ".");
    $("#prezzoNew-" + c).html(prezzoNew + "&nbsp;&euro;");
    $("#qta-" + c).html(e);
    $("#qta-" + c).removeClass("qtaZero");
    $("#qta-" + c).addClass("qtaUno");
    $("#submitEcomm-" + c).attr("style", "opacity:1");
    $("#msgEcomm-" + c).attr("style", "opacity:1");
    $("#msgEcomm-" + c).html("");
    $("#submitEcomm-" + c).attr("src", app_img + "/apps/32x32/kfloppy.png");
    $("#submitEcomm-" + c).css("cursor", "pointer")
}

function activeEcommButtonMeno(d) {
    var a = $(d).parent().attr("id");
    var c = a.substring(a.indexOf("-") + 1, a.lenght);
    var e = $("#qta-" + c).html();
    if (e == 0) {
        return false
    }
    e = parseInt(e);
    var g = parseInt($("#qta_multipli-" + c).val());
    e = (e - (1 * g));
    var b = parseInt($("#qta_minima-" + c).val());
    if (e < b) {
        e = 0
    }
    if (!validitationQta(c, e)) {
        return false
    }
    var f = $("#prezzo-" + c).val();
    prezzoNew = number_format((f * e), 2, ",", ".");
    $("#prezzoNew-" + c).html(prezzoNew + "&nbsp;&euro;");
    $("#qta-" + c).html(e);
    if (e == 0) {
        $("#qta-" + c).removeClass("qtaUno");
        $("#qta-" + c).addClass("qtaZero")
    }
    $("#submitEcomm-" + c).attr("style", "opacity:1");
    $("#msgEcomm-" + c).attr("style", "opacity:1");
    $("#msgEcomm-" + c).html("");
    $("#submitEcomm-" + c).attr("src", app_img + "/apps/32x32/kfloppy.png");
    $("#submitEcomm-" + c).css("cursor", "pointer")
}

function activeEcommButtonPiuCartsValidation(e) {
    var g = $(e).parent().attr("id");
    var c = g.substring(g.indexOf("-") + 1, g.lenght);
    var i = $("#qta-" + c).html();
    if (i == "") {
        i = 0
    }
    i = parseInt(i);
    var f = parseInt($("#qta_multipli-" + c).val());
    i = (i + (1 * f));
    var a = parseInt($("#qta_minima-" + c).val());
    if (i < a) {
        i = a
    }
    if (!validitationQta(c, i)) {
        return false
    }
    var h = parseInt($("#differenza_da_ordinare-" + c).html());
    if (h < 0) h = 0;
    if (h == 0) {
        alert("Il collo è stato completato");
        return false
    }
    var d = $("#prezzo-" + c).val();
    prezzoNew = number_format((d * i), 2, ",", ".");
    $("#prezzoNew-" + c).html(prezzoNew + "&nbsp;&euro;");
    $("#qta-" + c).html(i);
    $("#qta-" + c).removeClass("qtaZero");
    $("#qta-" + c).addClass("qtaUno");
    var b = (1 * f);
    if (i <= a) {
        b = a
    }
    h = (h - b);
    $("#differenza_da_ordinare-" + c).html(h);
    if (h == 0) {
        $("#differenza_da_ordinare-" + c).removeClass("qtaEvidenza");
        $("#differenza_da_ordinare-" + c).addClass("qtaUno")
    } else {
        $("#differenza_da_ordinare-" + c).removeClass("qtaUno");
        $("#differenza_da_ordinare-" + c).addClass("qtaEvidenza")
    }
    $("#submitEcomm-" + c).attr("style", "opacity:1");
    $("#msgEcomm-" + c).attr("style", "opacity:1");
    $("#msgEcomm-" + c).html("");
    $("#submitEcomm-" + c).attr("src", app_img + "/apps/32x32/kfloppy.png");
    $("#submitEcomm-" + c).css("cursor", "pointer")
}

function activeEcommButtonMenoCartsValidation(e) {
    var g = $(e).parent().attr("id");
    var c = g.substring(g.indexOf("-") + 1, g.lenght);
    var i = $("#qta-" + c).html();
    if (i == 0) {
        return false
    }
    i = parseInt(i);
    var f = parseInt($("#qta_multipli-" + c).val());
    i = (i - (1 * f));
    var a = parseInt($("#qta_minima-" + c).val());
    if (i < a) {
        i = 0
    }
    if (!validitationQta(c, i)) {
        return false
    }
    var d = $("#prezzo-" + c).val();
    prezzoNew = number_format((d * i), 2, ",", ".");
    $("#prezzoNew-" + c).html(prezzoNew + "&nbsp;&euro;");
    $("#qta-" + c).html(i);
    if (i == 0) {
        $("#qta-" + c).removeClass("qtaUno");
        $("#qta-" + c).addClass("qtaZero")
    }
    var h = parseInt($("#differenza_da_ordinare-" + c).html());
    var b = (1 * f);
    if (i < a) {
        b = a
    }
    h = (h + b);
    $("#differenza_da_ordinare-" + c).html(h);
    if (h == 0) {
        $("#differenza_da_ordinare-" + c).removeClass("qtaEvidenza");
        $("#differenza_da_ordinare-" + c).addClass("qtaUno")
    } else {
        $("#differenza_da_ordinare-" + c).removeClass("qtaUno");
        $("#differenza_da_ordinare-" + c).addClass("qtaEvidenza")
    }
    $("#submitEcomm-" + c).attr("style", "opacity:1");
    $("#msgEcomm-" + c).attr("style", "opacity:1");
    $("#msgEcomm-" + c).html("");
    $("#submitEcomm-" + c).attr("src", app_img + "/apps/32x32/kfloppy.png");
    $("#submitEcomm-" + c).css("cursor", "pointer")
}

function managementCart(c, b, a) {
    $("#submitEcomm-" + c).attr("style", "opacity:1");
    $("#msgEcomm-" + c).attr("style", "opacity:1");
    $("#msgEcomm-" + c).html("");
    if (b == "OK") {
        $("#submitEcomm-" + c).attr("src", app_img + "/actions/32x32/bookmark.png");
        $("#submitEcomm-" + c).css("cursor", "default");
        $("#msgEcomm-" + c).html("Salvato!");
        $("#submitEcomm-" + c).delay(1000).animate({
            opacity: 0
        }, 1500);
        $("#msgEcomm-" + c).delay(1000).animate({
            opacity: 0
        }, 1500);
        if ($("#cart-short").length > 0) {
            viewCartShort()
        }
    } else {
        if (b == "DELETE") {
            $("#submitEcomm-" + c).attr("src", app_img + "/actions/32x32/blue_basket.png");
            $("#submitEcomm-" + c).css("cursor", "default");
            $("#msgEcomm-" + c).html("Cancellato!");
            $("#submitEcomm-" + c).delay(1000).animate({
                opacity: 0
            }, 1500);
            $("#msgEcomm-" + c).delay(1000).animate({
                opacity: 0
            }, 1500)
        } else {
            if (b == "ERRORE-STATO-N") {
                $("#submitEcomm-" + c).attr("src", app_img + "/blank32x32.png");
                $("#submitEcomm-" + c).css("cursor", "default");
                $("#msgEcomm-" + c).html("");
                alert(a)
            } else {
                if (b == "ERRORE-QTAMIN") {
                    $("#submitEcomm-" + c).attr("src", app_img + "/blank32x32.png");
                    $("#submitEcomm-" + c).css("cursor", "default");
                    $("#msgEcomm-" + c).html("");
                    alert(a)
                } else {
                    if (b == "ERRORE-QTAMAX") {
                        $("#submitEcomm-" + c).attr("src", app_img + "/blank32x32.png");
                        $("#submitEcomm-" + c).css("cursor", "default");
                        $("#msgEcomm-" + c).html("");
                        alert(a)
                    } else {
                        if (b == "ERRORE-QTAMAXORDER") {
                            $("#submitEcomm-" + c).attr("src", app_img + "/blank32x32.png");
                            $("#submitEcomm-" + c).css("cursor", "default");
                            $("#msgEcomm-" + c).html("");
                            alert(a)
                        } else {
                        if (b == "ERRORE-QTAMAXORDER-STOP") {
                            $("#submitEcomm-" + c).attr("src", app_img + "/blank32x32.png");
                            $("#submitEcomm-" + c).css("cursor", "default");
                            $("#msgEcomm-" + c).html("");
                            alert(a)
                        } else {
                            if (b == "ERRORE-LOCK-STOP") {
                                $("#submitEcomm-" + c).attr("src", app_img + "/blank32x32.png");
                                $("#submitEcomm-" + c).css("cursor", "default");
                                $("#msgEcomm-" + c).html("");
                                alert(a)
                            } else {
	                            if (b == "LIMIT-CASH") {
	                                $("#submitEcomm-" + c).attr("src", app_img + "/blank32x32.png");
	                                $("#submitEcomm-" + c).css("cursor", "default");
	                                $("#msgEcomm-" + c).html("");
	                                alert(a)
	                            } else {                            
	                                if (b == "OKIMPORTO") {
	                                    $("#submitEcomm-" + c).attr("src", app_img + "/actions/32x32/bookmark.png");
	                                    $("#submitEcomm-" + c).css("cursor", "default");
	                                    $("#msgEcomm-" + c).html("Salvato!");
	                                    $("#submitEcomm-" + c).delay(1000).animate({
	                                        opacity: 0
	                                    }, 1500);
	                                    $("#msgEcomm-" + c).delay(1000).animate({
	                                        opacity: 0
	                                    }, 1500)
	                                } else {
	                                    if (b == "NO") {
	                                        $("#submitEcomm-" + c).attr("src", app_img + "/apps/32x32/error.png");
	                                        $("#submitEcomm-" + c).css("cursor", "default");
	                                        $("#msgEcomm-" + c).html("Errore!")
	                                    }
	                                }
	                            }
                            }
                        }
                    }
                }
            }
        }
        }    
    }
}

function viewCalendar(b) {
    var a = "/?option=com_cake&controller=Deliveries&action=calendar_view&delivery_id=" + b + "&format=notmpl";
    $("#calendar_view").load(a).animate({
        opacity: 1
    }, 750);
    target = $("#calendar_view");
    $("html, body").animate({
        scrollTop: target.offset().top
    }, {
        duration: 500,
        easing: "swing"
    })
}

function validitationQta(b, qta) {
    var stato = $("#articleOrder_stato-" + b).val();
    var qta_massima = parseInt($("#qta_massima-" + b).val());
    var qta_massima_order = parseInt($("#qta_massima_order-" + b).val());
    var qta_multipli = parseInt($("#qta_multipli-" + b).val());
    var qta_cart = parseInt($("#qta_cart-" + b).val());
    var qta_prima_modifica = parseInt($("#qta_prima_modifica-" + b).val());
    if (qta_prima_modifica == null || isNaN(qta_prima_modifica)) {
        qta_prima_modifica = 0
    }
    if (stato == "LOCK") {
        if (qta > qta_prima_modifica) {
            alert("L'articolo è bloccato, non si possono aggiungere articoli.");
            qta = (qta - (1 * qta_multipli));
            $("#qta-" + b).html(qta);
            return false
        }
    }
    else {
        if (stato == "QTAMAXORDER") {
            if (qta > qta_prima_modifica) {
                alert("Raggiunta la quantità massima che si può ordinare.");
                qta = (qta - (1 * qta_multipli));
                $("#qta-" + b).html(qta);
                return false
            }
        }
    } 
    
    if (qta_massima_order > 0) {
        if (qta_massima_order > 0 && (qta_cart - qta_prima_modifica + qta) > qta_massima_order) {
            alert("Raggiunta la quantità massima che si può ordinare.");
            qta = (qta - (1 * qta_multipli));
            $("#qta-" + b).html(qta);
            return false
        }
    }
    
    if (qta_massima > 0) {
	    if (qta > qta_massima) {
	        alert("Raggiunta la quantità massima che un singolo gasista può ordinare.");
	        qta = (qta - (1 * qta_multipli));
	        $("#qta-" + b).html(qta);
	        return false
	    }
    }
    
    return true
}

function ecommRowsValidation(b, c) {
    var i = parseInt($("#qta-" + b).html());
    if (!validitationQta(b, i)) {
        return false
    }
    var f = parseInt($("#qta_cart-" + b).val());
    var a = parseInt($("#qta_minima-" + b).val());
    var e = parseInt($("#qta_massima_order-" + b).val());
    var d = parseInt($("#qta_multipli-" + b).val());
    var g = parseInt($("#qta_prima_modifica-" + b).val());
    if (i > 0 && (i < a)) {
        alert("La quantità minima che si può ordinare è " + a);
        return false
    }
    if (e > 0) {
        if (e == g) {
            justQTAMAXORDER = true
        } else {
            justQTAMAXORDER = false
        } if (i > g) {
            if (justQTAMAXORDER) {
                alert("Si è già raggiunto la quantità massima!");
                return false
            }
            if ((f - g + i) > e) {
                var h = (e - f);
                if (!confirm("Hai ordinato una quantità che supera la quantità di articolo disponibile\nDesideri continuare? Se SI, ti verrà assegnata la quantità di articolo disponibile: " + h + " pezzi.")) {
                    return false
                }
            }
        }
    }
    if ((i % d) > 0) {
        alert("Si possono solo fare acquisti multipli di " + d);
        return false
    }
    return true
}

function activeEcommRows(a) {
    $(a).mouseenter(function() {
        $(this).find(".buttonPiuMeno").css("display", "inline")
    });
    $(a).mouseleave(function() {
        if ($(this).find(".submitEcomm").attr("src") == app_img + "/apps/32x32/kfloppy.png") {
            $(this).find(".msgEcomm").html("Non hai salvato!");
            $(this).find(".buttonPiuMeno").css("display", "none")
        } else {
            $(this).find(".submitEcomm").attr("src", app_img + "/blank32x32.png");
            $(this).find(".msgEcomm").html("");
            $(this).find(".buttonPiuMeno").css("display", "none")
        }
    });
    $(a).find(".buttonPiu").click(function() {
        activeEcommButtonPiu(this)
    });
    $(a).find(".buttonMeno").click(function() {
        activeEcommButtonMeno(this)
    });
    $(a).find(".buttonPiuCartsValidation").click(function() {
        activeEcommButtonPiuCartsValidation(this)
    });
    $(a).find(".buttonMenoCartsValidation").click(function() {
        activeEcommButtonMenoCartsValidation(this)
    })
}

function viewCalendar(b) {
    var a = "/?option=com_cake&controller=Deliveries&action=calendar_view&delivery_id=" + b + "&format=notmpl";
    $("#calendar_view").load(a).animate({
        opacity: 1
    }, 750);
    target = $("#calendar_view");
    $("html, body").animate({
        scrollTop: target.offset().top
    }, {
        duration: 500,
        easing: "swing"
    })
}

function settingEcommTotale(classItem, classTotale) {
	/*console.log('genericEcomm::settingEcommTotale() - cerco class '+classItem+' e setto il totale '+classTotale+' ');*/
	var prezzoNew = parseFloat(0.00);
	$('.'+classItem).each(function( index ) {
		var obj = $(this);
		var objPrezzoNew = $(obj).attr('data-attr-'+classItem);
		objPrezzoNew = numberToJs(objPrezzoNew);
		if(objPrezzoNew>0) {		
			prezzoNew = (parseFloat(prezzoNew) + parseFloat(objPrezzoNew)); 
			prezzoNew = parseFloat(prezzoNew).toFixed(2);
			/*console.log(classItem+" "+prezzoNew+" - obj"+classItem+" "+objPrezzoNew);*/
		}
	});
	/*console.log(prezzoNew);*/
	prezzoNew = number_format(prezzoNew,2,',','.');
	
	$('.'+classTotale).html(prezzoNew+' €');		
}