<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/component.php';
/** @noinspection PhpIncludeInspection */
jimport('phpexcel.library.PHPExcel');

/**
 * Class generates the department statistics XLS file.
 */
class THM_OrganizerTemplateDepartment_Statistics_XLS
{

    private $endDate;

    private $headerFill;

    private $hoursColumns;

    private $lightBorder;

    private $rightBorder;

    private $rooms;

    private $roomTypes;

    private $roomTypeMap;

    private $sHoursColumns;

    private $startDate;

    private $useData;

    /**
     * THM_OrganizerTemplateDepartment_Statistics_XLS constructor.
     *
     * @param object &$model the model containing the data for the room statistics
     */
    public function __construct(&$model)
    {
        $this->endDate         = $model->endDate;
        $this->planningPeriods = $model->planningPeriods;
        $this->rooms           = $model->rooms;
        $this->roomTypes       = $model->roomTypes;
        $this->roomTypeMap     = $model->roomTypeMap;
        $this->startDate       = $model->startDate;
        $this->useData         = $model->useData;
        unset ($model);

        $this->spreadSheet = new PHPExcel();

        $userName    = JFactory::getUser()->name;
        $startDate   = THM_OrganizerHelperComponent::formatDate($this->startDate);
        $endDate     = THM_OrganizerHelperComponent::formatDate($this->endDate);
        $description = sprintf(JText::_('COM_THM_ORGANIZER_DEPARTMENT_STATISTICS_EXPORT_DESCRIPTION'), $startDate,
            $endDate);
        $this->spreadSheet->getProperties()->setCreator("THM Organizer")
            ->setLastModifiedBy($userName)
            ->setTitle(JText::_('COM_THM_ORGANIZER_DEPARTMENT_STATISTICS_EXPORT_TITLE'))
            ->setDescription($description);

        $this->headerFill = [
            'type'  => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => ['rgb' => 'DFE5E6']
        ];

        $this->rightBorder = [
            'left'   => ['style' => PHPExcel_Style_Border::BORDER_NONE],
            'right'  => [
                'style' => PHPExcel_Style_Border::BORDER_THICK,
                'color' => ['rgb' => '394A59']
            ],
            'bottom' => [
                'style' => PHPExcel_Style_Border::BORDER_HAIR,
                'color' => ['rgb' => 'DFE5E6']
            ],
            'top'    => ['style' => PHPExcel_Style_Border::BORDER_NONE]
        ];

        $this->lightBorder = [
            'left'   => ['style' => PHPExcel_Style_Border::BORDER_NONE],
            'right'  => [
                'style' => PHPExcel_Style_Border::BORDER_HAIR,
                'color' => ['rgb' => 'DFE5E6']
            ],
            'bottom' => [
                'style' => PHPExcel_Style_Border::BORDER_HAIR,
                'color' => ['rgb' => 'DFE5E6']
            ],
            'top'    => ['style' => PHPExcel_Style_Border::BORDER_NONE]
        ];

        $this->spreadSheet->getDefaultStyle()->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $this->spreadSheet->getDefaultStyle()->applyFromArray([
            'font' => [
                'name'  => 'arial',
                'size'  => 12,
                'color' => ['rgb' => '394A59']
            ]
        ]);

        $summaryPP = [
            'name'      => JText::_('COM_THM_ORGANIZER_SUMMARY'),
            'startDate' => $this->startDate,
            'endDate'   => $this->endDate
        ];
        $this->addPlanningPeriodSheet(0, $summaryPP);

        $sheetNumber = 1;
        foreach ($this->planningPeriods as $planningPeriod) {
            // Saving these here prevents messy array functions and explicit iterative calculation permissions
            $this->hoursColumns  = [];
            $this->sHoursColumns = [];

            $this->addPlanningPeriodSheet($sheetNumber, $planningPeriod);
            $sheetNumber++;
        }

        // Reset the active sheet to the first item
        $this->spreadSheet->setActiveSheetIndex(0);
    }

