<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

DPCalendarHelper::loadLibrary(array('jquery' => true, 'datepicker' => true, 'chosen' => true, 'maps' => true));
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tabstate');
JHtml::_('script', 'system/core.js', false, true);

$input = JFactory::getApplication()->input;

$document = JFactory::getDocument();
$document->addScript(JURI::base() . 'components/com_dpcalendar/views/event/tmpl/edit_3.js');
$document->addStyleDeclaration('.ui-datepicker { z-index: 1003 !important; }');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'event.cancel' || document.formvalidator.isValid(document.id('event-form'))) {
				<?php echo $this->form->getField('description')->save(); ?>
				Joomla.submitform(task, document.getElementById('event-form'));
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>
<h4>
<font style="color: red">
<?php if ($this->item->original_id > 0)
{
	echo sprintf(JText::_('COM_DPCALENDAR_VIEW_EVENT_GOTO_ORIGINAL'),
		JRoute::_('index.php?option=com_dpcalendar&task=event.edit&id=' . $this->item->original_id));
}?>
<?php if ($this->item->original_id == -1)
{
	echo JText::_('COM_DPCALENDAR_VIEW_EVENT_ORIGINAL_WARNING');
}?>
</font>
</h4>
<form action="<?php echo JRoute::_('index.php?option=com_dpcalendar&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" name="adminForm" id="event-form" class="form-validate">
	<div class="row-fluid">
		<div class="span10 form-horizontal">
			<ul class="nav nav-tabs">
				<li class="active">
					<a href="#general" data-toggle="tab">
						<?php echo empty($this->item->id) ? JText::_('COM_DPCALENDAR_NEW_EVENT') : JText::sprintf('COM_DPCALENDAR_EDIT_EVENT', $this->item->id); ?>
					</a>
				</li>
				<li><a href="#publishing" data-toggle="tab"><?php echo JText::_('JGLOBAL_FIELDSET_PUBLISHING');?></a></li>
				<li><a href="#attending" data-toggle="tab"><?php echo JText::_('COM_DPCALENDAR_VIEW_EVENT_ATTEND_OPTIONS');?></a></li>
				<?php
				$fieldSets = $this->form->getFieldsets('params');
				foreach ($fieldSets as $name => $fieldSet)
				{
				?>
				<li><a href="#params-<?php echo $name;?>" data-toggle="tab">
					<?php echo JText::_($fieldSet->label);?></a></li>
				<?php
				} ?>
				<li><a href="#metadata" data-toggle="tab"><?php echo JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS');?></a></li>
			</ul>
			<div class="tab-content">
				<div class="tab-pane active" id="general">
					<div class="row-fluid">
						<div class="span6">
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('title'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('title'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('catid'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('catid'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('color'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('color'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('url'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('url'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('location_ids'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('location_ids');
									if (DPCalendarHelper::getActions()->get('core.create'))
									{?>
									<a class="btn btn-micro" href="javascript:void(0);" id="location-activator"><i class="icon-new"></i></a>
									<?php
									}
									if (DPCalendarHelper::getActions()->get('core.delete'))
									{?>
									<a class="btn btn-micro" href="javascript:void(0);" id="location-remove"><i class="icon-delete"></i></a>
									<?php
									}
									if (DPCalendarHelper::getActions()->get('core.create'))
									{?>
									<div id="location-form">
										<div class="btn-toolbar">
											<div class="btn-group">
												<button type="button" class="btn btn-primary" id="location-save-button">
													<i class="icon-ok"></i> <?php echo JText::_('JSAVE') ?>
												</button>
											</div>
											<div class="btn-group">
												<button type="button" class="btn" id="location-cancel-button">
													<i class="icon-cancel"></i> <?php echo JText::_('JCANCEL') ?>
												</button>
											</div>
										</div>
										<?php $locationForm = JForm::getInstance('com_dpcalendar.location', 'location', array('control' => 'location'));
										$locationForm->setFieldAttribute('title', 'required', false);?>
										<input type="hidden" id="location_token" value="<?php echo JSession::getFormToken();?>" />
										<div class="control-group">
											<div class="control-label">
												<?php echo $locationForm->getLabel('title'); ?>
											</div>
											<div class="controls">
												<?php echo $locationForm->getInput('title'); ?>
											</div>
										</div>
										<div class="control-group">
											<div class="control-label">
												<?php echo $locationForm->getLabel('country'); ?>
											</div>
											<div class="controls">
												<?php echo $locationForm->getInput('country'); ?>
											</div>
										</div>
										<div class="control-group">
											<div class="control-label">
												<?php echo $locationForm->getLabel('province'); ?>
											</div>
											<div class="controls">
												<?php echo $locationForm->getInput('province'); ?>
											</div>
										</div>
										<div class="control-group">
											<div class="control-label">
												<?php echo $locationForm->getLabel('city'); ?>
											</div>
											<div class="controls">
												<?php echo $locationForm->getInput('city'); ?>
											</div>
										</div>
										<div class="control-group">
											<div class="control-label">
												<?php echo $locationForm->getLabel('zip'); ?>
											</div>
											<div class="controls">
												<?php echo $locationForm->getInput('zip'); ?>
											</div>
										</div>
										<div class="control-group">
											<div class="control-label">
												<?php echo $locationForm->getLabel('street'); ?>
											</div>
											<div class="controls">
												<?php echo $locationForm->getInput('street'); ?>
											</div>
										</div>
										<div class="control-group">
											<div class="control-label">
												<?php echo $locationForm->getLabel('number'); ?>
											</div>
											<div class="controls">
												<?php echo $locationForm->getInput('number'); ?>
											</div>
										</div>
										<div class="control-group">
											<div class="control-label">
												<?php echo $locationForm->getLabel('room'); ?>
											</div>
											<div class="controls">
												<?php echo $locationForm->getInput('room'); ?>
											</div>
										</div>
									</div>
									<?php
									}?>
								</div>
							</div>
						</div>
						<div class="span6">
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('start_date'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('start_date'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('end_date'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('end_date'); ?>
								</div>
							</div>
							<?php echo $this->loadTemplate('date'); ?>
						</div>
						<div class="clearfix"> </div>
						<?php echo $this->form->getInput('description'); ?>
						<div id="event-location-frame" style="width:100%;height:200px;border-style: none;"></div>
					</div>
				</div>
				<div class="tab-pane" id="publishing">
					<div class="row-fluid">
						<div class="span6">
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('alias'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('alias'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('id'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('id'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('created_by'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('created_by'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('created_by_alias'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('created_by_alias'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('created'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('created'); ?>
								</div>
							</div>
						</div>
						<div class="span6">
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('publish_up'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('publish_up'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('publish_down'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('publish_down'); ?>
								</div>
							</div>
							<?php if ($this->item->modified_by)
							{ ?>
								<div class="control-group">
									<div class="control-label">
										<?php echo $this->form->getLabel('modified_by'); ?>
									</div>
									<div class="controls">
										<?php echo $this->form->getInput('modified_by'); ?>
									</div>
								</div>
								<div class="control-group">
									<div class="control-label">
										<?php echo $this->form->getLabel('modified'); ?>
									</div>
									<div class="controls">
										<?php echo $this->form->getInput('modified'); ?>
									</div>
								</div>
							<?php
							} ?>

							<?php if (isset($this->item->version) && $this->item->version)
							{ ?>
								<div class="control-group">
									<div class="control-label">
										<?php echo $this->form->getLabel('version'); ?>
									</div>
									<div class="controls">
										<?php echo $this->form->getInput('version'); ?>
									</div>
								</div>
							<?php
							} ?>

							<?php if ($this->item->hits)
							{ ?>
								<div class="control-group">
									<div class="control-label">
										<?php echo $this->form->getLabel('hits'); ?>
									</div>
									<div class="controls">
										<?php echo $this->form->getInput('hits'); ?>
									</div>
								</div>
							<?php
							} ?>
						</div>
					</div>
				</div>
				<div class="tab-pane" id="attending">
					<fieldset>
						<?php echo $this->loadTemplate('attend'); ?>
						<?php echo $this->loadTemplate('payment'); ?>
					</fieldset>
				</div>
				<div class="tab-pane" id="metadata">
					<fieldset>
						<?php echo $this->loadTemplate('metadata'); ?>
					</fieldset>
				</div>
				<?php echo $this->loadTemplate('params'); ?>
			</div>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="return" value="<?php echo $input->getCmd('return');?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
		<div class="span2">
			<h4><?php echo JText::_('JDETAILS');?></h4>
			<hr />
			<fieldset class="form-vertical">
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('state'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('state'); ?>
					</div>
				</div>

				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('access'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('access'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('access_content'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('access_content'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('featured'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('featured'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('language'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('language'); ?>
					</div>
				</div>
			</fieldset>
		</div>
		<!-- End Sidebar -->
	</div>
</form>

<div align="center" style="clear: both">
	<?php echo sprintf(JText::_('COM_DPCALENDAR_FOOTER'), JRequest::getVar('DPCALENDAR_VERSION'));?>
</div>
