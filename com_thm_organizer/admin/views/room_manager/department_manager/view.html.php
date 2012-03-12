<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        schedule manager view
 * @description provides a list of schedules
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('_JEXEC') or die( 'Restricted access' );
jimport( 'joomla.application.component.view' );
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';

class thm_organizersViewdepartment_manager extends JView
{
    protected $pagination;
    protected $state;
    protected $subsubbar;

    function display($tpl = null)
    {
        if(!JFactory::getUser()->authorise('core.admin'))
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));

        JHtml::_('behavior.tooltip');
        JHtml::_('behavior.multiselect');

        $model = $this->getModel();
        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");

        $this->items 			= $this->get('Items');
        $this->pagination 		= $this->get('Pagination');
        $this->state 			= $this->get('State');
        $this->institutions 	= $model->institutions;
        $this->campuses 		= (count($model->campuses))? $this->campuses = $model->campuses : array();
        $this->departments 		= $model->departments;
        
        // for sorting
        $state = $this->get('State');
        $this->orderby   = $state->get('filter_order');
        $this->direction = $state->get('filter_order_Dir');
        
        $this->addToolBar();
        if(count($this->departments))$this->addLinks();

        parent::display($tpl);
    }

    /**
     * addLinks
     *
     * creates links to the edit view for the particular schedule
     */
    private function addLinks()
    {
        $editURL = 'index.php?option=com_thm_organizer&view=department_edit&deartmentID=';
        foreach($this->items as $key => $item)
            $this->items[$key]->url = $editURL.$item->id;
    }

    /**
     * addToolBar
     *
     * creates a joomla administrative tool bar
     */
    private function addToolBar()
    {
        $title = JText::_( 'COM_THM_ORGANIZER_DEPARTMENT_MANAGER_TITLE' );
        JToolBarHelper::title($title);
        JToolBarHelper::addNew('department.add');
        JToolBarHelper::editList('department.edit');
        JToolBarHelper::deleteList(JText::_( 'COM_THM_ORGANIZER_RMM_DELETE_CONFIRM'),'department.delete');

    }
}