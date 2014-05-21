<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerControllerSubject
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') OR die;
jimport('joomla.application.component.controller');

/**
 * Performs access checks and user actions for events and associated resources
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerControllerSubject extends JController
{
    /**
     * edit
     *
     * performs access checks for the current user against the id of the event
     * to be edited, or content (event) creation access if id is missing or 0
     *
     * @return void
     */
    public function updateAll()
    {
        JModel::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/models');
        $model = JModel::getInstance('lsfSubject', 'THM_OrganizerModel');
        $model->updateAll();
    }
}
