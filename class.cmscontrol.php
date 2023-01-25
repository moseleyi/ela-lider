<?php
/* class.cmscontrol.php
.---------------------------------------------------------------------------.
|  Software: LemonCMS - Content Management System                           |
|   Version: 2.7.9                                                          |
|  Released: 17 October 2017                                                |
|   Contact: michal@lemon-art.pl, dawid@lemon-art.pl                        |
|      Info: http://lemon-art.pl                                            |
| ------------------------------------------------------------------------- |
|    Author: <coding> Dawid Nawrot                                          |
|    Author: <design> Michał Kortas                                         |
| Thanks to: <manual> Paulina Kortas                                        |
| Copyright: (c) 2009-2017, Lemon-Art Studio Graficzne. All Rghts Reserved. |
| ------------------------------------------------------------------------- |
|   License: Distributed by Lemon-Art Studio Graficzne. You can't modify    |
|			 redistribute, or sell this copy of CMS. One copy of this       |
|            software is allowed to run on one website. Multiple licensing  |
|            available.                                                     |
'---------------------------------------------------------------------------'
*/

/* Class tree
	001.	showErrors()
	002.	connectDB()
	003.	disconnectDB()
	004.	lastInsertId()
	005.	executeQuery()
	006.	getCount()
	007.	dbBackup()
	008. 	startCMS()
	009.	checkSession()
	010.	clearBuffer()
	011.	checkAccess()
	012.	login()
	013.	getCMSInfo()
	014.	getLang()
	015.	populateMenu()
	016.	loadTranslation()
	017.	translate()
	018.	saveAction()
	019.	compressHTML()
	020.	showIntNameInput()
	021.	validateInternalName()
	022.	checkExistingNames()
	023.	validateEmail()
	024.	createValidName()
	025.	createIntName()
	026.	esc()
	027.	getRandomName()
	028.	trimString()
	029.	processImage()
	030.	centerImage()
	031.	cropImage()
	032.	makeThumbnail()
	033.	convertDate()
	034.	setInfo()
	035.	setSessionInfo()
	036.	getSessionInfo()
*/


class cmsControl  {
	private $dbH           	= "sql.serwer1846576.home.pl"; //"sql.lemonart.nazwa.pl";			// database host
	private $dbD       		= "28979580_lider"; //"lemonart_lider";						// database name
	private $dbL         	= "28979580_lider"; //"lemonart_lider";						// database login
	private $dbP      		= "9Jt0yJ4oCAvq1zpwT94n";		// database password
	private $dbC        	= "utf8";						// database charset
	private $port			= 3306;							// port, change to 3307 for MySQL 5.5

