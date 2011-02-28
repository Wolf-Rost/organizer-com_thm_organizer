<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        [joomla type]
 * @description [description of the file and/or its purpose]
 * @author      [first name] [last name] [Email]
 * @copyright   TH Mittelhessen <year>
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     [versionsnr]
 */

defined('_JEXEC') or die('Restriced Access');
jimport('joomla.application.component.model');
class thm_organizersModelschedule extends JModel
{

    function __construct(){ parent::__construct(); }
    
    /**
     * public function upload
     *
     * saves a gp-untis schedule file in the database for later use
     *
     * @return $result boolean true on success, false on db error, string listing
     * data inconsistancies
     */
    public function upload()
    {
        $fileName = $_FILES['file']['name'];
        $tmpName  = $_FILES['file']['tmp_name'];
        $schedule = simplexml_load_file($tmpName);
        $result = $this->validate(&$schedule);
        if(!isset($result['errors']))
        {
            $fp = fopen($tmpName, 'r');
            $file = fread($fp, filesize($tmpName));
            fclose($fp);
            $file = addslashes($file);

            $includedate = date('Y-m-d');
            $creationdate = trim((string)$schedule[0]['date']);
            $startdate = trim((string)$schedule->general->schoolyearbegindate);
            $enddate = trim((string)$schedule->general->schoolyearenddate);
            unset($schedule);
            $sid = JRequest::getInt('semesterID');

            $dbo = JFactory::getDBO();
            $query = $dbo->getQuery(true);
            $statement = "#__thm_organizer_schedules( filename, file, includedate, creationdate, startdate, enddate, sid, plantypeID )
                          VALUES ( '$fileName', '$file', '$includedate', '$creationdate', '$startdate', '$enddate', '$sid', 1 )";
            $query->insert($statement);
            $dbo->setQuery((string)$query );
            $dbo->query();
            if ($dbo->getErrorNum())return false;
        }
        return $result;
    }

