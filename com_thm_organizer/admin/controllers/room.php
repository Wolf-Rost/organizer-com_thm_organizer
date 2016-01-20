<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerControllerRoom
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class performs access checks, redirects and model function calls for data persistence
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerControllerRoom extends JControllerLegacy
{
    /**
     * Performs access checks, sets the id variable to 0, and redirects to the
     * room edit view
     *
     * @return void
     */
    public function add()
    {
        $this->setRedirect("index.php?option=com_thm_organizer&view=room_edit");
    }

    /**
     * Performs access checks and redirects to the room edit view
     *
     * @return  void
     */
    public function edit()
    {
        $cid = $this->input->post->get('cid', array(), 'array');

        // Only edit the first id in the list
        if (count($cid) > 0)
        {
            $this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=room_edit&id=$cid[0]", false));
        }
        else
        {
            $this->setRedirect("index.php?option=com_thm_organizer&view=room_edit");
        }
    }

    /**
     * Performs access checks and calls the room model's autoMergeAll function
     * before redirecting to the room manager view. No output of success or
     * failure due to the merge of multiple entries.
     *
     * @return  void
     */
    public function mergeAll()
    {
        $model = $this->getModel('room');
        $model->autoMergeAll();
 
        $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_MERGE_AUTO');
        $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=room_manager', false), $msg);
    }
 
    /**
     * Performs access checks, calls the room model's autoMerge function. Should
     * the room entries be mergeable based upon plausibility constraints this is
     * done automatically, otherwise a redirect is made to the room merge view.
     *
     * @return  void
     */
    public function mergeView()
    {
        $input = JFactory::getApplication()->input;
        $selectedRooms = $input->get('cid', array(), 'array');
        if (count($selectedRooms) == 1)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_ERROR_TOOFEW');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=room_manager', false), $msg, 'warning');
        }
        else
        {
            $model = $this->getModel('room');
            $success = $model->autoMerge();
            if ($success)
            {
                $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_MERGE_SUCCESS');
                $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=room_manager', false), $msg);
            }
            else
            {
                $input->set('view', 'room_merge');
                parent::display();
            }
        }
    }

    /**
     * Performs access checks, makes call to the models's save function, and
     * redirects to the room manager view
     *
     * @return  void
     */
    public function save()
    {
        $success = $this->getModel('room')->save();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=room_manager', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=room_manager', false), $msg, 'error');
        }
    }

    /**
     * Performs access checks, makes call to the models's merge function, and
     * redirects to the room manager view
     *
     * @return  void
     */
    public function merge()
    {
        $success = $this->getModel('room')->merge();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_MERGE_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=room_manager', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_MERGE_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=room_manager', false), $msg, 'error');
        }
    }

    /**
     * Performs access checks, makes call to the models's delete function, and
     * redirects to the room manager view
     *
     * @return  void
     */
    public function delete()
    {
        $success = $this->getModel('room')->delete();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_DELETE_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=room_manager', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_DELETE_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=room_manager', false), $msg, 'error');
        }
    }

    /**
     * Method to cancel an edit.
     *
     * @return  void
     */
    public function cancel()
    {
        $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=room_manager', false));
    }
}
