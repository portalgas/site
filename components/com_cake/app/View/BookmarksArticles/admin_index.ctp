<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
// $this->Html->addCrumb(__('List Articles'), array('controller' => 'Articles', 'action' => 'context_articles_index'));
$this->Html->addCrumb(__('List Articles'), array('controller' => 'Connects', 'action' => 'index', 'c_to' => 'admin/articles&a_to=index-quick'));
$this->Html->addCrumb(__('Gest BookmarksArticles'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="bookmarkes-articles">';

echo '<h2 class="ico-bookmarkes-articles">';
echo __('BookmarksArticles');
echo '<div class="actions-img">';
echo '</ul>';
echo '</div>';
echo '</h2>';
	
		echo $this->Form->create('FilterBookmarksArticle',array('id'=>'formGasFilter','type'=>'get'));
		echo '<fieldset class="filter">';
			echo '<legend>'.__('Filter BookmarksArticles').'</legend>';
			echo '<table>';
			
				echo '<tr>';
				echo '<td width="33%">';
				echo $this->Form->input('user_id',array('label' => false, 'options' => $users, 'empty' => Configure::read('option.empty'), 'name'=>'FilterBookmarksArticleUserId' ,'default' => $FilterBookmarksArticleUserId));
				echo '</td>';
	
				echo '<td width="33%">';
				echo $this->Form->input('supplier_organization_id',array('label' => false, 'options' => $ACLsuppliersOrganization,'empty' => __('FilterToSuppliers'), 'name'=>'FilterBookmarksArticleSupplierOrganizationId', 'default'=>$FilterBookmarksArticleSupplierOrganizationId,'escape' => false));
				echo '</td>';
	
				echo '<td width="33%">';
				echo '<div id="filterAllArticles" style="display:none;">';
				echo $this->Form->input('allArticles', array('label' => __('Tutti gli articoli'),'options' => $allArticles,'name'=>'FilterBookmarksArticleAllArticles', 'default'=>$FilterBookmarksArticleAllArticles,'escape' => false)); 
				echo '</div>';
				echo '</td>';
				
				echo '<td>';
				echo $this->Form->end(array('label' => __('Filter'), 'class' => 'filter', 'div' => array('class' => 'submit filter', 'style' => 'display:none'))); 
				echo '</td>';		
				echo '</tr>';
			echo '</table>';
		echo '</fieldset>';

	if(!empty($results)) {

		echo '<div id="tabsDelivery">';
		echo '<table cellspacing="0" cellpadding="0">';
		echo '<tr>';
		echo '<th></th>';
		echo '<th>'.__('N').'</th>';
		echo '<th>'.__('User').'</th>';
		echo '<th>'.__('Supplier').'</th>';
		echo '<th>'.__('Bio').'</th>';
		echo '<th>'.__('Name').'</th>';
		echo '<th>'.__('Conf').'</th>';
		echo '<th>'.__('PrezzoUnita').'</th>';
		echo '<th>'.__('Prezzo/UM').'</th>';
		echo '<th>'.__('qta_minima').'</th>';
		echo '<th>'.__('qta').'</th>';
		echo '<th>'.__('Importo').'</th>';
		echo '</tr>';
		
			
		foreach ($results as $numResult => $result)
			echo $this->RowBookmarks->drawBackOfficeSimple($numResult, $result, $options);
		
		echo '</table>';
		echo '</div>';
	}	
	else
		echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => __('msg_search_not_result')));
			
echo '</div>';
?>

