<?php
App::uses('AppModel', 'Model');
App::uses('TimeHelper', 'View/Helper');

class Rss extends AppModel {

	public $useTable = false;
	
	/*
	 * richiamato dal Cron
	 *
     * per ogni organization scrive un file seo.rss in /rss/
     */	
	public function cronElabora($user, $timeHelper, $debug) {
        /*
         * DATE_RFC850 Thursday, 30-Apr-15 13:03:33 GMT
         * DATE_RFC822 Thu, 30 Apr 15 13:04:48 +0000
         */
        $formatDate = DATE_RFC822;

        $j_seo = $user->organization['Organization']['j_seo'];
        if(empty($j_seo))
        	return;
        	
        $fileName1 = $j_seo . '.rss';
        $fileName2 = $j_seo . '2.rss';
        $fileName3 = $j_seo . '-gcalendar.rss';
        $link = 'https://www.portalgas.it/home-' . $j_seo . '/consegne-' . $j_seo;

        /*
         * data nel formato Wed, 02 Oct 2002 08:00:00 EST
         */
        $d = date('Y-m-d H:i:s T', time());
        $date = gmdate($formatDate, strtotime($d));

        $rssHeader = '';
        $rssHeader .= '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
        $rssHeader .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
        $rssHeader .= '<channel>' . "\n";
        $rssHeader .= '<atom:link href="https://www.portalgas.it/rss/' . $j_seo . '.rss" type="application/rss+xml" />' . "\n";
        $rssHeader .= '<title>Ordini del G.A.S. ' . $this->_organizationNameError($user->organization) . '</title>' . "\n";
        $rssHeader .= '<link>https://www.portalgas.it</link>' . "\n";
        $rssHeader .= '<description>Gestionale web per G.A.S. (GAS gruppo d\'acquisto solidale)</description>' . "\n";
        $rssHeader .= '<pubDate>' . $date . '</pubDate>' . "\n";
        $rssHeader .= '<lastBuildDate>' . $date . '</lastBuildDate>' . "\n";
        $rssHeader .= '<copyright>Copyright 2012 - ' . date('Y') . ' - portalgas.it</copyright>' . "\n";

        if (!empty($user->organization['Organization']['img1'])) {
            $rssHeader .= '<image>' . "\n";
            $rssHeader .= '<url>https://www.portalgas.it' . Configure::read('App.web.img.upload.content') . '/' . $user->organization['Organization']['img1'] . '</url>' . "\n";
            $rssHeader .= '<link>https://www.portalgas.it</link>' . "\n";
            $rssHeader .= '<title>Ordini del G.A.S. ' . $this->_organizationNameError($user->organization) . '</title>' . "\n";
            $rssHeader .= '</image>' . "\n";
        }

        App::import('Model', 'Order');
        $Order = new Order;

        App::import('Model', 'Supplier');

        $options = [];
        $options['conditions'] = ['Delivery.organization_id' => $user->organization['Organization']['id'],
					            'Order.organization_id' => $user->organization['Organization']['id'],
					            'Delivery.isVisibleBackOffice' => 'Y',
					            'Delivery.isVisibleFrontEnd' => 'Y',
					            'DATE(Delivery.data) >= CURDATE()',
					            'Delivery.stato_elaborazione' => 'OPEN',
					            'Order.state_code != ' => 'CREATE-INCOMPLETE'];
        $options['recursive'] = 0;
        $options['order'] = ['Delivery.data asc', 'Order.data_inizio'];
        $results = $Order->find('all', $options);
        
        self::d($options, $debug);
        // self::d($results, $debug);

        $rssItems1 = '';
        $rssItems2 = '';
        $rssItems3 = '';
        foreach ($results as $numResult => $result) {


            $guid = 'https://www.portalgas.it/' . $j_seo . '-' . $result['Order']['id'];

            /*
             * titolo uno: Il Girasole - Ordine aperto dal 1 febbraio al 7 febbraio - Consegna 10 febbraio
             */
            $delivery = '';
            $title1 = '';
            if ($result['Delivery']['sys'] == 'Y') {
                $delivery .= "Consegna " . $result['Delivery']['luogo'];
				$d = date('Y-m-d', time());
            }
			else {
                $delivery .= $timeHelper->i18nFormat($result['Delivery']['data'], "%A %e %B");
				$d = date($result['Delivery']['data'], time());
			}
			
			/*
			 * data nel formato Wed, 02 Oct 2002 08:00:00 GMT
			 */
			$date = gmdate($formatDate, strtotime($d));	
			$order_data_fine = gmdate($formatDate, strtotime($result['Order']['data_fine']));
			
            $title1 = $result['SuppliersOrganization']['name'] . ', ordine aperto fino a ' . $timeHelper->i18nFormat($result['Order']['data_fine'], "%A %e %B") . ' - Consegna ';
            if ($result['Delivery']['sys'] == 'Y')
                $title1 .= $result['Delivery']['luogo'];
            else
                $title1 .= $timeHelper->i18nFormat($result['Delivery']['data'], "%A %e %B");

            $rssItems1 .= '<item>' . "\n";
            $rssItems1 .= '<guid>' . $guid . '</guid>' . "\n";
            $rssItems1 .= '<category ><![CDATA[' . $this->_pulisciStringaRss($delivery) . ']]></category >' . "\n";
            $rssItems1 .= '<title><![CDATA[' . $this->_pulisciStringaRss($title1) . ']]></title>' . "\n";
            $rssItems1 .= '<link>' . $link . '</link>' . "\n";
            $rssItems1 .= '<pubDate>' . $date . '</pubDate>' . "\n";
            if (!empty($result['Order']['nota']))
                $rssItems1 .= '<description><![CDATA[' . $this->_pulisciStringaRss($result['Order']['nota']) . ']]></description>' . "\n";
			
			$rssItems1 .= '<orderDateFine><![CDATA[' . $order_data_fine . ']]></orderDateFine>' . "\n";
            $rssItems1 .= '</item>' . "\n";


            /*
             * titolo due
             */
            $delivery = '';
            $title2 = '';
            if ($result['Delivery']['sys'] == 'Y')
                $delivery .= "Consegna " . $result['Delivery']['luogo'];
            else
                $delivery .= ucfirst($timeHelper->i18nFormat($result['Delivery']['data'], "%A %e %B"));

            $title2 .= $delivery . ' - ' . $result['SuppliersOrganization']['name'];

            $title2 .= ", ordine aperto fino a " . $timeHelper->i18nFormat($result['Order']['data_fine'], "%A %e %B");
            if ($result['Delivery']['sys'] == 'N')
                $title2 .= ", " . $result['Delivery']['luogo'];

            $rssItems2 .= '<item>' . "\n";
            $rssItems2 .= '<guid>' . $guid . '</guid>' . "\n";
            $rssItems2 .= '<category ><![CDATA[' . $this->_pulisciStringaRss($delivery) . ']]></category >' . "\n";
            $rssItems2 .= '<title><![CDATA[' . $this->_pulisciStringaRss($title2) . ']]></title>' . "\n";
            $rssItems2 .= '<link>' . $link . '</link>' . "\n";
            $rssItems2 .= '<pubDate>' . $date . '</pubDate>' . "\n";
            if (!empty($result['Order']['nota']))
                $rssItems2 .= '<description><![CDATA[' . $this->_pulisciStringaRss($result['Order']['nota']) . ']]></description>' . "\n";

			$rssItems2 .= '<orderDateFine><![CDATA[' . $order_data_fine . ']]></orderDateFine>' . "\n";
            $rssItems2 .= '</item>' . "\n";


            /*
             * titolo tre: 
             * 		una riga con Order.data_inizio
             * 		una riga con Order.data_fine
             */
            $data_inizio = date($result['Order']['data_inizio'], time());
            $data_inizio = gmdate($formatDate, strtotime($data_inizio));

            $data_fine = date($result['Order']['data_fine'], time());
            $data_fine = gmdate($formatDate, strtotime($data_fine));

            $delivery = '';
            $title_inizio = '';
            if ($result['Delivery']['sys'] == 'Y')
                $delivery .= "Consegna " . $result['Delivery']['luogo'];
            else
                $delivery .= $timeHelper->i18nFormat($result['Delivery']['data'], "%A %e %B");


            /*
             * Order.data inizio
             */
            $guid = $guid . '-inizio';

            $title_inizio = "Apertura ordine " . $result['SuppliersOrganization']['name'] . ' - Consegna ';
            if ($result['Delivery']['sys'] == 'Y')
                $title_inizio .= $result['Delivery']['luogo'];
            else
                $title_inizio .= $timeHelper->i18nFormat($result['Delivery']['data'], "%A %e %B");

            $rssItems3 .= '<item>' . "\n";
            $rssItems3 .= '<guid>' . $guid . '</guid>' . "\n";
            $rssItems3 .= '<category><![CDATA[' . $this->_pulisciStringaRss($delivery) . ']]></category >' . "\n";
            $rssItems3 .= '<title><![CDATA[' . $this->_pulisciStringaRss($title_inizio) . ']]></title>' . "\n";
            $rssItems3 .= '<link>' . $link . '</link>' . "\n";
            $rssItems3 .= '<pubDate>' . $data_inizio . '</pubDate>' . "\n";
            $rssItems3 .= '<description><![CDATA[Ordine aperto fino a ' . $timeHelper->i18nFormat($result['Order']['data_fine'], "%A %e %B") . ']]></description>' . "\n";
			//$rssItems3 .= '<orderDateFine><![CDATA[' . $order_data_fine . ']]></orderDateFine>' . "\n";
            $rssItems3 .= '</item>' . "\n";
            /*
             * Order.data fine
             */
            $guid = $guid . '-fine';

            $title_fine = "Chiusura ordine " . $result['SuppliersOrganization']['name'] . ' - Consegna ';
            if ($result['Delivery']['sys'] == 'Y')
                $title_fine .= $result['Delivery']['luogo'];
            else
                $title_fine .= $timeHelper->i18nFormat($result['Delivery']['data'], "%A %e %B");

            $rssItems3 .= '<item>' . "\n";
            $rssItems3 .= '<guid>' . $guid . '</guid>' . "\n";
            $rssItems3 .= '<category><![CDATA[' . $this->_pulisciStringaRss($delivery) . ']]></category >' . "\n";
            $rssItems3 .= '<title><![CDATA[' . $this->_pulisciStringaRss($title_fine) . ']]></title>' . "\n";
            $rssItems3 .= '<link>' . $link . '</link>' . "\n";
            $rssItems3 .= '<pubDate>' . $data_fine . '</pubDate>' . "\n";
            $rssItems3 .= '</item>' . "\n";
        } // end loop items

        $rssFooter .= '</channel>' . "\n";
        $rssFooter .= '</rss>';

        echo date("d/m/Y") . " - " . date("H:i:s") . " " . Configure::read('App.root') . DS . 'rss' . DS . $fileName1 . "\n";
        echo date("d/m/Y") . " - " . date("H:i:s") . " " . Configure::read('App.root') . DS . 'rss' . DS . $fileName2 . "\n";
        echo date("d/m/Y") . " - " . date("H:i:s") . " " . Configure::read('App.root') . DS . 'rss' . DS . $fileName3 . "\n";

        $rss1 = trim($rssHeader . $rssItems1 . $rssFooter);
        $rss2 = trim($rssHeader . $rssItems2 . $rssFooter);
        $rss3 = trim($rssHeader . $rssItems3 . $rssFooter);

        /*
        if ($debug) {
            echo "<code>";
			self::d($rss1, $debug);
			self::d($rss2, $debug);
			self::d($rss3, $debug);
            echo "</code>";
        }
		*/
		
		self::d('write '.Configure::read('App.root') . DS . 'rss' . DS . $fileName1);
        $file1 = new File(Configure::read('App.root') . DS . 'rss' . DS . $fileName1, true);
        if(!$file1->write($rss1)) 
        	self::d('Errore write '.Configure::read('App.root') . DS . 'rss' . DS . $fileName1);
        else
        	self::d('OK write '.Configure::read('App.root') . DS . 'rss' . DS . $fileName1);
		
		$file2 = new File(Configure::read('App.root') . DS . 'rss' . DS . $fileName2, true);
        if(!$file2->write($rss2)) 
        	self::d('Errore write '.Configure::read('App.root') . DS . 'rss' . DS . $fileName2);
        else
        	self::d('OK write '.Configure::read('App.root') . DS . 'rss' . DS . $fileName2);
      
		self::d('write '.Configure::read('App.root') . DS . 'rss' . DS . $fileName3);
        $file3 = new File(Configure::read('App.root') . DS . 'rss' . DS . $fileName3, true);
        if(!$file3->write($rss3)) 
        	self::d('Errore write '.Configure::read('App.root') . DS . 'rss' . DS . $fileName3);
        else
        	self::d('OK write '.Configure::read('App.root') . DS . 'rss' . DS . $fileName3);
	}
	 	
    private function _pulisciStringaRss($str) {

        $str = strip_tags($str);
        //$str = utf8_encode(htmlentities($str,ENT_COMPAT,'utf-8'));
        //$str = htmlspecialchars($str, ENT_QUOTES);
        $str = html_entity_decode($str);  // to &agrave; to ...
        $str = str_replace("&amp;", "", $str);

        return $str;
    }    	
}