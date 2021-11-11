<?php
$this->App->d($results);

echo $this->Html->script('moduleUsers-v02.min');

echo '<div class="users">';
echo '<h2 class="ico-calendar">';
echo __('Users Date');
echo '</h2>';

echo $this->Form->create('Filteruser', ['id' => 'formGasFilter', 'type' => 'get']);
echo '<fieldset class="filter">';
echo '<legend>'.__('Filter Users').'</legend>';
echo '<div class="table-responsive"><table class="table">';
echo '<tr>';
echo '<td>';
echo $this->Ajax->autoComplete('FilterUserUsername', Configure::read('App.server') . '/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteUsers_username&format=notmpl', array('label' => 'Username', 'name' => 'FilterUserUsername', 'value' => $FilterUserUsername, 'size' => '50', 'escape' => false));
echo '</td>';
echo '<td>';
echo $this->Ajax->autoComplete('FilterUserName', Configure::read('App.server') . '/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteUsers_name&format=notmpl', array('label' => 'Nominativo', 'name' => 'FilterUserName', 'value' => $FilterUserName, 'size' => '50', 'escape' => false));
echo '</td>';
echo '<td>';
echo $this->Form->input('block', array('label' => __('Stato'), 'options' => $block, 'name' => 'FilterUserBlock', 'default' => $FilterUserBlock, 'escape' => false)); 
echo '</td>';	
echo '<td>';
echo $this->Form->input('sort', array('label' => __('Sort'), 'options' => $sorts, 'name' => 'FilterUserSort', 'default' => $FilterUserSort, 'escape' => false));  
echo '</td>';					
echo '<td>';
echo $this->Form->end(array('label' => __('Filter'), 'class' => 'filter', 'class' => 'filter', 'div' => array('class' => 'submit filter', 'style' => 'display:none'))); 
echo '</td>';
echo '</tr>	';
echo '</table></div>';
echo '</fieldset>';
// echo $this->Form->end(); se chiudo non funziona Ajax->autoComplete!

