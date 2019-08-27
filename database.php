<?php

// The SQL to uninstall this tool
$DATABASE_UNINSTALL = array(
    // Nothing
);

// The SQL to create the tables if they don't exist
$DATABASE_INSTALL = array(
    array( "{$CFG->dbprefix}sql_splash",
        "create table {$CFG->dbprefix}sql_splash (
    user_id       INTEGER NOT NULL DEFAULT 0,
    skip_splash   BOOL NOT NULL DEFAULT 0,
    PRIMARY KEY(user_id)
	
) ENGINE = InnoDB DEFAULT CHARSET=utf8"),
    array( "{$CFG->dbprefix}sql_main",
        "create table {$CFG->dbprefix}sql_main (
    sql_id       INTEGER NOT NULL AUTO_INCREMENT,
    user_id     INTEGER NOT NULL,
    context_id  INTEGER NOT NULL,
	link_id     INTEGER NOT NULL,
	title       VARCHAR(255) NULL,
    modified    datetime NULL,
    
    PRIMARY KEY(sql_id)
	
) ENGINE = InnoDB DEFAULT CHARSET=utf8"),    
    array( "{$CFG->dbprefix}sql_question",
        "create table {$CFG->dbprefix}sql_question (
    question_id   INTEGER NOT NULL AUTO_INCREMENT,
    sql_id         INTEGER NOT NULL,
    question_num  INTEGER NULL,
    question_database  VARCHAR(45) NULL,
    question_tables  TINYTEXT NULL,
    question_txt  TEXT NULL,   
    question_solution  TEXT NULL,   
    modified      datetime NULL,
    
    CONSTRAINT `{$CFG->dbprefix}sql_ibfk_1`
        FOREIGN KEY (`sql_id`)
        REFERENCES `{$CFG->dbprefix}sql_main` (`sql_id`)
        ON DELETE CASCADE,

    PRIMARY KEY(question_id)
	
) ENGINE = InnoDB DEFAULT CHARSET=utf8"),
    array( "{$CFG->dbprefix}sql_answer",
        "create table {$CFG->dbprefix}sql_answer (
    answer_id    INTEGER NOT NULL AUTO_INCREMENT,
    user_id      INTEGER NOT NULL,
    question_id  INTEGER NOT NULL,
	answer_txt   TEXT NULL,
    modified     datetime NULL,
    
    CONSTRAINT `{$CFG->dbprefix}sql_ibfk_2`
        FOREIGN KEY (`question_id`)
        REFERENCES `{$CFG->dbprefix}sql_question` (`question_id`)
        ON DELETE CASCADE,
    
    PRIMARY KEY(answer_id)
    
) ENGINE = InnoDB DEFAULT CHARSET=utf8")
);