<script type="text/javascript">
function gestFilterAllArticles() {
	var supplier_organization_id = $("#FilterBookmarksArticleSupplierOrganizationId").val();
	var user_id = $("#FilterBookmarksArticleUserId").val();
	
	if(supplier_organization_id=='' || user_id=='')
		$('#filterAllArticles').hide();
	else		
		$('#filterAllArticles').show();	
}
$(document).ready(function() {

	$("#FilterBookmarksArticleSupplierOrganizationId").change(function() {	
		gestFilterAllArticles();
	});	

	$("#FilterBookmarksArticleUserId").change(function() {	
		gestFilterAllArticles();
	});

	gestFilterAllArticles();
	
	$(".rowEcomm").each(function () {
		activeEcommRows(this);    /* active + / - , mouseenter mouseleave */
		activeSubmitEcommBookMmarks(this);
	});	
	
	$('.actionTrView').css('display','inline-block');  /* rendo visibile il tasto espandi per i dettagli ajax */
	
	$('.actionTrView').each(function () {
		actionTrView(this);
	});
	
	$('.actionNotaView').css('display','inline-block');  /* rendo visibile il tasto espandi per i dettagli ajax */
	
	$('.actionNotaView').each(function () {
		actionNotaView(this); 
	});
});	

function activeSubmitEcommBookMmarks(obj) { 

		$(obj).find('.submitEcomm').click(function() {
			/* get id da id="xxx-1"  */
			var idRow = $(this).attr('id');
			var numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);

			/* chiudo eventuali dettagli */
			$('.trView').hide(); 
			$('.actionTrView').removeClass('closeTrView'); 
			$('.actionTrView').addClass('openTrView'); 
			
			$('#submitEcomm-'+numRow).attr('src',app_img+'/ajax-loader.gif');

			var user_id = $("#FilterBookmarksArticleUserId").val();
			var supplier_organization_id = $('#supplier_organization_id').val();
			var article_organization_id = $('#article_organization_id-'+numRow).val();
			var article_id = $('#article_id-'+numRow).val();
			var qta = $('#qta-'+numRow).html();
			
			if(!ecommRowsValidation(numRow, backOffice=false)) return false;
			
			var url = '';
			url = '/?option=com_cake&controller=BookmarksArticles&action=managementCartSimple&rowId='+numRow+'&user_id='+user_id+'&supplier_organization_id='+supplier_organization_id+'&article_organization_id='+article_organization_id+'&article_id='+article_id+'&qta='+qta+'&format=notmpl';

			$.ajax({
				type: "GET",
				url: url,
				data: "",
				success: function(response){
					$('#row-'+numRow).html(response);
					/* $('#msgEcomm-'+numRow).html(response); */
				},
				error:function (XMLHttpRequest, textStatus, errorThrown) {
					 $('#msgEcomm-'+numRow).html(textStatus);
					 $('#submitEcomm-'+numRow).attr('src',app_img+'/blank32x32.png');
				}
			});
			return false;
	});
}

function managementCartBookmarks(c, b, a) {
    $("#submitEcomm-" + c).attr("style", "opacity:1");
    $("#msgEcomm-" + c).attr("style", "opacity:1");
    $("#msgEcomm-" + c).html("");
    if (b == "OK") {
        $("#submitEcomm-" + c).attr("src", app_img + "/actions/32x32/bookmark.png");
        $("#submitEcomm-" + c).css("cursor", "default");
        $("#msgEcomm-" + c).html("Salvato!");
        $("#submitEcomm-" + c).delay(1000).animate({
            opacity: 0
        }, 1500);
        $("#msgEcomm-" + c).delay(1000).animate({
            opacity: 0
        }, 1500);
    } 
    else 
    if (b == "DELETE") {
            $("#submitEcomm-" + c).attr("src", app_img + "/actions/32x32/blue_basket.png");
            $("#submitEcomm-" + c).css("cursor", "default");
            $("#msgEcomm-" + c).html("Cancellato!");
            $("#submitEcomm-" + c).delay(1000).animate({
                opacity: 0
            }, 1500);
            $("#msgEcomm-" + c).delay(1000).animate({
                opacity: 0
            }, 1500)
    }
    else
    if (b == "NO") {
        $("#submitEcomm-" + c).attr("src", app_img + "/apps/32x32/error.png");
           $("#submitEcomm-" + c).css("cursor", "default");
           $("#msgEcomm-" + c).html("Errore!")
    }
}
</script>