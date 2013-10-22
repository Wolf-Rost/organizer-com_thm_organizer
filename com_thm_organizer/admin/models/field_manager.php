<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelField_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modellist');
require_once JPATH_COMPONENT . DS . 'assets' . DS . 'helpers' . DS . 'thm_organizerHelper.php';

/**
 * Class THM_OrganizerModelColors for component com_thm_organizer
 * Class provides methods to deal with colors
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelField_Manager extends JModelList
{
    /**
     * Constructor to set the config array and call the parent constructor
     *
     * @param   Array  $config  Configuration  (default: Array)
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array(
                    'id', 'field'
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to get all colors from the database
     *
     * @return  JDatabaseQuery
     */
    protected function getListQuery()
    {
        $dbo = JFactory::getDBO();

        // Get the filter values from the request
        $orderBy = $this->state->get('list.ordering');
        $orderDir = $this->state->get('list.direction');

        // Defailt ordering
        if ($orderBy == "")
        {
            $orderBy = "field";
            $orderDir = "ASC";
        }

        // Create the query
        $query = $dbo->getQuery(true);
        $query->select("f.id, gpuntisID, field, name, color");
        $query->from('#__thm_organizer_fields AS f');
        $query->innerJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');
        $query->order("$orderBy $orderDir");

        return $query;
    }

    /**
     * Method to get the populate state
     *
     * @param   string  $orderBy   the property by which the results should be ordered
     * @param   string  $orderDir  the direction in which results should be ordered
     *
     * @return  void
     */
    protected function populateState($orderBy = null, $orderDir = null)
    {
        $app = JFactory::getApplication('administrator');
        $context = $this->get('context');

        $orderBy = $app->getUserStateFromRequest($context . '.filter_order', 'filter_order', 'field');
        $this->setState('list.ordering', $orderBy);

        $orderDir = $app->getUserStateFromRequest($context . '.filter_order_Dir', 'filter_order_Dir', 'ASC');
        $this->setState('list.direction', $orderDir);

        $filter = $app->getUserStateFromRequest($context . '.filter', 'filter', '');
        $this->setState('filter', $filter);

        $limit = $app->getUserStateFromRequest($context . '.limit', 'limit', '');
        $this->setState('limit', $limit);

        parent::populateState($orderBy, $orderDir);
    }
}
