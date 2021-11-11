<?php
echo '<div class="docs">';	
echo '<div class="table-responsive"><table class="table table-hover">';
echo '<tr>';
echo '<th colspan="2">Tipologia di documento</th>';
echo '<th></th>';
echo '<th>Formato pdf</th>';
echo '<th>Formato csv</th>';
echo '<th>Formato excel</th>';
echo '</tr>';
	
echo $this->element('reportUsers', ['type' => 'BO', 'isManager' => $isManager, 'organizationsResults' => $organizationsResults]);
	
echo '</table></div>';
echo '</div>';