<?php
App::uses('AppModel', 'Model');
App::uses('CakeTime', 'Utility');

class MailsProdGasPromotionSend extends AppModel {

	public $useTable = 'prod_gas_promotions';
	public $name = 'ProdGasPromotion';
		
	/*
	 * quando la prmozione passa da stato WORKING a TRASMISSION-TO-GAS
	 * invio mail a 
	 *	isReferente
	 *	isSuperReferente 
	 */
	public function trasmissionToGas($user, $organization_id, $prod_gas_promotion_id, $debug = false) {
		
        App::import('Model', 'ProdGasPromotion');
        $ProdGasPromotion = new ProdGasPromotion;

        App::import('Model', 'ProdGasPromotionsOrganizationsManager');
        $ProdGasPromotionsOrganizationsManager = new ProdGasPromotionsOrganizationsManager;

		$usersResults = $ProdGasPromotionsOrganizationsManager->getReferents($user, $prod_gas_promotion_id);
		if($debug) debug($usersResults);

		$promotionResults = $ProdGasPromotion->getProdGasPromotion($user, $prod_gas_promotion_id);
		if($debug) debug($promotionResults);

		$body = '';
		$body .= 'Il produttore <b>'.$promotionResults['SuppliersOrganization']['name'].'</b> ha creato per voi una promozione per alcuni suoi prodotti';
		$body .= '<ul>';
		foreach($promotionResults['ProdGasArticlesPromotion'] as $prodGasArticlesPromotion) {
			$body .= '<li>Per l\'articolo ';
			$body .= '<b>'.$prodGasArticlesPromotion['Article']['name'].'</b>, '; 
			$body .= 'acquistandone '.$prodGasArticlesPromotion['ProdGasArticlesPromotion']['qta'].' pezzi, ';
			$body .= 'invece di <del>'.$prodGasArticlesPromotion['Article']['prezzo'].' &euro;</del> '.$prodGasArticlesPromotion['ProdGasArticlesPromotion']['prezzo_unita'].' &euro;';
			$body .= '</li>';
		}
		$body .= '</ul>';
		$body .= '<p>Promozione valida da '.CakeTime::format($promotionResults['ProdGasPromotion']['data_inizio'], "%A %e %B %Y");
		$body .= ' a '.CakeTime::format($promotionResults['ProdGasPromotion']['data_fine'], "%A %e %B %Y");
		$body .= '</p>';
		$body .= '<p>Se pensi che il tuo G.A.S. possa essere interessato, vai su <a target="_blank" href="https://'.Configure::read('SOC.site').'/my">https://'.Configure::read('SOC.site').'/my</a> e apri l\'ordine</p>	';


		debug($body);
	exit;


        App::import('Model', 'Mail');
        $Mail = new Mail;

		$Email = $Mail->getMailSystem($user);
					
        App::import('Model', 'User');
        $User = new User;

        if($debug)
			echo "Estraggo gli ordini che apriranno tra ".(Configure::read('GGMailToAlertOrderOpen')+1)." giorni o con mail_open_send = Y \n";
		
        $Email->viewVars(['body_footer' => sprintf(Configure::read('Mail.body_footer_no_reply'), $this->_traslateWww($user->organization['Organization']['www']))]);

        if(!Configure::read('mail.send'))  $Email->transport('Debug');

        /*
         * ciclo UTENTI
         */

        foreach($usersResults as $numResult => $usersResult) {

            $mail = $usersResult['User']['email'];
            $mail2 = $usersResult['UserProfile']['email'];
            $name = $usersResult['User']['name'];
            $username = $usersResult['User']['username'];
            
            $Email->viewVars(['body_header' => sprintf(Configure::read('Mail.body_header'), $name)]);

            if($debug)
				echo '<br />'.$numResult.") tratto l'utente ".$name.', username '.$username." \n";
		}

		$body_mail = "";
		$delivery_id_old = 0;
		$j_seo = $user->organization['Organization']['j_seo'];

		$body_mail .= '<div style="clear: both; float: none; margin: 5px 0 15px;">'; 
		$body_mail .= '<img src="https://www.portalgas.it'.Configure::read('App.img.cake').'/cesta-piena.png" title="" border="0" />';
		$body_mail .= ' <a target="_blank" href="'.$url.'">Clicca qui per visualizzare i tuoi <b>acquisti</b> che dovrai ritirare durante la consegna</a>';
		$body_mail .= '</div>'; 
		// $body_mail .= " fino a ".CakeTime::format($result['Order']['data_fine'], "%A %e %B %Y");
		if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
				$body_mail .= ' <img width="50" src="https://www.portalgas.it'.Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" alt="'.$result['SupplierOrganization']['name'].'" /> ';
		else
			$body_mail .= ' <img width="50" src="https://www.portalgas.it'.Configure::read('App.web.img.upload.content').'/empty.png" alt="'.$result['SupplierOrganization']['name'].'" /> ';	
		$subject_mail = $result['SupplierOrganization']['name'].", ordine che si apre oggi";
										$Email->subject($subject_mail);

		if($debug && $numResult==1) echo $body_mail_final;

		$mailResults = $Mail->send($Email, [$mail2, $mail], $body_mail_final, $debug);

	}
}