	function __construct() {
		ini_set("display_errors", 0);
		$this->connectDB();
	}

/**
 *	showErrors - override error switch on/off
 *		@param $a (bool) - true means show all errors, false - hide, useful for override default setting
 */
	public function showErrors($a) {
		if($a == true) {
			error_reporting(E_ALL);
			ini_set("display_errors", 1);
		}
		else {
			ini_set("display_errors", 0);
		}
	}

##########################################################################
########################## DATABASE HANDLING #############################
##########################################################################


/**
 *	connectDB - creates connection to the database
 */
 	private $db;
	protected function connectDB(){
		$this->db = mysqli_connect($this->dbH, $this->dbL, $this->dbP, $this->dbD, $this->port);
		$this->executeQuery("SET NAMES utf8",1);
		$this->executeQuery("SET CHARACTER_SET utf8_general_ci",2);
		// Populate config variables
		$this->executeQuery("SELECT * FROM cms_settings",1);
		while($row = mysqli_fetch_assoc($this->result1)) {
			$this->{$row["featureName"]} = $row["featureValue"] == "true" ? 1 : ($row["featureValue"] == "false" ? 0 : $row["featureValue"]);
		}
		/* Transform restriced names into array */
		$a = $this->restricted_names;
		$this->restricted_names = explode(" ",$a);
	}


/**
 *	disconnectDB - closes connection to database
 */
	public function disconnectDB() {
		mysqli_close($this->db);
	}

/**
 *	lastInsertId - returns an integer with the last id (pk, ai)
 */
	public function lastInsertId() {
		return mysqli_insert_id($this->db);
	}


/**
 *	executeQuery = executes mysql query
 *		@param $q (string) - query
 *		@param $i (integer) - result indicator (allows nesting while loops with different mysqli resources
 */
	public function executeQuery($q,$i){
		$this->{"result".$i} = mysqli_query($this->db,$q);
	}


/*
 *	getCount - shorthand method to get count of rows
 *		@param $t (string) - table
 *		@param $w (string) - WHERE clause (optional)
 */
	public function getCount($t,$w='') {
		$a = mysqli_fetch_assoc(mysqli_query($this->db, "SELECT COUNT(*) AS 'id' FROM `$t` $w"));
		return $a["id"];
	}


/**
 *	dbBackup - creates full backup of current database state
 */
	public $dbBackupFileName= '';
	public function dbBackup($path='') {
		$result = "-- DATABASE BACKUP FOR $this->dbD \n";
		$result .= "-- Generation date and time: ".$this->convertDate(gmdate("Y-m-d"),false,"en")." ".gmdate("H:i:s")."\n";
		$result .= "-- Generated by: ".$_SESSION["userLogin"].' ('.$_SESSION["userRank"].')';
		$this->executeQuery("SHOW TABLES",1);
		while($row = mysqli_fetch_row($this->result1)){
			$at[] = $row[0];
		}
		foreach($at as $t) {
			$result .= "\n\n\n\n--\n-- Table structure for table `$t` \n--\n\n";
			$c = mysqli_query($this->db, "DESCRIBE ".$t);
			$cc = mysqli_num_rows($c);
			$tc = mysqli_fetch_row(mysqli_query($this->db, "SHOW CREATE TABLE ".$t));
			$ccc = $this->getCount($t);
			$result .= $tc[1].";\n\n";
			if($ccc > 0) {
				$result .= "--\n-- Dumping data for table `$t` \n--\n\n";
				$result .= "INSERT INTO `$t` (";
				$fields = array();
				$result2 = array();
				while($row = mysqli_fetch_row($c)) {
					$result2[] = "`".$row[0]."`";
					$fields[] = $row[0];
				}
				$result .= implode(",",$result2).") VALUES\n(";
				$this->executeQuery("SELECT * FROM ".$t,1);
				$i2 = 1;
				while($row = mysqli_fetch_assoc($this->result1)) {
					foreach($fields as $i => $field) {
					  $row[$field] = addslashes($row[$field]);
					  $row[$field] = preg_replace("/\n/","\\n",$row[$field]);
						$result .= "'".$row[$field]."'";
						if($i < count($fields)-1){
							$result .= ",";
						}
					}
					$result .= ")";
					if($i2 < $ccc) {
						$result .= ",\n(";
					}
					$i2++;
				}
				$result .= ";";
			}
		}
		$handle = fopen($path.'_sql/db-backup_'.str_replace(" ","_",gmdate("Y-m-d H:i:s")).'.sql','wb+');
		fwrite($handle,pack("CCC",0xef,0xbb,0xbf));
		if(fwrite($handle,$result) != false) {
			$r = true;
		}
		else {
			$r = false;
		}
		fclose($handle);
		$this->dbBackupFileName = '_sql/db-backup_'.str_replace(" ","_",gmdate("Y-m-d H:i:s")).'.sql';
		return $r;
	}


##########################################################################
########################## SESSIONS HANDLING #############################
##########################################################################


/**
 *	startSession - starts new session
 */
	public function startCMS() {
		ob_start();
		session_start();
		$this->loadTranslation();
		$this->getCMSInfo();
		$this->getLang(true);
		$this->getSessionInfo();
	}


/**
 *	checkSession - checks if user is rightly logged in
 */
	public function checkSession() {
		if(!isset($_SESSION["userLogin"])) {
			header("Location:/login");
			exit();
		}
		else {
			$login = $_SESSION["userLogin"];
			$userid = $_SESSION["userId"];
			if($this->getCount("cms_accounts","WHERE login='$login'") == 0 || $_SESSION["userHash"] != $this->unique_hash) {
				$this->destroySession();
				header("Location:/login");
				exit();
			}
			else if(time() - $_SESSION["timestamp"] > 3600) {
				$this->destroySession();
				header("Location:/login");
				exit();
			}
			else {
				$_SESSION["timestamp"] = time();
			}
		}
	}


/**
 *	clearBuffer - clears buffer
 */
	public function clearBuffer() {
		ob_end_flush();
	}


/**
 *	checkAccess - checks if current user has rights to view this module
 */
	public function checkAccess() {
		$r = $_SESSION["userRankId"];
		$this->executeQuery("SELECT * FROM cms_accounts_ranks WHERE id='$r'",1);
		$row = mysqli_fetch_assoc($this->result1);
		$out = false;
		switch($r) {
			case 1: // Lemon-Art - all access
			break;
			case 2: // Administrator - access to all modules with status='1'
				if($this->getCount("cms_modules","WHERE shortName='$this->p' AND status='1' AND lemonOnly='0'") == 0) {
					$out = true;
				}
			break;
			default: // All other users
				$this->executeQuery("SELECT * FROM cms_accounts_ranks_actions WHERE rank_id='$r' AND module_id='$this->mId' AND action_id='$this->aId'",2);
				$row = mysqli_fetch_assoc($this->result2);
				if($row["rank_id"] == "") {
					$out = true;
				}
			break;
		}
		if($out == true) {
			$this->setSessionInfo(false,$this->translate(121));
			if($this->getCount("cms_accounts_ranks_actions","WHERE rank_id='$r' AND module_id='$this->mId' AND action_id='22'") == 0) {
				header("Location:".$this->get_link());
			}
			else {
				header("Location:".$this->get_link($this->p));
			}
		}
	}


/**
 *	login - checking inputted information in login form, creates session variables if true
 */
	public function login() {
		$this->connectDB();
		$pLogin = $_POST["login"];
		$pPass = $_POST["password"];
		$pPassH = hash("sha256",$pPass);
		if((empty($pLogin)) || (empty($pPass)) || strpos($pLogin, " ")!== false) {
			$this->setInfo(false,$this->translate(3));
		}
		else {
			$this->executeQuery("SELECT *,cms_accounts.id AS id, cms_accounts_ranks.rank AS rank,cms_accounts_ranks.id AS rankId FROM cms_accounts LEFT JOIN cms_accounts_ranks ON cms_accounts.rank=cms_accounts_ranks.id WHERE login='$pLogin'",1);
			$r = mysqli_fetch_assoc($this->result1);
			if($r["login"] != "") {
				if($pPassH != $r["password"]) {
					$this->setInfo(false,$this->translate(4));
				}
				elseif($r["active"] == 0) {
					$this->setInfo(false,$this->translate(120));
				}
				elseif(empty($r["rank"])) {
					$this->setInfo(false,$this->translate(233));
				}
				else {
					$_SESSION["userLogin"] = $r["login"];
					$_SESSION["userName"] = $r["name"];
					$_SESSION["userRank"] = $r["rank"];
					$_SESSION["userRankId"] = $r["rankId"];
					$_SESSION["userColor"] = $r["color"];
					$_SESSION["userId"] = $r["id"];
					$_SESSION["userHash"] = $this->unique_hash;
					$this->saveAction("","","CMS","login");
					$_SESSION["timestamp"] = time();
                    $_SESSION["KCFINDER"] = array();
                    $_SESSION["KCFINDER"]["disabled"] = false;
					header("Location:".$this->get_link());
				}
			}




			else {
				$this->setInfo(false,$this->translate(5));
			}
		}
	}


/**
 *	destroySession - destroys all session information on logout
 */
	public function destroySession() {
		unset($_SESSION["KCFINDER"], $_SESSION["userLogin"],$_SESSION["userName"],$_SESSION["userRank"],$_SESSION["userrankId"],$_SESSION["userColor"],$_SESSION["userHash"],$_SESSION["allowed_ranks"]);
		session_destroy();
	}


##########################################################################
############################# CMS STARTUP ################################
##########################################################################

/**
 *	getCMSInfo - Gets module, action and id of current CMS request, creates module file to include
 *		$cms->p 	- module
 *		$cms->a 	- action
 *		$cms->id 	- id
 *		$cms->incP 	- module file
 *		$cms->mId	- module id
 *		$cms->aId	- action id
 */
	public $p;
	public $a;
	public $pB;
	public $mG;
	public $mId;
	public $aId;
	private function getCMSInfo() {
		$this->p = "start";
		$this->getLang(true);
		if(isset($_GET["p"])) {
			$this->executeQuery("SELECT * FROM cms_modules WHERE shortName='".$_GET["p"]."'",1);
			$row = mysqli_fetch_assoc($this->result1);
			if($row["plName"] != "") {
				$this->p = $_GET["p"];
				$this->pB = $row[$this->cmsL.'Name'];
				$this->mG = $row["moduleGroup"];
				$this->mId = $row["id"];
			}
		}
        if($row["plName"] == "" && $this->debug_mode == true && file_exists("_modules/cms-dev.php")) {$this->p = "dev";}
		$this->incP = "_modules/cms-".$this->p.".php";
		$this->a = isset($_GET["a"])?$_GET["a"]:'list';
		$this->executeQuery("SELECT * FROM cms_actions_labels WHERE action='$this->a'",1);
		$row = mysqli_fetch_assoc($this->result1);
		$this->aId = $row["id"];
		$this->aN = $row["label_".$this->cmsL];
		$this->executeQuery("SELECT * FROM cms_modules_actions WHERE module_id='$this->mId' AND action_id='$this->aId'",2);
		$row2 = mysqli_fetch_assoc($this->result2);
		if($this->p != "start") {
			if($this->aId == "" || $row2["module_id"] == "") {
				if($_SESSION["userRankId"] != 1) {$this->a = "list";}
				$this->setSessionInfo(false, $this->translate(423));
			}
		}
		$this->id = isset($_GET["id"])?$_GET["id"]:0;


		/* Change session time for Lemon-Art */
		$this->session_time = $_SESSION["userRankId"] == 1 ? 3600 : $this->session_time;
	}


/**
 *	getLang - gets language from URL
 *		@param $c (bool) - TRUE allows language that is only added, FALSE language must be activated. If fails it returns default language of CMS
 *		$cms->lang = language
 */
	public $lang;
	public function getLang($c=false) {
		$this->executeQuery("SELECT * FROM cms_langs WHERE main='1'",1);
		$row = mysqli_fetch_assoc($this->result1);
		$t = empty($row["id"]) ? $this->cmsL : $row["shortLang"];

		$this->lang = isset($_GET["l"])?$_GET["l"]:$t;
		if($this->getCount("cms_langs","WHERE added='1' ".($c == false ? "AND status='1' " : "")."AND shortLang='$this->lang'") == 0) {
            $this->lang = $t;
            header("Location:".$this->get_link());
        }
	}

/**
 *  link - function that returns link from the array, allows to change links between /cms, /admin and pure GET
 *      @param $t (string) - comma separated list of elements (module, action, id)
 *      @param $ln (string) - optional lang - needed for directing to different language (flags)

 *      return string
 */
    public function get_link($t="", $ln="") {
        /* Get setting, possible options:
            cms
            admin
            get
        */
        $type = $this->link_types;

        /* first always add the beginning of the link to the array */
        $l = "/".($type == "get" ? "cms.php?l=".$this->lang : $type.(!empty($ln) || !empty($this->lang) ? '/'.(empty($ln) ? $this->lang : $ln) : '')).(strlen($t) > 0 ? ($type == "get" ? '&' : '/') : '');


        if(!empty($t)) {
            $e = explode(",", $t);

            $lt[] = ($type == "get" ? 'p=' : '').$e[0];

            if(count($e) > 1) {
                $lt[] = ($type == "get" ? 'a=' : '').$e[1];

                if(count($e) > 2) {
                    $lt[] = ($type == "get" ? "id=" : "").$e[2].(count($e) > 3 ? (($type == "get" ? $e[3]."=" : ",".$e[3]."=").$e[4]) : "");
                }
            }
        }

        return $l.implode($type == "get" ? "&" : "/", $lt);
    }


/**
 *	populateMenu - populates cms menu tabs
 */
	public $m;
	public $ma;
	public function populateMenu() {
		$r = '';
		$r2 = $_SESSION["userRankId"];
		$w = $r2 != 1 ? "WHERE status='1'" : "";
		$this->executeQuery("SELECT * FROM cms_modules $w ORDER BY position ASC",1);
		while($row = mysqli_fetch_assoc($this->result1)) {
			if($r2 == 1 || $r2 == 2) {
				$r .= '<li><a href="'.$this->get_link($row["shortName"]).'"';
				if($this->p == $row["shortName"]) {
					$r .= ' class="linkb"';
				}
				$r .= '>'.$row[$this->cmsL."Name"].'</a></li>';
			}
			else {
			// Populate menu only for certain rank
				$this->executeQuery("SELECT * FROM cms_accounts_ranks WHERE id='$r2'",2);
				$row2 = mysqli_fetch_assoc($this->result2);

				$modules = explode("|",$row2["access"]);
				foreach($modules as $ac2) {
					list($module,$actions) = explode(":",$ac2);
					$ac3 = explode(",",$actions);
					foreach($ac3 as $action) {
						$access[$module][$action] = 1;
					}
				}
				if(count($access[$row["shortName"]]) > 0) {
					$r .= '<li><a href="'.$this->get_link($row["shortName"]).'"';
					if($this->p == $row["shortName"]) {
						$r .= ' class="linkb"';
					}
					$r .= '>'.$row[$this->cmsL."Name"].'</a></li>';
			}
			}
		}
		return $r;
	}


/**
 *	loadTranslation - loads CMS translation from Database
 */
	public $t;
	public function loadTranslation() {
		$this->executeQuery("SELECT * FROM cms_translation",111);
		while($row = mysqli_fetch_assoc($this->result111)) {
			$this->t["pl"][$row["tId"]] = $row["pl"];
			$this->t["en"][$row["tId"]] = $row["en"];
		}
	}


/**
 *	translate - translates given string
 *		@param $n (string) - string to be translated
 */
	public function translate($n) {
		return htmlspecialchars($this->t[$this->cmsL][$n]);
	}


/**
 *	saveAction - saves user's action
 *		@param $c1 (string) - custom data 1
 *		@param $c2 (string) - custom data 2
 *		@param $t  (string) - current module (optional)
 *		@param $a  (string) - current action  (optional)
 */
	public function saveAction($c1,$c2="",$t="",$a="") {
		$ip = $_SERVER["REMOTE_ADDR"];
		if(isset($_SESSION["userLogin"])) {
			$login = $_SESSION["userLogin"];
			$rank = $_SESSION["userRank"];
			$name = $_SESSION["userName"];
			$module = isset($t) ? $t : $this->p;
			$action = isset($a) ? $a : $this->a;
			$this->executeQuery("INSERT INTO cms_actions (`id`, `timestamp`, `ip`, `login`, `name`, `rank`, `module`, `action`, `custom1`, `custom2`) VALUES ('', NOW(), '$ip', '$login', '$name', '$rank', '$module', '$action', '".$this->esc($c1)."', '".$this->esc($c2)."')",112);
		}
	}


##########################################################################
################### DATA VALIDATION & MANIPULATION #######################
##########################################################################

/**
 *	compressHTML - removes unneccessary spaces, carriage returns and new lines
 * 		@param $html (string) - html to compress
 */
 	public function compressHTML($html) {
		return preg_replace("/\>\s+\</", "><", preg_replace("/\s{2,100}/", " ", str_replace(array("\r\n","\n", "\r"), "", $html)));
	}


/**
 *	showIntNameInput - determines whether to show InternalName input field in the form
 *		@param $pt (integer) - page type
 */
	public $agoil;
	public $sinf;
	public function showIntNameInput($pt){
		/* Get automatic creation variable based on current module */
		$this->agoil = $this->{$this->p."_int_auto"};
		if(($_SESSION["userRankId"] == 1) || ($this->agoil == false && $_SESSION["userRankId"] != 1 && !in_array($pt,array(5)) == true)) {
			$this->sinf = true;
		}
	}


/**
 *	validateInternalName - validates InternalName passed from the form
 *		@param $en (string)  - external name
 *		@param $in (string)  - internal name
 *		@param $pt (integer) - page type
 *		@param $old (string) - old internal name
 */
	public $finalIntName;
	public function validateInternalName($en,$in,$pt,$old) {

		/* Final internal name */
		$this->finalIntName = '';
		/* Automatic generation for specific module ON */
		if($this->agoil == true) {
			$this->finalIntName = $this->createIntName($en);
		}
		/* Automatic generation for specific module OFF */
		else{
			/* Page type */
			switch($pt) {
				/* Normal page */
				case 1:
					$this->finalIntName = empty($in) ? $this->createIntName($en) : mb_strtolower($in, "UTF-8");
				break;

				/*  2: Module page i.e. /news, /aktualnosci, /offers, /calendar */
				case 2:
					/* If Lemon-Art rank show the input */
					if($_SESSION["userRankId"] == 1) {
						$this->finalIntName = empty($in) ? $this->createIntName($en) : mb_strtolower($in, "UTF-8");
					}

					/* Other ranks can't change the name */
					else {
						$this->finalIntName = $old;
					}
				break;

				/* Invisible page & default */
				default:
				case 3:
					$this->finalIntName = $this->createIntName(empty($in) ? $this->createIntName($en) : mb_strtolower($in, "UTF-8"));
				break;
			}
		}
		preg_match('/^[a-zA-Z0-9ąćęłńóśźżĄĆĘŁŃÓŚŹŻ\-_\.\,]+/',$this->finalIntName,$m);
		return isset($m[0])?($m[0] === $this->finalIntName?(!in_array($this->finalIntName,$this->restricted_names)?true:false):false):false;
	}

/**
 *  checkExistingNames - only if $cms->urls is current-noid or full-noid, check all tables
 * 		@param $ct (string() - current table
 */
 	public function checkExistingNames($ct, $pname="", $parent="") {
		/* If there are no ID's in site URL, we have to check all internal names for all switched on modules' tables
		This will only work if we completely disallow leaving url/internal name empty. It's best to process external name internally and still update the table
		/* Modules' tables we have to look in case of checking against internal names */
		$tables = array();
		$names = array();
		$i = 0;

		/* Loop through all modules and see if they contain intName column */
		$this->executeQuery("SELECT DISTINCT c.table_name AS 'tn' FROM information_schema.columns c INNER JOIN cms_modules cm ON CONCAT('cms_',cm.shortName) = c.table_name WHERE c.column_name = 'intName' AND cm.lemonOnly = 0 AND cm.status = '1'".($this->gal_mg == false ? " AND cm.shortName != 'gallery'" : ""),115);
		while($row = mysqli_fetch_assoc($this->result115)) {
			$tables[] = $row["tn"];
		}

		/* Loop through all tables and increment $i if you find a match */
        $aa = $table == "cms_menu" ? $pname." = ".$parent." AND " : "";
		foreach($tables as $table) {
			$i += $this->getCount($table,$aa."intName='$this->finalIntName'".($ct == $table ? " AND id != '$this->id' AND lang='$this->lang'" : "").($ct == "cms_menu" ? " AND type NOT IN(3,5) AND external_site = 0" : ""));
		}

		if($i == 0) {
			return true;
		}
		else {
			return false;
		}
	}


/**
 *	validateEmail - validates given e-mail address
 *		@param $e (string) - e-mail address
 */
	public function validateEmail($e) {
		preg_match("/^(?:(?:[-a-zA-Z0-9!#$%&'*+\/=?^_`{|}~])+\\.)*[-a-zA-Z0-9!#$%&'*+\/=?^_`{|}~]+@\\w(?:(?:-|\\w)*\\w)*\\.(?:\\w(?:(?:-|\\w)*\\w)*\\.)*\\w{2,4}$/",$e,$c);
		if(count($c) > 0 && $c[0] == $e) {
			return true;
		}
		else {
			return false;
		}
	}


/**
 *	createValidName - creates valid internal name for files, it doesn't make names lowercase, used for files mostly
 *		@param $n (string) - name to be processed
 */
	public function createValidName($n) {
		return str_replace(array("@","$","#","!","?","/","\\","%","^","&","*","(",")","+","=","{","}","[","]","\"","'",";",":",",","<",">"),"",str_replace(" ","_",str_replace(array("ą","ć","ę","ł","ń","ó","ś","ź","ż"),array("a","c","e","l","n","o","s","z","z"),$n)));
	}


/**
 *	createIntName - creates valid internal name for pages, galleries, offers etc.
 *		@param $n (string) - name to be processed
 */
	public function createIntName($n) {
		$v1 = str_replace(array("ą","ć","ę","ł","ń","ó","ś","ź","ż"),array("a","c","e","l","n","o","s","z","z"),mb_strtolower($n,"UTF-8"));
		$v2 = ltrim(rtrim($v1));
		$v3 = str_replace(array(" ","."),array("-",""),$v2);
		$v4 = str_replace(array("!","@","#","$","%","^","&","*","(",")","+","=","[","{","]","}","\\","|","<",",",">","/","?","~","`","'",'"',":"),"",$v3);
		$v5 = preg_replace('/-{2,5}/','-',$v4);
		return $v5;
	}

/**
 *	esc - escapes suspisious characters before they are passed to database query
 *		@param $d (string) - string to be processed
 */
	public function esc($d) {
		return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $d);
	}


