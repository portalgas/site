<?php $tot_orders = count($results['Order']);$tot_generics = count($results['PaymentsGeneric']);?><div class="box-open-close">	<div class="barra-open-close close" id="detailsLabel">		Dettaglio: <?php 					if($tot_orders>0) 						echo 'Ordini associati ('.$tot_orders.')';					else 						echo 'Nessun ordine associato';												if($tot_generics>0)
						echo ' - Voci di spesa associate ('.$tot_generics.')';									?>	</div>	<div id="detailsList" style="display:none;min-height:50px;">	</div></div><br /><?phpif($tot_orders>0 || $tot_generics>0) {?>	<script type="text/javascript">function gestDetails() {	if(jQuery('#detailsList').css('display')=='none')  {		jQuery('#detailsLabel').removeClass('close');		jQuery('#detailsLabel').addClass('open');		jQuery('#detailsList').show();		jQuery('#detailsList').html('');		jQuery('#detailsList').css('background', 'url("<?php echo Configure::read('App.server').Configure::read('App.img.cake');?>/ajax-loader.gif") no-repeat scroll center 0 transparent');				var url = '';		url = "/administrator/index.php?option=com_cake&controller=Ajax&action=view_request_payment&id=<?php echo $results['RequestPayment']['id'];?>&format=notmpl";		jQuery.ajax({			type: "GET",			url: url,			data: "",			success: function(response){				 jQuery('#detailsList').css('background', 'none repeat scroll 0 0 transparent');				 jQuery('#detailsList').html(response);			},			error:function (XMLHttpRequest, textStatus, errorThrown) {				jQuery('#detailsList').css('background', 'none repeat scroll 0 0 transparent');				jQuery('#detailsList').html(textStatus);			}		});		return false;	}		else {		jQuery('#detailsLabel').removeClass('open');		jQuery('#detailsLabel').addClass('close');		jQuery('#detailsList').hide('slow');	}	}jQuery(document).ready(function() {	jQuery('#detailsLabel').click(function() {		gestDetails();	});	<?php 	if($open_details=='Y')		echo "gestDetails();";	?>});</script><?php}?>