    /**
     * Adds a data row (single room use) to the active sheet
     *
     * @param int    $sheetNumber the sheet number
     * @param int    $rowNo       the row number
     * @param string $ppIndex     the index used for the planning period being iterated
     * @param int    $roomID      the id of the room
     *
     * @return string
     */
    private function addDataRow($sheetNumber, $rowNo, $ppIndex, $roomID)
    {
        $typeName = empty($this->roomTypeMap[$roomID]) ? 'X' : $this->roomTypes[$this->roomTypeMap[$roomID]]['name'];

        $this->spreadSheet->getActiveSheet($sheetNumber)->setCellValue("A{$rowNo}", $this->rooms[$roomID]);
        $this->spreadSheet->getActiveSheet()->getStyle("A{$rowNo}")->applyFromArray(['borders' => $this->lightBorder]);
        $this->spreadSheet->getActiveSheet($sheetNumber)->setCellValue("B{$rowNo}", $typeName);
        $this->spreadSheet->getActiveSheet()->getStyle("B{$rowNo}")->applyFromArray(['borders' => $this->rightBorder]);

        $column = 'D';

        foreach ($this->useData[$ppIndex] as $departmentName => $roomUsage) {
            $minutes = empty($roomUsage[$roomID]) ? 0 : $roomUsage[$roomID];

            ++$column;
            $hours = $minutes / 60;
            $this->spreadSheet->getActiveSheet($sheetNumber)->setCellValue("{$column}{$rowNo}", $minutes / 60);
            $this->spreadSheet->getActiveSheet()->getStyle("{$column}{$rowNo}")->getNumberFormat()
                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
            $this->spreadSheet->getActiveSheet()->getStyle("{$column}{$rowNo}")->applyFromArray(['borders' => $this->lightBorder]);
            $this->hoursColumns[$column] = $column;

            ++$column;
            $this->spreadSheet->getActiveSheet($sheetNumber)->setCellValue("{$column}{$rowNo}", $minutes / 45);
            $this->spreadSheet->getActiveSheet()->getStyle("{$column}{$rowNo}")->getNumberFormat()
                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
            $this->spreadSheet->getActiveSheet()->getStyle("{$column}{$rowNo}")->applyFromArray(['borders' => $this->lightBorder]);
            $this->sHoursColumns[$column] = $column;

            ++$column;
            $this->spreadSheet->getActiveSheet($sheetNumber)->setCellValue("{$column}{$rowNo}",
                "=IFERROR($hours/C$rowNo,0)");
            $this->spreadSheet->getActiveSheet()->getStyle("{$column}{$rowNo}")->getNumberFormat()
                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
            $this->spreadSheet->getActiveSheet()->getStyle("{$column}{$rowNo}")->applyFromArray(['borders' => $this->rightBorder]);

        }

        $hoursCells = implode("$rowNo,", $this->hoursColumns) . $rowNo;
        $this->spreadSheet->getActiveSheet($sheetNumber)->setCellValue(
            "C{$rowNo}",
            "=SUM($hoursCells)"
        );
        $this->spreadSheet->getActiveSheet()->getStyle("C{$rowNo}")->getNumberFormat()
            ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
        $this->spreadSheet->getActiveSheet()->getStyle("C{$rowNo}")->applyFromArray(['borders' => $this->lightBorder]);

        $sHoursCells = implode("$rowNo,", $this->sHoursColumns) . $rowNo;
        $this->spreadSheet->getActiveSheet($sheetNumber)->setCellValue(
            "D{$rowNo}",
            "=SUM($sHoursCells)"
        );

        $this->spreadSheet->getActiveSheet()->getStyle("D{$rowNo}")->getNumberFormat()
            ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
        $this->spreadSheet->getActiveSheet()->getStyle("D{$rowNo}")->applyFromArray(['borders' => $this->rightBorder]);

        return $column;
    }

