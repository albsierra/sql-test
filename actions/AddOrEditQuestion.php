<?php
require_once "../../config.php";
require_once('../dao/SQL_DAO.php');

use \Tsugi\Core\LTIX;
use \SQL\DAO\SQL_DAO;

$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;

$SQL_DAO = new SQL_DAO($PDOX, $p);

if ($USER->instructor) {

    $questionId = $_POST["questionId"];
    $questionText = $_POST["questionText"];

    $currentTime = new DateTime('now', new DateTimeZone($CFG->timezone));
    $currentTime = $currentTime->format("Y-m-d H:i:s");

	if ($questionId > -1) {
	    // Existing question
	    $SQL_DAO->updateQuestion($questionId, $questionText, $currentTime);
    } else {
	    // New question
        $SQL_DAO->createQuestion($_SESSION["sql_id"], $questionText, $currentTime);
    }

    header( 'Location: '.addSession('../instructor-home.php') ) ;
}