    /**
     * public funtion validate
     *
     * checks a given schedule in gp-untis xml format for data completeness and consistency
     *
     * inconsistancies purposefully not handled: teachers missing userid, rooms missing capacity,
     * subjects missing module number
     *
     * @param $file the gpuntis xml file to be validated
     * @return $result string listing inconsistancies or boolean on successful parce
     */
    public function validate(&$file)
    {
        $erray = array();
        $warray = array();
        $creationdate = trim((string)$file[0]['date']);
        if(empty($creationdate))
        {
            $error = JText::_("Document creation date missing.");
            if(!in_array($erray,$error))$erray[] = $error;
            unset($error);
        }
        $startdate = trim((string)$file->general->schoolyearbegindate);
        if(empty($startdate)) $erray[] = JText::_("Schedule startdate is missing.");
        $enddate = trim((string)$file->general->schoolyearenddate);
        if(empty($enddate)) $erray[] = JText::_("Schedule enddate is missing.");
        $header1 = trim((string)$file->general->header1);
        if(empty($header1))
            $warray[] = JText::_("Information regarding the creating department is completely missing.");
        else
        {
            $details = explode(",", $header1);
            if(count($details) < 3) $warray[] = JText::_("Header is missing information (institution/campus/department).");
        }

        $timeperiods = array();
        $timeperiodsnode = $file->timeperiods;
        if(empty($timeperiodsnode))
            $erray[] = JText::_("Time period information is completely missing.");
        else
        {
            foreach( $timeperiodsnode->children() as $timeperiod )
            {
                $id = trim((string)$timeperiod[0]['id']);
                if(empty($id))
                {
                    $error = JText::_("One or more timeperiods are missing their id attribute.");
                    if(!in_array($error, $erray)) $erray[] = $error;
                    unset($error);
                    continue;
                }
                $day = (int)$timeperiod->day;
                if(empty($day))
                {
                    $erray[] = JText::_("Timeperiod")." $id ".JText::_("does not have a day.");
                    continue;
                }
                $period = (int)$timeperiod->period;
                if(empty($period))
                {
                    $erray[] = JText::_("Timeperiod")." $id ".JText::_("does not have a period.");
                    continue;
                }
                $timeperiods[$day][$period]['id'] = $id;
                $starttime = trim((string)$timeperiod->starttime);
                if(empty($starttime))
                    $erray[] = JText::_("Timeperiod")." $id ".JText::_("does not have a starttime.");
                else $timeperiods[$day][$period]['starttime'] = $starttime;
                $endtime = trim((string)$timeperiod->endtime);
                if(empty($endtime))
                    $erray[] = JText::_("Timeperiod")." $id ".JText::_("does not have an endtime.");
                else $timeperiods[$day][$period]['endtime'] = $endtime;
                unset($id, $day, $period, $starttime, $endtime);
            }
        }
        unset($timeperiodsnode);

        $descriptions = array();
        $descriptionsnode = $file->descriptions;
        if(empty($descriptionsnode))
            $erray[] = JText::_("Room description information is completely missing.");
        else
        {
            foreach($descriptionsnode->children() as $description)
            {
                $id = trim((string)$description[0]['id']);
                if(empty($id))
                {
                    $error = JText::_("One or more descriptions are missing their id attribute.");
                    if(!in_array($error, $erray)) $erray[] = $error;
                    unset($error);
                    continue;
                }
                $longname = trim((string)$description->longname);
                if(empty($longname))
                {
                    $erray[] =
                        JText::_("Description")." $id ".JText::_("does not have a description.");
                    continue;
                }
                else $descriptions[$id] = $longname;
                unset($id, $name);
            }
        }
        unset($descriptionsnode);

        $departments = array();
        $departmentsnode = $file->departments;
        if(empty($departmentsnode))
            $erray[] = JText::_("Department information is completely missing.");
        else
        {
            foreach($departmentsnode->children() as $dptnode)
            {
                $id = trim((string)$dptnode[0]['id']);
                if(empty($id))
                {
                    $error = JText::_("One or more departments are missing their id attributes.");
                    if(!in_array($error, $erray))$erray[] = $error;
                    unset($error);
                    continue;
                }
                $details = explode(",",trim((string)$dptnode->longname));
                if(empty($details) or count($details) == 0)
                {
                    $erray[] = JText::_("Department")." $id ".JText::_("does not have all its required information.");
                    continue;
                }
                $departments[$id] = array();
                $departments[$id]['institution'] = trim($details [0]);
                $departments[$id]['campus'] = trim($details [1]);
                $departments[$id]['department'] = trim($details [2]);
                if(isset($details [3])) $departments[$id]['subdepartment'] = trim($details [3]);
                unset($id, $details);
            }
        }
        unset($departmentsnode);

        $rooms = array();
        $roomsnode = $file->rooms;
        if(empty($roomsnode))
            $erray[] = JText::_("Room information is completely missing.");
        else
        {
            foreach($roomsnode->children() as $room)
            {
                $id = trim((string)$room[0]['id']);
                if(empty($id))
                {
                    $error = JText::_("One or more rooms are missing their id attributes.");
                    if(!in_array($error, $erray))$erray[] = $error;
                    unset($error);
                    continue;
                }
                $name = str_replace("RM_", "", $id);
                $longname = trim((string)$room->longname);
                if(empty($longname))
                    $erray[] = JText::_("Room")." $name ($id) ".JText::_("does not have a longname.");
                else $rooms[$id]['longname'] = $longname;
                $capacity = trim((int)$room->capacity);
                if(!empty($capacity)) $rooms[$id]['capacity'] = $capacity;
                $descid = trim($room->room_description[0]['id']);
                if(empty($descid))
                    $erray[] = JText::_("Room")." $name ($id) ".JText::_("does not reference a description.");
                else if(empty($descriptions[$descid]))
                    $erray[] = JText::_("Room")." $name ($id) ".JText::_("references the missing or incomplete description")." $descid.";
                else $rooms[$id]['description'] = $descriptions[$descid];
                $dptid = trim($room->room_department[0]['id']);
                if(empty($dptid))
                    $erray[] = JText::_("Room")." $name ($id) ".JText::_("does not reference a department.");
                else if(empty($departments[$dptid]) or count($departments[$dptid]) < 3)
                    $erray[] =
                        JText::_("Room")." $name ($id) ".JText::_("references the missing or incomplete department")." $dptid.";
                else $rooms['department'] = $dptid;
                unset($id, $longname, $capacity, $descid, $dptid);
            }
        }
        unset($roomsnode, $descriptions);

        $subjects = array();
        $subjectsnode = $file->subjects;
        if(empty($subjectsnode))
            $erray[] = JText::_("Subject information is completely missing.");
        else
        {
            foreach($subjectsnode->children() as $subject)
            {
                $id = (string)$subject[0]['id'];
                if(empty($id))
                {
                    $error = JText::_("One or more subjects are missing their id attributes.");
                    if(!in_array($error, $erray))$erray[] = $error;
                    unset($error);
                    continue;
                }
                $longname = trim($subject->longname);
                if(empty($longname))
                {
                    $erray[] = JText::_("Subject")." $id ".JText::_("does not have a longname.");
                    continue;
                }
                else $subjects[$id]['longname'] = $longname;
                $subjectgroup = trim($subject->subjectgroup);
                if(!empty($subjectgroup)) $subjects[$id]['subjectgroup'] = $subjectgroup;
                else $warray[] =  JText::_("Subject")." $longname ($id) ".JText::_("does not have a module number (Fachgruppe).");
                unset($id, $longname, $subjectgroup);
            }
        }
        unset($subjectsnode);

        $teachers = array();
        $teachersnode = $file->teachers;
        if(empty($teachersnode))
            $erray[] = JText::_("Teacher information is completely missing.");
        else
        {
            foreach($teachersnode->children() as $teacher)
            {
                $id = (string)$teacher[0]['id'];
                if(empty($id))
                {
                    $error = JText::_("One or more teachers are missing their id attributes.");
                    if(!in_array($error, $erray))$erray[] = $error;
                    unset($error);
                    continue;
                }
                $surname = trim((string)$teacher->surname);
                if(empty($surname))
                {
                    $erray[] = JText::_("Teacher")." $id ".JText::_("does not have a surname.");
                    continue;
                }
                else $teachers[$id]['surname'] = $surname;
                $userid = trim((string)$teacher->payrollnumber);
                if(!empty($userid)) $teachers[$id]['userid'] = $userid;
                if(empty($userid))
                    $warray[] = JText::_("Teacher")." $surname ($id) ".JText::_("does not have a user account name(Pers. Nr.).");
                else $teachers[$id]['userid'] = $userid;
                $dptid = trim((string)$teacher->teacher_department[0]['id']);
                if(empty($dptid))
                    $erray[] = JText::_("Teacher")." $surname ($id) ".JText::_("does not reference a department.");
                else if(empty($departments[$dptid]) or empty($departments[$dptid]['subdepartment']))
                    $erray[] =
                        JText::_("Teacher")." $surname ($id) ".JText::_("references the missing or subdepartment")." $dptid.";
                else $teachers[$id]['department'] = $dptid;
                unset($id, $surname, $userid, $dptid);
            }
        }
        unset($teachersnode);

        $classes = array();
        $classesnode = $file->classes;
        if(empty($classesnode))
            $erray[] = JText::_("Class(Semester) information is completely missing.");
        else
        {
            foreach($classesnode->children() as $class)
            {
                $id = (string)$class[0]['id'];
                if(empty($id))
                {
                    $error = JText::_("One or more classes(semesters) are missing their id attributes.");
                    if(!in_array($error, $erray))$erray[] = $error;
                    unset($error);
                    continue;
                }
                $longname = trim($class->longname);
                if(empty($longname))
                {
                    $erray[] = JText::_("Class")." $id ".JText::_("does not have a longname.");
                    continue;
                }
                else
                {
                    $details = explode(",", $longname);
                    if(count($details) < 2)
                        $erray[] = JText::_("The longname attribute of class")." $id ".JText::_("is missing information.");
                    else
                    {
                        $classes[$id]['major'] = $details[0];
                        $classes[$id]['semester'] = $details[1];
                    }
                }
                $teacherid = trim((string)$class->class_teacher[0]['id']);
                if(empty($teacherid))
                    $erray[] = JText::_("Class")." $longname ($id) ".JText::_("does not reference a teacher.");
                else if(empty($teachers[$teacherid]) or empty($teachers[$teacherid]['userid']))
                    $warray[] = JText::_("Class")." $longname ($id) ".JText::_("references the missing or incomplete teacher")." $teacherid.";
                else $classes[$id]['teacher'] = $teacherid;
                unset($id, $longname, $teacherid);
            }
        }
        unset($classesnode);

        $lessonsnode = $file->lessons;
        if(empty($lessonsnode))
            $erray[] = JText::_("Lesson information is completely missing.");
        else
        {
            foreach($lessonsnode->children() as $lesson)
            {
                $id = (string)$lesson[0]['id'];
                if(empty($id))
                {
                    $error = JText::_("One or more lessons are missing their id attributes.");
                    if(!in_array($error, $erray))$erray[] = $error;
                    unset($error);
                    continue;
                }
                $subjectid = (string)$lesson->lesson_subject[0]['id'];
                if(empty($subjectid))
                {
                    $erray[] = JText::_("Lesson")." $id ".JText::_("does not have an associated subject.");
                    continue;
                }
                else if(empty($subjects[$subjectid]))
                {
                    $erray[] = JText::_("Lesson")." $id ".JText::_("references the missing or incomplete subject")." $subjectid.";
                    continue;
                }
                else $name = $subjects[$subjectid]['longname'];
                $lerrorstart = JText::_("Lesson")." $name ($id) ";
                $teacherid = (string)$lesson->lesson_teacher[0]['id'];
                if(empty($teacherid))
                    $erray[] = $lerrorstart.JText::_("does not have an associated teacher.");
                else if(empty($teachers[$teacherid]))
                {
                    $erray[] = $lerrorstart.JText::_("references the missing or incomplete teacher")." $teacherid.";
                    continue;
                }
                $classids = (string)$lesson->lesson_classes[0]['id'];
                if(empty($classids))
                    $erray[] = $lerrorstart.JText::_("does not have any associated classes(semesters).");
                else
                {
                    $classids = explode(" ", $classids);
                    foreach($classids as $classid)
                    {
                        if(!key_exists($classid, $classes))
                            $erray[] = $lerrorstart.JText::_("references the missing or incomplete class")." $classid.";
                    }
                }
                $lessontype = $lesson->text1;
                if(empty($lessontype))
                    $erray[] = $lerrorstart.JText::_("does not have a type.");
                $periods = trim($lesson->periods);
                if(empty($periods))
                    $erray[] = $lerrorstart.JText::_("does not have a periods attribute.");
                $times = $lesson->times;
                $timescount = count($times->children());
                if(isset($periods) and $periods != $timescount)
                    $warray[] = $lerrorstart.JText::_("is planned for")." $periods ".JText::_("blocks, only ")." $timescount ".JText::_("has been scheduled.");
                foreach($times->children() as $instance)
                {
                    $day = (string)$instance->assigned_day;
                    if(empty($day))
                    {
                        $error = $lerrorstart.JText::_("contains a time period which does not have a day attribute.");
                        if(!in_array($error, $erray))$erray[] = $error;
                        unset($error);
                    }
                    $period = (string)$instance->assigned_period;
                    if(empty($period))
                    {
                        $error = $lerrorstart.JText::_("contains a time period which does not have a period attribute.");
                        if(!in_array($error, $erray))$erray[] = $error;
                        unset($error);
                    }
                    if(isset($day) and isset($period) and empty($timeperiods[$day][$period]))
                        $erray[] =
                            $lerrorstart.JText::_("contains a time period which is missing or incomplete. Day:")." $day ".JText::_("Period:")." $period";
                    $roomid = (string)$instance->assigned_room[0]['id'];
                    if(empty($roomid))
                    {
                        $error = $lerrorstart.JText::_("contains a time period which does not have a room attribute.");
                        if(!in_array($error, $erray))$erray[] = $error;
                        unset($error);
                    }
                    else if(!key_exists($roomid, $rooms))
                    {
                        $error = $lerrorstart.JText::_("contains a time period which references the missing or incomplete room")." $roomid.";
                        if(!in_array($error, $erray))$erray[] = $error;
                        unset($error);
                    }
                    unset($day, $period, $roomid);
                }
                unset($id, $subjectid, $name, $lerrorstart, $teacherid, $classids, $lessontype, $times);
            }
        }
        unset($lessonsnode);
        if(count($erray) > 0 or count($warray) > 0)
        {
            $inconsistencies = array();
            if(count($erray) > 0 )
            {
                $errors = "";
                foreach($erray as $k => $v)
                {
                    if($v == "") unset($erray[$k]);
                    else $errors .= "<br />".$v;
                }
                $inconsistencies['errors'] = $errors;
            }
            if(count($warray) > 0)
            {
                $warnings = "";
                foreach($warray as $k => $v)
                {
                    if($v == "") unset($warray[$k]);
                    else $warnings .= "<br />".$v;
                }
                $inconsistencies['warnings'] = $warnings;
            }
            return $inconsistencies;
        }
        else return true;
    }

