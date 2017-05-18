<style type="text/css">
.cakeContainer .legenda table tr td.content {
    background-color: #FFFFFF;
    border: 0 none;
    padding: 5px;
}
.cakeContainer .legenda .result {
    background-color: #FFFFAA;
    border: 0 none;
    margin-top: 25px;
    padding: 5px;
}
</style>
<?php 
/*
* Struttura del file 
*/

echo '<div class="legenda legenda-ico-info" style="width:94%">';

if(isset($msg)) echo '<h2>'.$msg.'</h2>'; 
else echo '<h2>Struttura del file .csv</h2>';

echo '<table>';
echo '<tr>';
echo '<th>Num colonna</th>';
for ($i=0; $i < count($struttura_file); $i++) 			
	echo '<th>'.($i+1).'</th>';		
echo '</tr>';

echo '<tr>';
echo '<td></td>';
for ($i=0; $i < count($struttura_file); $i++) 
	echo '<th>'.$struttura_file[$i]['LABEL'].'</th>';
echo '</tr>';

echo '<tr>';
echo '<td></td>';
for ($i=0; $i < count($struttura_file); $i++) 
	echo '<td class="content">'.$struttura_file[$i]['EXAMPLE_VALUE1'].'</td>';
echo '</tr>';
echo '<tr>';
echo '<td></td>';
for ($i=0; $i < count($struttura_file); $i++) 
	echo '<td class="content">'.$struttura_file[$i]['EXAMPLE_VALUE2'].'</td>';
echo '</tr>';

echo '</table>';

$tmp = "";
for ($i=0; $i < count($struttura_file); $i++) 
	$tmp .= $struttura_file[$i]['EXAMPLE_VALUE1'].'|';
$tmp = substr($tmp,0,strlen($tmp)-1);

$tmp .= '<br />';

for ($i=0; $i < count($struttura_file); $i++) 
	$tmp .= $struttura_file[$i]['EXAMPLE_VALUE2'].'|';
$tmp = substr($tmp,0,strlen($tmp)-1);

echo '<p class="result">';
echo $tmp;
echo '</p>';

echo '<h2>Note</h2>';
echo '<ul>';
echo "<li>- sono consentiti file con un massimo di <b>$rowsMax</b> righe</li>";

if(isset($array_um) && !empty($array_um)) {
	echo '<li>- valori consentiti per i campi "Unità di misura" e "Unità di misura di riferimento": ';
	
	foreach ($array_um as $um) 
		echo $um.', '; 
	
	echo '</li>';
}

echo '</ul>';
echo '</div>';
?>