<style>.rowCart {    border-bottom: 1px solid rgb(204, 204, 204);    margin-bottom: 20px;    min-height: 240px;    padding-bottom: 15px;	}.c_item {	position: relative;	z-index: 1;	padding: 5px !important; 	width:200px;  }.c_item:hover {	z-index: 10;	padding: 5px !important;	background: #fff none repeat scroll 0 0;	border-radius: 5px;	-moz-border-radius: 5px;	-webkit-border-radius: 5px;	box-shadow: 0 0 30px rgba(0, 0, 0, 0.3);	-o-box-shadow: 0 0 30px rgba(0, 0, 0, 0.3);	-moz-box-shadow: 0 0 30px rgba(0, 0, 0, 0.3);	-webkit-box-shadow: 0 0 30px rgba(0, 0, 0, 0.3);	position: absolute; 	width:200px;  }.c_item .c_actions {    clear: both;    color: #999;    display: none;    font-size: 13px;    line-height: 16px;    padding-top: 10px;    text-align: center;    width: auto;    z-index: 10000;}.c_item:hover .c_actions {  display: block;  position: relative;}.c_item .cart-img {    height: 150px;    position: relative;    text-align: center;}.c_item .c_actions .prezzoNew, .c_item .c_actions .qta {    font-size: 14px;    color:#000 !important;}.c_item .title {    border-top: 1px solid #eee;    display: block;    padding-top: 12px;}.c_item .details-left {	clear:both;	text-align:left;	margin: 1px;	float:left;}.c_item .details-left span {	margin-left: 10px;}.c_item .details-right {	text-align:right;	margin: 1px;	float:right;}.c_item .bio {	bottom: 0;    left: 0;    margin: 0;    position: absolute;    z-index:50;}.c_item .qtaUno {    background-color: #09d96b;    color: #000;    padding: 5px;    text-align: center;}.c_item .qtaZero {    background-color: #fff;    padding: 5px;    text-align: center;}.c_item .msgEcomm {    background-color: #f2dede;    border-color: #ebccd1;    color: #a94442;    border-radius: 4px;    text-align:center;}.col-cart-md-12 {	clear:both;	float:left;	width: 100%;	padding:0;	margin:0;}.col-cart-md-6 {	float:left;	width: 50%;	padding:0;	margin:0;}.col-cart-md-3 {	float:left;	width: 25%;	padding:0;	margin:0;}.col-cart-md-2 {	float:left;	width: 16.6667%;	padding:0;	margin:0;}.col-cart-md-1 {	float:left;	width: 8.33333%;	padding:0;	margin:0;}.c_item .c_prezzo {    right: 0px;    top: 0px;    position: absolute;}.c_item .c_prezzo {    position: absolute;    z-index: 1;}.c_prezzo {    background-color: #1e83c2;    border-radius: 60px;    color: #fff;    display: block;    padding-top: 10px;    font-weight: normal;    height: 50px;    text-align: center;    width: 50px;    background-color: #1e83c2;}.c_prezzo.cart {    background-color: green;}.c_currency {    color: #fff;}.c_prezzo .c_currency {    font-size: 15px;    line-height: 16px;}.c_prezzo span {    display: block;}#tabsDelivery .buttonCarrello {    cursor: pointer;    float: none;    margin: 0 0 0 5px;    padding: 0;}</style><?php/* * gestione nome articolo */$FilterArticleNameIntro = "Filtra per il nome dell'articolo";if(empty($FilterArticleName)) {	$FilterArticleName = $FilterArticleNameIntro;	$FilterArticleNameEmpty = true;}else 	$FilterArticleNameEmpty = false;$options = array();$options = array('label' => false, 'value' => $FilterArticleName, 'size' => '50px', 'name' => 'FilterArticleName');if($FilterArticleNameEmpty) {	$options += array('class' => 'form-control input-sm', 'onFocus' => 'javascript:startFilterArticleName();');	echo '<script type="text/javascript">';	echo 'function startFilterArticleName() {';	echo "\n";	echo '	jQuery("input[name=FilterArticleName]").val("").css("color","#000");';	echo "\n";	echo '}';	echo "\n";	echo 'jQuery(document).ready(function() {';	echo '	jQuery("input[name=FilterArticleName]").css("color","#dedede");';	echo '});';	echo '</script>';}/* * filtro tipologie articoli */$tmp_articlec_type = '';$array_selecteds = explode(',',$FilterArticleArticleTypeIds);$i=0; foreach($ArticlesTypeResults as $key => $value) {	$tmp_articlec_type .= '<label for="ArticleFilterArticleArticleTypeIds'.$key.'" style="margin:0 3px 0 10px;" class="checkbox-inline">';	$tmp_articlec_type .= '<input type="checkbox" name="FilterArticleArticleTypeIds" id="ArticleFilterArticleArticleTypeIds'.$key.'" value="'.$key.'" ';		foreach($array_selecteds as $array_selected) {			if($array_selected==$key)	$tmp_articlec_type .= ' checked="checked" ';	}	$tmp_articlec_type .= '/>';		//if(($i % 2)==3)	//	$tmp_articlec_type .= '<hr/>';	$tmp_articlec_type .= $value.'</label>';		$i++;}/*  * debug	$array_selecteds = explode(',',$FilterArticleArticleTypeIds); 	echo "<pre>";	print_r($array_selecteds);	echo "</pre>";	foreach($ArticlesTypeResults as $key => $value) {			echo "<br />key: $key => value: $value";			foreach($array_selecteds as $array_selected) {			echo " - array_selected: $array_selected";				if($array_selected==$key)	echo ' checked ';		}	}*/?><?php/** filtro*/echo $this->Form->create('FilterEcomm',array('id'=>'formGasFilter_'.$order['Order']['id'],'type'=>'get', 'class' => 'legenda', 'onSubmit' => 'return ctrlSubmit_'.$order['Order']['id'].'();'));echo '<fieldset class="filter">';echo '<legend style="display:none;">Filter ecomm</legend>';	?><div class="slideshow hidden-print hidden-xs">	<div class="col-xs-12">		<div id="slideshow_<?php echo $order['Order']['id'];?>" class="carousel slide">			<div class="carousel-inner">				<div class="item active">					<div class="carousel-caption">   						<?php						/*						 * filtro						 */						echo '<div class="col-xs-1 col-md-1"></div>';							echo '<div class="col-xs-6 col-md-6">';						echo '<div class="input-group">';						echo $this->Ajax->autoComplete('FilterArticleName', Configure::read('App.server').'/?option=com_cake&controller=Ajax&action=autoCompleteArticlesName&supplier_organization_id='.$order['Order']['supplier_organization_id'].'&format=notmpl',$options);						echo '<div class="input-group-btn">';						echo '<button type="submit" class="btn btn-orange" name="ecomm-filter-label" id="ecomm-filter-label_'.$order['Order']['id'].'"><i class="glyphicon glyphicon-search"></i></button>';						echo '</div>';						echo '</div>';						echo '</div>';						echo '<div class="col-xs-4 col-md-4 hidden-sm">';						echo $tmp_articlec_type;						echo '</div>';												echo '<div class="col-xs-1 col-md-1"></div>';						?>					</div>				</div>				<div class="item">					<div class="carousel-caption">											<?php							/*							 * dettaglio ordine							 */							echo '<div class="col-xs-1 col-md-1"></div>';							 							echo '<div class="col-xs-10 col-md-10">';							 								echo '<h2 class="pull-left">';								if(!empty($supplier['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$supplier['Supplier']['img1']))
									echo '<img width="100" class="pull-left userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$supplier['Supplier']['img1'].'" /> ';								echo $supplier['Supplier']['name'];								echo '<br /><small style="color:#fff;white-space: nowrap;">';								if($order['Delivery']['sys']=='N')									echo $order['Delivery']['luogoData'];								else									echo Configure::read('DeliveryToDefinedLabel');								echo '</small></h2>';
																													echo '<div class="pull-right">';								if($order['Order']['hasTrasport']=='Y') {									echo '<img width="40" src="'.Configure::read('App.img.cake').'/help-online/trasporto.jpg" /> ';									echo '<span>l\'ordine ha le spese di <b>trasporto</b></span><br />';								}								if($order['Order']['hasCostMore']=='Y') {									echo '<img width="40" src="'.Configure::read('App.img.cake').'/help-online/carrello-piu.jpg" /> ';									echo '<span>l\'ordine ha le <b>spese aggiuntive</b></span><br />';								}									if($order['Order']['hasCostLess']=='Y') {									echo '<img width="40" src="'.Configure::read('App.img.cake').'/help-online/carrello-meno.jpg" /> ';									echo '<span>l\'ordine ha lo <b>sconto</b></span><br />';								}									if(!empty($order['Order']['typeGest'])) {									echo '<img width="40" src="'.Configure::read('App.img.cake').'/help-online/legno-cassa-pagamento.jpg" /> ';  // legno-bilancia.jpg									echo '<span>gli <b>importi</b> saranno confermati alla consegna</span><br />';								}								echo '</div>';								if(!empty($order['Order']['nota'])) {									echo '<div class="row col-xs-12">';									echo '<div role="alert" class="alert alert-info padding-right-lg padding-left-lg text-align-left">';									echo '<strong>Nota: </strong> '.strip_tags($order['Order']['nota']);									echo '</div>';									echo '</div>';								}																if($order['Order']['hasTrasport']=='N' && $order['Order']['hasCostMore']=='N' && $order['Order']['hasCostLess']=='N' && empty($order['Order']['typeGest']) && empty($order['Order']['nota'])) {									echo '<div class="row col-xs-12">';									echo '<div role="alert" class="alert alert-info padding-right-lg padding-left-lg text-align-left">';									echo "Nessuna <strong>nota</strong> per quest'ordine";									echo '</div>';																	echo '</div>';																}															/*								 *  distance								 */								if(!empty($arrayDistances)) {																	$arrayDistance = $arrayDistances[0];																		echo '<div class="row col-xs-12">';									$tot_distance = 0;									$percentuale = $arrayDistance['percentuale'];									if($percentuale==0) $percentuale = 1;																	echo '<p>';											echo 'La merce di <strong>'.$arrayDistance['supplierName'].'</strong> da '.$arrayDistance['supplierLocalita'].' percorrer&agrave; '.$arrayDistance['distance'].' Km';									echo '</p>';									echo '<div class="progressBar">';									echo '<span style="width: '.$percentuale.'%;"></span>';									echo '</div>';																		echo '</div>';																}																							echo '</div>';														echo '<div class="col-xs-1 col-md-1"></div>';						?>											</div>				</div>			</div>		</div>  		<!-- Controlli -->		 <a class="left carousel-control" href="#slideshow_<?php echo $order['Order']['id'];?>" data-slide="prev"><span class="icon-prev"></span></a>		 <a class="right carousel-control" href="#slideshow_<?php echo $order['Order']['id'];?>" data-slide="next"><span class="icon-next"></span></a></div><?phpecho '</fieldset>';echo '</form>';if(!empty($results)) {	echo '<input type="hidden" name="Order_type_draw" id="order_type_draw_'.$order['Order']['id'].'" value="'.$type_draw.'" />';		switch($type_draw) {		case 'COMPLETE':			echo '<div class="row">';			echo '<div class="col-md-12">';			echo '<button data-attr="SIMPLE" style="cursor:pointer;opacity:1;" title="visualizzazione in elenco" type="button" class="btn btn-primary btn-md btn-type-draw"><i class="fa fa-th-list" aria-hidden="true"></i></button>';			echo ' <button data-attr="COMPLETE" style="cursor:default;opacity:0.5;" title="Visualizzazione a box" type="button" class="btn btn-primary btn-md btn-type-draw-current"><i class="fa fa-th" aria-hidden="true"></i></button>';			echo '</div>';			echo '</div>';					echo '<div class="row rowCart">';			foreach($results as $numArticlesOrder => $result) { 				if($numArticlesOrder > 0 && ($numArticlesOrder % 4)==0) echo '</div><div class="row">'; 				echo $this->RowEcomm->drawFrontEndComplete($numArticlesOrder, $order, $result);			}			echo '</div>';				break;		case 'SIMPLE':			echo $this->Tabs->setTableHeaderEcommSimpleFrontEnd($order['Order']['delivery_id']);			foreach($results as $numArticlesOrder => $result) 				echo $this->RowEcomm->drawFrontEndSimple($numArticlesOrder, $order, $result);				break;		case 'PROMOTION':			echo $this->Tabs->setTableHeaderEcommPromotionFrontEnd($order['Order']['delivery_id']);					$i=0;			foreach($results as $numArticlesOrder => $result) {				echo $this->RowEcomm->drawFrontEndPromotion($i, $order, $result);				$i++;			}				break;	}}else {		if(!empty($FilterArticleName) || !empty($FilterArticleArticleTypeIds)) 		$msg = "Nessun articolo trovato con i parametri di filtro che hai impostato";	else		$msg = "Non ci sono articoli disponibili";	echo $this->element('boxMsgFrontEnd', array('class_msg' => 'notice', 'msg' => $msg));	}?><script type="text/javascript">function ctrlSubmit_<?php echo $order['Order']['id'];?>() {     	 	 var filterArticleName = jQuery('input[name=FilterArticleName]').val(); 	 	 if(filterArticleName=="<?php echo $FilterArticleNameIntro;?>") filterArticleName = '';  	 	 var filterArticleArticleTypeIds = "";	 jQuery("input[name=FilterArticleArticleTypeIds]:checked").each( function () {		filterArticleArticleTypeIds += jQuery(this).val()+',';	 });	 filterArticleArticleTypeIds = filterArticleArticleTypeIds.substring(0, (filterArticleArticleTypeIds.length-1));	 	 <?php	 /*	  * se ho gia' filtrato permetto la ricerca senza filtro cosi' resetto	  */	  if($FilterArticleName == $FilterArticleNameIntro) $FilterArticleName = '';	  	 if(empty($FilterArticleName) && empty($FilterArticleArticleTypeIds)) { ?>	 if((filterArticleName=='' || filterArticleName==undefined) && filterArticleArticleTypeIds=='') {	 	alert("Valorizza almeno un parametro di filtro!");	 	return false;	 }	 <?php	 }	 ?>	 	 jQuery('#introHelp_<?php echo $order['Order']['delivery_id'];?>').css('display', 'none');	 jQuery('#articlesOrderResult_<?php echo $order['Order']['delivery_id'];?>').css('display', 'block');	 jQuery('#articlesOrderResult_<?php echo $order['Order']['delivery_id'];?>').html('');	 jQuery('#articlesOrderResult_<?php echo $order['Order']['delivery_id'];?>').css('background', 'url("/images/cake/ajax-loader.gif") no-repeat scroll center 0 transparent');	 	 var url = "/?option=com_cake&controller=Deliveries&action=tabsAjaxEcommArticlesOrder&delivery_id=<?php echo $order['Order']['delivery_id'];?>&order_id="+<?php echo $order['Order']['id'];?>;	 url += "&a="+encodeURIComponent(filterArticleName);	 url += "&b="+filterArticleArticleTypeIds;	 url += "&c=";	 url += "&format=notmpl";	 	 jQuery.ajax({		type: "GET",		url: url,		data: "",		success: function(response){			jQuery('#articlesOrderResult_<?php echo $order['Order']['delivery_id'];?>').css('background', 'none repeat scroll 0 0 transparent');			jQuery('#articlesOrderResult_<?php echo $order['Order']['delivery_id'];?>').html(response);		},		error:function (XMLHttpRequest, textStatus, errorThrown) {			jQuery('#articlesOrderResult_<?php echo $order['Order']['delivery_id'];?>').css('background', 'none repeat scroll 0 0 transparent');			jQuery('#articlesOrderResult_<?php echo $order['Order']['delivery_id'];?>').html(textStatus);		} 	 });	 	  	 return false;}  jQuery(document).ready(function() {	jQuery(".rowEcomm").each(function () {		activeEcommRows(this);    /* active + / - , mouseenter mouseleave */		activeSubmitEcomm(this);	});			jQuery('.actionTrView').css('display','inline-block');  /* rendo visibile il tasto espandi per i dettagli ajax */		jQuery('.actionTrView').each(function () {		actionTrView(this);	});		jQuery('.actionNotaView').css('display','inline-block');  /* rendo visibile il tasto espandi per i dettagli ajax */		jQuery('.actionNotaView').each(function () {		actionNotaView(this); 	});	jQuery('#ecomm-filter-label').submit(function ctrlSubmit() {});	jQuery('#slideshow_<?php echo $order['Order']['id'];?>').carousel({interval: false});		<?php	if(!empty($order['Order']['nota'])) {		echo "jQuery('#slideshow_".$order['Order']['id']."').carousel('next');";		echo "\r\n";		echo "jQuery('#slideshow_".$order['Order']['id']."').carousel('pause');";	}	?>		jQuery('.btn-type-draw').click(function() {		var type_draw = jQuery(this).attr('data-attr');				var url = "/?option=com_cake&controller=Deliveries&action=tabsAjaxEcommArticlesOrder&delivery_id=<?php echo $order['Order']['delivery_id'];?>&order_id="+<?php echo $order['Order']['id'];?>;		url += "&a=";		url += "&b=";		url += "&c="+type_draw;		url += "&format=notmpl";		 		jQuery.ajax({			type: "GET",			url: url,			data: "",			success: function(response){				jQuery('#articlesOrderResult_<?php echo $order['Order']['delivery_id'];?>').css('background', 'none repeat scroll 0 0 transparent');				jQuery('#articlesOrderResult_<?php echo $order['Order']['delivery_id'];?>').html(response);			},			error:function (XMLHttpRequest, textStatus, errorThrown) {				jQuery('#articlesOrderResult_<?php echo $order['Order']['delivery_id'];?>').css('background', 'none repeat scroll 0 0 transparent');				jQuery('#articlesOrderResult_<?php echo $order['Order']['delivery_id'];?>').html(textStatus);			}	 	 });		 	 	 	 return false;			});});</script>