<?php
if(isset($order['Order']) && $order['Order']['order_type_id']==Configure::read('Order.type.gas_groups')) {
	$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
	$this->Html->addCrumb(__('List Orders'), Configure::read('Neo.portalgas.url').'admin/orders/index/'.$order['Order']['order_type_id']);
	$this->Html->addCrumb(__('Order home'), Configure::read('Neo.portalgas.url').'admin/orders/home/'.$order['Order']['order_type_id'].'/'.$order['Order']['id']);
	$this->Html->addCrumb(__('Management Carts One'));
}
else {
	$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
	$this->Html->addCrumb(__('List Orders'),array('controller' => 'Orders', 'action' => 'index'));
	if(isset($order_id) && !empty($order_id))
		$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order_id));
	$this->Html->addCrumb(__('Management Carts One'));
}
echo $this->Html->getCrumbList(['class'=>'crumbs']);
?>
<script type="text/javascript">
var debugLocal = false;
var delivery_id = <?php echo $delivery_id; // se arrivo da Orders/admin_index.ctp e' valorizzato ?>;
var order_id = <?php echo $order_id; // se arrivo da Orders/admin_index.ctp e' valorizzato ?>;

function choiceOrderPermission() {
	var div_contenitore = 'order-permission';
	showHideBox(div_contenitore,call_child=true); 

	AjaxCallToReportOptions();	/* chiamata Ajax opzioni utenti */
}
function choiceReportOptions() {	
	var div_contenitore = 'report-options';
	var order_id = $("#order_id").val();	
	var reportOptions = $("input[name='report-options']:checked").val();
	if(debugLocal) alert("choiceReportOptions - order_id "+order_id+", reportOptions "+reportOptions);

	if(order_id=="" || reportOptions=="") {
		showHideBox(div_contenitore,call_child=false);
		return;
	}

	showHideBox(div_contenitore,call_child=true);
	
	if(reportOptions=='report-users-cart' || reportOptions=='report-users-all') {
		AjaxCallToUsers(reportOptions); /* chiamata Ajax l'elenco degli utenti */
	}
	else
	if(reportOptions=='report-articles-details') {
		$('#users-result').css('display', 'none');  /* showHideBox non mi nasconde tutti perche' users-result e articles-options non li uso */
		$('#articles-result').css('display', 'block');
		
		AjaxCallToArticlesDetailsResult(delivery_id, order_id, order_by = 'articles_asc'); /* chiamata Ajax l'elenco degli articoli aggregati con il dettaglio degli utenti */
	}
}
function choiceUser() {
	var div_contenitore = $('#user_id').parent().parent().attr('id');  /* users-result */
	if (div_contenitore === undefined) 
		div_contenitore = 'users-result';
	var user_id = $('#user_id').val();
	if(debugLocal) alert("choiceUser - div_contenitore "+div_contenitore+", user_id "+user_id);
	if(user_id=='') {
		showHideBox(div_contenitore,call_child=false);
		return;
	}

	showHideBox(div_contenitore,call_child=true);
	AjaxCallToUserAnagrafica(user_id); /* chiamata Ajax per l'anagrafica utente se user_id == ALL disabilito l'opzione */	
}	
function choiceUserAnagrafica() {
	var div_contenitore = 'user-anagrafica';
	var user_id = $('#user_id').val();
	if(debugLocal) alert("choiceUserAnagrafica - div_contenitore "+div_contenitore+", user_id "+user_id);
	if(user_id=='') {
		showHideBox(div_contenitore,call_child=false);
		return;
	}
	
	showHideBox(div_contenitore,call_child=true);
	AjaxCallToArticlesOptions(user_id); /* chiamata Ajax per opzioni articoli, se user_id == ALL disabilito l'opzione (Tutti gli articoli) */	

}
function choiceArticlesOptions() {

	var div_contenitore = 'articles-options';

	var delivery_id = $('#delivery_id').val();
	var order_id    = $('#order_id').val(); /* estraggo info di delivery_id e supplier_id */
	var user_id     = $('#user_id').val();

	var articlesOptions = $("input[name='articles-options']:checked").val();
	var articlesSort = $("input[name='articles-sort']:checked").val();
	
	if(debugLocal) alert("choiceArticlesOptions - div_contenitore "+div_contenitore+", articlesOptions "+articlesOptions);
	if(delivery_id == '' || order_id=='' || user_id=='' || articlesOptions=='') {
		showHideBox(div_contenitore,call_child=false);
		return;
	}
	showHideBox(div_contenitore,call_child=true);
	
	if(user_id=='ALL') // order_by = 'users_asc'
		AjaxCallToArticlesDetailsResult(delivery_id, order_id, articlesSort);  /* chiamata Ajax per elenco articoli aggregati con il dettaglio degli utenti */
	else // order_by = 'articles_asc'
		AjaxCallToArticlesResult(delivery_id, order_id, user_id, articlesOptions, articlesSort); /* chiamata Ajax l'elenco degli articoli */
}

