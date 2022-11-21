<?php 
App::uses('UtilsCommons', 'Lib');

/*
 * $result['Order']['permission'] = ['permissionToEditUtente' => true, 
 * 							  	     'permissionToEditReferente' => false, 
 * 							  		 'permissionToEditCassiere' => false, 
 * 							   		 'permissionToEditTesoriere' => false];
 * 	Gestisce gli ORDER.state_code (ex PROCESSED-BEFORE-DELIVERY)
 *  
 *  
 * $permissions = ['isReferentGeneric' => $this->isReferentGeneric(),
 * 				   'isCassiereGeneric' => $this->isCassiereGeneric(),
 *	 			   'isTesoriereGeneric' => $this->isTesoriereGeneric()];
 *	Gestisce i RUOLI (ex group_id_referent)
 * */

class RowEcommHelper extends AppHelper {
		
	private $debug =  false;
	private $tabindex = 1;

	/*
	 * gestione con Article.img
	 */
	public function drawFrontEndComplete($numArticlesOrder, $order, $result, $options=[]) { 
			
		/*
		 * qta Cart
		 */
		if($result['Cart']['qta_forzato']==0)
			$qta = $result['Cart']['qta'];
		else
			$qta = $result['Cart']['qta_forzato'];
		if($qta>0) $classQta = "qtaUno";
		else $classQta = "qtaZero";
					
		/*
		 * importo Cart
		 */
		$importo_modificato = false;
		if(number_format($result['Cart']['importo_forzato'])==0) {
			if(number_format($result['Cart']['qta_forzato'])>0)
				$importo_cart = ($result['Cart']['qta_forzato'] * $result['ArticlesOrder']['prezzo']);
			else 
				$importo_cart = ($result['Cart']['qta'] * $result['ArticlesOrder']['prezzo']);
		}
		else {
			$importo_cart = $result['Cart']['importo_forzato'];
			$importo_modificato = true;
		}

	
		$tmp = "";

		$rowId = $this->_getRowId($numArticlesOrder, $result);

		$k=0;
		if(!isset($options['tr.no_display'])) {
			$tmp .= "\n";
			$tmp .= '<div class="col-md-3 col-sm-4 col-xs-12">';
			$tmp .= '<div class="c_item row'.$k.' suppliersOrganizationId'.$order['Order']['supplier_organization_id'].' rowEcomm" id="row-'.$rowId.'" style="display:block;">';
		}
		
		$tmp .= "\n";
		if($this->debug) 
			$type_input = 'text';
		else 
			$type_input = 'hidden';
		
		$tmp .= "\n";
		$tmp .= '<input class="debug form-control" type="hidden" value="'.$result['ArticlesOrder']['order_id'].'" id="order_id-'.$rowId.'" />';
		$tmp .= "\n";
		$tmp .= '<input class="debug form-control" type="hidden" value="'.$result['ArticlesOrder']['prezzo'].'" id="prezzo-'.$rowId.'" />';
		
		if($this->debug) $tmp .= 'AO.stato';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['stato'].'" id="articleOrder_stato-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'AO.article_organization_id';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['article_organization_id'].'" id="article_organization_id-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'AO.article_id';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['article_id'].'" id="article_id-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= '<br />AO.qta_cart';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['qta_cart'].'" id="qta_cart-'.$rowId.'" />';   // la qta_cart serve solo per il ctrl js, in Model/AjaxGasCart e' ricalcolato
		$tmp .= "\n";
		if($this->debug) $tmp .= 'AO.qta_min';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['qta_minima'].'" id="qta_minima-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'AO.qta_max';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['qta_massima'].'" id="qta_massima-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'AO.qta_min_order';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['qta_minima_order'].'" id="qta_minima_order-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'AO.qta_max_order';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['qta_massima_order'].'" id="qta_massima_order-'.$rowId.'" />';  
		$tmp .= "\n";
		if($this->debug) $tmp .= 'AO.qta_mult';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['qta_multipli'].'" id="qta_multipli-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'C.qta_prima_mod';
		if(empty($result['Cart']['qta'])) $result['Cart']['qta']=0;
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['Cart']['qta'].'" id="qta_prima_modifica-'.$rowId.'" />';  // serve in caso ArticlesOrder.stato = QTAMAXORDER o Carts.stato = LOCK per bloccare il tasto +
		$tmp .= "\n";
		
		$tmp .= '<div class="cart-img">';
		if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
			$info = getimagesize(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1']);
			$width = $info[0];
			$height = $info[1];			
			if($height > 150) $height = '150';
			
			$tmp .= '<img style="height:'.$height.'px" class="img-responsive-disabled" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';
		}
		
		if(empty($importo_cart)) // Prezzo unità __('PrezzoUnita')
			$tmp .= '<p class="c_prezzo">'.$result['ArticlesOrder']['prezzo_'].'<span class="c_currency">€</span></p>';
		else 
			$tmp .= '<p class="c_prezzo cart">'.number_format($importo_cart,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'<span class="s_currency s_after">€</span></p>';
		
		if($result['Article']['bio']=='Y') 
			$tmp .= '<p class="bio" title="'.Configure::read('bio').'"></p>';
		$tmp .= "</div>";
		
		$tmp .= '<p class="title">'.$result['ArticlesOrder']['name'].'</p>';
		
		$tmp .= '<p id="msgEcomm-'.$rowId.'" class="msgEcomm"></p>';
		
		if($result['Article']['qta']>0)  // Conf
			$tmp .= '<p class="details-left"><strong>'.__('Conf').'</strong> '.$this->getArticleConf($result['Article']['qta'], $this->traslateEnum($result['Article']['um'])).'</p>';
		
		$tmp .= '<p class="details-right"><strong>'.__('Prezzo').'</strong> '.$result['ArticlesOrder']['prezzo_e'].'</p>';
		
		$tmp .= '<div class="c_actions">';  // Prezzo/UM
			
	   /* 
	    * C A R T
	    */
		if($order['Order']['permissionToEditUtente']) {
			// $tmp .= $this->_ordineModificabileFrontEnd($rowId, $result);  // ordine modificabile (attivo)
		
				$tmp .= "\n";  // ArticlesOrder.Stato 'Y', 'N', 'LOCK', 'QTAMAXORDER'
				if($result['ArticlesOrder']['stato']!='Y') {	
					$tmp .= '<div ';
					$tmp .= ' class="stato_'.strtolower($result['ArticlesOrder']['stato']).'" title="Stato dell\'articolo associato all\'ordine: '.$this->traslateEnum($result['ArticlesOrder']['stato']).'"';
					$tmp .= '></div>';
				}

				$tmp .= '<div class="col-cart-md-6">';				
				$tmp .= '<div id="buttonPiuMeno-'.$rowId.'" class="buttonPiuMeno" style="float: none;display:none;">';
				$tmp .= '<img alt="compra in +" src="'.Configure::read('App.img.cake').'/button-piu.png" class="buttonCarrello buttonPiu" />';
				$tmp .= '<img alt="compra in -" src="'.Configure::read('App.img.cake').'/button-meno.png" class="buttonCarrello buttonMeno" />';
				$tmp .= '</div>';
				$tmp .= '</div>';
												
				$tmp .= "\n";  // Qta
				$tmp .= '<div class="col-cart-md-2">';				
				$tmp .= '<div id="qta-'.$rowId.'" class="'.$classQta.' qta">';
				if($qta>0) $tmp .= $qta;
				$tmp .= '</div>';
				$tmp .= '</div>';
				
				$tmp .= '<div class="col-cart-md-4">';
				$tmp .= "\n";
				$tmp .= '<div id="prezzoNew-'.$rowId.'" class="prezzoNewALL prezzoNew-'.$result['ArticlesOrder']['order_id'].'" data-attr-prezzoNewALL="'.$importo_cart.'" data-attr-prezzoNew-'.$result['ArticlesOrder']['order_id'].'="'.$importo_cart.'">';
				if(!empty($importo_cart)) 
					$tmp .= number_format($importo_cart,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
				$tmp .= '</div>';
				$tmp .= '</div>';
				
				$tmp .= '<div class="col-cart-md-12">';
				$tmp .= '<img alt="" src="'.Configure::read('App.img.cake').'/blank32x32.png" id="submitEcomm-'.$rowId.'" class="buttonCarrello submitEcomm" />';
				$tmp .= '</div>';
		}
		else 
			$tmp .= $this->_ordineNonModificabileFrontEnd($rowId, $result); // ordine non modificabile (scaduto)

		$tmp .= '<p class="details-left"><strong>'.__('Prezzo/UM').'</strong> '.$this->getArticlePrezzoUM($result['ArticlesOrder']['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']).'</p>';
		
		// Qta minima
		$tmp .= '<p class="details-left"><strong>Min.</strong> '.sprintf("%5.2f",$result['ArticlesOrder']['qta_minima']).'</p>';
		
		// Qta massima
		$tmp .= '<p class="details-right"><strong>Max.</strong> '.sprintf("%5.2f",$result['ArticlesOrder']['qta_massima']).'</p>';

		$tmp .= '<p class="clearfix">'.$this->drawArticleNotaAjax($rowId, strip_tags($result['Article']['nota'])).'</p>';
		
		/*
		 * dettaglio
		 */
		$tmp .= '<p class="details-right"><a id="actionTrView-'.$rowId.'" action="articles_order_no_img-'.$rowId.'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"><i class="fa fa-search-plus fa-2x" /></a></p>';
		$tmp .= '<div class="clearfix"></div>';
		$tmp .= '<div id="tdViewId-'.$rowId.'"></div>'; // style="height:25px;width:25px;"

		$tmp .= '</div>';  // c_action
				
		if(!isset($options['tr.no_display'])) {
			$tmp .= '</div>';
			$tmp .= '</div>';
		}
		else {
			$tmp .= $this->_draw_js_frontend($rowId, $result['ArticlesOrder']['order_id']);
		}
				
		$k = 1 - $k;
		
		return $tmp;
	}

	/*
	 * gestione con Article.img in rows
	*/
	public function drawFrontEndSimple($numArticlesOrder, $order, $result, $options=[]) {
	
		$tmp = "";
	
		$rowId = $this->_getRowId($numArticlesOrder, $result);
	
		$k=0;
		if(!isset($options['tr.no_display'])) {
			$tmp .= "\n";
			$tmp .= '<tr class="row'.$k.' suppliersOrganizationId'.$order['Order']['supplier_organization_id'].' rowEcomm" id="row-'.$rowId.'" style="display:table-row;">';
		}
	
		$tmp .= "\n";
		if($this->debug) {
			$tmp .= '<td width="400px">';
			$type_input = 'text';
		}
		else {
			$tmp .= '<td>';
			$type_input = 'hidden';
		}
		
		$tmp .= "\n";
		$tmp .= '<input class="debug form-control" type="hidden" value="'.$result['ArticlesOrder']['order_id'].'" id="order_id-'.$rowId.'" />';
		$tmp .= "\n";
		$tmp .= '<input class="debug form-control" type="hidden" value="'.$result['ArticlesOrder']['prezzo'].'" id="prezzo-'.$rowId.'" />';
	
		if($this->debug) $tmp .= 'AO.stato';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['stato'].'" id="articleOrder_stato-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'AO.article_organization_id';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['article_organization_id'].'" id="article_organization_id-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'AO.article_id';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['article_id'].'" id="article_id-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= '<br />AO.qta_cart';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['qta_cart'].'" id="qta_cart-'.$rowId.'" />';   // la qta_cart serve solo per il ctrl js, in Model/AjaxGasCart e' ricalcolato
		$tmp .= "\n";
		if($this->debug) $tmp .= 'AO.qta_min';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['qta_minima'].'" id="qta_minima-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'AO.qta_max';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['qta_massima'].'" id="qta_massima-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'AO.qta_min_order';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['qta_minima_order'].'" id="qta_minima_order-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'AO.qta_max_order';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['qta_massima_order'].'" id="qta_massima_order-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'AO.qta_mult';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['qta_multipli'].'" id="qta_multipli-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'C.qta_prima_mod';
		if(empty($result['Cart']['qta'])) $result['Cart']['qta']=0;
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['Cart']['qta'].'" id="qta_prima_modifica-'.$rowId.'" />';  // serve in caso ArticlesOrder.stato = QTAMAXORDER o Carts.stato = LOCK per bloccare il tasto +
		$tmp .= "\n";
	
		$tmp .= '<a id="actionTrView-'.$rowId.'" action="articles_order-'.$rowId.'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"><i class="fa fa-search-plus fa-2x"></a></td>';
	
		$tmp .= '<td>'.($numArticlesOrder+1).'</td>';
		$tmp .= "\n";
		$tmp .= '<td class="hidden-xs hiddex-sm">';
		if($result['Article']['bio']=='Y')
			$tmp .= '<span class="bio" title="'.Configure::read('bio').'"></span>';
		else
			$tmp .= "";
		$tmp .= '</td>';
		$tmp .= "\n";
		$tmp .= '<td>';
		$tmp .= '<div class="cart-img">';
		if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
			$info = getimagesize(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1']);
			$width = $info[0];
			$height = $info[1];			
			if($height > 150) $height = '150';
			
			$tmp .= '<img style="height:'.$height.'px" class="img-responsive-disabled" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';
		}	
		$tmp .= '</div>';		
		$tmp .= $result['ArticlesOrder']['name'];
		$tmp .= $this->drawArticleNotaAjax($rowId, strip_tags($result['Article']['nota']));
		$tmp .= '</td>';
	
		$tmp .= "\n";  // Conf
		$tmp .= '<td style="white-space: nowrap;">';
		if($result['Article']['qta']>0)
			$tmp .= $this->getArticleConf($result['Article']['qta'], $this->traslateEnum($result['Article']['um']));
		$tmp .= '</td>';
		$tmp .= "\n";  // Prezzo unità
		$tmp .= '<td style="white-space: nowrap;">';
		$tmp .= $result['ArticlesOrder']['prezzo_e'];
		$tmp .= '</td>';
		$tmp .= "\n";  // Prezzo/UM
		$tmp .= '<td class="hidden-xs hidden-sm" style="white-space: nowrap;">';
		$tmp .= $this->getArticlePrezzoUM($result['ArticlesOrder']['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']);
		$tmp .= '</td>';
		$tmp .= "\n";  // Qta minima
		$tmp .= '<td class="hidden-xs hidden-sm" style="white-space: nowrap;">';
		$tmp .= sprintf("%5.2f",$result['ArticlesOrder']['qta_minima']);
		$tmp .= '</td>';
	
		/*
		 * C A R T
		*/
		if($order['Order']['permissionToEditUtente'])
			$tmp .= $this->_ordineModificabileFrontEnd($rowId, $result);  // ordine modificabile (attivo)
		else
			$tmp .= $this->_ordineNonModificabileFrontEnd($rowId, $result); // ordine non modificabile (scaduto)

		if(isset($options['tr.no_display'])) 
			$tmp  .= $this->_draw_js_frontend($rowId, $result['ArticlesOrder']['order_id']);
		else {		
			$tmp .= '</tr>';
	
			$tmp .= '<tr class="trView" id="trViewId-'.$rowId.'">';
			$tmp .= '<td colspan="2"></td>';
			$tmp .= '<td colspan="11" id="tdViewId-'.$rowId.'"></td>';
			$tmp .= '</tr>';
		}
	
		$k = 1 - $k;
	
		return $tmp;
	}

	/*
	 * gestione senza con Article.img
	 * in versione preview, dal link della mail: non posso modificare gli acquisti, devo effettuare la login
	*/
	public function drawFrontEndPreviewSimple($numArticlesOrder, $order, $result, $options=[]) {
	
		$tmp = "";
	
		$rowId = $this->_getRowId($numArticlesOrder, $result);
	
		$k=0;
		if(!isset($options['tr.no_display'])) {
			$tmp .= "\n";
			$tmp .= '<tr class="row'.$k.' suppliersOrganizationId'.$order['Order']['supplier_organization_id'].' rowEcomm" id="row-'.$rowId.'" style="display:table-row;">';
		}
	
		$tmp .= '<td>';
		$tmp .= '<a id="actionTrView-'.$rowId.'" action="articles_order-'.$rowId.'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
	
		$tmp .= '<td>'.($numArticlesOrder+1).'</td>';
		$tmp .= "\n";
		$tmp .= '<td>';
		if($result['Article']['bio']=='Y')
			$tmp .= '<span class="bio" title="'.Configure::read('bio').'"></span>';
		else
			$tmp .= "";
		$tmp .= '</td>';
		$tmp .= "\n";
		$tmp .= '<td>';
		$tmp .= $result['ArticlesOrder']['name'];
		$tmp .= $this->drawArticleNotaAjax($rowId, strip_tags($result['Article']['nota']));
		$tmp .= '</td>';
	
		$tmp .= "\n";  // Conf
		$tmp .= '<td style="white-space: nowrap;">';
		if($result['Article']['qta']>0)
			$tmp .= $this->getArticleConf($result['Article']['qta'], $this->traslateEnum($result['Article']['um']));
		$tmp .= '</td>';
		$tmp .= "\n";  // Prezzo unità
		$tmp .= '<td style="white-space: nowrap;">';
		$tmp .= $result['ArticlesOrder']['prezzo_e'];
		$tmp .= '</td>';
		$tmp .= "\n";  // Prezzo/UM
		$tmp .= '<td style="white-space: nowrap;">';
		$tmp .= $this->getArticlePrezzoUM($result['ArticlesOrder']['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']);
		$tmp .= '</td>';
		$tmp .= "\n";  // Qta minima
		$tmp .= '<td style="white-space: nowrap;">';
		$tmp .= sprintf("%5.2f",$result['ArticlesOrder']['qta_minima']);
		$tmp .= '</td>';
	
		/*
		 * C A R T
		*/
		$tmp .= $this->_ordinePreviewFrontEnd($rowId, $result); // ordine non modificabile
	
		if(isset($options['tr.no_display']))
			$tmp  .= $this->_draw_js_frontend($rowId, $result['ArticlesOrder']['order_id']);
		else {
			$tmp .= '</tr>';
	
			$tmp .= '<tr class="trView" id="trViewId-'.$rowId.'">';
			$tmp .= '<td colspan="2"></td>';
			$tmp .= '<td colspan="11" id="tdViewId-'.$rowId.'"></td>';
			$tmp .= '</tr>';
		}
	
		$k = 1 - $k;
	
		return $tmp;
	}
	
	/*
	 * header table per gli acquisti da validate (ArticlesOrder.pezzi_confezione > 1)
	 * gestione senza con Article.img
	*/
	public function drawFrontEndCartsValidationSimple($numArticlesOrder, $order, $result, $options=[]) {
		
		$tmp = "";
	
		$rowId = $this->_getRowId($numArticlesOrder, $result);
	
		$k=0;
		if(!isset($options['tr.no_display'])) {
			$tmp .= "\n";
			$tmp .= '<tr class="row'.$k.' suppliersOrganizationId'.$order['Order']['supplier_organization_id'].' rowEcomm" id="row-'.$rowId.'" style="display:table-row;">';
		}
	
		$tmp .= "\n";
		if($this->debug) {
			$tmp .= '<td width="400px">';
			$type_input = 'text';
		}
		else {
			$tmp .= '<td>';
			$type_input = 'hidden';
		}
	
		$tmp .= "\n";
		$tmp .= '<input class="debug form-control" type="hidden" value="'.$result['ArticlesOrder']['order_id'].'" id="order_id-'.$rowId.'" />';
		$tmp .= "\n";
		$tmp .= '<input class="debug form-control" type="hidden" value="'.$result['ArticlesOrder']['prezzo'].'" id="prezzo-'.$rowId.'" />';
	
		if($this->debug) $tmp .= 'AO.stato';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['stato'].'" id="articleOrder_stato-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'AO.article_organization_id';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['article_organization_id'].'" id="article_organization_id-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'AO.article_id';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['article_id'].'" id="article_id-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= '<br />AO.qta_cart';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['qta_cart'].'" id="qta_cart-'.$rowId.'" />';   // la qta_cart serve solo per il ctrl js, in Model/AjaxGasCart e' ricalcolato
		$tmp .= "\n";
		if($this->debug) $tmp .= 'AO.qta_min';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['qta_minima'].'" id="qta_minima-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'AO.qta_max';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['qta_massima'].'" id="qta_massima-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'AO.qta_min_order';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['qta_minima_order'].'" id="qta_minima_order-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'AO.qta_max_order';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['qta_massima_order'].'" id="qta_massima_order-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'AO.qta_mult';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['qta_multipli'].'" id="qta_multipli-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'C.qta_prima_mod';
		if(empty($result['Cart']['qta'])) $result['Cart']['qta']=0;
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['Cart']['qta'].'" id="qta_prima_modifica-'.$rowId.'" />';  // serve in caso ArticlesOrder.stato = QTAMAXORDER o Carts.stato = LOCK per bloccare il tasto +
		$tmp .= "\n";
	
		$tmp .= '<a id="actionTrView-'.$rowId.'" action="articles_order-'.$rowId.'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
	
		$tmp .= '<td>'.($numArticlesOrder+1).'</td>';
		$tmp .= "\n";
		$tmp .= '<td>';
		if($result['Article']['bio']=='Y')
			$tmp .= '<span class="bio" title="'.Configure::read('bio').'"></span>';
		else
			$tmp .= "";
		$tmp .= '</td>';
		$tmp .= "\n";
		$tmp .= '<td>';
		$tmp .= $result['ArticlesOrder']['name'];
		// $tmp .= $this->drawArticleNotaAjax($rowId, strip_tags($result['Article']['nota']));
		$tmp .= '</td>';
	
		$tmp .= "\n";  // Conf
		$tmp .= '<td style="white-space: nowrap;">';
		if($result['Article']['qta']>0)
			$tmp .= $this->getArticleConf($result['Article']['qta'], $this->traslateEnum($result['Article']['um']));
		$tmp .= '</td>';
		
		$tmp .= "\n";  // Prezzo unità
		$tmp .= '<td style="white-space: nowrap;">';
		$tmp .= $result['ArticlesOrder']['prezzo_e'];
		$tmp .= '</td>';
		$tmp .= "\n";  // Prezzo/UM
		$tmp .= '<td style="white-space: nowrap;">';
		$tmp .= $this->getArticlePrezzoUM($result['ArticlesOrder']['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']);
		$tmp .= '</td>';
		$tmp .= "\n";  // Qta minima
		$tmp .= '<td style="white-space: nowrap;">';
		$tmp .= sprintf("%5.2f",$result['ArticlesOrder']['qta_minima']);
		$tmp .= '</td>';

		/*
		 * C A R T
		*/
		$tmp .= $this->_ordineModificabileFrontEndCartsValidation($rowId, $result);  // ordine modificabile (attivo)

		if(isset($options['tr.no_display'])) 
			$tmp  .= $this->_draw_js_frontend($rowId, $result['ArticlesOrder']['order_id']);
		else {	
			$tmp .= '</tr>';
	
			$tmp .= '<tr class="trView" id="trViewId-'.$rowId.'">';
			$tmp .= '<td colspan="2"></td>';
			$tmp .= '<td colspan="11" id="tdViewId-'.$rowId.'"></td>';
			$tmp .= '</tr>';
		}
	
		$k = 1 - $k;
	
		return $tmp;
	}

	/*
	 * header table per gli acquisti da ProdGasPromotion
	*/
	public function drawFrontEndPromotion($numArticlesOrder, $order, $result, $options=[]) {
	
		$tmp = "";
	
		$rowId = $this->_getRowId($numArticlesOrder, $result);
	
		$k=0;
		if(!isset($options['tr.no_display'])) {
			$tmp .= "\n";
			$tmp .= '<tr class="row'.$k.' suppliersOrganizationId'.$order['Order']['supplier_organization_id'].' rowEcomm" id="row-'.$rowId.'" style="display:table-row;">';
		}
	
		$tmp .= "\n";
		if($this->debug) {
			$tmp .= '<td width="400px">';
			$type_input = 'text';
		}
		else {
			$tmp .= '<td>';
			$type_input = 'hidden';
		}
	
		$tmp .= "\n";
		$tmp .= '<input class="debug form-control" type="hidden" value="'.$result['ArticlesOrder']['order_id'].'" id="order_id-'.$rowId.'" />';
		$tmp .= "\n";
		$tmp .= '<input class="debug form-control" type="hidden" value="'.$result['ArticlesOrder']['prezzo'].'" id="prezzo-'.$rowId.'" />';
	
		if($this->debug) $tmp .= 'AO.stato';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['stato'].'" id="articleOrder_stato-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'AO.article_organization_id';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['article_organization_id'].'" id="article_organization_id-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'AO.article_id';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['article_id'].'" id="article_id-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= '<br />AO.qta_cart';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['qta_cart'].'" id="qta_cart-'.$rowId.'" />';   // la qta_cart serve solo per il ctrl js, in Model/AjaxGasCart e' ricalcolato
		$tmp .= "\n";
		if($this->debug) $tmp .= 'AO.qta_min';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['qta_minima'].'" id="qta_minima-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'AO.qta_max';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['qta_massima'].'" id="qta_massima-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'AO.qta_min_order';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['qta_minima_order'].'" id="qta_minima_order-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'AO.qta_max_order';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['qta_massima_order'].'" id="qta_massima_order-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'AO.qta_mult';
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['qta_multipli'].'" id="qta_multipli-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'C.qta_prima_mod';
		if(empty($result['Cart']['qta'])) $result['Cart']['qta']=0;
		$tmp .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['Cart']['qta'].'" id="qta_prima_modifica-'.$rowId.'" />';  // serve in caso ArticlesOrder.stato = QTAMAXORDER o Carts.stato = LOCK per bloccare il tasto +
		$tmp .= "\n";
	
		$tmp .= '<a id="actionTrView-'.$rowId.'" action="articles_order-'.$rowId.'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
	
		$tmp .= '<td>'.($numArticlesOrder+1).'</td>';
		$tmp .= "\n";
		$tmp .= '<td>';
		if($result['Article']['bio']=='Y')
			$tmp .= '<span class="bio" title="'.Configure::read('bio').'"></span>';
		else
			$tmp .= "";
		$tmp .= '</td>';
		$tmp .= "\n";
		$tmp .= '<td>'; 
		$tmp .= '<div class="cart-img">';
		if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
			$info = getimagesize(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1']);
			$width = $info[0];
			$height = $info[1];			
			if($height > 150) $height = '150';
			
			$tmp .= '<img style="height:'.$height.'px" class="img-responsive-disabled" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';
		}	
		$tmp .= '</div>';
		$tmp .= $result['ArticlesOrder']['name'];
		// $tmp .= $this->drawArticleNotaAjax($rowId, strip_tags($result['Article']['nota']));
		$tmp .= '</td>';
	
		$tmp .= "\n";  // Conf
		$tmp .= '<td style="white-space: nowrap;">';
		if($result['Article']['qta']>0)
			$tmp .= $this->getArticleConf($result['Article']['qta'], $this->traslateEnum($result['Article']['um']));
		$tmp .= '</td>';
		
		$tmp .= "\n";  // Prezzo unita' in pormozione
		$tmp .= '<td style="white-space: nowrap;">';
		$tmp .= '<span style="text-decoration: line-through;">'.$result['Article']['prezzo_e'].'</span>';
		$tmp .= '<br />';
		$tmp .= $result['ProdGasArticlesPromotion']['prezzo_unita_e'];		
		$tmp .= '</td>';
		
		$tmp .= "\n";  // Prezzo/UM
		$tmp .= '<td style="white-space: nowrap;">';
		$tmp .= '<span style="text-decoration: line-through;">'.$this->getArticlePrezzoUM($result['Article']['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']).'</span>';
		$tmp .= '<br />';
		$tmp .= $this->getArticlePrezzoUM($result['ProdGasArticlesPromotion']['prezzo_unita'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']);
		$tmp .= '</td>';
		
		/*
		 * C A R T
		*/
		$tmp .= $this->_ordineModificabileFrontEndPromotion($rowId, $result);  // ordine modificabile (attivo)

		if(isset($options['tr.no_display'])) 
			$tmp  .= $this->_draw_js_frontend($rowId, $result['ArticlesOrder']['order_id']);
		else {	
			$tmp .= '</tr>';
	
			$tmp .= '<tr class="trView" id="trViewId-'.$rowId.'">';
			$tmp .= '<td colspan="2"></td>';
			$tmp .= '<td colspan="9" id="tdViewId-'.$rowId.'"></td>';
			$tmp .= '</tr>';
		}
	
		$k = 1 - $k;
	
		return $tmp;
	}
	
	/*
	 * solo per backoffice
	 */
	function prepareResult($numArticlesOrder, $order) {
		/*
		echo "<pre>";
		print_r($order['Order']);
		echo "</pre>";
		*/
		if(isset($order['SuppliersOrganization'])) $suppliersOrganization = $order['SuppliersOrganization'];
		else $suppliersOrganization = [];
	
		if(isset($order['SuppliersOrganizationsReferent'])) $suppliersOrganizationsReferent = $order['SuppliersOrganizationsReferent'];
		else $suppliersOrganizationsReferent = [];
	
		if(!isset($order['Cart'][$numArticlesOrder])) {
			$order['Cart'][$numArticlesOrder]['qta'] = 0;
			$order['Cart'][$numArticlesOrder]['qta_forzato'] = 0;
			$order['Cart'][$numArticlesOrder]['importo_forzato'] = 0;
		}
		if(!isset($order['User'][$numArticlesOrder])) {
			$order['User'][$numArticlesOrder]['id'] = 0;
		}
	
		$result = array('Order' => $order['Order'],
				'ArticlesOrder' => $order['ArticlesOrder'][$numArticlesOrder],
				'Article' => $order['Article'][$numArticlesOrder],
				'Cart' => $order['Cart'][$numArticlesOrder],
				'User' => $order['User'][$numArticlesOrder],
				'SummaryOrder' => $order['SummaryOrder'][$numArticlesOrder],
				'SuppliersOrganization' => $suppliersOrganization,
				'SuppliersOrganizationsReferent' => $suppliersOrganizationsReferent);
	
		return $result;
	}
	
	public function drawBackOfficeReportUsers($user, $numArticlesOrder, $result, $permissions, $options=[]) {
	
		$rowId = $this->_getRowId($numArticlesOrder, $result);
		// debug($user->organization['Organization']);		
		$tmp = "";
		
		$k=0;
		if(!isset($options['tr.no_display'])) {
			$tmp  .= "\n";
			$tmp  .= '<tr class="row'.$k.' suppliersOrganizationId'.$result['Order']['supplier_organization_id'].' rowEcomm" id="row-'.$rowId.'" style="display:table-row;">';
		}
		
		$tmp  .= $this->_drawBackOfficeFieldsHidden($rowId, $numArticlesOrder, $result, $permissions, $options);
		
		$tmp  .= '<a id="actionTrView-'.$rowId.'" action="articles_order-'.$rowId.'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
	
		$tmp  .= '<td>'.($numArticlesOrder+1).'</td>';
		
		if($user->organization['Organization']['hasFieldArticleCodice']=='Y') {
			$tmp  .= '<td>';
			$tmp  .= $result['Article']['codice'];
			$tmp  .= '</td>';
		}

		$tmp  .= "\n";
		$tmp  .= '<td class="'; 
		if($result['Cart']['deleteToReferent']=='Y')
			$tmp  .= 'deleteToReferent';
		$tmp  .= '">';
		$tmp  .= $result['ArticlesOrder']['name'];
		//$tmp  .= $this->drawArticleNotaAjax($rowId, strip_tags($result['Article']['nota']));
		$tmp  .= '</td>';
		
		$tmp  .= "\n";  // Conf.
		$tmp  .= '<td style="white-space: nowrap;">';
		if($result['Article']['qta']>0)
			$tmp  .= $this->getArticleConf($result['Article']['qta'], $this->traslateEnum($result['Article']['um']));
		$tmp  .= '</td>';
		$tmp  .= "\n"; // Prezzo unità
		$tmp  .= '<td style="white-space: nowrap;">';
		$tmp  .= $result['ArticlesOrder']['prezzo_e'];
		$tmp  .= '</td>';
		$tmp  .= "\n";  // Prezzo/UM
		$tmp  .= '<td style="white-space: nowrap;">';
		$tmp  .= $this->getArticlePrezzoUM($result['ArticlesOrder']['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']);
		$tmp  .= '</td>';
		$tmp  .= "\n";
	
		$tmp  .= $this->_drawBackOfficeCart($rowId, $numArticlesOrder, $result, $permissions, $options);

		if(isset($options['tr.no_display'])) 
			$tmp  .= $this->_draw_js_backoffice($rowId);
		
		$k = 1 - $k;
				
		return $tmp;
	}

	public function drawBackOfficeReportArticlesDetails($user, $numArticlesOrder, $result, $permissions, $options=[]) {
	
		$rowId = $this->_getRowId($numArticlesOrder, $result);

		$tmp = "";
		
		$k=0;
		if(!isset($options['tr.no_display'])) {  
			$tmp  .= "\n";
			$tmp  .= '<tr class="row'.$k.' suppliersOrganizationId'.$result['Order']['supplier_organization_id'].' rowEcomm" id="row-'.$rowId.'" style="display:table-row;">';
		}
	
		$tmp  .= $this->_drawBackOfficeFieldsHidden($rowId, $numArticlesOrder, $result, $permissions, $options);
		
		$tmp  .= '<a id="actionTrView-'.$rowId.'" action="articles_order-'.$rowId.'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
	
		$tmp  .= '<td>'.($numArticlesOrder+1).'</td>';
		$tmp  .= "\n";

		if($user->organization['Organization']['hasFieldArticleCodice']=='Y') {
			$tmp  .= '<td>';
			$tmp  .= $result['Article']['codice'];
			$tmp  .= '</td>';
		}
				
		$tmp  .= '<td class="'; 
		if($result['Cart']['deleteToReferent']=='Y')
			$tmp  .= 'deleteToReferent';
		$tmp  .= '">';
		$tmp  .= $result['ArticlesOrder']['name'];
		//$tmp  .= $this->drawArticleNotaAjax($rowId, strip_tags($result['Article']['nota']));
		$tmp  .= '</td>';
	
		$tmp  .= "\n";
		$tmp  .= '<td class="';
		if($result['Cart']['deleteToReferent']=='Y')
			$tmp  .= 'deleteToReferent';
		$tmp  .= '">';
		$tmp  .= $result['User']['name'].'</td>';
		$tmp  .= "\n";
		$tmp  .= '<td>'.$this->Time->i18nFormat($result['Cart']['date'],"%e %B %R").'</td>';
		/*	
		$tmp  .= "\n";  // Conf.
		$tmp  .= '<td style="white-space: nowrap;">';
		if($result['Article']['qta']>0)
			$tmp  .= $this->getArticleConf($result['Article']['qta'], $this->traslateEnum($result['Article']['um']));
		$tmp  .= '</td>';
		$tmp  .= "\n"; // Prezzo unità
		$tmp  .= '<td style="white-space: nowrap;">';
		$tmp  .= $result['ArticlesOrder']['prezzo_e'];
		$tmp  .= '</td>';
		$tmp  .= "\n";  // Prezzo/UM
		$tmp  .= '<td style="white-space: nowrap;">';
		$tmp  .= $this->getArticlePrezzoUM($result['ArticlesOrder']['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']);
		$tmp  .= '</td>';
		$tmp  .= "\n";
		*/
		$tmp  .= $this->_drawBackOfficeCart($rowId, $numArticlesOrder, $result, $permissions, $options);
	
		if(isset($options['tr.no_display']))
			$tmp  .= $this->_draw_js_backoffice($rowId);
		
		$k = 1 - $k;
		
		return $tmp;
	}
	
	private function _drawBackOfficeFieldsHidden($rowId, $numArticlesOrder, $result, $permissions, $options) {

		$tmp  = "";
		$tmp  .= "\n";
		if($this->debug) {
			$tmp  .= '<td width="400px">';
			$type_input = 'text';
		}
		else {
			$tmp  .= '<td>';
			$type_input = 'hidden';
		}
		
		$tmp  .= "\n";
		$tmp  .= '<input class="debug form-control" type="hidden" value="'.$result['ArticlesOrder']['order_id'].'" id="order_id-'.$rowId.'" />';
		$tmp  .= "\n";
		$tmp  .= '<input class="debug form-control" type="hidden" value="'.$result['ArticlesOrder']['prezzo'].'" id="prezzo-'.$rowId.'" />';
		
		$tmp  .= "\n";
		if($this->debug) $tmp  .= 'AO.stato';
		$tmp  .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['stato'].'" id="articleOrder_stato-'.$rowId.'" />';
		$tmp  .= "\n";
		if($this->debug) $tmp  .= 'AO.article_organization_id';
		$tmp  .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['article_organization_id'].'" id="article_organization_id-'.$rowId.'" />';
		$tmp  .= "\n";
		if($this->debug) $tmp  .= 'AO.article_id';
		$tmp  .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['article_id'].'" id="article_id-'.$rowId.'" />';
		$tmp  .= "\n";
		if($this->debug) $tmp  .= '<br />AO.qta_cart';
		$tmp  .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['qta_cart'].'" id="qta_cart-'.$rowId.'" />';   // la qta_cart serve solo per il ctrl js, in Model/AjaxGasCart e' ricalcolato
		$tmp  .= "\n";
		if($this->debug) $tmp  .= 'AO.qta_min';
		$tmp  .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['qta_minima'].'" id="qta_minima-'.$rowId.'" />';
		$tmp  .= "\n";
		if($this->debug) $tmp  .= 'AO.qta_max';
		$tmp  .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['qta_massima'].'" id="qta_massima-'.$rowId.'" />';
		$tmp  .= "\n";
		if($this->debug) $tmp  .= 'AO.qta_min_order';
		$tmp  .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['qta_minima_order'].'" id="qta_minima_order-'.$rowId.'" />';
		$tmp  .= "\n";
		if($this->debug) $tmp  .= 'AO.qta_max_order';
		$tmp  .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['qta_massima_order'].'" id="qta_massima_order-'.$rowId.'" />';
		$tmp  .= "\n";
		if($this->debug) $tmp  .= 'AO.qta_mult';
		$tmp  .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['ArticlesOrder']['qta_multipli'].'" id="qta_multipli-'.$rowId.'" />';
		$tmp  .= "\n";
		
		if(empty($result['User']['id']) && isset($result['Cart']['user_id'])) $result['User']['id'] = $result['Cart']['user_id'];
			
		if($this->debug) $tmp  .= 'U.id';
		$tmp  .= '<input class="debug form-control" type="'.$type_input.'" value="'.$result['User']['id'].'" id="user_id-'.$rowId.'" />';
		
		/*
		 * qta_prima_modifica
		 * 	la prima volta che da backOffice modifico la qta, qta_prima_modifica = $result['Cart']['qta']
		 *  le volte successive, $result['Cart']['qta_forzato']
		 */
		if($result['Cart']['qta_forzato']==0) 
			$qta_prima_modifica = $result['Cart']['qta'];
		else
			$qta_prima_modifica = $result['Cart']['qta_forzato'];
		
		if($this->debug) $tmp  .= 'C.qta_prima_mod';
		if(empty($qta_prima_modifica)) $qta_prima_modifica=0;
		$tmp  .= '<input class="debug form-control" type="'.$type_input.'" value="'.$qta_prima_modifica.'" id="qta_prima_modifica-'.$rowId.'" />';  // serve in caso ArticlesOrder.stato = QTAMAXORDER o Carts.stato = LOCK per bloccare il tasto +
		
		return $tmp;
	}		
	
	private function _drawBackOfficeCart($rowId, $numArticlesOrder, $result, $permissions, $options) {
		
		$tmp  = "";
		//self::d($result['SummaryOrder'], true); exit;
		if($result['SummaryOrder']>0) { 

			$tmp  .= '<td colspan="8">';
			$tmp .= '<div class="alert alert-info alert-dismissable">'; 
			$tmp .= __('msg_summary_order_just_saldato');
			$tmp .= '</div>';
			$tmp  .= '</td>';		

			if(!isset($options['tr.no_display'])) {
				$tmp  .= '</tr>';
					
				$tmp  .= '<tr class="trView" id="trViewId-'.$rowId.'">';
				$tmp  .= '<td colspan="2"></td>';
				$tmp  .= '<td colspan="10" id="tdViewId-'.$rowId.'"></td>';
				$tmp  .= '</tr>';
			}				
		}
		else
		if($result['Order']['permissionToEditReferente'] && $permissions['isReferentGeneric'] ||
		   $result['Order']['permissionToEditCassiere'] && $permissions['isCassiereGeneric'] ||
		   $result['Order']['permissionToEditTesoriere'] && $permissions['isTesoriereGeneric']) {
			$tmp  .= $this->_ordineModificabileBackOffice($rowId, $result);
				
			if(!isset($options['tr.no_display'])) {
				$tmp  .= '</tr>';
					
				$tmp  .= '<tr class="trView" id="trViewId-'.$rowId.'">';
				$tmp  .= '<td colspan="2"></td>';
				$tmp  .= '<td colspan="10" id="tdViewId-'.$rowId.'"></td>';
				$tmp  .= '</tr>';
			}
		
		}
		else {
			$tmp  .= $this->_ordineNonModificabileBackOffice($rowId, $result); // ordine non modificabile (scaduto)
				
			if(!isset($options['tr.no_display'])) {
				$tmp  .= '</tr>';
					
				$tmp  .= '<tr class="trView" id="trViewId-'.$rowId.'">';
				$tmp  .= '<td colspan="2"></td>';
				$tmp  .= '<td colspan="7" id="tdViewId-'.$rowId.'"></td>';
				$tmp  .= '</tr>';
			}
		}
		
		return $tmp;
	}
		
	/*
	 * O R D E R   M O D I F I C A B I L E       B A C K - O F F I C E 
	 */
	private function _ordineModificabileBackOffice($rowId, $result) {
		
		$tmp = "";
		
		$importo = ($result['ArticlesOrder']['prezzo'] * $result['Cart']['qta']);
		
		$tmp  .= "\n";  // Quantità dell'utente: non modificabile
		$tmp  .= '<td style="white-space:nowrap;text-align:center;">';
		if(!empty($result['Cart']['qta'])) $tmp  .= $result['Cart']['qta'];
		$tmp  .= '</td>';
		
		$tmp  .= "\n";  // Importo dell'utente: non modificabile
		$tmp  .= '<td style="white-space:nowrap;">'; 
		if($importo > 0) 
			$tmp  .= number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		$tmp  .= '</td>';
		
		$tmp  .= "\n";  // Stato
		$tmp  .= '<td ';
		if($result['ArticlesOrder']['stato']=='LOCK' || $result['ArticlesOrder']['stato']=='QTAMAXORDER')
			$tmp  .= ' class="stato_'.strtolower($result['ArticlesOrder']['stato']).'" title="Stato dell\'articolo associato all\'ordine: '.$this->traslateEnum($result['ArticlesOrder']['stato']).'"';
		$tmp  .= '></td>';
		
		if($result['Cart']['deleteToReferent']=='N') {
			// Quantità forzato impostata dal referente
			if($result['Cart']['qta_forzato']==0)
				$qta_forzato = $result['Cart']['qta'];
			else
				$qta_forzato = $result['Cart']['qta_forzato'];
			if($qta_forzato>0) $classQta = "qtaUno";
			else $classQta = "qtaZero";
		}
		else
		if($result['Cart']['deleteToReferent']=='Y') {
			$qta_forzato = 0;
			$classQta = "qtaEvidenza";			
		}
		else {
			/*
			 * mai acquistato
			 */
			$qta_forzato = 0;
			$classQta = "qtaZero";
		}	
			
		$tmp  .= '<td style="white-space:nowrap;text-align:center;width: 150px;">';
		$tmp  .= '<div id="qta-'.$rowId.'" class="'.$classQta.' qta">'.$qta_forzato.'</div>';
		$tmp  .= '<div id="buttonPiuMeno-'.$rowId.'" class="buttonPiuMeno" style="display:none;">';
		$tmp  .= '<img alt="compra in +" src="'.Configure::read('App.img.cake').'/button-piu.png" class="buttonCarrello buttonPiu" />';
		$tmp  .= "\n";
		$tmp  .= '<img alt="compra in -" src="'.Configure::read('App.img.cake').'/button-meno.png" class="buttonCarrello buttonMeno" />';
		$tmp  .= "\n";
		$tmp  .= '</div>';
		$tmp  .= "\n";
		$tmp  .= '</td>';
	
		$tmp  .= "\n";  // Importo calcolato (qta_forzato * prezzo) modificato dal referente
		$tmp  .= '<td style="white-space:nowrap;">';
		
		$importo = ($qta_forzato * $result['ArticlesOrder']['prezzo']);
		$tmp  .= '<div id="prezzoNew-'.$rowId.'" class="prezzoNewALL prezzoNew-'.$result['ArticlesOrder']['order_id'].'" data-attr-prezzoNewALL="'.$importo.'" data-attr-prezzoNew-'.$result['ArticlesOrder']['order_id'].'="'.$importo.'">';
		$importo = number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')); 
		if($importo!='0,00') $tmp  .= $importo.'&nbsp;&euro;';	
		$tmp  .= '</div>';
		
		$tmp  .= "\n";
		$tmp  .= '<img alt="" src="'.Configure::read('App.img.cake').'/blank32x32.png" id="submitEcomm-'.$rowId.'" class="buttonCarrello submitEcomm" />';
		$tmp  .= "\n";
		$tmp  .= '<div id="msgEcomm-'.$rowId.'" class="msgEcomm"></div>';
		$tmp  .= "\n";
		$tmp  .= '</td>';
		
		/*
		 * se non ho ancora effettuato acquisti dell'articolo 
		 * 	non visualizzo i campi importo_forzato e nota
		 */
		if(empty($result['Cart']['user_id']) ? $display = 'display:none;' : $display = 'display:inline;');
		
		$tmp  .= "\n"; // Importo nuovo
		$tmp  .= '<td id="importoForzato-'.$rowId.'" style="white-space:nowrap;">';
		if($result['Cart']['deleteToReferent']=='N') {	
			$importo_forzato = number_format($result['Cart']['importo_forzato'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));	
			$tmp  .= '<input tabindex="'.($this->tabindex++).'" type="text" style="'.$display.'" value="'.$importo_forzato.'" name="importo_forzato-'.$rowId.'" id="importo_forzato-'.$rowId.'" size="5" class="importo_forzato form-control" />&nbsp;<span id="importo_forzato_testo-'.$rowId.'" style="'.$display.'">&euro;</span>';
		}
		else 
		if($result['Cart']['deleteToReferent']=='Y') {
			$tmp  .= '&nbsp;';
		}
		else {
			/*
			 * mai acquistato
			 */
			$importo_forzato = number_format(0,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			$tmp  .= '<input tabindex="'.($this->tabindex++).'" type="text" style="'.$display.'" value="'.$importo_forzato.'" name="importo_forzato-'.$rowId.'" id="importo_forzato-'.$rowId.'" size="5" class="importo_forzato form-control" />&nbsp;<span id="importo_forzato_testo-'.$rowId.'" style="'.$display.'">&euro;</span>';
		}	
			
		$tmp  .= '</td>';

		$tmp  .= "\n"; // Nota
		$tmp  .= '<td id="nota-'.$rowId.'">';
		if(!empty($result['Cart']['nota']))
			$tmp  .= '<img style="'.$display.';cursor:pointer;" class="notaEcomm" id="notaEcomm-'.$rowId.'" alt="Aggiungi una nota all\'acquisto" src="'.Configure::read('App.img.cake').'/actions/32x32/playlist.png"></span>';
		else
			$tmp  .= '<img style="'.$display.';cursor:pointer;" class="notaEcomm" id="notaEcomm-'.$rowId.'" alt="Aggiungi una nota all\'acquisto" src="'.Configure::read('App.img.cake').'/actions/32x32/filenew.png"></span>';
		$tmp  .= '</td>';	

		return $tmp;
	}
	
	/*
	 * O R D E R   M O D I F I C A B I L E   F R O N T _ E N D
	 */
	private function _ordineModificabileFrontEnd($rowId, $result) {
		
		$tmp = "";

		$tmp .= "\n";  // ArticlesOrder.Stato 'Y', 'N', 'LOCK', 'QTAMAXORDER'
		$tmp .= '<td ';
		if($result['ArticlesOrder']['stato']!='Y')	$tmp .= ' class="stato_'.strtolower($result['ArticlesOrder']['stato']).'" title="Stato dell\'articolo associato all\'ordine: '.$this->traslateEnum($result['ArticlesOrder']['stato']).'"';
		$tmp .= '></td>';
		
		$tmp .= "\n";  // Qta
		if($result['Cart']['qta_forzato']==0)
			$qta = $result['Cart']['qta'];
		else
			$qta = $result['Cart']['qta_forzato'];
		if($qta>0) $classQta = "qtaUno";
		else $classQta = "qtaZero";
		$tmp .= '<td style="white-space:nowrap;text-align:center;width: 125px;">';
		$tmp .= "\n";
		$tmp .= '<div id="qta-'.$rowId.'" class="'.$classQta.' qta">';
		if($qta>0) $tmp .= $qta;
		$tmp .= '</div>';
		$tmp .= "\n";
		$tmp .= '<div id="buttonPiuMeno-'.$rowId.'" class="buttonPiuMeno" style="display:none;">';
		$tmp .= '<img alt="compra in +" src="'.Configure::read('App.img.cake').'/button-piu.png" class="buttonCarrello buttonPiu" />';
		$tmp .= "\n";
		$tmp .= '<img alt="compra in -" src="'.Configure::read('App.img.cake').'/button-meno.png" class="buttonCarrello buttonMeno" />';
		$tmp .= "\n";
		$tmp .= '</div>';
		$tmp .= "\n";
		$tmp .= '</td>';
	
		/*
		 * importo Cart
		 */
		$importo_modificato = false;
		if(number_format($result['Cart']['importo_forzato'])==0) {
			if(number_format($result['Cart']['qta_forzato'])>0)
				$importo = ($result['Cart']['qta_forzato'] * $result['ArticlesOrder']['prezzo']);
			else 
				$importo = ($result['Cart']['qta'] * $result['ArticlesOrder']['prezzo']);
		}
		else {
			$importo = $result['Cart']['importo_forzato'];
			$importo_modificato = true;
		}
		
		$tmp .= '<td style="white-space:nowrap;">';
		$tmp .= '<div id="prezzoNew-'.$rowId.'" class="prezzoNewALL prezzoNew-'.$result['ArticlesOrder']['order_id'].'" data-attr-prezzoNewALL="'.$importo.'" data-attr-prezzoNew-'.$result['ArticlesOrder']['order_id'].'="'.$importo.'">';
		if(!empty($importo)) 
			$tmp .= number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		$tmp .= '</div>';
		$tmp .= "\n";
		$tmp .= '<img alt="" src="'.Configure::read('App.img.cake').'/blank32x32.png" id="submitEcomm-'.$rowId.'" class="buttonCarrello submitEcomm" />';
		$tmp .= "\n";
		$tmp .= '<div id="msgEcomm-'.$rowId.'" class="msgEcomm"></div>';
		$tmp .= "\n";
		$tmp .= '</td>';
		
		return $tmp;
	}	

	/*
	 * O R D E R   M O D I F I C A B I L E   F R O N T _ E N D 
	 *  gestisco gli ordini da validare i colli (pezzi_confezione)
	*/
	private function _ordineModificabileFrontEndCartsValidation($rowId, $result) {
	
		$tmp = "";
	
		$tmp .= "\n";  // ArticlesOrder.Stato 'Y', 'N', 'LOCK', 'QTAMAXORDER'
		$tmp .= '<td ';
		if($result['ArticlesOrder']['stato']!='Y')	$tmp .= ' class="stato_'.strtolower($result['ArticlesOrder']['stato']).'" title="Stato dell\'articolo associato all\'ordine: '.$this->traslateEnum($result['ArticlesOrder']['stato']).'"';
		$tmp .= '></td>';
	
		$tmp .= "\n";  // Differenza da ordinare
		$tmp .= '<td style="white-space: nowrap;">';
		$tmp .= '<div  id="differenza_da_ordinare-'.$rowId.'" class="qtaEvidenza">'.$result['ArticlesOrder']['differenza_da_ordinare'].'</div>';
		$tmp .= '</td>';
		
		$tmp .= "\n";  // Qta
		if($result['Cart']['qta_forzato']==0)
			$qta = $result['Cart']['qta'];
		else
			$qta = $result['Cart']['qta_forzato'];
		if($qta>0) $classQta = "qtaUno";
		else $classQta = "qtaZero";
		$tmp .= '<td style="white-space:nowrap;text-align:center;width: 125px;">';
		$tmp .= "\n";
		$tmp .= '<div id="qta-'.$rowId.'" class="'.$classQta.' qta">';
		if($qta>0) $tmp .= $qta;
		$tmp .= '</div>';
		$tmp .= "\n";
		$tmp .= '<div id="buttonPiuMenoCartsValidation-'.$rowId.'" class="buttonPiuMeno" style="display:none;">';
		$tmp .= '<img alt="compra in +" src="'.Configure::read('App.img.cake').'/button-piu.png" class="buttonCarrello buttonPiuCartsValidation" />';
		$tmp .= "\n";
		$tmp .= '<img alt="compra in -" src="'.Configure::read('App.img.cake').'/button-meno.png" class="buttonCarrello buttonMenoCartsValidation" />';
		$tmp .= "\n";
		$tmp .= '</div>';
		$tmp .= "\n";
		$tmp .= '</td>';
	
		/*
		 * importo Cart
		*/
		$importo_modificato = false;
		if(number_format($result['Cart']['importo_forzato'])==0) {
			if(number_format($result['Cart']['qta_forzato'])>0)
				$importo = ($result['Cart']['qta_forzato'] * $result['ArticlesOrder']['prezzo']);
			else
				$importo = ($result['Cart']['qta'] * $result['ArticlesOrder']['prezzo']);
		}
		else {
			$importo = $result['Cart']['importo_forzato'];
			$importo_modificato = true;
		}
	
		$tmp .= '<td style="white-space:nowrap;">';
		$tmp .= '<div id="prezzoNew-'.$rowId.'" class="prezzoNewALL prezzoNew-'.$result['ArticlesOrder']['order_id'].'" data-attr-prezzoNewALL="'.$importo.'" data-attr-prezzoNew-'.$result['ArticlesOrder']['order_id'].'="'.$importo.'">';
		if(!empty($importo))
			$tmp .= number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
	
		$tmp .= '</div>';
		$tmp .= "\n";
		$tmp .= '<img alt="" src="'.Configure::read('App.img.cake').'/blank32x32.png" id="submitEcomm-'.$rowId.'" class="buttonCarrello submitEcomm" />';
		$tmp .= "\n";
		$tmp .= '<div id="msgEcomm-'.$rowId.'" class="msgEcomm"></div>';
		$tmp .= "\n";
		$tmp .= '</td>';
	
		return $tmp;
	}
	
	/*
	 * O R D E R   M O D I F I C A B I L E   F R O N T _ E N D 
	 *  gestisco gli ordini in ProdGasPromotion
	*/
	private function _ordineModificabileFrontEndPromotion($rowId, $result) {
		/*
		echo "<pre>_ordineModificabileFrontEndPromotion \n ";
		print_r($result);
		echo "</pre>";
		*/
		$tmp = "";
	
		$tmp .= "\n";  // ArticlesOrder.Stato 'Y', 'N', 'LOCK', 'QTAMAXORDER'
		$tmp .= '<td ';
		if($result['ArticlesOrder']['stato']!='Y')	$tmp .= ' class="stato_'.strtolower($result['ArticlesOrder']['stato']).'" title="Stato dell\'articolo associato all\'ordine: '.$this->traslateEnum($result['ArticlesOrder']['stato']).'"';
		$tmp .= '></td>';
	
		$tmp .= "\n";  // la qta totale da raggiungere per la promozione
		$tmp .= '<td style="white-space: nowrap;">';
		// $tmp .= $result['ProdGasArticlesPromotion']['qta'].' '.$result['ProdGasArticlesPromotion']['differenza_da_ordinare'];
		$tmp .= '<div  id="differenza_da_ordinare-'.$rowId.'" class="qtaEvidenza">'.$result['ProdGasArticlesPromotion']['differenza_da_ordinare'].'</div>';
		$tmp .= '</td>';
		
		$tmp .= "\n";  // Qta
		if($result['Cart']['qta_forzato']==0)
			$qta = $result['Cart']['qta'];
		else
			$qta = $result['Cart']['qta_forzato'];
		if($qta>0) $classQta = "qtaUno";
		else $classQta = "qtaZero";
		$tmp .= '<td style="white-space:nowrap;text-align:center;width: 125px;">';
		$tmp .= "\n";
		$tmp .= '<div id="qta-'.$rowId.'" class="'.$classQta.' qta">';
		if($qta>0) $tmp .= $qta;
		$tmp .= '</div>';
		$tmp .= "\n";
		$tmp .= '<div id="buttonPiuMenoCartsValidation-'.$rowId.'" class="buttonPiuMeno" style="display:none;">';
		$tmp .= '<img alt="compra in +" src="'.Configure::read('App.img.cake').'/button-piu.png" class="buttonCarrello buttonPiuCartsValidation" />';
		$tmp .= "\n";
		$tmp .= '<img alt="compra in -" src="'.Configure::read('App.img.cake').'/button-meno.png" class="buttonCarrello buttonMenoCartsValidation" />';
		$tmp .= "\n";
		$tmp .= '</div>';
		$tmp .= "\n";
		$tmp .= '</td>';
	
		/*
		 * importo Cart
		*/
		$importo_modificato = false;
		if(number_format($result['Cart']['importo_forzato'])==0) {
			if(number_format($result['Cart']['qta_forzato'])>0)
				$importo = ($result['Cart']['qta_forzato'] * $result['ArticlesOrder']['prezzo']);
			else
				$importo = ($result['Cart']['qta'] * $result['ArticlesOrder']['prezzo']);
		}
		else {
			$importo = $result['Cart']['importo_forzato'];
			$importo_modificato = true;
		}
	
		$tmp .= '<td style="white-space:nowrap;">';
		$tmp .= "\n";
		$tmp .= '<div id="prezzoNew-'.$rowId.'" class="prezzoNewALL prezzoNew-'.$result['ArticlesOrder']['order_id'].'" data-attr-prezzoNewALL="'.$importo.'" data-attr-prezzoNew-'.$result['ArticlesOrder']['order_id'].'="'.$importo.'">';
		if(!empty($importo))
			$tmp .= number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		$tmp .= '</div>';
		$tmp .= "\n";
		$tmp .= '<img alt="" src="'.Configure::read('App.img.cake').'/blank32x32.png" id="submitEcomm-'.$rowId.'" class="buttonCarrello submitEcomm" />';
		$tmp .= "\n";
		$tmp .= '<div id="msgEcomm-'.$rowId.'" class="msgEcomm"></div>';
		$tmp .= "\n";
		$tmp .= '</td>';
	
		return $tmp;
	}
		
	/* 
	 * O R D E R   N O N   M O D I F I C A B I L E   F R O N T _ E N D 
	 */
	private function _ordineNonModificabileFrontEnd($rowId, $result) {
		
		$tmp = "";

		$tmp .= "\n";  // ArticlesOrder.Stato 'Y', 'N', 'LOCK', 'QTAMAXORDER'
		$tmp .= '<td ';
		if($result['ArticlesOrder']['stato']!='Y')
			$tmp .= ' class="stato_'.strtolower($result['ArticlesOrder']['stato']).'" title="Stato dell\'articolo associato all\'ordine: '.$this->traslateEnum($result['ArticlesOrder']['stato']).'"';
		$tmp .= '></td>';
		
		$tmp .= "\n";   // Qta
		if($result['Cart']['qta_forzato']==0)
			$qta = $result['Cart']['qta'];
		else
			$qta = $result['Cart']['qta_forzato'];
		if($qta>0) $classQta = "qtaUno";
		else $classQta = "qtaZero";
		$tmp .= '<td style="white-space:nowrap;text-align:center;">';
		$tmp .= "\n";
		$tmp .= '<div id="qta-'.$rowId.'" class="'.$classQta.' qta">'.$qta.'</div>';
		$tmp .= "\n";
		$tmp .= '</td>';

		/*
		 * importo Cart
		*/
		$importo_modificato = false;
		if(number_format($result['Cart']['importo_forzato'])==0) {
			if(number_format($result['Cart']['qta_forzato'])>0)
				$importo = ($result['Cart']['qta_forzato'] * $result['ArticlesOrder']['prezzo']);
			else 
				$importo = ($result['Cart']['qta'] * $result['ArticlesOrder']['prezzo']);
		}
		else {
			$importo = $result['Cart']['importo_forzato'];
			$importo_modificato = true;
		}
		
		$tmp .= '<td style="white-space:nowrap;">';
		$tmp .= "\n";
		$tmp .= '<div id="prezzoNew-'.$rowId.'" class="prezzoNewALL prezzoNew-'.$result['ArticlesOrder']['order_id'].'" data-attr-prezzoNewALL="'.$importo.'" data-attr-prezzoNew-'.$result['ArticlesOrder']['order_id'].'="'.$importo.'">';		
		if(!empty($importo)) 
			$tmp .= number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';	
		$tmp .= '</div>';
		$tmp .= '</td>';
				
		return $tmp;
	}

	/*
	 * O R D E R   N O N   M O D I F I C A B I L E   F R O N T _ E N D
	*/
	private function _ordinePreviewFrontEnd($rowId, $result) {
		
			$tmp = "";
		
			$tmp .= "\n";  // ArticlesOrder.Stato 'Y', 'N', 'LOCK', 'QTAMAXORDER'
			$tmp .= '<td ';
			if($result['ArticlesOrder']['stato']!='Y')
				$tmp .= ' class="stato_'.strtolower($result['ArticlesOrder']['stato']).'" title="Stato dell\'articolo associato all\'ordine: '.$this->traslateEnum($result['ArticlesOrder']['stato']).'"';
			$tmp .= '></td>';
		
			$tmp .= "\n";   // Qta
			if($result['Cart']['qta_forzato']==0)
				$qta = $result['Cart']['qta'];
			else
				$qta = $result['Cart']['qta_forzato'];
			if($qta>0) $classQta = "qtaUno";
			else $classQta = "qtaZero";
			$tmp .= '<td style="white-space:nowrap;text-align:center;">';
			$tmp .= "\n";
			$tmp .= '<div id="qta-'.$rowId.'" class="'.$classQta.' qta">'.$qta.'</div>';
			
			if($result['Order']['permissionToEditUtente'])
				$tmp .= '<input type="submit" id="btn-account" class="btn btn-orange cartPreview" value="Modifica" name="cartPreview">';
			
			$tmp .= "\n";
			$tmp .= '</td>';
		
			/*
			 * importo Cart
			*/
			$importo_modificato = false;
			if(number_format($result['Cart']['importo_forzato'])==0) {
				if(number_format($result['Cart']['qta_forzato'])>0)
					$importo = ($result['Cart']['qta_forzato'] * $result['ArticlesOrder']['prezzo']);
				else
					$importo = ($result['Cart']['qta'] * $result['ArticlesOrder']['prezzo']);
			}
			else {
				$importo = $result['Cart']['importo_forzato'];
				$importo_modificato = true;
			}
		
			$tmp .= '<td style="white-space:nowrap;">';
			$tmp .= "\n";
			$tmp .= '<div id="prezzoNew-'.$rowId.'" class="prezzoNewALL prezzoNew-'.$result['ArticlesOrder']['order_id'].'" data-attr-prezzoNewALL="'.$importo.'" data-attr-prezzoNew-'.$result['ArticlesOrder']['order_id'].'="'.$importo.'">';
			if(!empty($importo))
				$tmp .= number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
			$tmp .= '</div>';
			$tmp .= '</td>';
		
			return $tmp;
	}
		
	/*
	 * O R D E R   N O N   M O D I F I C A B I L E   B A C K - O F F I C E
	*/
	private function _ordineNonModificabileBackOffice($rowId, $result) {
		
		$tmp  = "";
		
		$importo = ($result['ArticlesOrder']['prezzo'] * $result['Cart']['qta']);
		
		$tmp  .= "\n";
		$qta = $result['Cart']['qta'];
		if($qta>0) $classQta = "qtaUno";
		else $classQta = "qtaZero";
 	
		$tmp  .= "\n";  // Quantità dell'utente 	
		$tmp  .= '<td style="white-space:nowrap;text-align:center;">';
		$tmp  .= '<div id="qta-'.$rowId.'" class="'.$classQta.' qta">'.$qta.'</div>';
		$tmp  .= "\n";
		$tmp  .= '</td>';
	
		$tmp  .= "\n";  // Importo dell'utente
		$tmp  .= '<td style="white-space:nowrap;">';
		$tmp .= "\n";
		$tmp .= '<div id="prezzoNew-'.$rowId.'" class="prezzoNewALL prezzoNew-'.$result['ArticlesOrder']['order_id'].'" data-attr-prezzoNewALL="'.$importo.'" data-attr-prezzoNew-'.$result['ArticlesOrder']['order_id'].'="'.$importo.'">';	
		if($importo > 0) 
			$tmp  .= number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		$tmp  .= '</div>';
		$tmp  .= '</td>';
		
		$tmp  .= "\n";  // Stato
		$tmp  .= '<td ';
		if($result['ArticlesOrder']['stato']=='LOCK' || $result['ArticlesOrder']['stato']=='QTAMAXORDER')
			$tmp  .= ' class="stato_'.strtolower($result['ArticlesOrder']['stato']).'" title="Stato dell\'articolo associato all\'ordine: '.$this->traslateEnum($result['ArticlesOrder']['stato']).'"';
		$tmp  .= '></td>';
		
		return $tmp;
	}
	
	/*
	 * crea un ID per identificare la riga univoca
	 * $numArticlesOrder = numero incrementale della riga, serve quando ricostruisco la riga dopo il save 
	 */
	private function _getRowId($numArticlesOrder, $result) {
		 
		$rowId = $result['ArticlesOrder']['order_id'].'_'.$result['ArticlesOrder']['article_organization_id'].'_'.$result['ArticlesOrder']['article_id'].'_'.$result['ArticlesOrder']['stato'].'_'.$numArticlesOrder;
		 
		 return $rowId;
	}	
	
	/*
	 * viene ri-caricata solo la righa dopo il salvataggio
	*/
	private function _draw_js_frontend($rowId, $order_id=0) {
		$tmp = "";
	
		$tmp  .= '<script type="text/javascript">';
		$tmp  .= "\r\n";
		$tmp  .= '$(document).ready(function() {';
		$tmp  .= "\r\n";
		$tmp  .= '	activeSubmitEcomm($("#row-'.$rowId.'"));';
		$tmp  .= "\r\n";
		$tmp  .= '	activeEcommRows($("#row-'.$rowId.'"));';
		$tmp  .= "\r\n";
		$tmp  .= '	actionTrView($("#actionTrView-'.$rowId.'"));';
		$tmp  .= "\r\n";
		$tmp  .= '	actionNotaView($("#actionNotaView-'.$rowId.'"));';
		$tmp  .= "\r\n";
		$tmp  .= '	settingEcommTotale(\'prezzoNew-'.$order_id.'\',\'totalePrezzoNew-'.$order_id.'\');';
		$tmp  .= "\r\n";
		$tmp  .= '	settingEcommTotale(\'prezzoNewALL\',\'totalePrezzoNewALL\');';
		$tmp  .= "\r\n";	
		$tmp  .= '});';
		$tmp  .= '</script>';
	
		return $tmp;
	}
	
	/*
	 * viene ri-caricata solo la righa dopo il salvataggio
	*/
	private function _draw_js_backoffice($rowId) {
		$tmp = "";
	
		$tmp  .= '<script type="text/javascript">';
		$tmp  .= "\r\n";
		$tmp  .= '$(document).ready(function() {';
		$tmp  .= "\r\n";
		$tmp  .= '	activeSubmitEcomm($("#row-'.$rowId.'"));';
		$tmp  .= "\r\n";
		$tmp  .= '	activeEcommRows($("#row-'.$rowId.'"));';
		$tmp  .= "\r\n";
		$tmp  .= '	actionTrView($("#actionTrView-'.$rowId.'"));';
		$tmp  .= "\r\n";
		$tmp  .= '	actionNotaView($("#actionNotaView-'.$rowId.'"));';
		$tmp  .= "\r\n";
		$tmp  .= '	activeImportoForzato($("#importoForzato-'.$rowId.'"));';
		$tmp  .= "\r\n";
		$tmp  .= '	activeNotaEcomm($("#nota-'.$rowId.'"));';
		$tmp  .= "\r\n";
		$tmp  .= '});';
		$tmp  .= '</script>';
	
		return $tmp;
	}
}		
?>