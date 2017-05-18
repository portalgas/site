
<script type="text/javascript" src="http://localhost:81/components/com_cake/app/webroot/js/jquery/jquery-1.10.1.min.js"></script>
<script type="text/javascript" src="http://localhost:81/components/com_cake/app/webroot/js/jquery/jquery-ui-1.10.3.custom.min.js"></script>
<link rel="stylesheet" type="text/css" href="http://localhost:81/components/com_cake/app/webroot/ui-themes/smoothness/jquery-ui-1.10.3.custom.min.css">

<script type="text/javascript" src="http://localhost:81/components/com_cake/app/webroot/js/i18n/ui.datepicker-it.js"></script>
<script type="text/javascript" src="http://localhost:81/components/com_cake/app/webroot/js/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="http://localhost:81/components/com_cake/app/webroot/js/ckeditor/adapters/jquery.js"></script>

<!-- 
<link rel="stylesheet" type="text/css" href="http://localhost:81/components/com_cake/app/webroot/css/style-min.css?20140128">
<link rel="stylesheet" type="text/css" href="http://localhost:81/components/com_cake/app/webroot/css/styleBackoffice-min.css?20140128">
<link rel="stylesheet" type="text/css" href="http://localhost:81/components/com_cake/app/webroot/css/tabs-min.css">
-->


<?php		
echo $this->Tabs->setTableHeaderEcommSimpleFrontEnd($delivery['id']);

foreach($results as $numResult => $result)
	echo $this->ProdRowEcomm->drawFrontEndComplete($numResult, $result);
?>
<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery(".rowEcomm").each(function () {
		activeEcommRows(this);    /* active + / - , mouseenter mouseleave */
		activeSubmitEcomm(this);
		activeImportoForzato(this);
		activeNotaEcomm(this);		
	});	
	
	jQuery('.actionTrView').css('display','inline-block');  /* rendo visibile il tasto espandi per i dettagli ajax */
	
	jQuery('.actionTrView').each(function () {
		actionTrView(this);
	});
	
	jQuery('.actionNotaView').css('display','inline-block');  /* rendo visibile il tasto espandi per i dettagli ajax */
	
	jQuery('.actionNotaView').each(function () {
		actionNotaView(this); 
	});
});	
</script>