<?php


/*
 * Created on Aug 4, 2007
 */

class MassLetterHelper {

	private $pdf;
	private $fonts;
	private $wrapopts;
	private $encoding;
	private $fontSize;
    private $all;
    private $db;
    private $users;
    private $year;
    private $currentUser;
    private $date;
    private $tableopts;
    private $tablerows;
    
    function MassLetterHelper($db, $year) {
    	$this->db = $db;
        $this->year = $year;
        $this->date = new eZDate();
    }
    
    
	function listTemplates() {
		$arr = array ();
		$arr[] = "templates/bsc.txt";

		return $arr;
	}

    function getParams($args) {
        $pairs = explode(",", $args);
        
        $opts = array();
        
        foreach($pairs as $pair) {
            $set = explode(":", $pair);
            
            $id = trim(array_shift($set));
            $opts[$id] = trim(array_shift($set));
        }
        return $opts;    	
    }

    function setDocument($args) {
        $opts = $this->getparams($args);
        
        $this->pdf = new Cezpdf($opts["paper"], $opts["layout"]);
        
    }
    
    function startDocument() {
        $this->all = $this->pdf->openObject();
        $this->pdf->saveState();    	
    }
    
    function setEncoding($args) {
    	if($args == "latin-1") {
    		$this->encoding = array (
                190 => "ae",
                174 => "AE",
                191 => "oslash",
                175 => "Oslash",
                129 => "Aring",
                140 => "aring"
            );
    	}
    }
    
    function trimExplode($args) {
        $arr = explode(",", $args);
        $res = array();
        foreach($arr as $one) {
        	$res[] = trim($one);
        } 
    	return $res;
    }
    
    function setMargins($args) {
    	$margs = $this->trimExplode($args);
        
        $this->pdf->ezSetMargins(array_shift($margs),array_shift($margs),array_shift($margs),array_shift($margs));
    }
    
    function addFont($args) {
    	$opts = $this->trimExplode($args);
        $id = array_shift($opts);
        $this->fonts[$id] = array_shift($opts);
    }
    
    function text($args) {
    	$parts = explode(",",$args);
        
        $t = 0;
       	$y = array_shift($parts);
        $x = array_shift($parts);
        
        if(count($parts) > 1) {
        	$t = implode(",",$parts);
        } else {
        	$t = array_shift($parts);
        }
        
        $this->pdf->addText($this->fontSize, $y, $x, $t);
    }
    
    function wrapopts($opts) {
    	if(strlen(trim($opts)) == 0) {
    		$this->wrapopts = array();
    	} else {
    		$this->wrapopts = $this->getParams($opts);
    	}
    }

    function personText($text) {
        $text = str_replace("#firstname", $this->currentUser["firstname"], $text);
        $text = str_replace("#lastname", $this->currentUser["lastname"], $text);
        $text = str_replace("#address", $this->currentUser["address"], $text);
        $text = str_replace("#zip", $this->currentUser["postnmb"], $text);
        $text = str_replace("#city", $this->currentUser["city"], $text);
        
        $rep = $this->currentUser["email"] ? $this->currentUser["email"] : "mangler";
        $text = str_replace("#email", $rep, $text);
        
        if ($this->currentUser["birthdate"] && $this->currentUser["birthdate"] != "0000-00-00") {
           $this->date->setMySQLDate($this->currentUser["birthdate"]);
           $rep = $this->date->display();
        } else {
            $rep = "mangler";
        }
        $text = str_replace("#birthdate", $rep, $text);
        return $text;
    }
    
    function wraptext($text) {
        if($this->currentUser) {
            $text = $this->personText($text);
        }
        
    	$this->pdf->ezText($text, $this->fontSize, $this->wrapopts);
    }

    function setColor($args) {
    	$opts = $this->trimExplode($args);
        
        $this->pdf->setColor(array_shift($opts),array_shift($opts),array_shift($opts));
    }
    
    function startTable($args) {
        # $tabopts = array("fontSize" => 12, "showHeadings"=>0, "showLines"=>1, "xPos"=>"left", "xOrientation"=>"right");
        
    	$this->tableopts = $this->getParams($args);
        $this->tablerows = array();
    }
    
