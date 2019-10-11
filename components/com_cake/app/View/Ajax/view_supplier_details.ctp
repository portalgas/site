<?php 
$this->App->d($user, false);
$this->App->d($results, false);

if(isset($results['SuppliersOrganization']) && !empty($results['SuppliersOrganization'])) {

	echo '<h2>';
	echo '	<div class="actions-img">';
	echo '		<ul>';
	echo '		<li>'.$this->Html->link(__('Add Supplier Organization Id'), ['controller' => 'SuppliersOrganizations', 'action' => 'add_index'], ['class' => 'action actionAdd', 'title' => __('Add Supplier Organization Id')]).'</li>';
	echo '	</ul>';
	echo '</div>';
	echo '</h2>';
	
	/*
	 * articoli
	 */
	$first_gas=false;
	$first_gas_list_articles=false;
	$tmp = '';
	foreach ($results['SuppliersOrganization'] as $numResult => $result) {		 
		if(!$first_gas_list_articles && isset($result['Article']) && !empty($result['Article'])) {

			$first_gas_list_articles = true;

			$tmp .= '<div class="related">';
			$tmp .= '<h3 class="title_details">'.__('Related Suppliers Articles').'</h3>';
			
			$tmp .= '<div class="table-responsive"><table class="table">';
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
					$tmp .= '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$article['organization_id'].'/'.$article['img1'].'" />';	
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
			$tmp .= '</table></div></div>';
		}
	} // end foreach ($results['SuppliersOrganization'] as $numResult  => $result)
	
	echo $tmp;
	
	/*
	 * G.A.S.
	 */
	echo '<div class="related">';
	echo '<h3 class="title_details">'.__('Related Suppliers').'</h3>';	 
	echo '<div class="table-responsive"><table class="table">';
	echo '<tr>';
	echo '<th colspan="2">'.__('Name').' del G.A.S.</th>';
	echo '<th></th>';
	echo '</tr>';
	
	foreach ($results['SuppliersOrganization'] as $numResult => $result) {
		
		echo '<tr>';
		echo '<td>';
		echo ' <img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Organization']['img1'].'" alt="'.$result['Organization']['name'].'" /> ';	
		echo '</td>';
		echo '<td>';
		echo $result['Organization']['name'];
		if(!empty($result['Organization']['provincia'])) echo ' ('.h($result['Organization']['provincia']).')';
		echo '</td>';
		
		/*
		 * referenti
		 */
		echo '<td>';
		if(isset($result['SuppliersOrganizationsReferent']) && !empty($result['SuppliersOrganizationsReferent'])) {

			echo '<div class="actions-img" style="float:none;">';
			echo $this->Html->link(__('Send mail to referents to info'), [], ['class' => 'action actionEdit sendMail','title' => __('Send mail to referents'),
																				'pass_org_id' => $result['SuppliersOrganization']['organization_id'], 
																				'pass_id' => $result['SuppliersOrganization']['id'], 
																				'pass_entity' => 'suppliersOrganization']);
			
			echo '</div>';
		}
		else 
			echo "Nessun referente associato";
		echo '</td>';
		echo '</tr>';
	}	
	echo '</table></div></div>';
	
	echo $this->element('send_mail_popup');
}
else {
	echo $this->element('boxMsg', ['class_msg' => 'notice', 'msg' => "Il produttore non è ancora associato ad alcun G.A.S."]);
}
echo '</div>';
?>