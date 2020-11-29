<?php 
App::uses('UtilsCommons', 'Lib');

/*
 * $result['permission'] = array('orderPermissionToEditUtente' => true, 
 * 							   'orderpermissionToEditProduttore' => fals);
 * 			
 * */

class ProdRowEcommHelper extends AppHelper {
		
	private $debug =  false;
	private $tabindex = 1;
		
	/*
	 * $result Array ( [ProdDeliveriesArticle] => Array 
	 *				   [Article] => Array
	 *				   [ProdCart] => Array
	 * */
	public function drawFrontEndComplete($numProdDeliveriesArticle, $prodDeliveryResults, $result, $options=array()) { 
	
		$tmp = "";

		$rowId = $this->__getRowId($numProdDeliveriesArticle, $prodDeliveryResults, $result);

		$k=0;
		if(!isset($options['tr.no_display'])) {  // function se richiamata da Layout/ajax_rowecomm_frontend.ctp
			$tmp .= "\n";
			$tmp .= '<tr class="row'.$k.' prodDeliveryId'.$prodDeliveryResults['ProdDelivery']['id'].' rowEcomm" id="row-'.$rowId.'" style="display:table-row;">';
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
		$tmp .= '<input class="debug" type="hidden" value="'.$result['ProdDeliveriesArticle']['prod_delivery_id'].'" id="prod_delivery_id-'.$rowId.'" />';
		$tmp .= "\n";
		$tmp .= '<input class="debug" type="hidden" value="'.$result['ProdDeliveriesArticle']['prezzo'].'" id="prezzo-'.$rowId.'" />';
		
		if($this->debug) $tmp .= 'PDA.stato';
		$tmp .= '<input class="debug" type="'.$type_input.'" value="'.$result['ProdDeliveriesArticle']['stato'].'" id="prodDeliveriesArticle_stato-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'PDA.article_organization_id';
		$tmp .= '<input class="debug" type="'.$type_input.'" value="'.$result['ProdDeliveriesArticle']['article_organization_id'].'" id="article_organization_id-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'PDA.article_id';
		$tmp .= '<input class="debug" type="'.$type_input.'" value="'.$result['ProdDeliveriesArticle']['article_id'].'" id="article_id-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= '<br />PDA.qta_cart';
		$tmp .= '<input class="debug" type="'.$type_input.'" value="'.$result['ProdDeliveriesArticle']['qta_cart'].'" id="qta_cart-'.$rowId.'" />';   // la qta_cart serve solo per il ctrl js, in Model/AjaxProdCart e' ricalcolato
		$tmp .= "\n";
		if($this->debug) $tmp .= 'PDA.qta_min';
		$tmp .= '<input class="debug" type="'.$type_input.'" value="'.$result['ProdDeliveriesArticle']['qta_minima'].'" id="qta_minima-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'PDA.qta_max';
		$tmp .= '<input class="debug" type="'.$type_input.'" value="'.$result['ProdDeliveriesArticle']['qta_massima'].'" id="qta_massima-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'PDA.qta_min_order';
		$tmp .= '<input class="debug" type="'.$type_input.'" value="'.$result['ProdDeliveriesArticle']['qta_minima_order'].'" id="qta_minima_order-'.$rowId.'" />';  
		$tmp .= "\n";
		if($this->debug) $tmp .= 'PDA.qta_max_order';
		$tmp .= '<input class="debug" type="'.$type_input.'" value="'.$result['ProdDeliveriesArticle']['qta_massima_order'].'" id="qta_massima_order-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'PDA.qta_mult';
		$tmp .= '<input class="debug" type="'.$type_input.'" value="'.$result['ProdDeliveriesArticle']['qta_multipli'].'" id="qta_multipli-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'PC.qta_prima_mod';
		if(empty($result['ProdCart']['qta'])) $result['ProdCart']['qta']=0;
		$tmp .= '<input class="debug" type="'.$type_input.'" value="'.$result['ProdCart']['qta'].'" id="qta_prima_modifica-'.$rowId.'" />';  // serve in caso ProdDeliveriesArticle.stato = QTAMAXORDER o ProdCarts.stato = LOCK per bloccare il tasto +
		$tmp .= "\n";
		
		$tmp .= '<a id="actionTrView-'.$rowId.'" action="prod_deliveries_articles_no_img-'.$rowId.'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
		
		$tmp .= '<td>'.($numProdDeliveriesArticle+1).'</td>';
		$tmp .= "\n";
		$tmp .= '<td>';
		
		if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
			$tmp .= '<img src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';
		}
		else
			$tmp .= "";
		$tmp .= '</td>';
		$tmp .= "\n";
		$tmp .= '<td>';
		$tmp .= $result['Article']['name'];
		$tmp .= $this->drawArticleNotaAjax($rowId, $result['Article']['nota']);
		
		if($result['Article']['bio']=='Y') 
			$tmp .= '<br /><span class="bio" title="'.Configure::read('bio').'" style="float:right;"></span>';
		
		$tmp .= '<div style="margin-top:20px;">';
		if($result['Article']['qta']>0)  // Conf
			$tmp .= '<strong>'.__('Conf').'</strong> '.$this->getArticleConf($result['Article']['qta'], $this->traslateEnum($result['Article']['um']));
		
		$tmp .= '<br />'; // Prezzo unità
		$tmp .= '<strong>'.__('PrezzoUnita').'</strong> '.$result['ProdDeliveriesArticle']['prezzo_e'];
		
		$tmp .= '<br />';  // Prezzo/UM
		$tmp .= '<strong>'.__('Prezzo/UM').'</strong> '.$this->getArticlePrezzoUM($result['ProdDeliveriesArticle']['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']);
		
		$tmp .= '<br />';  // Qta minima
		$tmp .= '<strong>Min.</strong> '.sprintf("%5.2f",$result['ProdDeliveriesArticle']['qta_minima']);

		$tmp .= '<br />';  // Qta massima
		$tmp .= '<strong>Max.</strong> '.sprintf("%5.2f",$result['ProdDeliveriesArticle']['qta_massima']);
		
		$tmp .= '</div>';
				
		$tmp .= '</td>';
		
	   /* 
	    * C A R T
	    */
		if($prod_delivery['ProdDelivery']['permissionToEditUtente'])
			$tmp .= $this->__consegnaModificabileFrontEnd($rowId, $prodDeliveryResults, $result);  // ordine modificabile (attivo)
		else 
			$tmp .= $this->__consegnaNonModificabileFrontEnd($rowId, $prodDeliveryResults, $result); // ordine non modificabile (scaduto)
	
		if(!isset($options['tr.no_display'])) {
			$tmp .= '</tr>';
	
			$tmp .= '<tr class="trView" id="trViewId-'.$rowId.'">';
			$tmp .= '<td colspan="2"></td>';
			$tmp .= '<td colspan="11" id="tdViewId-'.$rowId.'"></td>';
			$tmp .= '</tr>';
		}
				
		$k = 1 - $k;
		
		return $tmp;
	}

