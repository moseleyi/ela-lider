<?php
    include_once("class.cmscontrol.php");
    
    $cms = new cmsControl(); 
    $cms->showErrors(true);
    
    $_SESSION["userLogin"] = "CRON";
    $_SESSION["userRank"] = "";
    
    $result = (int)$cms->dbBackup($cms->cron_path);
    
    $cms->executeQuery("INSERT INTO system_cron_jobs (`id`, `job_name`, `executed`, `result`) VALUES ('', 'database_backup', UTC_TIMESTAMP(), '$result')",1);
?>