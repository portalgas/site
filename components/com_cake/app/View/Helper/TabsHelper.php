<?php
class TabsHelper extends AppHelper {
        
	var $helpers = array('Html','Time');

	/*
	 * $delivery['nota_evidenza'] notice, alert, message
	 *
	 * boostrap 
	 * 		alert alert-success (green)
	 * 		alert alert-info (blue)
	 * 		alert alert-warning (yellow)
	 * 		alert alert-danger (red)
	 * 		alert alert-error (red)
	 */
	function messageDelivery($delivery, $user=null) {

		$tmp = '';
		
		/*
		 *  messagio Consegna
		 */
		$sub_class = 'info';
		
		if(strtolower($delivery['nota_evidenza'])!='no')  {
			if(strtolower($delivery['nota_evidenza'])=='message')
				$sub_class = 'success';
			else
			if(strtolower($delivery['nota_evidenza'])=='notice')
				$sub_class = 'warning';
			else
			if(strtolower($delivery['nota_evidenza'])=='alert')
				$sub_class = 'danger';
		}
		
		$tmp .= '<div class="container" style="padding-left: 0px; margin-left: 0px; width: 100%;">';
		$tmp .= '<div class="col-xs-10">';
		$tmp .= '<div role="alert" class="alert alert-'.$sub_class.'">';
		$tmp .= '<a href="#" class="close" data-dismiss="alert">&times;</a>';
        $tmp .= '<strong>'.$delivery['luogo'].'</strong>';
		
		if($delivery['sys']=='N')
			$tmp .= ', dalle ore '.$this->formatOrario($delivery['orario_da']).' alle '.$this->formatOrario($delivery['orario_a']);
		
		if(!empty($delivery['nota']))
			$tmp .= '<p>'.$delivery['nota'].'</p>';
        $tmp .= '</div>';
		$tmp .= '</div>';

		$tmp .= "\n";
		$tmp .= '<div class="col-xs-2">';
		$tmp .= '<div class="pull-right action-deliveries">';

		/*
		 *  ico INFO
		 */
		$tmp .= '<a title="Maggiori informazioni" href="#" rel="nofollow" data-toggle="modal" data-target="#myModal">';
		$tmp .= '<i class="fa fa-info-circle fa-2x"></i>';
		$tmp .= '</a>';

		/*
		 *  ico CALENDAR
		 */	
		if(count($delivery['Order'])>0) {		 
			$tmp .= '<a title="Visualizza il calendario" href="javascript:viewCalendar('.$delivery['id'].');">';
			$tmp .= '<i class="fa fa-calendar fa-2x"></i>';
			$tmp .= '</a>';
		}
		
		/*
		 *  ico PDF
		 *
		 * in AppController setto $this->user
		 * $this->user->organization_id                    = organization dell'utente (in table.User)
		 * $this->user->organization['Organization']['id'] = organization che si sta navigando (dal templates ho il parametro organization_id e organizationSEO che metto in $user->set('org_id',$organization_id))
		 */
		if(isset($user) && $user->id > 0 && $user->organization_id == $user->organization['Organization']['id']) {
			$tmp .= '<a href="#" title="'.__('Print Cart Delivery').'" rel="nofollow" onclick="window.open(\'/?option=com_cake&controller=ExportDocs&action=userCart&delivery_id='.$delivery['id'].'&doc_formato=PDF&format=notmpl\',\'win2\',\'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no\'); return false;">';
			$tmp .= '<i class="fa fa-file-pdf-o fa-2x"></i>';
			$tmp .= '</a>';				
		}
		$tmp .= '</div>';
		$tmp .= '</div>'; 
		$tmp .= '</div>';  // class="container">
				
		return $tmp;
	}

