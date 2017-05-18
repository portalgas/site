<?php
class ProdTabsHelper extends AppHelper {
        
	var $helpers = array('Html','Time');

	/*
	 * $prod_delivery['nota_evidenza'] notice, alert, message
	 */
	function messageDelivery($prod_delivery, $user=null) {
		
		$tmp = '';
		$tmp .= '<div class="legenda legenda-'.strtolower($prod_delivery['nota_evidenza']).'">';
		
		if(strtolower($prod_delivery['nota_evidenza'])!='no')  {
			
			$tmp .= '<div id="type-message" class="';
			if(strtolower($prod_delivery['nota_evidenza'])=='message')
				$tmp .= 'icon-pin';
			else
			if(strtolower($prod_delivery['nota_evidenza'])=='notice')
				$tmp .= 'icon-warning-sign';
			else
			if(strtolower($prod_delivery['nota_evidenza'])=='alert')
				$tmp .= 'icon-danger';
			
			$tmp .= '"></div>';
				
		}
		
		$tmp .= '<div style="float:left; width:70%; padding-left:15px; font-size:14px;">';
		$tmp .= $prod_delivery['luogo'].', dalle ore '.$this->formatOrario($prod_delivery['orario_da']).' alle '.$this->formatOrario($prod_delivery['orario_a']);
		
		if(!empty($prod_delivery['nota']))
			$tmp .= '<p class="nota">'.$prod_delivery['nota'].'</p>';
		
		$tmp .= '</div>';
		
		// <img alt="info" src="'.Configure::read('App.img.cake').'/tooltips/24x24/help.png">
		$tmp .= "\n";
		$tmp .= '<div style="float:right;width:20%">';
		
		$tmp .= "<ul>";

		/*
		 * in AppController setto $this->user
		* $this->user->organization_id                    = organization dell'utente (in table.User)
		* $this->user->organization['Organization']['id'] = organization che si sta navigando (dal templates ho il parametro organization_id e organizationSEO che metto in $user->set('org_id',$organization_id))
		*/
		if(isset($user) && $user->id > 0 && $user->organization_id == $user->organization['Organization']['id']) {
			// <img alt="PDF" src="'.Configure::read('App.img.cake').'/minetypes/32x32/pdf.png">
			$tmp .= '<li>';
			$tmp .= '<a style="cursor:pointer;" rel="nofollow" onclick="window.open(\'/?option=com_cake&controller=ExportDocs&action=userCart&prod_delivery_id='.$prod_delivery['id'].'&doc_formato=PDF&format=notmpl\',\'win2\',\'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no\'); return false;" title="'.__('Print Cart Delivery').'">';
			//$tmp .= '<div class="icon-archive"></div> '.__('Print Cart Delivery');
			$tmp .= '<img alt="'.__('Print Cart Delivery').'" src="'.Configure::read('App.img.cake').'/minetypes/32x32/pdf.png" border="0" /></a>';
			$tmp .= "</li>";
		}
		
		$tmp .= '<li>';
		$tmp .= '<a href="javascript:apriPopUp(\'/?option=com_cake&controller=PopUp&action=delivery_info&format=notmpl\')">';
		//$tmp .= '<div class="icon-question-sign"></div>';
		$tmp .= '<img alt="info" src="'.Configure::read('App.img.cake').'/actions/32x32/info.png" border="0" /></a>';
		$tmp .= "</li>";
		
		// in tabs_ajax_ecomm era if(!empty($result['ProdDelivery']['SuppliersOrganization'])) {
		if(isset($prod_delivery['totOrders']) && $prod_delivery['totOrders']>0) {
			// <img src="'.Configure::read('App.img.cake').'/apps/32x32/vcalendar.png" />
			$tmp .= '<li>';
			$tmp .= '<a href="javascript:viewCalendar('.$prod_delivery['id'].');">';
			// $tmp .= '<div class="icon-calendar"></div>';
			$tmp .= '<img alt="calendario" src="'.Configure::read('App.img.cake').'/apps/32x32/vcalendar.png" border="0" /></a>';
			$tmp .= "</li>";
		}
		$tmp .= "</ul>";
		
		$tmp .= '</div>';
		
		$tmp .= '</div><div class="clear"></div>';
		
		return $tmp;
	}

