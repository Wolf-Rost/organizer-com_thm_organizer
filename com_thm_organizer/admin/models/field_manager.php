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
require_once JPATH_ROOT . '/media/com_thm_organizer/models/list.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/component.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * Class retrieves information for a filtered set of fields (of expertise).
 */
class THM_OrganizerModelField_Manager extends THM_OrganizerModelList
{
    protected $defaultOrdering = 'field';

    protected $defaultDirection = 'asc';

    /**
     * Constructor to set the config array and call the parent constructor
     *
     * @param array $config Configuration  (default: array)
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['field', 'name'];
        }

        parent::__construct($config);
    }

    /**
     * Method to get all colors from the database
     *
     * @return JDatabaseQuery
     */
    protected function getListQuery()
    {
        $shortTag = THM_OrganizerHelperLanguage::getShortTag();

        // Create the query
        $query  = $this->_db->getQuery(true);
        $select = "f.id, gpuntisID, f.field_$shortTag AS field, c.name_$shortTag AS name, c.color, ";
        $parts  = ["'index.php?option=com_thm_organizer&view=field_edit&id='", "f.id"];
        $select .= $query->concatenate($parts, "") . "AS link ";
        $query->select($select);
        $query->from('#__thm_organizer_fields AS f');
        $query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');

        $this->setSearchFilter($query, ['field_de', 'field_en', 'gpuntisID', 'color']);
        $this->setValueFilters($query, ['colorID']);
        $this->setLocalizedFilters($query, ['field']);

        $this->setOrdering($query);

        return $query;
    }

    /**
     * Function to feed the data in the table body correctly to the list view
     *
     * @return array consisting of items in the body
     */
    public function getItems()
    {
        $items  = parent::getItems();
        $return = [];

        if (empty($items)) {
            return $return;
        }

        $index = 0;

        foreach ($items as $item) {
            $return[$index]              = [];
            $return[$index]['checkbox']  = JHtml::_('grid.id', $index, $item->id);
            $return[$index]['field']     = JHtml::_('link', $item->link, $item->field);
            $return[$index]['gpuntisID'] = JHtml::_('link', $item->link, $item->gpuntisID);
            $return[$index]['colorID']   = THM_OrganizerHelperComponent::getColorField($item->name, $item->color);
            $index++;
        }

        return $return;
    }

    /**
     * Function to get table headers
     *
     * @return array including headers
     */
    public function getHeaders()
    {
        $ordering             = $this->state->get('list.ordering', $this->defaultOrdering);
        $direction            = $this->state->get('list.direction', $this->defaultDirection);
        $headers              = [];
        $headers['checkbox']  = '';
        $headers['field']     = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_NAME', 'field', $direction, $ordering);
        $headers['gpuntisID'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_GPUNTISID', 'gpuntisID', $direction,
            $ordering);
        $headers['colorID']   = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_COLOR', 'c.name', $direction,
            $ordering);

        return $headers;
    }
}
