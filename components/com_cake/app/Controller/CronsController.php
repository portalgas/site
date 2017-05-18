<?php
/*
 * in /var/cakephp/cron creo {method}.sh
 * in Lib/UtilsCrons.php creo public function articlesOrdersQtaCart($organization_id)
 * per eseguirle da shell /var/cakephp/cron/...........sh
 */
App::uses('AppController', 'Controller');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

class CronsController extends AppController {

	public function beforeFilter() {
		parent::beforeFilter();

		if(!$this->isRoot()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
		
		$crons = array();
		$arr = array('category' => "Mail",
									'name' => "Mail agli utenti con dettaglio consegna",
									'nota' => "La mail viene inviata il giorno prima",
									'execute' => "Dopo la mezzanotte<br />25 0 * * *",
									'method' => "mailUsersDelivery",
									'stato' => 'Y');
        array_push($crons, $arr);								
		$arr = array('category' => "Mail",
									'name' => "Mail agli utenti degli ordini che si apriranno",
									'nota' => "La mail viene inviata con gli ordini che <b>apriranno</b> tra ".Configure::read('GGMailToAlertOrderOpen')." giorni (è 0 perchè sono quelli del giorno corrente) o con Order.mail_open_send = Y ",
									'execute' => "Dopo la mezzanotte<br />15 0 * * *",
									'method' => "mailUsersOrdersOpen",
									'stato' => 'Y');
		array_push($crons, $arr);									
		$arr = array('category' => "Mail",
									'name' => "Mail agli utenti degli ordini che si stanno chiudendo",
									'nota' => "La mail viene inviata con gli ordini che tra ".Configure::read('GGMailToAlertOrderClose')." giorni si <b>chiuderanno</b>",
									'execute' => "Dopo la mezzanotte<br />20 0 * * *",
									'method' => "mailUsersOrdersClose",
									'stato' => 'Y');
		array_push($crons, $arr);								
		$arr = array('category' => "Mail",
									'name' => "Mail agli utenti per notifica Event Calendar",
									'nota' => "La mail viene inviata per Utenti che hanno Eventi con alert_date_fe = al giorno corrente",
									'execute' => "Dopo la mezzanotte<br />35 0 * * *",
									'method' => "mailEvents",
									'stato' => 'Y');
		array_push($crons, $arr);	
		$arr = array('category' => "Mail",
									'name' => "Mail ai referenti per i prodotti che hanno raggiunto il limite",
									'nota' => "Ai referenti per i prodotti che hanno <b>raggiunto il limite</b>",
									'execute' => "10 12,17,20 * * *",
									'method' => "mailReferentiQtaMax",
									'stato' => 'Y');
		array_push($crons, $arr);	
		$arr = array('category' => "Mail",
									'name' => "Mail ai referenti se la quantità massima dell'ordine ha raggiunto il limite",
									'nota' => "Ai referenti se la <b>quantità massima</b> dell'ordine ha raggiunto il limite",
									'execute' => "11 13,18,21 * * *",
									'method' => "mailReferentiOrderQtaMax",
									'stato' => 'Y');
		array_push($crons, $arr);	
		$arr = array('category' => "Mail",
									'name' => "Mail ai referenti se l'importo massimo dell'ordine ha raggiunto il limite",
									'nota' => "Ai referenti se l'<b>importo massimo</b> dell'ordine ha raggiunto il limite",
									'execute' => "9 11,114,19 * * *",
									'method' => "mailReferentiOrderImportoMax",
									'stato' => 'Y');
		array_push($crons, $arr);	
		$arr = array('category' => "Mail",
									'name' => "Mail ai referenti quando l'ordine &egrave; terminto",
									'nota' => "Solo per i referenti che hanno i produttori <b>monitorati</b>",
									'execute' => "",
									'method' => "mailMonitoringSuppliersOrganizationsOrdersDataFine",
									'stato' => 'Y');
		array_push($crons, $arr);	
		$arr = array('category' => "GCalendar",
									'name' => "Event agli utenti con notifica consegna",
									'nota' => "le consegne OPEN e non elaborate (Delivery.gcalendar_event_id null)",
									'execute' => "Dopo la mezzanotte<br />25 0 * * *",
									'method' => "gcalendarUsersDeliveryInsert",
									'stato' => 'Y');
		array_push($crons, $arr);	
		$arr = array('category' => "GCalendar",
									'name' => "Event agli utenti con dettaglio consegna",
									'nota' => "Evento viene inviata il giorno prima",
									'execute' => "Dopo la mezzanotte<br />25 0 * * *",
									'method' => "gcalendarUsersDeliveryUpdate",
									'stato' => 'Y');
		array_push($crons, $arr);			
		$arr = array('category' => "Consegne",
									'name' => "Consegne: stato elaborazione",
									'nota' => "Elabora tutte le consegne:<ul><li>tutti gli ordini hanno lo <b>stato_elaborazione</b> = CLOSE</li><li>Order.tesoriere_stato_pay = Y</li><li>isToStoreroomPay = Y</li></ul> setta lo stato_elaborazione della consegna a CLOSE",
									'execute' => "All'una, <b>prima</b> di requestPaymentStatoElaborazione()<br />0 1 * * *",
									'method' => "deliveriesStatoElaborazione",
									'stato' => 'Y');
		array_push($crons, $arr);			
		$arr = array('category' => "Consegne",
									'name' => "Consegne del Cassiere: CLOSE",
									'nota' => "Vengono chiuse le consegne di ".Configure::read('GGDeliveryCassiereClose')." gg non chiuse dal cassiere,<br >con gli ordini CLOSE e Order.tesoriere_stato_pay = Y",
									'execute' => "",
									'method' => "deliveriesCassiereClose",
									'stato' => 'Y');
		array_push($crons, $arr);	
		$arr = array('category' => "Consegne",
									'name' => "Consegne: ricorsione",
									'nota' => "Elabora tutte le consegne del giorno e se sono impostate come ricorsive crea la nuova consegna",
									'execute' => "Prima della mezzanotte<br />30 23 * * *",
									'method' => "loopsDeliveries",
									'stato' => 'Y');
		array_push($crons, $arr);					
		$arr = array('category' => "Ordini",
									'name' => "Ordini: stato elaborazione",
									'nota' => "Elabora tutti gli stati elaborazione degli ordini<br />Riporta gli ordini ancora aperti stato elaborazione = 'OPEN' e<br />gli ordini chiusi stato elaborazione = 'PROCESSED-BEFORE-DELIVERY'",
									'execute' => "Dopo la mezzanotte<br />5 0 * * *",
									'method' => "ordersStatoElaborazione",
									'stato' => 'Y');
		array_push($crons, $arr);	
		$arr = array('category' => "Ordini",
								'name' => "Ordini: chiudo gli ordini pagati alla consegna",
								'nota' => "Elabora gli ordine in PROCESSED-ON-DELIVERY (in carico al cassiere durante la consegna) se tutti gli utenti hanno pagato SummaryOrder.importo = SummaryOrder.importo_pagato li chiudo",
								'execute' => "Dopo la mezzanotte<br />5 0 * * *",
								'method' => "ordersIncomingOnDeliveryToClose",
								'stato' => 'Y');
		array_push($crons, $arr);	
		$arr = array('category' => "Ordini",
								'name' => "Ordini: ricorsione",
								'nota' => "Elabora tutti gli ordine del giorno e se sono impostati come ricorsivi crea il nuovo ordine",
								'execute' => "Prima della mezzanotte<br />40 23 * * *<br />dopo loopsDeliveries",
								'method' => "loopsOrders",
								'stato' => 'N');
		array_push($crons, $arr);
		$arr = array('category' => "DES",
									'name' => "Ordine condiviso: stato elaborazione",
									'nota' => "Aggiorna lo stato dei DesOrders in base allo stato degli ordini associati",
									'execute' => "Dopo ordersStatoElaborazione ",
									'method' => "desOrdersStatoElaborazione",
									'stato' => 'Y');
		array_push($crons, $arr);		
		$arr = array('category' => "Dispensa",
									 'name' => "Articoli acquistati (Cart) dalla Dispensa vengono messi in Dispensa ala chiusura della Consegna",
									'nota' => "Validazione degli ordini (articoli con colli): gli articoli messi nel carrello per l'utente Dispensa vengono messi in Dispensa quando si chiude la consegna. Gli articoli dal Carrello alla Dispensa vengono copiati perche' in Cart servono per conteggi",
									'execute' => "Prima della mezzanotte<br />58 23 * * *",
									'method' => "articlesFromCartToStoreroom",
									'stato' => 'Y');
		array_push($crons, $arr);	
		$arr = array('category' => "Articoli",
									 'name' => "Articoli associati agli ordini: aggiorna qta_cart",
									'nota' => "Aggiorna il <b>totale della quantita'</b> acquistata per ogni articolo (ArticlesOrder.qta_cart) ed eventualmente ArticlesOrder.stato (QTAMAXORDER)",
									'execute' => "Dopo la mezzanotte<br />40 0 * * *",
									'method' => "articlesOrdersQtaCart",
									'stato' => 'Y');
		array_push($crons, $arr);	
		$arr = array('category' => "Articoli",
									 'name' => "Articoli: aggiorna campo bio",
									'nota' => "campo <b>Bio</b> in base a isArticlesTypeBio() se ho valorizzato ArticleType.bio o ArticleType.biodinamico",
									'execute' => "Dopo la mezzanotte<br />10 1 * * *",
									'method' => "articlesBio",
									'stato' => 'Y');
		array_push($crons, $arr);	
		$arr = array('category' => "Rich pagamento",
									 'name' => "Richieste di pagamento: stato elaborazione",
									'nota' => "Elabora tutte SummaryPayment: se tutti i SummaryPayment hanno importo_dovuto = importo_pagato setta lo stato_elaborazione della richiesta di pagamento a CLOSE",
									'execute' => "Alle 2, <b>dopo</b> deliveriesStatoElaborazione() <b>prima</b> archiveStatistics()<br />0 2 * * *",
									'method' => "requestPaymentStatoElaborazione",
									'stato' => 'Y');
		array_push($crons, $arr);		
		$arr = array('category' => "Archivia in statistiche",
									'name' => "Archivia/cancella consegne, ordine, aquisti",
									'nota' => "Elabora tutte RequestPayment.stato_elaborazione = CLOSE o Delivery.stato_elaborazione = CLOSE<br />cancellazione Carrello / Ordini / Consegne<br />dati archiviati in Statistiche",
									'execute' => "Alle 3, <b>dopo</b> requestPaymentStatoElaborazione()<br />0 3 * * *",
									'method' => "archiveStatistics",
									'stato' => 'Y');
		array_push($crons, $arr);	
		$arr = array('category' => "Supplier",
									'name' => "Maps google",
									'nota' => "Dall'indirizzo cerca lng e lat",
									'execute' => "Ogni x ore",
									'method' => "suppliersGmaps",
									'stato' => 'Y');
		array_push($crons, $arr);	
		$arr = array('category' => "Users",
									'name' => "Maps google",
									'nota' => "Dall'indirizzo cerca lng e lat",
									'execute' => "Ogni x ore",
									'method' => "usersGmaps",
									'stato' => 'Y');
		array_push($crons, $arr);	
		$arr = array('category' => "Users",
									'name' => "Gruppo referenti",
									'nota' => "Controllo se l'utente è un referente ed appartiene o no al gruppo",
									'execute' => "manuale",
									'method' => "usersSuppliersOrganizationsReferents",
									'stato' => 'Y');
		array_push($crons, $arr);	
		$arr = array('category' => "Rss",
									'name' => "Crea file .rss",
									'nota' => "Per ogni GAS crea /rss/seo.rss",
									'execute' => "ogni mezz'ora",
									'method' => "rss",
									'stato' => 'Y');
		array_push($crons, $arr);	
		$arr = array('category' => "Filesystem",
									'name' => "Backup",
									'nota' => "",
									'execute' => "0 5 * * *",
									'method' => "backup",
									'stato' => 'Y');
		array_push($crons, $arr);	
		$arr = array('category' => "Filesystem",
									'name' => "Cancella i file log, dump e backup",
									'nota' => "quelli vecchi di enne gg",
									'execute' => "0 7 * * *",
									'method' => "filesystemLogDelete",
									'stato' => 'Y');
		array_push($crons, $arr);	
		$arr = array('category' => "Database",
									'name' => "Dump database logs",
									'nota' => "",
									'execute' => "0 4 * * *",
									'method' => "database_dump",
									'stato' => 'Y');
		array_push($crons, $arr);	
		$arr = array('category' => "Database",
									'name' => "Import database produzione in test",
									'nota' => "da eseguire dalla shell del server",
									'execute' => "manuale",
									'method' => "database_import_prod_to_test",
									'stato' => 'Y');
		array_push($crons, $arr);	
		$arr = array('category' => "Produttori: consegna",
									'name' => "Consegna: stato elaborazione",
									'nota' => "Elabora tutti gli stati elaborazione delle consegne",
									'execute' => "Dopo la mezzanotte",
									'method' => "prodDeliveriesStatoElaborazione",
									'stato' => 'N');
		array_push($crons, $arr);	
		$arr = array('category' => "System",
									'name' => "invio mail dei logs",
									'nota' => "Invia le mail con error.log e altri settati",
									'execute' => "15 7 * * *",
									'method' => "/mails.sh",
									'stato' => 'Y');
		array_push($crons, $arr);			
		
		$this->set('crons', $crons);
	}

	public function admin_index() {
		$dir_size_backup = $this->__admin_dir_size(Configure::read('App.cron.backup'));
		$dir_size_dump   = $this->__admin_dir_size(Configure::read('App.cron.dump'));  
		$dir_size_log   = $this->__admin_dir_size(Configure::read('App.cron.log'));  
		$GGDeleteBackup = Configure::read('GGDeleteBackup');
		
		$this->set(compact('dir_size_backup','dir_size_dump','dir_size_log','GGDeleteBackup'));
	}
	
	public function admin_read($fileLog) {
		
		// echo Configure::read('App.cron.log'). DS . $fileLog;
		$file = new File(Configure::read('App.cron.log'). DS . $fileLog);
		if($file->exists()) {
			$contents = $file->read();
			$file->close(); 
		}
		else {
			$file = null;
			$contents = null;
		}
		
		$this->set('file',$file);
		$this->set('contents',$contents);	
	}

	public function admin_execute($metohd) {
		$utilsCrons = new UtilsCrons(new View(null));
		echo "<pre>";
		$utilsCrons->$metohd($this->user->organization['Organization']['id'], true);
		echo "</pre>";		
	}
	
	public function admin_execute_des($metohd) {
		
		if(empty($this->user->des_id)) {
			App::import('Model', 'De');			
			$De = new De();
   		
			$options = array();
			$options['recursive'] = -1;
			$options['fields'] = array('De.id');
			$results = $De->find('all', $options);

			foreach($results as $result) {
				
				$des_id = $result['De']['id'];
				
				$utilsCrons = new UtilsCrons(new View(null));
				echo "<pre>";
				$utilsCrons->$metohd($des_id, true);
				echo "</pre>";			
			}				
		}
		else {
			$utilsCrons = new UtilsCrons(new View(null));
			echo "<pre>";
			$utilsCrons->$metohd($this->user->des_id, true);
			echo "</pre>";					
		}
	}
	
	private function __admin_dir_size($dirPath) {
		$dir = new Folder($dirPath);
		return $dir->dirsize();
	}
}