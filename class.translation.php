<?php
/* class.translation.php
.---------------------------------------------------------------------------.
|  Software: LemonCMS - Content Management System                           |
|   Version: 2.7.8                                                          |
|  Released: 14 October 2017                                                |
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
    class Translation {
    
        private static $lang = '';
        
        public static function set_lang($l) {
            self::$lang = $l;
        }
    
        private static $translation = array(
                "pl" => array (
                ),
                "en" => array (
                )
            );
        
       public static function trans($in) {  

            $in = self::to_lower(str_replace("_", " ", $in));  

            foreach(self::$translation["pl"] as $i => $label) {
                if(($in == self::to_lower($label) && !is_numeric($in)) || ($i == $in && is_numeric($in))) {
                    return self::$translation[self::$lang][$i];
                    break;
                } 
            }
        }
        
        private static function to_lower($i) {
            return mb_strtolower($i, "UTF-8");
        }
    }
?>