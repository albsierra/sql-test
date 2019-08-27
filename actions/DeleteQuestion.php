<?php
require_once "../../config.php";
require_once "../dao/SQL_DAO.php";

use \Tsugi\Core\LTIX;
use \SQL\DAO\SQL_DAO;

// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;

$SQL_DAO = new SQL_DAO($PDOX, $p);

$question_id = isset($_GET["question_id"]) ? $_GET["question_id"] : false;

if ( $USER->instructor && $question_id ) {

    $SQL_DAO->deleteQuestion($question_id);

    $SQL_DAO->fixUpQuestionNumbers($_SESSION["sql_id"]);

    header( 'Location: '.addSession('../instructor-home.php') ) ;
} 
