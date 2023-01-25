<?php
/* class.cmsgetcontent.php
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

/* Class Tree
	001.	getDefaultInfo()
	002.	cookieInfo()
	003.	getPageFront()
	004. 	getLangsFront()
	005.	createNavigationFromSet()
	006.	createNavigation()
	007.	getNavigation()
	008.	showMenuItem()
	009.	buildLink()
	010.	getContent()
	011.	getFPage()
	012.	getModuleLink()
	013.	getLemonPage()
	014.	getMeta()
	015.	getNews()
	016.	getGallery()
	017.	getOffers()
	018.	getCaptcha()
	019.	checkCaptcha()
	020.	getScripts()
	021.	getSlide()
	022.	getPromobox()
	023.	translate()
*/


include_once "class.cmscontrol.php";
class cmsGetContent extends cmsControl{
	function __construct() {
        $this->showErrors(false);
		$this->connectDB();
		$this->getLang();

        /* Front end translation */
        if(file_exists("class.translation.php")) {
            include_once "class.translation.php";
            Translation::set_lang($this->lang);
        }

		$this->loadTranslation();
		$this->getDefaultInfo();
		$this->getPageFront();
		$this->getContent();
		$this->getScripts();
		$this->getPromoBox();
	}

    private $scrollable = array(
       /* "mod-33",
        "exc-3",
        "exc-6",
        "exc-4",
        "exc-5",
        "exc-7",
        "exc-8"*/
    );

   public function trans($input) {
        $translation = array(
            "pl" => array (
            ),
            "en" => array (
            )
        );

        $in = mb_strtolower(str_replace("_", " ", $input), "UTF-8");

        foreach($translation["pl"] as $i => $label) {
            if($in == mb_strtolower($label, "UTF-8") && !is_numeric($input)) {
                echo $translation[$this->lang][$i];
                break;
            }
            else if($i === $input) {
                return $translation[$this->lang][$i];
            }
        }
    }

/**
 *	getDefaultInfo - gets default information for the language
 */

	public $default_lang;
	public $cl_default_page;
 	private function getDefaultInfo() {
		$this->executeQuery("SELECT * FROM cms_settings_defaults WHERE lang_id = (SELECT id FROM cms_langs WHERE shortLang='$this->lang')",119);
		while($row = mysqli_fetch_assoc($this->result119)) {
			${$row["type"]} = $row["value"];
		}
		$this->{"pfDef_".$this->lang} = $default_page;
		$this->mTdef = $meta_title;
		$this->mKdef = $meta_keys;
		$this->mDdef = $meta_desc;

		/* Added 2.7.1: Get default language and do not show language in URLs */
		$this->executeQuery("SELECT * FROM cms_langs WHERE main='1'",1);
		$row = mysqli_fetch_assoc($this->result1);
		$this->default_lang = $row["shortLang"];

		/* Added 2.7.1: Dynamic homa page for current language */
		$this->cl_default_page = $this->{"pfDef_".$this->lang};
	}

    public function replaceKcFinderPaths($c) {
        return str_replace("/_scripts/cms/kcfinder/upload/images/", "/images/", str_replace("/_scripts/cms/kcfinder/upload/files/", "/files/", $c));
    }

/**
 *	cookieInfo - enables the cookie information needed for legal purposes
  */

