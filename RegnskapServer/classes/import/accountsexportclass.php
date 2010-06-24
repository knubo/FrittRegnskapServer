<?php
class ExportAccounts {

    public $objPHPExcel;
    private $db;
    private $year;
    private $summaryRow;
    private $sharedStyle1;
    private $sharedStyle2;

    function ExportAccounts($db, $year) {
        $this->db = $db;
        $this->year = $year;


        $this->sharedStyle1 = new PHPExcel_Style();
        $this->sharedStyle2 = new PHPExcel_Style();

        $this->sharedStyle1->applyFromArray(array('numberformat' => array('code' => '#,##0.00_-'), 'fill' => array('type'=> PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'ECF1F3'))));
        $this->sharedStyle2->applyFromArray(array('numberformat' => array('code' => '#,##0.00_-'), 'fill' => array('type'=> PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'D7E2E6'))));


        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        $this->objPHPExcel = $objPHPExcel;

        // Set properties
        $objPHPExcel->getProperties()->setCreator("Fritt Regnskap")
        ->setLastModifiedBy("Fritt Regnskap")
        ->setTitle("Regnskap for $year")
        ->setSubject("Regnskap for $year")
        ->setKeywords("regnskap $year")
        ->setCategory("regnskap");


        $this->addData();

        $this->createSummaryPage();

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        $objPHPExcel->getProperties()->setDescription("Regnskap for $year, komplett med alle posteringer. Minne benyttet:".(memory_get_peak_usage(true) / 1024 / 1024) . " MB)");
    }

    function displayData($header, $sheet, $data, $descfield, $displaySum = 0) {
        $sheet->setCellValueByColumnAndRow(0, $this->summaryRow, $header);
        $sheet->mergeCellsByColumnAndRow(0, $this->summaryRow, 2, $this->summaryRow);

        $style = $sheet->getStyleByColumnAndRow(0, $this->summaryRow);
        $style->getFont()->setBold(true)->setSize(13);
        $style->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('CDCDCD');

        $this->summaryRow++;

        $sum = 0;

        $stylerow = 0;
        foreach(array_keys($data) as $post_type) {
            if($data[$post_type]["value"] == 0) {
                continue;
            }
            $sum+=$data[$post_type]["value"];

            $sheet->setCellValueByColumnAndRow(0, $this->summaryRow, $post_type);
            $sheet->setCellValueByColumnAndRow(1, $this->summaryRow, $data[$post_type][$descfield]);
            $sheet->setCellValueByColumnAndRow(2, $this->summaryRow, $data[$post_type]["value"]);

            if( (($stylerow + 3) % 6) < 3 ) {
                $sheet->setSharedStyle($this->sharedStyle1, "A".$this->summaryRow.":C".$this->summaryRow);
            } else {
                $sheet->setSharedStyle($this->sharedStyle2, "A".$this->summaryRow.":C".$this->summaryRow);
            }

            $stylerow++;
            $this->summaryRow++;
        }

        if($displaySum) {
            $sheet->setCellValueByColumnAndRow(1, $this->summaryRow, "Sum");
            $sheet->setCellValueByColumnAndRow(2, $this->summaryRow, $sum);

            $sumstyle = new PHPExcel_Style();
            $sumstyle->applyFromArray( array('numberformat' => array('code' => '#,##0.00_-') ,
                                         'borders' => array('top' => array('style' => PHPExcel_Style_Border::BORDER_THIN), 'bottom' => array('style' => PHPExcel_Style_Border::BORDER_DOUBLE) )
            ));
            $sheet->setSharedStyle($sumstyle, "A".($this->summaryRow).":C".($this->summaryRow));

            $this->summaryRow += 2;

        } else {
            $this->summaryRow++;
        }
        
        return $sum;
    }

    function createSummaryPage() {
        $sheet = $this->objPHPExcel->setActiveSheetIndex(0);

        $rep = new ReportYear($this->db);

        $sheet->setCellValue("A1", $this->year);
        $style = $sheet->getStyle("A1");
        $style->getFont()->setBold(true)->setSize(15);

        $month = 12;
        $this->summaryRow = 3;

        
        $data = $rep->list_sums_earnings($this->year, $month);
        $sumIncome = $this->displayData('Inntekter', $sheet, $data, "description", 1);

        $data = $rep->list_sums_cost($this->year, $month);
        $sumCost = $this->displayData('Utgifter', $sheet, $data, "description", 1);

        $data = array(" " => array("description" => "Inntekter", "value" => $sumIncome),
                      "  " => array("description" => "Utgifter", "value" => 0 - $sumCost));
        $this->displayData('Resultat', $sheet, $data, "description", 1);

        $data = $rep->list_sums_ownings($this->year, $month);
        $this->displayData('EK og forpliktelser', $sheet, $data, "description",1);

        $data = $rep->list_sums_2000($this->year, $month);
        $this->displayData('Eiendeler', $sheet, $data, "desc", 1);

        $sheet->getColumnDimensionByColumn(1)->setAutoSize(true);
        $sheet->getColumnDimensionByColumn(2)->setAutoSize(true);

    }

    function addSumRow($lastrow, $maxcol) {
        $sheet = $this->objPHPExcel->getActiveSheet();

        for($col = 3; $col < $maxcol; $col++) {
            $lettercol = PHPExcel_Cell::stringFromColumnIndex($col);

            $sheet->setCellValueByColumnAndRow($col, $lastrow+1, '=SUM('.$lettercol.'3:'.$lettercol.$lastrow.')');
        }

        $colInLetters = PHPExcel_Cell::stringFromColumnIndex($maxcol);

        $sheet->setCellValueByColumnAndRow(2, $lastrow+1, "SUM");

        $sumstyle = new PHPExcel_Style();
        $sumstyle->applyFromArray( array('numberformat' => array('code' => '#,##0.00_-') ,
                                         'borders' => array('top' => array('style' => PHPExcel_Style_Border::BORDER_THIN), 'bottom' => array('style' => PHPExcel_Style_Border::BORDER_DOUBLE) )
        ));
        $sheet->setSharedStyle($sumstyle, "A".($lastrow+1).":".$colInLetters.($lastrow+1));

    }

    function addRowColors($maxrow, $maxcol) {
        $sheet =  $this->objPHPExcel->getActiveSheet();

        /* one less */
        $maxcol --;


        $colInLetters = PHPExcel_Cell::stringFromColumnIndex($maxcol);
        for($row = $maxrow; $row >= 3; $row--) {


            if( (($row + 3) % 6) < 3 ) {
                $sheet->setSharedStyle($this->sharedStyle1, "A".$row.":".$colInLetters.$row);
            } else {
                $sheet->setSharedStyle($this->sharedStyle2, "A".$row.":".$colInLetters.$row);
            }
        }
    }

    function addSheetsForMonths() {
        $this->objPHPExcel->setActiveSheetIndex(0);
        $this->objPHPExcel->getActiveSheet()->setTitle('Oversikt '.$this->year);

        $months = array("Januar","Februar","Mars","April","Mai","Juni","Juli","August","September","Oktober","November","Desember");

        for($i = 0; $i < count($months); $i++) {
            $objWorksheet = $this->objPHPExcel->createSheet();
            $objWorksheet->setTitle($months[$i]);
            $objWorksheet->setCellValueByColumnAndRow(0,2,"Bilag");
            $objWorksheet->setCellValueByColumnAndRow(1,2,"Dato");
            $objWorksheet->setCellValueByColumnAndRow(2,2,"Beskrivelse");

            for($row = 1; $row < 3; $row++) {
                for($col = 0; $col < 3; $col++) {
                    $style = $objWorksheet->getStyleByColumnAndRow($col, $row);
                    $style->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('CDCDCD');
                }
            }
        }

    }

    function setHeaders($sheet, $data, $colToUse) {

        $sheet->setCellValueByColumnAndRow($colToUse, 2, "DEBET");
        $style = $sheet->getStyleByColumnAndRow($colToUse, 2);
        $style->getFont()->setBold(true)->setSize(8);
        $style->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('CDCDCD');

        $sheet->setCellValueByColumnAndRow($colToUse + 1, 2, "KREDIT");
        $style = $sheet->getStyleByColumnAndRow($colToUse+1, 2);
        $style->getFont()->setBold(true)->setSize(8);
        $style->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('CDCDCD');


        $sheet->setCellValueByColumnAndRow($colToUse, 1, $data["post_type"]."\n".$data["accountdesc"]);
        $sheet->mergeCellsByColumnAndRow($colToUse, 1, $colToUse+1, 1);
        $style = $sheet->getStyleByColumnAndRow($colToUse, 1);
        $style->getFont()->setSize(8);
        $style->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('CDCDCD');


        $sheet->getColumnDimensionByColumn($colToUse)->setAutoSize(true);
        $sheet->getColumnDimensionByColumn($colToUse+1)->setAutoSize(true);
    }

    function addData() {
        $this->addSheetsForMonths();

        $allPosts = $this->getYearPosts();

        $currentMonth = 0;
        $nextFreeCol = 0;

        foreach ($allPosts as $one) {
            $month = $one["month"];

            if($currentMonth != $month) {

                if($currentMonth > 0) {
                    $this->addRowColors($row, $nextFreeCol);
                    $this->addSumRow($row, $nextFreeCol);
                }

                $this->objPHPExcel->setActiveSheetIndex($one["month"]);
                $row = 2;
                $currentMonth = $month;
                $headers = array();
                $nextFreeCol = 3;
                $currentLineId = $one["id"];
            }

            if($curentLineId != $one["id"]) {
                $row++;
                $curentLineId = $one["id"];
            }

            $sheet = $this->objPHPExcel->getActiveSheet();

            $sheet->setCellValueByColumnAndRow(0, $row, $one["attachnmb"]);
            $sheet->setCellValueByColumnAndRow(1, $row, $one["occured"]);
            $sheet->setCellValueByColumnAndRow(2, $row, $one["description"]);

            $sheet->getColumnDimensionByColumn(0)->setAutoSize(true);
            $sheet->getColumnDimensionByColumn(1)->setAutoSize(true);
            $sheet->getColumnDimensionByColumn(2)->setAutoSize(true);


            if(!array_key_exists($one["post_type"], $headers)) {
                $this->setHeaders($sheet, $one, $nextFreeCol);
                $headers[$one["post_type"]] = $nextFreeCol;
                $nextFreeCol += 2;
            }

            $debIndex = $one["debet"] == "1" ? 0 : 1;
            $sheet->setCellValueByColumnAndRow($headers[$one["post_type"]] + $debIndex, $row, $one["amount"]);
        }
        $this->addRowColors($row, $nextFreeCol);
        $this->addSumRow($row, $nextFreeCol);
    }

    function getYearPosts() {
        $sql = "select RL.id, RL.attachnmb, RL.occured, RL.description, RL.month, RP.debet, RP.post_type, RP.amount, RPT.description as accountdesc, ".
        		" PR.description as projectdesc, P.firstname as person_firstname, P.lastname as person_lastname,".
	  			" P2.firstname as edit_first_name, P2.lastname as edit_last_name". 
             	" from (" . AppConfig::pre() . "line RL, " . AppConfig::pre() . "post RP, " . AppConfig::pre() . "post_type RPT)". 
		    	" left join " . AppConfig::pre() . "project PR on (PR.project = RP.project)".  
		    	" left join " . AppConfig::pre() . "person P on (P.id = RP.person)".  
		    	" left join " . AppConfig::pre() . "person P2 on (P2.id = RL.edited_by_person)".  
		    	" where RL.year = ? and RP.line = RL.id and RPT.post_type = RP.post_type". 
		    	" order by month, RL.postnmb";

        $prep = $this->db->prepare($sql);
        $prep->bind_params("i", $this->year);
        return $prep->execute();
    }

}



?>