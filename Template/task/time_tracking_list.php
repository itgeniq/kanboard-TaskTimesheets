<?php
  $taskTimesheets_paginator = $this->taskTimesheetHelper->getTaskTimesheetPaginator($task);
?>
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
            <th class="column-25"><?= $taskTimesheets_paginator->order(t('Comment'), \Kanboard\Plugin\TaskTimesheets\Model\TaskTimesheetModel::TABLE.'.comment') ?></th>
            <th class="column-3"></th>
        </tr>
        <?php foreach ($taskTimesheets_paginator->getCollection() as $record): ?>
        <tr>
            <td><?= $this->url->link($this->text->e($record['user_fullname'] ?: $record['username']), 'UserViewController', 'show', array('user_id' => $record['user_id'])) ?></td>
            <td><?= $this->dt->datetime($record['start']) ?></td>
            <td><?= $this->dt->datetime($record['end']) ?></td>
            <td><?= n($record['time_spent']).' '.t('hours') ?></td>
            <td><?= $record['comment'] ?></td>
            <td>
            <?php if ($this->projectRole->canUpdateTask($record)) { ?>
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


