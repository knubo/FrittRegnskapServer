<?php
class ExportAccounts {

    public $objPHPExcel;
    private $db;
    private $year;

    function ExportAccounts($db, $year) {
        $this->db = $db;
        $this->year = $year;


        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        $this->objPHPExcel = $objPHPExcel;

        // Set properties
        $objPHPExcel->getProperties()->setCreator("Fritt Regnskap")
        ->setLastModifiedBy("Fritt Regnskap")
        ->setTitle("Regnskap for $year")
        ->setSubject("Regnskap for $year")
        ->setDescription("Regnskap for $year, komplett med alle posteringer.")
        ->setKeywords("regnskap $year")
        ->setCategory("regnskap");


        $this->addData();


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
    }

    function addSheetsForMonths() {
        $this->objPHPExcel->setActiveSheetIndex(0);
        $this->objPHPExcel->getActiveSheet()->setTitle('Oversikt '.$this->year);

        $months = array("Januar","Februar","Mars","April","Mai","Juni","Juli","August","September","Oktober","November","Desember");
        
        for($i = 0; $i < count($months); $i++) {
            $objWorksheet = $this->objPHPExcel->createSheet();
            $objWorksheet->setTitle($months[$i]);
        }
        
    }
    
    function addData() {
        $this->addSheetsForMonths();
        
        $allPosts = $this->getYearPosts();

        
        // Add some data
        $this->objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', 'Hello')
        ->setCellValue('B2', 'world!')
        ->setCellValue('C1', 'Hello')
        ->setCellValue('D2', 'world!');

        // Miscellaneous glyphs, UTF-8
        $this->objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A4', 'Miscellaneous glyphs')
        ->setCellValue('A5', 'Snodig');


    }

    function getYearPosts() {
        $sql = "select RL.id, RL.occured, RL.description, RL.month, RP.debet, RP.post_type, RP.amount, RPT.description as accountdesc, ".
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