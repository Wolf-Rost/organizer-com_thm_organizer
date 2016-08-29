<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelXML_Schedule
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

require_once 'descriptions.php';
require_once 'grids.php';
require_once 'lessons.php';
require_once 'pools.php';
require_once 'programs.php';
require_once 'rooms.php';
require_once 'subjects.php';
require_once 'teachers.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/schedule.php';

/**
 * Class enapsulating data abstraction and business logic for xml schedules
 * generated by Untis software.
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelXMLSchedule extends JModelLegacy
{
	/**
	 * array to hold error strings relating to critical data inconsistencies
	 *
	 * @var array
	 */
	public $scheduleErrors = null;

	/**
	 * array to hold warning strings relating to minor data inconsistencies
	 *
	 * @var array
	 */
	public $scheduleWarnings = null;

	/**
	 * Object containing information from the actual schedule
	 *
	 * @var object
	 */
	public $schedule = null;

	/**
	 * Creates an array with dates as indexes for the days of the given planning period
	 *
	 * @param   int $startDate the datetime upon which the school year begins
	 * @param   int $endDate   the datetime upon which the school year ends
	 *
	 * @return void
	 */
	private function initializeCalendar($startDate, $endDate)
	{
		$calendar = new stdClass;
		$grids    = $this->schedule->periods;

		// Get the maximum number of daily periods required
		$maxPeriods = 0;
		foreach ($grids AS $grid)
		{
			$gridIndexes = array_keys((array) $grid);
			if (count($gridIndexes) > $maxPeriods)
			{
				$maxPeriods = count($gridIndexes);
			}
		}

		for ($currentDT = $startDate; $currentDT <= $endDate; $currentDT = strtotime('+1 day', $currentDT))
		{
			// Create an index for the date
			$currentDate            = date('Y-m-d', $currentDT);
			$calendar->$currentDate = new stdClass;

			for ($index = 1; $index <= $maxPeriods; $index++)
			{
				$calendar->$currentDate->$index = new stdClass;
			}
		}
		$this->schedule->calendar = $calendar;
	}

	/**
	 * Saves the planning period to the corresponding table if not already existent.
	 *
	 * @param   string $ppName    the abbreviation for the planning period
	 * @param   int    $startDate the integer value of the start date
	 * @param   int    $endDate   the integer value of the end date
	 *
	 * @return  void creates database entries
	 */
	private static function savePlanningPeriod($ppName, $startDate, $endDate)
	{
		$data              = array();
		$data['startDate'] = date('Y-m-d', $startDate);
		$data['endDate']   = date('Y-m-d', $endDate);

		$table  = JTable::getInstance('planning_periods', 'thm_organizerTable');
		$exists = $table->load($data);
		if ($exists)
		{
			return $table->id;
		}

		$shortYear    = date('y', $endDate);
		$data['name'] = $ppName . $shortYear;
		$table->save($data);

		return $table->id;
	}

	/**
	 * Creates a status report based upon object error and warning messages
	 *
	 * @return  void  outputs errors to the application
	 */
	private function printStatusReport()
	{
		$app = JFactory::getApplication();
		if (count($this->scheduleErrors))
		{
			$errorMessage = JText::_('COM_THM_ORGANIZER_ERROR_HEADER') . '<br />';
			$errorMessage .= implode('<br />', $this->scheduleErrors);
			$app->enqueueMessage($errorMessage, 'error');
		}

		if (count($this->scheduleWarnings))
		{
			$app->enqueueMessage(implode('<br />', $this->scheduleWarnings), 'warning');
		}
	}

	/**
	 * Checks a given schedule in gp-untis xml format for data completeness and
	 * consistency and gives it basic structure
	 *
	 * @return  array  array of strings listing inconsistencies empty if none
	 *                 were found
	 */
	public function validate()
	{
		$input       = JFactory::getApplication()->input;
		$formFiles   = $input->files->get('jform', array(), 'array');
		$file        = $formFiles['file'];
		$xmlSchedule = simplexml_load_file($file['tmp_name']);

		$this->schedule         = new stdClass;
		$this->scheduleErrors   = array();
		$this->scheduleWarnings = array();

		// Creation Date & Time
		$creationDate = trim((string) $xmlSchedule[0]['date']);
		$this->validateDateAttribute('creationdate', $creationDate, 'CREATION_DATE', 'error');
		$creationTime = trim((string) $xmlSchedule[0]['time']);
		$this->validateTextAttribute('creationtime', $creationTime, 'CREATION_TIME', 'error');

		// School year dates
		$syStartDate = trim((string) $xmlSchedule->general->schoolyearbegindate);
		$this->validateDateAttribute('syStartDate', $syStartDate, 'SCHOOL_YEAR_START_DATE', 'error');
		$syEndDate = trim((string) $xmlSchedule->general->schoolyearenddate);
		$this->validateDateAttribute('syEndDate', $syEndDate, 'SCHOOL_YEAR_END_DATE', 'error');

		// Organizational Data
		$departmentName = trim((string) $xmlSchedule->general->header1);
		$this->validateTextAttribute('departmentname', $departmentName, 'ORGANIZATION', 'error', '/[\#\;]/');
		$semesterName      = trim((string) $xmlSchedule->general->footer);
		$validSemesterName = $this->validateTextAttribute('semestername', $semesterName, 'TERM_NAME', 'error', '/[\#\;]/');

		// Planning period start & end dates
		$startDate = trim((string) $xmlSchedule->general->termbegindate);
		$this->validateDateAttribute('startDate', $startDate, 'TERM_START_DATE');
		$endDate = trim((string) $xmlSchedule->general->termenddate);
		$this->validateDateAttribute('endDate', $endDate, 'TERM_END_DATE');

		// Checks if planning period and school year dates are consistent
		$startTimeStamp        = strtotime($startDate);
		$endTimeStamp          = strtotime($endDate);
		$invalidStart          = $startTimeStamp < strtotime($syStartDate);
		$invalidEnd            = $endTimeStamp > strtotime($syEndDate);
		$invalidPlanningPeriod = $startTimeStamp >= $endTimeStamp;
		$invalid               = ($invalidStart OR $invalidEnd OR $invalidPlanningPeriod);

		if ($invalid)
		{
			$this->scheduleErrors[] = JText::_('COM_THM_ORGANIZER_ERROR_TERM_WRONG');
		}
		elseif ($validSemesterName)
		{
			$this->schedule->planningPeriodID = THM_OrganizerHelperSchedule::getPlanningPeriodID($semesterName, $startTimeStamp, $endTimeStamp);
		}

		THM_OrganizerHelperXMLGrids::validate($this, $xmlSchedule);
		THM_OrganizerHelperXMLDescriptions::validate($this, $xmlSchedule);
		THM_OrganizerHelperXMLPrograms::validate($this, $xmlSchedule);
		THM_OrganizerHelperXMLPools::validate($this, $xmlSchedule);
		THM_OrganizerHelperXMLRooms::validate($this, $xmlSchedule);
		THM_OrganizerHelperXMLSubjects::validate($this, $xmlSchedule);
		THM_OrganizerHelperXMLTeachers::validate($this, $xmlSchedule);

		// Object longer needed (next version)
		unset($this->schedule->fields);

		$this->initializeCalendar($startTimeStamp, $endTimeStamp);
		$lessonsHelper = new THM_OrganizerHelperXMLLessons($this, $xmlSchedule);
		$lessonsHelper->validate();

		// No longer needed after lesson validation
		unset($this->schedule->methods);

		if (empty($this->scheduleErrors))
		{
			//$lessonsHelper->saveLessons();
		}

		$this->printStatusReport();

		return (count($this->scheduleErrors)) ? false : true;
	}

	/**
	 * Validates a date attribute
	 *
	 * @param   string $name     the attribute name
	 * @param   string $value    the attribute value
	 * @param   string $constant the unique text constant fragment
	 * @param   string $severity the severity of the item being inspected
	 *
	 * @return  void
	 */
	public function validateDateAttribute($name, $value, $constant, $severity = 'error')
	{
		if (empty($value))
		{
			if ($severity == 'error')
			{
				$this->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_{$constant}_MISSING");

				return;
			}

			if ($severity == 'warning')
			{
				$this->scheduleWarnings[] = JText::_("COM_THM_ORGANIZER_ERROR_{$constant}_MISSING");
			}
		}
		$this->schedule->$name = date('Y-m-d', strtotime($value));

		return;
	}

	/**
	 * Validates a text attribute
	 *
	 * @param   string $name     the attribute name
	 * @param   string $value    the attribute value
	 * @param   string $constant the unique text constant fragment
	 * @param   string $severity the severity of the item being inspected
	 * @param   string $regex    the regex to check the text against
	 *
	 * @return  void
	 */
	private function validateTextAttribute($name, $value, $constant, $severity = 'error', $regex = '')
	{
		if (empty($value))
		{
			if ($severity == 'error')
			{
				$this->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_{$constant}_MISSING");

				return false;
			}

			if ($severity == 'warning')
			{
				$this->scheduleWarnings[] = JText::_("COM_THM_ORGANIZER_ERROR_{$constant}_MISSING");
			}
		}

		if (!empty($regex) AND preg_match($regex, $value))
		{
			if ($severity == 'error')
			{
				$this->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_{$constant}_INVALID");

				return false;
			}

			if ($severity == 'warning')
			{
				$this->scheduleWarnings[] = JText::_("COM_THM_ORGANIZER_ERROR_{$constant}_INVALID");
			}
		}
		$this->schedule->$name = $value;

		return true;
	}
}
