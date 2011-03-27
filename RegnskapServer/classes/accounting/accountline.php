<?php
class AccountLine {
    public $Id;
    public $Description;
    public $EditedByPerson;
    public $EditedByPersonName;
    public $Attachment;
    public $Postnmb;
    private $Occured;
    private $db;

    /*! Used in getMonth */
    private $Posts;
    public $PostsArray;
    public $groupDebetMonth;
    public $groupKredMonth;
    public $debetKreditMismatch = 0;
    public $date;
    public $sum;

    /*! Used in edit */
    public $disableDelete;

    function AccountLine($db, $postnmb = 0, $attachment = 0, $description = 0, $day = 0, $id = 0, $occured = 0, $editedByPerson = 0) {
        $this->db = $db;
        $this->Postnmb = $postnmb;
        $this->Attachment = $attachment;
        $this->Description = $description;
        $this->EditedByPerson = $editedByPerson;
        if ($occured) {
            $this->Occured = $occured;
            $this->date = $occured->displayAccount();

        } else
        if ($day) {
            $standard = new AccountStandard($db);
            $month = $standard->getOneValue(AccountStandard::CONST_MONTH);
            $year = $standard->getOneValue(AccountStandard::CONST_YEAR);

            $this->Occured = & new eZDate();
            $this->Occured->setDay($day);
            $this->Occured->setMonth($month);
            $this->Occured->setYear($year);

        }
        $this->Id = $id;
    }

    function readLineAndPosts($ids) {

        $params = implode(",", array_fill(0, sizeof($ids), "?"));
        $prep = $this->db->prepare("select L.id, L.attachnmb, L.occured, L.postnmb, L.description, P.debet, P.post_type, P.amount, PT.description as postdesc FROM " . AppConfig::pre() . "line L, " . AppConfig::pre() . "post P, " . AppConfig::pre() . "post_type PT where L.id = P.line and L.id IN (" . $params . ") and PT.post_type = P.post_type order by L.id, P.debet");

        $prep->bind_array_params(str_repeat("i", sizeof($ids)), $ids);

        return $prep->execute($prep);
    }

    function findLine($action, $line) {
        switch($action) {
            case "next":
                $prep = $this->db->prepare("select min(id) as navid from " . AppConfig::pre() . "line where id > ?");
                $prep->bind_params("i", $line);
                break;
            case "previous":
                $prep = $this->db->prepare("select max(id) as navid from " . AppConfig::pre() . "line where id < ?");
                $prep->bind_params("i", $line);
                break;
            default:
                die("Unknown action for navigation '$action'");
        }
        $res = $prep->execute();

        if(!count($res)) {
            return $line;
        }
        $navline = $res[0]["navid"];

        if(!$navline) {
            return $line;
        }
        return $navline;
    }

    function read($id) {
        $prep = $this->db->prepare("select id, attachnmb, occured, postnmb, description, edited_by_person, (select max(id) from " . AppConfig::pre() . "line) as maxid  from " . AppConfig::pre() . "line where id = ?");
        $prep->bind_params("i", $id);
        $line_query = $prep->execute($prep);

        foreach ($line_query as $one) {
            $this->Id = $id;
            $this->Attachment = $one["attachnmb"];
            $this->Occured = new eZDate();
            $this->Occured->setMySQLDate($one["occured"]);
            $this->date = $this->Occured->displayAccount();
            $this->Postnmb = $one["postnmb"];
            $this->Description = $one["description"];
            $this->EditedByPerson = $one["edited_by_person"];
            $this->disableDelete = $one["id"] != $one["maxid"];
        }


    }

    function sumOneLinePosts($lineId, $debkred) {
        $prep = $this->db->prepare("select sum(" . AppConfig::pre() . "post.amount) as s from " . AppConfig::pre() . "post where line=? and debet=?");
        $prep->bind_params("is", $lineId, $debkred);
        $result_lines = $prep->execute($prep);

        if (count($result_lines) == 0) {
            return 0;
        }

        return $result_lines[0]["s"];

    }

