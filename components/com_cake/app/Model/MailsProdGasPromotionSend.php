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
	public function trasmissionToGas($user, $organization_id, $prod_gas_promotion_id, $options=[], $debug = false) {

		$mailResults = true;
		
		$nota_supplier = '';
		if(isset($options['nota_supplier']))
			$nota_supplier = $options['nota_supplier'];

        App::import('Model', 'ProdGasPromotion');
        $ProdGasPromotion = new ProdGasPromotion;

        App::import('Model', 'ProdGasPromotionsOrganizationsManager');
        $ProdGasPromotionsOrganizationsManager = new ProdGasPromotionsOrganizationsManager;

		$usersResults = $ProdGasPromotionsOrganizationsManager->getReferents($user, $prod_gas_promotion_id);
		// if($debug) debug($usersResults);

		$promotionResults = $ProdGasPromotion->getProdGasPromotion($user, $prod_gas_promotion_id);
		if($debug) debug($promotionResults);

		$body_mail = '';
		if(!empty($promotionResults['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$promotionResults['Supplier']['img1']))
				$body_mail .= ' <img width="50" src="https://www.portalgas.it'.Configure::read('App.web.img.upload.content').'/'.$promotionResults['Supplier']['img1'].'" alt="'.$result['SupplierOrganization']['name'].'" /> ';
		else
			$body_mail .= ' <img width="50" src="https://www.portalgas.it'.Configure::read('App.web.img.upload.content').'/empty.png" alt="'.$result['SupplierOrganization']['name'].'" /> ';

		$body_mail .= 'Il produttore <b>'.$promotionResults['SuppliersOrganization']['name'].'</b> ha creato per voi una promozione per alcuni suoi prodotti';
		$body_mail .= '<ul>';
		foreach($promotionResults['ProdGasArticlesPromotion'] as $prodGasArticlesPromotion) {
			$body_mail .= '<li>Per l\'articolo ';
			$body_mail .= '<b>'.$prodGasArticlesPromotion['Article']['name'].'</b>, '; 
			$body_mail .= 'acquistandone '.$prodGasArticlesPromotion['ProdGasArticlesPromotion']['qta'].' pezzi, ';
			$body_mail .= 'invece di <del>'.$prodGasArticlesPromotion['Article']['prezzo'].' &euro;</del> '.$prodGasArticlesPromotion['ProdGasArticlesPromotion']['prezzo_unita'].' &euro;';
			$body_mail .= '</li>';
		}
		$body_mail .= '</ul>';
		$body_mail .= '<p>Promozione valida da '.CakeTime::format($promotionResults['ProdGasPromotion']['data_inizio'], "%A %e %B %Y");
		$body_mail .= ' a '.CakeTime::format($promotionResults['ProdGasPromotion']['data_fine'], "%A %e %B %Y");
		$body_mail .= '</p>';
		$body_mail .= '<p>Se pensi che il tuo G.A.S. possa essere interessato, vai su <a target="_blank" href="https://'.Configure::read('SOC.site').'/my">https://'.Configure::read('SOC.site').'/my</a> e apri l\'ordine</p>	';

		// if($debug) debug($body_mail);

        App::import('Model', 'Mail');
        $Mail = new Mail;

		$Email = $Mail->getMailSystem($user);
					
        App::import('Model', 'User');
        $User = new User;
		
        $Email->viewVars(['body_footer' => sprintf(Configure::read('Mail.body_footer_no_reply'), $this->_traslateWww($user->organization['Organization']['www']))]);

        if(!Configure::read('mail.send'))  $Email->transport('Debug');

        /*
         * ciclo UTENTI
         */
        // debug($usersResults);
        foreach($usersResults as $numResult => $usersResult) {

            $mail = $usersResult['User']['email'];
            $mail2 = $usersResult['UserProfile']['email'];
            $name = $usersResult['User']['name'];
            $username = $usersResult['User']['username'];
            
            $Email->viewVars(['body_header' => sprintf(Configure::read('Mail.body_header'), $name)]);

            if($debug)
				echo '<br />'.$numResult.") tratto l'utente ".$name.', username '.$username." \n";

			$subject_mail = $promotionResults['SuppliersOrganization']['name'].": proposta di promozione articoli";
			$Email->subject($subject_mail);

			$body_mail_final = $body_mail . $nota_supplier;
			if($debug && $numResult==1) debug($body_mail_final);
			//debug($body_mail_final);
			$mailResults = $Mail->send($Email, [$mail2, $mail], $body_mail_final, $debug);
		}

		return $mailResults;
	}
}