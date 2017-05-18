<?php 
$msg = "Il G.A.S. ".$organizationTitolare['Organization']['name']." è 'Titolare ordini condivisi' per il produttore scelto.";

echo $this->element('boxMsg',array('msg' => $msg, 'class_msg' => 'notice resultsNotFonud'));
?>