    /**
     * public funtion activate
     *
     * earmarks an gp-untis schedule as being active for the given planning period
     */
    public function activate()
    {
        $semesterID = JRequest::getInt('semesterID');
        $scheduleIDs = JRequest::getVar('cid', array(), 'post', 'array');
        if(count($scheduleIDs) > 1)
            return JText::_("Only one file per type can be activated.");
        else $scheduleID = $scheduleIDs[0];

        $dbo = JFactory::getDBO();

        $query = $dbo->getQuery(true);
        $query->select("file, filename, plantypeID");
        $query->from("#__thm_organizer_schedules");
        $query->where("id = '$scheduleID'");
        $dbo->setQuery((string)$query);
        $row = $dbo->loadAssoc();
        if(empty($row))
            return JText::_("The file selected could not be found.");

        $file = $row['file'];
        $to = $row['filename'];
        $plantypeID = $row['plantypeID'];

        $query = $dbo->getQuery(true);
        $query->select("filename");
        $query->from("#__thm_organizer_schedules");
        $query->where("active IS NOT NULL");
        $query->where("sid = '$semesterID'");
        $query->where("plantypeID = '$plantypeID'");
        $dbo->setQuery((string)$query);
        $from = $dbo->loadResult();
        if(isset($from)) $oldData = $this->handleDeprecatedData($plantypeID);
        $newData = $this->getNewData(&$file, $plantypeID, $scheduleID);
        unset($file);
        if(isset($oldData) and isset($newData))
        {
            $deltaSuccess = $this->calculateDelta($oldData, $newData, $plantypeID);
            if($deltaSuccess)
            {
                $msg = JText::_('Der aktueller Stundenplan wurde ge&auml;ndert von ');
                $msg .= $from.JText::_(' auf ');
                $msg .= $to.JText::_(" ge&auml;ndert.");
                return $msg;
            }
        }
        else
        {
            if(isset($newData))
            {
                $msg = JText::_('Der aktueller Stundenplan wurde auf ');
                $msg .= $to.JText::_(" gesetzt.");
                return $msg;
            }
            else return false;
        }
    }

