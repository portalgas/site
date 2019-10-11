<?php 
echo '<div class="related">';

/* * ctrl che lo Article.stato = Y, se no non posso avere acquisti*/
if($article_stato=='N') 
	echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "L'articolo ha il campo Stato settato a <b>No</b> e non può essere acquistato"));
else {

	if(!empty($results)) {
		echo '<h3 class="title_details">'.__('Related ProdGas Article Carts').'</h3>';
		
		echo '<div class="table-responsive"><table class="table table-hover">';
		echo '<tr>';
		echo '	<th>'.__('N').'</th>';
		echo '	<th colspan="2">'.__('GasOrganization').'</th>';
		echo '	<th>'.__('Delivery').'</th>';
		echo '	<th>'.__('Order').': '.__('DataInizio').'</th>';
		echo '  <th>'.__('DataFine').'</th>';
		echo '	<th>'.__('OpenClose').'</th>';				
		echo '</tr>';	
	
		foreach($results as $numResult => $result) {
	
			echo "\r\n";
			echo '<tr>';
			echo '<td>'.($numResult+1).'</td>';
			echo '<td>';
			echo ' <img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Organization']['img1'].'" alt="'.$result['Organization']['name'].'" /> ';	
			echo '</td>';			
			echo '<td>'.$result['Organization']['name'].'</td>';
			echo '<td>';
			echo $result['Delivery']['luogoData'];
			echo '</td>';
			echo '<td>';
			echo $this->Time->i18nFormat($result['Order']['data_inizio'],"%A %e %B %Y");
			echo '</td>';
			echo '<td>';
			echo $this->Time->i18nFormat($result['Order']['data_fine'],"%A %e %B %Y");
			if($result['Order']['data_fine_validation']!=Configure::read('DB.field.date.empty'))
				echo '<br />Riaperto fino a '.$this->Time->i18nFormat($result['Order']['data_fine_validation'],"%A %e %B %Y");
			echo '</td>';
			echo '<td>';
			echo $this->App->utilsCommons->getOrderTime($result['Order']);
			echo '</td>';
			echo '</tr>';
		} // foreach($results as $numResult => $result) 
		
		echo '</table></div>';
	}
	else
		echo $this->element('boxMsg',array('class_msg' => 'notice', 'msg' => "L'articolo non &egrave; stato ancora acquistato"));
				
}  // if($article_stato=='N') 

echo '</div>';
?>