<?php
echo '<div class="input required">';
echo '<label class="control-label">'.__('OrderTypeGest').'</label>';
echo '<div style="width:75%;float: right;">';
echo '<div class="table-responsive"><table class="table">';

/* ********************************************************************************************************
<tr>
	<td style="width: 32px;">
		<input type="radio" checked="checked" value="" id="" name="data[Order][typeGestDisabled]" />
	</td>		
	<td style="width: 32px;">
		<div style="height: 32px;" title="'.__('Management Carts One').'" class="actionEditDbOne"></div>
	</td>
	<td>
		'.echo __('Management Carts One').'
	</td>
	<td></td>
</tr>
<tr>
	<td colspan="4">ed inoltre</td>
</tr>
******************************************************************************************************** */ 

echo '<tr>';
echo '<td>';		
if($modalita=='VIEW') {
	if(empty($value)) echo '<input type="radio" checked="checked" value="" id="" name="data[Order][typeGest]" />';
}
else {
	echo '<input type="radio"'; 
	if(empty($value)) echo 'checked="checked" ';			
	echo 'value="" id="" name="data[Order][typeGest]" />';
}

echo '</td>';
echo '<td></td>';
echo '<td>Nessuno di questi</td>';
echo '<td></td>';
echo '</tr>';
echo '<tr>';
echo '<td>';
if($modalita=='VIEW') {
	if($value=='AGGREGATE') echo '<input type="radio" checked="checked" value="AGGREGATE" id="" name="data[Order][typeGest]" />';
}
else {
	echo '<input type="radio"'; 
	if($value=='AGGREGATE') echo 'checked="checked" ';			
	echo 'value="AGGREGATE" id="" name="data[Order][typeGest]" />';
}
echo '</td>';
echo '<td>';
echo '<div style="height: 32px;width: 32px;" title="'.__('Management Carts Group By Users').'" class="actionEditDbGroupByUsers"></div>';
echo '</td>';
echo '<td>';
echo __('Management Carts Group By Users');
echo '</td>';
echo '<td style="width:50px;">';
echo '<button class="btn btn-primary" data-toggle="collapse" href="#collapseExampleAggregate" aria-expanded="false" aria-controls="collapseExampleAggregate">';
echo '<i class="fa fa-2x fa-info-circle" aria-hidden="true"></i> Clicca per visualizzare l\'esempio';
echo '</button>';
echo '</td>';
echo '</tr>';
echo '<tr>';
echo '<td>';
if($modalita=='VIEW') {
	if($value=='SPLIT') echo '<input type="radio" checked="checked" value="AGGREGATE" id="" name="data[Order][typeGest]" />';
}
else {
	echo '<input type="radio"'; 
	if($value=='SPLIT') echo 'checked="checked" ';			
	echo 'value="SPLIT" id="" name="data[Order][typeGest]" />';
}
echo '</td>';
echo '<td>';
echo '<div style="height: 32px;width: 32px;" title="'.__('Management Carts Split').'" class="actionEditDbSplit"></div>';
echo '</td>';
echo '<td>';
echo __('Management Carts Split');
echo '</td>';
echo '<td style="width:50px;">';
echo '<button class="btn btn-primary" data-toggle="collapse" href="#collapseExampleSplit" aria-expanded="false" aria-controls="collapseExampleSplit">';
echo '<i class="fa fa-2x fa-info-circle" aria-hidden="true"></i> Clicca per visualizzare l\'esempio';
echo '</button>';
echo '</td>';
echo '</tr>';
echo '</table></div>';
	


echo '<div class="collapse" id="collapseExampleAggregate">';
echo '<h2>Esempio: '.__('Management Carts Group By Users').'</h2>';
echo '<div class="table-responsive"><table class="table">';
echo '<tr>';
echo '<th>Gasista</th>';
echo '<th>ha ordinato</th>';
echo '<th>con l\'importo</th>';
echo '<th>Gestito la somma degli importi</th>';
echo '</tr>';
echo '<tr>';
echo '<td rowspan="2">Rossi Mario</th>';
echo '<td>2 orate</th>';
echo '<td>10,00&nbsp;&euro;</th>';
echo '<td rowspan="2">10,00&nbsp;&euro; + 5,00&nbsp;&euro; = <b>15,00</b>&nbsp;&euro;</td>';
echo '</tr>';
echo '<tr>';
echo '<td>1 branzino</th>';
echo '<td>5,00&nbsp;&euro;</th>';
echo '</tr>';
echo '</table></div>';
echo '</div>';
			
echo '<div class="collapse" id="collapseExampleSplit">';
echo '<h2>Esempio: '.__('Management Carts Split').'</h2>';
echo '<div class="table-responsive"><table class="table">';
echo '<tr>';
echo '<th>Gasista</th>';
echo '<th>ha ordinato</th>';
echo '<th>con l\'importo</th>';
echo '<th>Gestito ogni singola quantità</th>';
echo '</tr>';
echo '<tr>';
echo '<td rowspan="2">Rossi Mario</th>';
echo '<td rowspan="2"><b>2</b> orate</th>';
echo '<td rowspan="2">10,00&nbsp;&euro;</th>';
echo '<td><b>1</b> orate&nbsp;&nbsp;....&nbsp;&euro;</th>';
echo '</tr>';
echo '<tr>';
echo '<td><b>1</b> orata&nbsp;&nbsp;....&nbsp;&euro;</th>';
echo '</tr>';
echo '</table></div>';
echo '</div>';
			
echo '</div>';
echo '</div>';
?>