<?phpecho '<div class="legenda">';echo '<div class="table-responsive"><table class="table">';echo '<tr>';echo '<th>'.__('Delivery').'</th>';echo '<th colspan="2">'.__('Supplier').'</th>';echo '<th>'.__('Order').'</th>';echo '<th>Aperto/Chiuso</th>';if($user->organization['Organization']['hasVisibility']=='Y') 	echo '<th>'.__('isVisibleFrontEnd').'</th>';echo '</tr>';echo '<tr class="view">';echo '<td>';if($order['Delivery']['sys']=='N')	echo $order['Delivery']['luogoData'];else 	echo $order['Delivery']['luogo'];echo '</td>';echo '<td>';if(!empty($order['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$order['Supplier']['img1']))	echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$order['Supplier']['img1'].'" />';	echo '</td>';echo '<td>';echo $order['SuppliersOrganization']['name'];echo '</td>';echo '<td>';echo $order['Order']['name'];echo '</td>';echo '<td>';echo $this->App->utilsCommons->getOrderTime($order['Order']);echo '</td>';if($user->organization['Organization']['hasVisibility']=='Y')	echo '<td title="'.__('toolTipIsVisibleFrontEnd').'" class="stato_'.$this->App->traslateEnum($order['Order']['isVisibleFrontEnd']).'"></td>';echo '</tr>';echo '</table></div>';echo '</div>';?>