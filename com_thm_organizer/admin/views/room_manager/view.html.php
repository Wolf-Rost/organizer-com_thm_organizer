<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewRoom_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
/**
 * Class provides methods to display a list of rooms
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewRoom_Manager extends JView
{
    /**
     * Method to get display
     *
     * @param   Object  $tpl  template  (default: null)
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        JHtml::_('behavior.tooltip');
        $doc = JFactory::getDocument();
        $doc->addStyleSheet($this->baseurl . '/components/com_thm_organizer/assets/css/thm_organizer.css');

        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');

        $this->addToolBar();
        parent::display($tpl);
    }

    /**
     * Method to generate buttons for user interaction
     *
     * @return  void
     */
    protected function addToolBar()
    {
        JToolBarHelper::title(JText::_('COM_THM_ORGANIZER_RMM_TOOLBAR_TITLE'), 'organizer_rooms');
        JToolBarHelper::addNew('room.add', 'JTOOLBAR_NEW');
        JToolBarHelper::editList('room.edit', 'JTOOLBAR_EDIT');
        JToolBarHelper::custom('room.mergeAll', 'merge', 'merge', 'COM_THM_ORGANIZER_MERGE_ALL', false);
        JToolBarHelper::custom('room.mergeView', 'merge', 'merge', 'COM_THM_ORGANIZER_MERGE', true);
        JToolBarHelper::deleteList('', 'room.delete', 'JTOOLBAR_DELETE');
        JToolBarHelper::divider();
        JToolBarHelper::preferences('com_thm_organizer');
    }
}
