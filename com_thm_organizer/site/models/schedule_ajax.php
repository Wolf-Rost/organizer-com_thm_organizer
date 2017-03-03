<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelSchedule_Ajax
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/mapping.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/pools.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/programs.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/rooms.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/schedule.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/teachers.php';

define('SEMESTER_MODE', 1);
define('PERIOD_MODE', 2);
define('INSTANCE_MODE', 3);

/**
 * Class provides methods for retrieving program data
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelSchedule_Ajax extends JModelLegacy
{
	/**
	 * Getter method for programs
	 *
	 * @return string  a json coded array of available program objects
	 */
	public function getPrograms()
	{
		$programs = THM_OrganizerHelperPrograms::getPlanPrograms();

		$results = array();
		foreach ($programs as $program)
		{
			$name           = empty($program['name']) ? $program['ppName'] : $program['name'];
			$results[$name] = $program['id'];
		}

		return empty($results) ? '[]' : json_encode($results);
	}

	/**
	 * Getter method for pools
	 *
	 * @return string  all pools in JSON format
	 */
	public function getPools()
	{
		$selectedPrograms = JFactory::getApplication()->input->getString('programIDs');
		$programIDs       = explode(",", $selectedPrograms);
		$result           = THM_OrganizerHelperPools::getPlanPools(count($programIDs) == 1);

		return empty($result) ? '[]' : json_encode($result);
	}

	/**
	 * Getter method for room types
	 *
	 * @throws RuntimeException
	 * @return string  all room types in JSON format
	 */
	public function getRoomTypes()
	{
		$languageTag = THM_OrganizerHelperLanguage::getShortTag();

		/** @noinspection PhpIncludeInspection */
		require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/rooms.php';
		$rooms = THM_OrganizerHelperRooms::getPlanRooms();

		$relevantIDs = array();
		foreach ($rooms as $room)
		{
			if (!empty($room['typeID']))
			{
				$relevantIDs[$room['typeID']] = $room['typeID'];
			}
		}

		$query = $this->_db->getQuery(true);
		$query->select("id, name_$languageTag AS name")
			->from('#__thm_organizer_room_types AS type');

		if (!empty($relevantIDs))
		{
			$query->where("id IN ('" . implode("','", $relevantIDs) . "')");
		}

		$query->order('name');
		$this->_db->setQuery($query);

		try
		{
			// Like teachers, pools etc. roomTypes are returned as an ["name" => "id"] array instead of an object
			$result = $this->_db->loadAssocList('name', 'id');
		}
		catch (RuntimeException $exc)
		{
			return '[]';
		}

		return empty($result) ? '[]' : json_encode($result);
	}

	/**
	 * Getter method for rooms in database
	 *
	 * @throws RuntimeException
	 * @return string  all rooms in JSON format
	 */
	public function getRooms()
	{
		$departmentID = JFactory::getApplication()->input->getInt('departmentIDs');
		$typeID       = JFactory::getApplication()->input->getInt('typeID');

		$query = $this->_db->getQuery(true);
		$query->select("roo.id, roo.longname AS name")
			->from('#__thm_organizer_rooms AS roo');

		if ($departmentID != 0)
		{
			$query->leftJoin('#__thm_organizer_department_resources AS dr ON roo.id = dr.roomID');
			$query->where("dr.departmentID = $departmentID");
		}

		$query->where("roo.typeID = $typeID");
		$query->order('name');
		$this->_db->setQuery($query);

		try
		{
			// Like teachers, pools etc. rooms are returned as an ["name" => "id"] array instead of an object
			$result = $this->_db->loadAssocList('name', 'id');
		}
		catch (RuntimeException $exc)
		{
			return '[]';
		}

		return empty($result) ? '[]' : json_encode($result);
	}

	/**
	 * Getter method for teachers in database
	 *
	 * @return string  all teachers in JSON format
	 */
	public function getTeachers()
	{
		$result = THM_OrganizerHelperTeachers::getPlanTeachers();

		return empty($result) ? '[]' : json_encode($result);
	}

	/**
	 * get lessons by chosen resource
	 *
	 * @return string JSON coded lessons
	 */
	public function getLessons()
	{
		$input       = JFactory::getApplication()->input;
		$inputParams = $input->getArray();
		$inputKeys   = array_keys($inputParams);
		$parameters  = array();
		foreach ($inputKeys as $key)
		{
			if ($key == 'subjectIDs' || $key == 'poolIDs' || $key == 'teacherIDs' || $key == 'roomIDs')
			{
				$parameters[$key] = explode(',', $inputParams[$key]);
			}
		}

		$parameters['userID']          = JFactory::getUser()->id;
		$parameters['mySchedule']      = $input->getBool('mySchedule', false);
		$oneDay                        = $input->getBool('oneDay', false);
		$parameters['dateRestriction'] = $oneDay ? 'day' : 'week';
		$parameters['date']            = $input->getString('date');
		$parameters['format']          = '';
		$deltaDays                     = $input->getString('deltaDays', '14');
		$parameters['delta']           = empty($deltaDays) ? '' : date('Y-m-d', strtotime("-" . $deltaDays . " days"));

		$lessons = THM_OrganizerHelperSchedule::getLessons($parameters);

		return empty($lessons) ? '[]' : json_encode($lessons);
	}

	/**
	 * saves lessons in the personal schedule of the logged in user
	 *
	 * @return string JSON coded lessonID
	 */
	public function saveLesson()
	{
		$input    = JFactory::getApplication()->input;
		$mode     = $input->getInt('mode', PERIOD_MODE);
		$ccmID    = $input->getString('ccmID');
		$userID   = JFactory::getUser()->id;
		$lessonID = $this->getLessonIDByCcmID($ccmID);

		if (JFactory::getUser()->guest)
		{
			return '[]';
		}

		/** get configurations of selected lesson */
		$newCcmIDs = $this->getMatchingCcmIDs($mode, $ccmID);
		if (empty($newCcmIDs))
		{
			return '[]';
		}

		try
		{
			$userLessonTable = JTable::getInstance('user_lessons', 'thm_organizerTable');
			$hasUserLesson   = $userLessonTable->load(array('userID' => $userID, 'lessonID' => $lessonID));
		}
		catch (Exception $e)
		{
			return '[]';
		}

		$conditions = array(
			'userID'    => $userID,
			'lessonID'  => $lessonID,
			'user_date' => date('Y-m-d H:i:s')
		);

		if ($hasUserLesson)
		{
			$conditions['id'] = $userLessonTable->id;
			$newCcmIDs        = array_merge($newCcmIDs, json_decode($userLessonTable->configuration));
		}

		$conditions['configuration'] = $newCcmIDs;

		return (!$userLessonTable->bind($conditions) OR !$userLessonTable->store()) ? '[]' : json_encode($newCcmIDs);
	}

	/**
	 * Gets the lessonID of a ccmID
	 *
	 * @param int $ccmID primary key of calendar_configuration_map
	 *
	 * @return int | boolean
	 */
	private function getLessonIDByCcmID($ccmID)
	{
		try
		{
			$ccmTable = JTable::getInstance('calendar_configuration_map', 'thm_organizerTable');
			$hasCcm   = $ccmTable->load($ccmID);

			if (!$hasCcm)
			{
				return false;
			}

			$calendarTable = JTable::getInstance('calendar', 'thm_organizerTable');
			$hasCalendar   = $calendarTable->load($ccmTable->calendarID);
		}
		catch (Exception $e)
		{
			return false;
		}

		return (!$hasCalendar) ? false : $calendarTable->lessonID;
	}

	/**
	 * loads matching calendar_configuration_map IDs of a lesson
	 *
	 * @param int    $mode  global param like SEMESTER_MODE
	 * @param string $ccmID calendar_configuration_map ID
	 *
	 * @return array
	 */
	private function getMatchingCcmIDs($mode, $ccmID)
	{
		$query = $this->_db->getQuery(true);
		$query->select('lessonID, startTime, endTime, schedule_date, DAYOFWEEK(cal.schedule_date) AS weekday')
			->from('#__thm_organizer_calendar_configuration_map AS map')
			->innerJoin('#__thm_organizer_calendar AS cal ON cal.id = map.calendarID')
			->where("map.id = '$ccmID'")
			->where("delta != 'removed'");

		$query->order('map.id');
		$this->_db->setQuery($query);

		try
		{
			$calReference = $this->_db->loadObject();
		}
		catch (RuntimeException $e)
		{
			return array();
		}

		if (empty($calReference))
		{
			return array();
		}

		/** get other matching configurations, depending on given save mode */
		$query = $this->_db->getQuery(true);
		$query->select('map.id')
			->from('#__thm_organizer_calendar_configuration_map AS map')
			->innerJoin('#__thm_organizer_calendar AS cal ON cal.id = map.calendarID')
			->where("cal.lessonID = '$calReference->lessonID'")
			->where("delta != 'removed'");

		if ($mode !== SEMESTER_MODE)
		{
			/** lessons for same day of the week and same time */
			$query->where("cal.startTime = '$calReference->startTime'");
			$query->where("cal.endTime = '$calReference->endTime'");
			$query->where("DAYOFWEEK(cal.schedule_date) = '$calReference->weekday'");

			/** only the selected instance of lesson */
			if ($mode == INSTANCE_MODE)
			{
				$query->where("cal.schedule_date = '$calReference->schedule_date'");
			}
		}

		$query->order('map.id');
		$this->_db->setQuery($query);

		try
		{
			$configurationMappings = $this->_db->loadColumn(0);
		}
		catch (RuntimeException $e)
		{
			return array();
		}

		return $configurationMappings;
	}

	/**
	 * deletes lessons in the personal schedule of a logged in user
	 *
	 * @return string JSON coded array with lessonID and configurations or empty in case of errors
	 */
	public function deleteLesson()
	{
		$input    = JFactory::getApplication()->input;
		$mode     = $input->getInt('mode', PERIOD_MODE);
		$ccmID    = $input->getString('ccmID');
		$lessonID = $this->getLessonIDByCcmID($ccmID);
		$userID   = JFactory::getUser()->id;

		if (JFactory::getUser()->guest || empty($ccmID))
		{
			return '[]';
		}

		try
		{
			$userLessonTable = JTable::getInstance('user_lessons', 'thm_organizerTable');
			$hasUserLesson   = $userLessonTable->load(array('userID' => $userID, 'lessonID' => $lessonID));
		}
		catch (Exception $e)
		{
			return '[]';
		}

		$matchingCcmIDs = $this->getMatchingCcmIDs($mode, $ccmID);
		if (!$hasUserLesson OR empty($matchingCcmIDs))
		{
			return '[]';
		}

		/** delete a lesson completely? delete whole row in database */
		if ($mode == SEMESTER_MODE)
		{
			$success = $userLessonTable->delete($userLessonTable->id);
		}
		else
		{
			$configurations = array_flip(json_decode($userLessonTable->configuration));
			foreach ($matchingCcmIDs as $ccmID)
			{
				unset($configurations[$ccmID]);
			}

			$configurations = array_flip($configurations);
			if (empty($configurations))
			{
				$handled = $userLessonTable->delete($userLessonTable->id);
			}
			else
			{
				$conditions = array(
					'id'            => $userLessonTable->id,
					'userID'        => $userID,
					'lessonID'      => $userLessonTable->lessonID,
					'configuration' => array_values($configurations),
					'user_date'     => date('Y-m-d H:i:s')
				);
				$handled    = $userLessonTable->bind($conditions);
			}

			$success = $handled ? $userLessonTable->store() : false;
		}

		return !$success ? '[]' : json_encode($matchingCcmIDs);
	}
}