/*
 * chiamata Ajax per opzioni dei reports (Solo utenti con acquisti / Tutti gli utenti / Articoli aggregati con il dettaglio degli utenti)
 */
function AjaxCallToReportOptions() {
	var delivery_id = $('#delivery_id').val();
	var order_id    = $('#order_id').val(); 	
	var url = '/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_report_options&delivery_id='+delivery_id+'&order_id='+order_id+'&format=notmpl';
	var idDivTarget = 'report-options';
	ajaxCallBox(url, idDivTarget);	
}
/*
 * chiamata Ajax per elenco utenti
 */
function AjaxCallToUsers(reportOptions) {

	var url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_users&order_id="+order_id+'&reportOptions='+reportOptions+'&format=notmpl';
	var idDivTarget = 'users-result';
	ajaxCallBox(url, idDivTarget);
}
/*
 *  chiamata Ajax per anagrafica utente
 *  			se user_id == ALL disabilito l'opzione 
 */
function AjaxCallToUserAnagrafica(user_id) {
	var delivery_id = $('#delivery_id').val();
	var order_id    = $('#order_id').val(); 	
	var url = '/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_user_anagrafica&delivery_id='+delivery_id+'&order_id='+order_id+'&user_id='+user_id+'&call=managementCartsOne&format=notmpl';
	var idDivTarget = 'user-anagrafica';
	ajaxCallBox(url, idDivTarget);
}
/*
 *  chiamata Ajax per opzioni articoli
 *  			se user_id == ALL disabilito l'opzione (Tutti gli articoli)
 */
function AjaxCallToArticlesOptions(user_id) {
	var delivery_id = $('#delivery_id').val();
	var order_id    = $('#order_id').val(); 	
	var url = '/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_articles_options&delivery_id='+delivery_id+'&order_id='+order_id+'&user_id='+user_id+'&format=notmpl';
	var idDivTarget = 'articles-options';
	ajaxCallBox(url, idDivTarget);
}
/*
 *  chiamata Ajax per elenco articoli
 */
function AjaxCallToArticlesResult(delivery_id, order_id, user_id, articlesOptions, order_by){
	var url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_management_carts_users&delivery_id="+delivery_id+"&order_id="+order_id+"&user_id="+user_id+"&articlesOptions="+articlesOptions+"&order_by="+order_by+"&format=notmpl";
	var idDivTarget = 'articles-result';
	ajaxCallBox(url, idDivTarget);
}
/*
 *  chiamata Ajax per elenco articoli aggregati con il dettaglio degli utenti
 */
function AjaxCallToArticlesDetailsResult(delivery_id, order_id, order_by) {
	var url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_management_carts_articles_details&delivery_id="+delivery_id+"&order_id="+order_id+"&order_by="+order_by+"&format=notmpl";
	var idDivTarget = 'articles-result';
	ajaxCallBox(url, idDivTarget);
}
</script>



<h2 class="ico-management-carts-one">
	<?php echo __('Management Carts One');?>
	<div class="actions-img">
		<ul>
			<li><?php echo $this->Html->link(__('Order home'), array('controller' => 'Orders', 'action' => 'home', null, 'order_id='.$order_id),array('class' => 'action actionWorkflow','title' => __('Order home'))); ?></li>
		</ul>
	</div>
