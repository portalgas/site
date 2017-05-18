<?php 
echo '<div class="related">';

echo $this->Form->submit(__('Add Supplier Organization Id'), array('id' => 'submit_only_supplier', 'div'=> 'submitMultiple'));

if(isset($results['SuppliersOrganization']) && !empty($results['SuppliersOrganization']))
	echo $this->Form->submit(__('Add Supplier Organization Id And Article'), array('id' => 'submit_supplier_articles', 'div'=> 'submitMultiple','class' => 'buttonBlu'));

echo $this->Form->hidden('supplier_id',array('value' => $results['Supplier']['id']));


echo '<h3 class="title_details">'.__('Related Suppliers').'</h3>';

if(isset($results['SuppliersOrganization']) && !empty($results['SuppliersOrganization'])) {

	echo '<table cellpadding = "0" cellspacing = "0">';
	echo '<tr>';
	echo '<th colspan="4">'.__('Name').' del G.A.S.</th>';
	//echo '<th>Contatti del produttore</th>';
	//echo '<th>Contatti con i referenti del G.A.S.</th>';
	echo '</tr>';
	
	$tmp = '';
	foreach ($results['SuppliersOrganization'] as $numResult  => $result) {
		
		echo '<tr>';
		// echo '<td>'.($numResult+1).'</td>';
		echo '<td>';
		echo ' <img width="50" class="userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Organization']['img1'].'" alt="'.$result['Organization']['name'].'" /> ';	
		echo '</td>';
		echo '<td>';
		echo $result['Organization']['name'];
		//if(!empty($result['Organization']['descrizione'])) echo '<div class="small">'.$result['Organization']['descrizione'].'</div>';
		if(!empty($result['Organization']['provincia'])) echo ' ('.h($result['Organization']['provincia']).')';
		echo '</td>';
		echo '<td>';
		if(!empty($result['SuppliersVote'])) 
			echo $this->App->drawVote($result['SuppliersVote']['voto'], $result['SuppliersVote']['nota']);
		echo '</td>';
		/*
		echo '<td>';
		// if(!empty($result['Organization']['indirizzo'])) echo $result['Organization']['indirizzo'].'&nbsp;<br />';
		if(!empty($result['Organization']['localita'])) echo $result['Organization']['localita'].'&nbsp;';
		if(!empty($result['Organization']['cap'])) echo $result['Organization']['cap'].'&nbsp;';
		if(!empty($result['Organization']['provincia'])) echo '('.h($result['Organization']['provincia']).')';
		echo '</td>';
		echo '<td>';
		if(!empty($result['Organization']['telefono'])) echo h($result['Organization']['telefono']).'<br />';
		if(!empty($result['Organization']['telefono2'])) echo  h($result['Organization']['telefono2']).'<br />';
		if(!empty($result['Organization']['mail'])) echo '<a title="'.__('Email send').'" href="mailto:'.h($result['Organization']['mail']).'" class="link_mailto"></a>'.'<br />';
		echo '</td>';
		*/
		
		/*
		 * referenti
		 */
		echo '<td>';
		if(isset($result['SuppliersOrganizationsReferent']) && !empty($result['SuppliersOrganizationsReferent'])) {

			echo '<div class="actions-img" style="float:none;">';
			echo $this->Html->link(__('Send mail to referents to info'), array(), array( 'class' => 'action actionEdit sendMail','title' => __('Send mail to referents'),
																				'pass_org_id' => $result['SuppliersOrganization']['organization_id'], 
																				'pass_id' => $result['SuppliersOrganization']['id'], 
																				'pass_entity' => 'suppliersOrganization'));
			
			echo '</div>';
		}
		else 
			echo "Nessun referente associato";
		echo '</td>';
		
		echo '</tr>';
		
		/*
		 * articoli
		 */
		if($numResult==0 && isset($result['Article'])) {


			$tmp .= '<tr>';
			$tmp .= '<td></td>';
			$tmp .= '<td colspan="3"><br />';

			$tmp .= '<h3 class="title_details">'.__('Related Suppliers Articles').'</h3>';
			
			$tmp .= '<table cellpadding="0" cellspacing="0">';
			$tmp .= '<tr>';
			$tmp .= '<th>'.__('N').'</th>';
			$tmp .= '<th>'.__('Bio').'</th>';
			$tmp .= '<th></th>';
			$tmp .= '<th>'.__('Name').'</th>';
			$tmp .= '<th>'.__('pezzi_confezione').'</th>';
			$tmp .= '<th>'.__('PrezzoUnita').'</th>';
			$tmp .= '<th>'.__('Prezzo/UM').'</th>';
			$tmp .= '</tr>';
				
			foreach ($result['Article'] as $numResult2  => $article) {
			
				$tmp .= '<tr>';
				$tmp .= '<td>'.($numResult2+1).'</td>';
				$tmp .= '<td>';
				if($article['bio']=='Y') $tmp .= '<span class="bio" title="'.Configure::read('bio').'"></span>';
				$tmp .= '</td>';
				$tmp .= '<td>';
				if(!empty($article['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$article['organization_id'].DS.$articles['img1'])) {
					$tmp .= '<img width="50" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$article['organization_id'].'/'.$article['img1'].'" />';	
				}
				$tmp .= '</td>';
	
				$tmp .= '<td>';
			    $tmp .= $article['name'].'&nbsp;';
			 	if(!empty($article['nota'])) $tmp .= '<div class="small">'.$article['nota'].'</div>'; 
			    $tmp .= '</td>';
		 		$tmp .= '</td>';
				$tmp .= '<td>'.$this->App->getArticleConf($article['qta'], $article['um']).'</td>';
				$tmp .= '<td>'.$article['prezzo_e'].'</td>';
				$tmp .= '<td>'.$this->App->getArticlePrezzoUM($article['prezzo'], $article['qta'], $article['um'], $article['um_riferimento']).'</td>';
				$tmp .= '</tr>';
	
			} // end foreach ($result['Article'] as $numResult2  => $article)
			$tmp .= '</table>';
			
			
			$tmp .= '</td>';
			$tmp .= '</tr>';
		}
	} // end foreach ($results['SuppliersOrganization'] as $numResult  => $result)
	
	echo $tmp;
	
	echo '</table>';
	
	echo $this->element('send_mail_popup');
}
else {
	echo $this->element('boxMsg',array('class_msg' => 'notice', 'msg' => "Il produttore non è ancora associato ad alcun G.A.S."));
}
echo '</div>';
?>	

<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery('#submit_only_supplier').click(function() {	
		jQuery('#supplier_articles').val('N');
	});
	jQuery('#submit_supplier_articles').click(function() {	
		jQuery('#supplier_articles').val('Y');
	});
	
	jQuery('#formGas').submit(function() {
		<?php 
		if(isset($results['SuppliersOrganization']) && !empty($results['SuppliersOrganization'])) {
		?>
			var supplier_articles = jQuery('#supplier_articles').val();
			if(supplier_articles=='N') {
				if(!confirm("Sei sicuro di non voler importare anche gli articoli del produttore?"))
					return false;
			}
		<?php 
		}
		?>			
		return true;
	});

});
</script>