	public function drawFrontEndSimple($numProdDeliveriesArticle, $prodDeliveryResults, $result, $options=array()) {
	
		$tmp = "";
	
		$rowId = $this->__getRowId($numProdDeliveriesArticle, $prodDeliveryResults, $result);
	
		$k=0;
		if(!isset($options['tr.no_display'])) {  // function se richiamata da Layout/ajax_rowecomm_frontend.ctp
			$tmp .= "\n";
			$tmp .= '<tr class="row'.$k.' prodDeliveryId'.$prodDeliveryResults['ProdDelivery']['id'].' rowEcomm" id="row-'.$rowId.'" style="display:table-row;">';
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
		$tmp .= '<input class="debug" type="hidden" value="'.$result['ProdDeliveriesArticle']['prod_delivery_id'].'" id="prod_delivery_id-'.$rowId.'" />';
		$tmp .= "\n";
		$tmp .= '<input class="debug" type="hidden" value="'.$result['ProdDeliveriesArticle']['prezzo'].'" id="prezzo-'.$rowId.'" />';
	
		if($this->debug) $tmp .= 'PDA.stato';
		$tmp .= '<input class="debug" type="'.$type_input.'" value="'.$result['ProdDeliveriesArticle']['stato'].'" id="prodDeliveriesArticle_stato-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'PDA.article_organization_id';
		$tmp .= '<input class="debug" type="'.$type_input.'" value="'.$result['ProdDeliveriesArticle']['article_organization_id'].'" id="article_organization_id-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'PDA.article_id';
		$tmp .= '<input class="debug" type="'.$type_input.'" value="'.$result['ProdDeliveriesArticle']['article_id'].'" id="article_id-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= '<br />PDA.qta_cart';
		$tmp .= '<input class="debug" type="'.$type_input.'" value="'.$result['ProdDeliveriesArticle']['qta_cart'].'" id="qta_cart-'.$rowId.'" />';   // la qta_cart serve solo per il ctrl js, in Model/AjaxProdCart e' ricalcolato
		$tmp .= "\n";
		if($this->debug) $tmp .= 'PDA.qta_min';
		$tmp .= '<input class="debug" type="'.$type_input.'" value="'.$result['ProdDeliveriesArticle']['qta_minima'].'" id="qta_minima-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'PDA.qta_max';
		$tmp .= '<input class="debug" type="'.$type_input.'" value="'.$result['ProdDeliveriesArticle']['qta_massima'].'" id="qta_massima-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'PDA.qta_min_order';
		$tmp .= '<input class="debug" type="'.$type_input.'" value="'.$result['ProdDeliveriesArticle']['qta_minima_order'].'" id="qta_minima_order-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'PDA.qta_max_order';
		$tmp .= '<input class="debug" type="'.$type_input.'" value="'.$result['ProdDeliveriesArticle']['qta_massima_order'].'" id="qta_massima_order-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'PDA.qta_mult';
		$tmp .= '<input class="debug" type="'.$type_input.'" value="'.$result['ProdDeliveriesArticle']['qta_multipli'].'" id="qta_multipli-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'PC.qta_prima_mod';
		if(empty($result['ProdCart']['qta'])) $result['ProdCart']['qta']=0;
		$tmp .= '<input class="debug" type="'.$type_input.'" value="'.$result['ProdCart']['qta'].'" id="qta_prima_modifica-'.$rowId.'" />';  // serve in caso ProdDeliveriesArticle.stato = QTAMAXORDER o ProdCarts.stato = LOCK per bloccare il tasto +
		$tmp .= "\n";
	
		$tmp .= '<a id="actionTrView-'.$rowId.'" action="prod_deliveries_articles-'.$rowId.'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
	
		$tmp .= '<td>'.($numProdDeliveriesArticle+1).'</td>';
		$tmp .= "\n";
		$tmp .= '<td>';
		if($result['Article']['bio']=='Y')
			$tmp .= '<span class="bio" title="'.Configure::read('bio').'"></span>';
		else
			$tmp .= "";
		$tmp .= '</td>';
		$tmp .= "\n";
		$tmp .= '<td>';
		$tmp .= $result['Article']['name'];
		$tmp .= $this->drawArticleNotaAjax($rowId, $result['Article']['nota']);
		$tmp .= '</td>';
	
		$tmp .= "\n";  // Conf
		$tmp .= '<td style="white-space: nowrap;">';
		if($result['Article']['qta']>0)
			$tmp .= $this->getArticleConf($result['Article']['qta'], $this->traslateEnum($result['Article']['um']));
		$tmp .= '</td>';
		$tmp .= "\n";  // Prezzo unità
		$tmp .= '<td style="white-space: nowrap;">';
		$tmp .= $result['ProdDeliveriesArticle']['prezzo_e'];
		$tmp .= '</td>';
		$tmp .= "\n";  // Prezzo/UM
		$tmp .= '<td style="white-space: nowrap;">';
		$tmp .= $this->getArticlePrezzoUM($result['ProdDeliveriesArticle']['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']);
		$tmp .= '</td>';
		$tmp .= "\n";  // Qta minima
		$tmp .= '<td style="white-space: nowrap;">';
		$tmp .= sprintf("%5.2f",$result['ProdDeliveriesArticle']['qta_minima']);
		$tmp .= '</td>';
	
		/*
		 * C A R T
		*/
		if($prod_delivery['ProdDelivery']['permissionToEditUtente'])
			$tmp .= $this->__consegnaModificabileFrontEnd($rowId, $prodDeliveryResults, $result);  // ordine modificabile (attivo)
		else
			$tmp .= $this->__consegnaNonModificabileFrontEnd($rowId, $prodDeliveryResults, $result); // ordine non modificabile (scaduto)
	
		if(!isset($options['tr.no_display'])) {
			$tmp .= '</tr>';
	
			$tmp .= '<tr class="trView" id="trViewId-'.$rowId.'">';
			$tmp .= '<td colspan="2"></td>';
			$tmp .= '<td colspan="11" id="tdViewId-'.$rowId.'"></td>';
			$tmp .= '</tr>';
		}
	
		$k = 1 - $k;
	
		return $tmp;
	}
	
	public function drawBackOfficeReportUsers($user, $numProdDeliveriesArticle, $prodDeliveryResults ,$result, $permissions, $options=array()) {
	
		$rowId = $this->__getRowId($numProdDeliveriesArticle, $prodDeliveryResults, $result);
	
		$tmp = "";
	
		$k=0;
		if(!isset($options['tr.no_display'])) {
			$tmp .= "\n";
			$tmp .= '<tr class="row'.$k.' prodDeliveryId'.$prodDeliveryResults['ProdDelivery']['id'].' rowEcomm" id="row-'.$rowId.'" style="display:table-row;">';
		}
	
		$tmp .= $this->__drawBackOfficeFieldsHidden($rowId, $numProdDeliveriesArticle, $prodDeliveryResults, $result, $permissions, $options);
		//$tmp .= '<td>';
		$tmp .= '<a id="actionTrView-'.$rowId.'" action="prod_deliveries_articles-'.$rowId.'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
	
		$tmp .= '<td>'.($numProdDeliveriesArticle+1).'</td>';
		$tmp .= "\n";
		$tmp .= '<td class="';
		if($result['ProdCart']['deleteToReferent']=='Y')
			$tmp .= 'deleteToReferent';
		$tmp .= '">';
		$tmp .= $result['Article']['name'];
		//$tmp .= $this->drawArticleNotaAjax($rowId, $result['Article']['nota']);
		$tmp .= '</td>';
	
		$tmp .= "\n";  // Conf.
		$tmp .= '<td style="white-space: nowrap;">';
		if($result['Article']['qta']>0)
			$tmp .= $this->getArticleConf($result['Article']['qta'], $this->traslateEnum($result['Article']['um']));
		$tmp .= '</td>';
		$tmp .= "\n"; // Prezzo unità
		$tmp .= '<td style="white-space: nowrap;">';
		$tmp .= $result['ProdDeliveriesArticle']['prezzo_e'];
		$tmp .= '</td>';
		$tmp .= "\n";  // Prezzo/UM
		$tmp .= '<td style="white-space: nowrap;">';
		$tmp .= $this->getArticlePrezzoUM($result['ProdDeliveriesArticle']['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']);
		$tmp .= '</td>';
		$tmp .= "\n";
	
		$tmp .= $this->__drawBackOfficeCart($rowId, $numProdDeliveriesArticle, $prodDeliveryResults, $result, $permissions, $options);
	
		if(isset($options['tr.no_display']))
			$tmp .= $this->__draw_js_backoffice($rowId);
	
		$k = 1 - $k;
	
		return $tmp;
	}
	
	public function drawBackOfficeReportArticlesDetails($numProdDeliveriesArticle, $prodDeliveryResults, $result, $permissions, $options=array()) {
	
		$rowId = $this->__getRowId($numProdDeliveriesArticle, $prodDeliveryResults, $result);
	
		$tmp = "";
	
		$k=0;
		if(!isset($options['tr.no_display'])) {
			$tmp .= "\n";
			$tmp .= '<tr class="row'.$k.' prodDeliveryId'.$prodDeliveryResults['ProdDelivery']['id'].' rowEcomm" id="row-'.$rowId.'" style="display:table-row;">';
		}
	
		$tmp .= $this->__drawBackOfficeFieldsHidden($rowId, $numProdDeliveriesArticle, $prodDeliveryResults, $result, $permissions, $options);
	
		$tmp .= '<a id="actionTrView-'.$rowId.'" action="prod_deliveries_articles-'.$rowId.'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
	
		$tmp .= '<td>'.($numProdDeliveriesArticle+1).'</td>';
		$tmp .= "\n";
		$tmp .= '<td class="';
		if($result['ProdCart']['deleteToReferent']=='Y')
			$tmp .= 'deleteToReferent';
		$tmp .= '">';
		$tmp .= $result['Article']['name'];
		//$tmp .= $this->drawArticleNotaAjax($rowId, $result['Article']['nota']);
		$tmp .= '</td>';
	
		$tmp .= "\n";
		$tmp .= '<td class="';
		if($result['ProdCart']['deleteToReferent']=='Y')
			$tmp .= 'deleteToReferent';
		$tmp .= '">';
		$tmp .= $result['User']['name'].'</td>';
		$tmp .= "\n";
		$tmp .= '<td>'.$this->Time->i18nFormat($result['ProdCart']['date'],"%e %B %R").'</td>';
		/*
			$tmp .= "\n";  // Conf.
		$tmp .= '<td style="white-space: nowrap;">';
		if($result['Article']['qta']>0)
			$tmp .= $this->getArticleConf($result['Article']['qta'], $this->traslateEnum($result['Article']['um']));
		$tmp .= '</td>';
		$tmp .= "\n"; // Prezzo unità
		$tmp .= '<td style="white-space: nowrap;">';
		$tmp .= $result['ProdDeliveriesArticle']['prezzo_e'];
		$tmp .= '</td>';
		$tmp .= "\n";  // Prezzo/UM
		$tmp .= '<td style="white-space: nowrap;">';
		$tmp .= $this->getArticlePrezzoUM($result['ProdDeliveriesArticle']['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']);
		$tmp .= '</td>';
		$tmp .= "\n";
		*/
		$tmp .= $this->__drawBackOfficeCart($rowId, $numProdDeliveriesArticle, $prodDeliveryResults, $result, $permissions, $options);
	
		if(isset($options['tr.no_display']))
			$tmp .= $this->__draw_js_backoffice($rowId);
	
		$k = 1 - $k;
	
		return $tmp;
	}
	
	private function __drawBackOfficeFieldsHidden($rowId, $numProdDeliveriesArticle, $prodDeliveryResults, $result, $permissions, $options) {
	
		$tmp  = "";
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
		$tmp .= '<input class="debug" type="hidden" value="'.$result['ProdDeliveriesArticle']['prod_delivery_id'].'" id="prod_delivery_id-'.$rowId.'" />';
		$tmp .= "\n";
		$tmp .= '<input class="debug" type="hidden" value="'.$result['ProdDeliveriesArticle']['prezzo'].'" id="prezzo-'.$rowId.'" />';
		
		$tmp .= "\n";
		if($this->debug) $tmp .= 'PDA.stato';
		$tmp .= '<input class="debug" type="'.$type_input.'" value="'.$result['ProdDeliveriesArticle']['stato'].'" id="prodDeliveriesArticle_stato-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'PDA.article_organization_id';
		$tmp .= '<input class="debug" type="'.$type_input.'" value="'.$result['ProdDeliveriesArticle']['article_organization_id'].'" id="article_organization_id-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'PDA.article_id';
		$tmp .= '<input class="debug" type="'.$type_input.'" value="'.$result['ProdDeliveriesArticle']['article_id'].'" id="article_id-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= '<br />PDA.qta_cart';
		$tmp .= '<input class="debug" type="'.$type_input.'" value="'.$result['ProdDeliveriesArticle']['qta_cart'].'" id="qta_cart-'.$rowId.'" />';   // la qta_cart serve solo per il ctrl js, in Model/AjaxProdCart e' ricalcolato
		$tmp .= "\n";
		if($this->debug) $tmp .= 'PDA.qta_min';
		$tmp .= '<input class="debug" type="'.$type_input.'" value="'.$result['ProdDeliveriesArticle']['qta_minima'].'" id="qta_minima-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'PDA.qta_max';
		$tmp .= '<input class="debug" type="'.$type_input.'" value="'.$result['ProdDeliveriesArticle']['qta_massima'].'" id="qta_massima-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'PDA.qta_min_order';
		$tmp .= '<input class="debug" type="'.$type_input.'" value="'.$result['ProdDeliveriesArticle']['qta_minima_order'].'" id="qta_minima_order-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'PDA.qta_max_order';
		$tmp .= '<input class="debug" type="'.$type_input.'" value="'.$result['ProdDeliveriesArticle']['qta_massima_order'].'" id="qta_massima_order-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'PDA.qta_mult';
		$tmp .= '<input class="debug" type="'.$type_input.'" value="'.$result['ProdDeliveriesArticle']['qta_multipli'].'" id="qta_multipli-'.$rowId.'" />';
		$tmp .= "\n";
		if($this->debug) $tmp .= 'U.id';
		$tmp .= '<input class="debug" type="'.$type_input.'" value="'.$result['User']['id'].'" id="user_id-'.$rowId.'" />';
		
		/*
		 * qta_prima_modifica
		 * 	la prima volta che da backOffice modifico la qta, qta_prima_modifica = $result['ProdCart']['qta']
		 *  le volte successive, $result['ProdCart']['qta_forzato']
		 */
		if($result['ProdCart']['qta_forzato']==0) 
			$qta_prima_modifica = $result['ProdCart']['qta'];
		else
			$qta_prima_modifica = $result['ProdCart']['qta_forzato'];
		
		if($this->debug) $tmp .= 'C.qta_prima_mod';
		if(empty($qta_prima_modifica)) $qta_prima_modifica=0;
		$tmp .= '<input class="debug" type="'.$type_input.'" value="'.$qta_prima_modifica.'" id="qta_prima_modifica-'.$rowId.'" />';  // serve in caso ProdDeliveriesArticle.stato = QTAMAXORDER o ProdCarts.stato = LOCK per bloccare il tasto +
		
		return $tmp;
	}		
	
	private function __drawBackOfficeCart($rowId, $numProdDeliveriesArticle, $prodDeliveryResults, $result, $permissions, $options) {
		
		$tmp  = "";
		$tmp .= "\n ";
		if($prodDeliveryResults['ProdDelivery']['permissionToEditProduttore']) {
			$tmp  .= $this->__consegnaModificabileBackOffice($rowId, $prodDeliveryResults, $result);
				
			if(!isset($options['tr.no_display'])) {
				$tmp .= '</tr>';
					
				$tmp .= '<tr class="trView" id="trViewId-'.$rowId.'">';
				$tmp .= '<td colspan="2"></td>';
				$tmp .= '<td colspan="10" id="tdViewId-'.$rowId.'"></td>';
				$tmp .= '</tr>';
			}
		
		}
		else {
			$tmp  .= $this->__consegnaNonModificabileBackOffice($rowId, $prodDeliveryResults, $result); // consegna non modificabile (scaduto)
				
			if(!isset($options['tr.no_display'])) {
				$tmp .= '</tr>';
					
				$tmp .= '<tr class="trView" id="trViewId-'.$rowId.'">';
				$tmp .= '<td colspan="2"></td>';
				$tmp .= '<td colspan="7" id="tdViewId-'.$rowId.'"></td>';
				$tmp .= '</tr>';
			}
		}
		
		return $tmp;
	}
		
	/*
	 * C O N S E G N A   M O D I F I C A B I L E       B A C K - O F F I C E 
	 */
	private function __consegnaModificabileBackOffice($rowId, $prodDeliveryResults, $result) {
		
		$tmp = "";
		
		$importo = ($result['ProdDeliveriesArticle']['prezzo'] * $result['ProdCart']['qta']);
		
		$tmp .= "\n";  // Quantità dell'utente: non modificabile
		$tmp .= '<td style="white-space:nowrap;text-align:center;">';
		if(!empty($result['ProdCart']['qta'])) $tmp .= $result['ProdCart']['qta'];
		$tmp .= '</td>';
		
		$tmp .= "\n";  // Importo dell'utente: non modificabile
		$tmp .= '<td style="white-space:nowrap;">'; 
		if($importo > 0) 
			$tmp .= number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		$tmp .= '</td>';
		
		$tmp .= "\n";  // Stato
		$tmp .= '<td ';
		if($result['ProdDeliveriesArticle']['stato']=='LOCK' || $result['ProdDeliveriesArticle']['stato']=='QTAMAXORDER')
			$tmp .= ' class="stato_'.strtolower($result['ProdDeliveriesArticle']['stato']).'" title="Stato dell\'articolo associato alla consegna: '.$this->traslateEnum($result['ProdDeliveriesArticle']['stato']).'"';
		$tmp .= '></td>';
		
		// Quantità forzato impostata dal referente
		if($result['ProdCart']['qta_forzato']==0)
			$qta_forzato = $result['ProdCart']['qta'];
		else
			$qta_forzato = $result['ProdCart']['qta_forzato'];
		if($qta_forzato>0) $classQta = "qtaUno";
		else $classQta = "qtaZero";
		
		$tmp .= '<td style="white-space:nowrap;text-align:center;width: 150px;">';
		$tmp .= '<div id="qta-'.$rowId.'" class="'.$classQta.' qta">'.$qta_forzato.'</div>';
		$tmp .= '<div id="buttonPiuMeno-'.$rowId.'" class="buttonPiuMeno" style="display:none;">';
		$tmp .= '<img alt="compra in +" src="'.Configure::read('App.img.cake').'/button-piu.png" class="buttonCarrello buttonPiu" />';
		$tmp .= "\n";
		$tmp .= '<img alt="compra in -" src="'.Configure::read('App.img.cake').'/button-meno.png" class="buttonCarrello buttonMeno" />';
		$tmp .= "\n";
		$tmp .= '</div>';
		$tmp .= "\n";
		$tmp .= '</td>';
	
		$tmp .= "\n";  // Importo calcolato (qta_forzato * prezzo) modificato dal referente
		$tmp .= '<td style="white-space:nowrap;">';
		$tmp .= '<div id="prezzoNew-'.$rowId.'" class="prezzoNew">';
		$importo = number_format(($qta_forzato * $result['ProdDeliveriesArticle']['prezzo']),2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')); 
		if($importo>0) $tmp .= $importo.'&nbsp;&euro;';
		$tmp .= '</div>';
		
		$tmp .= "\n";
		$tmp .= '<img alt="" src="'.Configure::read('App.img.cake').'/blank32x32.png" id="submitEcomm-'.$rowId.'" class="buttonCarrello submitEcomm" />';
		$tmp .= "\n";
		$tmp .= '<div id="msgEcomm-'.$rowId.'" class="msgEcomm"></div>';
		$tmp .= "\n";
		$tmp .= '</td>';
		
		/*
		 * se non ho ancora effettuato acquisti dell'articolo 
		 * 	non visualizzo i campi importo_forzato e nota
		 */
		if(empty($result['ProdCart']['user_id']) ? $display = 'display:none;' : $display = 'display:inline;');
		
		$tmp .= "\n"; // Importo nuovo
		$tmp .= '<td id="importoForzato-'.$rowId.'" style="white-space:nowrap;">';
			
		$tmp .= "\n";
		if(empty($result['ProdCart'])) $importo_forzato = number_format(0,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
		else
			$importo_forzato = number_format($result['ProdCart']['importo_forzato'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			
		$tmp .= '<input tabindex="'.($this->tabindex++).'" type="text" style="'.$display.'" value="'.$importo_forzato.'" name="importo_forzato-'.$rowId.'" id="importo_forzato-'.$rowId.'" size="5" class="importo_forzato" />&nbsp;<span id="importo_forzato_testo-'.$rowId.'" style="'.$display.'">&euro;</span>';
		$tmp .= '</td>';

		$tmp .= "\n"; // Nota
		$tmp .= '<td id="nota-'.$rowId.'">';
		if(!empty($result['ProdCart']['nota']))
			$tmp .= '<img style="'.$display.'" class="notaEcomm" id="notaEcomm-'.$rowId.'" alt="Aggiungi una nota all\'acquisto" src="'.Configure::read('App.img.cake').'/actions/32x32/playlist.png"></span>';
		else
			$tmp .= '<img style="'.$display.'" class="notaEcomm" id="notaEcomm-'.$rowId.'" alt="Aggiungi una nota all\'acquisto" src="'.Configure::read('App.img.cake').'/actions/32x32/filenew.png"></span>';
		$tmp .= '</td>';	

		return $tmp;
	}
	
	/*
	 * C O N S E G N A   M O D I F I C A B I L E   F R O N T _ E N D
	 */
	private function __consegnaModificabileFrontEnd($rowId, $prodDeliveryResults, $result) {
		
		$tmp = "";

		$tmp .= "\n";  // prodDeliveriesArticle.Stato 'Y', 'N', 'LOCK', 'QTAMAXORDER'
		$tmp .= '<td ';
		if($result['ProdDeliveriesArticle']['stato']!='Y')	$tmp .= ' class="stato_'.strtolower($result['ProdDeliveriesArticle']['stato']).'" title="Stato dell\'articolo associato all\'ordine: '.$this->traslateEnum($result['ProdDeliveriesArticle']['stato']).'"';
		$tmp .= '></td>';
		
		$tmp .= "\n";  // Qta
		$qta = $result['ProdCart']['qta'];
		if($qta>0) $classQta = "qtaUno";
		else $classQta = "qtaZero";
		$tmp .= '<td style="white-space:nowrap;text-align:center;width: 125px;">';
		$tmp .= "\n";
		$tmp .= '<div id="qta-'.$rowId.'" class="'.$classQta.' qta">'.$qta.'</div>';
		$tmp .= "\n";
		$tmp .= '<div id="buttonPiuMeno-'.$rowId.'" class="buttonPiuMeno" style="display:none;">';
		$tmp .= '<img alt="compra in +" src="'.Configure::read('App.img.cake').'/button-piu.png" class="buttonCarrello buttonPiu" />';
		$tmp .= "\n";
		$tmp .= '<img alt="compra in -" src="'.Configure::read('App.img.cake').'/button-meno.png" class="buttonCarrello buttonMeno" />';
		$tmp .= "\n";
		$tmp .= '</div>';
		$tmp .= "\n";
		$tmp .= '</td>';
	
		$tmp .= '<td style="white-space:nowrap;">';
		$tmp .= '<div id="prezzoNew-'.$rowId.'" class="prezzoNew">';
		$tmp .= "\n";
		if(!empty($result['ProdCart']['importo'])) 
			$tmp .= number_format($result['ProdCart']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		
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
	 * C O N S E G N A   N O N   M O D I F I C A B I L E   F R O N T _ E N D 
	 */
	private function __consegnaNonModificabileFrontEnd($rowId, $prodDeliveryResults, $result) {
		
		$tmp = "";

		$tmp .= "\n";  // prodDeliveriesArticle.Stato 'Y', 'N', 'LOCK', 'QTAMAXORDER'
		$tmp .= '<td ';
		if($result['ProdDeliveriesArticle']['stato']!='Y')
			$tmp .= ' class="stato_'.strtolower($result['ProdDeliveriesArticle']['stato']).'" title="Stato dell\'articolo associato all\'ordine: '.$this->traslateEnum($result['ProdDeliveriesArticle']['stato']).'"';
		$tmp .= '></td>';
		
		$tmp .= "\n";   // Qta
		$qta = $result['ProdCart']['qta'];
		if($qta>0) $classQta = "qtaUno";
		else $classQta = "qtaZero";
		$tmp .= '<td style="white-space:nowrap;text-align:center;">';
		$tmp .= "\n";
		$tmp .= '<div id="qta-'.$rowId.'" class="'.$classQta.' qta">'.$qta.'</div>';
		$tmp .= "\n";
		$tmp .= '</td>';
		
		$tmp .= '<td style="white-space:nowrap;">';
		$tmp .= '<div id="prezzoNew-'.$rowId.'" class="prezzoNew">';
		$tmp .= "\n";
		if(!empty($result['ProdCart']['importo'])) 
			$tmp .= number_format($result['ProdCart']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';	
		$tmp .= '</div>';
		$tmp .= '</td>';
				
		return $tmp;
	}


	/*
	 * C O N S E G N A   N O N   M O D I F I C A B I L E   B A C K - O F F I C E
	*/
	private function __consegnaNonModificabileBackOffice($rowId, $prodDeliveryResults, $result) {
		
		$tmp = "";
		$importo = ($result['ProdDeliveriesArticle']['prezzo'] * $result['ProdCart']['qta']);
		
		$tmp .= "\n";
		$qta = $result['ProdCart']['qta'];
		if($qta>0) $classQta = "qtaUno";
		else $classQta = "qtaZero";
 	
		$tmp .= "\n";  // Quantità dell'utente 	
		$tmp .= '<td style="white-space:nowrap;text-align:center;">';
		$tmp .= '<div id="qta-'.$rowId.'" class="'.$classQta.' qta">'.$qta.'</div>';
		$tmp .= "\n";
		$tmp .= '</td>';
	
		$tmp .= "\n";  // Importo dell'utente
		$tmp .= '<td style="white-space:nowrap;">';
		$tmp .= '<div id="prezzoNew-'.$rowId.'" class="prezzoNew">';
		if($importo > 0) 
			$tmp .= number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		$tmp .= '</div>';
		$tmp .= '</td>';
		
		$tmp .= "\n";  // Stato
		$tmp .= '<td ';
		if($result['ProdDeliveriesArticle']['stato']=='LOCK' || $result['ProdDeliveriesArticle']['stato']=='QTAMAXORDER')
			$tmp .= ' class="stato_'.strtolower($result['ProdDeliveriesArticle']['stato']).'" title="Stato dell\'articolo associato alla consegna: '.$this->traslateEnum($result['ProdDeliveriesArticle']['stato']).'"';
		$tmp .= '></td>';
		
		return $tmp;
	}
	
	/*
	 * crea un ID per identificare la riga univoca
	 * $numProdDeliveriesArticle = numero incrementale della riga, serve quando ricostruisco la riga dopo il save 
	 */
	private function __getRowId($numProdDeliveriesArticle, $prodDeliveryResults, $result) {
		 
		$rowId = $prodDeliveryResults['ProdDelivery']['id'].'_'.$result['ProdDeliveriesArticle']['article_organization_id'].'_'.$result['ProdDeliveriesArticle']['article_id'].'_'.$result['ProdDeliveriesArticle']['stato'].'_'.$numProdDeliveriesArticle;
		 
		 return $rowId;
	}	

	/*
	 * viene ri-caricata solo la righa dopo il salvataggio
	*/
	private function __draw_js_backoffice($rowId) {
		$tmp = "";
	
		$tmp  .= '<script type="text/javascript">';
		$tmp  .= "\r\n";
		$tmp  .= 'jQuery(document).ready(function() {';
		$tmp  .= "\r\n";
		$tmp  .= '	activeSubmitEcomm(jQuery("#row-'.$rowId.'"));';
		$tmp  .= "\r\n";
		$tmp  .= '	activeEcommRows(jQuery("#row-'.$rowId.'"));';
		$tmp  .= "\r\n";
		$tmp  .= '	actionTrView(jQuery("#actionTrView-'.$rowId.'"));';
		$tmp  .= "\r\n";
		$tmp  .= '	actionNotaView(jQuery("#actionNotaView-'.$rowId.'"));';
		$tmp  .= "\r\n";
		$tmp  .= '	activeImportoForzato(jQuery("#importoForzato-'.$rowId.'"));';
		$tmp  .= "\r\n";
		$tmp  .= '	activeNotaEcomm(jQuery("#nota-'.$rowId.'"));';
		$tmp  .= "\r\n";
		$tmp  .= '});';
		$tmp  .= '</script>';
	
		return $tmp;
	}
	
}		
?>