function viewDesOrderSottoMenu(des_order_id, position_img) {
	if(des_order_id==null)  return;
	
	if(position_img==null) position_img = 'bgLeft'; 
	
	/*
	 * in DesOrders::index ho + sottoMenu
	 * quando e' laterale ne ho solo uno e ID cambia se cambio l'ordine dal menu a tendina (vecchia gestione) 
	 */
	if (jQuery('#des-order-sotto-menu-'+des_order_id).length==0)
		idSelector = '.des-order-sotto-menu-unique';
	else
		idSelector = '#des-order-sotto-menu-'+des_order_id;
	
	jQuery(idSelector).css('min-height', '35px');
	jQuery(idSelector).css('background', 'url("'+app_img+'/ajax-loader.gif") no-repeat scroll center 0 transparent');
	
	jQuery.ajax({
		type: "get", 
		url: "/administrator/index.php?option=com_cake&controller=DesOrders&action=sotto_menu&des_order_id="+des_order_id+"&position_img="+position_img+"&format=notmpl",
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