    function sumPostsYear($posttype, $year, $debkred) {
        $prep = $this->db->prepare("select sum(" . AppConfig::pre() . "post.amount) as s from " . AppConfig::pre() . "post, " . AppConfig::pre() . "line " .
		"WHERE " . AppConfig::pre() . "post.post_type=? and " . AppConfig::pre() . "post.debet=? " .
		"and " . AppConfig::pre() . "line.year=? " .
		"and " . AppConfig::pre() . "line.id = " . AppConfig::pre() . "post.line");

        $prep->bind_params("isi", $posttype, $debkred, $year);
        $result_lines = $prep->execute($prep);

        if (count($result_lines) == 0) {
            return 0;
        }

        return $result_lines[0]["s"];

    }

    function sumPosts($posttype, $year, $month, $debkred) {
        $prep = $this->db->prepare("select sum(" . AppConfig::pre() . "post.amount) as s from " . AppConfig::pre() . "post, " . AppConfig::pre() . "line " .
		"WHERE " . AppConfig::pre() . "post.post_type=? and " . AppConfig::pre() . "post.debet=? " .
		"and " . AppConfig::pre() . "line.year=? and " . AppConfig::pre() . "line.month=? " .
		"and " . AppConfig::pre() . "line.id = " . AppConfig::pre() . "post.line");
        $prep->bind_params("isii", $posttype, $debkred, $year, $month);
        $result_lines = $prep->execute();

        if (count($result_lines) == 0) {
            return 0;
        }

        return $result_lines[0]["s"];
    }

    function setNewLatest($description, $day, $year, $month, $editByPerson = 0) {
        $this->Description = $description;

        $this->Occured = & new eZDate();
        $this->Occured->setDay($day);
        $this->Occured->setMonth($month);
        $this->Occured->setYear($year);
   
        $this->Postnmb = $this->getNextPostnmb($year, $month);
        $this->Attachment = $this->getNextAttachmentNmb($year);
        $this->EditedByPerson = $editByPerson;
    }

    function update() {
        $prep = $this->db->prepare("update " . AppConfig::pre() . "line set attachnmb = ?, postnmb = ?, occured = ?, description = ?, edited_by_person=? where id = ?");

        $prep->bind_params("iissii", $this->Attachment, $this->Postnmb, $this->Occured->mySQLDate(), $this->Description, $this->EditedByPerson, $this->Id);

        $prep->execute();
        return $this->db->affected_rows();
    }

    function updateAttachment($id, $attachment) {
        $prep = $this->db->prepare("update " . AppConfig::pre() . "line set attachnmb = ? where id = ?");

        $prep->bind_params("ii", $attachment, $id);
        $prep->execute();
    }

    function store($month = 0, $year = 0) {

        $standard = new AccountStandard($this->db);

        if (!$month) {
            $month = $standard->getOneValue(AccountStandard::CONST_MONTH);
        }
        if (!$year) {
            $year = $standard->getOneValue(AccountStandard::CONST_YEAR);
        }

        $prep = $this->db->prepare("insert into " . AppConfig::pre() . "line SET id=null,attachnmb=?,postnmb=?,description=?,month=?,year=?,occured=?,edited_by_person=?");
        $prep->bind_params("iisiisi", $this->Attachment, $this->Postnmb, $this->Description, $month, $year, $this->Occured->mySQLDate(), $this->EditedByPerson);
        $prep->execute();
        $this->Id = $this->db->insert_id();
    }

    function addPostDebKred($line, $post_type, $amount) {
        $post = new AccountPost($this->db, $line, "1", $post_type, $amount);
        $post->store();

        $post = new AccountPost($this->db, $line, "-1", $post_type, $amount);
        $post->store();

    }

    function addPostSingleAmount($line, $debet, $post_type, $amount, $project = 0, $person = 0, $belonging = 0) {

        $post = new AccountPost($this->db, $line, $debet, $post_type, $amount, 0, $project, $person);
        $post->setBelonging($belonging);

        $post->store();

    }
    function addPost($line, $debet, $post_type, $nok, $nokdes, $project = 0, $person = 0) {
        $amount = "";
        if ($nokdes) {
            $amount = "$nok.$nokdes";
        } else {
            $amount = "$nok.00";
        }

        $this->addPostSingleAmount($line, $debet, $post_type, $amount, $project, $person);
    }

