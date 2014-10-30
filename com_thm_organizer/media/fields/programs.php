<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldPrograms
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.helpers.corehelper');
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/mapping.php';

/**
 * Class creates a form field for subject-degree program association
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class JFormFieldPrograms extends JFormField
{
    /**
     * @var  string
     */
    protected $type = 'programs';

    /**
     * Returns a selectionbox where stored coursepool can be chosen as a parent node
     *
     * @return Select box
     */
    public function getInput()
    {
        $this->addScript();
        $resourceID = $this->form->getValue('id');
        $contextParts = explode('.', $this->form->getName());

        // Option.View
        $resourceType = str_replace('_edit', '', $contextParts[1]);

        $ranges = THM_OrganizerHelperMapping::getResourceRanges($resourceType, $resourceID);
        $selectedPrograms = !empty($ranges)?
            THM_OrganizerHelperMapping::getSelectedPrograms($ranges) : array();
        $allPrograms = THM_OrganizerHelperMapping::getAllPrograms();

        $defaultOptions = array(array('value' => '-1', 'text' => JText::_('COM_THM_ORGANIZER_NONE')));
        $programs = array_merge($defaultOptions, $allPrograms);

        $attributes = array('multiple' => 'multiple', 'size' => '10');
        return JHTML::_("select.genericlist", $programs, "jform[programID][]", $attributes, "value", "text", $selectedPrograms);
    }

    private function addScript()
    {
?>
<script type="text/javascript" charset="utf-8">
jQuery(document).ready(function(){
    jQuery('#jformprogramID').change(function(){
        var selectedPrograms = jq('#jformprogramID').val();
        if (selectedPrograms === null)
        {
            selectedPrograms = '';
        }
        else
        {
            selectedPrograms = selectedPrograms.join(',');
        }
        var oldSelectedParents = jq('#jformparentID').val();
        if (jQuery.inArray('-1', selectedPrograms) != '-1'){
            jQuery("#jformprogramID").find('option').removeAttr("selected");
            return false;
        }
        var poolUrl = "<?php echo JURI::root(); ?>index.php?option=com_thm_organizer";
        poolUrl += "&view=pool_ajax&format=raw&task=poolDegreeOptions";
        poolUrl += "&ownID=<?php echo $this->form->getValue('id'); ?>";
        poolUrl += "&programID=" + selectedPrograms;
        poolUrl += "&languageTag=" + '<?php echo THM_CoreHelper::getLanguageShortTag(); ?>';
        jQuery.get(poolUrl, function(options){
            jQuery('#jformparentID').html(options);
            var newSelectedParents = jQuery('#jformparentID').val();
            var selectedParents = new Array();
            if (newSelectedParents !== null && newSelectedParents.length)
            {
                if (oldSelectedParents !== null && oldSelectedParents.length)
                {
                    selectedParents = jQuery.merge(newSelectedParents, oldSelectedParents);
                }
                else
                {
                    selectedParents = newSelectedParents;
                }
            }
            else if (oldSelectedParents !== null && oldSelectedParents.length)
            {
                selectedParents = oldSelectedParents;
            }
            jQuery('#jformparentID').val(selectedParents);
        });
    });
});
</script>
    <?php
    }
}
