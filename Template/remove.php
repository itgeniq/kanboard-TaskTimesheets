<div class="page-header">
    <h2><?= t('Remove a time tracking entry') ?></h2>
</div>

<div class="confirm">
    <div class="alert alert-info">
        <?= t('Do you really want to remove this entry?') ?>
        <ul>
            <li>
                <strong><?= $this->text->e($timesheet['time_spent']) ?> hours</strong>
            </li>
        </ul>
    </div>

    <?= $this->modal->confirmButtons(
        'TaskTimesheetController',
        'remove',
        array('plugin' => 'taskTimesheets', 'id' => $timesheet['id'], 'project_id' => $timesheet['project_id'], 'task_id' => $timesheet['task_id'])
    ) ?>

</div>