    /**
     * Adds a header group consisting of a title row of 4 merged cells and a header row consisting of 4 header cells
     *
     * @param int    $sheetNumber the sheet being iterated
     * @param string $startColumn the first column
     * @param string $groupTitle  the group header title
     * @param int    $firstRow    the first data row of the table
     * @param int    $lastRow     the last data row of the table
     *
     * @return string the column name currently iterated to
     */
    private function addHeaderGroup($sheetNumber, $startColumn, $groupTitle, $firstRow, $lastRow)
    {
        ++$startColumn;
        $currentColumn = $startColumn;
        $this->spreadSheet->getActiveSheet($sheetNumber)->setCellValue("{$currentColumn}3",
            JText::_('COM_THM_ORGANIZER_HOURS_ABBR'));
        $this->spreadSheet->getActiveSheet()->getStyle("{$currentColumn}3")->applyFromArray(['fill' => $this->headerFill]);
        $this->spreadSheet->getActiveSheet($sheetNumber)->setCellValue("{$currentColumn}4",
            "=SUBTOTAL(109,{$currentColumn}{$firstRow}:{$currentColumn}{$lastRow})");
        $this->spreadSheet->getActiveSheet()->getStyle("{$currentColumn}4")->applyFromArray(['borders' => $this->lightBorder]);
        $this->spreadSheet->getActiveSheet()->getStyle("{$currentColumn}4")->getNumberFormat()
            ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
        $this->spreadSheet->getActiveSheet($sheetNumber)->setCellValue("{$currentColumn}7",
            JText::_('COM_THM_ORGANIZER_HOURS_ABBR'));
        $this->spreadSheet->getActiveSheet()->getStyle("{$currentColumn}7")->applyFromArray(['fill' => $this->headerFill]);
        $this->spreadSheet->getActiveSheet()->getColumnDimension($currentColumn)->setWidth(10);

        ++$currentColumn;
        $this->spreadSheet->getActiveSheet($sheetNumber)->setCellValue("{$currentColumn}3",
            JText::_('COM_THM_ORGANIZER_SCHOOL_HOURS_ABBR'));
        $this->spreadSheet->getActiveSheet()->getStyle("{$currentColumn}3")->applyFromArray(['fill' => $this->headerFill]);
        $this->spreadSheet->getActiveSheet($sheetNumber)->setCellValue("{$currentColumn}4",
            "=SUBTOTAL(109,{$currentColumn}{$firstRow}:{$currentColumn}{$lastRow})");
        $this->spreadSheet->getActiveSheet()->getStyle("{$currentColumn}4")->applyFromArray(['borders' => $this->lightBorder]);
        $this->spreadSheet->getActiveSheet()->getStyle("{$currentColumn}4")->getNumberFormat()
            ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
        $this->spreadSheet->getActiveSheet($sheetNumber)->setCellValue("{$currentColumn}7",
            JText::_('COM_THM_ORGANIZER_SCHOOL_HOURS_ABBR'));
        $this->spreadSheet->getActiveSheet()->getStyle("{$currentColumn}7")->applyFromArray(['fill' => $this->headerFill]);
        $this->spreadSheet->getActiveSheet()->getColumnDimension($currentColumn)->setWidth(10);

        ++$currentColumn;
        $this->spreadSheet->getActiveSheet($sheetNumber)->setCellValue("{$currentColumn}3",
            JText::_('COM_THM_ORGANIZER_PERCENT_USAGE'));
        $this->spreadSheet->getActiveSheet()->getStyle("{$currentColumn}3")->applyFromArray(['fill' => $this->headerFill]);
        $this->spreadSheet->getActiveSheet($sheetNumber)->setCellValue("{$currentColumn}4",
            "==IFERROR({$startColumn}4/C4,0)");
        $this->spreadSheet->getActiveSheet()->getStyle("{$currentColumn}4")->applyFromArray(['borders' => $this->rightBorder]);
        $this->spreadSheet->getActiveSheet()->getStyle("{$currentColumn}4")->getNumberFormat()
            ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
        $this->spreadSheet->getActiveSheet($sheetNumber)->setCellValue("{$currentColumn}7",
            JText::_('COM_THM_ORGANIZER_PERCENT_USAGE'));
        $this->spreadSheet->getActiveSheet()->getStyle("{$currentColumn}7")->applyFromArray(['fill' => $this->headerFill]);
        $this->spreadSheet->getActiveSheet()->getStyle("{$currentColumn}7")->applyFromArray(['borders' => $this->rightBorder]);
        $this->spreadSheet->getActiveSheet()->getColumnDimension($currentColumn)->setWidth(10);

        $this->spreadSheet->getActiveSheet($sheetNumber)->mergeCells("{$startColumn}6:{$currentColumn}6");
        $this->spreadSheet->getActiveSheet($sheetNumber)->setCellValue("{$startColumn}6", $groupTitle);
        $this->spreadSheet->getActiveSheet()->getStyle("{$startColumn}6")->applyFromArray(['fill' => $this->headerFill]);
        $this->spreadSheet->getActiveSheet()->getStyle("{$currentColumn}6")->applyFromArray(['borders' => $this->rightBorder]);

        return $currentColumn;
    }