/**
 *	getRandomName - creates random, unique name
 */
	public function getRandomName() {
		list($na,$nb) = explode(" ",microtime());
		return $nb.rand(10,99);
	}


/**
 *	trimString - returns part of given string limiting it to x characters
 *		@param $s (string) - name to be processed
 *		@param $l (integer)- length of the result
 */
	public function trimString($s, $l) {
		$cc = explode(" ", strip_tags($s));
		$r = '';
		for($i=0; $i<count($cc); $i++) {
			if(strlen($r) < $l) {
				$r .= $cc[$i].' ';
			}
			else {
				break;
			}
		}
		return ltrim(rtrim($r));
	}


##########################################################################
################################ IMAGES ##################################
##########################################################################

/**
 *	processImage - takes all image dimensions sets from cms_image_dimensions and creates the files accordingly to the settings
 *		@param $f  (array) 	- file details array
 *		@param $fo (string)	- old file name
 *		@param $nn (bool) 	- force new name to be created, false will allow to reprocess existing image
 *		@param $m  (string) - module
 */
	public function processImage($f, $fo, $nn=true, $m='') {
		$n = $nn == true ? $this->getRandomName() : '';
		$mo = empty($m) ? $this->p : $m;
		$e = 0;
		$this->executeQuery("SELECT * FROM cms_image_dimensions WHERE moduleId=(SELECT id FROM cms_modules WHERE shortName='$mo') ORDER BY id DESC",1);
		while($row = mysqli_fetch_assoc($this->result1)) {
			// Get size of the original image
			list($w,$h) = getimagesize($f["tmp_name"]);
			// If portrait
			if($h > $w) {
				$fit = $row["portrait"] == "fit" ? $row["height"] : 0;
				$fill = $row["portrait"] == "fill" ? $row["height"] : 0;
			}
			// If landscape or square
			else {
				$fit = $row["landscape"] == "fit" ? $row["height"] : 0;
				$fill = $row["landscape"] == "fill" ? $row["height"] : 0;
			}
			$dir = $row["name"] == "Lemon Thumb" ? "_lemon" : $row["width"].'x'.$row["height"];

			if($this->makeThumbnail($f,"_images_content/".$mo."/".$dir,$n, $row["width"],"width",$fit,$fill,$row["quality"]) == false) {
				$e++;
			}
			elseif($this->a == "edit") {
				if(!empty($fo)) {
					unlink("_images_content/".$mo."/".$dir."/".$fo);
				}
			}
		}
		return array("errors"=>$e,"name"=>($e > 0 ? $fo : $this->fn));
	}