    /**
     * private function getNewData
     */
    private function getNewData(&$file, $planType, $scheduleID)
    {
        $dbo = JFactory::getDbo();
        $semesterID = JRequest::getInt('semesterID');
        $schedule = simplexml_load_string($file);

        $timeperiods = array();
        $timeperiodsnode = $schedule->timeperiods;
        foreach( $timeperiodsnode->children() as $timeperiod )
        {
            $id = trim((string)$timeperiod[0]['id']);
            $day = (int)$timeperiod->day;
            if(!isset($timeperiods[$day])) $timeperiods[$day] = array();
            $period = (int)$timeperiod->period;
            $timeperiods[$day][$period] = array();
            $timeperiods[$day][$period]['gpuntisID'] = $id;
            $starttime = trim((string)$timeperiod->starttime);
            $starttime = substr($starttime, 0, 2).":".substr($starttime, 2, 2).":00";
            $timeperiods[$day][$period]['starttime'] = $starttime;
            $endtime = trim((string)$timeperiod->endtime);
            $endtime = substr($endtime, 0, 2).":".substr($endtime, 2, 2).":00";
            $timeperiods[$day][$period]['endtime'] = $endtime;

            $query = $dbo->getQuery(true);
            $statement = "#__thm_organizer_periods ";
            $statement .= "( gpuntisID, semesterID, day, period, starttime, endtime ) ";
            $statement .= "VALUES ";
            $statement .= "( '$id', '$semesterID', '$day', '$period', '$starttime', '$endtime' )";
            $query->insert($statement);
            $dbo->setQuery((string)$query);
            $dbo->query();
            unset($query);

            $query = $dbo->getQuery(true);
            $query->select("id");
            $query->from("#__thm_organizer_periods");
            $query->where("gpuntisID = '$id'");
            $query->where("semesterID = '$semesterID'");
            $dbo->setQuery((string)$query);
            $timeperiods[$day][$period]['id'] = $dbo->loadResult();
            unset($id, $day, $period, $starttime, $endtime, $query);
        }
        unset($timeperiodsnode);

        $descriptions = array();
        $descriptionsnode = $schedule->descriptions;
        foreach($descriptionsnode->children() as $description)
        {
            $id = trim((string)$description[0]['id']);
            $name = trim((string)$description->longname);
            $descriptions[$id] = $name;
            unset($id, $name);
        }
        unset($descriptionsnode);

        $departments = array();
        $departmentsnode = $schedule->departments;
        foreach($departmentsnode->children() as $dptnode)
        {
            $id = (string)$dptnode[0]['id'];
            $departments[$id] = array();

            $details = explode(",",(string)$dptnode->longname);
            $name = $details[count($details) - 1];
            $departments[$id]['name'] = $name;
            $institution = $campus = $department = $subdepartment = "";
            if(isset($details [0]))$institution = trim($details [0]);
            $departments[$id]['institution'] = $institution;
            if(isset($details [1]))$campus = trim($details [1]);
            $departments[$id]['campus'] = $campus;
            if(isset($details [2]))$department = trim($details [2]);
            $departments[$id]['department'] = $department;
            if(isset($details [3]))$subdepartment = trim($details [3]);
            $departments[$id]['subdepartment'] = $subdepartment;

            $query = $dbo->getQuery(true);
            $query->select("id");
            $query->from("#__thm_organizer_departments");
            $query->where("gpuntisID = '$id'");
            $dbo->setQuery((string)$query);
            $savedID = $dbo->loadResult();
            unset($query);

            if(empty($savedID))
            {
                $query = $dbo->getQuery(true);
                $statement = "#__thm_organizer_departments
                              ( gpuntisID, name, institution, campus, department, subdepartment )
                              VALUES
                              ( '$id', '$name', '$institution', '$campus', '$department', '$subdepartment' )";
                $query->insert($statement);
                $dbo->setQuery((string)$query);
                $dbo->query();
                unset($query);

                $query = $dbo->getQuery(true);
                $query->select("id");
                $query->from("#__thm_organizer_departments");
                $query->where("gpuntisID = '$id'");
                $dbo->setQuery((string)$query);
                $departments[$id]['id'] = $dbo->loadResult();
                unset($query);
            }
            else
            {
                $departments[$id]['id'] = $savedID;
                $query = $dbo->getQuery(true);
                $query->update("#__thm_organizer_departments");
                $set = "name = '$name', institution = '$institution', campus = '$campus', ";
                $set .= "department = '$department', subdepartment = '$subdepartment' ";
                $query->set($set);
                $query->where("id = '$savedID'");
                $dbo->setQuery((string)$query);
                $dbo->query();
                unset($query, $savedID, $set);
            }
            unset($id, $details, $institution, $campus, $department, $subdepartment, $name);
        }
        unset($departmentsnode);
        
        $rooms = array();
        $roomsnode = $schedule->rooms;
        foreach($roomsnode->children() as $room)
        {
            $id = trim((string)$room[0]['id']);
            $name = str_replace("RM_","",$id);
            $rooms[$id]['name'] = $name;
            $longname = trim((string)$room->longname);
            $rooms[$id]['longname'] = $longname;
            $capacity = (int)$room->capacity;
            if(empty($capacity)) $capacity = 0;
            $rooms[$id]['capacity'] = $capacity;
            $descid = trim((string)$room->room_description[0]['id']);
            $description = $descriptions[$descid];
            $rooms[$id]['description'] = $description;
            $departmentID = trim((string)$room->room_department[0]['id']);
            $rooms[$id]['department'] = $departmentID;
            $departmentID = $departments[$departmentID]['id'];

            $query = $dbo->getQuery(true);
            $query->select("id");
            $query->from("#__thm_organizer_rooms");
            $query->where("gpuntisID = '$id'");
            $dbo->setQuery((string)$query);
            $savedID = $dbo->loadResult();
            unset($query);

            if(empty($savedID))
            {
                $query = $dbo->getQuery(true);
                $statement = "#__thm_organizer_rooms
                              ( gpuntisID, name, alias, capacity, type, departmentID )
                              VALUES
                              ( '$id', '$name', '$longname', '$capacity', '$description', '$departmentID' )";
                $query->insert($statement);
                $dbo->setQuery((string)$query);
                $dbo->query();
                unset($query);

                $query = $dbo->getQuery(true);
                $query->select("id");
                $query->from("#__thm_organizer_rooms");
                $query->where("gpuntisID = '$id'");
                $dbo->setQuery((string)$query);
                $rooms[$id]['id'] = $dbo->loadResult();
                unset($query);
            }
            else
            {
                $rooms[$id]['id'] = $savedID;
                $query = $dbo->getQuery(true);
                $query->update("#__thm_organizer_rooms");
                $set = "name = '$name', alias = '$longname', capacity = '$capacity', ";
                $set .= "type = '$description', departmentID = '$departmentID'";
                $query->set($set);
                $query->where("id = '$savedID'");
                $dbo->setQuery((string)$query);
                $dbo->query();
                unset($query, $savedID, $set);
            }
            unset($id, $name, $longname, $capacity, $descid, $description, $dptid);
        }
        unset($roomsnode, $descriptions);

        $subjects = array();
        $subjectsnode = $schedule->subjects;
        foreach($subjectsnode->children() as $subject)
        {
            $id = trim((string)$subject[0]['id']);
            $subjects[$id]['gpuntisID'] = $id;
            $name = str_replace("SU_","",$id);
            $subjects[$id]['name'] = $name;
            $alias = trim((string)$subject->longname);
            $subjects[$id]['longname'] = $alias;
            $moduleID = trim($subject->subjectgroup);
            if(empty($moduleID)) $moduleID = '';
            $subjects[$id]['moduleID'] = $moduleID;

            $query = $dbo->getQuery(true);
            $query->select("id");
            $query->from("#__thm_organizer_subjects");
            $query->where("gpuntisID = '$id' ");
            $dbo->setQuery((string)$query);
            $savedID = $dbo->loadResult();
            unset($query);

            if(empty($savedID))
            {
                $query = $dbo->getQuery(true);
                $statement = "#__thm_organizer_subjects ( gpuntisID, name, alias, moduleID )
                              VALUES ( '$id', '$name', '$alias', '$moduleID' )";
                $query->insert($statement);
                $dbo->setQuery((string)$query);
                $dbo->query();
                unset($query);

                $query = $dbo->getQuery(true);
                $query->select("id");
                $query->from("#__thm_organizer_subjects");
                $query->where("gpuntisID = '$id' ");
                $dbo->setQuery((string)$query);
                $subjects[$id]['id'] = $dbo->loadResult();
                unset($query);
            }
            else
            {
                $subjects[$id]['id'] = $savedID;
                $query = $dbo->getQuery(true);
                $query->update("#__thm_organizer_subjects");
                $set = "name = '$name', alias = '$alias', moduleID = '$moduleID'";
                $query->set($set);
                $query->where("id = '$savedID'");
                $dbo->setQuery((string)$query);
                $dbo->query();
                unset($query, $savedID, $set);
            }
            unset($id, $name, $alias, $moduleID);
        }
        unset($subjectsnode);

        $teachers = array();
        $teachersnode = $schedule->teachers;
        foreach($teachersnode->children() as $teacher)
        {
            $id = trim((string)$teacher[0]['id']);
            $name = trim((string)$teacher->surname);
            $dptid = trim((string)$teacher->teacher_department[0]['id']);
            $teachers[$id]['name'] = $name;
            $teachers[$id]['department'] = $dptid;
            $departmentID = $departments[$dptid]['id'];
            $userID = trim((string)$teacher->payrollnumber);
            if(empty($userID)) $userID = '';
            $teachers[$id]['userID'] = $userID;

            $query = $dbo->getQuery(true);
            $query->select("id");
            $query->from("#__thm_organizer_teachers");
            $query->where("gpuntisID = '$id'");
            $dbo->setQuery((string)$query);
            $savedID = $dbo->loadResult();
            unset($query);

            if(empty($savedID))
            {
                $query = $dbo->getQuery(true);
                $statement = "#__thm_organizer_teachers ( gpuntisID, name, manager, departmentID )
                              VALUES ( '$id', '$name', '$userID', '$departmentID' )";
                $query->insert($statement);
                $dbo->setQuery((string)$query);
                $dbo->query();
                unset($query);

                $query = $dbo->getQuery(true);
                $query->select("id");
                $query->from("#__thm_organizer_teachers");
                $query->where("gpuntisID = '$id'");
                $dbo->setQuery((string)$query);
                $teachers[$id]['id']= $dbo->loadResult();
            }
            else
            {
                $teachers[$id]['id']= $savedID;
                $query = $dbo->getQuery(true);
                $query->update("#__thm_organizer_teachers");
                $set = "name = '$name', manager = '$userID', departmentID = '$departmentID'";
                $query->set($set);
                $query->where("id = '$savedID'");
                $dbo->setQuery((string)$query);
                $dbo->query();
                unset($query, $savedID, $set);
            }
            unset($id, $name, $userid, $dptid);
        }
        unset($teachersnode);

        $classes = array();
        $classesnode = $schedule->classes;
        foreach($classesnode->children() as $class)
        {
            $id = trim((string)$class[0]['id']);
            $name = str_replace("CL_", "", $id);
            $longname = trim((string)$class->longname);
            $details = explode(",", $longname);
            $major = $details[0];
            $semester = $details[1];
            $teacherID = trim((string)$class->class_teacher[0]['id']);
            $classes[$id]['name'] = $name;
            $classes[$id]['alias'] = $longname;
            $classes[$id]['major'] = $major;
            $classes[$id]['semester'] = $semester;
            $classes[$id]['teacher'] = $teacherID;
            if(isset($teachers[$teacherID]) and isset($teachers[$teacherID]['userID']))
                $manager = $teachers[$teacherID]['userID'];
            else $manager = "";

            $query = $dbo->getQuery(true);
            $query->select("id");
            $query->from("#__thm_organizer_classes");
            $query->where("gpuntisID = '$id'");
            $dbo->setQuery((string)$query);
            $savedID = $dbo->loadResult();
            unset($query);

            if(empty($savedID))
            {
                $query = $dbo->getQuery(true);
                $statement = "#__thm_organizer_classes
                              ( gpuntisID, name, alias, manager, semester, major )
                              VALUES
                              ( '$id', '$name', '$longname', '$manager', '$semester', '$major' )";
                $query->insert($statement);
                $dbo->setQuery((string)$query);
                $dbo->query();
                unset($query);

                $query = $dbo->getQuery(true);
                $query->select("id");
                $query->from("#__thm_organizer_classes");
                $query->where("gpuntisID = '$id'");
                $dbo->setQuery((string)$query);
                $classes[$id]['id'] = $dbo->loadResult();
                unset($query);
            }
            else
            {
                $classes[$id]['id'] = $savedID;
                $query = $dbo->getQuery(true);
                $query->update("#__thm_organizer_classes");
                $set = "name = '$name', alias = '$longname',  manager = '$manager',
                        semester = '$semester',  major = '$major'";
                $query->set($set);
                $query->where("id = '$savedID'");
                $dbo->setQuery((string)$query);
                $dbo->query();
                unset($query, $savedID, $set);
            }
            unset($id, $name, $longname, $manager, $semester, $major, $details);
        }
        unset($classesnode);

        $lessons = array();
        $lessonsnode = $schedule->lessons;
        if(empty($lessonsnode))return false;
        else
        {
            foreach($lessonsnode->children() as $lesson)
            {
                $id = trim((string)$lesson[0]['id']);
                $id = substr($id, 0, strlen($id) - 2);
                $subjectID = trim((string)$lesson->lesson_subject[0]['id']);
                $subjectID = $subjects[$subjectID]['id'];
                $lessontype = trim((string)$lesson->text1);

                $query = $dbo->getQuery(true);
                $query->select("id");
                $query->from("#__thm_organizer_lessons");
                $query->where("semesterID = '$semesterID'");
                $query->where("gpuntisID = '$id'");
                $dbo->setQuery((string)$query);
                $lessonID = $dbo->loadResult();

                if(empty($lessonID))
                {
                    $query = $dbo->getQuery(true);
                    $statement = "#__thm_organizer_lessons
                                  ( gpuntisID, subjectID, semesterID, plantypeID,  type )
                                  VALUES
                                  ( '$id', '$subjectID', '$semesterID','1', '$lessontype' )";
                    $query->insert($statement);
                    $dbo->setQuery((string)$query);
                    $dbo->query();
                    unset($query);

                    $query = $dbo->getQuery(true);
                    $query->select("id");
                    $query->from("#__thm_organizer_lessons");
                    $query->where("semesterID = '$semesterID'");
                    $query->where("gpuntisID = '$id'");
                    $dbo->setQuery((string)$query);
                    $lessonID = $dbo->loadResult();
                }

                $teacherID = trim((string)$lesson->lesson_teacher[0]['id']);
                $teacherID = $teachers[$teacherID]['id'];

                $query = $dbo->getQuery(true);
                $query->select("COUNT(*)");
                $query->from("#__thm_organizer_lesson_teachers");
                $query->where("lessonID = '$lessonID'");
                $query->where("teacherID = '$teacherID'");
                $dbo->setQuery((string)$query);
                $countTeachers = $dbo->loadResult();

                if($countTeachers == 0)
                {
                    $query = $dbo->getQuery(true);
                    $statement = "#__thm_organizer_lesson_teachers ( lessonID, teacherID )
                                  VALUES ( '$lessonID', '$teacherID' )";
                    $query->insert($statement);
                    $dbo->setQuery((string)$query);
                    $dbo->query();
                    unset($query);
                }

                $classIDs = trim((string)$lesson->lesson_classes[0]['id']);
                $classIDs = explode(" ", $classIDs);
                foreach($classIDs as $k => $v)
                    $classIDs[$k] = $classes[$v]['id'];

                foreach($classIDs as $classID)
                {
                    $query = $dbo->getQuery(true);
                    $query->select("COUNT(*)");
                    $query->from("#__thm_organizer_lesson_classes");
                    $query->where("lessonID = '$lessonID'");
                    $query->where("classID = '$classID'");
                    $dbo->setQuery((string)$query);
                    $countClasses = $dbo->loadResult();

                    if($countClasses == 0)
                    {
                        $query = $dbo->getQuery(true);
                        $statement = "#__thm_organizer_lesson_classes ( lessonID, classID )
                                      VALUES ( '$lessonID', '$classID' )";
                        $query->insert($statement);
                        $dbo->setQuery((string)$query);
                        $dbo->query();
                        unset($query);
                    }
                }

                $times = $lesson->times;
                foreach($times->children() as $instance)
                {
                    $day = (int)$instance->assigned_day;
                    $period = (int)$instance->assigned_period;
                    $periodID = $timeperiods[$day][$period]['id'];
                    $roomID = trim((string)$instance->assigned_room[0]['id']);
                    $roomID = $rooms[$roomID]['id'];

                    $query = $dbo->getQuery(true);
                    $query->select("COUNT(*)");
                    $query->from("#__thm_organizer_lesson_times");
                    $query->where("lessonID = '$lessonID'");
                    $query->where("roomID = '$roomID'");
                    $query->where("periodID = '$periodID'");
                    $dbo->setQuery((string)$query);
                    $countLTimes = $dbo->loadResult();

                    if($countLTimes == 0)
                    {
                        $query = $dbo->getQuery(true);
                        $statement = "#__thm_organizer_lesson_times ( lessonID, roomID, periodID )
                                      VALUES ( '$lessonID', '$roomID', '$periodID' )";
                        $query->insert($statement);
                        $dbo->setQuery((string)$query);
                        $dbo->query();
                    }

                    $tpID = $timeperiods[$day][$period]['gpuntisID'];
                    if(!isset($lessons[$id]))$lessons[$id] = array();
                    $lessons[$id][$tpID] = array();
                    $lessons[$id][$tpID]['source'] = $planType;
                    $lessons[$id][$tpID]['subjectID'] = $subjectID;

                    if(!isset($lessons[$id][$tpID]['classIDs']))
                        $lessons[$id][$tpID]['classIDs'] = array();
                    foreach($classIDs as $classID)
                    {
                        if(!in_array($classID, $lessons[$id][$tpID]['classIDs']))
                            $lessons[$id][$tpID]['classIDs'][] = $classID;
                    }

                    $query = $dbo->getQuery(true);
                    $query->select("teacherID");
                    $query->from("#__thm_organizer_lesson_teachers");
                    $query->where("lessonID = '$lessonID'");
                    $dbo->setQuery((string)$query);
                    $lessonTeachers = $dbo->loadResultArray();
                    
                    if(!isset($lessons[$id][$tpID]['teacherIDs']))
                        $lessons[$id][$tpID]['teacherIDs'] = array();
                    foreach($lessonTeachers as $lessonTeacher)
                    {
                        if(!in_array($lessonTeacher, $lessons[$id][$tpID]['teacherIDs']))
                            $lessons[$id][$tpID]['teacherIDs'][] = $lessonTeacher;
                    }

                    $query = $dbo->getQuery(true);
                    $query->select("roomID");
                    $query->from("#__thm_organizer_lesson_times");
                    $query->where("lessonID = '$lessonID'");
                    $query->where("periodID = '$periodID'");
                    $dbo->setQuery((string)$query);
                    $lessonRooms = $dbo->loadResultArray();

                    if(!isset($lessons[$id][$tpID]['roomIDs']))
                        $lessons[$id][$tpID]['roomIDs'] = array();
                    foreach($lessonRooms as $lessonRoom)
                    {
                        if(!in_array($roomID, $lessons[$id][$tpID]['roomIDs']))
                            $lessons[$id][$tpID]['roomIDs'][] = $lessonRoom;
                    }

                    unset($countLT, $day, $period, $periodID, $roomID, $tpID);
                }
                unset($id, $subjectid, $name, $teacherid, $classGPIDs, $classIDs, $lessontype, $periods, $times);
            }
        }
        unset($lessonsnode);

        $date = date('Y-m-d');
        $query = $dbo->getQuery(true);
        $query->update("#__thm_organizer_schedules");
        $query->set("active = '$date'");
        $query->where("id = '$scheduleID'");
        $dbo->setQuery((string)$query);
        $dbo->query();
        
        return $lessons;
    }

