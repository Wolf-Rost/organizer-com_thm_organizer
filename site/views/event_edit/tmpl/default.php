<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_thm_organizer
 * @name        create/edit appointment/event default template
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */
defined('_JEXEC') or die('Restricted access');
$event = $this->event;
$showListLink = (isset($this->listLink) and $this->listLink != "")? true : false;
$showEventLink = (isset($this->eventLink) and $this->eventLink != "")? true : false;
?>
<script language="javascript" type="text/javascript">
    var categories = new Array;

<?php
$i = 0;
foreach($this->categories as $category)
{
    echo 'categories['.$category['id'].'] = new Array( "'.mysql_real_escape_string($category['description']).'", "'.addslashes($category['display']).'",  "'.addslashes($category['contentCat']).'", "'.addslashes($category['contentCatDesc']).'", "'.addslashes($category['access']).'" );';
}
?>

/**
 * was not moved to edit_event.js because of use of joomla language support in
 * alert output
 */
Joomla.submitbutton = function(task)
{
    if (task == '') { return false; }
    else
    {
        var isValid=true;
        var action = task.split('.');
        if (action[1] != 'cancel' && action[1] != 'close')
        {
            var forms = $$('form.form-validate');
            for (var i=0;i<forms.length;i++)
            {
                if (!document.formvalidator.isValid(forms[i]))
                {
                    isValid = false;
                    break;
                }
            }
        }
        if (isValid)
        {
            var requrl = '<?php echo $this->baseurl; ?>';
            requrl = requrl + '/index.php?option=com_thm_organizer&view=booking&format=raw&eventID=';
            requrl = requrl + document.getElementById('jform_id').value + '&startdate=';
            requrl = requrl + document.getElementById('jform_startdate').value + '&enddate=';
            requrl = requrl + document.getElementById('jform_enddate').value + '&starttime=';
            requrl = requrl + document.getElementById('jform_starttime').value + '&endtime=';
            requrl = requrl + document.getElementById('jform_endtime').value + '&category=';
            requrl = requrl + document.getElementById('category').value + '&rec_type=';
            requrl = requrl + getRecType() + '&teachers=';
            requrl = requrl + getResources('teachers') + '&rooms=';
            requrl = requrl + getResources('rooms') + '&groups=';
            requrl = requrl + getResources('groups');
            var message;
            var a = new Request({
                url: requrl,
                onComplete: function(response){
                    var confirmed = true;
                    if(response){ confirmed = confirm(response); }
                    if(confirmed){ Joomla.submitform(task, document.eventForm); }
                    else{ return false; };
                }
            }).send();
        }
        else
        {
            alert('<?php echo addslashes(JText::_('COM_THM_ORGANIZER_EE_INVALID_FORM')); ?>');
            return false;
        }
    }
}
</script>
<div id="thm_organizer_ee">
    <form enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?option=com_thm_organizer'); ?>"
          method="post" name="eventForm" id="eventForm" class="eventForm form-validate">
        <div id="thm_organizer_ee_head_div">
            <span id="thm_organizer_ee_title">
                <?php echo ($this->event['id'] == 0)? JText::_('COM_THM_ORGANIZER_EE_NEW') : JText::_('COM_THM_ORGANIZER_EE_EDIT'); ?>
            </span>
            <div id="thm_organizer_ee_button_div">
                <?php if($showListLink): ?>
                <a  class="hasTip thm_organizer_action_link"
                    title="<?php echo JText::_('COM_THM_ORGANIZER_LIST_TITLE')."::".JText::_('COM_THM_ORGANIZER_LIST_DESCRIPTION');?>"
                    href="<?php echo $this->listLink ?>">
                    <span id="thm_organizer_list_span" class="thm_organizer_action_span"></span>
                    <?php echo JText::_('COM_THM_ORGANIZER_LIST'); ?>
                </a>
                <?php endif; if($showEventLink): ?>
                <a  class="hasTip thm_organizer_action_link"
                    title="<?php echo JText::_('COM_THM_ORGANIZER_EVENT_TITLE')."::".JText::_('COM_THM_ORGANIZER_EVENT_DESCRIPTION');?>"
                    href="<?php echo $this->eventLink ?>">
                    <span id="thm_organizer_event_span" class="thm_organizer_action_span"></span>
                    <?php echo JText::_('COM_THM_ORGANIZER_EVENT'); ?>
                </a>
                <?php endif; if($showListLink or $showEventLink): ?>
                <span class="thm_organizer_divider_span"></span>
                <?php endif; ?>
                <a  class="hasTip thm_organizer_action_link"
                    title="<?php echo JText::_('COM_THM_ORGANIZER_SAVE_TITLE')."::".JText::_('COM_THM_ORGANIZER_SAVE_DESCRIPTION');?>"
                    onclick="Joomla.submitbutton('events.save')">
                    <span id="thm_organizer_save_span" class="thm_organizer_action_span"></span>
                    <?php echo JText::_('COM_THM_ORGANIZER_SAVE'); ?>
                </a>
                <a  class="hasTip thm_organizer_action_link"
                    title="<?php echo JText::_('COM_THM_ORGANIZER_SAVE_NEW_TITLE')."::".JText::_('COM_THM_ORGANIZER_SAVE_NEW_DESCRIPTION');?>"
                    onclick="Joomla.submitbutton('events.save2new')">
                    <span id="thm_organizer_save_new_span" class="thm_organizer_action_span"></span>
                    <?php echo JText::_('COM_THM_ORGANIZER_SAVE_NEW'); ?>
                </a>
            </div>
        </div>
        <div id="thm_organizer_ee_name_div">
            <div class="thm_organizer_ee_label_div" >
                <?php echo $this->form->getLabel('title'); ?>
            </div>
            <div class="thm_organizer_ee_data_div" >
                <?php echo $this->form->getInput('title'); ?>
            </div>
        </div>
        <div id="thm_organizer_ee_desc_div">
            <div class="thm_organizer_ee_label_div" >
                <?php echo $this->form->getLabel('description'); ?>
            </div>
            <div class="thm_organizer_ee_data_div" >
                <?php echo $this->form->getInput('description'); ?>
            </div>
        </div>
        <div id="thm_organizer_ee_time_div">
            <table class="thm_organizer_ee_table">
                <tr>
                    <td><?php echo $this->form->getLabel('startdate'); ?></td>
                    <td><?php echo $this->form->getInput('startdate'); ?></td>
                    <td><?php echo $this->form->getLabel('starttime'); ?></td>
                    <td><?php echo $this->form->getInput('starttime'); ?></td>
                    <td>
                        <label for="rec_type_block">
                            <span class="hasTip" title="Durchgehend::Der Termin beginnt am Startdatum zur Startzeit und endet am Enddatum zur Endzeit.">
                                <?php echo JText::_('Durchgehend:'); ?>
                            </span>
                        </label>
                    </td>
                    <td>
                        <input type="radio" id="rec_type_block" name="rec_type" <?php echo $this->blockchecked;?> value="0">
                    </td>
                </tr>
                <tr>
                    <td><?php echo $this->form->getLabel('enddate'); ?></td>
                    <td><?php echo $this->form->getInput('enddate'); ?></td>
                    <td><?php echo $this->form->getLabel('endtime'); ?></td>
                    <td><?php echo $this->form->getInput('endtime'); ?></td>
                    <td>
                        <label for="rec_type_daily">
                           <span class="hasTip" title="T&auml;glich::Der Termin findet t&auml;glich zwischen Start- und Endzeit statt, an allen Tagen zwischen Start- und Enddatum.">
                                <?php echo JText::_('Täglich').":"; ?>
                            </span>
                        </label>
                    </td>
                    <td>
                        <input type="radio" id="rec_type_daily" name="rec_type" <?php echo $this->dailychecked;?> value="1">
                    </td>
                </tr>
            </table>
        </div>
        <div id="thm_organizer_ee_category_div">
            <div id="thm_organizer_ee_category_select_div">
                <div class="thm_organizer_ee_label_div" >
                    <?php echo $this->form->getLabel('categorys'); ?>
                </div>
                <div class="thm_organizer_ee_data_div" >
                    <?php echo $this->categoryselect; ?>
                </div>
            </div>
            <div id="thm_organizer_ee_event_cat_desc_div" >
                <div><?php echo $this->categories[$this->event['categoryID']]['description']; ?></div>
            </div>
            <div id="thm_organizer_ee_event_cat_disp_div" >
                <div><?php echo $this->categories[$this->event['categoryID']]['display']; ?></div>
            </div>
            <div id="thm_organizer_ee_content_cat_name_div" >
                <div><?php echo $this->categories[$this->event['categoryID']]['contentCat']; ?></div>
            </div>
            <div id="thm_organizer_ee_content_cat_desc_div" >
                <div><?php echo $this->categories[$this->event['categoryID']]['contentCatDesc']; ?></div>
            </div>
            <div id="thm_organizer_ee_content_cat_access_div" >
                <div><?php echo $this->categories[$this->event['categoryID']]['access']; ?></div>
            </div>
        </div>
        <div id="thm_organizer_ee_resource_selection_div" >
            <table class="thm_organizer_ee_table">
                <tr>
                    <td align="center">
                        <?php echo $this->form->getLabel('teachers'); ?>
                    </td>
                    <td align="center">
                        <?php echo $this->form->getLabel('rooms'); ?>
                    </td>
                    <td align="center">
                        <?php echo $this->form->getLabel('groups'); ?>
                    </td>
                </tr>
                <tr>
                    <td><?php echo $this->teacherselect; ?></td>
                    <td><?php echo $this->roomselect; ?></td>
                    <td><?php echo $this->groupselect; ?></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <?php echo $this->form->getLabel('emailNotification'); ?>
                    </td>
                    <td>
                        <?php echo $this->form->getInput('emailNotification'); ?>
                    </td>
                </tr>
            </table>
        </div>
        <?php echo $this->form->getInput('id'); ?>
        <input type='hidden' name='task' value='events.save' />
        <input type='hidden' name='schedulerCall' value='<?php echo JRequest::getBool('schedulerCall'); ?>' />
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>
