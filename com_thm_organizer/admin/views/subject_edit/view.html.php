<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerViewSubject_Edit
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
jimport('jquery.jquery');
/**
 * Class loadd persistent subject information into dispaly context
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewSubject_Edit extends JView
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

		// Assign the Data
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
        $this->_layout = $this->item->id == 0? 'add' : 'edit';

		// Set the toolbar
		$this->addToolBar();

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return  void
	 */
	protected function addToolBar()
	{
        $isNew = $this->item->id == 0;
		JToolBarHelper::title($isNew? JText::_('COM_THM_ORGANIZER_SUM_NEW') : JText::_('COM_THM_ORGANIZER_SUM_EDIT'));
		JToolBarHelper::apply('subject.apply', $isNew? JText::_('COM_THM_ORGANIZER_APPLY_NEW') : JText::_('COM_THM_ORGANIZER_APPLY_EDIT'));
		JToolBarHelper::save('subject.save');
		JToolBarHelper::save2new('subject.save');
		JToolBarHelper::cancel('subject.cancel', $this->item->id == 0 ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
	}
}