    /**
     * private function getDeprecatedData
     *
     * retrieves lesson data for the currently active schedule/semester/type
     * and encapsulates it in a php array structure, then removes it.
     */
    private function handleDeprecatedData($plantypeID)
    {
        $dbo = JFactory::getDbo();
        $semesterID = JRequest::getInt('semesterID');
        $lessons = array();

        $query = $dbo->getQuery(true);
        $query->select("id, gpuntisID");
        $query->from("#__thm_organizer_lessons");
        $query->where("plantypeID = '$plantypeID'");
        $query->where("semesterID = '$semesterID'");
        $dbo->setQuery($query);
        $lessonIDs = $dbo->loadResultArray(0);
        $assocIDs = $dbo->loadAssocList();

        //string containin all lesson ids formatted for sql queries
        $lessonIDString = "'".implode("', '", $lessonIDs)."'";
        
        $lessonGPIDs = array();
        foreach($assocIDs as $assoc)
            $lessonGPIDs[$assoc['id']] = $assoc['gpuntisID'];

        foreach($lessonIDs as $lessonID)
        {
            $gpid = $lessonGPIDs[$lessonID];

            $query = $dbo->getQuery(true);
            $query->select("subjectID");
            $query->from("#__thm_organizer_lessons");
            $query->where("id = '$lessonID'");
            $dbo->setQuery($query);
            $subjectID = $dbo->loadResult();
                        
            $query = $dbo->getQuery(true);
            $query->select("roomID, gpuntisID");
            $query->from("#__thm_organizer_lesson_times AS lt");
            $query->leftJoin("#__thm_organizer_periods AS p on p.id = lt.periodID");
            $query->where("lessonID = '$lessonID'");
            $dbo->setQuery((string)$query);
            $periods = $dbo->loadAssocList();
            
            foreach($periods as $period)
            {
                $tpID = $period['gpuntisID'];

                if(!isset($lessons[$gpid])) $lessons[$gpid] = array();
                if(!isset($lessons[$gpid][$tpID])) $lessons[$gpid][$tpID] = array();
                $lessons[$gpid][$tpID]['source'] = $plantypeID;
                $lessons[$gpid][$tpID]['subjectID'] = $subjectID;

                if(!isset($lessons[$gpid][$tpID]['roomIDs']))
                    $lessons[$gpid][$tpID]['roomIDs'] = array();
                if(!in_array($period['roomID'], $lessons[$gpid][$tpID]['roomIDs']))
                    $lessons[$gpid][$tpID]['roomIDs'][] = $period['roomID'];

                $query = $dbo->getQuery(true);
                $query->select("teacherID");
                $query->from("#__thm_organizer_lesson_teachers");
                $query->where("lessonID = '$lessonID'");
                $dbo->setQuery((string)$query);
                $lessons[$gpid][$tpID]['teacherIDs'] = $dbo->loadResultArray();

                $query = $dbo->getQuery(true);
                $query->select("classID");
                $query->from("#__thm_organizer_lesson_classes");
                $query->where("lessonID = '$lessonID'");
                $dbo->setQuery((string)$query);
                $lessons[$gpid][$tpID]['classIDs'] = $dbo->loadResultArray();
            }
        }

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_periods");
        $query->where("semesterID = '$semesterID'");
        $dbo->setQuery((string)$query);
        $dbo->query();

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_lessons");
        $query->where("id IN ( $lessonIDString )");
        $dbo->setQuery((string)$query);
        $dbo->query();

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_lesson_teachers");
        $query->where("lessonID IN ( $lessonIDString )");
        $dbo->setQuery((string)$query);
        $dbo->query();

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_lesson_classes");
        $query->where("lessonID IN ( $lessonIDString )");
        $dbo->setQuery((string)$query);
        $dbo->query();

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_lesson_times");
        $query->where("lessonID IN ( $lessonIDString )");
        $dbo->setQuery((string)$query);
        $dbo->query();

        $query = $dbo->getQuery(true);
        $query->update("#__thm_organizer_schedules");
        $query->set("active = NULL");
        $query->where("plantypeID = '$plantypeID'");
        $query->where("sid = '$semesterID'");
        $dbo->setQuery((string)$query);
        $dbo->query();

        return $lessons;
    }

