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
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/component.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

define('DAY', 1);
define('WEEK', 2);

/**
 * Loads lesson and event data for a filtered set of rooms into the view context.
 */
class THM_OrganizerViewRoom_Overview extends JViewLegacy
{
    public $form = null;

    public $lang = null;

    public $languageSwitches = [];

    public $model = null;

    public $state = null;

    /**
     * Loads persistent data into the view context
     *
     * @param string $tpl the name of the template to load
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $this->model = $this->getModel();
        $this->state = $this->get('State');
        $this->form  = $this->get('Form');
        $this->lang  = THM_OrganizerHelperLanguage::getLanguage();

        $this->form->setValue('template', null, $this->state->get('template'));
        $this->form->setValue('date', null, $this->state->get('date'));
        $this->form->setValue('campusID', null, $this->state->get('campusID'));
        $this->form->setValue('buildingID', null, $this->state->get('buildingID'));
        $this->form->setValue('types', null, $this->state->get('types'));
        $this->form->setValue('rooms', null, $this->state->get('rooms'));

        $this->languageSwitches = THM_OrganizerHelperLanguage::getLanguageSwitches();

        $this->modifyDocument();
        parent::display($tpl);
    }

    /**
     * Adds css and javascript files to the document
     *
     * @return void  modifies the document
     */
    private function modifyDocument()
    {
        JHtml::_('jquery.ui');
        JHtml::_('behavior.tooltip');
        JHtml::_('formbehavior.chosen', 'select');
        $document = JFactory::getDocument();
        $document->setCharset("utf-8");
        $document->addScript(JUri::root() . "media/com_thm_organizer/js/room_overview.js");
        $document->addStyleSheet(JUri::root() . "media/com_thm_organizer/css/room_overview.css");
    }

    /**
     * Creates a tooltip for individual blocks
     *
     * @param string $date    the block's date
     * @param int    $blockNo the block number
     * @param string $roomNo  the room number
     *
     * @return string  formatted tooltip
     */
    public function getBlockTip($date, $blockNo, $roomNo)
    {
        $dayConstant   = strtoupper(date('l', strtotime($date)));
        $day           = $this->lang->_($dayConstant);
        $formattedDate = THM_OrganizerHelperComponent::formatDate($date);
        $dateText      = "$day $formattedDate<br />";

        $block     = $this->model->grid['periods'][$blockNo];
        $startTime = THM_OrganizerHelperComponent::formatTime($block['startTime']);
        $endTime   = THM_OrganizerHelperComponent::formatTime($block['endTime']);
        $blockText = "$blockNo. Block ($startTime - $endTime)<br />";

        $roomText = $this->lang->_('COM_THM_ORGANIZER_ROOM') . " $roomNo<br />";

        return htmlentities('<div>' . $dateText . $blockText . $roomText . '</div>');
    }

    /**
     * Creates tips for block events
     *
     * @param array $events the events taking place in the block
     *
     * @return string  the html to be used in the tooltip
     */
    public function getEventTips($events)
    {
        $tips = [];
        foreach ($events as $eventNo => $event) {
            $eventTip   = [];
            $eventTip[] = '<div>';
            $eventTip[] = $this->lang->_('COM_THM_ORGANIZER_DEPT_ORG') . ": {$event['department']}<br/>";
            $eventTip[] = $this->lang->_('COM_THM_ORGANIZER_EVENT') . ": {$event['title']}<br/>";
            $eventTip[] = $this->lang->_('COM_THM_ORGANIZER_TEACHERS') . ": {$event['teachers']}";
            if (!empty($event['comment'])) {
                $eventTip[] = '<br />';
                $eventTip[] = $this->lang->_('COM_THM_ORGANIZER_EXTRA_INFORMATION') . ": {$event['comment']}";
            }
            if (!empty($event['divTime'])) {
                $eventTip[] = '<br />';
                $eventTip[] = $this->lang->_('COM_THM_ORGANIZER_DIVERGENT_TIME') . ": {$event['divTime']}<br/>";
            }
            $eventTip[] = '</div>';
            $tips[]     = implode('', $eventTip);
        }

        return htmlentities(implode('', $tips));
    }

    /**
     * Creates a dynamically translated label.
     *
     * @param string $inputName the name of the form field whose label should be generated
     *
     * @return string the HMTL for the field label
     */
    public function getLabel($inputName)
    {
        $title  = $this->lang->_($this->form->getField($inputName)->title);
        $tip    = $this->lang->_($this->form->getField($inputName)->description);
        $return = '<label id="jform_' . $inputName . '-lbl" for="jform_' . $inputName . '" class="hasPopover"';
        $return .= 'data-content="' . $tip . '" data-original-title="' . $title . '">' . $title . '</label>';

        return $return;
    }

    /**
     * Creates a tooltip for individual blocks
     *
     * @param array $room an array with room information
     *
     * @return string  formatted tooltip
     */
    public function getRoomTip($room)
    {
        $typeText = '';
        if (!empty($room['typeName'])) {
            $typeText .= $room['typeName'];
            if (!empty($room['typeDesc'])) {
                $typeText .= ":<br /> {$room['typeDesc']}";
            }
            $typeText .= '<br />';
        }

        $capacityText = '';
        if (!empty($room['capacity'])) {
            $capacityText .= $this->lang->_('COM_THM_ORGANIZER_CAPACITY');
            $capacityText .= ": {$room['capacity']}";
            $capacityText .= '<br />';
        }

        return htmlentities('<div>' . $typeText . $capacityText . '</div>');

        return $roomTip;
    }
}
