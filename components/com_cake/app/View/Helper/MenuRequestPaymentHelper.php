<?php
class MenuRequestPaymentHelper extends AppHelper {
        
     var $helpers = array('Html', 'App');

     public function drawWrapper($request_payment_id, $options = array()) {

		$html = "";
		
		if(!isset($request_payment_id) || empty($request_payment_id)) 
			return $html;

		/*
		 * OPTIONS
		 * di default e' close
		 */
		$optOpenCloseClassCss = '';
		if(isset($options['openCloseClassCss']))
			$optOpenCloseClassCss = $options['openCloseClassCss'];
		
		/*
		 * nav-min per gestione ridotta
		 */
		$html .= '<nav class="nav-min-disabled navbar navbar-fixed-left navbar-minimal animate '.$optOpenCloseClassCss.'" role="navigation">';
		$html .= '	<div class="navbar-toggler animate">';
		$html .= '		<span class="menu-icon"></span>';
		$html .= '	</div>';

		$html .= '	<ul id="order-sotto-menu-'.$request_payment_id.'" class="navbar-menu animat order-sotto-menu-uniquee">';		
		$html .= '	</ul>';
		
		$html .= '</nav>';
		
		$html .= '<script type="text/javascript">';
		/*
		if($isReferenteTesoriere) 
			$html .= 'viewReferenteTesoriereRequestPaymentSottoMenuBootstrap('.$request_payment_id.');';
		else
		*/
			$html .= 'viewTesoriereRequestPaymentSottoMenuBootstrap('.$request_payment_id.');';		
		$html .= '</script>';
					
		if($optOpenCloseClassCss=='open') {
			$html .= '<style>';
			$html .= '.cakeContainer div.contentMenuLaterale {padding-left:320px;}';
			$html .= '</style>';			
		}	
		
		
		return $html;
	}	
}
?>