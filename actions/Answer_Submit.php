<?php
require_once "../../config.php";
require_once('../dao/SQL_DAO.php');
require_once('../dao/PDO_LOCAL.php');

use \Tsugi\Core\LTIX;
use \SQL\DAO\SQL_DAO;
use \SQL\DAO\PDO_LOCAL;

$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;

$SQL_DAO = new SQL_DAO($PDOX, $p);

$currentTime = new DateTime('now', new DateTimeZone($CFG->timezone));
$currentTime = $currentTime->format("Y-m-d H:i:s");
$totalScore = 0.0;

for ($x = 1; $x < ($_POST["Total"]+1); $x++) {
    $answerId = $_POST['AnswerID'.$x];
    $questionId = $_POST['QuestionID'.$x];
    $answerText = $_POST['A'.$x];

    if (strlen($answerText) > 0) {
        $question = $SQL_DAO->getQuestionById($questionId);

        $PDO_LOCAL = new PDO_LOCAL($CFG, $question['question_database']);
        $answerSuccess = $PDO_LOCAL->gradeAnswer($answerText, $question['question_solution'], $question['question_probe']);
        $totalScore += ($answerSuccess ? 1 : 0) ;

        if ($answerId > -1) {
            // Existing answer check if it needs to be updated
            $oldAnswer = $SQL_DAO->getAnswerById($answerId);
    
            if ($answerText !== $oldAnswer['answer_txt']) {
                // Answer has changed so update
                $SQL_DAO->updateAnswer($answerId, $answerText, $answerSuccess, $currentTime);
            }
        } else if ($answerText != '') {
            // New answer
            $SQL_DAO->createAnswer($USER->id, $questionId, $answerText, $answerSuccess, $currentTime);
        }
    } elseif($answerId > -1) {
        $oldAnswer = $SQL_DAO->getAnswerById($answerId);
        $totalScore += ($oldAnswer['answer_success'] ? 1 : 0) ;
    }


}

$totalScore = $totalScore / $_POST["Total"];

LTIX::gradeSend($totalScore);

header( 'Location: '.addSession('../student-home.php') ) ;