/**
 *	centerImage - creates <img> element with given path setting correct margins to be centered vertically and horizontally
 *		@param $i (string)  - image path
 *		@param $x (integer) - width of the container
 *		@param $y (integer) - height of the container
 *		@param $a (string)	- alt attribute
 */
	public function centerImage($i,$x,$y, $a='') {
		list($w,$h) = getimagesize($i);
		$mx = ($x - $w) / 2;
		$my = ($y - $h) / 2;
		return '<img src="/'.$i.'" style="margin-top:'.$my.'px;margin-left:'.$mx.'px;" alt="'.$a.'" title="'.$a.'" height="'.$h.'" width="'.$w.'" />';
	}


/**
 * 	cropImage - crops an image
 *		@param $f (string)	- image path
 *		@param $x (integer) - x-coordinate of source point
 *		@param $y (integer) - y-coordinate of source point
 *		@param $w (integer) - width
 *		@param $h (integer) - height
 *		@param $m (string)  - module
 *		@param $q (integer) - quality
 */
 	public function cropImage($f, $x, $y, $w, $h, $m, $q) {
		/* If quality isn't set, set to 75 */
		if((int)$q == 0 || empty($q)) {
			$q = 100;
		}
		$ri = imagecreatetruecolor($w, $h);
		$file = "_images_content/".$m."/_cropped/".$f;
		$result = "_images_content/".$m."/_cropped/".$f;
		$e = strtolower(pathinfo($file, PATHINFO_EXTENSION));
		switch ($e) {
			case "jpg": case "jpeg":
				$ni = imagecreatefromjpeg($file);
			break;
			case "gif":
				$ni = imagecreatefromgif($file);
			break;
			case "png":
				imagealphablending($ri, false);
				imagesavealpha($ri, true);
				$ni = imagecreatefrompng($file);
				imagealphablending($ni, true);
			break;
		};
		imagecopyresampled($ri, $ni, 0, 0, $x, $y, $w, $h, $w, $h);
		imageinterlace ($ri, 1);
		switch ($e) {
			case "jpg":
			case "jpeg":
			case "gif":
				imagejpeg($ri, $result, $q);
			break;
			case "png":
				imagepng($ri, $result);
			break;
		}
		imagedestroy($ri);
		imagedestroy($ni);
		return is_file($result);
	}


