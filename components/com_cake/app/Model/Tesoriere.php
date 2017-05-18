<?php
App::uses('AppModel', 'Model');

class Tesoriere extends AppModel {

	public $useTable = false;
	
	public function updateAfterUpload($user, $request, $esito, $tesoriere_sorce='REFERENTE', $debug=false) {
		
		if($debug) {
			echo "<pre>Tesoriere::updateAfterUpload() ";
			print_r($request);
			print_r($esito);
			echo "</pre>";
		}
		
		$tesoriere_fattura_importo = $request['Order']['tesoriere_fattura_importo'];
		if(empty($tesoriere_fattura_importo))
			$tesoriere_fattura_importo = '0,00';
		
		/*
		 * update database
		 *  aggiorno stato dell'ordine
		*/
		try {

			/*
			 * return $importo_totale gia' formattato 1000.00
			 */		
			App::import('Model', 'Order');
			$Order = new Order;
		
			$tot_importo = $Order->getTotImporto($user, $request['Order']['id']);
			/* 
			 *  bugs float: i float li converte gia' con la virgola!  li riporto flaot
			 */
			if(strpos($tot_importo,',')!==false)  $tot_importo = str_replace(',','.',$tot_importo);					 
			
			$sql = "UPDATE
				`".Configure::read('DB.prefix')."orders`
			SET ";
			
			if(!empty($esito['fileNewName']))
				$sql .= " tesoriere_doc1 = '".addslashes($esito['fileNewName'])."',";
			 
			$sql .= "
				tesoriere_nota = '".addslashes($request['Order']['tesoriere_nota'])."', 
				tesoriere_fattura_importo = ".$this->importoToDatabase($tesoriere_fattura_importo).",
				tesoriere_sorce = '".$tesoriere_sorce."', 
				tot_importo = ".$tot_importo." ,  
				modified = '".date('Y-m-d H:i:s')."'
			WHERE
				organization_id = ".(int)$user->organization['Organization']['id']."
				and id = ".(int)$request['Order']['id'];
			if($debug) echo '<br />'.$sql;
			$result = $Order->query($sql);
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
			$msg = __('Order State in Wait Processed Tesoriere Error');
		}		

		return $msg;
	}

	/*
	 * invio mail a Configure::read('group_id_tesoriere')
	 * data: dati del form $this->request->data (tot_importo, tesoriere_fattura_importo, tesoriere_nota)
	 * results: dati ordine precedenti al salvataggio
	*/	
	public function sendMailToUpload($user, $data, $results, $tesoriere_sorce='REFERENTE', $debug=false) {
		
		$importo_totale = $data['Order']['importo_totale'];
		$tesoriere_fattura_importo = $data['Order']['tesoriere_fattura_importo'];
		$tesoriere_nota = $data['Order']['tesoriere_nota'];

		if($debug) {
			echo "<pre>Tesoriere::sendMailToUpload() ";
			print_r($data);
			print_r($results);
			echo "</pre>";
		}
					
		App::import('Model', 'User');
		$User = new User;
		
		App::import('Model', 'Mail');
		$Mail = new Mail;
			
		$Email = $Mail->getMailSystem($user);
			
		$conditions = array('UserGroupMap.group_id' => Configure::read('group_id_tesoriere'));
		$userResults = $User->getUsers($user, $conditions);
		
		$subject_mail = "Ordine di ".$results['SuppliersOrganization']['name']." passato al tesoriere";
		$body_mail  = "L'ordine del produttore <b>".$results['SuppliersOrganization']['name']."</b> per la consegna <b>".$results['Delivery']['luogoData']."</b> ";
		if($tesoriere_sorce=='REFERENTE')
			$body_mail  .= "è stato passato dal referente ";
		else
		if($tesoriere_sorce=='CASSIERE')
			$body_mail  .= "è stato passato dal cassiere ";
		$body_mail  .= "(".$user->name." - <a href=mailto:".$user->email.">".$user->email."</a>) del produttore al tesoriere"; 
		$body_mail  .= "<br />";
		if(!empty($importo_totale) && $importo_totale!='0.00') $body_mail  .= "<br />".__('Importo totale ordine').": ".number_format($importo_totale,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).' &euro;';
		if(!empty($tesoriere_fattura_importo) && $tesoriere_fattura_importo!='0.00') $body_mail  .= "<br />".__('Tesoriere fattura importo').": ".$tesoriere_fattura_importo.' &euro;';
		if(!empty($tesoriere_nota)) $body_mail  .= "<br />Nota del referente: ".$tesoriere_nota;
	
		$Email->subject($subject_mail);
		$Email->viewVars(array('body_footer_simple' => sprintf(Configure::read('Mail.body_footer'))));
		
		if($debug) 
			echo "<br />Tesoriere::sendMailToUpload() ".$body_mail;
		
		foreach ($userResults as $userResult)  {
					
			$name = $userResult['User']['name'];
			$mail = $userResult['User']['email'];
				
			if(!empty($mail)) {
				$Email->viewVars(array('body_header' => sprintf(Configure::read('Mail.body_header'), $name)));
				$Email->to($mail);
					
				$Mail->send($Email, $mail, $body_mail, $debug);
			} // end if(!empty($mail))
		} // end foreach ($userResults as $userResult)		
	}
	
}