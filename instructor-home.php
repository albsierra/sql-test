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

$toolTitle = $SQL_DAO->getMainTitle($_SESSION["sql_id"]);

if (!$toolTitle) {
    $toolTitle = "SQL test";
}

$questions = $SQL_DAO->getQuestions($_SESSION["sql_id"]);

$totalQuestions = count($questions);
?>
<div id="sideNav" class="side-nav">
    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()"><span class="fa fa-times"></span></a>
    <a href="splash.php"><span class="fa fa-fw fa-pencil-square" aria-hidden="true"></span> Getting Started</a>
    <a href="actions/ExportToFile.php"><span class="fa fa-fw fa-cloud-download" aria-hidden="true"></span> Export Results</a>
    <a href="javascript:void(0);" id="editTitleLink"><span class="fa fa-fw fa-pencil" aria-hidden="true"></span> Edit Tool Title</a>
    <a href="import-questions.php" class="disabled"><span class="fa fa-fw fa-upload" aria-hidden="true"></span> Import Questions</a>
    <a href="actions/DeleteAll.php" onclick="return confirmResetTool();"><span class="fa fa-fw fa-trash" aria-hidden="true"></span> Reset Tool</a>
</div>

    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header">
                <a class="navbar-brand" href="javascript:void(0);" onclick="openSideNav();"><span class="fa fa-bars"></span> Menu</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-3 col-sm-offset-1" id="sqlInfo">
                <input type="hidden" id="sess" value="<?php echo($_GET["PHPSESSID"]) ?>">
                <h1 contenteditable="true" id="toolTitle"><?php echo($toolTitle); ?></h1>
                <p>Use the button below to add a question to the list. Once a question has been created, you can make changes to the text or delete it and its answers.</p>
                <a href="#addOrEditQuestion" data-toggle="modal" class="btn btn-success small-shadow"><span class="fa fa-plus"></span> Add Question</a>
            </div>
            <div class="col-sm-7">
                <div class="list-group fadeInFast" id="sqlContentContainer">
                    <div class="list-group-item">
                        <a href="view-all-results.php" class="pull-right">View All Results <span id="viewAllChevron" class="fa fa-chevron-right"></span></a>
                        <h3>Questions (<?php echo($totalQuestions); ?>)</h3>
                    </div>
                    <?php
                    foreach ($questions as $question) {
                        $totalAnswers = $SQL_DAO->countAnswersForQuestion($question["question_id"]);
                        echo('
                        <div class="list-group-item">
                            <div id="questionText'.$question["question_id"].'">'.$question["question_txt"].'</div>
                            <h5 id="questionDatabase'.$question["question_id"].'"><b>Database:</b> '.$question["question_database"].'</h5>
                            <h6 id="questionType'.$question["question_id"].'"><b>Type:</b> '.$question["question_type"].'</h4>
                            <form id="questionTextForm'.$question["question_id"].'" action="actions/AddOrEditQuestion.php" method="post" style="display:none;">
                                <p>
                                    <input type="hidden" name="questionId" value="'.$question["question_id"].'">
                                    <label for="questionDatabase">Question Database</label>
                                    <input type="text" name="questionDatabase" value="'.$question["question_database"].'">
                                    <label for="questionType">Question Type</label>
                                    <select name="questionType" id="questionType">
                                        <option value="SELECT" ' . ($question["question_type"] == 'SELECT' ? "selected" : "") . '>SELECT</option>
                                        <option value="DML" ' . ($question["question_type"] == 'DML' ? "selected" : "") . '>DML</option>
                                    </select>
                                    <textarea class="form-control ckeditor" id="questionText" name="questionText" rows="4" required>'.$question["question_txt"].'</textarea>
                                    <label for="questionSolution">Question Solution</label>
                                    <textarea class="form-control" name="questionSolution" rows="4" required>'.$question["question_solution"].'</textarea>
                                    <label for="questionProbe">Question Probe</label>
                                    <textarea class="form-control" name="questionProbe" rows="4">'.$question["question_probe"].'</textarea>
                                </p>
                                <div class="text-right">
                                    <input type="submit" class="btn btn-success" value="Save" form="questionTextForm'.$question["question_id"].'">
                                    <a href="javascript:void(0);" class="btn btn-link" onclick="cancelEditQuestionText('.$question["question_id"].');">Cancel</a>
                                </div>                                
                            </form>
                            <div class="question-actions button-group pull-right">
                                <a href="javascript:void(0);" onclick="editQuestionText('.$question["question_id"].');">
                                    <span class="fa fa-lg fa-pencil" aria-hidden="true"></span>
                                    <span class="sr-only">Edit Question Text</span>
                                </a>
                                <a onclick="return confirmDeleteQuestion();" href="actions/DeleteQuestion.php?question_id='.$question["question_id"].'">
                                    <span aria-hidden="true" class="fa fa-lg fa-trash"></span>
                                    <span class="sr-only">Delete Question</span>
                                </a>
                            </div>
                            <a class="question-answers" href="view-answers.php?question_id='.$question["question_id"].'">Answers ('.$totalAnswers.')</a>
                        </div>
                        ');
                    }
                    ?>
                </div>
            </div>
        </div>

    </div>

    <div class="modal fade" id="addOrEditQuestion" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add Question</h4>
                </div>
                <form method="post" id="addQuestionForm" action="actions/AddOrEditQuestion.php">
                    <div class="modal-body">
                        <input type="hidden" name="questionId" id="questionId" value="-1">
                        <label for="questionDatabase">Question Database</label>
                        <input type="text" class="form-control" name="questionDatabase" value="" autofocus required >
                        <label for="questionType">Question Type</label>
                        <select name="questionType" id="questionType">
                            <option value="SELECT" >SELECT</option>
                            <option value="DML" >DML</option>
                        </select>
                        <br />
                        <label for="questionText">Question Text</label>
                        <textarea class="form-control ckeditor" name="questionText" id="questionText" rows="4" required></textarea>
                        <label for="questionSolution">Question Solution</label>
                        <textarea class="form-control" name="questionSolution" id="questionSolution" rows="4" required></textarea>
                        <label for="questionProbe">Question Probe</label>
                        <textarea class="form-control" name="questionProbe" id="questionProbe" rows="4"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                        <input type="submit" form="addQuestionForm" class="btn btn-success" value="Save">
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php

$OUTPUT->footerStart();

include("tool-footer.html");

$OUTPUT->footerEnd();
