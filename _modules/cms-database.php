<?php
	$cms->checkAccess();  
	switch($cms->a) { 
		case "list":
            if($cms->dbBackup() == false) {
                $cms->setSessionInfo(false, $cms->translate(309));
            }
            else {
                $cms->setSessionInfo(true, $cms->translate(310));
            }
            header("Location:".$cms->get_link("start"));
        break;
    }
?>