/**
 *	makeThumbnail - makes thumbnail of image
 * 		@param $i 	(array) 	- image array
 *		@param $pt 	(string)	- destination folder
 *		@param $n 	(string)	- file name
 *		@param $ts 	(integer) 	- thumbnail main dimension
 *		@param $t 	(string)  	- main dimension type
 *		@param $ft 	(integer)	- fit value (if zero, it's disregarded)
 *		@param $fl 	(integer)	- fill value (if zero, it's disregarded)
 *		@param $q 	(integer)	- quality
 */
	public function makeThumbnail($i, $pt, $n, $ts, $t, $ft, $fl, $q=100) {
		if($t == "height" && $ts != 0) {
			$th = $ts;
		}
		else {
			$tw = $ts;
		}
		$e = strtolower(strrchr($i["name"], "."));
		$fn = empty($n) ? $i["name"] : $n.$e;
		$this->fn = $fn;
		$tn = $i["tmp_name"];

		list($w, $h) = getimagesize($tn);
		if($t == "height") {
			if($ts == 0) {
				$tw = $w;
				$th = $h;
			}
			else {
				$tw = ($ts * $w) / $h;
			}
			if(($ft > 0 ) && ($tw > $ft)) {
				$th = ($ts * $ft) / $tw;
				$tw = $ft;
			}
			elseif(($fl > 0) && ($tw < $fl)) {
				$th = ($fl * $ts) / $tw;
				$tw = $fl;
			}
		}
		else {
			if($ts == 0) {
				$tw = $w;
				$th = $h;
			}
			else {
				$th = ($ts * $h) / $w;
			}
			if(($ft > 0) && ($th > $ft)) {
				$tw  = ($ts * $ft) / $th;
				$th = $ft;
			}
			elseif(($fl > 0) && ($th < $fl)) {
				$tw = ($fl * $ts) / $th;
				$th = $fl;
			}
		}
		$ri = imagecreatetruecolor($tw,$th);
		switch ($e) {
			case ".jpg": case ".jpeg":
				$ni = imagecreatefromjpeg($tn);
			break;
			case ".gif":
				$ni = imagecreatefromgif($tn);
			break;
			case ".png":
				imagealphablending($ri, false);
				imagesavealpha($ri, true);
				$ni = imagecreatefrompng($tn);
				imagealphablending($ni, true);
			break;
		};
		imagecopyresampled($ri, $ni, 0,0,0,0, $tw, $th,$w,$h);
		imageinterlace ($ri, 1);
		/* If quality isn't set, set to 75 */
		if((int)$q == 0 || empty($q)) {
			$q = 100;
		}
		switch ($e) {
			case ".jpg":
			case ".jpeg":
			case ".gif":
				imagejpeg($ri, "$pt/$fn", $q);
			break;
			case ".png":
				imagepng($ri, "$pt/$fn");
			break;
		}
		imagedestroy($ri);
		imagedestroy($ni);
		return is_file("$pt/$fn");
	}


