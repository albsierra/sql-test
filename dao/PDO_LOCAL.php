<?php
namespace SQL\DAO;
use PDO;

class PDO_LOCAL {

    /*** mysql hostname ***/
    private $hostname = 'localhost';

    private $connection;

    public function __construct($CFG, $database) {
        try {
            $this->connection =
                new PDO(
                    "mysql:host=$this->hostname;dbname=$database",
                    $CFG->dbuser,
                    $CFG->dbpass
                );
            }
        catch(PDOException $e)
            {
            echo $e->getMessage();
            }
    }

    public function gradeAnswer($queryAnswer, $question) {
        return $this->compareQueries(
            $queryAnswer,
            $question
        );
    }

    private function compareQueries($resultAnswer, $question) {
        return
            $this->getQueryResult($question, $resultAnswer) === $this->getQueryResult($question) ? 1 : 0;
    }

    private function getQueryResult($question, $resultAnswer = null) {
        $this->connection->beginTransaction();
        $query = (isset($resultAnswer) ? $resultAnswer : $question['question_solution']);
        $resultQuery = $this->connection->prepare($query);
        $resultQuery->execute();
        if ($question['question_type'] == 'DML') {
            $query = $question['question_probe'];
            $resultQuery = $this->connection->prepare($query);
            $resultQuery->execute();
        }
        $resultArray = $resultQuery->fetchAll();
        $this->connection->rollBack();
        return $resultArray;
    }

    public function getQueryTable($question) {
        $resultQueryString = '';
        if ($question['question_type'] == 'SELECT') {
            $query = $question['question_solution'];
            $resultQueryString = "<div class='table-results'><table>";
            $resultQuery = $this->connection->prepare($query);
            $resultQuery->execute();
            $resultQueryString .= $this->getHeaderQueryTable($resultQuery);
            $resultQueryString .= $this->getBodyQueryTable($resultQuery);
            $resultQueryString .= "</table></div>";
        }
        return $resultQueryString;
    }

    private function getHeaderQueryTable($resultQuery) {
        $tableHeader = "<tr>";
        for ($i = 0; $i < $resultQuery->columnCount(); $i++) {
            $col = $resultQuery->getColumnMeta($i);
            $tableHeader .= "<th>" . $col['name'] . "</th>";
        }
        $tableHeader .= "</tr>";
        return $tableHeader;
    }

    private function getBodyQueryTable($resultQuery) {
        $tableBody = "";
        while ($row = $resultQuery->fetch(PDO::FETCH_NUM)) {
            $tableBody .= "<tr>";
            foreach ($row as $column) {
                $tableBody .= "<td>" . $column . "</td>";
            }
            $tableBody .= "</tr>";
        }
        return $tableBody;
    }
}
