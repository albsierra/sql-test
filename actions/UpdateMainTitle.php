<?php
require_once "../../config.php";
require_once('../dao/SQL_DAO.php');

use \Tsugi\Core\LTIX;
use \SQL\DAO\SQL_DAO;

$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;

$SQL_DAO = new SQL_DAO($PDOX, $p);

if ($USER->instructor) {

    if (isset($_POST["toolTitle"])) {
        $currentTime = new DateTime('now', new DateTimeZone($CFG->timezone));
        $currentTime = $currentTime->format("Y-m-d H:i:s");

        $SQL_DAO->updateMainTitle($_SESSION["sql_id"], $_POST["toolTitle"], $currentTime);
    }

    if (!isset($_POST["nonav"])) {
        header( 'Location: '.addSession('../instructor-home.php') ) ;
    }
}
