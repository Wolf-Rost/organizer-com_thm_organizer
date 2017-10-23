<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2017 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/views/list.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/prep_course.php';

/**
 * Class which loads data into the view output context
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewCourse_Manager extends JViewLegacy
{
	public $lang;

	public $languageSwitches;

	public $items;

	public $courseAuth = false;

	public $form;

	public $course;

	public $sortDirection;

	public $sortColumn;

	public $curCap;

	public $capacity;

	public $isAdmin;

	public $dateText = "";

	/**
	 * Method to get display
	 *
	 * @param Object $tpl template  (default: null)
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->modifyDocument();

		$this->lang = THM_OrganizerHelperLanguage::getLanguage();

		$user = JFactory::getUser();

		if (empty(JFactory::getApplication()->input->get("lessonID")))
		{
			JError::raiseError(401, $this->lang->_('COM_THM_ORGANIZER_MESSAGE_NO_ACCESS_VIEW'));
		}

		if (!empty($user->id))
		{
			$this->items  = $this->get('Items');
			$this->form   = $this->get('Form');
			$this->course = THM_OrganizerHelperPrep_Course::getCourse();

			$dates = THM_OrganizerHelperPrep_Course::getDates();

			if (!empty($dates))
			{
				$dateFormat = JComponentHelper::getParams('com_thm_organizer')->get('dateFormat', 'd.m.Y');
				$start      = JHtml::_('date', $dates[0]["schedule_date"], $dateFormat);
				$end        = JHtml::_('date', end($dates)["schedule_date"], $dateFormat);

				$this->dateText = "$start - $end";
			}

			$state = $this->get('State');

			$this->sortDirection = $state->get('list.direction');
			$this->sortColumn    = $state->get('list.ordering');

			if (!empty($this->course))
			{
				$this->courseAuth = THM_OrganizerHelperPrep_Course::authSubjectTeacher($this->course["subjectID"]);
				$this->curCap     = THM_OrganizerHelperPrep_Course::getRegisteredStudents($this->course["id"]);
			}

			$params = ['view' => 'course_manager', 'id' => empty($this->course) ? 0 : $this->course["id"]];

			$this->languageSwitches = THM_OrganizerHelperLanguage::getLanguageSwitches($params);

			$this->capacity = (!empty($this->course["lessonP"]) ? $this->course["lessonP"] : $this->course["subjectP"]);
		}

		$this->isAdmin = $user->authorise('core.admin');
		$authorized    = ($this->isAdmin OR $this->courseAuth);

		if (!$authorized)
		{
			JError::raiseError(401, $this->lang->_('COM_THM_ORGANIZER_MESSAGE_NO_ACCESS_VIEW'));

			return;
		}

		parent::display($tpl);
	}

	/**
	 * Adds resource files to the document
	 *
	 * @return  void
	 */
	private function modifyDocument()
	{
		JHtml::_('bootstrap.tooltip');
		JHtml::_('behavior.framework', true);

		JFactory::getDocument()->addStyleSheet(JUri::root() . '/media/com_thm_organizer/css/prep_course.css');
	}
}