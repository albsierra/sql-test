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

    public function gradeAnswer($queryAnswer, $querySolution) {
        return $this->compareQueries(
            $queryAnswer,
            $querySolution
        );
    }

    private function compareQueries($resultAnswer, $resultSolution) {
        return
            $this->getQueryTable($resultAnswer) === $this->getQueryTable($resultSolution) ? 1 : 0;
    }

    public function getQueryTable($query) {
        $resultQueryString = "<div class='table-results'><table>";
        $resultQuery = $this->connection->prepare($query);
        $resultQuery->execute();
        $resultQueryString .= $this->getHeaderQueryTable($resultQuery);
        $resultQueryString .= $this->getBodyQueryTable($resultQuery);
        $resultQueryString .= "</table></div>";
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