	/*
	 * $prod_delivery['nota_evidenza'] notice, alert, message
	 * 
	 * per la modalita' preview dal link della mail
	*/
	function messageDeliveryPreview($results, $user=null) {

		$tmp = '';
		
		$data = $this->Time->i18nFormat($results['ProdDelivery']['data'],"%a %e %b");
		
		$tmp .= '<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all" role="tablist">';
		$tmp .= '<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active" role="tab" tabindex="0" aria-controls="tabs-0" aria-labelledby="ui-id-1" aria-selected="true">';
		$tmp .= '<a>'.$data;
		$tmp .= '</a></li></ul>';
		
		$tmp .= '<div class="legenda legenda-'.strtolower($results['ProdDelivery']['nota_evidenza']).'">';
	
		if(strtolower($results['ProdDelivery']['nota_evidenza'])!='no')  {
				
			$tmp .= '<div id="type-message" class="';
			if(strtolower($results['ProdDelivery']['nota_evidenza'])=='message')
				$tmp .= 'icon-pin';
			else
			if(strtolower($results['ProdDelivery']['nota_evidenza'])=='notice')
				$tmp .= 'icon-warning-sign';
			else
			if(strtolower($results['ProdDelivery']['nota_evidenza'])=='alert')
				$tmp .= 'icon-danger';
				
			$tmp .= '"></div>';
	
		}
	
		$tmp .= '<div style="float:left; width:70%; padding-left:15px; font-size:14px;">';
		$tmp .= $results['ProdDelivery']['luogo'].', dalle ore '.$this->formatOrario($results['ProdDelivery']['orario_da']).' alle '.$this->formatOrario($results['ProdDelivery']['orario_a']);
	
		if(!empty($results['ProdDelivery']['nota']))
			$tmp .= '<p class="nota">'.$results['ProdDelivery']['nota'].'</p>';
	
		$tmp .= '</div>';
	
		// <img alt="info" src="'.Configure::read('App.img.cake').'/tooltips/24x24/help.png">
		$tmp .= "\n";
		$tmp .= '<div style="float:right;width:5%">';
	
		$tmp .= '<li>';
		$tmp .= '<a style="cursor:pointer;" rel="nofollow" class="cartPreview" title="stampa gli articoli della consegna in PDF">';
		$tmp .= '<div class="icon-archive"></div>';
		$tmp .= '</a>';
		$tmp .= "</li>";

		$tmp .= "</ul>";
	
		$tmp .= '</div>';
	
		$tmp .= '</div><div class="clear"></div>';
	
		return $tmp;
	}
	
	function messageNotProdDeliveries($msg="") {
		
		$tmp = '';
		$tmp .= '<div class="legenda legenda-message">';
		$tmp .= '<div id="type-message" class="message icon-pin"></div>';
		$tmp .= '<div style="float:left; width:70%; padding-left:15px; font-size:14px;">';
		if(!empty($msg))
			$tmp .= $msg;
		else
			$tmp .= 'Non ci sono ancora consegne';
		$tmp .= '</div>';
		$tmp .= '</div>';
		$tmp .= '</div>';		
		
		return $tmp;
	}

	function drawTabs($results) {
	
		$totTabs = count($results['Tab']);
	
		$tmp = '';
		$tmp .= '<ul>';
		foreach($results['Tab'] as $numTabs => $result) {
				
			if($result['data'] < date('Y-m-d')) $cssOldTime = "oldTime";
			else  $cssOldTime = "";
	
			if($totTabs > 5)
				$data = $this->Time->i18nFormat($result['data'],"%a %e %b");
			else
				$data = $this->Time->i18nFormat($result['data'],"%A %e %B");
	
			$tmp .= '<li><a href="#tabs-'.$numTabs.'" class="tabsDelivery '.$cssOldTime.'">'.$data.'</a></li>';
	
		}
		$tmp .= '</ul>';
	
		return $tmp;
	}

