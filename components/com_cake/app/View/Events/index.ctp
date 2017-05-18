<?php
/*
echo "<pre>";
print_r($results);
echo "</pre>";
*/
if($user->organization['Organization']['payToDelivery']=='POST' || $user->organization['Organization']['payToDelivery']=='ON-POST') {
	
	if(!empty($requestPaymentsResults)) {
		echo "<br />";
		echo '<div role="alert" class="alert alert-success">';
		echo '<a class="close" data-dismiss="alert">&times;</a>';
		foreach ($requestPaymentsResults as $requestPaymentsResult) {
			echo '<p>';
			echo '<a target="_blank" ';
			echo "href=\"/?option=com_cake&controller=ExportDocs&action=userRequestPayment&request_payment_id=".$requestPaymentsResult['RequestPayment']['id']."&doc_formato=PDF&format=notmpl\"";
			echo ' id="userRequestPayment-PDF" style="cursor:pointer;" rel="nofollow" title="stampa la richiesta di pagamento '.__('formatFilePdf').'"><img alt="PDF" src="'.Configure::read('App.img.cake').'/minetypes/32x32/pdf.png" />';
			echo '&nbsp;'.__('request_payment_num').' '.$requestPaymentsResult['RequestPayment']['num'];
			echo ' di '.$this->Time->i18nFormat($requestPaymentsResult['RequestPayment']['data_send'],"%A %e %B %Y");
			echo '</a>: '.$requestPaymentsResult['SummaryPayment']['importo_dovuto_e'];
			echo ' ('.$this->App->traslateEnum($requestPaymentsResult['SummaryPayment']['stato']).')';
			echo '</p>'; 

			if(!empty($requestPaymentsResult['RequestPayment']['nota'])) 
				echo '<p>'.$requestPaymentsResult['RequestPayment']['nota'].'</p>';
		}
		echo '</div>';		
	}
}
?>
<ul class="nav nav-tabs">
  <li class="active"><a href="#current" data-toggle="tab">Attività del G.A.S.</a></li>
  <li><a href="#history" data-toggle="tab">Attività trascorse</a></li>
</ul>

<div class="tab-content clearfix">
	
	<?php
	$i=0;
	foreach ($results as $type => $value) {	

		echo '<div class="tab-pane';
		if($i==0) echo ' active';
		echo '" id="'.$type.'">';
		
		if(!empty($value)) {
			
			$month_start_old = '';
			foreach ($value as $result):

				$month = substr($result['Event']['start'],5,2);
				$month_end = substr($result['Event']['end'],5,2);
				
				if(empty($month_start_old) || $month!=$month_start_old) {
					echo '<h2>'.$this->Time->i18nFormat($result['Event']['start'],"%B %Y").'</h2>';
				}
			
				echo '<h4>';
				echo $this->Time->i18nFormat($result['Event']['start'],"%A %e %B %Y");
				echo ' dalle '.$this->Time->i18nFormat($result['Event']['start'],"%H:%M");
				echo ' alle '.$this->Time->i18nFormat($result['Event']['end'],"%H:%M");
				if($month!=$month_end)
					echo ' di '.$this->Time->i18nFormat($result['Event']['end'],"%A %e %B %Y");
				echo '</h4>';
				
				echo '<div class="well">';
				echo '<div class="event-type-title" style="color:'.$result['EventType']['color'].'">';
				echo $result['EventType']['name'];
				echo '<span class="event-title"> - ';
				echo $result['Event']['title'];
				echo '</span>';				
				echo '</div>';
								
				if(!empty($result['Event']['user_id'])) {
					echo '<div class="event-responsabile">';
					echo __('EventUser').' ';

					if($result['User']['id']==$user->get('id'))
						$class_btn = 'btn-danger';
					else
						$class_btn = 'btn-default';
					
					echo '<span class="btn users '.$class_btn.' btn-sm">'.$result['User']['name'].'</span>';

					echo '</div>';					
				}

				echo '<div>';
				if(!empty($result['Event']['EventsUser']))
					foreach($result['Event']['EventsUser'] as $eventsUser) {
						if($eventsUser['User']['id']==$user->get('id'))
							$class_btn = 'btn-danger';
						else
							$class_btn = 'btn-info';
						
						echo '<span class="btn users '.$class_btn.' btn-sm">'.$eventsUser['User']['name'].'</span>';
					} 
				echo '</div>';
				
				echo '<div>'.$result['Event']['nota'].'</div>';
				echo '</div>';

				$month_start_old = $month;
			endforeach; 
		}
		else {
			$msg = '';
			if($type=='current')
				$msg = "Non ci sono attività";
			else
			if($type=='history')
				$msg = "Non ci sono attività trascorse";
			
			echo $this->element('boxMsgFrontEnd', array('class_msg' => 'notice', 'msg' => $msg));			
		}

		echo '</div>'; // <div class="tab-pane active" id=""> 
		
		$i++;
	}
	?>
</div>	

<style>
ul.nav.nav-tabs li.active a {
	background: #4fb4f3 none repeat scroll 0 0;
    color: #fff;
}
.btn.users {
    cursor: default;
    margin-right: 5px;
}
.event-type-title {
	font-size: 16px;
    font-weight: bold;
}
.event-title {
	margin-left:5px;
	color: #777;
    font-weight: bold;
}
.event-responsabile {
	font-size: 14px;
    font-weight: bold;
	margin: 5px 0;
}
</style>