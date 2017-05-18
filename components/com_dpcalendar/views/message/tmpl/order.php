<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$dispatcher = JDispatcher::getInstance();
$statement = $dispatcher->trigger('onDPPaymentStatement', array($this->item));

$this->loadHelper('message');
if (empty($this->item->ordertext))
{
	$this->item->ordertext = JComponentHelper::getParams('com_dpcalendar')->get('ordertext', null);
}


// Translate message
$message = DPCalendarHelperMessage::processLanguage($this->item->ordertext);

// Parse merge tags
$message = DPCalendarHelperMessage::processPaymentTags($message, $this->item);

// Process content plugins
$message = JHTML::_('content.prepare', $message);
?>

<h1 class="componentheading">
	<?php echo $this->escape(JText::_('COM_DPCALENDAR_MESSAGE_THANKYOU')) ?>
</h1>

<?php echo $message;
foreach ($statement as $b)
{
	if ($b->status && $this->item->type = $b->type)
	{
		echo DPCalendarHelperMessage::processPaymentTags($this->item->params->get('payment_statement', $b->statement), $this->item);
	}
} ?>

<div class="dpcalendar-goback" style="padding-top: 15px">
	<p><a href="<?php echo $this->return_page; ?>"><button class="btn"><?php echo JText::_('COM_DPCALENDAR_MESSAGE_DONE') ?></button></a></p>
</div>
