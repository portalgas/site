<?php 
$delivery_id_old = 0;
foreach ($orders as $order) {

    App::import('Model', 'SuppliersOrganizationsReferent');
    $SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;
    $conditions = [];
    $conditions['SuppliersOrganization.id'] = $order['Order']['supplier_organization_id'];
    $referenti = $SuppliersOrganizationsReferent->getReferentsCompact($user, $conditions);


    if($delivery_id_old==0 || $delivery_id_old != $order['Delivery']['id']) {

    if($delivery_id_old != $order['Delivery']['id']) {

        if($delivery_id_old > 0) {
            echo $this->Mail->drawGoToCart($user, $delivery_id_old, $utente);
        }
    }

    if($order['Delivery']['sys']=='Y')
    echo  "<br />Per una consegna <b>".$order['Delivery']['luogo']."</b><br />";						
        else
    echo  "<br />Per la consegna di <b>".CakeTime::format($order['Delivery']['data'], "%A %e %B %Y")."</b> a ".$order['Delivery']['luogo']."<br />";

    if(count($orders)==1)
        echo  "si <span style='color:green;'>apre</span> oggi il periodo d'ordine nei confronti del seguente produttore:<br /><br />";
    else 
        echo  "si <span style='color:green;'>apre</span> oggi il periodo d'ordine nei confronti dei seguenti produttori: <br /><br />";
 
} // end if($delivery_id_old==0 || $delivery_id_old != $order['Delivery']['id']) 

//echo  ((int)$numResult+1).") ".$order['SupplierOrganization']['name'];
echo  "<div style='clear:both;float:none;margin-top:5px;'>";	
echo  "- ";						
echo  $order['SupplierOrganization']['name'];
if(!empty($order['Supplier']['descrizione'])) echo  "/".$order['Supplier']['descrizione'];
if(!empty($order['SupplierOrganization']['frequenza'])) echo  " (frequenza ".$order['SupplierOrganization']['frequenza'].')';
echo  " fino a ".CakeTime::format($order['Order']['data_fine'], "%A %e %B %Y");

if(!empty($order['Supplier']['img1']) && file_exists($App_root.Configure::read('App.img.upload.content').'/'.$order['Supplier']['img1']))
echo  ' <img width="50" src="'.$Portalgas_urlMail.$App_web_img_upload_content.'/'.$order['Supplier']['img1'].'" alt="'.$order['SupplierOrganization']['name'].'" /> ';
else
echo  ' <img width="50" src="'.$Portalgas_urlMail.$App_web_img_upload_content.'/empty.png" alt="'.$order['SupplierOrganization']['name'].'" /> ';										

echo '<div style="clear:both;float:none;margin-left:25px;">';
switch($order['Order']['mail_order_type']) {
    case 'DEFAULT':
        if(count($referenti)==1)
            echo 'Referente del produttore: ';
        else 
            echo 'Referenti del produttore: ';
        
        foreach($referenti as $numResult => $referente) {
            echo '<a href="mailto:'.$referente['User']['email'].'">'.$referente['User']['name'].'</a>';
            if($numResult<(count($referenti)-1)) echo ' | ';
        }
    break;
    case 'CONFIRM_AFTER_INCOMING':
        echo '<div>La consegna avverrà '.$order['Delivery']['luogo'].' la data presunta è '.CakeTime::format($order['Delivery']['data'], "%A %e %B %Y").'</div>';
        echo '<div><b>Il giorno e l\'ora esatti della consegna verranno specificati quando la merce sarà effettivamente arrivata</b></div>';
        echo '<div>Per richieste o dubbi non rispondere a questa mail ma contattare direttamente ';
        if(count($referenti)==1)
            echo 'il referente del produttore: ';
        else 
            echo 'i referenti del produttore: ';
        
        foreach($referenti as $numResult => $referente) {
            echo '<a href="mailto:'.$referente['User']['email'].'">'.$referente['User']['name'].'</a>';
            if($numResult<(count($referenti)-1)) echo ' | ';
        }
        echo '</div>';
    break;
}
echo '</div>';

/*
 * note
 * */
if(!empty($order['Order']['mail_open_testo'])) {
    echo  '<div style="float:right;width:75%;margin-top:5px;">';
    echo  '<span style="color:red;">Nota</span> ';
    echo  $order['Order']['mail_open_testo'];
    echo  '</div>';
}
echo '</div>';


$delivery_id_old = $order['Delivery']['id'];

} // loops Orders dello user

echo $this->Mail->drawGoToCart($user, $delivery_id_old, $utente);