    /**
     * private funtion delta
     *
     * creates a change set between the currently active schedule and the schedule to
     * become active, and saves this data as a json string in the structure used by
     * the scheduler rich internet application
     */
    private function calculateDelta($oldData, $newData, $plantypeID)
    {
        $delta = array();
        foreach($newData as $lessonKey => $lessonValue)
        {
            if(!key_exists($lessonKey, $oldData))//lesson did not exist
            {
                $delta[$lessonKey] = $lessonValue;
                $delta[$lessonKey]['changes'] = 'new';
                unset($newData[$lessonKey]);
            }
            else//lesson occurs in both plans
            {
                foreach($lessonValue as $periodKey => $periodValue)
                {
                    if(!key_exists($periodKey, $oldData[$lessonKey]))
                    {
                        $delta[$lessonKey][$periodKey] = $periodValue;
                        $delta[$lessonKey][$periodKey]['changes'] = 'new';
                        unset($newData[$lessonKey][$periodKey]);
                    }
                    else
                    {
                        foreach($periodValue['classIDs'] as $newClassKey => $newClassValue)
                        {
                            if(in_array($newClassValue, $oldData[$lessonKey][$periodKey]['classIDs']))
                                continue;
                            else
                            {
                                if(!isset($delta[$lessonKey][$periodKey]))
                                    $delta[$lessonKey][$periodKey] = $periodValue;
                                if(!isset($delta[$lessonKey][$periodKey]['changes']))
                                    $delta[$lessonKey][$periodKey]['changes'] = array();
                                if(!isset($delta[$lessonKey][$periodKey]['changes']['classIDs']))
                                    $delta[$lessonKey][$periodKey]['changes']['classIDs'] = array();
                                if(!key_exists($newClassValue, $delta[$lessonKey][$periodKey]['changes']['classIDs']))
                                    $delta[$lessonKey][$periodKey]['changes']['classIDs'][$newClassValue] = "new";
                            }
                        }
                        foreach($oldData[$lessonKey][$periodKey]['classIDs'] as $oldClassKey => $oldClassValue)
                        {
                            if(in_array($oldClassValue, $periodValue['classIDs']))
                                continue;
                            else
                            {
                                if(!isset($delta[$lessonKey][$periodKey]))
                                    $delta[$lessonKey][$periodKey] = $periodValue;
                                if(!isset($delta[$lessonKey][$periodKey]['changes']))
                                    $delta[$lessonKey][$periodKey]['changes'] = array();
                                if(!isset($delta[$lessonKey][$periodKey]['changes']['classIDs']))
                                    $delta[$lessonKey][$periodKey]['changes']['classIDs'] = array();
                                if(!key_exists($oldClassValue, $delta[$lessonKey][$periodKey]['changes']['classIDs']))
                                    $delta[$lessonKey][$periodKey]['changes']['classIDs'][$oldClassValue] = "removed";
                            }
                        }
                        foreach($periodValue['teacherIDs'] as $newTeacherKey => $newTeacherValue)
                        {
                            if(in_array($newTeacherValue, $oldData[$lessonKey][$periodKey]['teacherIDs']))
                                continue;
                            else
                            {
                                if(!isset($delta[$lessonKey][$periodKey]))
                                    $delta[$lessonKey][$periodKey] = $periodValue;
                                if(!isset($delta[$lessonKey][$periodKey]['changes']))
                                    $delta[$lessonKey][$periodKey]['changes'] = array();
                                if(!isset($delta[$lessonKey][$periodKey]['changes']['teacherIDs']))
                                    $delta[$lessonKey][$periodKey]['changes']['teacherIDs'] = array();
                                if(!key_exists($newTeacherValue, $delta[$lessonKey][$periodKey]['changes']['teacherIDs']))
                                    $delta[$lessonKey][$periodKey]['changes']['teacherIDs'][$newTeacherValue] = "new";
                            }
                        }
                        foreach($oldData[$lessonKey][$periodKey]['teacherIDs'] as $oldTeacherKey => $oldTeacherValue)
                        {
                            if(in_array($oldTeacherValue, $periodValue['teacherIDs']))
                                continue;
                            else
                            {
                                if(!isset($delta[$lessonKey]))
                                    $delta[$lessonKey][$periodKey] = $periodValue;
                                if(!isset($delta[$lessonKey][$periodKey]['changes']))
                                    $delta[$lessonKey][$periodKey]['changes'] = array();
                                if(!isset($delta[$lessonKey][$periodKey]['changes']['teacherIDs']))
                                    $delta[$lessonKey][$periodKey]['changes']['teacherIDs'] = array();
                                if(!key_exists($oldTeacherValue, $delta[$lessonKey][$periodKey]['changes']['teacherIDs']))
                                    $delta[$lessonKey][$periodKey]['changes']['teacherIDs'][$oldTeacherValue] = "removed";
                            }
                        }
                        foreach($periodValue['roomIDs'] as $newRoomKey => $newRoomValue)
                        {
                            if(in_array($newRoomValue, $oldData[$lessonKey][$periodKey]['roomIDs']))
                                continue;
                            else
                            {
                                if(!isset($delta[$lessonKey][$periodKey]))
                                    $delta[$lessonKey][$periodKey] = $periodValue;
                                if(!isset($delta[$lessonKey][$periodKey]['changes']))
                                    $delta[$lessonKey][$periodKey]['changes'] = array();
                                if(!isset($delta[$lessonKey][$periodKey]['changes']['roomIDs']))
                                    $delta[$lessonKey][$periodKey]['changes']['roomIDs'] = array();
                                if(!key_exists($newRoomValue, $delta[$lessonKey][$periodKey]['changes']['roomIDs']))
                                    $delta[$lessonKey][$periodKey]['changes']['roomIDs'][$newRoomValue] = "new";
                            }
                        }
                        foreach($oldData[$lessonKey][$periodKey]['roomIDs'] as $oldRoomKey => $oldRoomValue)
                        {
                            if(in_array($oldRoomValue, $periodValue['roomIDs']))
                                continue;
                            else
                            {
                                if(!isset($delta[$lessonKey][$periodKey]))
                                    $delta[$lessonKey][$periodKey] = $periodValue;
                                if(!isset($delta[$lessonKey][$periodKey]['changes']))
                                    $delta[$lessonKey][$periodKey]['changes'] = array();
                                if(!isset($delta[$lessonKey][$periodKey]['changes']['roomIDs']))
                                    $delta[$lessonKey][$periodKey]['changes']['roomIDs'] = array();
                                if(!key_exists($oldRoomValue, $delta[$lessonKey][$periodKey]['changes']['roomIDs']))
                                    $delta[$lessonKey][$periodKey]['changes']['roomIDs'][$oldRoomValue] = "removed";
                            }
                        }
                    }
                    unset($oldData[$lessonKey][$periodKey]);
                }
                if(count($oldData[$lessonKey]) > 0)
                {
                    foreach($oldData[$lessonKey] as $periodKey => $periodValue)
                    {
                        $delta[$lessonKey][$periodKey] = $periodValue;
                        $delta[$lessonKey][$periodKey]['changes'] = 'removed';
                        unset($oldData[$lessonKey]);
                    }
                }
            }
            unset($oldData[$lessonKey]);
        }
        if(count($oldData) > 0)
        {
            foreach($oldData as $lessonKey => $lessonValue)
            {
                $delta[$lessonKey] = $lessonValue;
                $delta[$lessonKey]['changes'] = 'removed';
                unset($oldData[$lessonKey]);
            }
        }

        //json_encode does not handle umlaute properly
        $malformedjsondelta = json_encode($delta);
        $jsondelta = str_replace('\u00d6', 'Ö',
                     str_replace('\u00f6', 'ö',
                     str_replace('\u00c4', 'Ä',
                     str_replace('\u00e4', 'ä',
                     str_replace('\u00dc', 'Ü',
                     str_replace('\u00fc', 'ü',
                     str_replace('\u00df', 'ß', $malformedjsondelta)))))));

        $dbo = JFactory::getDbo();
        $semesterID = JRequest::getInt('semesterID');
        $currenttime = time();

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_user_schedules");
        $query->where("username = 'delta$plantypeID'");
        $query->where("sid = '$semesterID'");
        $dbo->setQuery( (string) $query );
        $dbo->query();

        $query = $dbo->getQuery(true);
        $statement = "#__thm_organizer_user_schedules (username, data, created, sid ) ";
        $statement .= "VALUES ( 'delta$plantypeID', '$jsondelta', '$currenttime', '$semesterID' ) ";
        $query->insert($statement);
        $dbo->setQuery( (string) $query );
        $dbo->query();
        
        if ($dbo->getErrorNum())return false;
        else return true;
    }

