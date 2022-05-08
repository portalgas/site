<?php
App::uses('AppModel', 'Model');

class Tesoriere extends AppModel {

	public $useTable = false;
	
	public function updateAfterUpload($user, $request, $esito, $inviato_al_tesoriere_da='REFERENTE', $debug=false) {
		
		self::d([$request,$esito], $debug);
		
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
			
			$sql = "UPDATE
				`".Configure::read('DB.prefix')."orders`
			SET ";
			
			if(!empty($esito['fileNewName']))
				$sql .= " tesoriere_doc1 = '".addslashes($esito['fileNewName'])."',";
			 
			$sql .= "
				tesoriere_nota = '".addslashes($request['Order']['tesoriere_nota'])."', 
				tesoriere_fattura_importo = ".$this->importoToDatabase($tesoriere_fattura_importo).",
				inviato_al_tesoriere_da = '".$inviato_al_tesoriere_da."', 
				tot_importo = ".$tot_importo." ,  
				modified = '".date('Y-m-d H:i:s')."'
			WHERE
				organization_id = ".(int)$user->organization['Organization']['id']."
				and id = ".(int)$request['Order']['id'];
			self::d($sql, $debug);
			self::l($sql, $debug);			
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
	public function sendMailToUpload($user, $data, $results, $inviato_al_tesoriere_da='REFERENTE', $debug=false) {
		
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
		if($inviato_al_tesoriere_da=='REFERENTE')
			$body_mail  .= "è stato passato dal referente ";
		else
		if($inviato_al_tesoriere_da=='CASSIERE')
			$body_mail  .= "è stato passato dal cassiere ";
		$body_mail  .= "(".$user->name." - <a href=mailto:".$user->email.">".$user->email."</a>) del produttore al tesoriere"; 
		$body_mail  .= "<br />";
		if(!empty($importo_totale) && $importo_totale!='0.00') $body_mail  .= "<br />".__('Importo totale ordine').": ".number_format($importo_totale,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).' &euro;';
		if(!empty($tesoriere_fattura_importo) && $tesoriere_fattura_importo!='0.00') $body_mail  .= "<br />".__('Tesoriere fattura importo').": ".$tesoriere_fattura_importo.' &euro;';
		if(!empty($tesoriere_nota)) $body_mail  .= "<br />Nota del referente: ".$tesoriere_nota;
	
		$Email->subject($subject_mail);
		$Email->viewVars(['body_footer_simple' => sprintf(Configure::read('Mail.body_footer'))]);
		
		if($debug) 
			echo "<br />Tesoriere::sendMailToUpload() ".$body_mail;
		
		foreach ($userResults as $userResult)  {
					
			$name = $userResult['User']['name'];
			$mail = $userResult['User']['email'];
				
			if(!empty($mail)) {
				$Email->viewVars(['body_header' => sprintf(Configure::read('Mail.body_header'), $name)]);
				$Email->to($mail);
					
				$Mail->send($Email, $mail, $body_mail, $debug);
			} // end if(!empty($mail))
		} // end foreach ($userResults as $userResult)		
	}
	
	/*
	 * aggiornamento dei dati da 
 	 * 	Tesoriere::pay_suppliers (Pagamenti per consegna)
 	 *  Tesoriere::pay_suppliers_by_supplier (Pagamenti per produttore)
	 */
	public function updateFromModulo($user, $order_id, $data, $debug=false) {

		/*
		 *   ctrl che siano cambiati i dati
		 */
		 $sqlTmp = "";
		 if($this->importoToDatabase($data['tesoriere_importo_pay']) != $data['tesoriere_importo_pay_old']) {
             if(empty($data['tesoriere_importo_pay']))
                 $data['tesoriere_importo_pay'] = 0;
             $sqlTmp .= " tesoriere_importo_pay = ".$this->importoToDatabase($data['tesoriere_importo_pay']).',';
         }

		 if($data['tesoriere_data_pay_db'] != $data['tesoriere_data_pay_old'])
		 	$sqlTmp .= " tesoriere_data_pay = '".$data['tesoriere_data_pay_db']."',";
		 
		 if(empty($data['tesoriere_stato_pay'])) 
			$data['tesoriere_stato_pay'] = 'N';
		 	
		 if($data['tesoriere_stato_pay'] != $data['tesoriere_stato_pay_old'])
		 	$sqlTmp .= " tesoriere_stato_pay = '".$data['tesoriere_stato_pay']."',";

		self::d('Order.id '.$order_id.' ctrl se parametri diversi', $debug);
		self::d('	tesoriere_importo_pay '.$this->importoToDatabase($data['tesoriere_importo_pay']).' tesoriere_importo_pay_old '.$data['tesoriere_importo_pay_old'], $debug);
		self::d('	tesoriere_data_pay_db '.$data['tesoriere_data_pay_db'].' tesoriere_data_pay_old '.$data['tesoriere_data_pay_old'], $debug);
		self::d('	tesoriere_stato_pay '.$data['tesoriere_stato_pay'].' tesoriere_stato_pay_old '.$data['tesoriere_stato_pay_old'], $debug);
		self::d('	sqlTmp '.$sqlTmp, $debug);
		
		if(!empty($sqlTmp)) {
		
			try {
				$sql = "UPDATE
							`".Configure::read('DB.prefix')."orders`
						SET
							".$sqlTmp."
							modified = '".date('Y-m-d H:i:s')."'
						WHERE
							organization_id = ".(int)$user->organization['Organization']['id']."
							and id = ".(int)$order_id;
				self::d($sql, $debug);
				$resultUpdate = $this->query($sql);
			}
			catch (Exception $e) {
				CakeLog::write('error',$sql);
				CakeLog::write('error',$e);
			}
		} // if(!empty($sqlTmp))
	
		return true;
	}
}