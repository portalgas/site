<?php$this->App->d($promotionResults, $debug);$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);$this->Html->addCrumb(__('List ProdGasPromotions New'), array('controller' => 'ProdGasPromotionsOrganizationsManagers', 'action' => 'index_new'));$this->Html->addCrumb(__('Contact PromotionOrganizationManager'));echo $this->Html->getCrumbList(array('class'=>'crumbs'));echo '<div class="promotion form" style="padding:0 15px 0 15px;">';echo $this->element('boxProdGasPromotion', ['results' => $promotionResults, 'prodGasArticlesPromotionShow' => true]);echo $this->Html->div('clearfix','');echo $this->element('boxMsg', ['class_msg' => 'info', 'msg' => __('msg_prodgas_contact_to_gas')]);echo '<div class="input text ">';echo '<label for="Name">'.__('Name').'</label> ';echo $promotionResults['ProdGasPromotion']['contact_name'];echo '</div>';echo '<div class="input text ">';echo '<label for="Name">'.__('Email').'</label> ';if(!empty($promotionResults['ProdGasPromotion']['contact_mail']))	echo '<a href="'.$promotionResults['ProdGasPromotion']['contact_mail'].'">'.$promotionResults['ProdGasPromotion']['contact_mail'].'</a>';echo '</div>';echo '<div class="input text ">';echo '<label for="Name">'.__('Telephone').'</label> ';echo $promotionResults['ProdGasPromotion']['contact_phone'];echo '</div>';echo '</div>';?><div class="actions">	<h3><?php echo __('Actions'); ?></h3>	<ul>		<li><?php echo $this->Html->link(__('List ProdGasPromotions New'), array('controller' => 'ProdGasPromotionsOrganizationsManagers', 'action' => 'index_new'),array('class'=>'action actionReload'));?></li>	</ul></div>