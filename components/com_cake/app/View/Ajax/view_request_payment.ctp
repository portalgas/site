<?php
$tot_generics = count($results['PaymentsGeneric']);
			if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
				echo '<img width="50" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" />';
			echo '</td>';
			echo '<td>'.$result['SuppliersOrganization']['name'].'</td>';
			if(!empty($result['Order']['tesoriere_doc1']) && file_exists(Configure::read('App.root').Configure::read('App.doc.upload.tesoriere').DS.$user->organization['Organization']['id'].DS.$result['Order']['tesoriere_doc1'])) {
				$ico = $this->App->drawDocumentIco($result['Order']['tesoriere_doc1']);
				echo '<a alt="Scarica il documento" title="Scarica il documento" href="'.Configure::read('App.server').Configure::read('App.web.doc.upload.tesoriere').'/'.$user->organization['Organization']['id'].'/'.$result['Order']['tesoriere_doc1'].'" target="_blank"><img src="'.$ico.'" /></a>';
			}
			else
				echo "";
				
			if($result['Order']['tesoriere_fattura_importo']>0)
				echo '<br />'.number_format($result['Order']['tesoriere_fattura_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).' &euro;';
			echo '</td>';