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
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/mapping.php';

/**
 * Class creates a select box for superordinate (subject) pool mappings.
 */
class JFormFieldParentPool extends JFormField
{
    /**
     * Type
     *
     * @var    String
     */
    protected $type = 'parentpool';

    /**
     * Returns a select box in which pools can be chosen as a parent node
     *
     * @return string  the HTML for the parent pool select box
     * @throws Exception
     */
    public function getInput()
    {
        $options = $this->getOptions();
        $select  = '<select id="jformparentID" name="jform[parentID][]" multiple="multiple" size="10">';
        $select  .= implode('', $options) . '</select>';

        return $select;
    }

    /**
     * Gets pool options for a select list. All parameters come from the
     *
     * @return array  the options
     * @throws Exception
     */
    public function getOptions()
    {
        // Get basic resource data
        $resourceID   = JFactory::getApplication()->input->getInt('id', 0);
        $contextParts = explode('.', $this->form->getName());
        $resourceType = str_replace('_edit', '', $contextParts[1]);

        $mappings   = [];
        $mappingIDs = [];
        $parentIDs  = [];
        THM_OrganizerHelperMapping::setMappingData($resourceID, $resourceType, $mappings, $mappingIDs, $parentIDs);

        $options   = [];
        $options[] = '<option value="-1">' . JText::_('JNONE') . '</option>';

        if (!empty($mappings)) {
            $unwantedMappings = [];
            $programEntries   = THM_OrganizerHelperMapping::getProgramEntries($mappings);
            $programMappings  = THM_OrganizerHelperMapping::getProgramMappings($programEntries);

            // Pools should not be allowed to be placed anywhere where recursion could occur
            if ($resourceType == 'pool') {
                $children         = THM_OrganizerHelperMapping::getChildren($mappings);
                $unwantedMappings = array_merge($unwantedMappings, $mappingIDs, $children);
            }

            foreach ($programMappings as $mapping) {
                // Recursive mappings or mappings belonging to subjects should not be offered
                if (in_array($mapping['id'], $unwantedMappings) or !empty($mapping['subjectID'])) {
                    continue;
                }

                if (!empty($mapping['poolID'])) {
                    $options[] = THM_OrganizerHelperMapping::getPoolOption($mapping, $parentIDs);
                } else {
                    $options[] = THM_OrganizerHelperMapping::getProgramOption($mapping, $parentIDs, $resourceType);
                }
            }
        }

        return $options;
    }
}
