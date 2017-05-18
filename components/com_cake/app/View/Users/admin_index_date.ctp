<div class="users">

    <h2 class="ico-calendar">
        <?php echo __('Users Date'); ?>
    </h2>

    <?php echo $this->Form->create('Filteruser', array('id' => 'formGasFilter', 'type' => 'get')); ?>
    <fieldset class="filter">
        <legend><?php echo __('Filter Users'); ?></legend>
        <table>
            <tr>
                <td>
                    <?php
                    echo $this->Ajax->autoComplete('FilterUserUsername', Configure::read('App.server') . '/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteUsers_username&format=notmpl', array('label' => 'Username', 'name' => 'FilterUserUsername', 'value' => $FilterUserUsername, 'size' => '50', 'escape' => false));
                    ?>
                </td>
                <td>
                    <?php
                    echo $this->Ajax->autoComplete('FilterUserName', Configure::read('App.server') . '/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteUsers_name&format=notmpl', array('label' => 'Nominativo', 'name' => 'FilterUserName', 'value' => $FilterUserName, 'size' => '50', 'escape' => false));
                    ?>
                </td>
                <td>
                    <?php echo $this->Form->input('block', array('label' => __('Stato'), 'options' => $block, 'name' => 'FilterUserBlock', 'default' => $FilterUserBlock, 'escape' => false)); ?> 
                </td>	
                <td>
                    <?php echo $this->Form->input('sort', array('label' => __('Sort'), 'options' => $sorts, 'name' => 'FilterUserSort', 'default' => $FilterUserSort, 'escape' => false)); ?> 
                </td>					
                <td>
                    <?php echo $this->Form->end(array('label' => __('Filter'), 'class' => 'filter', 'class' => 'filter', 'div' => array('class' => 'submit filter', 'style' => 'display:none'))); ?>
                </td>
            </tr>	
        </table>
    </fieldset>
<?php
// echo $this->Form->end(); se chiudo non funziona Ajax->autoComplete!