    function getMonthSimple($month) {
        $standard = new AccountStandard($this->db);
        $year = $standard->getOneValue(AccountStandard::CONST_YEAR);

        $prep = $this->db->prepare("select id, attachnmb, occured, description from " . AppConfig::pre() . "line where year = ? and month = ? order by postnmb");
        $prep->bind_params("ii", $month, $year);

        $line_query = $prep->execute();

        $res = array ();

        foreach ($line_query as $one) {
            $occured = new eZDate();
            $occured->setMySQLDate($one["occured"]);
            $res[] = new AccountLine($this->db, 0, $one["attachnmb"], $one["description"], 0, $one["id"], $occured);
        }
        return $res;
    }

    /*! Returns all posts in a month. On the accountlines that are
     returned, the posts are cached for better access */
    function getMonth($year, $month, $fromline = 0, $toline = 0, $fillGrouped = 0) {

        $query = 0;
        $prep = 0;

        if ($fromline && $toline) {
            $prep = $this->db->prepare("select id, attachnmb, occured, postnmb, description from " . AppConfig::pre() . "line where month=? and year=? and id >= ? and id <= ? order by postnmb");
            $prep->bind_params("iiii", $year, $month, $fromline, $toline);
        } else {
            $prep = $this->db->prepare("select id, attachnmb, occured, postnmb, description from " . AppConfig::pre() . "line where month=? and year=? order by postnmb");
            $prep->bind_params("ii", $month, $year);
        }

        $lines = $this->getLines($prep->execute());

        if ($fillGrouped) {
            $groupArray = $this->fill_grouped(&$lines);

            return array("lines"=>$lines, "debetsums"=>$groupArray["debetsums"], "creditsums"=>$groupArray["creditsums"]);
        }

        return $lines;
    }
    /*!
     * Removes the postArray data here as I don't want it when requesting overview data.
     * Sums up the collection post data making it ready for wire.
     */
    function fill_grouped($lines) {
        $groupDebet = array();
        $groupCredit = array();
        foreach ($lines as &$one) {
            $debbe = 0;
            $sumCredit = 0;

            $one->groupDebetMonth = array ();
            $one->groupKredMonth = array ();

            if (!($one->Posts)) {
                $one->Posts = array ();
            }

            foreach (array_keys($one->Posts) as $groupid) {
                $posts = $one->Posts[$groupid];

                foreach ($posts as $post) {
                    if ($post->getDebet() == 1) {
                        $debbe = $debbe + 0 + $post->getAmount();

                        if (array_key_exists($groupid, $one->groupDebetMonth)) {
                            $one->groupDebetMonth[$groupid] += $post->getAmount();
                        } else {
                            $one->groupDebetMonth[$groupid] = $post->getAmount();
                        }
                        if (array_key_exists($groupid, $groupDebet)) {
                            $groupDebet[$groupid]+= $post->getAmount();
                        } else {
                            $groupDebet[$groupid] = $post->getAmount();
                        }

                    } else {
                        $sumCredit = $sumCredit + 0 + $post->getAmount();

                        if (array_key_exists($groupid, $one->groupKredMonth)) {
                            $one->groupKredMonth[$groupid] += $post->getAmount();
                        } else {
                            $one->groupKredMonth[$groupid] = $post->getAmount();
                        }
                        if (array_key_exists($groupid, $groupCredit)) {
                            $groupCredit[$groupid]+= $post->getAmount();
                        } else {
                            $groupCredit[$groupid] = $post->getAmount();
                        }
                    }
                }
            }
            $one->debetKreditMismatch = ($debbe != $sumCredit) ? "1" : "0";
            $one->PostsArray = null;
        }
        return array("creditsums" => $groupCredit, "debetsums" => $groupDebet);
    }