	function drawTabsAjax($results) {			$totTabs = count($results);			$tmp = '';		$tmp .= '<ul>';		foreach($results as $numTabs => $result) {
			if($result['ProdDelivery']['data'] < date('Y-m-d')) $cssOldTime = "oldTime";			else  $cssOldTime = "";				if($totTabs > 5)				$data = $this->Time->i18nFormat($result['ProdDelivery']['data'],"%a %e %b");			else				$data = $this->Time->i18nFormat($result['ProdDelivery']['data'],"%A %e %B");				$tmp .= '<li><a href="#tabs-'.$numTabs.'" class="tabsDelivery '.$cssOldTime.'" onClick="javascript:drawDelivery(\''.$result['ProdDelivery']['data'].'\', '.$numTabs.')">'.$data.'</a></li>';			}		$tmp .= '</ul>';			return $tmp;	}	
	function drawTabsUserCart($results, $storeroomResults=null) {

		$totTabs = count($results['Tab']);
		
		$tmp = '';	
		$tmp .= '<ul>';
		foreach($results['Tab'] as $numTabs => $result) {
			
			/*
			 * ctrl che almeno una consegna del tab abbia un acquisto (anche per la dispensa)
			 * */
			$continue = false;
			foreach($result['ProdDelivery'] as $numDelivery => $prod_delivery) { 
				if($prod_delivery['totArticlesOrder']>0) $continue = true;
				
			}
			
			if($continue) {
				if($result['data'] < date('Y-m-d')) $cssOldTime = "oldTime";
				else  $cssOldTime = "";
	
				if($totTabs > 5)
					$data = $this->Time->i18nFormat($result['data'],"%a %e %b");
				else
					$data = $this->Time->i18nFormat($result['data'],"%A %e %B");
			
				$tmp .= '<li><a href="#tabs-'.$numTabs.'" class='.$cssOldTime.'>'.$data.'</a></li>';
			}		
		}
		$tmp .= '</ul>';
		
		return $tmp;
	}
		
	public function setTableHeader($prod_prod_delivery_id, $user) {
		$tmp = '';
		$tmp .= '<table cellspacing="0" cellpadding="0">';
		$tmp .= "\n";
		$tmp .= '<thead>';
		$tmp .= '<tr>';
		$tmp .= "\n";
		$tmp .= '<th>N.</th>';
		$tmp .= "\n";
		$tmp .= '<th>'.__('Supplier').'</th>';
		$tmp .= "\n";
		$tmp .= '<th class="th-deliveryId'.$prod_prod_delivery_id.'-A" style="display:table-cell;">Scheda</th>';
		$tmp .= "\n";
		$tmp .= '<th class="th-deliveryId'.$prod_prod_delivery_id.'-A" style="display:table-cell;">Chiusura ordine</th>';
		$tmp .= "\n";
		$tmp .= '<th class="th-deliveryId'.$prod_prod_delivery_id.'-A" style="display:table-cell;" class="" ></th>';
		$tmp .= "\n";
		$tmp .= '<th class="th-deliveryId'.$prod_prod_delivery_id.'-A" style="display:table-cell;">Frequenza</th>';
		$tmp .= "\n";
		$tmp .= '<th class="th-deliveryId'.$prod_prod_delivery_id.'-A" style="display:table-cell;">Referenti</th>';
		$tmp .= "\n";
		if(isset($user) && $user->id > 0 && $user->organization_id == $user->organization['Organization']['id']) {
			$tmp .= '<th class="th-deliveryId'.$prod_prod_delivery_id.'-B dettaglioAcquisti" style="display:none;" colspan="5">Articoli acquistati</th>';
			$tmp .= '<th width="70">Acquisti<input type="checkbox" value="ALL" id="showHideAllCart_'.$prod_prod_delivery_id.'" name="showHideAllCart_'.$prod_prod_delivery_id.'" style="float:right;" /></th>';
			$tmp .= "\n";
		}
		$tmp .= '</tr></thead>';
		$tmp .= '<tbody>';

		return $tmp;
	}

