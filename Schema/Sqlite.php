<?php

namespace Kanboard\Plugin\TaskTimesheets\Schema;

const VERSION = 1;

function version_1($pdo)
{
  $pdo->exec("
        CREATE TABLE task_time_tracking (
            id INTEGER PRIMARY KEY,
            user_id INTEGER NOT NULL,
            task_id INTEGER NOT NULL,
            start INTEGER DEFAULT 0,
            end INTEGER DEFAULT 0,
            time_spent REAL DEFAULT 0,
            comment TEXT,
            is_billable INTEGER,
            FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY(task_id) REFERENCES tasks(id) ON DELETE CASCADE
        )
  ");
}