</h2>

<div class="contentMenuLaterale">
<?php 
echo $this->Form->create();
echo $this->element('boxDesOrder', array('results' => $desOrdersResults, 'summaryDesOrderResults' => $summaryDesOrderResults));
?>
	<fieldset>
	
		<?php 
		echo $this->element('boxOrder',array('results' => $results));
		?>		
						
		<div id="report-options" style="display:none;clear: both;"></div>
		
		<div id="users-result" style="display:none;width:55%;float:left;clear: both;"></div>
	
		<div id="user-anagrafica"  style="display:none;clear:none;width:45%;float:left;"></div>
		
		<div id="articles-options" style="display:none;clear: both;"></div>
	
		<div id="articles-result" style="display:none;min-height:50px;clear: both;"></div>
		
	</fieldset>
</div>

<div id="dialogmodal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Nota da associare all'articolo acquistato</h4>
      </div>
      <div class="modal-body">
        <p><textarea class="noeditor" id="notaTextEcomm" name="nota" style="width: 100%;" rows="10"></textarea>
		<div class="clearfix"></div>
		</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo __('Close');?></button>
        <button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo __('Submit');?></button>
      </div>
    </div>

  </div>
</div>


<?php 
$options = [];
echo $this->MenuOrders->drawWrapper($order_id, $options);

/*
 * legenda profilata
*/
echo $this->App->drawLegenda($user, $orderStatesToLegenda);
?>
<script type="text/javascript">
$(document).ready(function() {
	
    $('#dialogmodal').on('shown.bs.modal', function() {
		var numRowData = $("#dialogmodal");
		numRow = numRowData.data('numRow');
		
		$('#notaTextEcomm').val("");
				
		var order_id = $('#order_id-'+numRow).val();
		var article_organization_id = $('#article_organization_id-'+numRow).val();
		var article_id = $('#article_id-'+numRow).val();
		var user_id = $('#user_id-'+numRow).val();
		var key = order_id+"_"+article_organization_id+"_"+article_id+"_"+user_id;
				
		$.ajax({
			type: "GET",
			url: "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=getNotaForzato&key="+key+"&format=notmpl",
			data: "",
			success: function(response){
				$('#notaTextEcomm').val(response);
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
			}
		});
		return false;
    })
	.on('hidden.bs.modal', function() {

		var numRowData = $("#dialogmodal");
		numRow = numRowData.data('numRow');
			
		var notaTextEcomm = encodeURIComponent($('#notaTextEcomm').val());
		
		var order_id = $('#order_id-'+numRow).val();
		var article_organization_id = $('#article_organization_id-'+numRow).val();
		var article_id = $('#article_id-'+numRow).val();
		var user_id = $('#user_id-'+numRow).val();
		var key = order_id+"_"+article_organization_id+"_"+article_id+"_"+user_id;

		$.ajax({
			type: "POST",
			url: "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=setNotaForzato&key="+key+"&format=notmpl",
			data: "notaTextEcomm="+notaTextEcomm,
			success: function(response){
				if(notaTextEcomm=="")
					$('#notaEcomm-'+numRow).attr('src','<?php echo Configure::read('App.img.cake');?>/actions/32x32/filenew.png');					
				else	
					$('#notaEcomm-'+numRow).attr('src','<?php echo Configure::read('App.img.cake');?>/actions/32x32/playlist.png');
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
			}
		});
		return false;
    });
	
	<?php if(!empty($alertModuleConflicts)) {
		if(!$popUpDisabled)
			echo "apriPopUpBootstrap('".Configure::read('App.server')."/administrator/index.php?option=com_cake&controller=PopUp&action=".$alertModuleConflicts."&orderHasTrasport=".$orderHasTrasport."&orderHasCostMore=".$orderHasCostMore."&orderHasCostLess=".$orderHasCostLess."&format=notmpl', '')";
	}
	?>
});
</script>