  	public function cookieInfo() {
		/* Check if cookies are enabled */
		if($this->cookies_enabled == true) {

			/* Check if cookies doesn't exist */
			if(!isset($_COOKIE["laaccept"])) {

				$r = '<div id="cookie-info-wrap" class="o-90 trans">
						<div id="cookie-info">
							<a href="#" id="cookie-info-accept">'.$this->translate(504).'</a>
                            <span>'.$this->cookies_content.' </span>
						</div>
					</div>';

				return $this->compressHTML($r);
			}
		}
	}


/**
 *	getPageFront - gets current page, if none - default page from cms is taken
 */
	public $id;
	private $activeLangs;
	private function getPageFront() {

        $scroll = false;

        /* Addition for scrollable pages */
        $this->executeQuery("SELECT * FROM cms_menu WHERE intName='{$_GET["p"]}' AND lang='$this->lang'",1);
        $r = mysqli_fetch_assoc($this->result1);

        if($r["connection"] > 0) {
            foreach($this->scrollable as $i => $n) {

                list($type, $id) = explode("-", $n);

                $t = $type == "mod" ? "module" : "exception";

                if($r["connection"] == $id && $r["connection_type"] == $t) {
                    $scroll = true;
                }
            }
        }

		// Page is set in the URL
		if(isset($_GET["p"]) && $scroll == false) {
			$this->pf = $_GET["p"];
			$this->id = !empty($_GET["id"]) ? $_GET["id"] : 0;

		}
		// Page is not known, get id of default name for the language and find its internal name
		else {
			$this->id = $this->{"pfDef_".$this->lang};
			$this->executeQuery("SELECT intName FROM cms_menu WHERE id='$this->id'",1);
			$r = mysqli_fetch_assoc($this->result1);
			$this->pf = $r["intName"];
		}

		/* Count number of active languages. If only one, do not display language in links */
		$this->activeLangs = $this->getCount("cms_langs","WHERE added='1' AND status='1'");
	}

/**
 *	getLangsFront - gets language array and outputs it if necessary
 *		@param	$c	(string)	-	optional classes you want to apply on LI elements
 */
 	public function getLangsFront($c) {
		$r = '<ul id="languages">';
		if($this->activeLangs > 1) {
			$this->executeQuery("SELECT * FROM cms_langs WHERE added='1' AND status='1' ORDER BY id ASC",117);
			while($row = mysqli_fetch_assoc($this->result117)) {
				$r .= '<li class="'.$c.''.($row["shortLang"] == $this->lang ? ' active' : '').'" id="lang-'.$row["shortLang"].'"><a href="/'.($row["shortLang"] != $this->default_lang ? $row["shortLang"] : '').'" title="'.$row["longLang"].'">&nbsp;</a></li>';
			}
		}
		$r .= '</ul>';
		return $r;
	}

/**
 *	createNavigationFromSet - sets up a navigation from pre-defined set in CMS
 *		@param $set_name (string)	-	unique name of the set
 */
 	private $navigation_id;
	private $from_set = array();
 	public function createNavigationFromSet($set_name, $levels = array(0)) {
		$this->executeQuery("SELECT id FROM cms_navigation WHERE name_int='$set_name' AND lang='$this->lang'",1);
		$row = mysqli_fetch_assoc($this->result1);
		$this->from_set[$set_name] = $row["id"];
		return $this->createNavigation($set_name, 0, $levels);
	}


/**
 *	createNavigation - sets up a navigation settings and returns the html
 *		@param $nav_id (string)		-	the name of the ID of navigation UL element
 *		@param $id (integer)		-	id of the page to return the navigation for. If 0 it means return all pages
 *		@param $levels (array)		-	levels array to limit navigation levels to be returned
 *		@param $classes (array)		-	classes to be applied to LI element for each link
 *		@param $order (array)		-	ordering of links
 */
	public function createNavigation($nav_id, $id = 0, $levels = array(0,1,2), $classes = array(0=>"lvl0",1=>"lvl1",2=>"lvl2"), $order = array(0=>"ASC", 1=>"ASC", 2=>"ASC")) {
		$start_level = 0;

		if($id > 0) {
			$this->executeQuery("SELECT * FROM cms_menu WHERE id='$id'",1);
			$row = mysqli_fetch_assoc($this->result1);
			$start_level = $row["level"];
		}
		$return = $this->getNavigation($id, $start_level, $nav_id, $classes, $order, $levels);
		return $return;
	}

/**
 *	getNavigation - gets one level of navigation
 *		@param $id (integer)		-	id of the page to return the navigation for. If 0 it means return all pages
 *		@param $level (integer) 	- 	level of current page
 *		@param $classes (array)	 	-	classes to be applied to LI element for each link
 *		@param $order (array)		-	ordering of links
 *		@param $levels (array)		- 	levels array to limit navigation levels to be returned
 */
	private function getNavigation($id, $level, $nav_id, $classes, $order, $levels) {
		if($level == 0) {
			$return .= '<ul id="'.$nav_id.'" class="navigation">';
		}
		else {
			$return .= '<ul id="'.$nav_id.'-sub-'.$id.'" class="nav-ul-'.$level.'">';
		}

		/* Check if current navigation is to be taken from pre-defined set */
		if(in_array($nav_id, array_keys($this->from_set))) {
			$this->executeQuery("SELECT * FROM cms_menu cm
				INNER JOIN cms_navigation_pages cnp ON cnp.page_id = cm.id AND cnp.navigation_id='".$this->from_set[$nav_id]."'
			WHERE parentId='$id' AND cnp.status='1' AND cm.lang='$this->lang' AND type NOT IN (3,5) ORDER BY ".($level == 0 ? 'cnp.position' : 'cm.position')." ".$order[$level], $id);
		}
		else {
			$this->executeQuery("SELECT * FROM cms_menu WHERE parentId='$id' AND status='1' AND lang='$this->lang' AND type NOT IN (3,5) ORDER BY position ".$order[$level], $id);
		}
		while($row = mysqli_fetch_assoc($this->{"result".$id})) {
			/* Number of children */
			$c = $this->getCount("cms_menu", "WHERE parentId='".$row["id"]."' AND status='1' AND type NOT IN(3,5)");

			/* Active/current class */
			$cu = $this->page[$row["level"]]["id"] == $row["id"] ? ' active current' : '';

            $exc = $row["connection"] > 0 ? " ".($row["connection_type"] == "module" ? "mod" : "exc")."-".$row["connection"] : "";

			$return .= '<li class="'.$classes[$row["level"]].''.($c > 0 ? ' submenu-true nav-sub-'.$row["level"] : '').''.$cu.''.$exc.'" id="'.$nav_id.'-'.$row["level"].'-'.$row["id"].'">'.$this->showMenuItem($row,$c);

			/* Recursive */
			if($this->getCount("cms_menu","WHERE parentId='".$row["id"]."' AND type NOT IN(3,5) AND status='1'") > 0 && in_array($row["level"]+1, $levels)) {
				$return .= $this->getNavigation($row["id"], $row["level"]+1, $nav_id, $classes, $order, $levels);
			}

			$return .= '</li>';
		}

		$return .= '</ul>';

		return $return;
	}


/**
 *	showMenuItem - populates link accordingly to settings
 *		@param $r (array)   - database row
 *		@param $c (integer) - count of sublinks
 *		@param $lc (integer)- count of added and active languages
 */
	private function showMenuItem($r,$c) {
		$l='';
		$id = $r["id"];

		// If page has children, add class for submenu interaction
		$sub = $c > 0 ? ' lvl'.$r["level"].'sub' : '';

		// If page has children, add unique if for submenu interaction
		$sub1 = $c > 0 ? ' id="sub-'.$id.'"' : '';

		// Page points to external site and url is not blank
		if($r["external_site"] == 1 && $r["external_site_link"] != "") {
			$l = $r["external_site_link"];
		}
		// Normal Link
		else {
			// If page has children but is not clickable - make an anchor only
			if($c > 0 && $r["clickable"] == 0) {
				$l = "";
			}
			// Normal Link
			else {
				// If there is more than one active language add it before the link
				/* Added 2.7.1: Do not show language if default lang */
				$l = $this->activeLangs > 1 && $this->lang != $this->default_lang ? '/'.$this->lang : '';

				// If not build the link only from this page and its id
				if($this->urls == "current-noid") {
					/* Added 2.7.1: If default page leave just / */
					$l .= '/'.($r["id"] != $this->cl_default_page ? $r["intName"] : '');
				}
				// Are urls to be shown in full?
				else {
					$ll = array();
					// Get parent id
					$pId = $r["parentId"];

					// Loop through the pages to get all hierarchy
					for($i=$r["level"]-1;$i>=0;$i--) {
						$this->executeQuery("SELECT * FROM cms_menu WHERE id='$pId'",11);
						$row11 = mysqli_fetch_assoc($this->result11);
                        if($row11["url_use"] == 1) {
						  $ll[$i] = $row11["intName"];
						  $pId = $row11["parentId"];
                        }
					}

					// If there is at least one parent, sort the array from last to first
					count($ll)>0 && ksort($ll);

					// If there is at least one parent, include extra slash at the beginning
					// Join all names
					$l .= (count($ll)>0?'/':'').implode("/",$ll);

					// Add page name
					$l .= ($this->lang != $this->default_lang && $r["id"] == $this->cl_default_page ? '' : '/').($r["id"] != $this->cl_default_page ? $r["intName"] : '');
				}
			}
		}

		// Return full HTML
		return '<a'.($l != "" ? ' href="'.$l.'"'.($r["external_site"] == 1 ? '' : '') :'').'>'.$r["extName"].'</a>';
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
                case "articles":
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
                        if($row11["url_use"] == 1) {
						  $ll[$i] = $row11["intName"];
						  $pId = $row11["parentId"];
                        }
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
 *	getContent - outer method to get page content
 */
	public $metaTitle;
	public $metaKeys;
	public $metaDesc;
	public $pageContent = '';
	public $pageTitle = '';
	public $elementTitle = '';
	public $curLevel = 1;
	public $page = array();
	public $object_type = "page";
	public $slide_id = 0;
	public $gallery_id = 0;
	private function getContent() {
		// Meta tags values get global defaults from configuration settings
		$this->metaTitle = $this->mTdef;
		$this->metaKeys = $this->mKdef;
		$this->metaDesc = $this->mDdef;

		/* If $cms->urls is current-noid, we have to look at all possible tables to find the boject by its internal name */
		if($this->urls == "current-noid" || $this->urls == "full-noid") {
			$this->executeQuery("SELECT DISTINCT
									c.table_name AS 'tn',
									ce.id,
									ce.intName
								FROM information_schema.columns c
									INNER JOIN cms_modules cm ON CONCAT('cms_', cm.shortName) = c.table_name
									LEFT JOIN cms_menu ce ON ce.connection_type = 'module' AND ce.connection = cm.id AND ce.lang='$this->lang'

								WHERE
									c.column_name = 'intName'
									AND cm.lemonOnly = 0
									AND cm.status = '1'
		".($this->gal_mg == false ? " AND cm.shortName != 'gallery'" : ""),115);

			while($row = mysqli_fetch_assoc($this->result115)) {
				$tables[$row["tn"]]["table"] = $row["tn"];
				$tables[$row["tn"]]["id"] = $row["id"];
				$tables[$row["tn"]]["name"] = $row["intName"];
			}

			foreach($tables as $tm => $table) {
				$this->executeQuery("SELECT * FROM ".$table["table"]." WHERE intName = '$this->pf' AND lang='$this->lang'",1);
				$row = mysqli_fetch_assoc($this->result1);
				if($row["id"] != "") {
					if($table["table"] != "cms_menu") {
						$_GET["title"] = $row["intName"];
						$this->pf = $table["name"];
					}
					$this->id = $row["id"];
					break;
				}
			}
		}
        if(empty($this->id)) {
            $this->executeQuery("SELEcT * FROM cms_menu WHERE connection_type='exception' AND connection = 2", 11);
            $r11 = mysqli_fetch_assoc($this->result11);
            $url = $this->buildLink($r11, "menu");
            http_response_code(404);
            $this->id = $r11["id"];
            $this->pf = $r11["intName"];
        }

		// Get all details
		$this->executeQuery("
			SELECT
				*,
				cm.id AS 'id',
				cme.name_int AS 'exceptName'
			FROM cms_menu cm
				LEFT JOIN cms_modules cm2 ON cm.connection = cm2.id AND cm.connection_type='module'
				LEFT JOIN cms_menu_exceptions cme ON cme.id = cm.connection AND cm.connection_type='exception'
			WHERE
				cm.intName='$this->pf' ".
				(empty($_GET["title"]) ? "AND cm.id='$this->id'" : "AND cm.id=(CASE WHEN IFNULL(cm2.id,0) = 0 THEN '$this->id' ELSE cm.id END)")
				."
		",1);
		$row = mysqli_fetch_assoc($this->result1);
		$level = $row["level"];
		$this->slide_id = $row["slideId"];
		$this->gallery_id = $row["galleryId"];
		$this->curLevel = $row["level"];


		// Set current level page details
		$this->page[$this->curLevel] = array(
										"name"=>$row["extName"],
										"iname"=>$row["intName"],
										"slide"=>$row["slideId"],
										"parent"=>$row["parentId"],
										"id"=>$row["id"],
										"gallery"=>$row["galleryId"],
										"connection"=>$row["connection"],
										"connection_type"=>$row["connection_type"]);

		// Loop through levels to get full hierarchy
		for($i=$level-1;$i>=0;$i--) {
			$pId = $this->page[$i+1]["parent"];
			$this->executeQuery("SELECT * FROM cms_menu WHERE id='$pId'",2);
			$row2 = mysqli_fetch_assoc($this->result2);
			$this->page[$i] = array(
								"name"=>$row2["extName"],
								"iname"=>$row2["intName"],
								"slide"=>$row2["slideId"],
								"parent"=>$row2["parentId"],
								"id"=>$row2["id"],
								"gallery"=>$row2["galleryId"],
								"connection"=>$row2["connection"],
								"connection_type"=>$row2["connection_type"]);
		}

		// Slideshow has not been assigned for this page
		if(empty($this->slide_id)) {
			$theSlide = 0;

			// Loop through $this->page array to find nearest parent with slideshow assigned
			for($i=$this->curLevel-1;$i>-1;$i--) {
				if($this->page[$i]["slide"] != 0 && $theSlide == 0) {
					$theSlide = $this->page[$i]["slide"];
				}
			}

			// If slideshow is still not found - try to get "main"
			if($theSlide == 0) {
				$this->executeQuery("SELECT * FROM cms_slideshow WHERE main='1' AND lang='$this->lang'",3);
				$row3 = mysqli_fetch_assoc($this->result3);
				$this->slide_id = $row3["id"];
			}
			// Otherwise get parent's slideshow
			else {
				$this->slide_id = $theSlide;
			}
		}

		// Slideshow has not been assigned for this page and
		if(empty($this->gallery_id)) {
			$theGallery = 0;

			// Loop through $this->page array to find nearest parent with slideshow assigned
			for($i=$this->curLevel-1;$i>-1;$i--) {
				if($this->page[$i]["gallery"] != 0 && $theGallery == 0) {
					$theGallery = $this->page[$i]["gallery"];
				}
			}

			// If slideshow is still not found - try to get "main"
            // Fix: only get main if default enabled
			if($theSlide == 0 && $this->slid_sm == true) {
				$this->executeQuery("SELECT * FROM cms_gallery WHERE main='1' AND category='0' AND gallery_id='0' ORDER BY position ASC LIMIT 0,1",3);
				$row3 = mysqli_fetch_assoc($this->result3);
				$this->gallery_id = $row3["id"];
			}
			// Otherwise get parent's slideshow
			else {
				$this->gallery_id = $theGallery;
			}
		}

		// Connection with module found
		if($row["connection"] > 0 && $row["connection_type"] == "module") {
			$this->page[$row["level"]]["iname"] = $row["intName"];

			// Set elementTitle variable in order to recognize whether it's main module page, or one element (news, offer, gallery)
			$this->elementTitle = !empty($_GET["title"]) ? $_GET["title"] : '';

			// Set page title
			$this->pageTitle = $row["slogan"] != "" ? $row["slogan"] : $row["extName"];

            /* Add


		// Get META Tags
		$this->getMeta($row);

        If you need to overwrite meta tags
        */

			// Execute right method depending on what connection the page has
			switch($row["shortName"]) {
                case "articles":
                    $this->getArticles();
                    $this->object_type = "article";
                break;
				case "news":
					$this->getNews();
					$this->object_type = "news";
				break;
				case "gallery":
					// Assign gallery page content to page content if it's not blank
					$c = strip_tags($row["content"]);
					$this->pageContent = empty($c) ? '' : $this->replaceKcFinderPaths($row["content"]).'<br /><br />';
					$this->getGallery(400,300,1000,700);
					$this->object_type = "gallery";
				break;
				case "offers":
					//$this->getOffers(300, 200);
					$this->object_type = "offer";
				break;
				case "products":
					$this->object_type = "product";
					//$this->getProducts();
				break;
				case "blog":
					$this->object_type = "blog";
					$this->getBlog(125,125,250,250,900,600);
				break;
				case "files":
					$this->pageContent .= '<h1 class="colour-red size-30 font-abold">Do pobrania</h1><br /><Br />';
					$this->executeQuery("SELECT * FROM cms_files_cats WHERE lang='$this->lang' AND status='1' ORDER BY position ASC", 3);
					while($r3 = mysqli_fetch_assoc($this->result3)) {
						$this->pageContent .= '<div class="file-category"><div class="file-category-title size-18 font-abold colour-red">'.$r3["name"].'</div>';

						$this->executeQuery("SELECT * FROM cms_files WHERE category_id='".$r3["id"]."' ORDER BY position ASC", 4);
						while($r4 = mysqli_fetch_assoc($this->result4)) {
							$this->pageContent .= '<a href="/_files/files/'.$r4["file"].'" target="_blank" class="file-link colour-grey-dark size-15"><span>'.$r4["name"].'</span></a><br />';
						}

						$this->pageContent .= '</div>';
					}
				break;
			}
		}
		// Display normal page (no connection with module)
		else {
			$this->getFPage($row);

		// Get META Tags
		$this->getMeta($row);
		}
	}

/**
 *	getFPage - takes content for normal pages
 *		@param $row (array)	- array of data from database
 */
	private function getFPage($row) {
		$this->pageTitle = empty($row["slogan"]) ? $row["extName"] : $row["slogan"];

            $k = $this->getCount("cms_menu", "WHERE parentId='{$this->page[0]["id"]}' AND status='1'");

            if($k > 0 && $row["content"] == "") {

                // $this->executeQuery("SELECT * FROM cms_menu WHERE parentId='{$this->page[0]["id"]}' AND status='1' AND content<>'' ORDER BY position ASC LIMIT 0,1",3);
                // $r3 = mysqli_fetch_assoc($this->result3);
                // header("Location:".$this->buildLink($r3, "menu"));
            }
		$this->pageContent = '<h1 class="colour-red size-30 font-abold">'.$this->pageTitle.'</h1>';

        if($k > 0) {
            $this->pageContent .= '<div class="right">';
        }
        $this->pageContent .= '<div class="align-justify">'.$this->replaceKcFinderPaths($row["content"]).'&nbsp;</div>';
		if($row["id"] == 27) {
			$lw = 400; $lh = 300; $tw = 1000; $th = 700;
			$this->executeQuery("SELECT * FROM cms_gallery_files WHERE galleryId = '27' ORDER BY position ASC",1);
			while($row = mysqli_fetch_assoc($this->result1)) {
				$this->pageContent .= '<a href="'.($row["link"] != "" ? $row["link"] : '/_images_content/gallery/'.$lw.'x'.$lh.'/'.$row["file"]).'" class="gallery-link'.($row["link"] != "" ? ' no-colorbox' : '').'" title="'.$row["alt"].'" class="gallery-image-link lightbox">
				<span class="display-block gallery-image"><img data-src="/_images_content/gallery/'.$tw.'x'.$th.'/'.$row["file"].'" alt="'.$row["alt"].'" title="'.$row["alt"].'" /></span>
				<span class="display-block align-center colour-red size-18">'.$row["description"].'</span></a>';
			}
		}

        if($k > 0) {
            $this->pageContent .= '</div><div class="left">';

            $this->executeQuery("SELECT * FROM cms_menu WHERE parentId='{$this->page[0]["id"]}' AND status='1' ORDER BY position ASC",2);
            while($r2 = mysqli_fetch_assoc($this->result2)) {
                $this->pageContent .= '<a href="'.$this->buildLink($r2, "menu").'" class="colour-grey-dark page-link'.($this->id == $r2["id"] ? ' active' : '').'">'.$r2["extName"].'</a>';

                if($this->getCount("cms_menu", "WHERE parentId='{$r2["id"]}' AND status='1'") > 0) {

                    $this->executeQuery("SELECT * FROM cms_menu WHERE parentId='{$r2["id"]}' AND status='1' ORDER BY position ASC",3);
                    while($r3 = mysqli_fetch_assoc($this->result3)) {
                        $this->pageContent .= '<a href="'.$this->buildLink($r3, "menu").'" class="lvl2 colour-grey-dark page-link'.($this->id == $r3["id"] ? ' active' : '').'">'.$r3["extName"].'</a>';
                    }
                }
            }

            $this->pageContent .= '</div><div class="c"></div>';
        }


		// Add extra content pre-defined with exception connection
		// Here you can also change object_type if the exception actually defines most of its content
		switch($row["exceptName"]) {
			case "contact_form":
				$this->pageContent .= '<br /><br />';
				if(isset($_POST["submit2"])) {
                    $name = strip_tags($_POST["name"]);
                    $email = strip_tags($_POST["email"]);
                    $msg = strip_tags($_POST["msg"]);
                    $phone = strip_tags($_POST["phone"]);

                    if(empty($name) || empty($email) || empty($msg) || empty($phone)) {
                        $this->pageContent .= '<div class="error">Proszę wypełnić formularz</div>';
                    }
                    else {
                        include_once("class.phpmailer.php");
                        $mail = new PHPMailer();
                        $mail->IsMail();
                        $mail->IsHTML(true);
                        $mail->From = "kontakt@elalider.pl";
                        $mail->FromName = $this->newsletter_from_name;
						$mail->AddAddress($this->newsletter_from_email);
						// $mail->AddAddress("dawid@lemon-art.com");
                        $mail->Subject  = "Lider - formularz kontaktowy";
                        $body  = '
                            <table cellpadding="5" cellspacing="0" style="font-family:Arial;font-size:15px;color:#585858;background:#f6f5f5;" width="500">
                                <tr><td style="font-weight:bold;letter-spacing:1px;font-size:15px;text-align:center;height:50px;background:#eeeeee;" valign="middle" width="500" height="30" colspan="2">Wiadomość ze strony Lider</td></tr>
                                <tr><td height="10" style="height:10px;"></td></tr>
                                <tr><td style="font-weight:bold;font-size:13px;text-indent:10px;color:#ac1204;" height="20">Imię i nazwisko:</td></tr><tr><td style="font-style:italic;font-size:11px;text-indent:20px;">'.$name.'</td></tr>
                                <tr><td height="10" style="height:10px;"></td></tr>
                                <tr><td style="font-weight:bold;font-size:13px;text-indent:10px;color:#ac1204;" height="20">Kontakt:</td></tr><tr><td style="font-style:italic;font-size:11px;text-indent:20px;">'.$email.', '.$phone.'</td></tr>
                                <tr><td height="10" style="height:10px;"></td></tr>
                                <tr><td style="font-weight:bold;font-size:13px;text-indent:10px;color:#ac1204;" height="20">Treść:</td></tr><tr><td style="font-style:italic;font-size:11px;text-indent:20px;">'.$msg.'</td></tr>
                                <tr><td height="20" style="height:20px;"></td></tr>
                            </table>';

                        $mail->Body = $body;

                        if($mail->Send() == true) {
                            $this->pageContent .= '<div class="ok">Wiadomość została wysłana pomyślnie</div>';
                            $name = $phone = $email = $msg = $call = "";
                        }
                        else {
                            $this->pageContent .= '<div class="error">Wystąpił błąd podczas wysyłania wiadomości, prosimy spróbować ponownie</div>';
                        }
                    }
                }
                $this->pageContent .= '
						<form method="POST" action="" enctype="multipart/form-data" id="contact-form">
							<div class="size-12 colour-red">* pola wymagane </div>   <Br />
                            <input type="text" placeholder="Imię i nazwisko *" value="'.$name.'" name="name" required />
                            <input type="email" placeholder="E-mail *" value="'.$email.'" name="email" required />
                            <input type="text" placeholder="Numer telefonu *" value="'.$phone.'" name="phone" required />
                            <textarea name="msg" placeholder="Treść zapytania *">'.$msg.'</textarea>
                            <input type="submit" name="submit2" value="Wyślij" />
                        </form>';
			break;
		}

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
			$link = $this->activeLangs > 1 && $this->lang != $this->default_lang ? '/'.$this->lang : '';

			// Are urls to be shown in full?
			if($this->urls == "full-noid") {
				$lvl = $row["level"];
				$tree = array();
				$tree[$lvl]["in"] = $row["intName"];
				$tree[$lvl]["id"] = $row["id"];
				$pId = $row["parentId"];

				// Loop through the pages to get all hierarchy
				for($i=$lvl-1;$i>=0;$i--) {
					$this->executeQuery("SELECT * FROM cms_menu WHERE id='$pId'",11);
					$row11 = mysqli_fetch_assoc($this->result11);
					$pId = $row11["parentId"];
                    if($row11["url_use"] == 1) {
					   $tree[$i]["in"] = $row11["intName"];
					   $tree[$i]["id"] = $row11["id"];
                    }
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


/**
 *	getLemonPage - takes content of page with type 5, which can only be altered by Lemon-Art
 *		@param $id (integer) - ID of the desired page
 */
	public function getLemonPage($id){
		$this->executeQuery("SELECT content FROM cms_menu WHERE id='$id' AND type='5' AND status='1'",114);
		$c = mysqli_fetch_row($this->result114);
		return $c[0];
	}


/**
 *	getMeta - sets meta tags
 *		@param $r (array) - database row
 */
	public function getMeta($r) {
		$this->metaTitle = $r["metaTitle"] != "" ? $r["metaTitle"] : $this->metaTitle;
		$this->metaKeys = $r["metaKeys"] != "" ? $r["metaKeys"] : $this->metaKeys;
		$this->metaDesc = $r["metaDesc"] != "" ? $r["metaDesc"] : $this->metaDesc;
	}


/**
 *	getOffers - retrieves news information
 */
	private function getNews() {
		// Get one particular piece of news<div class="right">';

        $this->pageContent = '<div class="right">';

		if(empty($this->elementTitle)) {
            $this->executeQuery("SELECT * FROM cms_news WHERE status='1' ORDER BY date DESC LIMIT 0,1",1);
            $r = mysqli_fetch_assoc($this->result1);
            header("Location:".$this->buildLink($r, "news"));
        }

        $this->executeQuery("SELECT * FROM cms_news WHERE id='$this->id'",1);
        $row = mysqli_fetch_assoc($this->result1);
        $this->getMeta($row);
        $this->pageTitle = $row["title"];
        $this->pageContent .= '<div>
            <h1 class="size-30 font-abold colour-red">'.$this->pageTitle.'</h1>
            <div class="align-justify">
                '.$this->replaceKcFinderPaths($row["content"]);

                if($this->getCount("cms_news_files", "WHERE news_id='$this->id'") > 0) {
                    $this->pageContent .= '<div id="gallery">';

                    $this->executeQuery("SELECT * FROM cms_news_files WHERE news_id='$this->id' ORDER BY position ASC",4);
                    while($r4 = mysqli_fetch_assoc($this->result4)) {
                        $this->pageContent .= '<a href="'.($r4["link"] != "" ? $r4["link"] : '/_images_content/news/1000x700/'.$r4["file"]).'" class="gallery-link'.($r4["link"] != "" ? ' no-colorbox' : '').'" title="'.$r4["alt"].'" class="gallery-image-link lightbox">
						<span class="display-block gallery-image"><img data-src="/_images_content/news/400x300/'.$r4["file"].'" alt="'.$r4["alt"].'" /></span>
						<span class="display-block align-center colour-red size-18">'.$r4["description"].'</span></a>';
                    }

                    $this->pageContent .= '</div>';
                }
        $this->pageContent .= '
            </div>
        </div>';

        $this->pageContent .= '</div><div class="left">';

			$this->executeQuery("SELECT * FROM cms_news WHERE lang='$this->lang' AND status='1' ORDER BY date DESC",1);
			while($row = mysqli_fetch_assoc($this->result1)) {
				$this->pageContent .= '<a href="'.$this->buildLink($row, "news").'" class="news-link colour-grey-dark'.($this->id == $row["id"] ? ' active' : '').'"><span class="news-link-date colour-red font-abold">'.$this->convertDate($row["date"], false, $this->lang).'</span><span class="news-link-title">'.$row["title"].'</span></a>';
			}

        $this->pageContent .= '</div><div class="c"></div>';
	}

	private function getArticles() {
		// Get one particular piece of news<div class="right">';

        $this->pageContent = '<div class="right">';

		if(empty($this->elementTitle)) {
            $this->executeQuery("SELECT * FROM cms_articles WHERE status='1' ORDER BY date DESC LIMIT 0,1",1);
            $r = mysqli_fetch_assoc($this->result1);
            header("Location:".$this->buildLink($r, "articles"));
        }

        $this->executeQuery("SELECT * FROM cms_articles WHERE id='$this->id'",1);
        $row = mysqli_fetch_assoc($this->result1);
        $this->getMeta($row);
        $this->pageTitle = $row["title"];
        $this->pageContent .= '<div>
            <h1 class="size-30 font-abold colour-red">'.$this->pageTitle.'</h1>
            <div class="align-justify">
                '.$this->replaceKcFinderPaths($row["content"]);

                if($this->getCount("cms_articles_files", "WHERE news_id='$this->id'") > 0) {
                    $this->pageContent .= '<div id="gallery">';

                    $this->executeQuery("SELECT * FROM cms_articles_files WHERE news_id='$this->id' ORDER BY position ASC",4);
                    while($r4 = mysqli_fetch_assoc($this->result4)) {
                        $this->pageContent .= '<a href="'.($r4["link"] != "" ? $r4["link"] : '/_images_content/articles/1000x700/'.$r4["file"]).'" class="gallery-link'.($r4["link"] != "" ? ' no-colorbox' : '').'" title="'.$r4["alt"].'" class="gallery-image-link lightbox">
						<span class="display-block gallery-image"><img data-src="/_images_content/articles/400x300/'.$r4["file"].'" alt="'.$r4["alt"].'" /></span>
						<span class="display-block align-center colour-red size-18">'.$r4["description"].'</span></a>';
                    }

                    $this->pageContent .= '</div>';
                }
        $this->pageContent .= '
            </div>
        </div>';

        $this->pageContent .= '</div><div class="left">';

			$this->executeQuery("SELECT * FROM cms_articles WHERE lang='$this->lang' AND status='1' ORDER BY date DESC",1);
			while($row = mysqli_fetch_assoc($this->result1)) {
				$this->pageContent .= '<a href="'.$this->buildLink($row, "articles").'" class="news-link colour-grey-dark'.($this->id == $row["id"] ? ' active' : '').'"><span class="news-link-date colour-red font-abold">'.$this->convertDate($row["date"], false, $this->lang).'</span> - '.$row["title"].'</a>';
			}

        $this->pageContent .= '</div><div class="c"></div>';
	}

/**
 *	getGallery - retrieves gallery information
 *		@param $tw  (integer)	- small image width
 *		@param $th  (integer) 	- small image height
 *		@param $lw  (integer)	- large image width
 *		@parem $lh  (integer) 	- large image height
 */
	private function getGallery($tw, $th, $lw, $lh) {
		// ElementTitle is set - meaning it's either link to category or particular gallery
		if(!empty($this->elementTitle)) {
			$this->executeQuery("SELECT * FROM cms_gallery WHERE id='$this->id'",1);
			$row = mysqli_fetch_assoc($this->result1);
			$this->getMeta($row);
			$intName = $row["intName"];
			$this->pageTitle = $row["extName"];
			$this->pageContent = empty($row["content"]) ? '' : $this->replaceKcFinderPaths($row["content"]).'<br />';

			// This is a single gallery - display all images
			if($row["category"] == 0) {
				$this->executeQuery("SELECT * FROM cms_gallery_files WHERE galleryId = '$this->id' ORDER BY position ASC",1);
				while($row = mysqli_fetch_assoc($this->result1)) {
					$this->pageContent .= '<a href="'.($row["link"] != "" ? $row["link"] : '/_images_content/gallery/'.$lw.'x'.$lh.'/'.$row["file"]).'" class="gallery-link'.($row["link"] != "" ? ' no-colorbox' : '').'" title="'.$row["alt"].'" class="gallery-image-link lightbox">
					<span class="display-block gallery-image"><img data-src="/_images_content/gallery/'.$tw.'x'.$th.'/'.$row["file"].'" alt="'.$row["alt"].'" /></span>
					<span class="display-block align-center colour-red size-18">'.$row["description"].'</span></a>';
				}
			}
			// This is a category - show all galleries inside with at least one image
			else {
				$this->executeQuery("SELECT *, cg.id AS id FROM cms_gallery cg INNER JOIN cms_gallery_files cgf ON cg.id = cgf.galleryId WHERE cg.status='1' AND gallery_id='$this->id' AND cg.lang='$this->lang' AND cgf.position = (SELECT MIN(position) FROM cms_gallery_files WHERE galleryId = cg.id) GROUP BY cg.id ORDER BY cg.position ASC",1);
				while($row = mysqli_fetch_assoc($this->result1)) {
					$this->pageContent .= '
						<a href="'.$this->buildLink($row, "gallery").'" class="gallery-image-link">
							<div>'.$this->centerImage("_images_content/gallery/".$tw."x".$th."/".$row["file"], $tw, $th, $row["alt"]).'</div>'.$row["extName"].'
						</a>
					';
				}
			}
			$this->pageContent .= '<div class="c"></div>';
		}
		else {
			// Show all galleries
			if($this->gallery_cats == true) {
				// Show categories (with minimum one gallery inside) and galleries with no categories (with at least one image)
				$this->executeQuery("SELECT
										cg.id AS id, cg.extName, cg.intName, '1' AS category, '' AS file, cg.position, '' AS alt
									FROM cms_gallery cg
										INNER JOIN cms_gallery cg2 ON cg.id = cg2.gallery_id
									WHERE
										cg.status = '1' AND cg.lang = 'pl' AND cg.category = '1'
									GROUP BY cg.id
									UNION ALL
									SELECT
										cg.id AS id, cg.extName, cg.intName, '0' AS category, cgf.file AS file, cg.position, cgf.alt AS 'alt'
									FROM cms_gallery cg
										INNER JOIN cms_gallery_files cgf ON cg.id = cgf.galleryId
									WHERE
										cg.status = '1' AND cg.lang = 'pl' AND cg.category = '0' AND cg.gallery_id = '0'
									GROUP BY cg.id
									ORDER BY position ASC",1);
				while($row = mysqli_fetch_assoc($this->result1)) {
					// Show category
					if($row["category"] == 1) {
						$this->pageContent .= '
							<a href="'.$this->buildLink($row, "gallery").'" class="gallery-category-link">
								'.$row["extName"].'
							</a>
						';
					}
					// Show gallery
					else {
					$this->pageContent .= '
						<a href="'.$this->buildLink($row, "gallery").'" class="gallery-image-link">
							<div>'.$this->centerImage("_images_content/gallery/".$tw."x".$th."/".$row["file"], $tw, $th, $row["alt"]).'</div>'.$row["extName"].'
						</a>
					';
					}
				}
			}
			else {
				// // Multiple galleries but without categories
				// if($this->gal_mg == true) {
				// 	// Show all galleries
				// 	$this->executeQuery("SELECT *, cg.id AS id FROM cms_gallery cg INNER JOIN cms_gallery_files cgf ON cg.id = cgf.galleryId WHERE cg.status='1' AND cg.lang='$this->lang' AND cgf.position = (SELECT MIN(position) FROM cms_gallery_files WHERE galleryId = cg.id) GROUP BY cg.id ORDER BY cg.position ASC",1);
				// 	while($row = mysqli_fetch_assoc($this->result1)) {
				// 		$this->pageContent .= '
				// 			<a href="'.$this->buildLink($row, "gallery").'" class="gallery-image-link">
				// 				'.$this->centerImage("_images_content/gallery/".$tw."x".$th."/".$row["file"], $tw, $th, $row["alt"]).'
				// 			</a>
				// 		';
				// 	}
				// }
				// // One gallery only - show all images
				// else {
					$this->executeQuery("SELECT * FROM cms_gallery_files WHERE galleryId='2' ORDER BY position ASC",1);
					while($row = mysqli_fetch_assoc($this->result1)) {
						$this->pageContent .= '<a href="'.($row["link"] != "" ? $row["link"] : '/_images_content/gallery/'.$lw.'x'.$lh.'/'.$row["file"]).'" class="gallery-link'.($row["link"] != "" ? ' no-colorbox' : '').'" title="'.$row["alt"].'" class="gallery-image-link lightbox">
                            <span class="display-block gallery-image"><img data-src="/_images_content/gallery/'.$tw.'x'.$th.'/'.$row["file"].'" alt="'.$row["alt"].'" /></span>
                            <span class="display-block align-center colour-red size-18">'.$row["description"].'</span></a>';
				 	}
				// }
			}
		}
	}


/**
 *	getOffers - retrieves offer information
 */
	private function getOffers($w, $h) {
		// Get one particular offer
		if(!empty($this->elementTitle)) {
			$this->executeQuery("SELECT * FROM cms_offers WHERE id='$this->id'",1);
			$row = mysqli_fetch_assoc($this->result1);
			$this->getMeta($row);
			$this->pageTitle = $row["extName"];
			$this->pageContent = '';
			if($row["file"] != ""){
				$this->pageContent .= '<div class="offer-image">'.$this->centerImage("_images_content/offers/".$w."x".$h."/".$row["file"], $w, $h).'</div>';
			}
			$this->pageContent .= $this->replaceKcFinderPaths($row["content"]);
		}
		// Get all offers
		else {
			$this->executeQuery("SELECT * FROM cms_offers WHERE lang='$this->lang' AND status='1' ORDER BY position ASC",1);
			while($row = mysqli_fetch_assoc($this->result1)) {
				$this->pageContent .= '<a href="'.$this->buildLink($row, "offers").'" class="offer-link">'.$row["extName"].'</a>';
			}
		}
	}

/**
 *	getCaptcha - retrieves captcha style for forms
 */
 	public function getCaptcha() {
		/* Simple addition/substraction */

		if($this->blog_cs == "math") {
			$f = rand(1,10);
			$t = rand(1,10);
			$d1 = array("+","-");
			$d = $d1[rand(0,1)];
			return '<div id="captcha">'.$this->translate(467).' <input type="hidden" id="captcha-math-vars" value="'.$f.'|'.$d.'|'.$t.'" /><span id="captcha-math-q">'.$f.' '.$d.'	 '.$t.'</span><input type="text" name="captcha-answer" id="captcha-math-answer" maxlength="3" /></div>';
		}
		/* Captcha Secure Image */
		else {
			return '<div id="captcha" class="image"><div id="captcha-image-text">'.$this->translate(469).' <input type="text" id="captcha-code" /></div><img id="captcha-image" src="/_scripts/captcha/securimage_show.php" alt="CAPTCHA Image" /></div>';
		}
	}

/**
 *	checkCaptcha - outputs the result of captcha
 */
	public function checkCaptcha($v, $a) {
		if($this->blog_cs == "math") {
			list($f, $d, $t) = explode("|", $v);
			$r = $d == "+" ? ($f + $t) : ($f - $t);
			return ($r == $a) ? true : false;
		}
		else {
			include_once "_scripts/captcha/securimage.php";
			$securimage = new Securimage();
			return $securimage->check($v);
		}
	}

/**
 *	getScritps - retrieves scripts and css styles for current page
 */
	public $scripts = '';
	public $scripts_css = '';
	private function getScripts() {
		$this->executeQuery("SELECT * FROM cms_scripts WHERE status='1' ORDER BY id ASC, type ASC",1);
		while($row = mysqli_fetch_assoc($this->result1)) {
			if($row["type"] == "CSS") {
				$this->scripts_css .= '<link rel="stylesheet" type="text/css" href="'.$row["file"].'" />
				';
			}
			elseif($row["type"] == "JS") {
				$this->scripts .= '<script type="text/javascript" src="'.$row["file"].'"'.($row["async"] == 1 ? ' async' : '').'></script>
				';
			}
		}
	}


/*
 *	getSlide - retrieves slideshow files for current page
 *		@param $id (integer) - slideshow id
 *		@param $w  (integer) - width of the slideshow container
 *		@param $h  (integer) - height of the slideshow container
 *		@param $res(bool) 	 - is the page responsive? if true, do not set width/height or alignment
*/
	public function getSlide($id, $w, $h, $res = false) {
		$r = '';
		$s_id = $this->menu_ps == true ? $id : 0;
		$this->executeQuery("SELECT * FROM cms_slideshow_files WHERE slideId='$s_id' ORDER BY position ASC",1);
		while($row = mysqli_fetch_assoc($this->result1)) {

			$start = $row["link"] != "" ? '<a href="'.$row["link"].'" target="_blank" class="slide type-link">' : '<div class="slide type-alt">';
			$image = $res == false ? $this->centerImage('_images_content/slideshow/'.$w.'x'.$h.'/'.$row["file"], $w, $h, $row["alt"]) : '<img src="/_images_content/slideshow/'.$w.'x'.$h.'/'.$row["file"].'" alt="'.$row["alt"].'" width="" height="" title="'.$row["alt"].'" />';
			$desc = $row["description"] != "" ? '<div class="slide-text">'.$row["description"].'</div>' : '';
			$end = $row["link"] != "" ? '</a>' : '</div>';

			$r .= $start.$image.$desc.$end;
		}
		return $this->compressHTML($r);
	}


/**
 *	getPromoBox - retrieves PromoBox data for current page
 */
	public $promoFit = 0;
	public $promoOrgWidth = 0;
	public $promoOrgHeight = 0;
	public $promoboxData;
	private function getPromoBox() {
		$this->promoboxData = '';
		$this->executeQuery("
			SELECT
				cp.*
			FROM cms_menu cm
				INNER JOIN cms_promobox cp ON cm.id = cp.pageId
			WHERE
				cm.id='".$this->page[$this->curLevel]["id"]."'
				AND cm.lang='$this->lang'
				AND cp.status='1'
				AND (IFNULL(DATE(cp.date_start), '1900-01-01') <= DATE(NOW()))
				AND (IFNULL(DATE(cp.date_end), '2099-12-12') >= DATE(NOW()))
		",1);
		$row = mysqli_fetch_assoc($this->result1);
		$id = $row["id"];
		if($id != "" ) {
			// Read the setting whether the image should fit the window or be at 100% scale of uploaded file
			$this->promoFit = $row["fitWindow"];

			// Set the link or anchor accordingly
			$link = empty($row["link"]) ? "#" : $row["link"];

			// Image is found
			if($row["file"] != "") {
				list($this->promoOrgWidth,$this->promoOrgHeight) = getimagesize("_images_content/promobox/".$row["file"]);
				$pd = '<div id="promobox">
						<div id="promobox-bg" style="background-color:#'.$row["bgColor"].'"></div>
						<div id="promobox-inside" style="padding:20px;background-color:#'.$row["borderColor"].';">
							<a href="'.$link.'" title="'.$row["name"].'">'.($row["textPosition"]=="top"?'<div id="promobox-text">'.$row["content"].'</div>':'').'
								<img src="/_images_content/promobox/'.$row["file"].'" alt="'.$row["name"].'" id="promobox-image" height="'.$this->promoOrgHeight.'" width="'.$this->promoOrgWidth.'"/>
								'.($row["textPosition"]=="bottom"?'<div id="promobox-text">'.$row["content"].'</div>':'').'
							</a>
							<a href="#" id="promobox-close" style="background-color:#'.$row["borderColor"].';color:#'.$row["closeColor"].'">x</a>
						</div>
					</div>';

			}
			// Only text promobox
			else {
				$this->promoOrgWidth = 600;
				$this->promoOrgHeight = 0;
				$pd = '<div id="promobox">
						<div id="promobox-bg" style="background-color:#'.$row["bgColor"].'"></div>
						<div id="promobox-inside" style="padding:20px;background-color:#'.$row["borderColor"].';">
							<a href="'.$link.'" title="'.$row["name"].'">'.($row["textPosition"]=="top"?'<div id="promobox-text">'.$row["content"].'</div>':'').'</a>
							<a href="#" id="promobox-close" style="background-color:#'.$row["borderColor"].';color:#'.$row["closeColor"].'">x</a>
						</div>
					</div>';
			}
			$this->promoboxData = $this->compressHTML($pd);
		}
	}

/**
 *	translate - override cmscontrol function to add language
 */
	public function translate($n) {
		return htmlspecialchars($this->t[$this->lang][$n]);
	}

    public static function obfuscate_string($e):string {
        $t = '';
        foreach(str_split($e, 1) as $c) {
            $t .= '&#' . ord($c) . ';';
        }
        return $t;
    }
}
?>