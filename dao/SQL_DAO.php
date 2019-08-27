<?php
namespace SQL\DAO;

class SQL_DAO {

    private $PDOX;
    private $p;

    public function __construct($PDOX, $p) {
        $this->PDOX = $PDOX;
        $this->p = $p;
    }

    function skipSplash($user_id) {
        $query = "SELECT skip_splash FROM {$this->p}sql_splash WHERE user_id = :userId";
        $arr = array(':userId' => $user_id);
        $context = $this->PDOX->rowDie($query, $arr);
        return $context["skip_splash"];
    }

    function toggleSkipSplash($user_id) {
        $skip = $this->skipSplash($user_id) ? 0 : 1;
        $query = "INSERT INTO {$this->p}sql_splash (user_id, skip_splash) VALUES (:userId, ".$skip.") ON DUPLICATE KEY UPDATE skip_splash = ".$skip;
        $arr = array(':userId' => $user_id);
        $this->PDOX->queryDie($query, $arr);
    }

    function getOrCreateMain($user_id, $context_id, $link_id, $current_time) {
        $main_id = $this->getMainID($context_id, $link_id);
        if (!$main_id) {
            return $this->createMain($user_id, $context_id, $link_id, $current_time);
        } else {
            return $main_id;
        }
    }

    function getMainID($context_id, $link_id) {
        $query = "SELECT sql_id FROM {$this->p}sql_main WHERE context_id = :context_id AND link_id = :link_id";
        $arr = array(':context_id' => $context_id, ':link_id' => $link_id);
        $context = $this->PDOX->rowDie($query, $arr);
        return $context["sql_id"];
    }

    function createMain($user_id, $context_id, $link_id, $current_time) {
        $query = "INSERT INTO {$this->p}sql_main (user_id, context_id, link_id, modified) VALUES (:userId, :contextId, :linkId, :currentTime);";
        $arr = array(':userId' => $user_id, ':contextId' => $context_id, ':linkId' => $link_id, ':currentTime' => $current_time);
        $this->PDOX->queryDie($query, $arr);
        return $this->PDOX->lastInsertId();
    }

    function getMainTitle($sql_id) {
        $query = "SELECT title FROM {$this->p}sql_main WHERE sql_id = :sqlId";
        $arr = array(':sqlId' => $sql_id);
        return $this->PDOX->rowDie($query, $arr)["title"];
    }

    function updateMainTitle($sql_id, $title, $current_time) {
        $query = "UPDATE {$this->p}sql_main set title = :title, modified = :currentTime WHERE sql_id = :sqlId;";
        $arr = array(':title' => $title, ':currentTime' => $current_time, ':sqlId' => $sql_id);
        $this->PDOX->queryDie($query, $arr);
    }

    function deleteMain($sql_id, $user_id) {
        $query = "DELETE FROM {$this->p}sql_main WHERE sql_id = :mainId AND user_id = :userId";
        $arr = array(':mainId' => $sql_id, ':userId' => $user_id);
        $this->PDOX->queryDie($query, $arr);
    }

    function getQuestions($sql_id) {
        $query = "SELECT * FROM {$this->p}sql_question WHERE sql_id = :sqlId order by question_num;";
        $arr = array(':sqlId' => $sql_id);
        return $this->PDOX->allRowsDie($query, $arr);
    }

    function getQuestionById($question_id) {
        $query = "SELECT * FROM {$this->p}sql_question WHERE question_id = :questionId;";
        $arr = array(':questionId' => $question_id);
        return $this->PDOX->rowDie($query, $arr);
    }

    function createQuestion($sql_id, $question_database, $question_tables, $question_text, $question_solution, $current_time) {
        $nextNumber = $this->getNextQuestionNumber($sql_id);
        $query = "INSERT INTO {$this->p}sql_question 
            (sql_id, question_num, question_database, question_tables, question_txt, question_solution, modified) 
            VALUES (:sqlId, :questionNum, :questionDatabase, :questionTables, :questionText, :questionSolution, :currentTime);";
        $arr = array(
            ':sqlId' => $sql_id,
            ':questionNum' => $nextNumber,
            ':questionDatabase' => $question_database,
            ':questionTables' => $question_tables,
            ':questionText' => $question_text,
            ':questionSolution' => $question_solution,
            ':currentTime' => $current_time
        );
        $this->PDOX->queryDie($query, $arr);
        return $this->PDOX->lastInsertId();
    }

