<?php$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);if(!isset($delivery_id)) $delivery_id = 0;$this->Html->addCrumb(__('Tesoriere home'));echo $this->Html->getCrumbList(array('class'=>'crumbs'));?><script type="text/javascript">var debugLocal = false;var delivery_id = '<?php echo $delivery_id; // se arrivo da Orders/admin_index.ctp e' valorizzato ?>';var order_id = '<?php echo $order_id; // se arrivo da Orders/admin_index.ctp e' valorizzato ?>';function choiceDeliveryTesoriere() {	var div_contenitore = 'deliveries';	delivery_id = $('#delivery_id').val();	if(debugLocal) alert("choiceDeliveryTesoriere - div_contenitore "+div_contenitore+", delivery_id "+delivery_id);	if(delivery_id=='') {		showHideBox(div_contenitore,call_child=false);		return;	}		showHideBox(div_contenitore,call_child=true);	AjaxCallToUsersDelivery(delivery_id); /* chiamata Ajax per elenco users della consegna */	}function choiceUser() {	var div_contenitore = $('#user_id').parent().parent().attr('id');  /* users-result */	var user_id = $('#user_id').val();	if(debugLocal) alert("choiceUser - div_contenitore "+div_contenitore+", user_id "+user_id);	if(user_id=='') {		showHideBox(div_contenitore,call_child=false);		return;	}	showHideBox(div_contenitore,call_child=true);	AjaxCallToUserAnagrafica(user_id); /* chiamata Ajax per l'anagrafica utente se user_id == ALL disabilito l'opzione */	}	/* * articlesOptions = 'options-articles-cart',  'options-users-all' */function choiceArticlesOptions(order_id, user_id, articlesOptions) {		/* id = order.id_user.id */	var idRow = order_id+'_'+user_id;	/* console.log('idRow '+idRow); */ 	var delivery_id = $('#delivery_id').val();	var idDivTarget = 'box-cart-content'+idRow;	var order_by = "";		AjaxCallToManagementCartsUsers(delivery_id, order_id, user_id, articlesOptions, order_by, idDivTarget);		}/* * chiamata Ajax per elenco utenti di una consegna */function AjaxCallToUsersDelivery(delivery_id) {			var url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_users_delivery&delivery_id="+delivery_id+'&format=notmpl';	var idDivTarget = 'users-result';	ajaxCallBox(url, idDivTarget);}/* * chiamata Ajax per elenco ordini di cui lo user scelto ha effettuato acquisti */function AjaxCallToOrdersUsersCart(delivery_id, user_id, idDivTarget) {	var url = '/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_orders_users_cart&delivery_id='+delivery_id+'&user_id='+user_id+'&order_id=0&format=notmpl';	ajaxCallBox(url, idDivTarget);}/* *  chiamata Ajax per elenco articoli */function AjaxCallToReadCartsUsers(delivery_id, user_id, order_id, idDivTarget) {	var url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_orders_users_cart&delivery_id="+delivery_id+"&user_id="+user_id+"&order_id="+order_id+"&format=notmpl";	ajaxCallBox(url, idDivTarget);}function AjaxCallToManagementCartsUsers(delivery_id, order_id, user_id, articlesOptions, order_by, idDivTarget) {	var url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_management_carts_users&delivery_id="+delivery_id+"&order_id="+order_id+"&user_id="+user_id+"&articlesOptions="+articlesOptions+"&order_by="+order_by+"&format=notmpl";	ajaxCallBox(url, idDivTarget);}function AjaxCallToOptions(delivery_id, order_id, user_id, idDivTarget) {	var url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_articles_options_pay_to_delivery&delivery_id="+delivery_id+"&order_id="+order_id+"&user_id="+user_id+"&format=notmpl";	ajaxCallBox(url, idDivTarget);}</script><div class="tesoriere">	<h2 class="ico-users">		<?php echo __('Tesoriere');?>	</h2></div><div class="docs"><?php echo $this->Form->create('Tesoriere',array('id' => 'formGas'));?>		<fieldset>				<div id="deliveries">		<?php		if(!empty($deliveries)) {			$options = array('id'=>'delivery_id', 'class' => 'form-control');			if(!empty($delivery_id) && $delivery_id>0)				$options += array('default' => $delivery_id);			else				$options += array('empty' => Configure::read('option.empty'));						echo $this->Form->input('delivery_id',$options);		}
		else
			echo $this->element('boxMsg',array('class_msg' => 'notice', 'msg' => "Non ci sono consegne da elaborare"));				?>			</div>					<div class="clearfix"></div>		<div id="users-result" style="display:none;"></div>			</fieldset></div><style type="text/css">.cakeContainer table.localSelector tr:hover {    background-color: #F9FFCC; }.cakeContainer table.localSelector tr.user-totale:hover {    background-color: #FFFFFF; }.cakeContainer table.localSelector tr.box-user.open {    background-color: #F9FFAA; }.cakeContainer .box-user,.cakeContainer .box-cart {cursor:pointer;}</style><script type="text/javascript">$(document).ready(function() {	var delivery_id = $('#delivery_id').val();	if(delivery_id!="" && delivery_id!=undefined) choiceDeliveryTesoriere();		$('#delivery_id').change(function() {		choiceDeliveryTesoriere();	});});	</script>