    /**
    * public function deactivate
    *
    * sets the current active schedule to inactive. this entails the deletion
    * of the delta, and the removal of schedule specific data from the db.
    */
    public function deactivate($id = null)
    {
        //todo work around double code in handle deprecated by calling this function for the deletes
        $dbo = JFactory::getDBO();
        $semesterID = JRequest::getInt('semesterID');
        $scheduleIDs = JRequest::getVar('cid', array(), 'post', 'array');

        foreach($scheduleIDs as $scheduleID)
        {
            $query = $dbo->getQuery(true);
            $query->select("plantypeID");
            $query->from("#__thm_organizer_schedules");
            $query->where("id = '$scheduleID'");
            $dbo->setQuery((string)$query);
            $plantypeID = $dbo->loadResult();
            unset($query);

            $query = $dbo->getQuery(true);
            $query->select("DISTINCT ( id )");
            $query->from("#__thm_organizer_lessons");
            $query->where("semesterID = '$semesterID'");
            $query->where("plantypeID = '$plantypeID'");
            $dbo->setQuery((string)$query);
            $lessonIDs = $dbo->loadResultArray();
            unset($query);

            $lessonIDs = "'".implode("', '", $lessonIDs)."'";

            $query = $dbo->getQuery(true);
            $query->delete();
            $query->from("#__thm_organizer_lessons");
            $query->where("semesterID = '$semesterID'");
            $query->where("plantypeID = '$plantypeID'");
            $dbo->setQuery((string)$query);
            $dbo->query();
            unset($query);

            $query = $dbo->getQuery(true);
            $query->delete();
            $query->from("#__thm_organizer_lesson_teachers");
            $query->where("lessonID IN ( $lessonIDs )");
            $dbo->setQuery((string)$query);
            $dbo->query();
            unset($query);

            $query = $dbo->getQuery(true);
            $query->delete();
            $query->from("#__thm_organizer_lesson_classes");
            $query->where("lessonID IN ( $lessonIDs )");
            $dbo->setQuery((string)$query);
            $dbo->query();
            unset($query);

            $query = $dbo->getQuery(true);
            $query->select("DISTINCT ( periodID )");
            $query->from("#__thm_organizer_lesson_times");
            $query->where("lessonID IN ( $lessonIDs )");
            $dbo->setQuery((string)$query);
            $periodIDs = $dbo->loadResultArray();
            unset($query);

            $periodIDs = "'".implode("', '", $periodIDs)."'";

            $query = $dbo->getQuery(true);
            $query->delete();
            $query->from("#__thm_organizer_lesson_times");
            $query->where("lessonID IN ( $lessonIDs )");
            $dbo->setQuery((string)$query);
            $dbo->query();
            unset($query, $lessonIDs);
            
            $query = $dbo->getQuery(true);
            $query->delete();
            $query->from("#__thm_organizer_periods");
            $query->where("id IN ( $periodIDs )");
            $dbo->setQuery((string)$query);
            $dbo->query();
            unset($query, $lessonIDs);

            $query = $dbo->getQuery(true);
            $query->update("#__thm_organizer_schedules");
            $query->set("active = NULL");
            $query->where("active IS NOT NULL");
            $query->where("sid = '$semesterID'");
            $query->where("plantypeID = '$plantypeID'");
            $dbo->setQuery( $query );
            $dbo->query();

            $query = $dbo->getQuery(true);
            $query->delete();
            $query->from("#__thm_organizer_user_schedules");
            $query->where("username = 'delta$plantypeID'");
            $query->where("sid = '$semesterID'");
            $dbo->setQuery( $query );
            $dbo->query();
        }
        if($dbo->getErrorNum())return false;
        else return true;
    }

