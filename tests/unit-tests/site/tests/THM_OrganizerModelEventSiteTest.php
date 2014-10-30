<?php

/**
 * @package    THM_Organizer.UnitTest
 * @author     Wolf Rost <Wolf.Rost@mni.thm.de>
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */
// Include the SUT class
require_once JPATH_BASE . '/components/com_thm_organizer/models/event.php';

/**
 * Class THM_OrganizerModelConsumptionSiteTest
 *
 * @package             com_thm_organizer
 * @coversDefaultClass  THM_OrganizerModelConsumption
 *
 * @requires            extension sqlite3
 */
class THM_OrganizerModelEventSiteTest extends TestCaseDatabase {

    /**
     * @var THM_OrganizerModelConsumption
     * @access protected
     */
    protected $object, $data, $eventid;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     * 
     * @return  null
     */
    protected function setUp() {
        parent::setup();

        $this->saveFactoryState();

        JFactory::$session = $this->getMockSession();
        JFactory::$application = $this->getMockApplication();

        $this->_db = JFactory::getDbo();
        $this->object = new THM_OrganizerModelEvent;


        $this->data['title'] = "test";
        $this->data['description'] = "test";
        $this->data['categoryID'] = 1;
        $this->data['userID'] = JFactory::getUser()->id;
        $this->data['alias'] = JApplication::stringURLSafe($this->data['title']);
        $this->data['fulltext'] = "test"; //$this->getDbo()->escape($data['description']);
        $this->data['startdate'] = date("d.m.Y");
        $this->data['starttime'] = date("H:i");
        $this->data['endtime'] = date("H:i");
        $this->data['id'] = 0;
        $this->data['contentCatID'] = 1;
        JRequest::setVar('category', 1, 'get');
        JRequest::setVar('rec_type', 0, 'get');
        //$date = JFactory::getDate();
        //var_dump($date);
        //var_dump(JFactory::getApplication()->getCfg('dbtype'));
        JRequest::setVar('jform', $this->data, 'get');
    }

    /**
     * Overrides the parent tearDown method.
     *
     * @return  void
     *
     * @see     PHPUnit_Framework_TestCase::tearDown()
     * @since   3.2
     */
    protected function tearDown() {
        $this->restoreFactoryState();

        $this->object = null;

        parent::tearDown(); // TODO: Change the autogenerated stub
    }

    /**
     * Gets the data set to be loaded into the database during setup
     *
     * @return xml dataset
     */
    protected function getDataSet() {
        $dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet(',', "'", '\\');

        //$dataSet->addTable('jos_thm_organizer_events', JPATH_TEST_DATABASE . '/jos_thm_organizer_events.csv');
        $dataSet->addTable('jos_thm_organizer_categories', JPATH_TEST_DATABASE . '/jos_thm_organizer_categories.csv');
        $dataSet->addTable('jos_content', JPATH_TEST_DATABASE . '/jos_content.csv');
        $dataSet->addTable('jos_assets', JPATH_TEST_DATABASE . '/jos_assets.csv');
        //$dataSet->addTable('jos_categories', JPATH_TEST_DATABASE . '/jos_categories.csv');

        return $dataSet;
    }

    public function testsave_added() {
        $id = 59;
        $this->data['id'] = $id;

        JRequest::setVar('jform', $this->data, 'get');

        $MockAppObj = $this->getMockApplication();

        $MockAppObj->expects($this->exactly(1))
                ->method('getCfg')
                ->with('offset')
                ->will($this->returnValue("UTC"));
        JFactory::$application = $MockAppObj;
        //$sql = "select count(*) from jos_content";

        $actual = $this->object->save();
        $this->assertEquals($id, $actual);
    }

    public function testsave_new() {
        $MockAppObj = $this->getMockApplication();

        $MockAppObj->expects($this->exactly(1))
                ->method('getCfg')
                ->with('offset')
                ->will($this->returnValue("UTC"));
        JFactory::$application = $MockAppObj;
        //$sql = "select count(*) from jos_content";


        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select('max(id)');
        $query->from('jos_content');
        //$query = "SHOW TABLE STATUS LIKE 'jos_content'";
        $dbo->setQuery((string) $query);
        $id = $dbo->loadResult();
        $actual = $this->object->save();
        $this->assertEquals($id + 1, $actual);
    }

    public function testdelete() {
        $actual = $this->object->delete(-1);
        $this->assertEquals(false, $actual);
    }

    /*
      public function testsaveNewEvent() {
      $actual = $this->invokeMethod($this->object, "saveNewEvent", array(&$this->data));
      //$actual = $this->object->saveNewEvent();
      var_dump($actual);
      $this->assertEquals(true, $actual);
      }

      public function invokeMethod(&$object, $methodName, array $parameters = array()) {
      $reflection = new \ReflectionClass(get_class($object));
      $method = $reflection->getMethod($methodName);
      $method->setAccessible(true);

      return $method->invokeArgs($object, $parameters);
      }
     */
}