	/*
	 * $delivery['nota_evidenza'] notice, alert, message
	 *
	 * boostrap 
	 * 		alert alert-success (green)
	 * 		alert alert-info (blue)
	 * 		alert alert-warning (yellow)
	 * 		alert alert-danger (red)
	 * 		alert alert-error (red)
	 * 
	 * per la modalita' preview dal link della mail
	*/
	function messageDeliveryPreview($results, $user=null) {

		$tmp = '';
			
		/*
		 *  messagio Consegna
		 */
		if($results['Delivery']['sys']=='N')
			$data = $this->Time->i18nFormat($results['Delivery']['data'],"%a %e %b");
		else
			$data = $results['Delivery']['luogo'];
			
		$sub_class = 'info';
		
		if(strtolower($delivery['nota_evidenza'])!='no')  {
			if(strtolower($delivery['nota_evidenza'])=='message')
				$sub_class = 'success';
			else
			if(strtolower($delivery['nota_evidenza'])=='notice')
				$sub_class = 'warning';
			else
			if(strtolower($delivery['nota_evidenza'])=='alert')
				$sub_class = 'danger';
		}
				
		
		$tmp .= '<ul data-tabs="tabs" class="nav nav-tabs deliveries" id="tabs">';
		$tmp .= '<li class="active">';
		$tmp .= '<a>'.$data;
		$tmp .= '</a></li></ul>';

		$tmp .= '<div class="tab-content deliveries">';
		$tmp .= '<div id="tabs-0" class="tab-pane deliveries active" style="min-height: 100px; background: none repeat scroll 0px 0px transparent;">';
		
		$tmp .= '<div class="container">';
		$tmp .= '<div class="col-xs-10">'; 
		$tmp .= '<div role="alert" class="alert alert-'.$sub_class.'">';
		$tmp .= '<a href="#" class="close" data-dismiss="alert">&times;</a>';
        $tmp .= '<strong>'.$results['Delivery']['luogo'].'</strong>';
		
		if($results['Delivery']['sys']!='N') 
			$tmp .= ', dalle ore '.$this->formatOrario($results['Delivery']['orario_da']).' alle '.$this->formatOrario($results['Delivery']['orario_a']);
		
		if(!empty($results['Delivery']['nota']))
			$tmp .= '<p>'.$results['Delivery']['nota'].'</p>';
			
        $tmp .= '</div>';
        $tmp .= '</div>';

		$tmp .= "\n";
		$tmp .= '<div class="col-xs-2">';
		$tmp .= '<div class="pull-right action-deliveries">';		
		/*
		 *  ico PDF
		 */
		$tmp .= '<a href="#" title="'.__('Print Cart Delivery').'" rel="nofollow" class="cartPreview">';
		$tmp .= '<i class="fa fa-file-pdf-o fa-2x"></i>';
		$tmp .= '</a>';
			
		$tmp .= '</div>';
		$tmp .= '</div>';
		$tmp .= '</div>';  //  class="container">
		
		$tmp .= '</div>';
		$tmp .= '</div>';
		
		return $tmp;
	}
	
	function messageNotOrders($msg="") {
		
		$tmp = '';
		
		$tmp .= '<div role="alert" class="alert alert-danger">';
		$tmp .= '<a href="#" class="close" data-dismiss="alert">&times;</a>';

		if(!empty($msg))
			$tmp .= $msg;
		else
			$tmp .= 'Non ci sono ancora consegne';
			
		$tmp .= '</div>';		
		
		return $tmp;
	}

	function drawTabs($results) {
	
		$totTabs = count($results['Tab']);
		
		$tmp = '';
		$tmp .= '<ul id="tabs" class="nav nav-tabs" data-tabs="tabs">';
		foreach($results['Tab'] as $numTabs => $result) {
				
			if($result['data'] < date('Y-m-d')) $cssOldTime = "oldTime";
			else  $cssOldTime = "";
	
			if($totTabs > Configure::read('TabsDeliveriesSmallLabel'))
				$data = $this->Time->i18nFormat($result['data'],"%a %e %b");
			else
				$data = $this->Time->i18nFormat($result['data'],"%A %e %B");
	
			$tmp .= '<li><a href="#tabs-'.$numTabs.'" class="tabsDelivery '.$cssOldTime.'">'.$data.'</a></li>';
	
		}
		$tmp .= '</ul>';
	
		return $tmp;
	}