##########################################################################
############################# DATE & TIME ################################
##########################################################################


/**
 *	convertDate - converts datatime data type to string in relevant language
 *		@param $dt (date)  - date
 * 		@param $i (bool)   - returned string to go to input field
 *		@param $l (string) - language
 */
	public function convertDate($dt,$i=false,$l="pl") {
		list($y,$m,$d) = explode("-",$dt);
		$mnt["pl"] = array("01"=>"Stycznia","02"=>"Lutego","03"=>"Marca","04"=>"Kwietnia","05"=>"Maja","06"=>"Czerwca","07"=>"Lipca","08"=>"Sierpnia","09"=>"Września","10"=>"Października","11"=>"Listopada","12"=>"Grudnia");
		$mnt["en"] = array("01"=>"January","02"=>"February","03"=>"March","04"=>"April","05"=>"May","06"=>"June","07"=>"July","08"=>"August","09"=>"Septempber","10"=>"October","11"=>"November","12"=>"December");
		$mnt["de"] = array("01"=>"Januar","02"=>"Februar","03"=>"März","04"=>"April","05"=>"Mai","06"=>"Juni","07"=>"Juli","08"=>"August","09"=>"September","10"=>"Oktober","11"=>"November","12"=>"Dezember");
		if($i == true) {
			$nd = $d."-".$m."-".$y;
		}
		else {
			if($d[0] == 0) {
				$d = substr($d,-1,1);
			}
			switch($l) {
				case "pl":
					$nd = $d." ".$mnt[$l][$m]." ".$y;
				break;
				case "en":
					$nd = $d;
					if(substr($d,-1,1) == 1 && $d != 11) {$nd .= "st";}
					elseif(substr($d,-1,1) == 2 && $d != 12){$nd .= "nd";}
					elseif(substr($d,-1.1) == 3 && $d != 13) {$nd .= "rd";}
					else {$nd .= "th";}
					$nd .= " ".$mnt[$l][$m]." ".$y;
				break;
				case "de":
					$nd = $d.'. '.$mnt[$l][$m].' '.$y;
				break;
			}
		}
		return $nd;
	}


