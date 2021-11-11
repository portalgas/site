<?php
App::uses('AppModel', 'Model');

class StatoElaborazione extends AppModel {

	public $useTable = false;

   public function prodDeliveries($user, $debug = true, $prod_delivery_id = 0) {
        $debugSql = false;

        try {
            App::import('Model', 'ProdDelivery');
            $ProdDelivery = new ProdDelivery;

            /*
             * cron: consegne senza articoli associati (ProdDeliveriesArticle) in CREATE-INCOMPLETE
             */
            self::d(date("d/m/Y") . " - " . date("H:i:s") . " Porto le consegne senza articoli associati (ProdDeliveriesArticle) in CREATE-INCOMPLETE", $debug);
            $sql = "SELECT
						ProdDelivery.id
				   FROM
						" . Configure::read('DB.prefix') . "prod_deliveries ProdDelivery LEFT JOIN
						 " . Configure::read('DB.prefix') . "prod_deliveries_articles ProdDeliveriesArticle ON
						 		(ProdDeliveriesArticle.prod_delivery_id = ProdDelivery.id
						 		and ProdDeliveriesArticle.organization_id = " . (int) $user->organization['Organization']['id'] . ")
				   WHERE
						ProdDelivery.organization_id = " . (int) $user->organization['Organization']['id'] . "
						AND (ProdDelivery.prod_delivery_state_id != " . Configure::read('CREATE-INCOMPLETE') . " and ProdDelivery.prod_delivery_state_id != " . Configure::read('CLOSE') . ")
						AND ProdDeliveriesArticle.article_id IS NULL AND ProdDeliveriesArticle.prod_delivery_id IS NULL ";
            if (!empty($prod_delivery_id))
                $sql .= " AND ProdDelivery.id = " . (int) $prod_delivery_id;
            $sql .= " GROUP BY ProdDelivery.id";
            self::d($sql, $debugSql);
            $results = $ProdDelivery->query($sql);
            self::d("Aggiornati: " . count($results), $debug);
            foreach ($results as $result) {
                $sql = "UPDATE " . Configure::read('DB.prefix') . "prod_deliveries 
					   SET
							prod_delivery_state_id = " . Configure::read('CREATE-INCOMPLETE') . ",
							modified = '" . date('Y-m-d H:i:s') . "'
					   WHERE
					   		organization_id = " . (int) $user->organization['Organization']['id'] . "
					   		and id = " . $result['ProdDelivery']['id'];
                if ($debugSql)
                    echo $sql . "\n";
                $ProdDelivery->query($sql);
            }

            /*
             * cron: consegne con articoli associati (ProdDeliveriesArticle) da CREATE-INCOMPLETE a OPEN-NEXT o OPEN
             * 		ProdDeliveriesArticle::add
             */
            self::d("Porto le consegne con articoli associati (ProdDeliveriesArticle) da CREATE-INCOMPLETE a OPEN-NEXT o OPEN (ProdDeliveriesArticle::add)", $debug);
            $sql = "SELECT
						ProdDelivery.id
				   FROM
						" . Configure::read('DB.prefix') . "prod_deliveries ProdDelivery,
						 " . Configure::read('DB.prefix') . "prod_deliveries_articles ProdDeliveriesArticle
				   WHERE
						ProdDelivery.organization_id = " . (int) $user->organization['Organization']['id'] . "
						AND ProdDeliveriesArticle.organization_id = " . (int) $user->organization['Organization']['id'] . "
						AND ProdDeliveriesArticle.prod_delivery_id = ProdDelivery.id
						AND ProdDelivery.prod_delivery_state_id = " . Configure::read('CREATE-INCOMPLETE');
            if (!empty($prod_delivery_id))
                $sql .= " AND ProdDelivery.id = " . (int) $prod_delivery_id;
            $sql .= " group by ProdDelivery.id";
            self::d($sql, $debugSql);
            $results = $ProdDelivery->query($sql);
            self::d("Aggiornati: " . count($results), $debug);
            foreach ($results as $result) {
                /*
                 * calcolo se OPEN-NEXT o OPEN
                 */
                $data_oggi = date("Y-m-d");
                if ($results['ProdDelivery']['data_inizio'] > $data_oggi)
                    $prod_delivery_state_id = Configure::read('OPEN-NEXT');
                else
                    $prod_delivery_state_id = Configure::read('OPEN');

                $sql = "UPDATE " . Configure::read('DB.prefix') . "prod_deliveries
						SET
							prod_delivery_state_id = $prod_delivery_state_id,
							modified = '" . date('Y-m-d H:i:s') . "'
						WHERE
					   		organization_id = " . (int) $user->organization['Organization']['id'] . "
							and id = " . $result['ProdDelivery']['id'];
                if ($debugSql)
                    echo $sql . "\n";
                $ProdDelivery->query($sql);
            }

            /*
             * cron: orders in OPEN-NEXT
             * 	estraggo gli ordini che si aprono successivamente
             */
            self::d("Porto gli ordini a OPEN-NEXT per quelli che devono ancora aprirsi", $debug);
            $sql = "SELECT
						count(ProdDelivery.id) as totale
				   FROM
						" . Configure::read('DB.prefix') . "prod_deliveries as ProdDelivery
				   WHERE
				   		ProdDelivery.organization_id = " . (int) $user->organization['Organization']['id'] . "
				   		AND (ProdDelivery.prod_delivery_state_id != " . Configure::read('CREATE-INCOMPLETE') . "
				   			AND ProdDelivery.prod_delivery_state_id != " . Configure::read('OPEN-NEXT') . "
				   			AND ProdDelivery.prod_delivery_state_id != " . Configure::read('CLOSE') . "
				   			)
				   		AND ProdDelivery.data_inizio > CURDATE() ";  // data_inizio successiva ad oggi
            if (!empty($prod_delivery_id))
                $sql .= " AND id = " . (int) $prod_delivery_id;
            if ($debugSql)
                echo $sql . "\n";
            $results = current($ProdDelivery->query($sql));
            $sql = "UPDATE
						" . Configure::read('DB.prefix') . "prod_deliveries
				   SET
						prod_delivery_state_id = " . Configure::read('OPEN-NEXT') . ",
						modified = '" . date('Y-m-d H:i:s') . "'
				   WHERE
						organization_id = " . (int) $user->organization['Organization']['id'] . "
				   		AND (prod_delivery_state_id != " . Configure::read('CREATE-INCOMPLETE') . "
				   			AND prod_delivery_state_id != " . Configure::read('OPEN-NEXT') . "
				   			AND prod_delivery_state_id != " . Configure::read('CLOSE') . "
				   			)
				   		AND data_inizio > CURDATE() ";  // data_inizio successiva ad oggi
            if (!empty($prod_delivery_id))
                $sql .= " AND id = " . (int) $prod_delivery_id;
            self::d($sql, $debugSql);
            self::d("Aggiornati: " . $results[0]['totale'], $debug);
            $ProdDelivery->query($sql);


            /*
             * cron: consegne da OPEN-NEXT a OPEN
             * 	estraggo le consegne che si aprono oggi (o dovrebbero essere gia' aperti!)
             */
            self::d("Porto gli ordini da OPEN-NEXT a OPEN: estraggo gli ordini che si aprono oggi (o dovrebbero essere gia' aperti!)", $debug);
            $sql = "SELECT count(id) as totale
				   FROM " . Configure::read('DB.prefix') . "prod_deliveries as ProdDelivery
				   WHERE
				   		organization_id = " . (int) $user->organization['Organization']['id'] . "
				   		and prod_delivery_state_id = " . Configure::read('OPEN-NEXT') . "
				   		and data_inizio <= CURDATE()"; // data_inizio precedente o uguale ad oggi
            if ($debugSql)
                echo $sql . "\n";
            $results = current($ProdDelivery->query($sql));
            $sql = "UPDATE " . Configure::read('DB.prefix') . "prod_deliveries
				   SET
						prod_delivery_state_id = " . Configure::read('OPEN') . ",
						modified = '" . date('Y-m-d H:i:s') . "'
				   WHERE
				   		organization_id = " . (int) $user->organization['Organization']['id'] . "
				   		and prod_delivery_state_id = " . Configure::read('OPEN-NEXT') . "
				   		and data_inizio <= CURDATE() ";  // data_inizio precedente o uguale ad oggi
            if (!empty($prod_delivery_id))
                $sql .= " AND id = " . (int) $prod_delivery_id;
            self::d($sql, $debugSql);
            self::d("Aggiornati: " . $results[0]['totale'], $debug);
            $ProdDelivery->query($sql);

            /*
             * cron: consegne da OPEN a PROCESSED-POST-DELIVERY
             * 	estraggo le consegne aperte che devono chiudersi
             */
            self::d("Porto le consegne da OPEN a PROCESSED-POST-DELIVERY: estraggo le consegne aperte che devono chiudersi", $debug);

            $sql = "UPDATE
						" . Configure::read('DB.prefix') . "prod_deliveries as ProdDelivery
				   SET
						prod_delivery_state_id = " . Configure::read('PROCESSED-POST-DELIVERY') . ",
						modified = '" . date('Y-m-d H:i:s') . "'
				   WHERE
				   		organization_id = " . (int) $user->organization['Organization']['id'] . "
				   		and ProdDelivery.stato_elaborazione = 'OPEN'
				   		and ProdDelivery.prod_delivery_state_id = " . Configure::read('OPEN') . "
				   		and ProdDelivery.data_fine < CURDATE()";
            if (!empty($prod_delivery_id))
                $sql .= " AND ProdDelivery.id = " . (int) $prod_delivery_id;
            if ($debugSql)
                echo $sql . "\n";
            $ProdDelivery->query($sql);
        } catch (Exception $e) {
            self::d(UtilsCrons::prodDeliveriesStatoElaborazione() => StatoElaborazione::prodDeliveries() '.$sql.' '.$e, $debug);
        }
    }
	 
	/* 
	 * utilizzata
	 */ 
	public function requestProdDeliveries($user, $debug, $prod_delivery_id) {
        $debugSql = false;

        try {
            App::import('Model', 'ProdDelivery');
            $ProdDelivery = new ProdDelivery;

            /*
             * cron: consegne senza articoli associati (ProdDeliveriesArticle) in CREATE-INCOMPLETE
             */
            self::d(date("d/m/Y") . " - " . date("H:i:s") . " Porto le consegne senza articoli associati (ProdDeliveriesArticle) in CREATE-INCOMPLETE", $debug);
            $sql = "SELECT
						ProdDelivery.id
				   FROM
						" . Configure::read('DB.prefix') . "prod_deliveries ProdDelivery LEFT JOIN
						 " . Configure::read('DB.prefix') . "prod_deliveries_articles ProdDeliveriesArticle ON
						 		(ProdDeliveriesArticle.prod_delivery_id = ProdDelivery.id
						 		and ProdDeliveriesArticle.organization_id = " . (int) $user->organization['Organization']['id'] . ")
				   WHERE
						ProdDelivery.organization_id = " . (int) $user->organization['Organization']['id'] . "
						AND ProdDelivery.prod_delivery_state_id NOT IN ('CREATE-INCOMPLETE','CLOSE')
						AND ProdDeliveriesArticle.article_id IS NULL AND ProdDeliveriesArticle.prod_delivery_id IS NULL ";
            if (!empty($prod_delivery_id))
                $sql .= " AND ProdDelivery.id = " . (int) $prod_delivery_id;
            $sql .= " GROUP BY ProdDelivery.id";
            self::d($sql, debugSql);
            $results = $ProdDelivery->query($sql);
            self::d("Aggiornati: " . count($results), $debug);
            foreach ($results as $result) {
                $sql = "UPDATE " . Configure::read('DB.prefix') . "prod_deliveries 
					   SET
							prod_delivery_state_id = 'CREATE-INCOMPLETE' ,
							modified = '" . date('Y-m-d H:i:s') . "'
					   WHERE
					   		organization_id = " . (int) $user->organization['Organization']['id'] . "
					   		and id = " . $result['ProdDelivery']['id'];
                if ($debugSql)
                    echo $sql . "\n";
                $ProdDelivery->query($sql);
            }

            /*
             * cron: consegne con articoli associati (ProdDeliveriesArticle) da CREATE-INCOMPLETE a OPEN-NEXT o OPEN
             * 		ProdDeliveriesArticle::add
             */
            self::d("Porto le consegne con articoli associati (ProdDeliveriesArticle) da CREATE-INCOMPLETE a OPEN-NEXT o OPEN (ProdDeliveriesArticle::add)", $debug);
            $sql = "SELECT
						ProdDelivery.id
				   FROM
						" . Configure::read('DB.prefix') . "prod_deliveries ProdDelivery,
						 " . Configure::read('DB.prefix') . "prod_deliveries_articles ProdDeliveriesArticle
				   WHERE
						ProdDelivery.organization_id = " . (int) $user->organization['Organization']['id'] . "
						AND ProdDeliveriesArticle.organization_id = " . (int) $user->organization['Organization']['id'] . "
						AND ProdDeliveriesArticle.prod_delivery_id = ProdDelivery.id
						AND ProdDelivery.prod_delivery_state_id = 'CREATE-INCOMPLETE' ";
            if (!empty($prod_delivery_id))
                $sql .= " AND ProdDelivery.id = " . (int) $prod_delivery_id;
            $sql .= " group by ProdDelivery.id";
            if ($debugSql)
                echo $sql . "\n";
            $results = $ProdDelivery->query($sql);
            self::d("Aggiornati: " . count($results), $debug);
            foreach ($results as $result) {
                /*
                 * calcolo se OPEN-NEXT o OPEN
                 */
                $data_oggi = date("Y-m-d");
                if ($results['ProdDelivery']['data_inizio'] > $data_oggi)
                    $prod_delivery_state_id = 'OPEN-NEXT';
                else
                    $prod_delivery_state_id = 'OPEN';

                $sql = "UPDATE " . Configure::read('DB.prefix') . "prod_deliveries
						SET
							prod_delivery_state_id = $prod_delivery_state_id,
							modified = '" . date('Y-m-d H:i:s') . "'
						WHERE
					   		organization_id = " . (int) $user->organization['Organization']['id'] . "
							and id = " . $result['ProdDelivery']['id'];
                if ($debugSql)
                    echo $sql . "\n";
                $ProdDelivery->query($sql);
            }

            /*
             * cron: orders in OPEN-NEXT
             * 	estraggo gli ordini che si aprono successivamente
             */
            self::d("Porto gli ordini a OPEN-NEXT per quelli che devono ancora aprirsi", $debug);
            $sql = "SELECT
						count(ProdDelivery.id) as totale
				   FROM
						" . Configure::read('DB.prefix') . "prod_deliveries as ProdDelivery
				   WHERE
				   		ProdDelivery.organization_id = " . (int) $user->organization['Organization']['id'] . "
				   		AND ProdDelivery.prod_delivery_state_id NOT IN ('CREATE-INCOMPLETE','OPEN-NEXT','CLOSE')
				   		AND ProdDelivery.data_inizio > CURDATE() ";  // data_inizio successiva ad oggi
            if (!empty($prod_delivery_id))
                $sql .= " AND id = " . (int) $prod_delivery_id;
            if ($debugSql)
                echo $sql . "\n";
            $results = current($ProdDelivery->query($sql));
            $sql = "UPDATE ".Configure::read('DB.prefix')."prod_deliveries
				   SET
						prod_delivery_state_id = 'OPEN-NEXT', modified = '" . date('Y-m-d H:i:s') . "'
				   WHERE
						organization_id = " . (int) $user->organization['Organization']['id'] . "
				   		AND prod_delivery_state_id NOT IN ('CREATE-INCOMPLETE','OPEN-NEXT','CLOSE')
				   		AND data_inizio > CURDATE() ";  // data_inizio successiva ad oggi
            if (!empty($prod_delivery_id))
                $sql .= " AND id = " . (int) $prod_delivery_id;
            self::d($sql, $debugSql);
            self::d("Aggiornati: " . $results[0]['totale'], $debug);
            $ProdDelivery->query($sql);


            /*
             * cron: consegne da OPEN-NEXT a OPEN
             * 	estraggo le consegne che si aprono oggi (o dovrebbero essere gia' aperti!)
             */
            self::d("Porto gli ordini da OPEN-NEXT a OPEN: estraggo gli ordini che si aprono oggi (o dovrebbero essere gia' aperti!)", $debug);
            $sql = "SELECT count(id) as totale FROM " . Configure::read('DB.prefix') . "prod_deliveries as ProdDelivery
				   WHERE
				   		organization_id = " . (int) $user->organization['Organization']['id'] . "
				   		and prod_delivery_state_id = 'OPEN-NEXT' 
				   		and data_inizio <= CURDATE()"; // data_inizio precedente o uguale ad oggi
            if ($debugSql)
                echo $sql . "\n";
            $results = current($ProdDelivery->query($sql));
            $sql = "UPDATE " . Configure::read('DB.prefix') . "prod_deliveries
				   SET prod_delivery_state_id = 'OPEN', modified = '" . date('Y-m-d H:i:s') . "'
				   WHERE
				   		organization_id = " . (int) $user->organization['Organization']['id'] . "
				   		and prod_delivery_state_id = 'OPEN-NEXT' 
				   		and data_inizio <= CURDATE() ";  // data_inizio precedente o uguale ad oggi
            if (!empty($prod_delivery_id))
                $sql .= " AND id = " . (int) $prod_delivery_id;
            self::d($sql, $debugSql);
            self::d("Aggiornati: " . $results[0]['totale'], $debug);
            $ProdDelivery->query($sql);

            /*
             * cron: consegne da OPEN a PROCESSED-POST-DELIVERY
             * 	estraggo le consegne aperte che devono chiudersi
             */
            self::d("Porto le consegne da OPEN a PROCESSED-POST-DELIVERY: estraggo le consegne aperte che devono chiudersi", $debug);

            $sql = "UPDATE
						" . Configure::read('DB.prefix') . "prod_deliveries as ProdDelivery
				   SET
						prod_delivery_state_id = 'PROCESSED-POST-DELIVERY', modified = '" . date('Y-m-d H:i:s') . "'
				   WHERE
				   		organization_id = " . (int) $user->organization['Organization']['id'] . "
				   		and ProdDelivery.stato_elaborazione = 'OPEN'
				   		and ProdDelivery.prod_delivery_state_id = 'OPEN' 
				   		and ProdDelivery.data_fine < CURDATE()";
            if (!empty($prod_delivery_id))
                $sql .= " AND ProdDelivery.id = " . (int) $prod_delivery_id;
            if ($debugSql)
                echo $sql . "\n";
            $ProdDelivery->query($sql);
        } catch (Exception $e) {
            self::d('UtilsCrons::requestProdDeliveriesStatoElaborazione() => StatoElaborazione::requestProdDeliveries() '.$sql.' '.$e, $debug);
        }				
	}
}