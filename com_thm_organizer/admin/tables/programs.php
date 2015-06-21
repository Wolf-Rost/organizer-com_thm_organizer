<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerTablePrograms
 * @description majors table class
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.tables.assets');
/**
 * Class representing the majors table.
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerTablePrograms extends THM_CoreTableAssets
{
    /**
     * Constructor function for the class representing the majors table
     *
     * @param   JDatabaseDriver  &$dbo  A database connector object
     */
    public function __construct(&$dbo)
    {
        parent::__construct('#__thm_organizer_programs', 'id', $dbo);
    }

    /**
     * Set the table column names which are allowed to be null
     *
     * @return  boolean  true
     */
    public function check()
    {
        $nullColumns = array('fieldID');
        foreach ($nullColumns as $nullColumn)
        {
            if (!strlen($this->$nullColumn))
            {
                $this->$nullColumn = NULL;
            }
        }
        return true;
    }

    /**
     * Sets the department asset name
     *
     * @return  string
     */
    protected function _getAssetName()
    {
        return "com_thm_organizer.program.$this->id";
    }

    /**
     * Sets the parent as the component root
     *
     * @return  int  the asset id of the component root
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getAssetParentId(JTable $table = null, $id = null)
    {
        $asset = JTable::getInstance('Asset');
        $asset->loadByName("com_thm_organizer.department.$this->departmentID");
        return $asset->id;
    }
}
