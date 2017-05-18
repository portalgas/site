<?php
echo $this->Html->script('generic-v02.min', array('date' => '2017feb'));
echo $this->Html->script('genericBackOffice.min', array('date' => '2015may'));
//echo $this->Html->script('jquery/chosen.jquery.min'); // bootstrap 2.x
//echo $this->Html->css('chosen.min');
echo $this->Html->script('bootstrap-select.min');
echo $this->Html->css('bootstrap-select.min');

if($user->organization['Organization']['type']=='PROD') {
	echo $this->Html->script('genericEcommProd.min', array('date' => '2015apr'));
	echo $this->Html->script('genericBackOfficeProd.min', array('date' => '2015apr'));
}
else {
	/*
	 * vale per tutti i GAs e i ProdGasSuppliers
	 */
	echo $this->Html->script('genericEcomm.min', array('date' => '2015apr'));
	echo $this->Html->script('genericBackOfficeGas.min', array('date' => '2015apr'));	
}



if(Configure::read('LayoutBootstrap')) {
?>
<!-- start bootstrap start -->
<!-- start bootstrap start -->
<!-- start bootstrap start -->
<link rel="stylesheet" href="templates/bluestork/css/bootstrap.min.css" type="text/css" />
<link rel="stylesheet" href="templates/bluestork/css/font-awesome.min.css" type="text/css" />
<script src="templates/bluestork/js/bootstrap.min.js" type="text/javascript"></script>
<script type="text/javascript">
function viewOrderSottoMenuBootstrap(order_id) {

	if(order_id==null)  return;
	
	/*
	 * in Orders::index ho + sottoMenu
	 * quando e' laterale ne ho solo uno e ID cambia se cambio l'ordine dal menu a tendina (vecchia gestione) 
	 */
	if ($('#order-sotto-menu-'+order_id).length==0)
		idSelector = '.order-sotto-menu-unique';
	else
		idSelector = '#order-sotto-menu-'+order_id;
	
	$.ajax({
		type: "get", 
		url: "/administrator/index.php?option=com_cake&controller=Orders&action=sotto_menu_bootstrap&order_id="+order_id+"&format=notmpl",
		data: "",
		success: function(response) {
			$(idSelector).html(response);
		},
		error:function (XMLHttpRequest, textStatus, errorThrown) {
			$(idSelector).html(textStatus);
		}
	});
	return;
}
function apriPopUpBootstrap(url) {

	var modalId = 'tmpModal';
	var modalSize = 'lg';
	var modalHeader = '';
	
	var html = '';

	html =  '<div class="modal fade" id="'+modalId+'" role="dialog">';
	html += '<div class="modal-dialog modal-'+modalSize+'">';
	html += '<div class="modal-content">';
	html += '<div class="modal-header">'; 
	html += '<button type="button" class="close" data-dismiss="modal">&times;</button>';
	html += '<h4 class="modal-title">'+modalHeader+'</h4>'; // msg esito
	html += '</div>';
	html += '<div class="modal-body">';
	html += '</div>';
	html += '<div class="modal-footer">';
	html += '<button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">Chiudi</button>'; 
	html += '</div>'; 
	html += '</div>';
	html += '</div>';
	html += '</div>'; 
	
	$(html).appendTo('body');
	$('#'+modalId).modal('show');
	
	$('#'+modalId).on("shown.bs.modal", function () {
		/*console.log("event show.bs.modal");*/

		$('#'+modalId).find('.modal-body').css('background', 'url("'+app_img+'/ajax-loader.gif") no-repeat scroll center center transparent');

		$.ajax({
			type: "GET",
			url: url,
			dataType: "html",
		})
		.fail(function () {
			/*console.log("Errore di sistema! url chiamato "+url);*/
		})
		.done(function (response) {
			$('#'+modalId).find(".modal-body").css("background", "none repeat scroll 0 0 transparent");
			$('#'+modalId).find(".modal-body").html(response);
			/*console.log("Chiamata avvenuta con successo");*/ 
		});	
	});
	
	$('#'+modalId).on("hide.bs.modal", function () {
		/*console.log("event hide.bs.modal");*/

		$('#'+modalId).find(".modal-header").html("");            
		$('#'+modalId).find(".modal-body").html("");

		$('#'+modalId).detach();                   
	});
}

$(document).ready(function() {

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
});
</script>
<!-- end bootstrap end -->
<!-- end bootstrap end -->
<!-- end bootstrap end -->
<?php
} // end Configure::read('LayoutBootstrap')
?>
	<style type="text/css">
	#toolbar-box { /* in administrator box del titolo e menu */
		display: none;
	}		
	</style>
		
	<script type="text/javascript">
		//<![CDATA[
		var app_img = "<?php echo Configure::read('App.img.cake');?>";

		$(function() {
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
		});
					
	//]]>
	</script>
				
	<div class="cakeContainer">
		
		<?php echo $this->Session->flash(); ?>

		<?php echo $this->fetch('content'); ?>

		<div id="footer">
			<?php
			if(Configure::read('developer.mode')) echo $this->element('boxMsg',array('class_msg' => 'notice', 'msg' => __('developer.mode'))); 
			?>	
		</div>
			
		<div id="help">
			<div class="logo">Manuali</div>
		</div>
			
	</div>
	<?php echo $this->element('sql_dump');?>