	function drawTabsAjax($results) {		$totTabs = count($results);			$tmp = '';		$tmp .= '<ul id="tabs" class="nav nav-tabs deliveries" data-tabs="tabs">';		foreach($results as $numTabs => $result) {
			
			$cssOldTime = "";
			if($result['Delivery']['data']==Configure::read('DeliveryToDefinedDate')) 
				$data = Configure::read('DeliveryToDefinedLabel');
			else {
				if($result['Delivery']['data'] < date('Y-m-d')) $cssOldTime = "oldTime";								if($totTabs > Configure::read('TabsDeliveriesSmallLabel'))					$data = $this->Time->i18nFormat($result['Delivery']['data'],"%a %e %b");				else					$data = $this->Time->i18nFormat($result['Delivery']['data'],"%A %e %B");			}
							$tmp .= '<li><a href="#tabs-'.$numTabs.'" data-toggle="tab" class="tabsDelivery '.$cssOldTime.'" onClick="javascript:drawDelivery(\''.$result['Delivery']['data'].'\', '.$numTabs.')">';
			$tmp .= $data.'</a></li>';			}		$tmp .= '</ul>';			return $tmp;	}	
	function drawTabsUserCart($results, $storeroomResults=null) {

		$totTabs = count($results['Tab']);
		
		$tmp = '';	
		$tmp .= '<ul id="tabs" class="nav nav-tabs" data-tabs="tabs">';
		foreach($results['Tab'] as $numTabs => $result) {
			
			/*
			 * ctrl che almeno una consegna del tab abbia un acquisto (anche per la dispensa)
			 * */
			$continue = false;
			foreach($result['Delivery'] as $numDelivery => $delivery) { 
				if($delivery['totArticlesOrder']>0) $continue = true;
				
				if(isset($storeroomResults['Tab']) && $storeroomResults['Tab'][$numTabs]['Delivery'][$numDelivery]['totStorerooms']>0) $continue = true;
			}
			
			if($continue) {
				$cssOldTime = "";
				
				if($result['Delivery']['data']==Configure::read('DeliveryToDefinedDate'))
					$data = Configure::read('DeliveryToDefinedLabel');
				else {
					if($result['data'] < date('Y-m-d')) $cssOldTime = "oldTime";
					
		
					if($totTabs > Configure::read('TabsDeliveriesSmallLabel'))
						$data = $this->Time->i18nFormat($result['data'],"%a %e %b");
					else
						$data = $this->Time->i18nFormat($result['data'],"%A %e %B");
				}
							
				$tmp .= '<li><a href="#tabs-'.$numTabs.'" class='.$cssOldTime.'>'.$data.'</a></li>';
			}		
		}
		$tmp .= '</ul>';
		
		return $tmp;
	}
		
