<?php 
$tmp = '';
if(!empty($results)) {
	$tmp .= '[';
	foreach($results as $numResult => $result):
		$tmp .= '{"id":"'.$numResult.'","value":"'.$result['Supplier']['name'].'"},';
	endforeach;
	$tmp = substr($tmp, 0, (strlen($tmp)-1));
	$tmp .= ']';
}
echo $tmp;
?>

