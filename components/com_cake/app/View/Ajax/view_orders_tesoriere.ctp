<?php
if($user->organization['Template']['payToDelivery']=='POST' || $user->organization['Template']['payToDelivery']=='ON-POST') {
	echo '<div class="table-responsive"><table class="table table-hover">';
	echo '<tr>';
	echo '<th>Aperto/Chiuso</th>';
	echo '<th>'.__('Referenti').'</th>';
	echo '<th>'.__('Fattura').'</th>';
	echo '<th>'.__('Nota del referente').'</th>';
	echo '</tr>';
	
	echo '<tr>';
	echo '<td style="white-space:nowrap;">';
	echo $this->App->utilsCommons->getOrderTime($results['Order']);
	echo '</td>';
	echo '<td>';
	if(isset($results['Order']['SuppliersOrganizationsReferent'])) // deve sempre esistere!
		echo $this->App->drawListSuppliersOrganizationsReferents($user, $results['Order']['SuppliersOrganizationsReferent']);
	else 
		echo "Nessun referente associato!";
	echo '</td>';
	
	echo '<td>';
	if(!empty($results['Order']['tesoriere_doc1']) && file_exists(Configure::read('App.root').Configure::read('App.doc.upload.tesoriere').DS.$user->organization['Organization']['id'].DS.$results['Order']['tesoriere_doc1'])) {
		$ico = $this->App->drawDocumentIco($results['Order']['tesoriere_doc1']);
		echo '<a alt="Scarica il documento" title="Scarica il documento" href="'.Configure::read('App.server').Configure::read('App.web.doc.upload.tesoriere').'/'.$user->organization['Organization']['id'].'/'.$results['Order']['tesoriere_doc1'].'" target="_blank"><img src="'.$ico.'" /></a>';
	}
	else
		echo "";
	echo '</td>';
	
	echo '<td>';
	if(!empty($results['Order']['tesoriere_nota']))
		echo $results['Order']['tesoriere_nota'];
	else
		echo "Nessuna nota del referente";
	echo '</td>';				
	
	echo '</tr>';
	echo '</table></div>';
} // end if($user->organization['Template']['payToDelivery']=='POST' || $user->organization['Template']['payToDelivery']=='ON-POST')

echo '<br />';
echo '<div class="table-responsive"><table class="table table-hover">';
echo '<tr>';
echo '<th colspan="2">'.__('Supplier').'</th>';
echo '<th colspan="2">'.__('Contatti').'</th>';
echo '<th>'.__('Payment profile').'</th>';
echo '</tr>';

echo '<tr>';
echo '<td>';
if(!empty($supplier['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$supplier['Supplier']['img1']))
	echo ' <img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$supplier['Supplier']['img1'].'" alt="'.$supplier['SupplierOrganization']['name'].'" /> ';
echo '</td>';
echo '<td style="white-space:nowrap;">';
echo $supplier['Supplier']['name'];
echo '</td>';
echo '<td>';
if(!empty($supplier['Supplier']['indirizzo'])) echo $supplier['Supplier']['indirizzo'].'&nbsp;<br />';
if(!empty($supplier['Supplier']['localita'])) echo $supplier['Supplier']['localita'].'&nbsp;';
if(!empty($supplier['Supplier']['cap'])) echo $supplier['Supplier']['cap'].'&nbsp;';
if(!empty($supplier['Supplier']['provincia'])) echo '('.$supplier['Supplier']['provincia'].')'; 
echo '</td>';

echo '<td>';
if(!empty($supplier['Supplier']['telefono2'])) echo '<br />'.$supplier['Supplier']['telefono2'];
if(!empty($supplier['Supplier']['mail'])) echo '<br /><a title="'.__('Email send').'" href="mailto:'.$supplier['Supplier']['mail'].'" class="fa fa-envelope-o fa-lg"></a>';
if(!empty($supplier['Supplier']['www'])) echo '<a title="link esterno al sito del produttore" href="'.$this->App->traslateWww($supplier['Supplier']['www']).'" target="_blank" class="blank fa fa-globe fa-lg"></a>';
echo '</td>';

echo '<td>';
if(!empty($supplier['Supplier']['cf'])) echo '<br />'.__('Cf').' '.$supplier['Supplier']['cf'];
if(!empty($supplier['Supplier']['piva'])) echo '<br />'.__('Piva').' '.$supplier['Supplier']['piva'];
if(!empty($supplier['Supplier']['conto'])) echo '<br />'.$supplier['Supplier']['conto'];
if(!empty($supplier['Supplier']['nota'])) echo '<br />'.$supplier['Supplier']['nota'];
echo '</td>';

echo '</tr>';
echo '</table></div>';
?>