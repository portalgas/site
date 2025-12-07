<?php 
$delivery_id_old = 0;
$data_oggi = date("Y-m-d");
$data_oggi_incrementata = date('Y-m-d', strtotime('+'.(Configure::read('GGMailToAlertOrderClose')).' day', strtotime($data_oggi)));

foreach ($orders as $order) {

    App::import('Model', 'SuppliersOrganizationsReferent');
    $SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;
    $conditions = [];
    $conditions['SuppliersOrganization.id'] = $order['Order']['supplier_organization_id'];
    $referenti = $SuppliersOrganizationsReferent->getReferentsCompact($user, $conditions);

    if($delivery_id_old==0 || $delivery_id_old!=$order['Delivery']['id']) {

            if($delivery_id_old > 0) {
                echo $this->Mail->drawGoToCart($user, $delivery_id_old, $utente);
            }

            if($order['Delivery']['sys']=='Y')
                    echo  "<br />\nper una consegna <b>".$order['Delivery']['luogo']."</b><br />\n";						
            else
                    echo  "<br />\nper la consegna di <b>".CakeTime::format($order['Delivery']['data'],"%A %e %B %Y")."</b> a ".$order['Delivery']['luogo']."<br />\n";

            echo  "si <span style='color:red'>chiudera'</span> tra ".(Configure::read('GGMailToAlertOrderClose')+1)." giorni, ".CakeTime::format($data_oggi_incrementata,"%A %e %B %Y").", il periodo d'ordine nei confronti ";

            if(count($orders)==1) {
                    echo  "del seguente produttore: <br />\n<br />\n";
            }
            else {
                    echo  "dei seguenti produttori: <br />\n<br />\n";
            }												
    }
    
    echo  "<div style='clear:both;float:none;margin-top:5px;'>";
    echo  "- ";
    echo  $order['SupplierOrganization']['name'];
    if(!empty($order['Supplier']['descrizione'])) echo  "/".$order['Supplier']['descrizione'];
    if(!empty($order['SupplierOrganization']['frequenza'])) echo  " (frequenza ".$order['SupplierOrganization']['frequenza'].')';

    if(!empty($order['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$order['Supplier']['img1']))
            echo ' <img width="50" src="'.Configure::read('Portalgas.urlMail').Configure::read('App.web.img.upload.content').'/'.$order['Supplier']['img1'].'" alt="'.$order['SupplierOrganization']['name'].'" /> ';
    else
        echo ' <img width="50" src="'.Configure::read('Portalgas.urlMail').Configure::read('App.web.img.upload.content').'/empty.png" alt="'.$order['SupplierOrganization']['name'].'" /> ';

    echo  "<br />\n";

    $delivery_id_old=$order['Delivery']['id'];
        

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
            echo '<div>La data presunta della consegna sarà intorno alla data '.CakeTime::format($order['Delivery']['data'], "%A %e %B %Y").'</div>';
            echo '<div><b>Il giorno e ora esatti della consegna verranno specificati quando la merce sarà effettivamente arrivata.</b></div>';
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
        echo '<div style="float:right;width:75%;margin-top:5px;">';
        echo '<span style="color:red;">Nota</span> ';
        echo  $order['Order']['mail_open_testo'];
        echo '</div>';
    }
    echo '</div>';


    $delivery_id_old = $order['Delivery']['id'];

} // loops Orders dello user

echo $this->Mail->drawGoToCart($user, $delivery_id_old, $utente);