    function updateQuestion($question_id, $question_database, $question_tables, $question_text, $question_solution, $current_time) {
        $query = "UPDATE {$this->p}sql_question SET
            question_database = :questionDatabase, 
            question_tables = :questionTables, 
            question_txt = :questionText, 
            question_solution = :questionSolution, 
            modified = :currentTime 
            WHERE question_id = :questionId;";
        $arr = array(
            ':questionId' => $question_id,
            ':questionDatabase' => $question_database,
            ':questionTables' => $question_tables,
            ':questionText' => $question_text,
            ':questionSolution' => $question_solution,
            ':currentTime' => $current_time
        );
        $this->PDOX->queryDie($query, $arr);
    }

    function getNextQuestionNumber($sql_id) {
        $query = "SELECT MAX(question_num) as lastNum FROM {$this->p}sql_question WHERE sql_id = :sqlId";
        $arr = array(':sqlId' => $sql_id);
        $lastNum = $this->PDOX->rowDie($query, $arr)["lastNum"];
        return $lastNum + 1;
    }

    function countAnswersForQuestion($question_id) {
        $query = "SELECT COUNT(*) as total FROM {$this->p}sql_answer WHERE question_id = :questionId;";
        $arr = array(':questionId' => $question_id);
        return $this->PDOX->rowDie($query, $arr)["total"];
    }

    function deleteQuestion($question_id) {
        $query = "DELETE FROM {$this->p}sql_question WHERE question_id = :questionId;";
        $arr = array(':questionId' => $question_id);
        $this->PDOX->queryDie($query, $arr);
    }

    function fixUpQuestionNumbers($sql_id) {
        $query = "SET @question_num = 0; UPDATE {$this->p}sql_question set question_num = (@question_num:=@question_num+1) WHERE sql_id = :sqlId ORDER BY question_num";
        $arr = array(':sqlId' => $sql_id);
        $this->PDOX->queryDie($query, $arr);
    }

    function getUsersWithAnswers($sql_id) {
        $query = "SELECT DISTINCT user_id FROM {$this->p}sql_answer a join {$this->p}sql_question q on a.question_id = q.question_id WHERE q.sql_id = :sqlId;";
        $arr = array(':sqlId' => $sql_id);
        return $this->PDOX->allRowsDie($query, $arr);
    }

    function getStudentAnswerForQuestion($question_id, $user_id) {
        $query = "SELECT * FROM {$this->p}sql_answer WHERE question_id = :questionId AND user_id = :userId; ";
        $arr = array(':questionId' => $question_id, ':userId' => $user_id);
        return $this->PDOX->rowDie($query, $arr);
    }

    function getMostRecentAnswerDate($user_id, $sql_id) {
        $query = "SELECT max(a.modified) as modified FROM {$this->p}sql_answer a join {$this->p}sql_question q on a.question_id = q.question_id WHERE a.user_id = :userId AND q.sql_id = :sqlId;";
        $arr = array(':userId' => $user_id, ':sqlId' => $sql_id);
        $context = $this->PDOX->rowDie($query, $arr);
        return $context['modified'];
    }

    function createAnswer($user_id, $question_id, $answer_txt, $current_time) {
        $query = "INSERT INTO {$this->p}sql_answer (user_id, question_id, answer_txt, modified) VALUES (:userId, :questionId, :answerTxt, :currentTime);";
        $arr = array(':userId' => $user_id,':questionId' => $question_id, ':answerTxt' => $answer_txt, ':currentTime' => $current_time);
        $this->PDOX->queryDie($query, $arr);
        return $this->PDOX->lastInsertId();
    }

    function updateAnswer($answer_id, $answer_txt, $current_time) {
        $query = "UPDATE {$this->p}sql_answer set answer_txt = :answerTxt, modified = :currentTime where answer_id = :answerId;";
        $arr = array(':answerId' => $answer_id, ':answerTxt' => $answer_txt, ':currentTime' => $current_time);
        $this->PDOX->queryDie($query, $arr);
    }

    function getAllAnswersToQuestion($question_id) {
        $query = "SELECT * FROM {$this->p}sql_answer WHERE question_id = :questionId;";
        $arr = array(':questionId' => $question_id);
        return $this->PDOX->allRowsDie($query, $arr);
    }

    function getAnswerById($answer_id) {
        $query = "SELECT * FROM {$this->p}sql_answer WHERE answer_id = :answerId;";
        $arr = array(':answerId' => $answer_id);
        return $this->PDOX->rowDie($query, $arr);
    }

    function findEmail($user_id) {
        $query = "SELECT email FROM {$this->p}lti_user WHERE user_id = :user_id;";
        $arr = array(':user_id' => $user_id);
        $context = $this->PDOX->rowDie($query, $arr);
        return $context["email"];
    }

    function findDisplayName($user_id) {
        $query = "SELECT displayname FROM {$this->p}lti_user WHERE user_id = :user_id;";
        $arr = array(':user_id' => $user_id);
        $context = $this->PDOX->rowDie($query, $arr);
        return $context["displayname"];
    }
}
