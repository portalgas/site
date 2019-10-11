<?php
echo '<h2>Fai la tua spesa</h2>';

/*
 * filtro
 */
echo $this->Form->create('FilterEcomm',array('id'=>'formGasFilter','type'=>'get', 'class' => 'legenda', 'onSubmit' => 'return ctrlSubmit();'));
echo '<fieldset class="filter">';
echo '<legend>Filter ecomm</legend>';	
echo '<div id="ecomm-filter" style="float:none;clear:both;height:auto;">';	
echo '	<div class="ecomm-filter-content-right" style="float:left;">';	
echo '		<input type="submit" name="ecomm-filter-label" value="Filtra" class="btn btn-orange" id="ecomm-filter-label" />';	
echo '	</div>';

/*
 * nome articolo
 */
$FilterArticleNameIntro = "Filtra per il nome dell'articolo";
if(empty($FilterArticleName)) {
	$FilterArticleName = $FilterArticleNameIntro;
	$FilterArticleNameEmpty = true;
}
else 
	$FilterArticleNameEmpty = false;

$options = [];
$options = array('label' => false, 'value' => $FilterArticleName, 'size' => '50px', 'name' => 'FilterArticleName');
if($FilterArticleNameEmpty) {
	$options += array('onFocus' => 'javascript:startFilterArticleName();');

	echo '<script type="text/javascript">';
	echo 'function startFilterArticleName() {';
	echo "\n";
	echo '	$("input[name=FilterArticleName]").val("").css("color","#000");';
	echo "\n";
	echo '}';
	echo "\n";
	echo '$(document).ready(function() {';
	echo '	$("input[name=FilterArticleName]").css("color","#dedede");';
	echo '});';
	echo '</script>';
}	
echo '	<div class="ecomm-filter-content-middle" style="float:left;padding-left:10px;">';
echo $this->Ajax->autoComplete('FilterArticleName', Configure::read('App.server').'/?option=com_cake&controller=Ajax&action=autoCompleteArticlesName&supplier_organization_id='.$prodDelivery['ProdDelivery']['supplier_organization_id'].'&format=notmpl',$options);
echo '	</div>';							
/*
 * tipologie articoli
 */
 $array_selecteds = explode(',', $FilterArticleArticleTypeIds); 
echo '	<div class="ecomm-filter-content-left" style="float:left;padding:10px 0 0 10px;">';
foreach($ArticlesTypeResults as $key => $value) {

	echo '<label for="ArticleFilterArticleArticleTypeIds'.$key.'" style="margin:0 3px 0 10px;">'.$value.'</label> ';
	echo '<input type="checkbox" name="FilterArticleArticleTypeIds" id="ArticleFilterArticleArticleTypeIds'.$key.'" value="'.$key.'" ';
	
	foreach($array_selecteds as $array_selected) {
		if($array_selected==$key)	echo ' checked="checked" ';
		break;
	}
	echo '/>';
}
echo '	</div>';

echo '</div>';
echo '</fieldset>';
echo '</form>';		
		

if(!empty($results)) {

	echo '<input type="hidden" name="ProdDelivery_type_draw" id="prod_delivery_type_draw" value="'.$prodDelivery['ProdDelivery']['type_draw'].'" />';
	

	// echo $this->Tabs->setTableHeaderEcommCompleteFrontEnd($prodDelivery['ProdDelivery']['id']);
	echo $this->Tabs->setTableHeaderEcommSimpleFrontEnd($prodDelivery['ProdDelivery']['id']);

	foreach($results as $numProdDeliveriesArticle => $result) {

		if($prodDelivery['ProdDelivery']['type_draw']=='COMPLETE')
			echo $this->ProdRowEcomm->drawFrontEndComplete($numProdDeliveriesArticle, $prodDelivery, $result);
		else
			echo $this->ProdRowEcomm->drawFrontEndSimple($numProdDeliveriesArticle, $prodDelivery, $result);			
	}
	echo '</table>';
}
else {
	echo '<p style="clear: both;float: none;"></p>';
	if(!empty($FilterArticleName) || !empty($FilterArticleArticleTypeIds)) 
		$msg = "Nessun articolo trovato con i parametri di filtro che hai impostato";
	else
		$msg = "Non ci sono articoli disponibili";

	echo '<div id="system-message-container">';
	echo '<dl id="system-message">';
	echo '<dt class="notice">Message</dt>';
	echo '<dd class="notice message">';
	echo '<ul><li>'.$msg.'</li></ul>';
	echo '</dd>';
	echo '</dl>';
	echo '</div>';
}

