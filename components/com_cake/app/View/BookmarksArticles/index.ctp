<div class="bookmarkers" id="ajaxContent">

	<?php
	if(!empty($results)) {

		$msg = "Qui potrai gestire gli articoli dei produttori che desideri che vengano aggiunti in automatico da PortAlGas all'apertura di un ordine";
		echo $this->element('boxMsgFrontEnd',array('class_msg' => 'notice', 'msg' => $msg));
	
		echo '<p style="clear:both; float:right;" class="actions-table">';
		echo '<a title="Gestisci gli articoli tra i preferiti" href="javascript:viewContentAjax()">Gestisci gli articoli</a>';
		echo '</p>';
	?>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th></th>
			<th><?php echo __('N');?></th>
			<th><?php echo __('Bio');?></th>
			<th><?php echo __('Name');?></th>
			<th><?php echo __('Conf');?></th>
			<th><?php echo __('PrezzoUnita');?></th>
			<th><?php echo __('Prezzo/UM');?></th>
			<th><?php echo __('qta');?></th>
			<th><?php echo __('importo');?></th>
	</tr>
	<?php
	$supplier_organization_id_old = 0;
	foreach ($results as $numResult => $result): 
		if($result['SuppliersOrganization']['id']!=$supplier_organization_id_old) {
			echo '<tr>';
			echo '<td colspan="9" class="trGroup">'.__('Supplier').': '.$result['SuppliersOrganization']['name'].' ';

			if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
				echo '<img width="50" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" />';
				
			echo '</td>';
			echo '</tr>';
		}
		
		if(!empty($result['BookmarksArticle']['qta']))
			$qta = $result['BookmarksArticle']['qta'];
		else
			$qta = 0;
		
		$rowId = $result['Article']['id'];
		
		echo '<tr style="display:table-row; font-size: 14px; height: 60px;">';
		
		
		echo '<td>';
		echo '<a id="actionTrView-'.$rowId.'" action="articles-'.$rowId.'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
		
		echo '<td>'.($numResult +1).'</td>';
		echo "\n";
		echo '<td>';
		if($result['Article']['bio']=='Y')
			echo '<span class="bio" title="'.Configure::read('bio').'"></span>';
		else
			echo "";
		echo '</td>';
		
		echo '<td>';
		echo $result['Article']['name'];
		echo $this->App->drawArticleNotaAjax($rowId, $result['Article']['nota']);
		echo '</td>';
		
		echo '<td style="white-space: nowrap;">';
		if($result['Article']['qta']>0)
			echo $this->App->getArticleConf($result['Article']['qta'], $this->App->traslateEnum($result['Article']['um']));
		echo '</td>';
		
		echo "\n";  // Prezzo unità
		echo '<td style="white-space: nowrap;">';
		echo $result['Article']['prezzo_e'];
		echo '</td>';
		echo "\n";  // Prezzo/UM
		echo '<td style="white-space: nowrap;">';
		echo $this->App->getArticlePrezzoUM($result['Article']['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']);
		echo '</td>';
		
		echo "\n";  // Qta
		if($qta>0) $classQta = "qtaUno";
		else $classQta = "qtaZero";
		
		echo '<td style="white-space:nowrap;text-align:center;width:125px;">';
		echo "\n";
		echo '<div id="qta-'.$rowId.'" class="'.$classQta.' qta">';
		if($qta>0) echo $qta;
		echo '</div>';
		echo "\n";
		echo '</td>';
		
		/*
		 * importo
		*/
		$importo = ($qta * $result['Article']['prezzo']);
		
		echo '<td style="white-space:nowrap;width:100px;">';		
		echo "\n";
		if(!empty($importo))
			echo number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		echo '</td>';
		echo '</tr>';
		
		echo '<tr class="trView" id="trViewId-'.$rowId.'">';
		echo '<td colspan="2"></td>';
		echo '<td colspan="7" id="tdViewId-'.$rowId.'"></td>';
		echo '</tr>';
		
		$supplier_organization_id_old=$result['SuppliersOrganization']['id'];
	endforeach; 
	
	echo '</table>';
	
	}
	else {
		$msg = "Non hai ancora aggiunto articoli che vengano aggiunti in automatico da PortAlGas all'apertura dell'ordine del produttore.";
		echo $this->element('boxMsgFrontEnd',array('class_msg' => 'notice', 'msg' => $msg));	
	}
	?>
	
</div>
	

<script type="text/javascript">
function viewContentAjax() {
	jQuery('#ajaxContent').html('');
	jQuery('#ajaxContent').css('min-height', '50px');
	jQuery('#ajaxContent').css('background', 'url("'+app_img+'/ajax-loader.gif") no-repeat scroll center 0 transparent');

	var url = "/?option=com_cake&controller=BookmarksArticles&action=add&format=notmpl";
	jQuery.ajax({
		type: "get", 
		url: url,
		data: "",
		success: function(response) {
			jQuery('#ajaxContent').css('background', 'none repeat scroll 0 0 transparent');
			jQuery('#ajaxContent').html(response);
		},
		error:function (XMLHttpRequest, textStatus, errorThrown) {
			jQuery('#ajaxContent').css('background', 'none repeat scroll 0 0 transparent');
			jQuery('#ajaxContent').html(textStatus);
		}
	});
	
	return;	
}

jQuery(document).ready(function() {
	
	jQuery('.actionTrView').css('display','inline-block');  /* rendo visibile il tasto espandi per i dettagli ajax */
	
	jQuery('.actionTrView').each(function () {
		actionTrView(this);
	});
	
	<?php 
	/*
	 * devo ripulire il campo hidden che inizia per page perche' dopo la prima pagina sbaglia la ricerca con filtri
	 */
	?>
	jQuery('.filter').click(function() {
		jQuery("input[name^='page']").val('');
	});
	
});		
</script>