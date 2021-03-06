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
    $questionDatabase = $_POST["questionDatabase"];
    $questionType = $_POST["questionType"];
    $questionText = $_POST["questionText"];
    $questionSolution = $_POST["questionSolution"];
    $questionProbe = $_POST["questionProbe"];

    $currentTime = new DateTime('now', new DateTimeZone($CFG->timezone));
    $currentTime = $currentTime->format("Y-m-d H:i:s");

	if ($questionId > -1) {
	    // Existing question
	    $SQL_DAO->updateQuestion($questionId, $questionDatabase, $questionType, $questionText, $questionSolution, $questionProbe, $currentTime);
    } else {
	    // New question
        $SQL_DAO->createQuestion($_SESSION["sql_id"], $questionDatabase, $questionType, $questionText, $questionSolution, $questionProbe, $currentTime);
    }

    header( 'Location: '.addSession('../instructor-home.php') ) ;
}
