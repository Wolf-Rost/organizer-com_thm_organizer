<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerTemplateCurriculumPanel
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class renders curriculum panel information
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerTemplateCurriculumPanel
{
    /**
     * Generates the HTML output for a panel element
     *
     * @param   object  &$pool  the element to be rendered
     * @param   string  $type   the pool display type
     *
     * @return  void  generates HTML output
     *
     * @SuppressWarnings("PMD.NPathComplexity")
     */
    public static function render(&$pool, $type = 'modal')
    {
        $displayHead = ($type == 'modal')? 'hidden' : 'shown';
        $dontDisplayDesc = (empty($pool->enable_desc) OR empty($pool->description));
        echo '<div id="panel-' . $pool->mapping . '" class="' . $type . '-panel ' . $displayHead . '">';
        self::renderHead($pool, $type);
        if (!$dontDisplayDesc)
        {
            echo '<div class="' . $type . '-panel-description">' . $pool->description . '</div>';

        }
        self::renderBody($pool, $type);
        echo '</div>';
    }

    /**
     * Generates the HTML output for a panel element header
     *
     * @param   object  &$pool  the element to be rendered
     * @param   string  $type   the pool display type (main for first level pools|modal for others)
     *
     * @return  void  generates HTML output
     */
    private static function renderHead(&$pool, $type = 'modal')
    {
        $crpText = THM_OrganizerHelperPool::getCrPText($pool);
        $headStyle = '';
        if (!empty($pool->bgColor))
        {
            $textColor = THM_OrganizerHelperComponent::getTextColor($pool->bgColor);
            $headStyle .= ' style="background-color: ' . $pool->bgColor . '; color: ' . $textColor . '"';
        }
        $script = ($type == 'main')?
            ' onclick="toggleGroupDisplay(\'#main-panel-items-' . $pool->mapping . '\')"' :
            ' onclick="toggleGroupDisplay(\'#panel-' . $pool->mapping . '\')"';
        $iconClass = $type == 'main'? 'icon-add' : 'icon-remove';
        echo '<div class="' . $type . '-panel-head" ' . $headStyle . '>';
        echo '<a ' . $script . '><i class="' . $iconClass . '"></i></a>';
        echo '<div class="' . $type . ' panel-title" ' . $script . '> ';
        echo '<span class="' . $type . '-panel-name">' . $pool->name . '</span>';
        echo '<span class="' . $type . '-panel-crp">(' . $crpText . ')</span>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Generates the HTML output for a panel element body
     *
     * @param   object  &$pool  the element to be rendered
     * @param   string  $type   the pool display type
     *
     * @return  void  generates HTML output
     *
     * @SuppressWarnings("PMD.NPathComplexity")
     */
    private static function renderBody(&$pool, $type)
    {
        $displayBody = ($type == 'main')? 'hidden' : 'shown';
        $mainID = ($type == 'main')? 'id="main-panel-items-' . $pool->mapping . '"' : '';
        $maxItems = (int) JFactory::getApplication()->getMenu()->getActive()->params->get('maxItems', 5);
        $itemWidth = 100 / $maxItems - 2;
        $childIndex = $childNumber = 1;
        $childCount = count($pool->children);

        echo '<div class="' . $type . '-panel-items ' . $displayBody . '" ' . $mainID . '>';
        foreach ($pool->children AS $element)
        {

            if ($childIndex === 1)
            {
                echo '<div class="panel-row">';
            }
            $itemPanel = new THM_OrganizerTemplateCurriculumItemPanel;
            $itemPanel->render($element, $itemWidth);
            $isRowEnd = $childIndex === $maxItems;
            $endRow = ($isRowEnd OR $childNumber === $childCount);
            if ($endRow)
            {
                echo '</div>';
            }
            $childIndex = $isRowEnd? 1 : $childIndex + 1;
            $childNumber++;
        }
        echo '</div>';
    }
}