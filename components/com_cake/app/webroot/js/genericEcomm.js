function activeEcommButtonPiu(d) {
    var a = jQuery(d).parent().attr("id");
    var c = a.substring(a.indexOf("-") + 1, a.lenght);
    var e = jQuery("#qta-" + c).html();
    if (e == "") {
        e = 0
    }
    e = parseInt(e);
    var g = parseInt(jQuery("#qta_multipli-" + c).val());
    e = (e + (1 * g));
    var b = parseInt(jQuery("#qta_minima-" + c).val());
    if (e < b) {
        e = b
    }
    if (!validitationQta(c, e)) {
        return false
    }
    var f = jQuery("#prezzo-" + c).val();
    prezzoNew = number_format((f * e), 2, ",", ".");
    jQuery("#prezzoNew-" + c).html(prezzoNew + "&nbsp;&euro;");
    jQuery("#qta-" + c).html(e);
    jQuery("#qta-" + c).removeClass("qtaZero");
    jQuery("#qta-" + c).addClass("qtaUno");
    jQuery("#submitEcomm-" + c).attr("style", "opacity:1");
    jQuery("#msgEcomm-" + c).attr("style", "opacity:1");
    jQuery("#msgEcomm-" + c).html("");
    jQuery("#submitEcomm-" + c).attr("src", app_img + "/apps/32x32/kfloppy.png");
    jQuery("#submitEcomm-" + c).css("cursor", "pointer")
}

function activeEcommButtonMeno(d) {
    var a = jQuery(d).parent().attr("id");
    var c = a.substring(a.indexOf("-") + 1, a.lenght);
    var e = jQuery("#qta-" + c).html();
    if (e == 0) {
        return false
    }
    e = parseInt(e);
    var g = parseInt(jQuery("#qta_multipli-" + c).val());
    e = (e - (1 * g));
    var b = parseInt(jQuery("#qta_minima-" + c).val());
    if (e < b) {
        e = 0
    }
    if (!validitationQta(c, e)) {
        return false
    }
    var f = jQuery("#prezzo-" + c).val();
    prezzoNew = number_format((f * e), 2, ",", ".");
    jQuery("#prezzoNew-" + c).html(prezzoNew + "&nbsp;&euro;");
    jQuery("#qta-" + c).html(e);
    if (e == 0) {
        jQuery("#qta-" + c).removeClass("qtaUno");
        jQuery("#qta-" + c).addClass("qtaZero")
    }
    jQuery("#submitEcomm-" + c).attr("style", "opacity:1");
    jQuery("#msgEcomm-" + c).attr("style", "opacity:1");
    jQuery("#msgEcomm-" + c).html("");
    jQuery("#submitEcomm-" + c).attr("src", app_img + "/apps/32x32/kfloppy.png");
    jQuery("#submitEcomm-" + c).css("cursor", "pointer")
}

