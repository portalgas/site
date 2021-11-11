<?php
$tmp = '';

if(!empty($users)) {
	
	$tmp .= '<div class="panel-group">';
	$tmp .= '<div class="panel panel-primary">';
	$tmp .= '<div class="panel-heading">';
	$tmp .= '      <h4 class="panel-title">'.__('Users').'</h4>';
	$tmp .= '    </div>';
	$tmp .= '    <div class="panel-collapse">';
	$tmp .= '      <ul class="list-group">';
	
	foreach($users as $numResult => $result) {
		
		$tmp .= '<li class="list-group-item">';
		$tmp .= '<a data-toggle="collapse" href="#collapseUser'.$result['User']['id'].'"><span class="fa fa-chevron-down"></span> ';
		$tmp .= $result['User']['name'];
		$tmp .= '</a>';
		if(!empty($result['User']['email']))
			$tmp .= ' <a title="'.__('Email send').'" target="_blank" href="mailto:'.$result['User']['email'].'">'.$result['User']['email'].'</a>';
		//if(!empty($result['User']['Profile']['phone'])) $tmp .= ' '.$result['User']['Profile']['phone'].'<br />';
		//if(!empty($result['User']['Profile']['phone2'])) $tmp .= ' '.$result['User']['Profile']['phone2'];

		$tmp .= '    <div id="collapseUser'.$result['User']['id'].'" data-attr-id="'.$result['User']['id'].'" class="detail-users panel-collapse collapse">';
		$tmp .= '    </div>';
	
		$tmp .= '</li>';
		
	}
	$tmp .= '      </ul>';
	$tmp .= '  </div>';
	$tmp .= '</div>';
		
	echo $tmp;
	?>
	<script type="text/javascript">
	$(document).ready(function() {
		$('.detail-users').on('hidden.bs.collapse', function(){
			$(this).parent().find(".fa-chevron-up").removeClass("fa-chevron-up").addClass("fa-chevron-down");
			
			var user_id = $(this).attr('data-attr-id');
			var idDivTarget = 'collapseUser'+user_id;
			$('#'+idDivTarget).html("");
		}).on('shown.bs.collapse', function(){
			$(this).parent().find(".fa-chevron-down").removeClass("fa-chevron-down").addClass("fa-chevron-up");

			var delivery_id = $('#delivery_id').val();
			var user_id = $(this).attr('data-attr-id');
			var idDivTarget = 'collapseUser'+user_id;
			AjaxCallToOrdersUsersCart(delivery_id, user_id, idDivTarget);
		});	
	});
	</script>
<?php 
}
else
	echo $this->element('boxMsg',array('class_msg' => 'notice resultsNotFound', 'msg' => "Nessun utente ha effettuato acquisti"));
?>