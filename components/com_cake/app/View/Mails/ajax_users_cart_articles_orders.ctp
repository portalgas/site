<?php
if(!empty($results)) {
	echo '<div class="table-responsive"><table class="table table-hover">';
	echo '<tr>';
	echo '<th colspan="2">';
	echo '<div class="checkbox"><label>';
	echo '<input checked type="checkbox" name="data[Mail][article_order_key_selected_all]" value="ALL" />';	
	echo '</label> Seleziona i gasisti che hanno effettuato acquisti per gli articoli</div>';	
	echo '</th>';
	echo '<th>'.__('PrezzoUnita').'</th>';
	echo '</tr>';
	
	foreach($results as $result) {
	
		echo '<tr>';
		echo '<td>';
		echo '<div class="checkbox"><label>';
		echo '<input checked type="checkbox" name="data[Mail][article_order_key_selected]" value="'.$result['ArticlesOrder']['article_organization_id'].'-'.$result['ArticlesOrder']['article_id'].'" />';	
		echo $result['ArticlesOrder']['name'].'</label></div>';	
		echo '</td>';
			
		echo '<td>';
		if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
			echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';	
		}
		echo '</td>';
		echo '<td>'.$result['ArticlesOrder']['prezzo_e'].'</td>';
		echo '</tr>';
		
	}
	
	echo '</table></div>';
	
	echo '<input type="hidden" value="" name="data[Mail][article_order_key_selecteds]" id="article_order_key_selecteds" />';
}
else
	echo $this->element('boxMsg',array('class_msg' => 'notice resultsNotFound', 'msg' => "Nessun gasista ha effettuato acquisti per l'ordine scelto"));
?>
<script type="text/javascript">
$(document).ready(function() {

	$("input[name='data[Mail][article_order_key_selected_all]']").click(function () {
		var checked = $("input[name='data[Mail][article_order_key_selected_all]']:checked").val();
		if(checked=='ALL')
			$("input[name='data[Mail][article_order_key_selected]']").prop('checked',true);
		else
			$("input[name='data[Mail][article_order_key_selected]']").prop('checked',false);
	});
});
</script>