    /**
     * Adds a planning period sheet
     *
     * @param int   $sheetNumber    the number of the sheet being iterated
     * @param array $planningPeriod the planning period this sheet references
     *
     * @return void
     */
    private function addPlanningPeriodSheet($sheetNumber, $planningPeriod)
    {
        if ($sheetNumber !== 0) {
            $ppIndex = $planningPeriod['name'];
            $this->spreadSheet->createSheet();
        } else {
            $ppIndex = 'total';
        }

        $title = $planningPeriod['name'];

        if ($planningPeriod['startDate'] < $this->startDate) {
            $title .= ' ' . sprintf(JText::_('COM_THM_ORGANIZER_FROM_DATE'), $this->startDate);
        }

        if ($planningPeriod['endDate'] > $this->endDate) {
            $title .= ' ' . sprintf(JText::_('COM_THM_ORGANIZER_TO_DATE'), $this->endDate);
        }

        $this->spreadSheet->setActiveSheetIndex($sheetNumber);
        $this->spreadSheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight('18');
        $this->spreadSheet->getActiveSheet()->setTitle($title);
        $this->spreadSheet->getActiveSheet()->mergeCells("A1:H1");
        $this->spreadSheet->getActiveSheet()->setCellValue('A1', $title);
        $this->spreadSheet->getActiveSheet()->getStyle('A1')->getFont()->setSize(16);
        $this->spreadSheet->getActiveSheet()->getStyle("B3")->applyFromArray([
            'fill'    => $this->headerFill,
            'borders' => $this->rightBorder
        ]);
        $this->spreadSheet->getActiveSheet()->setCellValue('B4', JText::_('COM_THM_ORGANIZER_SUMMARY'));
        $this->spreadSheet->getActiveSheet()->getStyle("B4")->applyFromArray([
            'fill'    => $this->headerFill,
            'borders' => $this->rightBorder
        ]);

        $lastRow = $firstRow = 7;
        foreach ($this->rooms as $roomID => $roomName) {
            $lastRow++;
            $lastColumn = $this->addDataRow($sheetNumber, $lastRow, $ppIndex, $roomID);
        }

        $this->addSummaryHeader($sheetNumber, 'C', JText::_('COM_THM_ORGANIZER_HOURS_ABBR'), $lastRow, 'lightBorder');
        $this->addSummaryHeader($sheetNumber, 'D', JText::_('COM_THM_ORGANIZER_SCHOOL_HOURS_ABBR'), $lastRow,
            'rightBorder');

        $currentColumn = 'D';

        foreach (array_keys($this->useData[$ppIndex]) as $departmentName) {
            $currentColumn = $this->addHeaderGroup($sheetNumber, $currentColumn, $departmentName, $firstRow, $lastRow);
        }

        $this->spreadSheet->getActiveSheet($sheetNumber)->setCellValue('A7', JText::_('COM_THM_ORGANIZER_NAME'));
        $this->spreadSheet->getActiveSheet()->getStyle("A7")->applyFromArray(['fill' => $this->headerFill]);
        $this->spreadSheet->getActiveSheet()->getStyle("B6")->applyFromArray(['borders' => $this->rightBorder]);
        $this->spreadSheet->getActiveSheet($sheetNumber)->setCellValue('B7', JText::_('COM_THM_ORGANIZER_ROOM_TYPE'));
        $this->spreadSheet->getActiveSheet()->getStyle("D6")->applyFromArray(['borders' => $this->rightBorder]);
        $this->spreadSheet->getActiveSheet()->getStyle("B7")->applyFromArray([
            'fill'    => $this->headerFill,
            'borders' => $this->rightBorder
        ]);
        $this->spreadSheet->getActiveSheet($sheetNumber)->setAutoFilter("A7:{$lastColumn}{$lastRow}");

        $this->spreadSheet->getActiveSheet()->getColumnDimension('A')->setWidth('11.5');
        $this->spreadSheet->getActiveSheet()->getColumnDimension('B')->setWidth('18');
        $this->spreadSheet->getActiveSheet()->getColumnDimension('C')->setWidth('11');
        $this->spreadSheet->getActiveSheet()->getColumnDimension('D')->setWidth('11');

        $this->spreadSheet->getActiveSheet()->freezePane('C8');
    }

