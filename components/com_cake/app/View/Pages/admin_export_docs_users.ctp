<div class="docs">
	
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th colspan="2">Tipologia di documento</th>
		<th></th>
		<th>Formato pdf</th>
		<th>Formato csv</th>
		<th>Formato excel</th>
	</tr>	
	
	<?php
	echo $this->element('reportUsers', array('type' => 'BO', 'isManager' => $isManager));
	?>
	</table>
</div>