<?php
App::uses('AppModel', 'Model');

class MailsSend extends AppModel {

	public $useTable = 'deliveries';
	public $actsAs = ['Data'];
	public $virtualFields = ['luogoData' => "CONCAT_WS(' - ',Doc.luogo,DATE_FORMAT(Doc.data, '%W, %e %M %Y'))"];
		
	/*
	 * invio mail 
	 * 		ordini che si aprono oggi
	 * 		ctrl data_inizio con data_oggi
	 * 		mail_open_send = Y (perche' in Order::add data_inizio = data_oggi)
	*/
	public function mailUsersOrdersOpen($organization_id, $user, $debug=false) {

		$debug = true;

			date_default_timezone_set('Europe/Rome');

            try {
                    if($debug)
						echo date("d/m/Y")." - ".date("H:i:s")." Mail agli utenti degli ordini che aperti \n";

                    App::import('Model', 'Mail');
                    $Mail = new Mail;

					$Email = $Mail->getMailSystem($user);
					
                    App::import('Model', 'Order');
                    $Order = new Order;

                    App::import('Model', 'User');
                    $User = new User;

                    if($debug)
						echo "Estraggo gli ordini che apriranno tra ".(Configure::read('GGMailToAlertOrderOpen')+1)." giorni o con mail_open_send = Y \n";
					
                    /*
                     * prima di filtrare per users, ctrl che ci siano ordini da inviare
                     */
                    $sql ="SELECT 
                                `Order`.*,
                                Delivery.*,
                                SupplierOrganization.name, SupplierOrganization.frequenza,
                                Supplier.descrizione, Supplier.img1    
                          FROM 
                                ".Configure::read('DB.prefix')."orders `Order`,
                                ".Configure::read('DB.prefix')."deliveries Delivery,  
                                ".Configure::read('DB.prefix')."suppliers_organizations SupplierOrganization,  
                                ".Configure::read('DB.prefix')."suppliers Supplier  
                          WHERE 
                                `Order`.organization_id = $organization_id
                                and Delivery.organization_id = $organization_id
                                and SupplierOrganization.organization_id = $organization_id 
                                and `Order`.delivery_id = Delivery.id
                                and SupplierOrganization.id = `Order`.supplier_organization_id 
                                and Supplier.id = SupplierOrganization.supplier_id 
                                and SupplierOrganization.stato = 'Y'
                                and SupplierOrganization.mail_order_open = 'Y'
                                and `Order`.isVisibleFrontEnd = 'Y'  and `Order`.isVisibleFrontEnd = 'Y' 
                                and Delivery.isVisibleFrontEnd = 'Y' and Delivery.isVisibleFrontEnd = 'Y' 
                                and  `Order`.state_code != 'CREATE-INCOMPLETE' and `Order`.state_code != 'CLOSE'
                                and (`Order`.data_inizio = CURDATE() - INTERVAL ".Configure::read('GGMailToAlertOrderOpen')." DAY OR `Order`.mail_open_send = 'Y')	
                                order by Delivery.data, Supplier.name ";
                    // self::d($sql, $debug);
                    $orderCtrlResults = $Order->query($sql);
                    
                    if(!empty($orderCtrlResults)) {
                        if($debug)
							echo "Trovati ".count($orderCtrlResults)." ordini \n";
                        
                        $Email->viewVars(['body_footer' => sprintf(Configure::read('Mail.body_footer_no_reply'), $this->_traslateWww($user->organization['Organization']['www']))]);

                        if(!Configure::read('mail.send'))  $Email->transport('Debug');

                        /*
                         * ciclo UTENTI
                         */
                        $usersResults = $User->getUsersToMail($organization_id);

                        foreach($usersResults as $numResult => $usersResult) {

                            $mail = $usersResult['User']['email'];
                            $mail2 = $usersResult['UserProfile']['email'];
                            $name = $usersResult['User']['name'];
                            $username = $usersResult['User']['username'];
                            
                            $Email->viewVars(['body_header' => sprintf(Configure::read('Mail.body_header'), $name)]);

                            if($debug)
								echo '<br />'.$numResult.") tratto l'utente ".$name.', username '.$username." \n";

							/*
							 * ordini filtrati per users, ctrl che ci siano ordini da inviare
							 */
							$sql ="SELECT 
										`Order`.*,
										Delivery.*,
										SupplierOrganization.name, SupplierOrganization.frequenza,
										Supplier.descrizione, Supplier.img1    
								  FROM 
										".Configure::read('DB.prefix')."orders `Order`,
										".Configure::read('DB.prefix')."deliveries Delivery,  
										".Configure::read('DB.prefix')."suppliers_organizations SupplierOrganization,  
										".Configure::read('DB.prefix')."suppliers Supplier  
								  WHERE 
										`Order`.organization_id = $organization_id
										and Delivery.organization_id = $organization_id
										and SupplierOrganization.organization_id = $organization_id 
										and `Order`.delivery_id = Delivery.id
										and SupplierOrganization.id = `Order`.supplier_organization_id 
										and Supplier.id = SupplierOrganization.supplier_id 
										and SupplierOrganization.stato = 'Y'
										and SupplierOrganization.mail_order_open = 'Y'
										and `Order`.isVisibleFrontEnd = 'Y'  and `Order`.isVisibleFrontEnd = 'Y' 
										and Delivery.isVisibleFrontEnd = 'Y' and Delivery.isVisibleFrontEnd = 'Y' 
										and  `Order`.state_code != 'CREATE-INCOMPLETE' and `Order`.state_code != 'CLOSE' 
										and (`Order`.data_inizio = CURDATE() - INTERVAL ".Configure::read('GGMailToAlertOrderOpen')." DAY OR `Order`.mail_open_send = 'Y')

										and `Order`.supplier_organization_id not in (
											SELECT supplier_organization_id 
											FROM ".Configure::read('DB.prefix')."bookmarks_mails where user_id = ".$usersResult['User']['id']." and organization_id = $organization_id and order_open = 'N')

										order by Delivery.data, Supplier.name "; 
							// self::d($sql, $debug);
							$orderResults = $Order->query($sql);
							
							if(!empty($orderResults)) { 
								if($debug)
									echo "Trovati ".count($orderResults)." ordini per lo user ".$usersResult['User']['username']." (".$usersResult['User']['id'].") \n";

								$body_mail = "";
								$delivery_id_old = 0;
								$j_seo = $user->organization['Organization']['j_seo'];

							   /*
								* ciclo sugli ordini dello user 
								* per creare il mailBody
								 */
								foreach ($orderResults as $result) {

									if($delivery_id_old==0 || $delivery_id_old != $result['Delivery']['id']) {

										if($delivery_id_old != $result['Delivery']['id']) {

												if($delivery_id_old > 0) {
														/*
														 * manca lo username crittografato, lo faccio al ciclo degli utenti
														*/
														$url = 'https://www.portalgas.it/home-'.$j_seo.'/preview-carrello-'.$j_seo.'?'.$User->getUrlCartPreviewNoUsername($user, $delivery_id_old);

														$body_mail .= '<div style="clear: both; float: none; margin: 5px 0 15px;">'; 
														$body_mail .= '<img src="https://www.portalgas.it'.Configure::read('App.img.cake').'/cesta-piena.png" title="" border="0" />';
														$body_mail .= ' <a target="_blank" href="'.$url.'">Clicca qui per visualizzare i tuoi <b>acquisti</b> che dovrai ritirare durante la consegna</a>';
														$body_mail .= '</div>'; 
												}
										}

										if($result['Delivery']['sys']=='Y')
												$body_mail .= "<br />\nPer una consegna <b>".$result['Delivery']['luogo']."</b><br />\n";						
										else
												$body_mail .= "<br />\nPer la consegna di <b>".CakeTime::format($result['Delivery']['data'], "%A %e %B %Y")."</b> a ".$result['Delivery']['luogo']."<br />\n";

										if(count($orderResults)==1) {
												$body_mail .= "si <span style='color:green;'>apre</span> oggi il periodo d'ordine nei confronti del seguente produttore:<br />\n<br />\n";
												$subject_mail = $result['SupplierOrganization']['name'].", ordine che si apre oggi";
										} 
										else {
												$body_mail .= "si <span style='color:green;'>apre</span> oggi il periodo d'ordine nei confronti dei seguenti produttori: <br />\n<br />\n";
												$subject_mail = $this->_organizationNameError($user->organization).", ordini che si aprono oggi";												
										}
										$Email->subject($subject_mail);

									} // end if($delivery_id_old==0 || $delivery_id_old != $result['Delivery']['id'])



									//$body_mail .= ((int)$numResult+1).") ".$result['SupplierOrganization']['name'];
									$body_mail .= "<div style='clear:both;float:none;margin-top:5px;'>";	
									$body_mail .= "- ";						
									$body_mail .= $result['SupplierOrganization']['name'];
									if(!empty($result['Supplier']['descrizione'])) $body_mail .= "/".$result['Supplier']['descrizione'];
									if(!empty($result['SupplierOrganization']['frequenza'])) $body_mail .= " (frequenza ".$result['SupplierOrganization']['frequenza'].')';
									$body_mail .= " fino a ".CakeTime::format($result['Order']['data_fine'], "%A %e %B %Y");

									if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
											$body_mail .= ' <img width="50" src="https://www.portalgas.it'.Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" alt="'.$result['SupplierOrganization']['name'].'" /> ';
									else
										$body_mail .= ' <img width="50" src="https://www.portalgas.it'.Configure::read('App.web.img.upload.content').'/empty.png" alt="'.$result['SupplierOrganization']['name'].'" /> ';										

									$body_mail .= "<br />\n";

									if(!empty($result['Order']['mail_open_testo'])) {
											$body_mail .= '<div style="float:right;width:75%;margin-top:5px;">';
											$body_mail .= '<span style="color:red;">Nota</span> ';
											$body_mail .= $result['Order']['mail_open_testo'];
											$body_mail .= '</div>';
									}
									$body_mail .= '</div>';

									$delivery_id_old = $result['Delivery']['id'];

								} // loops Orders dello user
								
								
								/*
								 * manca lo username crittografato, lo faccio al ciclo degli utenti
								 */
								$url = 'https://www.portalgas.it/home-'.$j_seo.'/preview-carrello-'.$j_seo.'?'.$User->getUrlCartPreviewNoUsername($user, $delivery_id_old);

								$body_mail .= '<div style="clear: both; float: none; margin: 5px 0 15px;">'; 
								$body_mail .= '<img src="https://www.portalgas.it'.Configure::read('App.img.cake').'/cesta-piena.png" title="" border="0" />';
								$body_mail .= ' <a target="_blank" href="'.$url.'">Clicca qui per visualizzare i tuoi <b>acquisti</b> che dovrai ritirare durante la consegna</a>';
								$body_mail .= '</div>'; 
								 
								/*
								 * all'url per il CartPreview aggiungo lo username crittografato
								 */
								$body_mail_final = str_replace("{u}", urlencode($User->getUsernameCrypted($username)), $body_mail);
								
								if($debug && $numResult==1) echo $body_mail_final ."\n";

								$mailResults = $Mail->send($Email, [$mail2, $mail], $body_mail_final, false);

							}   
							else {
							   if($debug)
								   echo "Per lo user ".$usersResult['User']['username']." (".$usersResult['User']['id'].") non ci sono ordini da inviare \n";                      
							}
                        } // loops Users
                        
                        /*
                         * per gli ordini trovati 
                         * UPDATE Order.mail_open_send, Order.mail_open_data
                         */
                        foreach ($orderCtrlResults as $orderCtrlResult) {
                            $sql ="UPDATE ".Configure::read('DB.prefix')."orders 
                                  SET
                                    mail_open_send = 'N',
                                    mail_open_data = '".date('Y-m-d H:i:s')."'
                                  WHERE
                                    organization_id = $organization_id
                                    and id = ".$orderCtrlResult['Order']['id'];
                            self::d($sql, $debug);
                            $Order->query($sql);
                        }
                        
                    } // end if(!empty($orderCtrlResults))
                    else 
                        echo "non ci sono ordini che apriranno tra ".(Configure::read('GGMailToAlertOrderOpen')+1)." giorni \n";
            }
            catch (Exception $e) {
                echo '<br />UtilsCrons::mailUsersOrdersOpen()<br />'.$e;
            }                    
	}