    /**
     * Adds summary column headers
     *
     * @param int    $sheetNumber the number of the sheet
     * @param string $column      the header column
     * @param string $title       the header text
     * @param int    $lastRow     the last table row
     * @param string $borderStyle the border style for the column
     *
     * @return void
     */
    private function addSummaryHeader($sheetNumber, $column, $title, $lastRow, $borderStyle)
    {
        $this->spreadSheet->getActiveSheet($sheetNumber)->setCellValue("{$column}3", $title);
        $this->spreadSheet->getActiveSheet()->getStyle("{$column}3")->applyFromArray(['fill' => $this->headerFill]);
        $this->spreadSheet->getActiveSheet()->getStyle("{$column}3")->applyFromArray(['borders' => $this->$borderStyle]);

        $this->spreadSheet->getActiveSheet($sheetNumber)->setCellValue("{$column}4", "=SUBTOTAL(109,C8:C{$lastRow})");
        $this->spreadSheet->getActiveSheet()->getStyle("{$column}4")->getNumberFormat()
            ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
        $this->spreadSheet->getActiveSheet()->getStyle("{$column}4")->applyFromArray(['borders' => $this->$borderStyle]);

        $this->spreadSheet->getActiveSheet($sheetNumber)->setCellValue("{$column}7", $title);
        $this->spreadSheet->getActiveSheet()->getStyle("{$column}7")->applyFromArray(['fill' => $this->headerFill]);
        $this->spreadSheet->getActiveSheet()->getStyle("{$column}7")->applyFromArray(['borders' => $this->$borderStyle]);
    }

    /**
     * Outputs the generated Excel file
     *
     * @return void
     */
    public function render()
    {
        $objWriter = PHPExcel_IOFactory::createWriter($this->spreadSheet, 'Excel2007');
        ob_end_clean();
        header('Content-type: application/vnd.ms-excel');
        $docTitle = JApplicationHelper::stringURLSafe(JText::_('COM_THM_ORGANIZER_DEPARTMENT_STATISTICS_EXPORT_TITLE') . '_' . date('Ymd'));
        header("Content-Disposition: attachment;filename=$docTitle.xlsx");
        $objWriter->save('php://output');
        exit();
    }
}