	public function setTableHeader($delivery_id, $user) {
	
		$tmp = '';
		
		$tmp .= '<div class="col-xs-12">';
		$tmp .= '<div class="table"><table class="table table-hover">';
		$tmp .= "\n";
		$tmp .= '<thead>';
		$tmp .= '<tr>';
		$tmp .= "\n";
		$tmp .= '<th>N.</th>';
		$tmp .= "\n";
		$tmp .= '<th>'.__('Supplier').'</th>';
		$tmp .= "\n";
		$tmp .= '<th class="hidden-xs th-deliveryId'.$delivery_id.'-A" style="display:table-cell;">Scheda</th>';
		$tmp .= "\n";
		$tmp .= '<th class="th-deliveryId'.$delivery_id.'-A" style="display:table-cell;">Chiusura ordine</th>';
		$tmp .= "\n";
		$tmp .= '<th class="hidden-xs th-deliveryId'.$delivery_id.'-A" style="display:table-cell;" class="" ></th>';
		$tmp .= "\n";
		$tmp .= '<th class="hidden-xs th-deliveryId'.$delivery_id.'-A" style="display:table-cell;">Frequenza</th>';
		$tmp .= "\n";
		$tmp .= '<th class="th-deliveryId'.$delivery_id.'-A" style="display:table-cell;">'.__('Suppliers Organizations Referents').'</th>';
		$tmp .= "\n";
		if(isset($user) && $user->id > 0 && $user->organization_id == $user->organization['Organization']['id']) {
			$tmp .= '<th class="th-deliveryId'.$delivery_id.'-B dettaglioAcquisti" style="display:none;" colspan="5">Articoli acquistati</th>';
			$tmp .= '<th width="70">Acquisti<input type="checkbox" value="ALL" id="showHideAllCart_'.$delivery_id.'" name="showHideAllCart_'.$delivery_id.'" style="float:right;" /></th>';
			$tmp .= "\n";
		}
		$tmp .= '</tr></thead>';

		return $tmp;
	}

	function setTableHeaderEcommSimpleFrontEnd($delivery_id) {

		$str = "";
		$str .= '<div class="table"><table class="table table-hover" id="tableList_'.$delivery_id.'" >';
		$str .= "\n";
		$str .= '<thead>';
		$str .= '<tr>';
		$str .= "\n";
		$str .= '<th>';
		$str .= '<button data-attr="SIMPLE" style="cursor:default;opacity:0.5;" title="visualizzazione in elenco" type="button" class="btn btn-primary btn-md btn-type-draw-current"><i class="fa fa-th-list" aria-hidden="true"></i></button>';
		$str .= '<br />';
		$str .= '<button data-attr="COMPLETE" style="cursor:pointer;opacity:1;margin-top:5px;" title="Visualizzazione a box" type="button" class="btn btn-primary btn-md btn-type-draw"><i class="fa fa-th" aria-hidden="true"></i></button>';
		$str .= '</th>';
		$str .= "\n";
		$str .= '<th>N.</th>';
		$str .= "\n";
		$str .= '<th class="hidden-xs hidden-sm">'.__('Bio').'</th>';
		$str .= "\n";
		$str .= '<th>'.__('Article').'</th>';
		$str .= "\n";
		$str .= '<th>'.__('Conf').'</th>';
		$str .= "\n";
		$str .= '<th>'.__('PrezzoUnita').'</th>';
		$str .= "\n";
		$str .= '<th class="hidden-xs hidden-sm">'.__('Prezzo/UM').'</th>';
		$str .= "\n";
		$str .= '<th class="hidden-xs hidden-sm">Min.</th>';
		$str .= "\n";
		$str .= '<th style="width:16px;"></th>';
		$str .= "\n";
		$str .= '<th width="150px">'.__('qta').'</th>';
		$str .= "\n";
		$str .= '<th width="100px">'.__('Importo').'</th>';
		$str .= "\n";
		$str .= '</tr></thead>';

		return $str;
	}

	/*
	 * header table per gli acquisti da validate (ArticlesOrder.pezzi_confezione > 1)
	 */
	function setTableHeaderEcommCartsValidationFrontEnd($delivery_id) {
	
		$str = "";
		$str .= '<div class="table"><table class="table table-hover" id="tableList_'.$delivery_id.'" >';
		$str .= "\n";
		$str .= '<thead>';
		$str .= '<tr>';
		$str .= "\n";
		$str .= '<th></th>';
		$str .= "\n";
		$str .= '<th>N.</th>';
		$str .= "\n";
		$str .= '<th class="hidden-xs">'.__('Bio').'</th>';
		$str .= "\n";
		$str .= '<th>'.__('Article').'</th>';
		$str .= "\n";
		$str .= '<th>'.__('Conf').'</th>';		
		$str .= "\n";
		$str .= '<th>'.__('PrezzoUnita').'</th>';
		$str .= "\n";
		$str .= '<th class="hidden-xs">'.__('Prezzo/UM').'</th>';
		$str .= "\n";
		$str .= '<th class="hidden-xs">Min.</th>';
		$str .= "\n";
		$str .= '<th style="width:16px;"></th>';
		$str .= "\n";
		$str .= '<th>Mancano al collo</th>';		
		$str .= "\n";
		$str .= '<th width="150px">'.__('qta').'</th>';
		$str .= "\n";
		$str .= '<th width="100px">'.__('Importo').'</th>';
		$str .= "\n";
		$str .= '</tr></thead>';
	
		return $str;
	}
	
