<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('Cassiere home'),array('controller'=>'Cassiere','action'=>'home', null, 'delivery_id='.$delivery_id));
$this->Html->addCrumb(__('Export/Gest Docs to delivery'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<script type="text/javascript">
var debugLocal = false;
var delivery_id = <?php echo $delivery_id; // se ho Submit e' valorizzato ?>;
var user_id = <?php echo $user_id; // se ho Submit e' valorizzato ?>;

function choiceDeliveryCassiere() {
	var div_contenitore = 'deliveries';
	delivery_id = $('#delivery_id').val();
	if(debugLocal) alert("choiceDeliveryCassiere - div_contenitore "+div_contenitore+", delivery_id "+delivery_id);
	if(delivery_id=='') {
		showHideBox(div_contenitore,call_child=false);
		return;
	}
	
	showHideBox(div_contenitore,call_child=true);
	AjaxCallToUsersDeliveryList(delivery_id); /* chiamata Ajax per elenco users della consegna */	
}
function choiceUser() {
	var div_contenitore = $('#user_id').parent().parent().parent().attr('id');  /* users-result */
	if (div_contenitore === undefined) 
		div_contenitore = 'users-result';
	var user_id = $('#user_id').val();
	if(debugLocal) alert("choiceUser - div_contenitore "+div_contenitore+", user_id "+user_id);
	if(user_id=='') {
		showHideBox(div_contenitore,call_child=false);
		return;
	}

	if(user_id=='ALL') {
		var div_contenitore = 'doc-options';
		$('#user-anagrafica').html('');
		$('#user-cash').html('');
		$('#user-anagrafica').hide();
		$('#user-cash').hide();
		$('#doc-preview').html('');
		$('#doc-preview').hide('');
		
		$('#doc-options').html('');
		$('#doc-options').css('display', 'block');
		$('#doc-options').css('min-height', '35px');
		$('#doc-options').css('background', 'url('+app_img+'/ajax-loader.gif) no-repeat scroll center 0 transparent');
		AjaxCallToDocOptions();   /* chiamata Ajax per optioni formato documento (csv, pdf) */
	}
	else {
		$('#doc-options').html('');
		$('#doc-options').hide();
		showHideBox(div_contenitore,call_child=true);
		AjaxCallToUserAnagrafica(user_id); /* chiamata Ajax per l'anagrafica utente se user_id == ALL disabilito l'opzione */	
	}
}
function choiceUserAnagrafica() {
	var div_contenitore = 'user-anagrafica';
	var user_id = $('#user_id').val();
	var delivery_id = $('#delivery_id').val();
	if(debugLocal) alert("choiceUserAnagrafica - div_contenitore "+div_contenitore+", user_id "+user_id);
	if(user_id=='' || delivery_id=='') {
		showHideBox(div_contenitore,call_child=false);
		return;
	}
	
	showHideBox(div_contenitore,call_child=true);
	AjaxCallToUserCash(delivery_id, user_id); 	

}
function choiceUserCash() {
	var div_contenitore = 'user-cash';
	var user_id = $('#user_id').val();
	if(debugLocal) alert("choiceUserCash - div_contenitore "+div_contenitore+", user_id "+user_id);
	if(user_id=='') {
		showHideBox(div_contenitore,call_child=false);
		return;
	}
	
	showHideBox(div_contenitore,call_child=true);
	AjaxCallToDocOptions();   /* chiamata Ajax per optioni formato documento (csv, pdf) */
}

function choiceDocOptions() {
	var div_contenitore = 'doc-options';
	
	showHideBox(div_contenitore,call_child=true);
	AjaxCallToDocPrint(); /* chiamata Ajax per tasto print */	
}	
function choiceDocPrint() {
	var div_contenitore = 'doc-print';
	
	showHideBox(div_contenitore,call_child=true);
	AjaxCallToDocPreview(); /* chiamata Ajax per anteprima documento */	
}

	


/*
 * chiamata Ajax per elenco utenti di una consegna
 */
function AjaxCallToUsersDeliveryList(delivery_id) {
		
	var url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_users_delivery_list&delivery_id="+delivery_id+'&user_id='+user_id+'&format=notmpl';
	var idDivTarget = 'users-result';
	ajaxCallBox(url, idDivTarget);
}
/*
 *  chiamata Ajax per anagrafica utente
 *  			se user_id == ALL disabilito l'opzione 
 */
function AjaxCallToUserAnagrafica(user_id) {
	var order_id = 0;
	var url = '/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_user_anagrafica&delivery_id='+delivery_id+'&order_id='+order_id+'&user_id='+user_id+'&call=cassiereDeliveryDocsExport&format=notmpl';
	var idDivTarget = 'user-anagrafica';
	ajaxCallBox(url, idDivTarget);
}

/*
 *  chiamata Ajax per la gestione della Cassa dell'utente
 *  			solo se scelto uno user
 */
function AjaxCallToUserCash(delivery_id, user_id) {
	url = '/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_user_cash&delivery_id='+delivery_id+'&user_id='+user_id+'&format=notmpl';
	var idDivTarget = 'user-cash';
	ajaxCallBox(url, idDivTarget);
}

/*
 * chiamata Ajax il formato del doc
 */
function AjaxCallToDocOptions() {	
	var user_id = $('#user_id').val(); 
	var url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_doc_options_cassiere&user_id="+user_id+"&format=notmpl";
	var idDivTarget = 'doc-options';
	ajaxCallBox(url, idDivTarget);
}
/*
 * chiamata Ajax per tasto print
 */
function AjaxCallToDocPrint() {
	var doc_options = $("input[name='doc_options']:checked").val();
	
	var url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_doc_print_cassiere&doc_options="+doc_options+"&format=notmpl";
	var idDivTarget = 'doc-print';
	ajaxCallBox(url, idDivTarget);
}
/*
 * chiamata Ajax per anteprima documento
 */
function AjaxCallToDocPreview() {
	var delivery_id = $('#delivery_id').val(); 
	var user_id = $('#user_id').val(); 
	var doc_options = $("input[name='doc_options']:checked").val();
	
	if(delivery_id =='' || doc_options=='') return;
	
	if(user_id=='ALL') {
	
		$('#doc-preview').css('display','block');

		if(doc_options=='to-delivery-cassiere-users-all-split') {
		
			$("#user_id > option").each(function() {
    			 /* console.log(this.text + ' ' + this.value); */
				
				var user_id = this.value;
    			if(user_id!='' && user_id!='ALL') {
				// if(user_id==827) {	
					var url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToCassiere&delivery_id='+delivery_id+'&user_id='+user_id+'&doc_options='+doc_options+'&doc_formato=PREVIEW&format=notmpl';
				
					var idDivTarget = 'doc-preview-user-'+user_id;
					/* console.log('idDivTarget ' + idDivTarget); */ 
					$('#doc-preview').append('<div class="doc-preview-user" id="'+idDivTarget+'"></div>');
					ajaxCallBox(url, idDivTarget);
		
    			}
			});
		
		}
   		else
    	if(doc_options=='to-delivery-cassiere-users-all') {
    		url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToCassiereAllDelivery&delivery_id='+delivery_id+'&doc_options='+doc_options+'&doc_formato=PREVIEW&format=notmpl';
    		var idDivTarget = 'doc-preview';
    		ajaxCallBox(url, idDivTarget); 
    	}
   		else
    	if(doc_options=='to-delivery-cassiere-users-compact-all') {
    		url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToCassiereAllDelivery&delivery_id='+delivery_id+'&doc_options='+doc_options+'&doc_formato=PREVIEW&format=notmpl';
    		var idDivTarget = 'doc-preview';
    		ajaxCallBox(url, idDivTarget); 
    	}
		else
		if(doc_options=='to-lists-suppliers-cassiere') {
			url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToCassiereListOrders&delivery_id='+delivery_id+'&doc_options='+doc_options+'&doc_formato=PREVIEW&format=notmpl';
    		var idDivTarget = 'doc-preview';
    		ajaxCallBox(url, idDivTarget); 
		}
		else
		if(doc_options=='to-lists-orders-cassiere') {
			url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToCassiereListOrders&delivery_id='+delivery_id+'&doc_options='+doc_options+'&doc_formato=PREVIEW&format=notmpl';
    		var idDivTarget = 'doc-preview';
    		ajaxCallBox(url, idDivTarget); 
		}	
		else
		if(doc_options=='to-list-users-delivery-cassiere') {
			url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToCassiereListUsersDelivery&delivery_id='+delivery_id+'&doc_options='+doc_options+'&doc_formato=PREVIEW&format=notmpl';
    		var idDivTarget = 'doc-preview';
    		ajaxCallBox(url, idDivTarget); 
		}		
					
	}
	else {
		$('.doc-preview-user').html('');
		$('.doc-preview-user').hide();
		
		var url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToCassiere&delivery_id='+delivery_id+'&user_id='+user_id+'&doc_options='+doc_options+'&doc_formato=PREVIEW&format=notmpl';
		var idDivTarget = 'doc-preview';
		ajaxCallBox(url, idDivTarget);	
	}	
}
</script>


<div class="cassiere">
	<h2 class="ico-users">
		<?php echo __('Cassiere');?>		
	</h2>
</div>

<?php
echo '<div class="contentMenuLaterale">';
echo $this->Form->create('Cassiere',array('id' => 'formGas'));
echo '<fieldset>';
		
echo '<div id="deliveries">';

if(!empty($deliveries)) {
	$options = ['id'=>'delivery_id', 'class' => 'form-control'];
	if(!empty($delivery_id) && $delivery_id>0)
		$options += array('default' => $delivery_id);
	else
		$options += array('empty' => Configure::read('option.empty'));
	
	echo $this->Form->input('delivery_id',$options);			
}
else 
	echo $this->element('boxMsg',array('class_msg' => 'notice', 'msg' => "Non ci sono consegne con ordini passati al cassiere"));		

echo '</div>';
			
echo '<div class="clearfix" id="users-result" style="display:none;width:55%;float:left;"></div>';
	
echo '<div id="user-anagrafica"  style="display:none;clear:none;width:45%;float:left;"></div>';

echo '<div class="clearfix" id="user-cash" style="display:none;"></div>';
					
echo '<div class="clearfix" id="doc-options" style="display:none;"></div>';

echo '<div class="clearfix" id="doc-print" style="display:none;"></div>';

echo '<div class="clearfix" id="doc-preview" style="display:none;"></div>';
	
echo '</fieldset>';

echo $this->Form->end();

echo '</div>';
?>

<style type="text/css">
.cakeContainer table.localSelector tr:hover {
    background-color: #F9FFCC; 
}
.cakeContainer table.localSelector tr.user-totale:hover {
    background-color: #FFFFFF; 
}
.cakeContainer table.localSelector tr.box-user.open {
    background-color: #F9FFAA; 
}
.cakeContainer .box-user,
.cakeContainer .box-cart {cursor:pointer;}
</style>
<script type="text/javascript">
$(document).ready(function() {

	var delivery_id = $('#delivery_id').val();
	if(delivery_id!="" && delivery_id!=undefined) choiceDeliveryCassiere();
	
	$('#delivery_id').change(function() {
		choiceDeliveryCassiere();
	});
});	
</script>