    function searchLines($fromdate, $todate, $account, $project, $person, $description) {

        $stat = 0;


        if ($account || $person || $project) {
            $stat = "select distinct RL.id as id, RL.attachnmb as attachnmb, RL.occured as occured, RL.postnmb as postnmb, RL.description as description from " . AppConfig::pre() . "line RL, " . AppConfig::pre() . "post RP";
        } else {
            $stat = "select distinct RL.id as id, attachnmb as attachnmb, RL.occured as occured, RL.postnmb as postnmb, RL.description as description from " . AppConfig::pre() . "line RL";
        }

        $searchWrap = $this->db->search($stat, "order by occured, postnmb");
        if ($account || $person || $project) {
            $searchWrap->addAndSQL("i", 1, "RL.id = RP.line and 1 = ?");
        }
        if ($fromdate) {
            $searchD = new eZDate();
            $searchD->setDate($fromdate);
            $searchWrap->addAndSQL("s", $searchD->mySQLDate(), "RL.occured >= ?");
        }

        if ($todate) {
            $searchD = new eZDate();
            $searchD->setDate($todate);
            $searchWrap->addAndSQL("s", $searchD->mySQLDate(), "RL.occured <= ?");
        }

        if ($person) {
            $searchWrap->addAndSQL("i", $person, "RP.person = ?");
        }

        if ($project) {
            $searchWrap->addAndSQL("i", $project, "RP.project = ?");
        }

        if ($account) {
            $searchWrap->addAndSQL("i", $account, "RP.post_type = ?");
        }

        if($description) {
            $searchWrap->addAndSQL("s", $description, "RL.description like ?");
        }

        $res = $this->getLines($searchWrap->execute());

        return $res;
    }

    /*! Expects a query done on regn_line*/
    function getLines($line_query) {

        $result_array = array ();
        $sortedById = array ();

        if (count($line_query) >= 0) {

            $accAccountPostType = new AccountPostType($this->db);

            /* Init cache in accAccountPostType */
            $accAccountPostType->getAll(1);

            $minId = 999999999;
            $maxId = 0;
            foreach ($line_query as $line) {

                $id = $line["id"];

                $occured = new eZDate();
                $occured->setMySQLDate($line["occured"]);
                $day = $occured->day();

                $line = new AccountLine($this->db, $line["postnmb"], $line["attachnmb"], $line["description"], 0, $id, $occured);
                $result_array[] = $line;
                $sortedById[$id] = $line;

                if ($id < $minId) {
                    $minId = $id;
                }
                if ($id > $maxId) {
                    $maxId = $id;
                }
            }

            /* Then, fetch all posts based on min and max number. Assume
             * that most posts are registered linearly so we will not fetch
             * a lot of columns we do not need. And then we add the posts to
             * their respective lines */
            $accountPostAcc = new AccountPost($this->db);

            $allPosts = $accountPostAcc->getRange($minId, $maxId);

            foreach ($allPosts as $onePost) {
                $type = $onePost->getPost_type();
                $collectionPost = $accAccountPostType->getAccountPostType($type);
                if ($collectionPost && array_key_exists($onePost->getLine(), $sortedById)) {
                    $sortedById[$onePost->getLine()]->addCachedPost($collectionPost->getCollectionPost(), $onePost);
                }
            }
        }

        return $result_array;
    }

    function sumCashPosts($cashPosts) {

        $postListString = implode(",", $cashPosts);

        $prep = $this->db->prepare("select sum(amount) as sum FROM " . AppConfig::pre() . "post WHERE" .
		" line = ? AND debet='1' AND post_type IN ($postListString)");
        $prep->bind_params("i", $this->Id);

        $result_array = $prep->execute();

        $debet = 0;
        if (count($result_array) > 0) {
            $debet = $result_array[0]["sum"];
        }

        $prep = $this->prepare("select sum(amount) as sum FROM " . AppConfig::pre() . "post WHERE" .
		" line = ? AND debet='-1' AND post_type IN ($postListString)");

        $prep->bind_params("i", $this->Id);

        $result_array = $prep->execute();

        $kredit = 0;
        if (count($result_array) > 0) {
            $kredit = $result_array[0]["sum"];
        }

        return $debet - $kredit;
    }

    function getNextAttachmentNmb($year) {

        $prep = $this->db->prepare("select max(attachnmb) as m from " . AppConfig::pre() . "line where year=?");
        $prep->bind_params("i", $year);

        $result_array = $prep->execute();

        if (count($result_array) == 1) {
            return $result_array[0]["m"] + 1;
        }
        return 0;

    }

    function getNewLineData($year, $month) {
        $sql = "select (select max(attachnmb) as m from " . AppConfig::pre() . "line where year=?) as attachnmb,".
	  		"(select max(postnmb) as m from " . AppConfig::pre() . "line where year=? and month=?) as postnmb,". 
            "(select max(id) from " . AppConfig::pre() . "line) as line,".
       		"(select sum(amount) from " . AppConfig::pre() . "post where debet = '1' and line = (select max(id) from " . AppConfig::pre() . "line) ) as debet,".
            "(select sum(amount) from " . AppConfig::pre() . "post where debet = '-1' and line = (select max(id) from " . AppConfig::pre() . "line) ) as kredit";

        $prep = $this->db->prepare($sql);
        $prep->bind_params("iii", $year, $year, $month);
        $result_array = $prep->execute();
        
        return array_shift($result_array);
    }

