<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');

if (isset($user->organization['Organization']))
    $j_seo = $user->organization['Organization']['j_seo'];
else
    $j_seo = '';

/*
 * logout
 */
if ($type == 'logout') {

        echo '<div class="box-account">';

        if ($params->get('greeting')) {
            echo '<span style="margin-right: 10px;">';
            if ($params->get('name') == 0)
                echo JText::sprintf('MOD_LOGIN_HINAME', htmlspecialchars($user->get('name')));
            else
                echo JText::sprintf('MOD_LOGIN_HINAME', htmlspecialchars($user->get('username')));
            echo '</span>';
        }
        if (!empty($j_seo)) {
            if(strpos(JURI::current(), '/carrello-'.$j_seo)===false)
                echo '<a href="/home-' . $j_seo . '/carrello-' . $j_seo . '" class="btn btn-primary">Carrello</a>';
            else
                echo '<a href="/home-' . $j_seo . '/carts-history" class="btn btn-primary">Storico acquisti</a>';
        }
        ?>
        <a id="btn-account-logout" class="btn btn-orange btn-account" href="#">Account
            <span class="fa fa-caret-down account-arrow" style="padding-left: 5px;"></span>
        </a>
    </div>

    <div id="box-account-dashboard" style="display:none;" data-attr-type="after-login">
        <form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" id="login-form" >
            <fieldset>
                <div class="gb_S">
                    <p><?php echo $user->email; ?></p>
                    <p>
                        <?php
                        if (!empty($j_seo)) {
                            echo '<ul class="list-group" style="list-style-type: none;">';
                            echo '<li><a href="/home-' . $j_seo . '/my-profile" class="btn btn-blue">Visualizza il tuo profilo / Modifica le impostazioni</a></li>';
                            echo '<li><a href="/home-' . $j_seo . '/bookmarks-mails" class="btn btn-blue">Personalizza le mail</a></li>';
                            echo '<li><a href="/home-' . $j_seo . '/carts-history" class="btn btn-blue">Storico acquisti</a></li>';
                            echo '</ul>';
                            // <a href=" echo JRoute::_('index.php?option=com_users&view=profile'); " class="btn btn-blue">Visualizza il tuo profilo / Modifica le impostazioni</a> 
                        }
                        ?>
                    </p>
                    
                    <div id="box-cash">Loading...</div>
                    <script id="tmpl-box-cash" type="x-tmpl-mustache">
                        <p>
                        {{#data.cash_btn_debito}}
                            <label id="cash-action" data-attr-url="/?option=com_cake&controller=Ajax&action=view_cashes_histories&format=notmpl" class="btn btn-danger">Debito verso la cassa {{{ data.user_cash_e }}}</label>                      
                        {{/data.cash_btn_debito}}
                        {{#data.cash_btn_credito}}
                            <label id="cash-action" data-attr-url="/?option=com_cake&controller=Ajax&action=view_cashes_histories&format=notmpl" class="btn btn-primary">Credito verso la cassa {{{ data.user_cash_e }}}</label>                        
                        {{/data.cash_btn_credito}}
                        </p>
                        {{#data.ctrl_limit.fe_msg}}
                            <p>
                                <div class="alert alert-warning">{{{data.ctrl_limit.fe_msg}}}</div>
                            </p>
                        {{/data.ctrl_limit.fe_msg}}
                        {{#data.ctrl_limit.fe_msg_tot_acquisti}}
                        <p>
                            <div class="alert alert-warning">{{{data.ctrl_limit.fe_msg_tot_acquisti}}}</div>
                        </p>
                        {{/data.ctrl_limit.fe_msg_tot_acquisti}}

                        {{#data.ctrl_limit.has_fido}}
                        <p>
                            <div class="alert alert-info">Fido di {{{data.ctrl_limit.importo_fido_e}}}</div>
                        </p>
                        {{/data.ctrl_limit.has_fido}}                        
                    </script>
                    <?php   
                        /*
                        if(!empty($cash)) {
                            echo '<p>';
                            echo '<button id="cash-action3" data-attr-url="/?option=com_cake&controller=Ajax&action=view_cashes_histories&format=notmpl" class="btn ';
                            if($cash->importo < 0)
                                echo 'btn-danger">Debito verso la cassa '.$cash->importo_e;
                            else
                                echo 'btn-primary">Credito verso la cassa '.$cash->importo_e;
                            echo '</button>';
                            echo '</p>';
                        }
                        else
                           echo '<span class="label label-info">Nessuna voce in cassa</span>'; */
                       ?>
                    <p>
                        <input type="submit" name="Submit" value="<?php echo JText::_('JLOGOUT'); ?>" class="btn btn-orange" />
                        <input type="hidden" name="option" value="com_users" />
                        <input type="hidden" name="task" value="user.logout" />
                        <input type="hidden" name="return" value="<?php echo base64_encode("index.php"); ?>" />
                        <?php echo JHtml::_('form.token'); ?>
                    </p>
                </div>
            </fieldset>
        </form>
    </div>
    <?php
}
/*
 * login
 */
else {
    ?>          

    <div class="box-account">

        <a class="btn btn-orange btn-account" id="btn-account-login" href="#"><i class="fa fa-user fa-fw"></i> Account
            <span class="fa fa-caret-down account-arrow" style="padding-left: 5px;"></span>
        </a>    
    </div>

    <div id="box-account-dashboard" style="display:none;">
        <form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" id="login-form" >
    <?php if ($params->get('pretext')): ?>
                <div class="pretext">
                    <p><?php echo $params->get('pretext'); ?></p>
                </div>
    <?php endif; ?>
            <fieldset>
                <div class="gb_S margin-top-lg">

                    <div id="account-msg" class="account-msg"></div>


                    <div class="form-group input-group margin-bottom-sm margin-top-lg">
                        <span class="input-group-addon"><i class="fa fa-user fa-fw"></i></span>
                        <input class="form-control" type="text" name="username" placeholder="Username" />
                    </div>
                    <div class="form-group input-group margin-bottom-lg margin-top-lg">
                        <span class="input-group-addon"><i class="fa fa-key fa-fw"></i></span>
                        <input class="form-control" type="password" name="password" placeholder="Password" />
                    </div>                      


    <?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
                        <label for="modlgn-remember"><?php echo JText::_('MOD_LOGIN_REMEMBER_ME') ?></label>
                        <input id="modlgn-remember" type="checkbox" name="remember" class="inputbox" value="yes"/>
    <?php endif; ?>         
                    <div class="content-btn">

                        <div style="float: left; clear: both; margin: 0px; ">
                            <img width="100" height="39" src="https://www.google.com/intl/it/images/logos/mail_logo.png" title="fai login con il tuo account GMail" />
                        </div>


                        <input type="submit" name="Submit" class="btn btn-success pull-right" value="<?php echo JText::_('JLOGIN') ?>" />
                        <input type="hidden" name="option" value="com_users" />
                        <input type="hidden" name="task" value="user.login" />
                        <input type="hidden" name="return" value="<?php echo $return; ?>" />
    <?php echo JHtml::_('form.token'); ?>
                    </div>

                </div>

                <div class="gb_0">
                    <div>
                        <a class="gb_F btn btn-default" id="btn-account-forget">Dati dimenticati</a>
                    </div>

                    <?php
                    $usersConfig = JComponentHelper::getParams('com_users');
                    if ($usersConfig->get('allowUserRegistration')) :
                        ?>
                        <div>
                            <a href="<?php echo JRoute::_('index.php?option=com_users&view=registration'); ?>" id="gb_71" class="gb_F btn btn-default"><?php echo JText::_('MOD_LOGIN_REGISTER'); ?></a>
                        </div>
    <?php endif; ?>
                </div>
            </fieldset>

    <?php if ($params->get('posttext')): ?>
                <div class="posttext">
                    <p><?php echo $params->get('posttext'); ?></p>
                </div>
    <?php endif; ?> 
        </form>
    </div>


    <div id="box-account-dashboard-forget" style="display:none;">
        <form action="" method="get" id="login-form-forget" >
            <fieldset>
                <div class="gb_S">                          
                    <p>
                        <a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>" class="btn btn-blue">
                            Hai dimenticato la tua password?</a>
                    </p>
                    <p>
                        <a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>" class="btn btn-blue">
                            Hai dimenticato il tuo nome utente?</a>
                    </p>            
                </div>

                <div class="gb_0">
                    <div>
                        <a class="gb_F btn" id="btn-account-return">Torna alla login</a>
                    </div>
                </div>

            </fieldset> 
        </form>
    </div>
    <?php
} // end if login / logout
?>  


<script type="text/javascript">
$(document).ready(function () {
    var url = '/api/cash_ctrl_limit?format=notmpl';
    var tmpl_box_cash = $('#tmpl-box-cash').html();
    
    $('.btn-account').on('click', function () {

        $('#account-msg').css('display', 'none');
        $('#account-msg').html("");

        if ($('#box-account-dashboard').css('display') == 'none') {
            $('#box-account-dashboard').show();
            $('.account-arrow').removeClass('fa-caret-down');
            $('.account-arrow').addClass('fa-caret-up');
            
            /* Mustache */
            Mustache.parse(tmpl_box_cash); 
            
            if($('#box-account-dashboard').attr("data-attr-type")=='after-login') {
                $.ajax({url: url, 
                      datatype:'json',
                      success: function(data){
                            var data = JSON.parse(data);
                        
                            if(data.user_cash < 0)
                                data.cash_btn_debito = true;
                            else 
                                data.cash_btn_credito = true;
                        
                            var rendered = Mustache.render(tmpl_box_cash, {data: data});

                            /*console.log(rendered);*/
                            $('#box-cash').html(rendered);
                                                    
                            var modalCallerId = "cash-action";
                            var modalHeader = "Movimenti di cassa";
                            var modalBody = "";
                            var modalSubmitFunc = "";
                            var modalSubmitText = "";

                            objModal = new Modal(modalCallerId, modalHeader, modalBody, modalSubmitFunc, modalSubmitText);                          
                      },
                      error:function(){
                          $("#box-cash").html("");
                      }             
                });
            }
            /* */
            
        } else {
            $("#box-cash").html("");
            $('#box-account-dashboard').hide();
            $('.account-arrow').removeClass('fa-caret-up');
            $('.account-arrow').addClass('fa-caret-down');
        }

        return false;
    });

    $('#btn-account-forget').on('click', function () {
        $('#box-account-dashboard').hide();
        $('#box-account-dashboard-forget').show();

        return false;
    });

    $('#btn-account-return').on('click', function () {
        $('#box-account-dashboard-forget').hide();
        $('#box-account-dashboard').show();

        return false;
    });
});
</script>