if (!empty($results)) {

    /*
     * creo un altro form e no quello dei filtri passa tutti i campi sottostante 
     */
    echo $this->Form->create('User');
    ?>
        <table cellpadding="0" cellspacing="0">
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

    foreach ($results as $numResult => $result):

        if (!empty($result['User']['lastvisitDate']) && $result['User']['lastvisitDate'] != '0000-00-00 00:00:00')
            $lastvisitDate = $this->Time->i18nFormat($result['User']['lastvisitDate'], "%e-%m-%Y");
        else
            $lastvisitDate = "";
        ?>
                <tr class="view">
                    <td><?php echo ($numResult + 1); ?></td>
                    <td><a action="user_block-<?php echo $result['User']['id']; ?>" class="actionTrView openTrView" href="#" title="<?php echo __('Href_title_expand'); ?>"></a></td>
                    <td><?php echo $this->App->drawUserAvatar($user, $result['User']['id'], $result['User']); ?></td>
                    <td><?php echo $result['User']['name']; ?></td>
			        <?php
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
					echo '<p>'.__('RegisterDate').' '.$this->Time->i18nFormat($result['User']['registerDate'], "%e-%m-%Y").'</p>';
                    echo $this->Form->input('DataRichEnter' . $result['User']['id'], array('label' => __('dataRichEnter'), 'type' => 'text', 'size' => '10', 'class' => 'noWidth callUpdateDate', 'value' => $this->Time->i18nFormat($result['Profile']['dataRichEnter'], "%e-%m-%Y"), 'data_attr_user_id' => $result['User']['id'], 'data_attr_field_db' => 'dataRichEnter'));
                    echo $this->Ajax->datepicker('UserDataRichEnter' . $result['User']['id'], array('dateFormat' => 'dd-mm-yy', 'altField' => '#dataRichEnterDb_' . $result['User']['id'], 'altFormat' => 'yy-mm-dd'));
                    echo '<input type="hidden" id="dataRichEnterDb_' . $result['User']['id'] . '" name="data[User][UserDataRichEnterDb_' . $result['User']['id'] . ']" value="' . $result['Profile']['dataRichEnter'] . '" />';
                    echo '<br/>';
                    echo $this->Form->input('DataEnter' . $result['User']['id'], array('label' => __('dataEnter'), 'type' => 'text', 'size' => '10', 'class' => 'noWidth callUpdateDate', 'value' => $this->Time->i18nFormat($result['Profile']['dataEnter'], "%e-%m-%Y"), 'data_attr_user_id' => $result['User']['id'], 'data_attr_field_db' => 'dataEnter'));
                    echo $this->Ajax->datepicker('UserDataEnter' . $result['User']['id'], array('dateFormat' => 'dd-mm-yy', 'altField' => '#dataEnterDb_' . $result['User']['id'], 'altFormat' => 'yy-mm-dd'));
                    echo '<input type="hidden" id="dataEnterDb_' . $result['User']['id'] . '" name="data[User][UserDataEnterDb_' . $result['User']['id'] . ']" value="' . $result['Profile']['dataEnter'] . '" />';
                    echo '</td>';

                    echo '<td>';
                    echo $this->Form->input('numDeliberaEnter' . $result['User']['id'], array('label' => __('numDeliberaEnter'), 'type' => 'text', 'size' => '5', 'class' => 'noWidth callUpdateDate', 'value' => $result['Profile']['numDeliberaEnter'], 'data_attr_user_id' => $result['User']['id'], 'data_attr_field_db' => 'numDeliberaEnter'));
                    echo '<br/>';
                    echo $this->Form->input('DataDeliberaEnter' . $result['User']['id'], array('label' => __('dataDeliberaEnter'), 'type' => 'text', 'size' => '10', 'class' => 'noWidth callUpdateDate', 'value' => $this->Time->i18nFormat($result['Profile']['dataDeliberaEnter'], "%e-%m-%Y"), 'data_attr_user_id' => $result['User']['id'], 'data_attr_field_db' => 'dataDeliberaEnter'));
                    echo $this->Ajax->datepicker('UserDataDeliberaEnter' . $result['User']['id'], array('dateFormat' => 'dd-mm-yy', 'altField' => '#dataDeliberaEnterDb_' . $result['User']['id'], 'altFormat' => 'yy-mm-dd'));
                    echo '<input type="hidden" id="dataDeliberaEnterDb_' . $result['User']['id'] . '" name="data[User][UserDataDeliberaEnterDb_' . $result['User']['id'] . ']" value="' . $result['Profile']['dataDeliberaEnter'] . '" />';
                    echo '</td>';
                    
                    echo '<td>';
                    echo $this->Form->input('DataRichExit' . $result['User']['id'], array('label' => __('dataRichExit'), 'type' => 'text', 'size' => '10', 'class' => 'noWidth callUpdateDate', 'value' => $this->Time->i18nFormat($result['Profile']['dataRichExit'], "%e-%m-%Y"), 'data_attr_user_id' => $result['User']['id'], 'data_attr_field_db' => 'dataRichExit'));
                    echo $this->Ajax->datepicker('UserDataRichExit' . $result['User']['id'], array('dateFormat' => 'dd-mm-yy', 'altField' => '#dataRichExitDb_' . $result['User']['id'], 'altFormat' => 'yy-mm-dd'));
                    echo '<input type="hidden" id="dataRichExitDb_' . $result['User']['id'] . '" name="data[User][UserDataRichExitDb_' . $result['User']['id'] . ']" value="' . $result['Profile']['dataRichExit'] . '" />';
                    echo '<br />';
                    echo $this->Form->input('numDeliberaExit' . $result['User']['id'], array('label' => __('numDeliberaExit'), 'type' => 'text', 'size' => '5', 'class' => 'noWidth callUpdateDate', 'value' => $result['Profile']['numDeliberaExit'], 'data_attr_user_id' => $result['User']['id'], 'data_attr_field_db' => 'numDeliberaExit'));
                    echo '<br />';
                    echo $this->Form->input('motivoRichExit' . $result['User']['id'], array('label' => __('motivoRichExit'), 'type' => 'text', 'size' => '25', 'class' => 'noWidth callUpdateDate', 'value' => $result['Profile']['motivoRichExit'], 'data_attr_user_id' => $result['User']['id'], 'data_attr_field_db' => 'motivoRichExit'));
                    echo '</td>';

                    echo '<td>';
                    echo $this->Form->input('DataExit' . $result['User']['id'], array('label' => __('dataExit'), 'type' => 'text', 'size' => '10', 'class' => 'noWidth callUpdateDate', 'value' => $this->Time->i18nFormat($result['Profile']['dataExit'], "%e-%m-%Y"), 'data_attr_user_id' => $result['User']['id'], 'data_attr_field_db' => 'dataExit'));
                    echo $this->Ajax->datepicker('UserDataExit' . $result['User']['id'], array('dateFormat' => 'dd-mm-yy', 'altField' => '#dataExitDb_' . $result['User']['id'], 'altFormat' => 'yy-mm-dd'));
                    echo '<input type="hidden" id="dataExitDb_' . $result['User']['id'] . '" name="data[User][UserDataExitDb_' . $result['User']['id'] . ']" value="' . $result['Profile']['dataExit'] . '" />';
                    echo '<br/>';
                    echo $this->Form->input('DataDeliberaExit' . $result['User']['id'], array('label' => __('dataDeliberaExit'), 'type' => 'text', 'size' => '10', 'class' => 'noWidth callUpdateDate', 'value' => $this->Time->i18nFormat($result['Profile']['dataDeliberaExit'], "%e-%m-%Y"), 'data_attr_user_id' => $result['User']['id'], 'data_attr_field_db' => 'dataDeliberaExit'));
                    echo $this->Ajax->datepicker('UserDataDeliberaExit' . $result['User']['id'], array('dateFormat' => 'dd-mm-yy', 'altField' => '#dataDeliberaExitDb_' . $result['User']['id'], 'altFormat' => 'yy-mm-dd'));
                    echo '<input type="hidden" id="dataDeliberaExitDb_' . $result['User']['id'] . '" name="data[User][UserDataDeliberaExitDb_' . $result['User']['id'] . ']" value="' . $result['Profile']['dataDeliberaExit'] . '" />';
                    echo '</td>';                    

                    echo '<td>';
                    echo $this->Form->input('DataRestituzCassa' . $result['User']['id'], array('label' => __('dataRestituzCassa'), 'type' => 'text', 'size' => '10', 'class' => 'noWidth callUpdateDate', 'value' => $this->Time->i18nFormat($result['Profile']['dataRestituzCassa'], "%e-%m-%Y"), 'data_attr_user_id' => $result['User']['id'], 'data_attr_field_db' => 'dataRestituzCassa'));
                    echo $this->Ajax->datepicker('UserDataRestituzCassa' . $result['User']['id'], array('dateFormat' => 'dd-mm-yy', 'altField' => '#dataRestituzCassaDb_' . $result['User']['id'], 'altFormat' => 'yy-mm-dd'));
                    echo '<input type="hidden" id="dataRestituzCassaDb_' . $result['User']['id'] . '" name="data[User][UserDataRestituzCassaDb_' . $result['User']['id'] . ']" value="' . $result['Profile']['dataRestituzCassa'] . '" />';
                    echo '<br/>'; 
                    echo $this->Form->input('notaRestituzCassa' . $result['User']['id'], array('label' => __('notaRestituzCassa'), 'type' => 'text', 'size' => '25', 'class' => 'noWidth callUpdateDate', 'value' => $result['Profile']['notaRestituzCassa'], 'data_attr_user_id' => $result['User']['id'], 'data_attr_field_db' => 'notaRestituzCassa'));
                    echo '</td>';
                    
                    echo '</tr>';
                    
                    echo '<tr>';
                    echo '<td colspan="5"></td>';
                    echo '<td colspan="6">';
                    echo $this->Form->input('nota' . $result['User']['id'], array('label' => __('nota'), 'style' => 'width:95%', 'type' => 'text', 'class' => 'callUpdateDate', 'value' => $result['Profile']['nota'], 'data_attr_user_id' => $result['User']['id'], 'data_attr_field_db' => 'nota'));
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

                    ?>	
                </tr>
                <tr class="trView" id="trViewId-<?php echo $result['User']['id']; ?>">
                    <td colspan="3"></td>
                    <td colspan="8" id="tdViewId-<?php echo $result['User']['id']; ?>"></td>
                </tr>
            <?php
            endforeach;
            echo '</table>';

            echo $this->Form->end();
        } else
            echo $this->element('boxMsg', array('class_msg' => 'message resultsNotFonud'));
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