    function getNextPostnmb($year, $month) {

        $prep = $this->db->prepare("select max(postnmb) as m from " . AppConfig::pre() . "line where year=? and month=?");
        $prep->bind_params("ii", $year, $month);
        $result_array = $prep->execute();

        if (count($result_array) == 1) {
            return $result_array[0]["m"] + 5;
        }
        return 0;
    }

    /*! Returns the cached posts as an array */
    function getPosts() {
        return $this->PostsArray;
    }

    function fetchAllPosts() {
        $this->postArray = $this->getAllPosts();

        $accperson = new AccountPerson($this->db);

        $idGivesName = array($this->EditedByPerson => $this->EditedByPersonName);


        $sum = 0;
        foreach ($this->postArray as $post) {
            if (!is_object($post)) {
                echo "Strange: $post";
                continue;
            }
            if ($post->getDebet() == 1) {
                $sum += $post->getAmount();
            } else {
                $sum -= $post->getAmount();
            }
             
            /* Load edited by names */
            if($post->EditedByPerson > 0) {
                if(array_key_exists($post->EditedByPerson, $idGivesName)) {
                    $post->EditedByPersonName = $idGivesName[$post->EditedByPerson];
                } else {
                    $name = $accperson->getName($post->EditedByPerson);
                    $post->EditedByPersonName = $name;
                    $idGivesName[$post->EditedByPerson] = $name;
                }
            }
        }
        $this->sum = $sum;
    }

    function addCachedPost($colposttype, $post) {
        if (!$this->Posts) {
            $this->Posts = array ();
            $this->PostsArray = array ();
        }
        if (!array_key_exists($colposttype, $this->Posts)) {
            $this->Posts[$colposttype] = array ();
        }

        $this->Posts[$colposttype][] = $post;
        $this->PostsArray[] = $post;
    }

    /*! Returns all posts for a given account line. If first cached by
     getMonth, then the items are indexed by their collection post type. */
    function getAllPosts($onlycache = 0) {
        if ($this->PostsArray) {
            return $this->PostsArray;
        }

        if ($onlycache) {
            return array ();
        }

        $accessor = new AccountPost($this->db, $this->Id);

        return $accessor->getAll($this->Id);
    }

    function search($desc) {

        $prep = $this->db->prepare("select id, attachnmb, occured, postnmb, description from " . AppConfig::pre() . "line where description like ?");
        $prep->bind_params("s", $desc);

        $line_query = $prep->execute();

        $result = array ();
        foreach ($line_query as $one) {
            $occ = new eZDate();
            $occ->setMySQLDate($one["occured"]);

            $result[] = new AccountLine($this->db, $one["postnmb"], $one["attachnmb"], $one["description"], 0, $one["id"], $occ);
        }
        return $result;
    }

    function getId() {
        return $this->Id;
    }

    function getDescription() {
        return $this->Description;
    }
    function getAttachment() {
        return $this->Attachment;
    }

    function setAttachment($a) {
        $this->Attachment = $a;
    }

    function getPostnmb() {
        return $this->Postnmb;
    }

    function setPostnmb($p) {
        $this->Postnmb = $p;
    }

    function getDay() {
        return $this->Occured->day();
    }

    function getOccured() {
        return $this->Occured->displayAccount();
    }

    function listOfYearMonths() {
        $prep = $this->db->prepare("select year,month from " . AppConfig::pre() . "line group by year,month order by year,month;");
        return $prep->execute();
    }

    function loadEditedByPersonName() {
        $accperson = new AccountPerson($this->db);

        $this->EditedByPersonName = $accperson->getName($this->EditedByPerson);
    }

    function listUniqeYears() {
        $prep = $this->db->prepare("select distinct year from ". AppConfig::pre() . "line order by year desc");
         
        $res = $prep->execute();
        $data = array();

        foreach ($res as $one) {
            $data[] = $one["year"];
        }
         
        return $data;
    }
}
?>
