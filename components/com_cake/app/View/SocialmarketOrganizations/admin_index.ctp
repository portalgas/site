<?php
$this->App->d($results, false);
  
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('ProdGasSuppliersImport'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="organizations">';

echo '<h3>'.__('SuppliersOrganization').'</h3>';

?>
<h2>Passi</h2>
    <ul>
    <li>produttore deve avere account da produttore</li>
    <li>associare produttore all'organizzazione SocialMarket</li>
    <li>controllare <b>dati</b> produttore: logo, articoli</li>
    <li>Questionario: Modalità consegna
        <ul>
            <li>Spedizione</li>
            <li>In sede</li>
            <li>Punti di ritiro (ex mercati, botteghe)</li>
        </ul>
    </li>
        <li>in SocialMarket apro l'<b>ordine</b> con "consegna da definire"</li>
    </ul>

<h3>Modalità consegna</h3>
<h4>Spedizione</h4>
<p>Associare tutti i G.A.S. escluso quelli già in "Elenco G.A.S. associati"</p>

<h4>In sede</h4>
<p>Associare tutti i G.A.S. vicino alla sede escluso quelli già in "Elenco G.A.S. associati"</p>

<h4>Punti di ritiro (ex mercati, botteghe)</h4>
<p>Associare tutti i G.A.S. vicino al "Punti di ritiro" escluso quelli già in "Elenco G.A.S. associati"</p>


<h3>Associazione G.A.S.</h3>
<p>
<ul>
    <li><a target="_blank" href="https://www.portalgas.it/gmaps-gas">Mappa G.A.S.</a></li>
    <li><b>estraggo</b> gli ID dell'organization da associare al produttore</li>
    <li>print screen mappa</li>
    <li>database insert into socialmarket_organizations...</li>
    <li><b>escludo</b> gli ID dell'organization già associate al produttore</li>
</ul>
</p>
<?php
echo '<div class="table-responsive"><table class="table table-hover">';
echo '<tr>';
echo '<th>N.</th>';
echo '<th>'.__('Organization').'</th>';
echo '<th>'.__('Supplier').'</th>';
echo '<th>Ids</th>';
echo '<th>Sql</th>';
echo '</tr>';

foreach($results as $numResult => $result) {

	$this->App->d($result, false);
	
	echo '<tr>';
	echo '<td>'.((int)$numResult+1).'</td>';
	echo '<td>';
	echo ' <img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['Organization']['img1'].'" alt="'.$result['Supplier']['Organization']['name'].'" /> ';
    echo '<br /><a target="_blank" href="'.Configure::read('Neo.portalgas.url').'site/produttore/'.$result['Supplier']['slug'].'">site/produttore/'.$result['Supplier']['slug'].'</a>';
    echo '</td>';
	echo '<td>'.$result['Supplier']['Organization']['name'];
    echo '</td>';
    echo '<td>';
    echo 'organization_id associato all\'organization dell\'account da produttore '.$result['Supplier']['Organization']['id'].'<br />';
    echo 'supplier_id '.$result['Supplier']['id'].'<br />';
    echo 'supplier_organization_id associato all\'organization SocialMarket '.$result['SuppliersOrganization']['id'].' (database.socialmarket_organizations)';
    echo '</td>';;
    echo '<td>INSERT into socialmarket_organizations (supplier_organization_id, organization_id) VALUES ('.$result['SuppliersOrganization']['id'].', 0);';
    echo '<br /><b>escludo</b> organization_id dei G.A.S. già associati';
    echo '</td>';
	echo '</tr>';
	
	/*
	 * GAS gia' assocati al produttore => non saranno in SocialMarket per conflitti d'interesse
	 */
	if(!isset($result['Organization'])) {
		echo '<tr>';
		echo '<td></td>';
		echo '<td colspan="3">';
		echo $this->element('boxMsg',['class_msg' => 'notice','msg' => "Non associato ad un G.A.S.: scegli il G.A.S., importa il produttore"]);		
		echo '</td>';
		echo '</tr>';		
	}
	else {
        echo '<tr>';
        echo '<td></td>';
        echo '<td></td>';
        echo '<th>G.A.S. già associati</th>';
        echo '<th></th>';
        echo '<th></th>';
        echo '</tr>';

        echo '<tr class="">';

        echo '<td></td>';
        echo '<td></td>';
        echo '<td>';
        foreach($result['Organization'] as $organization) {
            echo '<img width="50" src="' . Configure::read('App.web.img.upload.content') . '/' . $organization['Organization']['img1'] . '"  alt="' . $organization['Organization']['name'] . '" />';
            echo ' ' . $organization['Organization']['name'] . ' (' . $organization['Organization']['id'] . ') ';
            if($organization['SuppliersOrganization']['owner_articles']=='SUPPLIER')
                echo '<label class="btn btn-info">'.$this->App->traslateEnum('ProdGasSupplier'.$organization['SuppliersOrganization']['owner_articles']).'</label>';
            else
                echo $this->App->traslateEnum('ProdGasSupplier'.$organization['SuppliersOrganization']['owner_articles']);
        }
        echo '</td>';
        echo '<td>';
        echo '</td>';
        echo '<td></td>';
        echo '</tr>';
	} // end if(!isset($result['Supplier']['Supplier']['Organization']))

    /*
     * GAS assocati al SocialMarket (in base alla modalita' di consegna)
     */
    if(!isset($result['SocialmarketOrganization'])) {
        echo '<tr>';
        echo '<td></td>';
        echo '<td colspan="3">';
        echo $this->element('boxMsg',['class_msg' => 'notice','msg' => "Non associato ad un G.A.S.: scegli il G.A.S., importa il produttore"]);
        echo '</td>';
        echo '</tr>';
    }
    else {
        echo '<tr>';
        echo '<td></td>';
        echo '<td></td>';
        echo '<th>G.A.S. associati a SocialMarket</th>';
        echo '<th></th>';
        echo '<th></th>';
        echo '</tr>';

        echo '<tr class="">';
        echo '<td></td>';
        echo '<td></td>';
        echo '<td>';
        foreach($result['SocialmarketOrganization'] as $organization) {
            echo '<img width="50" src="' . Configure::read('App.web.img.upload.content') . '/' . $organization['Organization']['img1'] . '"  alt="' . $organization['Organization']['name'] . '" />';
            echo ' ' . $organization['Organization']['name'] . ' (' . $organization['Organization']['id'] . ') ';
        }
        echo '</td>';
        echo '<td></td>';
        echo '<td></td>';
        echo '</tr>';

    } // end if(!isset($result['Supplier']['Supplier']['Organization']))
}
echo '</table></div>';			

echo '</div>';