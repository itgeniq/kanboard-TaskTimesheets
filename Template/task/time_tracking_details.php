<?= $this->render('task/details', array(
    'task' => $task,
    'tags' => $tags,
    'project' => $project,
    'editable' => false,
)) ?>

<?= $this->render('task/time_tracking_summary', array('task' => $task)) ?>

<?php
  $taskTimesheets_paginator = $this->taskTimesheetHelper->getTaskTimesheetPaginator($task);
?>
<h3><?= t('Task timesheet') ?></h3>

<?= $this->modal->medium("plus",t('Add a new timetracking entry'), 'TaskTimesheetController',
        'create', array(
            'plugin' => 'taskTimesheets',
            'task_id' => $task['id'],
            'project_id' => $task['project_id'])) ?>

<?php if ($taskTimesheets_paginator->isEmpty()): ?>
    <p class="alert"><?= t('There is nothing to show.') ?></p>
<?php else: ?>
    <table class="table-fixed table-scrolling">
        <tr>
            <th class="column-15"><?= $taskTimesheets_paginator->order(t('User'), 'username') ?></th>
            <th class="column-20"><?= $taskTimesheets_paginator->order(t('Start'), 'start') ?></th>
            <th class="column-20"><?= $taskTimesheets_paginator->order(t('End'), 'end') ?></th>
            <th class="column-10"><?= $taskTimesheets_paginator->order(t('Time spent'), \Kanboard\Plugin\TaskTimesheets\Model\TaskTimesheetModel::TABLE.'.time_spent') ?></th>
            <th class="column-3"></th>
        </tr>
        <?php foreach ($taskTimesheets_paginator->getCollection() as $record): ?>
        <tr>
            <td><?= $this->url->link($this->text->e($record['user_fullname'] ?: $record['username']), 'UserViewController', 'show', array('user_id' => $record['user_id'])) ?></td>
            <td><?= $this->dt->datetime($record['start']) ?></td>
            <td><?= $this->dt->datetime($record['end']) ?></td>
            <td><?= n($record['time_spent']).' '.t('hours') ?></td>
            <td>
            <?php if ($this->subtaskPermission->canEdit($record)) { ?>
                <?= $this->render('taskTimesheets:menu', array(
                    'task' => $task,
                    'id' => $record['id']
                )) ?>
            <?php } ?>
            </td>
        </tr>
        <?php endforeach ?>
    </table>
    <?= $taskTimesheets_paginator ?>
<?php endif ?>

<h3><?= t('Subtask timesheet') ?></h3>
<?php if ($subtask_paginator->isEmpty()): ?>
    <p class="alert"><?= t('There is nothing to show.') ?></p>
<?php else: ?>
    <table class="table-fixed table-scrolling">
        <tr>
            <th class="column-15"><?= $subtask_paginator->order(t('User'), 'username') ?></th>
            <th><?= $subtask_paginator->order(t('Subtask'), 'subtask_title') ?></th>
            <th class="column-20"><?= $subtask_paginator->order(t('Start'), 'start') ?></th>
            <th class="column-20"><?= $subtask_paginator->order(t('End'), 'end') ?></th>
            <th class="column-10"><?= $subtask_paginator->order(t('Time spent'), \Kanboard\Model\SubtaskTimeTrackingModel::TABLE.'.time_spent') ?></th>
        </tr>
        <?php foreach ($subtask_paginator->getCollection() as $record): ?>
        <tr>
            <td><?= $this->url->link($this->text->e($record['user_fullname'] ?: $record['username']), 'UserViewController', 'show', array('user_id' => $record['user_id'])) ?></td>
            <td><?= t($record['subtask_title']) ?></td>
            <td><?= $this->dt->datetime($record['start']) ?></td>
            <td><?= $this->dt->datetime($record['end']) ?></td>
            <td><?= n($record['time_spent']).' '.t('hours') ?></td>
        </tr>
        <?php endforeach ?>
    </table>

    <?= $subtask_paginator ?>
<?php endif ?>