if (!empty($results)) {

    /*
     * creo un altro form e no quello dei filtri passa tutti i campi sottostante 
     */
    echo $this->Form->create('User');
    ?>
        <div class="table-responsive"><table class="table table-hover">
            <tr>
                <th></th>
                <th></th>
                <th></th>
                <th><?php echo __('Nominativo'); ?></th>
                <th colspan="2"><?php echo __('Stato'); ?></th>
                <th></th>  
                <th></th>  
                <th></th> 
                <th></th>
                <th></th> 
    <?php
    echo '</tr>';

    foreach ($results as $numResult => $result) {

					if (!empty($result['User']['lastvisitDate']) && $result['User']['lastvisitDate'] != Configure::read('DB.field.datetime.empty'))
						$lastvisitDate = $this->Time->i18nFormat($result['User']['lastvisitDate'], "%e-%m-%Y");
					else
						$lastvisitDate = "";
					
					echo '<tr class="view">';
					echo '<td>';
					echo ($numResult + 1);
					echo '</td>';
					echo '<td>';
					echo '<a action="user_block-'.$result['User']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a>';
					echo '</td>';
                    echo '<td>'; 
					echo $this->App->drawUserAvatar($user, $result['User']['id'], $result['User']);
					echo '</td>';
                    echo '<td>'; 
					echo $result['User']['name']; 
					echo '<p>'.__('RegisterDate').' '.$this->Time->i18nFormat($result['User']['registerDate'], "%e-%m-%Y").'</p>';
					echo '</td>';
			        echo '<td>';
			        if ($result['User']['block'] == 0)
			            echo '<span style="color:green;">Attivo</span>';
			        else
			            echo '<span style="color:red;">Disattivato</span>';
			        echo '</td>';
			        
			        echo '<td>';
			        echo '<img alt="" src="' . Configure::read('App.img.cake') . '/blank32x32.png" id="submitEcomm-' . $result['User']['id'] . '" class="buttonCarrello submitEcomm" />';
			        echo '<div id="msgEcomm-' . $result['User']['id'] . '" class="msgEcomm"></div>';
			        echo '</td>';        
			        
                    
                    echo '<td>';
					echo $this->Form->input('DataRichEnter' . $result['User']['id'], ['label' => __('dataRichEnter'), 'type' => 'text',  'class' => 'callUpdateDate', 'value' => $this->Time->i18nFormat($result['Profile']['dataRichEnter'], "%e-%m-%Y"), 'data-attr-organization_id' => $result['User']['organization_id'], 'data-attr-user_id' => $result['User']['id'], 'data-attr-field_db' => 'dataRichEnter']);
                    echo $this->Ajax->datepicker('UserDataRichEnter' . $result['User']['id'], ['dateFormat' => 'dd-mm-yy', 'altField' => '#dataRichEnterDb_' . $result['User']['id'], 'altFormat' => 'yy-mm-dd']);
                    echo '<input type="hidden" id="dataRichEnterDb_' . $result['User']['id'] . '" name="data[User][UserDataRichEnterDb_' . $result['User']['id'] . ']" value="' . $result['Profile']['dataRichEnter'] . '" />';
                    echo '<br/>';
                    echo $this->Form->input('DataEnter' . $result['User']['id'], array('label' => __('dataEnter'), 'type' => 'text',  'class' => 'callUpdateDate', 'value' => $this->Time->i18nFormat($result['Profile']['dataEnter'], "%e-%m-%Y"), 'data-attr-organization_id' => $result['User']['organization_id'], 'data-attr-user_id' => $result['User']['id'], 'data-attr-field_db' => 'dataEnter'));
                    echo $this->Ajax->datepicker('UserDataEnter' . $result['User']['id'], array('dateFormat' => 'dd-mm-yy', 'altField' => '#dataEnterDb_' . $result['User']['id'], 'altFormat' => 'yy-mm-dd'));
                    echo '<input type="hidden" id="dataEnterDb_' . $result['User']['id'] . '" name="data[User][UserDataEnterDb_' . $result['User']['id'] . ']" value="' . $result['Profile']['dataEnter'] . '" />';
                    echo '</td>';

                    echo '<td>';
                    echo $this->Form->input('numDeliberaEnter' . $result['User']['id'], array('label' => __('numDeliberaEnter'), 'type' => 'text', 'size' => '5', 'class' => 'callUpdateDate', 'value' => $result['Profile']['numDeliberaEnter'], 'data-attr-organization_id' => $result['User']['organization_id'], 'data-attr-user_id' => $result['User']['id'], 'data-attr-field_db' => 'numDeliberaEnter'));
                    echo '<br/>';
                    echo $this->Form->input('DataDeliberaEnter' . $result['User']['id'], array('label' => __('dataDeliberaEnter'), 'type' => 'text',  'class' => 'callUpdateDate', 'value' => $this->Time->i18nFormat($result['Profile']['dataDeliberaEnter'], "%e-%m-%Y"), 'data-attr-organization_id' => $result['User']['organization_id'], 'data-attr-user_id' => $result['User']['id'], 'data-attr-field_db' => 'dataDeliberaEnter'));
                    echo $this->Ajax->datepicker('UserDataDeliberaEnter' . $result['User']['id'], array('dateFormat' => 'dd-mm-yy', 'altField' => '#dataDeliberaEnterDb_' . $result['User']['id'], 'altFormat' => 'yy-mm-dd'));
                    echo '<input type="hidden" id="dataDeliberaEnterDb_' . $result['User']['id'] . '" name="data[User][UserDataDeliberaEnterDb_' . $result['User']['id'] . ']" value="' . $result['Profile']['dataDeliberaEnter'] . '" />';
                    echo '</td>';
                    
                    echo '<td>';
                    echo $this->Form->input('DataRichExit' . $result['User']['id'], array('label' => __('dataRichExit'), 'type' => 'text',  'class' => 'callUpdateDate', 'value' => $this->Time->i18nFormat($result['Profile']['dataRichExit'], "%e-%m-%Y"), 'data-attr-organization_id' => $result['User']['organization_id'], 'data-attr-user_id' => $result['User']['id'], 'data-attr-field_db' => 'dataRichExit'));
                    echo $this->Ajax->datepicker('UserDataRichExit' . $result['User']['id'], array('dateFormat' => 'dd-mm-yy', 'altField' => '#dataRichExitDb_' . $result['User']['id'], 'altFormat' => 'yy-mm-dd'));
                    echo '<input type="hidden" id="dataRichExitDb_' . $result['User']['id'] . '" name="data[User][UserDataRichExitDb_' . $result['User']['id'] . ']" value="' . $result['Profile']['dataRichExit'] . '" />';
                    echo '<br />';
                    echo $this->Form->input('numDeliberaExit' . $result['User']['id'], array('label' => __('numDeliberaExit'), 'type' => 'text', 'size' => '5', 'class' => 'callUpdateDate', 'value' => $result['Profile']['numDeliberaExit'], 'data-attr-organization_id' => $result['User']['organization_id'], 'data-attr-user_id' => $result['User']['id'], 'data-attr-field_db' => 'numDeliberaExit'));
                    echo '<br />';
                    echo $this->Form->input('motivoRichExit' . $result['User']['id'], array('label' => __('motivoRichExit'), 'type' => 'text', 'size' => '25', 'class' => 'callUpdateDate', 'value' => $result['Profile']['motivoRichExit'], 'data-attr-organization_id' => $result['User']['organization_id'], 'data-attr-user_id' => $result['User']['id'], 'data-attr-field_db' => 'motivoRichExit'));
                    echo '</td>';

                    echo '<td>';
                    echo $this->Form->input('DataExit' . $result['User']['id'], array('label' => __('dataExit'), 'type' => 'text',  'class' => 'callUpdateDate', 'value' => $this->Time->i18nFormat($result['Profile']['dataExit'], "%e-%m-%Y"), 'data-attr-organization_id' => $result['User']['organization_id'], 'data-attr-user_id' => $result['User']['id'], 'data-attr-field_db' => 'dataExit'));
                    echo $this->Ajax->datepicker('UserDataExit' . $result['User']['id'], array('dateFormat' => 'dd-mm-yy', 'altField' => '#dataExitDb_' . $result['User']['id'], 'altFormat' => 'yy-mm-dd'));
                    echo '<input type="hidden" id="dataExitDb_' . $result['User']['id'] . '" name="data[User][UserDataExitDb_' . $result['User']['id'] . ']" value="' . $result['Profile']['dataExit'] . '" />';
                    echo '<br/>';
                    echo $this->Form->input('DataDeliberaExit' . $result['User']['id'], array('label' => __('dataDeliberaExit'), 'type' => 'text',  'class' => 'callUpdateDate', 'value' => $this->Time->i18nFormat($result['Profile']['dataDeliberaExit'], "%e-%m-%Y"), 'data-attr-organization_id' => $result['User']['organization_id'], 'data-attr-user_id' => $result['User']['id'], 'data-attr-field_db' => 'dataDeliberaExit'));
                    echo $this->Ajax->datepicker('UserDataDeliberaExit' . $result['User']['id'], array('dateFormat' => 'dd-mm-yy', 'altField' => '#dataDeliberaExitDb_' . $result['User']['id'], 'altFormat' => 'yy-mm-dd'));
                    echo '<input type="hidden" id="dataDeliberaExitDb_' . $result['User']['id'] . '" name="data[User][UserDataDeliberaExitDb_' . $result['User']['id'] . ']" value="' . $result['Profile']['dataDeliberaExit'] . '" />';
                    echo '</td>';                    

                    echo '<td>';
                    echo $this->Form->input('DataRestituzCassa' . $result['User']['id'], array('label' => __('dataRestituzCassa'), 'type' => 'text',  'class' => 'callUpdateDate', 'value' => $this->Time->i18nFormat($result['Profile']['dataRestituzCassa'], "%e-%m-%Y"), 'data-attr-organization_id' => $result['User']['organization_id'], 'data-attr-user_id' => $result['User']['id'], 'data-attr-field_db' => 'dataRestituzCassa'));
                    echo $this->Ajax->datepicker('UserDataRestituzCassa' . $result['User']['id'], array('dateFormat' => 'dd-mm-yy', 'altField' => '#dataRestituzCassaDb_' . $result['User']['id'], 'altFormat' => 'yy-mm-dd'));
                    echo '<input type="hidden" id="dataRestituzCassaDb_' . $result['User']['id'] . '" name="data[User][UserDataRestituzCassaDb_' . $result['User']['id'] . ']" value="' . $result['Profile']['dataRestituzCassa'] . '" />';
                    echo '<br/>'; 
                    echo $this->Form->input('notaRestituzCassa' . $result['User']['id'], array('label' => __('notaRestituzCassa'), 'type' => 'text', 'size' => '25', 'class' => 'callUpdateDate', 'value' => $result['Profile']['notaRestituzCassa'], 'data-attr-organization_id' => $result['User']['organization_id'], 'data-attr-user_id' => $result['User']['id'], 'data-attr-field_db' => 'notaRestituzCassa'));
                    echo '</td>';
                    
                    echo '</tr>';
                    
                    echo '<tr>';
                    echo '<td colspan="5"></td>';
                    echo '<td colspan="6">';
                    echo $this->Form->input('nota' . $result['User']['id'], array('label' => __('nota'), 'style' => 'width:95%', 'type' => 'text', 'class' => 'callUpdateDate', 'value' => $result['Profile']['nota'], 'data-attr-organization_id' => $result['User']['organization_id'], 'data-attr-user_id' => $result['User']['id'], 'data-attr-field_db' => 'nota'));
                    echo '</td>';
                    echo '</tr>';
                    
                    /*
                    echo '<td>';
                    if (!empty($result['User']['email']))
                        echo '<a title="' . __('Email send') . '" target="_blank" href="mailto:' . $result['User']['email'] . '">' . $result['User']['email'] . '</a><br />';
                    if (!empty($result['Profile']['address']))
                        echo $result['Profile']['address'] . '<br />';
                    if (!empty($result['Profile']['phone']))
                        echo $result['Profile']['phone'] . '<br />';
                    if (!empty($result['Profile']['phone2']))
                        echo $result['Profile']['phone2'] . '<br />';
                    echo '</td>';
					*/

                echo '</tr>';
                echo '<tr class="trView" id="trViewId-'.$result['User']['id'].'">';
                echo '<td colspan="3"></td>';
                echo '<td colspan="8" id="tdViewId-'.$result['User']['id'].'"></td>';
                echo '</tr>';
	} // loops
	
	echo '</table></div>';

	echo $this->Form->end();
        } else
            echo $this->element('boxMsg', array('class_msg' => 'message resultsNotFound', 'msg' => __('msg_search_not_result')));
        ?>
        <p>
            <?php
            echo $this->Paginator->counter(array(
                'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
            ));
            ?>	</p>

        <div class="paging">
            <?php
            echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
            echo $this->Paginator->numbers(array('separator' => ''));
            echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
            ?>
        </div>
</div>