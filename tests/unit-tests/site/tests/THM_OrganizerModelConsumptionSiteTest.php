<?php
/**
 * @package    THM_Organizer.UnitTest
 * @author     Wolf Rost <Wolf.Rost@mni.thm.de>
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// Include the SUT class
require_once JPATH_BASE . '/components/com_thm_organizer/models/consumption.php';

/**
 * Class THM_OrganizerModelConsumptionSiteTest
 *
 * @package             com_thm_organizer
 * @coversDefaultClass  THM_OrganizerModelConsumption
 *
 * @requires            extension sqlite3
 */
class THM_OrganizerModelConsumptionSiteTest extends TestCaseDatabase
{
    /**
     * @var THM_OrganizerModelConsumption
     * @access protected
     */
    protected $object;
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     * 
     * @return  null
     */
    protected function setUp()
    {
        parent::setup();

        $this->saveFactoryState();

        JFactory::$application = $this->getMockCmsApp();

        $this->object = new THM_OrganizerModelConsumption;
    }

    /**
     * Overrides the parent tearDown method.
     *
     * @return  void
     *
     * @see     PHPUnit_Framework_TestCase::tearDown()
     * @since   3.2
     */
    protected function tearDown()
    {
        $this->restoreFactoryState();

        $this->object = null;

        parent::tearDown(); // TODO: Change the autogenerated stub
    }
    
    /**
     * Gets the data set to be loaded into the database during setup
     *
     * @return xml dataset
     */
    protected function getDataSet()
    {
        return $this->createXMLDataSet(JPATH_TEST_DATABASE . '/jos_thm_organizer_schedules.xml');
    }

    /**
     * Method to test the getActiveSchedules method
     *
     * @covers ::getActiveSchedules
     * @covers ::setSchedule
     *
     * @return null
     */
    public function testgetActiveSchedules()
    {
        $expectedArray = array();
        $expected = array();

        $expectedArray['id'] = "122";
        $expectedArray['name'] = "MNI - WS";

        array_push($expected, $expectedArray);

        $actual = $this->object->getActiveSchedules();

        $this->assertEquals($expected, $actual);
    }

    /**
     * Method to test the setSchedule method
     *
     * @covers ::setSchedule
     *
     * @return null
     */
    public function testsetSchedule_WithoutScheduleID()
    {
        // See expected results below
        $expected = null;

        $actual = $this->object;

        $this->assertEquals(null, $actual->schedule, "Schedule attribute should be null");

        $this->assertEquals(null, $actual->consumption, "Consumption attribute should be null");
    }

    /**
     * Method to test the setSchedule method
     *
     * @covers ::setSchedule
     *
     * @return null
     */
    public function testsetSchedule_WithScheduleID()
    {
        $expectedSchedule = file_get_contents(JPATH_TEST_STUBS . '/MNI_WS_schedule.json');

        $this->object = new THM_OrganizerModelConsumption;

        JFactory::getApplication()->input->set('activated', 122);

        $this->object->setSchedule();

        $actual = $this->object;

        // Compare the schedules as string because it is 5 times faster
        $this->assertEquals($expectedSchedule, json_encode($actual->schedule), "Schedule attribute should be equal to the expected schedule");
    }



    /**
     * Method to test the setConsumption method
     *
     * @covers ::setConsumption
     *
     * @return null
     */
    public function testsetConsumption()
    {
        $expectedRooms = array('hours' => 396.0, 'type' => "D2");

        $expectedTeachers = array();

        JFactory::getApplication()->input->set('activated', 122);

        $this->object = new THM_OrganizerModelConsumption;

        $this->object->setConsumption();

        $actual = $this->object;

        $this->assertEquals($expectedRooms, $actual->consumption['rooms']['BI']['A20.1.08'], "Consumption attribute with index 'rooms' should be equal to the expected rooms");

        $this->assertEquals($expectedTeachers, $actual->consumption['teachers'], "Consumption attribute with index 'teachers' should be an empty array");
    }

    /**
     * Method to test the getConsumptionTable method
     *
     * @covers ::getConsumptionTable
     *
     * @return null
     */
    public function testgetConsumptionTable_WithTypeRooms()
    {
        $expected = JPATH_TEST_STUBS . '/MNI_WS_Room_Table.txt';

        JFactory::getApplication()->input->set('activated', 122);

        $this->object = new THM_OrganizerModelConsumption;

        $actual = $this->object->getConsumptionTable("rooms");

        $this->assertStringEqualsFile($expected, $actual);
    }

    /**
     * Method to test the getConsumptionTable method
     *
     * @covers ::getConsumptionTable
     *
     * @return null
     */
    public function testgetConsumptionTable_WithTypeTeachers()
    {
        $expected = JPATH_TEST_STUBS . '/MNI_WS_Teacher_Table.txt';

        JFactory::getApplication()->input->set('activated', 122);

        $this->object = new THM_OrganizerModelConsumption;

        $actual = $this->object->getConsumptionTable("teachers");

        $this->assertStringEqualsFile($expected, $actual);
    }

    /**
     * Method to test the getNameArray method
     *
     * @covers ::getNameArray
     *
     * @return null
     */
    public function testgetNameArray_Degrees()
    {
        $expected = array("BI" => "Bioinformatik (B.Sc.)", "I.B" => "Informatik (B.Sc.)");

        JFactory::getApplication()->input->set('activated', 122);

        $this->object = new THM_OrganizerModelConsumption;

        $columns = array("BI", "I.B");
        $actual = $this->object->getNameArray('degrees', $columns, array('name'));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Method to test the getNameArray method
     *
     * @covers ::getNameArray
     *
     * @return null
     */
    public function testgetNameArray_Rooms()
    {
        $expected = array("A20.1.09" => "A20.1.09", "A10.2.01" => "A.2.01");

        JFactory::getApplication()->input->set('activated', 122);

        $this->object = new THM_OrganizerModelConsumption;

        $rows = array("A20.1.09", "A10.2.01");

        $actual = $this->object->getNameArray('rooms', $rows, array('longname'));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Method to test the getNameArray method
     *
     * @covers ::getNameArray
     *
     * @return null
     */
    public function testgetNameArray_Teachers()
    {
        $expected = array("KneiP" => "Kneisel, Peter", "ThelC" => "Thelen, Christopf");

        JFactory::getApplication()->input->set('activated', 122);

        $this->object = new THM_OrganizerModelConsumption;

        $rows = array("KneiP", "ThelC");

        $properties = array('surname', 'forename');
        $actual = $this->object->getNameArray('teachers', $rows, $properties, ', ');

        $this->assertEquals($expected, $actual);
    }
}