	public function mailUsersOrdersClose($organization_id, $user, $debug=false) {
		
		$debug = true;

            date_default_timezone_set('Europe/Rome');

            try {
                    echo date("d/m/Y")." - ".date("H:i:s")." Mail agli utenti degli ordini che si chiuderanno tra ".(Configure::read('GGMailToAlertOrderClose')+1)." giorni \n";

                    App::import('Model', 'Mail');
                    $Mail = new Mail;

					$Email = $Mail->getMailSystem($user);
					
                    App::import('Model', 'Order');
                    $Order = new Order;

                    App::import('Model', 'User');
                    $User = new User;
			
                    if($debug)
						echo "Estraggo gli ordini che chiuderanno tra ".(Configure::read('GGMailToAlertOrderClose')+1)." giorni \n";
					
                    /*
                     * prima di filtrare per users, ctrl che ci siano ordini da inviare
                     */                    
                    $sql ="SELECT 
                                `Order`.*,
                                Delivery.*,
                                SupplierOrganization.name, SupplierOrganization.frequenza,
                                Supplier.descrizione, Supplier.img1   
                           FROM 
                                ".Configure::read('DB.prefix')."orders `Order`,
                                ".Configure::read('DB.prefix')."deliveries Delivery,  
                                ".Configure::read('DB.prefix')."suppliers_organizations SupplierOrganization,  
                                ".Configure::read('DB.prefix')."suppliers Supplier  
                           WHERE 
                                `Order`.organization_id = $organization_id
                                and Delivery.organization_id = $organization_id
                                and SupplierOrganization.organization_id = $organization_id 
                                and `Order`.delivery_id = Delivery.id
                                and SupplierOrganization.id = `Order`.supplier_organization_id 
                                and Supplier.id = SupplierOrganization.supplier_id 
                                and SupplierOrganization.stato = 'Y'
                                and SupplierOrganization.mail_order_close = 'Y'
                                and `Order`.data_fine = CURDATE() + INTERVAL ".Configure::read('GGMailToAlertOrderClose')." DAY 
                                and  `Order`.state_code != 'CREATE-INCOMPLETE' and `Order`.state_code != 'CLOSE'
                                and `Order`.isVisibleFrontEnd = 'Y' 
                                and Delivery.isVisibleFrontEnd = 'Y' 
                                order by Delivery.data, Supplier.name ";
                    // self::d($sql, $debug);
                    $orderCtrlResults = $Order->query($sql);
                    
					if(!empty($orderCtrlResults)) {
                        if($debug)
							echo "Trovati ".count($orderCtrlResults)." ordini \n";

                        $Email->viewVars(['body_footer' => sprintf(Configure::read('Mail.body_footer_no_reply'), $this->_traslateWww($user->organization['Organization']['www']))]);

                        $data_oggi = date("Y-m-d");
                        $data_oggi_incrementata = date('Y-m-d', strtotime('+'.(Configure::read('GGMailToAlertOrderClose')).' day', strtotime($data_oggi)));

                        /*
                         * ciclo UTENTI
                         */
                        $usersResults = $User->getUsersToMail($organization_id);	

                        foreach($usersResults as $numResult => $usersResult) {

                            $mail = $usersResult['User']['email'];
							$mail2 = $usersResult['UserProfile']['email'];
                            $name = $usersResult['User']['name'];
                            $username = $usersResult['User']['username'];

                            $Email->viewVars(['body_header' => sprintf(Configure::read('Mail.body_header'), $name)]);
                            
                            if($debug)
								echo '<br />'.$numResult.") tratto l'utente ".$name.', username '.$username." \n";

							/*
							 * ordini filtrati per users, ctrl che ci siano ordini da inviare
							 */
							$sql ="SELECT 
										`Order`.*,
										Delivery.*,
										SupplierOrganization.name, SupplierOrganization.frequenza,
										Supplier.descrizione, Supplier.img1   
								   FROM 
										".Configure::read('DB.prefix')."orders `Order`,
										".Configure::read('DB.prefix')."deliveries Delivery,  
										".Configure::read('DB.prefix')."suppliers_organizations SupplierOrganization,  
										".Configure::read('DB.prefix')."suppliers Supplier  
								   WHERE 
										`Order`.organization_id = $organization_id
										and Delivery.organization_id = $organization_id
										and SupplierOrganization.organization_id = $organization_id 
										and `Order`.delivery_id = Delivery.id
										and SupplierOrganization.id = `Order`.supplier_organization_id 
										and Supplier.id = SupplierOrganization.supplier_id 
										and SupplierOrganization.stato = 'Y'
										and SupplierOrganization.mail_order_close = 'Y'
										and `Order`.data_fine = CURDATE() + INTERVAL ".Configure::read('GGMailToAlertOrderClose')." DAY 
										and  `Order`.state_code != 'CREATE-INCOMPLETE' 
										and `Order`.isVisibleFrontEnd = 'Y'  and `Order`.isVisibleFrontEnd = 'Y' 
										and Delivery.isVisibleFrontEnd = 'Y' and Delivery.isVisibleFrontEnd = 'Y'
										
										and `Order`.supplier_organization_id not in (
											SELECT supplier_organization_id 
											FROM ".Configure::read('DB.prefix')."bookmarks_mails where user_id = ".$usersResult['User']['id']." and organization_id = $organization_id and order_close = 'N')
												
										order by Delivery.data, Supplier.name ";
							// self::d($sql, $debug);
							$orderResults = $Order->query($sql);
							
							if(!empty($orderResults)) { 
								if($debug)
									echo "Trovati ".count($orderResults)." ordini per lo user ".$usersResult['User']['username']." (".$usersResult['User']['id'].") \n";
								
								$body_mail = "";
								$delivery_id_old = 0;
								$j_seo = $user->organization['Organization']['j_seo'];    
								
							   /*
								* ciclo sugli ordini dello user 
								* per creare il mailBody
								 */
								foreach ($orderResults as $result) {

										if($delivery_id_old==0 || $delivery_id_old!=$result['Delivery']['id']) {

												if($delivery_id_old > 0) {
														/*
														 * manca lo username crittografato, lo faccio al ciclo degli utenti
														*/
														$url = 'https://www.portalgas.it/home-'.$j_seo.'/preview-carrello-'.$j_seo.'?'.$User->getUrlCartPreviewNoUsername($user, $delivery_id_old);

														$body_mail .= '<div style="clear: both; float: none; margin: 5px 0 15px;">'; 
														$body_mail .= '<img src="https://www.portalgas.it'.Configure::read('App.img.cake').'/cesta-piena.png" title="" border="0" />';
														$body_mail .= ' <a target="_blank" href="'.$url.'">Clicca qui per visualizzare i tuoi <b>acquisti</b> che dovrai ritirare durante la consegna</a>';
														$body_mail .= '</div>'; 
												}

												if($result['Delivery']['sys']=='Y')
														$body_mail .= "<br />\nper una consegna <b>".$result['Delivery']['luogo']."</b><br />\n";						
												else
														$body_mail .= "<br />\nper la consegna di <b>".CakeTime::format($result['Delivery']['data'],"%A %e %B %Y")."</b> a ".$result['Delivery']['luogo']."<br />\n";

												$body_mail .= "si <span style='color:red'>chiudera'</span> tra ".(Configure::read('GGMailToAlertOrderClose')+1)." giorni, ".CakeTime::format($data_oggi_incrementata,"%A %e %B %Y").", il periodo d'ordine nei confronti ";

												if(count($orderResults)==1) {
														$body_mail .= "del seguente produttore: <br />\n<br />\n";
														$subject_mail = $result['SupplierOrganization']['name'].", ordine che si chiuderà tra ".(Configure::read('GGMailToAlertOrderClose')+1)." giorni";
												}
												else {
														$body_mail .= "dei seguenti produttori: <br />\n<br />\n";
														$subject_mail = $this->_organizationNameError($user->organization).", ordini che si chiuderanno tra ".(Configure::read('GGMailToAlertOrderClose')+1)." giorni";
												}
												$Email->subject($subject_mail);												
										}
										
										//$body_mail .= ((int)$numResult+1).") ".$result['SupplierOrganization']['name'];
										$body_mail .= "- ";
										$body_mail .= $result['SupplierOrganization']['name'];
										if(!empty($result['Supplier']['descrizione'])) $body_mail .= "/".$result['Supplier']['descrizione'];
										if(!empty($result['SupplierOrganization']['frequenza'])) $body_mail .= " (frequenza ".$result['SupplierOrganization']['frequenza'].')';

										if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
												$body_mail .= ' <img width="50" src="https://www.portalgas.it'.Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" alt="'.$result['SupplierOrganization']['name'].'" /> ';
										else
											$body_mail .= ' <img width="50" src="https://www.portalgas.it'.Configure::read('App.web.img.upload.content').'/empty.png" alt="'.$result['SupplierOrganization']['name'].'" /> ';

										$body_mail .= "<br />\n";

										$delivery_id_old=$result['Delivery']['id'];
										
								} // loops Orders dello user 
								
								/*
								 * manca lo username crittografato, lo faccio al ciclo degli utenti
								 */
								$url = 'https://www.portalgas.it/home-'.$j_seo.'/preview-carrello-'.$j_seo.'?'.$User->getUrlCartPreviewNoUsername($user, $delivery_id_old);

								$body_mail .= '<div style="clear: both; float: none; margin: 5px 0 15px;">'; 
								$body_mail .= '<img src="https://www.portalgas.it'.Configure::read('App.img.cake').'/cesta-piena.png" title="" border="0" />';
								$body_mail .= ' <a target="_blank" href="'.$url.'">Clicca qui per visualizzare i tuoi <b>acquisti</b> che dovrai ritirare durante la consegna</a>';
								$body_mail .= '</div>'; 
								 
								/*
								 * all'url per il CartPreview aggiungo lo username crittografato
								 */
								$body_mail_final = str_replace("{u}", urlencode($User->getUsernameCrypted($username)), $body_mail);

								if($debug && $numResult==1) echo $body_mail_final."\n"; 

								$mailResults = $Mail->send($Email, [$mail2, $mail], $body_mail_final, false);
							}   
							else { 
							   if($debug)
								   echo "Per lo user ".$usersResult['User']['username']." (".$usersResult['User']['id'].") non ci sono ordini da inviare \n";
							}
							 
                        } // loops users 
                        
                        /*
                         * per gli ordini trovati 
                         * UPDATE Order.mail_open_send, Order.mail_open_data
                         */
                        foreach ($orderCtrlResults as $orderCtrlResult) {
                            $sql ="UPDATE ".Configure::read('DB.prefix')."orders 
                                  SET mail_close_data = '".date('Y-m-d H:i:s')."'
                                  WHERE organization_id = $organization_id and id = ".$orderCtrlResult['Order']['id'];
                            self::d($sql, $debug);
                            $Order->query($sql);
                        }
                        
                    } // end if(!empty($orderCtrlResults))
                    else 
                            echo "non ci sono ordini che apriranno tra ".(Configure::read('GGMailToAlertOrderClose')+1)." giorni \n";
		}
		catch (Exception $e) {
			echo '<br />UtilsCrons::mailUsersOrdersClose()<br />'.$e;
		}			
	}
	
	public $hasMany = [
			'Order' => [
					'className' => 'Order',
					'foreignKey' => 'delivery_id',
					'dependent' => false,
					'conditions' => '',
					'fields' => '',
					'order' => '',
					'limit' => '',
					'offset' => '',
					'exclusive' => '',
					'finderQuery' => '',
					'counterQuery' => ''
			]
	];
}