	function setTableHeaderEcommCompleteFrontEnd($prod_prod_delivery_id) {
	
		$str = "";
		$str .= '<table cellspacing="0" cellpadding="0" id="tableList_'.$prod_prod_delivery_id.'" >';
		$str .= "\n";
		$str .= '<thead>';
		$str .= '<tr>';
		$str .= "\n";
		$str .= '<th></th>';
		$str .= "\n";
		$str .= '<th>N.</th>';
		$str .= "\n";
		$str .= '<th></th>';
		$str .= "\n";
		$str .= '<th>'.__('Article').'</th>';
		$str .= "\n";
		$str .= '<th style="width:16px;"></th>';
		$str .= "\n";
		$str .= '<th width="150px">'.__('qta').'</th>';
		$str .= "\n";
		$str .= '<th width="100px">'.__('Importo').'</th>';
		$str .= "\n";
		$str .= '</tr></thead>';
		$str .= '<tbody>';
	
		return $str;
	}
	
	function setTableHeaderEcommSimpleFrontEnd($prod_prod_delivery_id) {

		$str = "";
		$str .= '<table cellspacing="0" cellpadding="0" id="tableList_'.$prod_prod_delivery_id.'" >';
		$str .= "\n";
		$str .= '<thead>';
		$str .= '<tr>';
		$str .= "\n";
		$str .= '<th></th>';
		$str .= "\n";
		$str .= '<th>N.</th>';
		$str .= "\n";
		$str .= '<th>'.__('Bio').'</th>';
		$str .= "\n";
		$str .= '<th>'.__('Article').'</th>';
		$str .= "\n";
		$str .= '<th>'.__('Conf').'</th>';
		$str .= "\n";
		$str .= '<th>'.__('PrezzoUnita').'</th>';
		$str .= "\n";
		$str .= '<th>'.__('Prezzo/UM').'</th>';
		$str .= "\n";
		$str .= '<th>Min.</th>';
		$str .= "\n";
		$str .= '<th style="width:16px;"></th>';
		$str .= "\n";
		$str .= '<th width="150px">'.__('qta').'</th>';
		$str .= "\n";
		$str .= '<th width="100px">'.__('Importo').'</th>';
		$str .= "\n";
		$str .= '</tr></thead>';
		$str .= '<tbody>';

		return $str;
	}

	function drawTableHeaderBackOfficeReportUsers ($result, $permissions) {
	
		$str = "";
		
		$str .= "<script type='text/javascript'>
				jQuery(document).ready(function() {
		
					jQuery('.colsOrderBy').click(function() {
							var order_by = jQuery(this).attr('id');
							var prod_delivery_id = jQuery('#prod_delivery_id').val();
							var user_id     = jQuery('#user_id').val();
							var articlesOptions = jQuery(\"input[name='articles-options']:checked\").val();
							AjaxCallToArticlesResult(prod_delivery_id, user_id, articlesOptions, order_by);
					});
				});
				</script>
				";
				
		$str .= '<table cellspacing="0" cellpadding="0" id="tableList_'.$result['ProdDelivery']['prod_delivery_id'].'">';
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
	
		if($result['ProdDelivery']['permissionToEditProduttore'])  {
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
	
		if($result['ProdDelivery']['permissionToEditProduttore'])  {
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
							var prod_delivery_id = jQuery('#prod_delivery_id').val();
							AjaxCallToArticlesDetailsResult(prod_delivery_id, order_by);
					});
				});
				</script>
				";
		
		$str .= '<table cellspacing="0" cellpadding="0" id="tableList_'.$result['ProdDelivery']['prod_delivery_id'].'">';
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
		
		if($result['ProdDelivery']['permissionToEditProduttore'])  {
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

		if($result['ProdDelivery']['permissionToEditProduttore'])  {
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