?>
<script type="text/javascript">
$(document).ready(function() {

	$(".rowEcomm").each(function () {
		activeEcommRows(this);    /* active + / - , mouseenter mouseleave */
		activeSubmitEcomm(this);	
	});	
	
	$('.actionTrView').css('display','inline-block');  /* rendo visibile il tasto espandi per i dettagli ajax */
	
	$('.actionTrView').each(function () {
		actionTrView(this);
	});
	
	$('.actionNotaView').css('display','inline-block');  /* rendo visibile il tasto espandi per i dettagli ajax */
	
	$('.actionNotaView').each(function () {
		actionNotaView(this); 
	});




	$('#ecomm-filter-label').submit(function ctrlSubmit() {});
});	

function ctrlSubmit() {     	
 	 var filterArticleName = $('input[name=FilterArticleName]').val();
 	
 	 if(filterArticleName=="<?php echo $FilterArticleNameIntro;?>") filterArticleName = ''; 
 	
 	 var filterArticleArticleTypeIds = "";
	 $("input[name=FilterArticleArticleTypeIds]:checked").each( function () {
		filterArticleArticleTypeIds += $(this).val()+',';
	 });
	 filterArticleArticleTypeIds = filterArticleArticleTypeIds.substring(0, (filterArticleArticleTypeIds.length-1));
	 
	 <?php
	 /*
	  * se ho gia' filtrato permetto la ricerca senza filtro cosi' resetto
	  */
	  if($FilterArticleName == $FilterArticleNameIntro) $FilterArticleName = '';
	  
	 if(empty($FilterArticleName) && empty($FilterArticleArticleTypeIds)) { ?>
	 if((filterArticleName=='' || filterArticleName==undefined) && filterArticleArticleTypeIds=='') {
	 	alert("Valorizza almeno un parametro di filtro!");
	 	return false;
	 }
	 <?php
	 }
	 ?>
	 
	 $('#introHelp_<?php echo $prodDelivery['ProdDelivery']['prod_delivery_id'];?>').css('display', 'none');
	 $('#articlesOrderResult_<?php echo $prodDelivery['ProdDelivery']['prod_delivery_id'];?>').css('display', 'block');
	 $('#articlesOrderResult_<?php echo $prodDelivery['ProdDelivery']['prod_delivery_id'];?>').html('');
	 $('#articlesOrderResult_<?php echo $prodDelivery['ProdDelivery']['prod_delivery_id'];?>').css('background', 'url("/images/cake/ajax-loader.gif") no-repeat scroll center 0 transparent');
	 
	 var url = "/?option=com_cake&controller=ProdDeliveries&action=ecomm&prod_delivery_id=<?php echo $prodDelivery['ProdDelivery']['prod_delivery_id'];?>";
	 url += "&a="+encodeURIComponent(filterArticleName);
	 url += "&b="+filterArticleArticleTypeIds;
	 url += "&format=notmpl";
	 
	 $.ajax({
		type: "GET",
		url: url,
		data: "",
		success: function(response){
			$('#articlesOrderResult_<?php echo $prodDelivery['ProdDelivery']['prod_delivery_id'];?>').css('background', 'none repeat scroll 0 0 transparent');
			$('#articlesOrderResult_<?php echo $prodDelivery['ProdDelivery']['prod_delivery_id'];?>').html(response);
		},
		error:function (XMLHttpRequest, textStatus, errorThrown) {
			$('#articlesOrderResult_<?php echo $prodDelivery['ProdDelivery']['prod_delivery_id'];?>').css('background', 'none repeat scroll 0 0 transparent');
			$('#articlesOrderResult_<?php echo $prodDelivery['ProdDelivery']['prod_delivery_id'];?>').html(textStatus);
		}
 	 });
	 	 
 	 return false;
 } 
</script>