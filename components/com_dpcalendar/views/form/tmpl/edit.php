<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

DPCalendarHelper::loadLibrary(array('jquery' => true, 'datepicker' => true, 'bootstrap' => true, 'chosen' => true, 'maps' => true));

$document = JFactory::getDocument();
if (DPCalendarHelper::isJoomlaVersion('2.5'))
{
	$document->addScriptDeclaration("dpjQuery(document).ready(function(){
			dpjQuery('#jform_location_ids, #jform_scheduling_weekly_days, #jform_scheduling_monthly_week, #jform_scheduling_monthly_week_days, #jform_scheduling_monthly_days').chosen({
			no_results_text: '" . JText::_('COM_DPCALENDAR_VIEW_EVENT_NONE_LABEL') . "',
			placeholder_text_multiple: '" . JText::_('COM_DPCALENDAR_VIEW_EVENT_SELECTED_LABEL') . "'
	});});");
}
$document->addStyleDeclaration('.ui-datepicker, .ui-timepicker-list { font:' .
		(DPCalendarHelper::isJoomlaVersion('2.5') ? '75' : '90') . '% Arial,sans-serif; }');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');
JHtml::_('script', 'system/core.js', false, true);

if (DPCalendarHelper::isJoomlaVersion('3'))
{
	JHtml::_('behavior.tabstate');
}

$params = $this->state->get('params');

