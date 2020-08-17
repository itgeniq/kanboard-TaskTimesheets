<?php

namespace Kanboard\Plugin\TaskTimesheets\Schema;

const VERSION = 1;

function version_1($pdo)
{
  $pdo->exec("
        CREATE TABLE task_time_tracking (
            id INT NOT NULL AUTO_INCREMENT,
            user_id INT NOT NULL,
            task_id INT NOT NULL,
            start INT DEFAULT 0,
            end INT DEFAULT 0,
            time_spent FLOAT DEFAULT 0,
            comment TEXT,
            is_billable INT,
            PRIMARY KEY(id),
            FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY(task_id) REFERENCES tasks(id) ON DELETE CASCADE
        )
  ");
}
