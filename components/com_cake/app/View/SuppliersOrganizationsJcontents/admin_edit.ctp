<?php
			echo '<label>';
			echo '<img width="150" class="print_screen" id="print_screen_supplier_article" src="'.Configure::read('App.img.cake').'/print_screen_supplier_article.jpg" title="" border="0" />';
			echo '</label>';
			echo $this->Form->input('fulltext', array('id' => 'fulltext', 'label' => false, 'value' => $results['SuppliersOrganizationsJcontent']['text']));
			echo '</div>';
			echo '<label>';
			echo '</label>';
echo $this->Form->submit(__('Exit'),  array('id' => 'action_exit', 'class' => 'buttonBlu', 'div'=> 'submitMultiple'));
echo $this->Form->submit(__('Submit'),array('div'=> 'submitMultiple', 'class' => 'afterDisabled'));