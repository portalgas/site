<?php 
echo '<div class="legenda legenda-ico-info">';
echo "Importo totale in cassa per il gasista: ".number_format($cashResults['Cash']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';

if(!empty($cashResults['Cash']['nota'])) echo '<p><span style="font-weight: bold;">Nota:</span> '.$cashResults['Cash']['nota'].'</p>';

echo '</div>';
?>