    /**
     * public function delete
     *
     * removes the selected schedules
     */
    public function delete()
    {
        $scheduleIDs = JRequest::getVar('cid', array(), 'post', 'array');

 	$dbo = & JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select('id');
        $query->from("#__thm_organizer_schedules");
        $query->where("id IN ( '".implode("', '", $scheduleIDs)."' )");
        $query->where("active != NULL");
        $dbo->setQuery((string) $query);
        $activeIDs = $dbo->loadResultArray();
        unset($query);

        if(!empty($activeIDs))
            foreach($activeIDS as $active)
                $this->deactivate ($active);

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_schedules");
        $query->where("id IN ( '".implode("', '", $scheduleIDs)."' )");
        $dbo->setQuery((string)$query);
        $dbo->query();

        if ($dbo->getErrorNum())return false;
        else return true;
    }

    /**
     * public function updateText
     *
     * Adds or updates the description of the schedule.
     */
    function edit_comment()
    {
        $scheduleIDs = JRequest::getVar('cid', array(), 'post', 'array');
        if(!empty($scheduleIDs))
        {
            $dbo = & JFactory::getDBO();
            foreach($scheduleIDs as $scheduleID)
            {
                $description = JRequest::getString("description$scheduleID");
                if(isset($description))
                {
                    $query = $dbo->getQuery(true);
                    $query->update("#__thm_organizer_schedules");
                    $query->set("description = '$description'");
                    $query->where("id = '$scheduleID'");
                    $dbo->setQuery((string)$query);
                    $dbo->query();
                    unset($query, $description);
                }                
            }
        }
        if ($dbo->getErrorNum())return false;
        else return true;
    }
}
?>
