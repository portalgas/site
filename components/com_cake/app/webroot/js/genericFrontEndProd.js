function viewCartShort() {
    var a = "/?option=com_cake&controller=ProdCarts&action=cart_to_user_preview&format=notmpl";
    jQuery("#cart-short").load(a).animate({
        opacity: 1,
        marginLeft: "+=275"
    }, 750);
    jQuery(".lastCart").animate({
        backgroundColor: "#fff"
    }, 350);
    jQuery("#cart-short").delay(2000).animate({
        opacity: 0,
        marginLeft: "-=275"
    }, 1500)
}

function activeSubmitEcomm(a) {
    jQuery(a).find(".submitEcomm").click(function() {
        var d = jQuery(this).attr("id");
        var f = d.substring(d.indexOf("-") + 1, d.lenght);
        jQuery(".trView").hide();
        jQuery(".actionTrView").removeClass("closeTrView");
        jQuery(".actionTrView").addClass("openTrView");
        jQuery("#submitEcomm-" + f).attr("src", app_img + "/ajax-loader.gif");
        var g = jQuery("#prod_delivery_id-" + f).val();
        var e = jQuery("#article_organization_id-" + f).val();
        var h = jQuery("#article_id-" + f).val();
        var c = jQuery("#qta-" + f).html();
        if (!ecommRowsValidation(f, backOffice = false)) {
            return false
        }
        if (jQuery("#prod_delivery_type_draw_" + g).val() == "COMPLETE") {
            action = "managementCartComplete"
        } else {
            action = "managementCartSimple"
        }
        var b = "";
        b = "/?option=com_cake&controller=AjaxProdCarts&action=" + action + "&row_id=" + f + "&prod_delivery_id=" + g + "&article_organization_id=" + e + "&article_id=" + h + "&qta=" + c + "&format=notmpl";
        jQuery.ajax({
            type: "GET",
            url: b,
            data: "",
            success: function(i) {
                jQuery("#row-" + f).html(i)
            },
            error: function(i, k, j) {
                jQuery("#msgEcomm-" + f).html(k);
                jQuery("#submitEcomm-" + f).attr("src", app_img + "/blank32x32.png")
            }
        });
        return false
    })
};