function activeEcommButtonPiuCartsValidation(e) {
    var g = jQuery(e).parent().attr("id");
    var c = g.substring(g.indexOf("-") + 1, g.lenght);
    var i = jQuery("#qta-" + c).html();
    if (i == "") {
        i = 0
    }
    i = parseInt(i);
    var f = parseInt(jQuery("#qta_multipli-" + c).val());
    i = (i + (1 * f));
    var a = parseInt(jQuery("#qta_minima-" + c).val());
    if (i < a) {
        i = a
    }
    if (!validitationQta(c, i)) {
        return false
    }
    var h = parseInt(jQuery("#differenza_da_ordinare-" + c).html());
    if (h < 0) h = 0;
    if (h == 0) {
        alert("Il collo è stato completato");
        return false
    }
    var d = jQuery("#prezzo-" + c).val();
    prezzoNew = number_format((d * i), 2, ",", ".");
    jQuery("#prezzoNew-" + c).html(prezzoNew + "&nbsp;&euro;");
    jQuery("#qta-" + c).html(i);
    jQuery("#qta-" + c).removeClass("qtaZero");
    jQuery("#qta-" + c).addClass("qtaUno");
    var b = (1 * f);
    if (i <= a) {
        b = a
    }
    h = (h - b);
    jQuery("#differenza_da_ordinare-" + c).html(h);
    if (h == 0) {
        jQuery("#differenza_da_ordinare-" + c).removeClass("qtaEvidenza");
        jQuery("#differenza_da_ordinare-" + c).addClass("qtaUno")
    } else {
        jQuery("#differenza_da_ordinare-" + c).removeClass("qtaUno");
        jQuery("#differenza_da_ordinare-" + c).addClass("qtaEvidenza")
    }
    jQuery("#submitEcomm-" + c).attr("style", "opacity:1");
    jQuery("#msgEcomm-" + c).attr("style", "opacity:1");
    jQuery("#msgEcomm-" + c).html("");
    jQuery("#submitEcomm-" + c).attr("src", app_img + "/apps/32x32/kfloppy.png");
    jQuery("#submitEcomm-" + c).css("cursor", "pointer")
}

function activeEcommButtonMenoCartsValidation(e) {
    var g = jQuery(e).parent().attr("id");
    var c = g.substring(g.indexOf("-") + 1, g.lenght);
    var i = jQuery("#qta-" + c).html();
    if (i == 0) {
        return false
    }
    i = parseInt(i);
    var f = parseInt(jQuery("#qta_multipli-" + c).val());
    i = (i - (1 * f));
    var a = parseInt(jQuery("#qta_minima-" + c).val());
    if (i < a) {
        i = 0
    }
    if (!validitationQta(c, i)) {
        return false
    }
    var d = jQuery("#prezzo-" + c).val();
    prezzoNew = number_format((d * i), 2, ",", ".");
    jQuery("#prezzoNew-" + c).html(prezzoNew + "&nbsp;&euro;");
    jQuery("#qta-" + c).html(i);
    if (i == 0) {
        jQuery("#qta-" + c).removeClass("qtaUno");
        jQuery("#qta-" + c).addClass("qtaZero")
    }
    var h = parseInt(jQuery("#differenza_da_ordinare-" + c).html());
    var b = (1 * f);
    if (i < a) {
        b = a
    }
    h = (h + b);
    jQuery("#differenza_da_ordinare-" + c).html(h);
    if (h == 0) {
        jQuery("#differenza_da_ordinare-" + c).removeClass("qtaEvidenza");
        jQuery("#differenza_da_ordinare-" + c).addClass("qtaUno")
    } else {
        jQuery("#differenza_da_ordinare-" + c).removeClass("qtaUno");
        jQuery("#differenza_da_ordinare-" + c).addClass("qtaEvidenza")
    }
    jQuery("#submitEcomm-" + c).attr("style", "opacity:1");
    jQuery("#msgEcomm-" + c).attr("style", "opacity:1");
    jQuery("#msgEcomm-" + c).html("");
    jQuery("#submitEcomm-" + c).attr("src", app_img + "/apps/32x32/kfloppy.png");
    jQuery("#submitEcomm-" + c).css("cursor", "pointer")
}

