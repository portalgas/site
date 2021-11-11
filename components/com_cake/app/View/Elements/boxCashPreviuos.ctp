<?php
$this->App->d($results);
 
echo '<div class="legenda legenda-ico-info" style="float:none;">';
echo '<h2>Voce di cassa precedente</h2>';

echo '<div class="input text">';
echo '<label for="importo">'.__('CashSaldo').'</label> ';
echo $results['Cash']['importo_e'];
echo '</div>';
echo '<div class="input text">';
echo '<label for="importo">'.__('Nota').'</label> ';
echo $results['Cash']['nota'];
echo '</div>';
echo '<div class="input text">';
echo '<label for="importo">'.__('Data').'</label> ';
echo $this->Time->i18nFormat($results['Cash']['created'],"%A, %e %B %Y");
echo '</div>';

if(!empty($results['Cash']['CashesHistory'])) {

}

echo '</div>';