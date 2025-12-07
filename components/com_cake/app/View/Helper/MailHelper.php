<?php
class MailHelper extends AppHelper {
        
    var $helpers = ['Html'];

    public function drawGoToCart($user, $delivery_id, $utente): string {

		$html = "";
		
		$j_seo = $user->organization['Organization']['j_seo'];

		$url = Configure::read('Portalgas.url').'/home-'.$j_seo.'/preview-carrello-'.$j_seo.'?'.$this->getUrlCartPreviewNoUsername($user, $delivery_id);
		$url = str_replace("{u}", urlencode($this->getUsernameCrypted($utente['User']['username'])), $url);

		$html .= '<div style="clear: both; float: none; margin: 5px 0 15px;">'; 
		$html .= '<img src="'.Configure::read('Portalgas.urlMail').Configure::read('App.img.cake').'/cesta-piena.png" title="" border="0" />';
		$html .= ' <a target="_blank" href="'.$url.'">Clicca qui per visualizzare i tuoi <b>acquisti</b> che dovrai ritirare durante la consegna</a>';
		$html .= '</div>'; 

		return $html;		
	}	  

	 /*
	  * creo url senza lo username, 
	  * in Cron::mailUsersOrdersOpen, quando ciclo per utenti ho gia' creato il messaggio per consegna
	  */
	  public function getUrlCartPreviewNoUsername($user, $delivery_id) {
	 	 
		$tmp = "";
	
		$E = '';
		$O = '';
		$R = '';
		$D = '';
		$org_id = '';
		 
		$E = $this->utilsCommons->randomString($length=5);
		 
		$O = rand (10, 99).$user->organization['Organization']['id'];
		 
		$R = "{u}";
		 
		$D = rand (10, 99).$delivery_id;
		 
		$org_id = $user->organization['Organization']['id'];
	
		$tmp = 'E='.$E.'&O='.$O.'&R='.$R.'&D='.$D.'&org_id='.$org_id;
		 
		return $tmp;
	}
	
	 /*
	 * creo link della mail /preview-carrello?E=3456434&O=451&R=fHqbzWjOK6GaWezgE4mycHsphSPsE9HhincbgjTmDjY=&format=html
	 * 	E = random, non serve a niente
	 *  O = (tolgo i primi 2 numeri e poi organization_id) organization_id
	 *  R = username crittografata User->getUsernameCrypted()
	 *  D = (tolgo i primi 2 numeri e poi delivery_id) delivery_id
	 *  org_id serve per mod_gas_organization_choice	  */
	public function getUrlCartPreview($user, $username, $delivery_id) {
	 	
		$tmp = "";

		$E = '';
		$O = '';
		$R = '';
		$D = '';
		$org_id = '';
		
		$E = $this->utilsCommons->randomString($length=5);
		
		$O = rand (10, 99).$user->organization['Organization']['id'];
		
		$R = urlencode($this->getUsernameCrypted($username));
		
		$D = rand (10, 99).$delivery_id;
		
		$org_id = $user->organization['Organization']['id'];

		$tmp = 'E='.$E.'&O='.$O.'&R='.$R.'&D='.$D.'&org_id='.$org_id;
		
		return $tmp;
	}

	public function getUsernameCrypted($username) {
	 	
		$salt = Configure::read('Security.salt');
		
		/*
		 * crea stringa cifrata ma non leggibile
		* php 7.4 non supportato
		 $encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $salt, $username, MCRYPT_MODE_ECB);
		  *
		 * converte stringa cifrata in modo leggibile (MGCP+iQL/0qPiL2H62c+WXrnY856xfided9FJhjarEU=)
		$encrypted_base64 = base64_encode($encrypted);	
		*/
		$encrypted = $this->utilsCommons->encoding($username);

		return $encrypted;
	}
}
?>