##########################################################################
############################## MESSAGES ##################################
##########################################################################


/**
 *	setInfo - displays an info message
 *		@param $t (bool)   - type of the message (true - ok, false - error)
 *		@param $i (string) - message
 */
	public $is = 0;
	public function setInfo($t,$i) {
		echo '<script type="text/javascript">displayInfo(\''.($t==true?'ok':'error').'\',\''.$i.'\');</script>';
		$this->is = 1;
	}


/**
 *	setSessionInfo - sets message to be displayed after redirection
 *		@param $t (bool)   - type of the message (true - ok, false - error)
 *		@param $i (string) - message
 */
	public function setSessionInfo($t,$i) {
		$_SESSION[($t==true?'ok':'er')] = $i;
	}


/**
 *	getSessionInfo - receives and displays message from session
 */
	public $infoT;
	public $info;
	public function getSessionInfo() {
		if(isset($_SESSION["ok"]) || isset($_SESSION["er"])) {
			$this->infoT = (isset($_SESSION["ok"])?'ok':'error');
			$this->info = $_SESSION[(isset($_SESSION["ok"])?'ok':'er')];
			unset($_SESSION["ok"]);
			unset($_SESSION["er"]);
			$this->is = 1;
		}
	}


/**
 *	buildLink - building a link from a database row, module recognition etc.
 *		@param $row	(array)	-	database row
 *		@param $m	(string)-	module
 */
 	public function buildLink($row, $m) {
		if($this->urls == "current-noid") {
			// If there is more than one active language add it before the link
			$l = $this->activeLangs > 1 ? '/'.$this->lang : '';
			$l .= '/'.$row["intName"];
		}
		elseif($this->urls == "current") {
			// If there is more than one active language add it before the link
			$l = $this->activeLangs > 1 ? '/'.$this->lang : '';
			$l .= '/'.$row["intName"];
		}
		else{
			switch($m) {
				case "news":
				case "offers":
				case "blog":
					$l .= $this->getModuleLink($m,true).'/'.($this->urls == "full" ? $row["id"].',' : '').$row["intName"];
				break;
				case "gallery":
					if($row["gallery_id"] == 0) {
						$l .= $this->getModuleLink("gallery", true).'/'.($this->urls == "full" ? $row["id"].',' : '').$row["intName"];
					}
					else {
						$this->executeQuery("SELECT * FROM cms_gallery WHERE id = '".$row["gallery_id"]."'",116);
						$row2 = mysqli_fetch_assoc($this->result116);
						$l .= $this->getModuleLink("gallery",true).'/'.$row2["intName"].'/'.($this->urls == "full" ? $row["id"].',' : '').$row["intName"];
					}
				break;
				case "product":
				break;
				case "menu":
					$ll = array();
					// Get parent id
					$pId = $row["parentId"];

					// Loop through the pages to get all hierarchy
					for($i=$row["level"]-1;$i>=0;$i--) {
						$this->executeQuery("SELECT * FROM cms_menu WHERE id='$pId'",11);
						$row11 = mysqli_fetch_assoc($this->result11);
						$ll[$i] = $row11["intName"];
						$pId = $row11["parentId"];
					}

					// If there is at least one parent, sort the array from last to first
					count($ll)>0 && ksort($ll);

					// If there is at least one parent, include extra slash at the beginning
					// Join all names
					$l .= (count($ll)>0?'/':'').implode("/",$ll);

					// Add page name
					$l .= '/'.$row["intName"];
				break;
			}
		}

		return $l;
	}


