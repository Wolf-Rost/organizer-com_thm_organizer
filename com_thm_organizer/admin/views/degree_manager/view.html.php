<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewDegree_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.list.view');

/**
 * Class provides methods to display the view degrees
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewDegree_Manager extends THM_CoreViewList
{
    /**
     * Method to generate buttons for user interaction
     *
     * @return  void
     */
    protected function addToolBar()
    {
        JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_DEGREE_MANAGER_VIEW_TITLE'), 'organizer_degrees');
        $actions = $this->getModel()->actions;

        $canCreate = ($actions->{'core.admin'} OR $actions->{'core.create'});
        if ($canCreate)
        {
            JToolbarHelper::addNew('degree.add');
        }

        $canEdit = ($actions->{'core.admin'} OR $actions->{'core.edit'});
        if ($canEdit)
        {
            JToolbarHelper::editList('degree.edit');
        }

        $canDelete = ($actions->{'core.admin'} OR $actions->{'core.delete'});
        if ($canDelete)
        {
            JToolbarHelper::deleteList(JText::_('COM_THM_ORGANIZER_ACTION_DELETE_CONFIRM'), 'degree.delete');
        }

        if ($actions->{'core.admin'})
        {
            JToolbarHelper::divider();
            JToolbarHelper::preferences('com_thm_organizer');
        }
    }
}
