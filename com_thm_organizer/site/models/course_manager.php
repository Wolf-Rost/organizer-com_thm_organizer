<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

/**
 * Class loads forms for managing basic course attributes and participants participants, and sending circulars.
 */
class THM_OrganizerModelCourse_Manager extends JModelForm
{
    /**
     * Constructor to set up the config array and call the parent constructor
     *
     * @param array $config Configuration  (default: array)
     */
    public function __construct($config = [])
    {
        $config['filter_fields'] = [
            'name',
            'email',
            'status_date',
            'status'
        ];
        parent::__construct($config);
    }

    /**
     * Method to get the form
     *
     * @param  array   $data     Data for the form.
     * @param  boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return JForm|boolean  A JForm object on success, false on failure
     */
    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm(
            "com_thm_organizer.course_manager",
            "course_manager",
            ['control' => 'jform', 'load_data' => true]
        );

        return !empty($form) ? $form : false;
    }
}