function managementCart(c, b, a) {
    jQuery("#submitEcomm-" + c).attr("style", "opacity:1");
    jQuery("#msgEcomm-" + c).attr("style", "opacity:1");
    jQuery("#msgEcomm-" + c).html("");
    if (b == "OK") {
        jQuery("#submitEcomm-" + c).attr("src", app_img + "/actions/32x32/bookmark.png");
        jQuery("#submitEcomm-" + c).css("cursor", "default");
        jQuery("#msgEcomm-" + c).html("Salvato!");
        jQuery("#submitEcomm-" + c).delay(1000).animate({
            opacity: 0
        }, 1500);
        jQuery("#msgEcomm-" + c).delay(1000).animate({
            opacity: 0
        }, 1500);
        if (jQuery("#cart-short").length > 0) {
            viewCartShort()
        }
    } else {
        if (b == "DELETE") {
            jQuery("#submitEcomm-" + c).attr("src", app_img + "/actions/32x32/blue_basket.png");
            jQuery("#submitEcomm-" + c).css("cursor", "default");
            jQuery("#msgEcomm-" + c).html("Cancellato!");
            jQuery("#submitEcomm-" + c).delay(1000).animate({
                opacity: 0
            }, 1500);
            jQuery("#msgEcomm-" + c).delay(1000).animate({
                opacity: 0
            }, 1500)
        } else {
            if (b == "ERRORE-STATO-N") {
                jQuery("#submitEcomm-" + c).attr("src", app_img + "/blank32x32.png");
                jQuery("#submitEcomm-" + c).css("cursor", "default");
                jQuery("#msgEcomm-" + c).html("");
                alert(a)
            } else {
                if (b == "ERRORE-QTAMIN") {
                    jQuery("#submitEcomm-" + c).attr("src", app_img + "/blank32x32.png");
                    jQuery("#submitEcomm-" + c).css("cursor", "default");
                    jQuery("#msgEcomm-" + c).html("");
                    alert(a)
                } else {
                    if (b == "ERRORE-QTAMAX") {
                        jQuery("#submitEcomm-" + c).attr("src", app_img + "/blank32x32.png");
                        jQuery("#submitEcomm-" + c).css("cursor", "default");
                        jQuery("#msgEcomm-" + c).html("");
                        alert(a)
                    } else {
                        if (b == "ERRORE-QTAMAXORDER") {
                            jQuery("#submitEcomm-" + c).attr("src", app_img + "/blank32x32.png");
                            jQuery("#submitEcomm-" + c).css("cursor", "default");
                            jQuery("#msgEcomm-" + c).html("");
                            alert(a)
                        } else {
                        if (b == "ERRORE-QTAMAXORDER-STOP") {
                            jQuery("#submitEcomm-" + c).attr("src", app_img + "/blank32x32.png");
                            jQuery("#submitEcomm-" + c).css("cursor", "default");
                            jQuery("#msgEcomm-" + c).html("");
                            alert(a)
                        } else {
                            if (b == "ERRORE-LOCK-STOP") {
                                jQuery("#submitEcomm-" + c).attr("src", app_img + "/blank32x32.png");
                                jQuery("#submitEcomm-" + c).css("cursor", "default");
                                jQuery("#msgEcomm-" + c).html("");
                                alert(a)
                            } else {
                                if (b == "OKIMPORTO") {
                                    jQuery("#submitEcomm-" + c).attr("src", app_img + "/actions/32x32/bookmark.png");
                                    jQuery("#submitEcomm-" + c).css("cursor", "default");
                                    jQuery("#msgEcomm-" + c).html("Salvato!");
                                    jQuery("#submitEcomm-" + c).delay(1000).animate({
                                        opacity: 0
                                    }, 1500);
                                    jQuery("#msgEcomm-" + c).delay(1000).animate({
                                        opacity: 0
                                    }, 1500)
                                } else {
                                    if (b == "NO") {
                                        jQuery("#submitEcomm-" + c).attr("src", app_img + "/apps/32x32/error.png");
                                        jQuery("#submitEcomm-" + c).css("cursor", "default");
                                        jQuery("#msgEcomm-" + c).html("Errore!")
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
    jQuery("#calendar_view").load(a).animate({
        opacity: 1
    }, 750);
    target = jQuery("#calendar_view");
    jQuery("html, body").animate({
        scrollTop: target.offset().top
    }, {
        duration: 500,
        easing: "swing"
    })
}

function validitationQta(b, qta) {
    var stato = jQuery("#articleOrder_stato-" + b).val();
    var qta_massima = parseInt(jQuery("#qta_massima-" + b).val());
    var qta_massima_order = parseInt(jQuery("#qta_massima_order-" + b).val());
    var qta_multipli = parseInt(jQuery("#qta_multipli-" + b).val());
    var qta_cart = parseInt(jQuery("#qta_cart-" + b).val());
    var qta_prima_modifica = parseInt(jQuery("#qta_prima_modifica-" + b).val());
    if (qta_prima_modifica == null || isNaN(qta_prima_modifica)) {
        qta_prima_modifica = 0
    }
    if (stato == "LOCK") {
        if (qta > qta_prima_modifica) {
            alert("L'articolo è bloccato, non si possono aggiungere articoli.");
            qta = (qta - (1 * qta_multipli));
            jQuery("#qta-" + b).html(qta);
            return false
        }
    }
    else {
        if (stato == "QTAMAXORDER") {
            if (qta > qta_prima_modifica) {
                alert("Raggiunta la quantità massima che si può ordinare.");
                qta = (qta - (1 * qta_multipli));
                jQuery("#qta-" + b).html(qta);
                return false
            }
        }
    } 
    
    if (qta_massima_order > 0) {
        if (qta_massima_order > 0 && (qta_cart - qta_prima_modifica + qta) > qta_massima_order) {
            alert("Raggiunta la quantità massima che si può ordinare.");
            qta = (qta - (1 * qta_multipli));
            jQuery("#qta-" + b).html(qta);
            return false
        }
    }
    
    if (qta_massima > 0) {
	    if (qta > qta_massima) {
	        alert("Raggiunta la quantità massima che un singolo gasista può ordinare.");
	        qta = (qta - (1 * qta_multipli));
	        jQuery("#qta-" + b).html(qta);
	        return false
	    }
    }
    
    return true
}

function ecommRowsValidation(b, c) {
    var i = parseInt(jQuery("#qta-" + b).html());
    if (!validitationQta(b, i)) {
        return false
    }
    var f = parseInt(jQuery("#qta_cart-" + b).val());
    var a = parseInt(jQuery("#qta_minima-" + b).val());
    var e = parseInt(jQuery("#qta_massima_order-" + b).val());
    var d = parseInt(jQuery("#qta_multipli-" + b).val());
    var g = parseInt(jQuery("#qta_prima_modifica-" + b).val());
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
    jQuery(a).mouseenter(function() {
        jQuery(this).find(".buttonPiuMeno").css("display", "inline")
    });
    jQuery(a).mouseleave(function() {
        if (jQuery(this).find(".submitEcomm").attr("src") == app_img + "/apps/32x32/kfloppy.png") {
            jQuery(this).find(".msgEcomm").html("Non hai salvato!");
            jQuery(this).find(".buttonPiuMeno").css("display", "none")
        } else {
            jQuery(this).find(".submitEcomm").attr("src", app_img + "/blank32x32.png");
            jQuery(this).find(".msgEcomm").html("");
            jQuery(this).find(".buttonPiuMeno").css("display", "none")
        }
    });
    jQuery(a).find(".buttonPiu").click(function() {
        activeEcommButtonPiu(this)
    });
    jQuery(a).find(".buttonMeno").click(function() {
        activeEcommButtonMeno(this)
    });
    jQuery(a).find(".buttonPiuCartsValidation").click(function() {
        activeEcommButtonPiuCartsValidation(this)
    });
    jQuery(a).find(".buttonMenoCartsValidation").click(function() {
        activeEcommButtonMenoCartsValidation(this)
    })
}

function viewCalendar(b) {
    var a = "/?option=com_cake&controller=Deliveries&action=calendar_view&delivery_id=" + b + "&format=notmpl";
    jQuery("#calendar_view").load(a).animate({
        opacity: 1
    }, 750);
    target = jQuery("#calendar_view");
    jQuery("html, body").animate({
        scrollTop: target.offset().top
    }, {
        duration: 500,
        easing: "swing"
    })
}