/**
 *	getModuleLink() - returns full link (according to settings) for a specific module
 *		@param $m (string)	- module name
 *		@param $a (bool)	- flag for conditional output, false : show full link to the page, true : show part of the link to attach element
 *  	@param $exc (bool)	- if this flag is true, it will look for an exception instead of a module
 */
	public function getModuleLink($m, $a = false, $exc = false) {
		// Get details of the page with connection to given module
		if($exc == false) {
			$this->executeQuery("SELECT *,cm.id AS 'id' FROM cms_menu cm INNER JOIN cms_modules co ON cm.connection = co.id WHERE co.shortName='".$this->esc($m)."' AND cm.lang='$this->lang' AND cm.connection_type='module'",113);
		}
		else {
			$this->executeQuery("SELECT *, cm.id AS 'id' FROM cms_menu cm INNER JOIN cms_menu_exceptions cme ON cm.connection = cme.id WHERE cme.name_int='".$this->esc($m)."' AND cm.lang='$this->lang' AND connection_type='exception'",113);
		}
		$row = mysqli_fetch_assoc($this->result113);

		// If page wasn't found return empty anchor
		if(empty($row["id"])) {
			return "#";
		}
		// Page was found
		else {
			// If there is more than one active language add it before the link
			$link = $this->activeLangs > 1 ? '/'.$this->lang : '';

			// Are urls to be shown in full?
			if($this->urls == "full-noid") {
				$lvl = $row["level"];
				$tree = array();
				$tree[$lvl]["in"] = $row["intName"];
				$tree[$lvl]["id"] = $row["id"];
				$pId = $row["parentId"];

				// Loop through the pages to get all hierarchy
				for($i=$lvl-1;$i>=0;$i--) {
					$this->executeQuery("SELECT * FROM cms_menu WHERE id='$pId'",1);
					$row = mysqli_fetch_assoc($this->result1);
					$pId = $row["parentId"];
					$tree[$i]["in"] = $row["intName"];
					$tree[$i]["id"] = $row["id"];
				}

				// Sort the array from last to first
				ksort($tree);

				// Build the link
				foreach($tree as $lev => $arr) {
					$link .= '/'.$arr["in"];
				}
			}
			// If not build the link only from this page and its id
			else {
				$link .= '/'.$row["intName"];
			}

			// Return full link
			return $link;
		}
	}
}
?>