$document->addStyleSheet(JURI::base() . 'components/com_dpcalendar/views/form/tmpl/edit.css');
$document->addScript(JURI::base() . 'components/com_dpcalendar/views/form/tmpl/edit.js');

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
<?php if ($params->get('show_page_heading', 1))
{ ?>
<div class="page-header">
	<h1>
		<?php echo $this->escape($params->get('page_heading')); ?>
	</h1>
</div>
<?php
}
if ($this->item->original_id != '0')
{?>
<h4>
<span style="color: red">
<?php if ($this->item->original_id == '-1')
{
	echo JText::_('COM_DPCALENDAR_VIEW_EVENT_ORIGINAL_WARNING');
}
else if (!empty($this->item->original_id))
{
	echo sprintf(JText::_('COM_DPCALENDAR_VIEW_EVENT_GOTO_ORIGINAL'),
		DPCalendarHelper::getFormRoute($this->item->original_id, DPCalendarHelper::getEventRoute($this->item->original_id, $this->item->catid)));
}?>
</span>
</h4>
<?php
}?>
<form action="<?php echo JRoute::_('index.php?option=com_dpcalendar&layout=edit&e_id=' . $this->item->id); ?>"
	method="post" name="adminForm" id="event-form" class="form-validate dp-container">
	<div class="btn-toolbar">
		<div class="btn-group">
			<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('event.apply')">
				<i class="icon-ok"></i> <?php echo JText::_('JAPPLY') ?>
			</button>
		</div>
		<div class="btn-group">
			<button type="button" class="btn" onclick="Joomla.submitbutton('event.save')">
				<i class="icon-ok"></i> <?php echo JText::_('JSAVE') ?>
			</button>
		</div>
		<div class="btn-group">
			<button type="button" class="btn" onclick="Joomla.submitbutton('event.save2new')">
				<i class="icon-ok"></i> <?php echo JText::_('JTOOLBAR_SAVE_AND_NEW') ?>
			</button>
		</div>
		<div class="btn-group">
			<button type="button" class="btn" onclick="Joomla.submitbutton('event.cancel')">
				<i class="icon-remove-sign icon-cancel"></i> <?php echo JText::_('JCANCEL') ?>
			</button>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12 form-horizontal">
			<ul class="nav nav-tabs">
				<li class="active">
					<a href="#general" data-toggle="tab">
						<?php echo empty($this->item->id) ? JText::_('COM_DPCALENDAR_NEW_EVENT') : JText::sprintf('COM_DPCALENDAR_EDIT_EVENT', $this->item->id); ?>
					</a>
				</li>
				<li><a href="#location" data-toggle="tab" id="dp-form-location-tab"><?php echo JText::_('COM_DPCALENDAR_FIELD_LOCATION_LABEL') ?></a></li>
				<?php if (is_numeric($this->item->id) || empty($this->item->id))
				{?>
				<li><a href="#attend" data-toggle="tab"><?php echo JText::_('COM_DPCALENDAR_VIEW_EVENT_ATTEND_OPTIONS');?></a></li>
				<li><a href="#publishing" data-toggle="tab"><?php echo JText::_('JGLOBAL_FIELDSET_PUBLISHING');?></a></li>
				<li><a href="#language" data-toggle="tab"><?php echo JText::_('JFIELD_LANGUAGE_LABEL') ?></a></li>
				<li><a href="#metadata" data-toggle="tab"><?php echo JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS');?></a></li>
				<?php
				}?>
			</ul>
			<div class="tab-content">
				<div class="tab-pane active" id="general">
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
					    <?php echo $this->form->getInput('description');

					    if (!DPCalendarHelper::isFree() && is_numeric($this->item->id))
					    {?>
					    <hr/>
					    <div class="control-group">
                        	<div class="control-label">
                        		<?php echo JText::_('COM_DPCALENDAR_ATTEND')?>
                        	</div>
                        	<div class="controls">
                               <input type="checkbox" id="attend-state-checkbox" name="attend"
                               		value="1" <?php echo $this->state->get('attend_id', 0) > 0 ? 'checked="checked"' : '';?>/>
                        	</div>
                        </div>
						<div class="control-group attend-control-group">
                        	<div class="control-label">
                        		<?php echo $this->form->getLabel('name'); ?>
                        	</div>
                        	<div class="controls">
                        		<?php echo $this->form->getInput('name'); ?>
                        	</div>
                        </div>
                        <div class="control-group attend-control-group">
                        	<div class="control-label">
                        		<?php echo $this->form->getLabel('email'); ?>
                        	</div>
                        	<div class="controls">
                        		<?php echo $this->form->getInput('email'); ?>
                        	</div>
                        </div>
                        <div class="control-group attend-control-group">
                        	<div class="control-label">
                        		<?php echo $this->form->getLabel('telephone'); ?>
                        	</div>
                        	<div class="controls">
                        		<?php echo $this->form->getInput('telephone'); ?>
                        	</div>
                        </div>
                        <div class="control-group attend-control-group">
                        	<div class="control-label">
                        		<?php echo $this->form->getLabel('remind_time'); ?>
                        	</div>
                        	<div class="controls">
                        		<?php echo $this->form->getInput('remind_time'); ?>
                        		<?php echo $this->form->getInput('remind_type'); ?>
                        	</div>
                        </div>
                        <?php
                        echo $this->form->getInput('user_id');
                        echo $this->form->getInput('attend_date');
					    }
					    echo $this->captchaOutput;
                        ?>
				</div>

				<div class="tab-pane" id="location">
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('location'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('location'); ?>
						</div>
					</div>
					<?php if ($this->form->getField('location_ids'))
					{?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('location_ids'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('location_ids');
							if (DPCalendarHelper::getActions()->get('core.create'))
							{?>
							<a class="btn btn-micro" href="javascript:void(0);" id="location-activator"><i class="icon-new icon-plus-sign"></i></a>
							<?php
							}
							if (DPCalendarHelper::getActions()->get('core.delete'))
							{?>
							<a class="btn btn-micro" href="javascript:void(0);" id="location-remove"><i class="icon-delete icon-remove-sign"></i></a>
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
											<i class="icon-cancel icon-remove-sign"></i> <?php echo JText::_('JCANCEL') ?>
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
					<div class="control-group">
							<div id="event-location-frame" style="width:100%;height:200px;border-style: none;"></div>
					</div>
					<?php
                    }?>
				</div>
				<div class="tab-pane" id="attend">
					<fieldset>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('capacity'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('capacity'); ?>
								<?php echo $this->freeInformationText;?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('capacity_used'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('capacity_used'); ?>
								<?php echo $this->freeInformationText;?>
							</div>
						</div>
						<?php echo $this->loadTemplate('payment'); ?>
					</fieldset>
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
							<?php if ((!$this->item->id && $this->user->authorise('core.edit.state', 'com_dpcalendar')) || ($this->item->id && $this->user->authorise('core.edit.state', 'com_dpcalendar.category.' . $this->item->catid)))
							{ ?>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('state'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('state'); ?>
								</div>
							</div>
							<?php
							}?>
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
						</div>
					</div>
				</div>
				<div class="tab-pane" id="metadata">
					<fieldset>
						<?php echo $this->loadTemplate('metadata'); ?>
					</fieldset>
				</div>
				<div class="tab-pane" id="language">
					<div class="row-fluid">
						<div class="span6">
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('language'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('language'); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="return" value="<?php echo $this->return_page;?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</div>
</form>
