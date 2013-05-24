<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewDegree_Program_Edit
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
require_once JPATH_COMPONENT . '/assets/helpers/thm_organizerHelper.php';

/**
 * Class loads form information for editing
 *
 * @category    Joomla.Component.Admin
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewDegree_Program_Edit extends JView
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
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl . "/components/com_thm_organizer/assets/css/thm_organizer.css");

		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
        $this->children = $this->getModel()->children;

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
		JRequest::setVar('hidemainmenu', true);
        $isNew = $this->item->id == 0;
		$title = $isNew ? JText::_("COM_THM_ORGANIZER_DGP_NEW") : JText::_("COM_THM_ORGANIZER_DGP_EDIT");
		JToolBarHelper::title($title);
		JToolBarHelper::save('degree_program.save');
		JToolBarHelper::save2new('degree_program.save2new');
        if ($isNew)
        {
            JToolBarHelper::cancel('degree_program.cancel', 'JTOOLBAR_CANCEL');
        }
        else
        {
            JToolBarHelper::save2copy('degree_program.save2copy');
            JToolBarHelper::cancel('degree_program.cancel', 'JTOOLBAR_CLOSE');
        }
	}
}