	/*
	 * header table per gli acquisti da ProdGasPromotion
	 */
	function setTableHeaderEcommPromotionFrontEnd($delivery_id) {
	
		$str = "";
		$str .= '<div class="table"><table class="table table-hover" id="tableList_'.$delivery_id.'" >';
		$str .= "\n";
		$str .= '<thead>';
		$str .= '<tr>';
		$str .= "\n";
		$str .= '<th></th>';
		$str .= "\n";
		$str .= '<th>N.</th>';
		$str .= "\n";
		$str .= '<th class="hidden-xs">'.__('Bio').'</th>';
		$str .= "\n";
		$str .= '<th>'.__('Article').'</th>';
		$str .= "\n";
		$str .= '<th>'.__('Conf').'</th>';		
		$str .= "\n";
		$str .= '<th>'.__('prezzo_unita_in_promozione').'</th>';
		$str .= "\n";
		$str .= '<th class="hidden-xs">'.__('Prezzo/UM').'</th>';
		$str .= "\n";
		$str .= '<th style="width:16px;"></th>';	
		$str .= '<th>'.__('qta_in_promozione').'</th>';	 // la qta totale da raggiungere per la promozione		
		$str .= "\n";
		$str .= '<th width="150px">'.__('qta').'</th>';
		$str .= "\n";
		$str .= '<th width="100px">'.__('Importo').'</th>';
		$str .= "\n";
		$str .= '</tr></thead>';
	
		return $str;
	}
		
	function setTableHeaderEcommStoreroomFrontEnd($delivery_id) {

		$str = "";
		$str .= '<div class="table"><table class="table table-hover" cellpadding="0" id="tableList_'.$delivery_id.'" >';
		$str .= "\n";
		$str .= '<thead>';
		$str .= '<tr>';
		$str .= "\n";
		$str .= '<th></th>';
		$str .= "\n";
		$str .= '<th>N.</th>';
		$str .= "\n";
		$str .= '<th class="hidden-xs">'.__('Bio').'</th>';
		$str .= "\n";
		$str .= '<th>'.__('Article').'</th>';
		$str .= "\n";
		$str .= '<th>'.__('Conf').'</th>';
		$str .= "\n";
		$str .= '<th class="hidden-xs">'.__('PrezzoUnita').'</th>';
		$str .= "\n";
		$str .= '<th class="hidden-xs">'.__('Prezzo/UM').'</th>';
		$str .= "\n";
		$str .= '<th>'.__('Acquistato').'</th>';
		$str .= "\n";
		$str .= '<th>'.__('Importo').'</th>';
		$str .= "\n";
		$str .= '<th></th>';
		$str .= "\n";
		$str .= '</tr></thead>';

		return $str;
	}

