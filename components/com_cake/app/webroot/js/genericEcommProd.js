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
    })
}