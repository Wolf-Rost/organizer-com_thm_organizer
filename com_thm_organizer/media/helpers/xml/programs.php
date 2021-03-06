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

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/departments.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/programs.php';

/**
 * Provides functions for XML (degree) program / organizational grouping validation and modeling.
 */
class THM_OrganizerHelperXMLPrograms
{
    /**
     * Validates the resource collection node
     *
     * @param object &$scheduleModel the validating schedule model
     * @param object &$xmlObject     the xml object being validated
     *
     * @return void
     */
    public static function validate(&$scheduleModel, &$xmlObject)
    {
        if (empty($xmlObject->departments)) {
            $scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_PROGRAMS_MISSING");

            return;
        }

        $scheduleModel->newSchedule->degrees = new stdClass;

        foreach ($xmlObject->departments->children() as $degreeNode) {
            self::validateIndividual($scheduleModel, $degreeNode);
        }
    }

    /**
     * Checks whether program nodes have the expected structure and required information
     *
     * @param object &$scheduleModel the validating schedule model
     * @param object &$programNode   the degree (program/department) node to be validated
     *
     * @return void
     */
    private static function validateIndividual(&$scheduleModel, &$programNode)
    {
        $gpuntisID = trim((string)$programNode[0]['id']);
        if (empty($gpuntisID)) {
            if (!in_array(JText::_("COM_THM_ORGANIZER_ERROR_PROGRAM_ID_MISSING"), $scheduleModel->scheduleErrors)) {
                $scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_PROGRAM_ID_MISSING");
            }

            return;
        }

        $degreeID                                                  = str_replace('DP_', '', $gpuntisID);
        $scheduleModel->newSchedule->degrees->$degreeID            = new stdClass;
        $scheduleModel->newSchedule->degrees->$degreeID->gpuntisID = $degreeID;

        $degreeName = (string)$programNode->longname;
        if (!isset($degreeName)) {
            $scheduleModel->scheduleErrors[] = sprintf(JText::_('COM_THM_ORGANIZER_ERROR_PROGRAM_NAME_MISSING'),
                $degreeID);

            return;
        }

        $scheduleModel->newSchedule->degrees->$degreeID->name = $degreeName;

        $planResourceID = THM_OrganizerHelperPrograms::getPlanResourceID($scheduleModel->newSchedule->degrees->$degreeID);

        if (!empty($planResourceID)) {
            $scheduleModel->newSchedule->degrees->$degreeID->id = $planResourceID;
            THM_OrganizerHelperDepartments::setDepartmentResource($planResourceID, 'programID');
        }
    }
}
