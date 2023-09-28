<?php 
if(!empty($gasGroups)) {
	echo $this->Form->create('OrganizationsCash', ['id' => 'frmGasGroup']);
	echo $this->Form->hidden('dest_controller', ['value' => $dest_controller]);
	echo $this->Form->hidden('dest_action', ['value' => $dest_action]);

    echo '<fieldset>';
    echo '<legend>Scegli il gruppo con il quale lavorare</legend>';

    echo $this->Form->select('gas_group_id', $gasGroups, [
				'label' => "Scegli il gruppo con il quale lavorare",
				'empty' => Configure::read('option.empty'),
                'class'=> 'form-control',
                'default' => $gas_group_id,
				'onChange' => 'javascript:choiceGasGroup(this);',
			]);

    echo '</fieldset>';
	echo $this->Form->end();
	?>
	<script type="text/javascript">
	function choiceGasGroup(obj) {
		let gas_group_id = $(obj).val();	
		if(gas_group_id!='') {
			$('#frmGasGroup').submit();
		}
	}

    <?php 
    if(count($gasGroups)==1) {
        echo "let gas_group_id = $('#OrganizationsCashGasGroupId').val();";
        echo "if(gas_group_id!='') $('#frmGasGroup').submit();";
    }
    ?>
	</script>
<?php	
}