<?php
require_once('../config.php');
require_once('dao/SQL_DAO.php');

use \Tsugi\Core\LTIX;
use \SQL\DAO\SQL_DAO;

// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;

$SQL_DAO = new SQL_DAO($PDOX, $p);

if ( $USER->instructor ) {

    echo('Import questions coming soon. <a href="instructor-home.php">Back</a>');

} else { // student

    header( 'Location: '.addSession('student-home.php') ) ;
}
