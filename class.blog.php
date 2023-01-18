<?php
/* class.blog.php
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
	001.	getBlog()
	002.	showComments() 
	003.	createTagCloud()
	004.	createBlogArchive()
	005.	lastComments()
	006.	mostVisited() 
*/

include_once("class.cmsgetcontent.php");
class Blog extends cmsGetContent {
	
/**
 *	getBlog - retrieves blog information
 *		@param @w_s	(integer)	-	width of small image
 *		@param @h_s	(integer)	-	height os small image
 *		@param @w	(integer)	-	width of first image
 *		@param @h	(integer)	-	height of first image
 *		@param @w_l (integer)	-	width of large image
 *		@param @h_l (integer)	-	height of large image
 */
 
	public function getBlog($w_s, $h_s, $w, $h, $w_l, $h_l) {  
		// Get one particular post
		if(!empty($this->elementTitle)) {   
			$this->executeQuery("UPDATE cms_blog SET visits_count=visits_count+1 WHERE id='$this->id'",10);
			$this->executeQuery("SELECT 
									cb.*,
									cbf.file,
									cbf.link,
									cbf.alt,
									cbf.id AS 'main-image-id'
								FROM cms_blog cb
									LEFT JOIN cms_blog_files cbf ON cb.id = cbf.post_id 
										AND (
											cbf.main = (CASE WHEN (SELECT COUNT(*) FROM cms_blog_files WHERE post_id = cb.id AND main = 1) > 0 THEN 1 ELSE cbf.main END) 
											AND position = (CASE WHEN (SELECT COUNT(*) FROM cms_blog_files WHERE post_id = cb.id AND main = 1) > 0 
																THEN position ELSE (SELECT MIN(position) FROM cms_blog_files WHERE post_id = cb.id) END)
										)
								WHERE cb.id='$this->id'",1);
			$row = mysqli_fetch_assoc($this->result1); 
			$this->getMeta($row); 	
			$this->pageTitle = $row["title"];
			$this->pageContent = ($row["file"] != "" && $this->blog_galleries == true ? '<a href="'.($row["link"] != "" ? $row["link"] : '/_images_content/blog/'.$w_l.'x'.$h_l.'/'.$row["file"]).'" id="post-image">'.$this->centerImage("_images_content/blog/".$w."x".$h."/".$row["file"], $w, $h, $row["alt"]).'</a>' : '').$row["content"].'<br />';
									
			/* Show gallery only if enabled */
			if($this->blog_galleries == true) {
				$this->executeQuery("SELECT * FROM cms_blog_files WHERE post_id='$this->id' AND id!='".$row["main-image-id"]."' ORDER BY position ASC",2);
				while($row2 = mysqli_fetch_assoc($this->result2)) {
					$link = $row2["link"] != "" ? $row2["link"] : '/_images_content/blog/'.$w_l.'x'.$h_l.'/'.$row2["file"];
					$this->pageContent .= '<a href="'.$link.'" class="post-image-small lightbox">'.$this->centerImage("_images_content/blog/".$w_s."x".$h_s."/".$row2["file"], $w_s, $h_s, $row2["alt"]).'</a>';
				}
				$this->pageContent .= '<div class="c"></div>';		
			}
												
									
			$this->comments_enabled = $row["comments_enabled"]; 
			/* Comments are enabled */
			if($this->blog_ca == true) {
				
				/* Start comments links div */
				$this->pageContent .= '<div id="post-comments-top">';
				
				/* Check if we should show all comments right away or just the link to toggle the comments */
				if($this->blog_col == false) {
					
					$this->pageContent .= '<div id="post-show-comments">'.$this->translate(456).' ('.$this->getCount("cms_blog_comments","WHERE post_id='$this->id' AND status='1'").')</div>';
				}
				
				/* Show Add Comment link only if comments enabled */
				if($row["comments_enabled"] == 1 && $this->blog_acd == false) {
					$this->pageContent .= '<div id="post-add-comment">'.$this->translate(457).'</div>';	
				}
					
				/* End comments links div */
				$this->pageContent .= '</div>
									   <div id="post-comments-bottom"'./* Add class to hide the comments if they are not on load */($this->blog_col == false ? ' class="hide"' : '').'>';	 
				
				/* Check the method of comments display */		
				$this->comment_level = 0;	
				$this->showComments(0);
				
				$this->pageContent .= '</div>';
				 
				/* Add Comment form only if comments enabled */
				if($row["comments_enabled"] == 1) {
					$this->pageContent .= '<div id="add-comment-form-wrap">
											<div id="add-comment-form" class="'.($this->blog_acd == false ? 'hide' : 'always-show').'">
												<div id="add-comment-overlay-wrap">
													<div id="add-comment-overlay">&nbsp;</div>
													<div id="add-comment-info"></div>
												</div>
												<div id="add-comment-form-in">
													'.($this->blog_acd == false ? '<div id="add-comment-close">x</div>' : '').'
													<div id="add-comment-title">'.$this->translate(457).'</div>
													<input type="text" id="add-comment-name" value="'.$this->translate(413).'" />
													<input type="text" id="add-comment-email" value="'.$this->translate(458).'" />
													<textarea id="add-comment-text">'.$this->translate(459).'</textarea>
													'.$this->getCaptcha().'
													<div id="add-comment-button"'.($this->blog_cs != "math" ? ' class="captcha-image"' : '').'>'.$this->translate(457).'</div><div class="c"></div>
													<input type="hidden" id="post-id" value="'.$this->id.'" />
													<input type="hidden" id="comment-id" value="" />
													<input type="hidden" id="comment-level" value="" />
                                                    <div class="c"></div>
												</div>
										   </div>
										  </div>';
				}
			}
		}
		// Get all posts 
		else { 
			/* Reset page title, we don't want to show "blog" or anything else, just blog posts straight away */
			$this->pageTitle = "";
			
			/* Grab URL variables for different search, archive, tags etc. */
			$year = (int)$_GET["y"];
			$month = (int)$_GET["m"];
			$tag = (string)urldecode($_GET["tag"]); 
			
			$this->executeQuery("SELECT DISTINCT
									cbf.main,
									cbf.file,
									cb.id,
									cb.date,
									cb.comments_enabled,
									cb.intName,
									cb.title,
									cb.content,
									cbf.alt,
									(SELECT COUNT(id) FROM cms_blog_comments WHERE post_id = cb.id AND status='1') AS 'comments_count'
								FROM cms_blog cb 
									LEFT JOIN cms_blog_files cbf ON cb.id = cbf.post_id 
										AND (
											cbf.main = (CASE WHEN (SELECT COUNT(*) FROM cms_blog_files WHERE post_id = cb.id AND main = 1) > 0 THEN 1 ELSE cbf.main END) 
											AND position = (CASE WHEN (SELECT COUNT(*) FROM cms_blog_files WHERE post_id = cb.id AND main = 1) > 0 THEN position ELSE (SELECT MIN(position) FROM cms_blog_files WHERE post_id = cb.id) END)
										)
									LEFT JOIN cms_blog_tags cbt ON cb.id = cbt.post_id
								WHERE 
									cb.lang='$this->lang' 
									AND cb.status='1' 
									".($year > 0 ? " AND YEAR(date)='$year'" : "")."
									".($month > 0 ? " AND MONTH(date)='$month'" : "")."
									".($tag != "" ? " AND cbt.tag_name='$tag'" : "")."
								ORDER BY date DESC",1);
			while($row = mysqli_fetch_assoc($this->result1)) {
				$id = $row["id"];
				
				/* Retrieve tags for current post */
				$tags = array();
				$this->executeQuery("SELECT * FROM cms_blog_tags WHERE post_id='$id' ORDER BY tag_name ASC",2);
				while($row2 = mysqli_fetch_assoc($this->result2)) {
					$tags[] = $row2["tag_name"];
				}
				
				/* Chop content to 1000 characters */
				$content = $this->trimString($row["content"], 1000);
				
				$this->pageContent .= '	<div class="blog-post">
											<h2 class="blog-post-title size-18">'.$row["title"].'</h2>
											<div class="blog-post-date">'.$this->convertDate($row["date"],false,$this->lang).'</div>
											<div class="blog-post-text">
												'.($row["file"] != "" ? '<a href="/_images_content/blog/'.$w_l.'x'.$h_l.'/'.$row["file"].'" class="blog-post-image">'.$this->centerImage("_images_content/blog/".$w."x".$h."/".$row["file"], $w, $h, $row["alt"]).'</a>' : '').'
												'.$content.'...
											</div>
											<div class="c"></div>';
											/* Show tags wrapper only if tags are there */
											if(count($tags) > 0) {
												$this->pageContent .= '
											<div class="blog-post-tags-wrap">
												<div class="blog-post-tags-title">'.$this->translate(478).':</div>
												<div class="blog-post-tags">'.implode(", ",$tags).'</div>
												<div class="c"></div>
											</div>';
											}
				$this->pageContent .= '
											<div class="blog-post-links">
												<div class="blog-post-social">
													<div class="fb-like" data-href="'.$this->cw.''.$this->buildLink($row, "blog").'" data-send="false" data-layout="button_count" data-width="450" data-show-faces="false" data-font="lucida grande"></div>
													<div class="fb-send" data-href="'.$this->cw.''.$this->buildLink($row, "blog").'" data-width="51" data-height="20" data-colorscheme="light"></div>
													<a href="https://twitter.com/share" class="twitter-share-button" data-url="'.$this->cw.''.$this->buildLink($row, "blog").'" data-via="'.$this->link_twitter.'">Tweet</a> 
													<div class="g-plusone" data-size="medium" data-href="'.$this->cw.''.$this->buildLink($row, "blog").'"></div>
												</div>
												<a href="'.$this->buildLink($row, "blog").'" class="blog-post-link">'.$this->translate(479).''.($this->blog_ca == true ? ' / '.$this->translate(455).' ('.$row["comments_count"].')' : '').'</a>
												<div class="c"></div>
											</div>
										</div>';
			}
		}
	}
 
 	public function showComments($comment_id) { 
	$i = $this->getCount("cms_blog_comments","WHERE post_id='$this->id' AND status='1' AND comment_id='$comment_id'"); 
	$this->executeQuery("SELECT * FROM cms_blog_comments WHERE post_id='$this->id' AND status='1' AND comment_id='$comment_id' ORDER BY date_added ASC",$comment_id);
	while($row[$comment_id] = mysqli_fetch_assoc($this->{"result".$comment_id})) { 
		if($comment_id == 0) {$this->comment_level = 0;} 
			$this->pageContent .= '<div class="comment comment-level-'.$this->comment_level.'" id="comment-'.$row[$comment_id]["id"].'" style="margin-left:'.($this->comment_level * 10).'px;">
										<div class="comment-person">'.$row[$comment_id]["name"].'</div>
										<div class="comment-date">'.$row[$comment_id]["date_added"].'</div>
										<div class="c"></div>
										<div class="comment-text">'.$row[$comment_id]["content"].'</div>
										'./* Nested comments + comments enabled */($this->blog_nc == true && $this->blog_ca ? '<div class="comment-reply">'.$this->translate(468).'</div>' : '').'
									</div>';
			$i--;
			if($this->blog_nc == true) {
				if($this->getCount("cms_blog_comments","WHERE comment_id='".$row[$comment_id]["id"]."'") > 0) { 
					$this->comment_level++;
					$this->showComments($row[$comment_id]["id"]);
				}
			}
		}
	}
		
/**
 *	createTagClound - creates list of tags with number of occurrences
 */
	public function createTagCloud() {
		$tags = array();
		$r = '';
		$this->executeQuery("SELECT
								tag_name,
								COUNT(*) AS 'count'
							FROM cms_blog_tags cbt
								INNER JOIN cms_blog cb ON cb.id = cbt.post_id AND cb.lang='$this->lang' AND cb.status='1'
							GROUP BY tag_name
							ORDER BY COUNT(*) DESC, tag_name ASC",111);
		while($row = mysqli_fetch_assoc($this->result111)) {
			$r .= '<a href="'.$this->getModuleLink("blog",true).'/tag:'.urlencode($row["tag_name"]).'" class="tag-cloud-link">'.$row["tag_name"].' ('.$row["count"].')</a>';
		}
		return $r;
	}
	
/**
 *	createBlogArchive - creates list of links for each year and month for all blog posts
 */	
	public function createBlogArchive() {
		$months = array(
					"pl"=>array("","Styczeń","Luty","Marzec","Kwiecień","Maj","Czerwiec","Lipiec","Sierpień","Wrzesień","Październik","Listopad","Grudzień"),
					"en"=>array("","January","February","March","April","May","June","July","August","September","October","November","December")
				);
		$r = '<ul id="blog-archive">';
		$this->executeQuery("SELECT DISTINCT(YEAR(date)) AS 'year' FROM cms_blog WHERE lang='$this->lang' AND status='1'ORDER BY YEAR(date) DESC",1);
		while($row = mysqli_fetch_assoc($this->result1)) {
			$year = $row["year"];
			$r .= '<li class="blog-archive-year"><a href="'.$this->getModuleLink("blog",true).'/'.$year.'">'.$year.' ('.$this->getCount("cms_blog","WHERE lang='$this->lang' AND YEAR(date)='$year' AND status='1'").')</a>';
			if($this->getCount("cms_blog","WHERE lang='$this->lang' AND YEAR(date)='$year' AND status='1'") > 0) {
				$r .= '<ul>';
				$this->executeQuery("SELECT DISTINCT(MONTH(date)) AS 'month' FROM cms_blog WHERE lang='$this->lang' AND YEAR(date)='$year' AND status='1' ORDER BY MONTH(date) DESC",2);
				while($row2 = mysqli_fetch_assoc($this->result2)) {
					$month = $row2["month"];
					$r .= '	<li class="blog-archive-month">
								<a href="'.$this->getModuleLink("blog",true).'/'.$year.'-'.$month.'">'.$months[$this->lang][(int)$month].' ('.$this->getCount("cms_blog","WHERE lang='$this->lang' AND YEAR(date)='$year' AND MONTH(date)='$month' AND status='1'").')</a>
							</li>';
				}
				$r .= '</ul>';
			}
			$r .= '</li>';
		}
		$r .= '</ul>';
		return $r;
	}
	
	public function lastComments($i) {
		$r = '';
		$this->executeQuery("SELECT cb.intName,cbc.id AS 'comment_id', cb.id, cbc.content, cbc.date_added AS 'date', cbc.name FROM cms_blog_comments cbc INNER JOIN cms_blog cb ON cb.id = cbc.post_id WHERE cb.lang='$this->lang' AND cbc.status='1' ORDER BY cbc.date_added DESC LIMIT 0,$i",1);
		while($row = mysqli_fetch_assoc($this->result1)) {
			$d = strip_tags($row["content"]);
			$r .= '<a href="'.$this->buildLink($row, "blog").'" class="last-comment-link">
					<span class="last-comment-name">'.$row["name"].'</span>
					<span class="last-comment-date">'.$row["date"].'</span>
					<span class="last-comment-text">'.substr($d, 0, 200).'..</span>
				</a>';
		}
		return $r;
	}
	
	public function mostVisited($i) {
		$r = '';
		$this->executeQuery("SELECT * FROM cms_blog WHERE lang='$this->lang' ORDER BY visits_count DESC LIMIT 0,$i",1);
		while($row = mysqli_fetch_assoc($this->result1)) {
			$r .= '<a href="'.$this->buildLink($row, "blog").'" class="blog-most-visited-link">'.$row["date"].' - '.$row["title"].'</a>';
		}
		return $r;
	}
}