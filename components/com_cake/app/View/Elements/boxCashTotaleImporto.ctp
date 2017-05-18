<?php 
echo '<div class="legenda legenda-ico-info">';
echo "Importo totale in cassa: ".number_format($totale_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
echo '</div>';
?>