    function row($text) {
        if($this->currentUser) {
    	   $text = $this->personText($text);        	
        }
        $this->tablerows[] = explode("|",$text);
    }
    
    function endTable() {
    	$this->pdf->ezTable($this->tablerows,'','',$this->tableopts);
    }
    
    function relrectangle($args) {
        $opts = $this->trimExplode($args);
        
        $p1 = array_shift($opts);
        $p2 = array_shift($opts);
        $p3 = array_shift($opts);
        $p4 = array_shift($opts);
        
        $pdf = $this->pdf;
        
    	$pdf->rectangle($p1,$pdf->y + $p2, $p3, $p4);
    }
    
    function reltext($args) {
        $opts = explode(",", $args);
        
        $p1 = trim(array_shift($opts));
        $p2 = trim(array_shift($opts));
        $t = implode(",",$opts);
        
        $pdf = $this->pdf;
    	$pdf->addText($p1, ($pdf->y)+$p2, $this->fontSize, $t);
    }
    
    function query($args) {
        if($args == "memberships") {
        	$accYearMem = new AccountYearMembership($this->db);

            $this->users = $accYearMem->getReportUsersFull($this->year);
        } else {
            die("Unknown query '$args'");
        }	
        $this->pdf->restoreState();
        $this->pdf->closeObject();
        // note that object can be told to appear on just odd or even pages by changing 'all' to 'odd'
        // or 'even'.
        $this->pdf->addObject($this->all, 'all');
    }

	function useTemplate($number) {
		$arr = $this->listtemplates();

		$this->fonts = array ();
		$this->wrapopts = array ();
		$template = array_shift(array_slice($arr,$number));

		$lines = file($template);

        if(!$lines) {
        	die("Failed to open $template");
        }

		$wrapopts = array ();
		$this->encoding = 0;
		$this->fontSize = 12;

        $record = 0;
        $toLoop = array();

		foreach ($lines as $one) {
            if($record) {
            	$toLoop[] = $one;
                continue;
            }
            
            $record = $this->handleOne($one);
		}
        
        if(!($this->users)) {
        	die("Query not set up.");
        }
        
        foreach($this->users as $user) {
        	$this->currentUser = $user;
            
            foreach($toLoop as $one) {
            	$this->handleOne($one);
            }
            $this->pdf->newPage();
        }
        
        $this->pdf->ezStream();
           
    }
        
    function handleOne($one) {
    	if (strlen(trim($one)) == 0 || substr($one, 0, 1) == "#") {
            return 0;
        }

        $coms = explode("=", $one);
        $action = trim(array_shift($coms));
        $args = trim(array_shift($coms));
        switch ($action) {
            case "document" :
                $this->setDocument($args);
                break;
            case "encoding" :
                $this->setEncoding($args);
                break;
            case "margins" :
                $this->setMargins($args);
                break;
            case "font" :
                $this->addFont($args);
                break;
            case "fontsize" :
                $this->fontSize = $args;
                break;
            case "fontselect" :
                $this->pdf->selectFont($this->fonts[$args], array (
                    'encoding' => 'WinAnsiEncoding',
                    'differences' => $this->encoding
                ));
                break;
            case "text" :
                $this->text($args);
                break;
            case "ezSetY" :
                $this->pdf->ezSetY($args);
                break;
            case "ezSetDy" :
                $this->pdf->ezSetDy($args);
                break;
            case "wrapopts" :
                $this->wrapopts($args);
                break;
            case "wraptext" :
                $this->wraptext($args);
                break;
            case "setColor" :
                $this->setColor($args);
                break;
            case "setLineStyle" :
                $this->pdf->setLineStyle($args);
                break;
            case "query":
                $this->query($args);
                return 1;
            case "starttable":
                $this->startTable($args);
                break;
            case "row":
                $this->row($args);
                break;
            case "endtable":
                $this->endTable();
                break;
            case "relrectangle":
                $this->relrectangle($args); 
                break;
            case "reltext":
                $this->reltext($args);
                break;
            case "startdocument":
                $this->startDocument();
                break;
            default:
                die("Unknown action: '$action'");
        }
        return 0;
    }

}
?>