<script type="text/javascript">
    function callUpdateDate(user_id, field_db, data_db) {

        jQuery("#submitEcomm-" + user_id).animate({opacity: 1});

        var url = '';
        url = "/administrator/index.php?option=com_cake&controller=Users&action=index_date_update&format=notmpl";

        jQuery.ajax({
            type: "POST",
            url: url,
            data: "user_id=" + user_id + "&field_db=" + encodeURIComponent(field_db) + "&data_db=" + data_db,
            success: function (response) {
                jQuery("#submitEcomm-" + user_id).attr("src", app_img + "/actions/32x32/bookmark.png");
                jQuery("#msgEcomm-" + user_id).html("Salvato!");
                jQuery("#submitEcomm-" + user_id).delay(1000).animate({
                    opacity: 0
                }, 1500);
                jQuery("#msgEcomm-" + user_id).delay(1000).animate({
                    opacity: 0
                }, 1500);
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                jQuery('#msgEcomm-' + user_id).html(textStatus);
                jQuery('#submitEcomm-' + user_id).attr('src', app_img + '/blank32x32.png');
            }
        });
    }

    jQuery(document).ready(function () {
        jQuery('.callUpdateDate').change(function () {
            
            var user_id = jQuery(this).attr('data_attr_user_id');
            var field_db = jQuery(this).attr('data_attr_field_db');
            
            var data_db = "";
            if(jQuery('#' + field_db + 'Db_' + user_id).length>0)
                data_db = jQuery('#' + field_db + 'Db_' + user_id).val(); /* datepicker */
            else
                data_db = jQuery(this).val(); /* campo testo */
            console.log("user_id " + user_id + " - field_db " + field_db + " data_db " + data_db);

            callUpdateDate(user_id, field_db, data_db);
            return false;

        });
    });
</script>