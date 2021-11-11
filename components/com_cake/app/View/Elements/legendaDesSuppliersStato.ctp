<?php
$htmlLegenda = '';

/*
echo "<pre>";
print_r($desSupplierStates);
echo "</pre>";
*/
		
$colsWidth = floor(100/count($desSupplierStates));
	
$htmlLegenda = '<div class="legenda">';
$htmlLegenda .= '<table cellpadding="0" cellspacing="0" border="0">';
$htmlLegenda .= "\r\n";

$htmlLegenda .= '<tr>';
foreach($desSupplierStates as $desSupplierState) {
	
	$target = 'Referente';
		
	$htmlLegenda .= "\r\n";
	$htmlLegenda .= '<td width="'.$colsWidth.'%"><h3>';
	$htmlLegenda .= $target;
	$htmlLegenda .= '</h3></td>';
}
$htmlLegenda .= '</tr>';
		
$htmlLegenda .= '<tr>';
foreach($desSupplierStates as $desSupplierState) {
	$htmlLegenda .= "\r\n";
	$htmlLegenda .= '<td id="icoOrder'.$desSupplierState['state_code'].'" class="tdLegendaOrdersStateIco">';
	$htmlLegenda .= '<div style="padding-left:45px;width: 80%;cursor: pointer;" class="action desSupplierStato'.$desSupplierState['state_code'].'" title="'.__('DES-'.$desSupplierState['state_code'].'-intro').'">'.__('DES-'.$desSupplierState['state_code'].'-label').'</div>&nbsp;';
	$htmlLegenda .= '</td>';

}
$htmlLegenda .= '</tr>';

$htmlLegenda .= '<tr>';
$htmlLegenda .= '<td id="tdLegendaOrdersStateTesto" colspan="'.count($desSupplierStates).'" style="border-bottom:none;background-color:#FFFFFF;height:50px;">';

$htmlLegenda .= "\r\n";
foreach($desSupplierStates as $desSupplierState) {
	$htmlLegenda .= "\r\n";
	$htmlLegenda .= '<div class="testoLegendaTesoriereStato" id="testoOrder'.$desSupplierState['state_code'].'" style="display:none;">';
	$htmlLegenda .= __('DES-'.$desSupplierState['state_code'].'-descri');
	$htmlLegenda .= '</div>';
}
$htmlLegenda .= '</td>';
$htmlLegenda .= '</tr>';

$htmlLegenda .= '</table>';


$htmlLegenda .= "\r\n";
$htmlLegenda .= '<script type="text/javascript">';
$htmlLegenda .= "\r\n";
$htmlLegenda .= 'function bindLegenda() {';
$htmlLegenda .= "\r\n";
foreach($desSupplierStates as $desSupplierState) {
	$htmlLegenda .= "\r\n";
	$htmlLegenda .= '$( ".desSupplierStato'.$desSupplierState['state_code'].'" ).mouseenter(function () {';
	$htmlLegenda .= "\r\n";
	$htmlLegenda .= '	$(".tdLegendaOrdersStateIco").css("background-color","#ffffff").css("border-radius","0px");';
	$htmlLegenda .= "\r\n";
	$htmlLegenda .= '	$(".testoLegendaTesoriereStato").hide();';
	$htmlLegenda .= "\r\n";
	$htmlLegenda .= '	$("#icoOrder'.$desSupplierState['state_code'].'").css("background-color","yellow").css("border-radius","15px 15px 15px 15px");';
	$htmlLegenda .= "\r\n";
	$htmlLegenda .= '	$(".tdLegendaOrdersStateTesto").css("background-color","#F0F0F0");';
	$htmlLegenda .= "\r\n";
	$htmlLegenda .= '	$("#testoOrder'.$desSupplierState['state_code'].'").show();';
	$htmlLegenda .= "\r\n";
	$htmlLegenda .= '});';

	$htmlLegenda .= "\r\n";
	$htmlLegenda .= '$( ".desSupplierStato'.$desSupplierState['state_code'].'" ).mouseleave(function () {';
	$htmlLegenda .= "\r\n";
	$htmlLegenda .= '	$(".tdLegendaOrdersStateIco").css("background-color","#ffffff");';
	$htmlLegenda .= "\r\n";
	$htmlLegenda .= '	$(".testoLegendaTesoriereStato").hide();';
	$htmlLegenda .= "\r\n";
	$htmlLegenda .= '});';

}
$htmlLegenda .= "\r\n";
$htmlLegenda .= '}</script>';
$htmlLegenda .= '</div>';
$htmlLegenda .= "\r\n";
$htmlLegenda .= '<script type="text/javascript">';
$htmlLegenda .= '$(document).ready(function() {bindLegenda();});';
$htmlLegenda .= "\r\n";
$htmlLegenda .= '</script>';
$htmlLegenda .= "\r\n";


echo $htmlLegenda;