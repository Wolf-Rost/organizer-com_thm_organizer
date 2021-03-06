<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
JFormHelper::loadFieldClass('list');

/**
 * Class creates a select box for plan programs.
 */
class JFormFieldPlanProgramID extends JFormFieldList
{
    /**
     * @var  string
     */
    protected $type = 'planProgramID';

    /**
     * Returns a select box where resource attributes can be selected
     *
     * @return array the options for the select box
     * @throws Exception
     */
    public function getOptions()
    {
        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT ppr.id AS value, ppr.name AS text");
        $query->from("#__thm_organizer_plan_programs AS ppr");
        $query->innerJoin("#__thm_organizer_department_resources AS dr ON dr.programID = ppr.id");
        $query->order('text ASC');

        // For use in the merge view
        $selectedIDs = JFactory::getApplication()->input->get('cid', [], 'array');
        if (!empty($selectedIDs)) {
            $selectedIDs = Joomla\Utilities\ArrayHelper::toInteger($selectedIDs);
            $query->innerJoin("#__thm_organizer_plan_pools AS ppl ON ppl.programID = ppr.id");
            $query->where("ppl.id IN ( '" . implode("', '", $selectedIDs) . "' )");
        }

        // Ensures a boolean value and avoids double checking the variable because of false string positives.
        $accessRequired     = $this->getAttribute('access', 'false') == 'true';
        $departmentRestrict = $this->getAttribute('departmentRestrict', 'false') == 'true';

        $allowedDepartments = $accessRequired ? THM_OrganizerHelperComponent::getAccessibleDepartments('schedule') : [];

        if ($departmentRestrict) {

            // Direct input
            $input        = JFactory::getApplication()->input;
            $departmentID = $input->getInt('departmentID', 0);

            // Possible frontend form (jform)
            $feFormData      = $input->get('jform', [], 'array');
            $plausibleFormID = (!empty($feFormData) and !empty($feFormData['departmentID']) and is_numeric($feFormData['departmentID']));
            $departmentID    = $plausibleFormID ? $feFormData['departmentID'] : $departmentID;

            // Possible backend form (list)
            $beFormData      = $input->get('list', [], 'array');
            $plausibleFormID = (!empty($beFormData) and !empty($beFormData['departmentID']) and is_numeric($beFormData['departmentID']));
            $departmentID    = $plausibleFormID ? $beFormData['departmentID'] : $departmentID;

            $restrict = (!empty($departmentID)
                and (empty($allowedDepartments) or in_array($departmentID, $allowedDepartments)));

            if ($restrict) {
                $query->where("dr.departmentID = '$departmentID'");
            }
        }

        $dbo->setQuery($query);
        $defaultOptions = parent::getOptions();

        try {
            $values = $dbo->loadAssocList();
        } catch (Exception $exc) {
            return $defaultOptions;
        }
        $options = [];

        foreach ($values as $value) {
            if (!empty($value['value'])) {
                $options[] = JHtml::_('select.option', $value['value'], $value['text']);
            }
        }

        // An empty/default value should not be allowed in a merge view.
        if (empty($selectedIDs)) {
            $options = array_merge($defaultOptions, $options);

            return $options;
        }

        return count($options) ? $options : $defaultOptions;
    }
}
