<?php

namespace Kanboard\Plugin\TaskTimesheets\Helper;


use Kanboard\Model\TaskFinderModel;
use Kanboard\Plugin\TaskTimesheets\Model\TaskTimesheetModel;
use Kanboard\Core\Base;

class TaskTimesheetHelper extends Base
{
    public function getTaskTimesheetPaginator($task)
    {
        $taskTimesheetModel = new TaskTimesheetModel($this->container);
        $timesheet_paginator = $this->paginator
            ->setUrl('TaskViewController', 'timetracking', array('task_id' => $task['id'], 'project_id' => $task['project_id'], 'pagination' => 'timesheets'))
            ->setMax(10)
            ->setOrder('start')
            ->setDirection('DESC')
            ->setQuery($taskTimesheetModel->getTaskQuery($task['id']))
            ->calculateOnlyIf($this->request->getStringParam('pagination') === 'timesheets');

        // return $taskTimesheetModel->getAll($taskId);
        return $timesheet_paginator;
    }

    public function getUserTimesheetPaginator($user)
    {
        $taskTimesheetModel = new TaskTimesheetModel($this->container);
        $timesheet_paginator = $this->paginator
            ->setUrl('UserViewController', 'timesheet', array('user_id' => $user['id'], 'pagination' => 'timesheets'))
            ->setMax(10)
            ->setOrder('start')
            ->setDirection('DESC')
            ->setQuery($taskTimesheetModel->getUserQuery($user['id']))
            ->calculateOnlyIf($this->request->getStringParam('pagination') === 'timesheets');

        // return $taskTimesheetModel->getAll($taskId);
        return $timesheet_paginator;
    }
}