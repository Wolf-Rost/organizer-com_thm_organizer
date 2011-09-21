<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        view category manager
 * @description lists saved event categories and basic information about them
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('_JEXEC') or die('Restricted Access');
jimport('joomla.application.component.view');
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';

class thm_organizersViewcategory_manager extends JView
{
	
    public function display($tpl = null)
    {
        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");


        $model = $this->getModel();
        $this->categories = $model->categories;
        if(count($this->categories))$this->setIcons();
        JToolBarHelper::title( JText::_('COM_THM_ORGANIZER_CAT_TITLE' ), 'generic.png' );
        $this->access = thm_organizerHelper::isAdmin('category_manager');
        if($this->access)
        {
            $this->addToolBar();
            thm_organizerHelper::addSubmenu('category_manager');
        }

        parent::display($tpl);
    }

    /**
     * addToolBar
     *
     * generates buttons for user interaction
     */
    private function addToolBar()
    {
        JToolBarHelper::custom ('category.edit', 'new.png', 'new.png', JText::_('COM_THM_ORGANIZER_NEW'), false);
        JToolBarHelper::custom ('category.edit', 'edit.png', 'edit.png', JText::_('COM_THM_ORGANIZER_EDIT'), false);
        JToolBarHelper::deleteList( JText::_('COM_THM_ORGANIZER_CAT_DELETE_CONFIRM'), 'category.delete');
    }

    /**
     * setIcons
     *
     * sets images used for display of properties
     */
    private function setIcons()
    {
        $this->yes = JHTML::_('image', 'administrator/templates/bluestork/images/admin/tick.png',
                        JText::_( 'COM_THM_ORGANIZER_ALLOWED' ), array( 'class' => 'thm_organizer_sm_icon'));
        $this->no = JHTML::_('image', 'administrator/templates/bluestork/images/admin/publish_x.png',
                       JText::_( 'COM_THM_ORGANIZER_DENIED' ), array( 'class' => 'thm_organizer_sm_icon'));
    }
	
	
}