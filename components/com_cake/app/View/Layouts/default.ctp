<?php
echo $this->Html->script('generic-v05.min');
//echo $this->Html->script('jquery/chosen.jquery.min'); // bootstrap 2.x
//echo $this->Html->css('chosen.min');
echo $this->Html->script('bootstrap-select.min');
echo $this->Html->css('bootstrap-select.min');

if($user->organization['Organization']['type']=='PROD') {
	echo $this->Html->script('genericEcommProd.min', array('date' => '2017feb'));
	echo $this->Html->script('genericBackOfficeProd.min', array('date' => '2017feb'));
}
else {
	/*
	 * vale per tutti i GAs e i ProdGasSuppliers
	 */
	echo $this->Html->script('genericEcomm-v03.min');
	echo $this->Html->script('genericBackOfficeGas-v02.min'); 	
}

echo $this->Html->css('bo-mobile-v01.min');
?>
<link rel="stylesheet" href="templates/bluestork/css/bootstrap.min.css" type="text/css" />
<link rel="stylesheet" href="templates/bluestork/css/font-awesome.min.css" type="text/css" />
<script src="templates/bluestork/js/bootstrap.min.js" type="text/javascript"></script>
<script type="text/javascript">
//<![CDATA[
"use strict";
var objMy;
var app_img = "<?php echo Configure::read('App.img.cake');?>";
$(function() {			
	objMy = new My();
});	

var now = new Date();
var time = now.getTime();		
//]]>	
</script>

<style type="text/css">
#toolbar-box { /* in administrator box del titolo e menu */
	display: none;
}		
</style>
			
<?php
echo '<div class="cakeContainer">';
		
echo $this->Session->flash(); 

echo $this->fetch('content');

echo '<div id="footer">';
if(Configure::read('developer.mode')) echo $this->element('boxMsg',array('class_msg' => 'notice', 'msg' => __('developer.mode'))); 
echo '</div>';
			
echo '<div id="help">';
echo '	<div class="logo">Manuali</div>';
echo '</div>';
			
echo '</div>'; // cakeContainer

echo $this->element('sql_dump');
?>