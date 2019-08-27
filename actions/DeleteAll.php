<?php
require_once "../../config.php";
require_once "../dao/SQL_DAO.php";

use \Tsugi\Core\LTIX;
use \SQL\DAO\SQL_DAO;

// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;

$SQL_DAO = new SQL_DAO($PDOX, $p);

if ( $USER->instructor ) {

    $SQL_DAO->deleteMain($_SESSION["sql_id"], $USER->id);

    header( 'Location: '.addSession('../index.php') ) ;
}
