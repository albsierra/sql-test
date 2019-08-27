<?php

require_once('../config.php');
require_once('dao/SQL_DAO.php');

use \Tsugi\Core\LTIX;
use \SQL\DAO\SQL_DAO;

// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;

$SQL_DAO = new SQL_DAO($PDOX, $p);

// Start of the output
$OUTPUT->header();

include("tool-header.html");

$OUTPUT->bodyStart();

$SetID = $_SESSION["sql_id"];

$toolTitle = $SQL_DAO->getMainTitle($_SESSION["sql_id"]);

if (!$toolTitle) {
    $toolTitle = "SQL Test";
}

$questions = $SQL_DAO->getQuestions($SetID);
$totalQuestions = count($questions);

$moreToSubmit = false;

?>
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-3 col-sm-offset-1" id="sqlInfo">
            <h1><?php echo($toolTitle); ?></h1>
            <p>Use the form to respond to the question prompts in the list. You can respond to each question all at once or one at a time over multiple sessions. However, once you respond to a question you will not be able to edit or delete your answer.</p>
        </div>
        <div class="col-sm-7">
            <form method="post" action="actions/Answer_Submit.php">
                <div class="list-group fadeInFast student-container" id="sqlContentContainer">
                    <?php
                    if ($totalQuestions == 0) {
                        echo ('<h4 class="alert alert-info text-center">No question prompts have been created.</h4>');
                    } else {
                        ?>
                        <div class="list-group-item">
                            <h3>Questions (<?php echo($totalQuestions); ?>)</h3>
                        </div>
                        <?php
                        foreach ($questions as $question) {
                            $answerText = "";
                            $question_id = $question["question_id"];
                            $answerId = -1;

                            $answer = $SQL_DAO->getStudentAnswerForQuestion($question_id, $USER->id);

                            if ($answer) {
                                $answerId = $answer['answer_id'];
                                $answerText = $answer['answer_txt'];
                            }
// TODO Show tables schemas
                            echo('<div class="list-group-item">
                                <h4>'.$question["question_txt"].'</h4>
                                <h5><b>Database:</b> '.$question["question_database"].'</h5>
                                <h6><b>Tables:</b> '.$question["question_tables"].'</h6>
                                <p>');

                            if (!$answer || $answerText == "") {
                                echo('<textarea class="form-control" name="A'.$question["question_num"].'" rows="3" autofocus></textarea>');
                                $moreToSubmit = true;
                            } else {
                                $dateTime = new DateTime($answer['modified']);
                                $formattedDate = $dateTime->format("m-d-y")." at ".$dateTime->format("h:i A");

                                echo($answerText.'
                                    <div class="text-right text-muted"><span aria-hidden="true" class="fa fa-check text-success"></span> '.$formattedDate.'</div>
                                    <input type="hidden" name="A'.$question["question_num"].'" value="'.$answerText.'" />');
                            }
                            echo ('</p>');
                            echo('<input type="hidden" name="QuestionID'.$question["question_num"].'" value="'.$question_id.'"/>');
                            echo('<input type="hidden" name="AnswerID'.$question["question_num"].'" value="'.$answerId.'"/>');

                            echo('</div>');
                        }
                    }
                    ?>
                </div>
                <input type="hidden" name="Total" value="<?php echo($totalQuestions); ?>"/>
                <?php
                if ($moreToSubmit) {
                    echo('<input type="submit" class="btn btn-success big-shadow pull-right" value="Save Responses">');
                }
                ?>
            </form>
        </div>
    </div>
</div>
<?php
$OUTPUT->footerStart();

$OUTPUT->footerEnd();
