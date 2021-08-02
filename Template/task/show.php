<details class="accordion-section" <?= empty($this->taskTimesheetHelper->getTaskTimesheetPaginator($task)) ? '' : 'open' ?>>
    <summary class="accordion-title"><?= t('Task Time Sheet') ?></summary>
    <div class="accordion-content">
        <?= $this->render('taskTimesheets:task/time_tracking_list', array('task' => $task)) ?>
    </div>
</details>
