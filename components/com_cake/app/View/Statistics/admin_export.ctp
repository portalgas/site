<?php$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));$this->Html->addCrumb(__('Statistics'),array('controller' => 'Statistics', 'action' => 'index'));$this->Html->addCrumb(__('Export Statistics'));echo $this->Html->getCrumbList(array('class'=>'crumbs'));?><script type="text/javascript">jQuery(document).ready(function() {		jQuery(function() {		jQuery(".blank").attr("target","_blank");	});		jQuery('#actionExportDoc').click(function() {		var year = jQuery('#year').val();				var doc_options = jQuery("input[name='doc_options']:checked").val();		var doc_formato = jQuery("input[name='doc_formato']:checked").val();		if(year=='') {			alert("Anno obbligatorio");			return false;		}		if(doc_options==null) {			alert("<?php echo __('jsAlertPrintFormatRequired');?>");			return false;		}				if(doc_formato==null) {			alert("<?php echo __('jsAlertPrintTypeRequired');?>");			return false;		}		url = '/administrator/index.php?option=com_cake&controller=Statistics&action='+doc_options+'&year='+year+'&doc_options=export_file_excel&doc_formato='+doc_formato+'&format=notmpl';		jQuery('#actionExportDoc').attr('href', url);    			return true;	});	});</script>	<div class="statistic form">		<h2 class="ico-statistic">		<?php echo __('Statistics');?>	</h2>	<div class="docs">	<?php echo $this->Form->create();?>		<fieldset>						<div id="doc-year">			<?php				echo $this->Form->year('year', 2012, $anno_da, array('id' => 'year', 'label' => __('Year'), 'default' => date('Y'), 'empty' => false));			?>		</div>		<div class="left label" style="width:100px !important;">Opzioni</div>		<div class="left radio">			<p>				<input type="radio" name="doc_options" id="export_orders_file" value="export_orders_file" checked /><label for="orders">Statistica con dettaglio ordini</label>			</p>			<p>				<input type="radio" name="doc_options" id="export_file" value="export_file" /><label for="completa">Statistica completa (con dettaglio singoli acquisti)</label>			</p>		</div>				<div id="doc-print">			<h2 class="ico-export-docs">				<?php echo __('Print Doc');?>				<div class="actions-img">					<ul>						<li><?php echo $this->Form->input('typeDoc', array(											 'type' => 'radio',											 'name' => 'doc_formato',											 'fieldset' => false,											 'legend' => false,											 'div' => array('class' => ''),											 'options' => $options,											 'default' => 'EXCEL',									   ));							?>						</li>						<li><?php echo $this->Html->link(__('Export Statistics'), '' ,array('id' => 'actionExportDoc', 'class' => 'action actionPrinter blank', 'title' => __('Export Statistics'))); ?></li>					</ul>				</div>			</h2>				</div>		</fieldset>	</div></div><div class="actions">	<h3><?php echo __('Actions'); ?></h3>	<ul>		<li><?php echo $this->Html->link(__('Statistics'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>	</ul></div>