	function drawTableHeaderBackOfficeReportUsers($result, $permissions) {
	
		$str = "";
		
		$str .= "<script type='text/javascript'>
				jQuery(document).ready(function() {
		
					jQuery('.colsOrderBy').click(function() {
							var order_by = jQuery(this).attr('id');
							var delivery_id = jQuery('#delivery_id').val();
							var order_id = jQuery('#order_id').val();
							var user_id     = jQuery('#user_id').val();
							var articlesOptions = jQuery(\"input[name='articles-options']:checked\").val();
							AjaxCallToArticlesResult(delivery_id, order_id, user_id, articlesOptions, order_by);
					});
				});
				</script>
				";
				
		$str .= '<table cellspacing="0" cellpadding="0" id="tableList_'.$result['Order']['delivery_id'].'">';
		$str .= "\n";
		$str .= '<thead>';
		$str .= '<tr>';
		$str .= "\n";
		$str .= '<th rowspan="2" style="height: 10px;"></th>';
		$str .= "\n";
		$str .= '<th rowspan="2" style="height: 10px;">N.</th>';
		$str .= "\n";
		$str .= '<th rowspan="2" style="height: 10px;">&nbsp;'.__('Article');
		$str .= '<span><img src="'.Configure::read('App.img.cake').'/actions/16x16/1downarrow.png" class="colsOrderBy" id="articles_asc"></span>';
		$str .= '<span><img src="'.Configure::read('App.img.cake').'/actions/16x16/1uparrow.png" class="colsOrderBy" id="articles_desc"></span>';
		$str .= '</th>';
		
		$str .= "\n";
		$str .= '<th rowspan="2" style="height: 10px;">'.__('Conf').'</th>';
		$str .= "\n";
		$str .= '<th rowspan="2" style="height: 10px;">'.__('PrezzoUnita').'</th>';
		$str .= "\n";
		$str .= '<th rowspan="2" style="height: 10px;">'.__('Prezzo/UM').'</th>';
		
		$str .= "\n";
		$str .= '<th style="text-align:center;width:50px;height:10px;border-bottom:none;border-left:1px solid #CCCCCC;">'.__('qta').'</th>';
		$str .= "\n";
		$str .= '<th style="text-align:center;width:100px;height:10px;border-bottom:none;border-right:1px solid #CCCCCC;">'.__('Importo').'</th>';
		$str .= "\n";
		$str .= '<th rowspan="2" style="text-align:center;width:16px;height:10px;"></th>';
	
		if(($result['Order']['permissionToEditReferente'] && $permissions['isReferentGeneric']) ||
				($result['Order']['permissionToEditTesoriere'] && $permissions['isTesoriereGeneric']) )  {
			$str .= "\n";
			$str .= '<th colspan="2" style="text-align:center;width:250px;height:10px;border-bottom:none;">Quantità e importi totali</th>';
			$str .= "\n";
			$str .= '<th rowspan="2" style="height: 10px;width:125px"><span style="float:left;">Importo<br />forzato</span>';
			$str .= '<span style="float:right;">'.$this->drawTooltip('Importo forzato',__('toolTipImportoForzato'),$type='WARNING',$pos='LEFT').'</span>';
			$str .= '</th>';
			$str .= "\n";
			$str .= '<th rowspan="2" style="height: 10px;"></th>';
		}
		$str .= "\n";
		$str .= '</tr>';
	
		$str .= '<tr>';
		$str .= "\n";
		$str .= '<th colspan="2" style="text-align:center;height:10px;border-left:1px solid #CCCCCC;border-right:1px solid #CCCCCC;">dell\'utente</th>';
	
		if(($result['Order']['permissionToEditReferente'] && $permissions['isReferentGeneric']) ||
				($result['Order']['permissionToEditTesoriere'] && $permissions['isTesoriereGeneric']) )  {
			$str .= "\n";
			$str .= '<th colspan="2" style="text-align:center;height:10px;border-right:1px solid #CCCCCC;">modificati dal referente</th>';
		}
		$str .= '</tr>';
	
		$str .= '</thead>';
		$str .= '<tbody>';
	
		return $str;
	}
	
	function drawTableHeaderBackOfficeReportArticlesDetails($result, $permissions) {
	
		$str = "";
		
		$str .= "<script type='text/javascript'>
				jQuery(document).ready(function() {
		
					jQuery('.colsOrderBy').click(function() {
							var order_by = jQuery(this).attr('id');
							var delivery_id = jQuery('#delivery_id').val();
							var order_id = jQuery('#order_id').val();
							AjaxCallToArticlesDetailsResult(delivery_id, order_id, order_by);
					});
				});
				</script>
				";
		
		$str .= '<table cellspacing="0" cellpadding="0" id="tableList_'.$result['Order']['delivery_id'].'">';
		$str .= "\n";
		$str .= '<thead>';
		$str .= '<tr>';
		$str .= "\n";
		$str .= '<th rowspan="2" style="height: 10px;"></th>';
		$str .= "\n";
		$str .= '<th rowspan="2" style="height: 10px;">N.</th>';
		$str .= "\n";
		$str .= '<th rowspan="2" style="height: 10px;">&nbsp;'.__('Article');
		$str .= '<span><img src="'.Configure::read('App.img.cake').'/actions/16x16/1downarrow.png" class="colsOrderBy" id="articles_asc"></span>';
		$str .= '<span><img src="'.Configure::read('App.img.cake').'/actions/16x16/1uparrow.png" class="colsOrderBy" id="articles_desc"></span>';
		$str .= '</th>';
		
		$str .= "\n";
		$str .= '<th rowspan="2" style="height: 10px;">Utente';
		$str .= '<span><img src="'.Configure::read('App.img.cake').'/actions/16x16/1downarrow.png" class="colsOrderBy" id="users_asc"></span>';
		$str .= '<span><img src="'.Configure::read('App.img.cake').'/actions/16x16/1uparrow.png" class="colsOrderBy" id="users_desc"></span>';
		$str .= '</th>';
		
		$str .= "\n";
		$str .= '<th rowspan="2" style="height: 10px;">Acquistato il</th>';
		
		$str .= "\n";
		$str .= '<th style="text-align:center;width:50px;height:10px;border-bottom:none;border-left:1px solid #CCCCCC;">'.__('qta').'</th>';
		$str .= "\n";
		$str .= '<th style="text-align:center;width:100px;height:10px;border-bottom:none;border-right:1px solid #CCCCCC;">'.__('Importo').'</th>';
		$str .= "\n";
		$str .= '<th rowspan="2" style="text-align:center;width:16px;height:10px;"></th>';
		
		if(($result['Order']['permissionToEditReferente'] && $permissions['isReferentGeneric']) ||
		   ($result['Order']['permissionToEditTesoriere'] && $permissions['isTesoriereGeneric']) )  {
			$str .= "\n";
			$str .= '<th colspan="2" style="text-align:center;width:250px;height:10px;border-bottom:none;">Quantità e importi totali</th>';
			$str .= "\n";
			$str .= '<th rowspan="2" style="height: 10px;width:125px"><span style="float:left;">Importo<br />forzato</span>';
			$str .= '<span style="float:right;">'.$this->drawTooltip('Importo forzato',__('toolTipImportoForzato'),$type='WARNING',$pos='LEFT').'</span>';
			$str .= '</th>';
			$str .= "\n";
			$str .= '<th rowspan="2" style="height: 10px;"></th>';
		}
		$str .= "\n";
		$str .= '</tr>';
		
		$str .= '<tr>';
		$str .= "\n";
		$str .= '<th colspan="2" style="text-align:center;height:10px;border-left:1px solid #CCCCCC;border-right:1px solid #CCCCCC;">dell\'utente</th>';

		if(($result['Order']['permissionToEditReferente'] && $permissions['isReferentGeneric']) ||
				($result['Order']['permissionToEditTesoriere'] && $permissions['isTesoriereGeneric']) )  {
			$str .= "\n";
			$str .= '<th colspan="2" style="text-align:center;height:10px;border-right:1px solid #CCCCCC;">modificati dal referente</th>';
		}
		$str .= '</tr>';
		
		$str .= '</thead>';
		$str .= '<tbody>';
			
		return $str;
	}	
}
?>