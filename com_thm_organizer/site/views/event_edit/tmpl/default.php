<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        edit event default template
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
$showHeading = $this->item->params->get('show_page_heading', '');
$title = $this->item->params->get('page_title', '');
?>
<script type="text/javascript">
    Joomla.submitbutton = function(task)
    {
        if (document.formvalidator.isValid(document.id('item-form')))
        {
            Joomla.submitform(task, document.getElementById('item-form'));
        }
    }
</script>
<div class="organizer-form">
    <form id='item-form'
          name='adminForm'
          enctype='multipart/form-data'
          method='post'
          action='index.php?'
          class="form-horizontal">
        <?php if (!empty($showHeading)): ?>
            <h2 class="componentheading">
                <?php echo $title; ?>
            </h2>
        <?php endif; ?>
        <div class="btn-toolbar">
            <?php foreach ($this->buttons AS $button): ?>
            <div class="btn-group">
                <?php  echo $button; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="form-horizontal">
<?php
echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details'));
$sets = $this->form->getFieldSets();
foreach ($sets as $set)
{
    echo JHtml::_('bootstrap.addTab', 'myTab', $set->name, JText::_($set->label, true));
    echo $this->form->renderFieldset($set->name);
    echo JHtml::_('bootstrap.endTab');
}
echo JHtml::_('bootstrap.endTabSet');
?>
        </div>
    <?php echo JHtml::_('form.token'); ?>
    <input type='hidden' name='option' value="com_thm_organizer" />
    <input type='hidden' name='view' value="event_edit" />
    <input type='hidden' name='task' value="event.save" />
    <input type='hidden' name='Itemid' value="<?php echo JFactory::getApplication()->input->get('Itemid', 0); ?>" />
    </form>
</div>
