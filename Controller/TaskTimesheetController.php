<?php

namespace Kanboard\Plugin\TaskTimesheets\Controller;

use Kanboard\Controller\BaseController;
use Kanboard\Plugin\TaskTimesheets\Model\TaskTimesheetModel;
use Kanboard\Plugin\TaskTimesheets\Validator\TaskTimesheetValidator;

/**
 * Column Controller
 *
 * @package  Kanboard\Plugin\Timetrackingeditor\Controller
 * @author   Frederic Guillot
 */
class TaskTimesheetController extends BaseController
{
  /**
   * Show Form to create new entry
   * @access public
   * @param array $values
   * @param array $errors
   */
  public function create(array $values = array(), array $errors = array())
  {
    $project = $this->getProject();

    if (empty($values)) {
      $values = array(
        'project_id' => $project['id'],
        'task_id' => $this->request->getIntegerParam('task_id'),
      );
      // set Default Start
      $values['start'] = date($this->dateParser->getUserDateTimeFormat());
      // $values = $this->formatValueDates($values);
    }

    $autofocus = "time_spent";

    $this->response->html($this->template->render('TaskTimesheets:create', array(
      'values' => $values,
      'errors' => $errors,
      // 'project' => $project,
      'autofocus' => $autofocus,
      'title' => t('Add new task time tracking event')
    )));
  }


  /**
   * Save a newly created time tracking entry
   * @access public
   * @param array $values
   * @param array $errors
   */
  public function save(array $values = array(), array $errors = array())
  {
    $project = $this->getProject();
    $values = $this->request->getValues();

    list($valid, $errors) = $this->taskTimesheetValidator->validateCreation($values);

    if ($valid && $this->taskTimesheetModel->create($values)) {
      $this->updateTimespent($values['task_id']);
      // if (isset($values['is_billable']) && $values['is_billable'] == 1) {
      //   // $this->updateTimebillable($values['task_id'], $values['opposite_subtask_id'], $values['time_spent']);
      // }
      $this->flash->success(t('Timetracking entry added successfully.'));

      return $this->afterSave($project, $values);
    }

    $this->flash->failure(t('Unable to create your time tracking entry.'));
    return $this->create($values, $errors);
  }

  /**                 
   * Edit an existing entry      
   *                  
   * @access public      
   * @param array $values      
   * @param array $errors      
   */
  public function edit(array $values = array(), array $errors = array())
  {
    $project = $this->getProject();

    if (empty($values)) {
      $values = array(
        'project_id' => $project['id'],
        'task_id' => $this->request->getIntegerParam('task_id'),
        'id' => $this->request->getIntegerParam('id')
      );
    }
    $values = $this->taskTimesheetModel->getById($this->request->getIntegerParam('id')); 

    $values = $this->formatValueDates($values);      
    // $values['subtask'] = $values['subtask_title'];      
    // $values['opposite_subtask_id']  = $values['subtask_id'];      

    $this->response->html($this->template->render('TaskTimesheets:edit', array(
      'values' => $values,
      'errors' => $errors,
      'project' => $project,
      'title' => t('Edit a time tracking event')
    )));
  }


  /**
   * Confirmation dialog before removing an entry
   *
   * @access public
   */
  public function confirm()
  {

    $id = $this->request->getIntegerParam('id');

    $timesheet = ($this->taskTimesheetModel->getById($id));

    $this->response->html($this->template->render('taskTimesheets:remove', array(
      'timesheet' => $timesheet,
    )));
  }

  /**
   * Remove an entry
   *
   * @access public
   */
  public function remove()
  {
    $this->checkCSRFParam();
    $id = $this->request->getIntegerParam('id');
    $timesheet = $this->taskTimesheetModel->getById($id);

    if ($this->taskTimesheetModel->remove($id)) {
      $this->updateTimespent($timesheet['task_id'],  $timesheet['time_spent'] * -1);
      // if ($timetracking['is_billable'] == 1) {
      //     $this->updateTimebillable($timetracking['task_id'], $timetracking['subtask_id'], $timetracking['time_spent'] * -1);
      // }
      $this->flash->success(t('Entry removed successfully.'));
    } else {
      $this->flash->failure(t('Unable to remove this entry.'));
    }

    $this->response->redirect($this->helper->url->to('TaskViewController', 'timetracking', array('project_id' => $timesheet['project_id'], 'task_id' => $timesheet['task_id'])), true);
  }

    /**
    * Update a time tracking entry
    *
    * @access public
    * @param array $values
    * @param array $errors
    */
    public function update(array $values = array(), array $errors = array())
    {
      $project = $this->getProject();
      $values = $this->request->getValues();
      $oldtimetracking = $this->taskTimesheetModel->getById($values['id']);

      if (!isset($values['is_billable'])) {
        $values["is_billable"] = 0;
      }

      list($valid, $errors) = $this->taskTimesheetValidator->validateModification($values);

      // preserve User Id
      $values['user_id'] = (int)$oldtimetracking['user_id'];

      // $this->preserveStartTime($values, $oldtimetracking);

      if ($valid && $this->taskTimesheetModel->update($values)) {
        $this->flash->success(t('Timetracking entry updated successfully.'));
        $this->updateTimespent($values['task_id'], $oldtimetracking['subtask_id'], $oldtimetracking['time_spent'] * -1);
        $this->updateTimespent($values['task_id'], $values['opposite_subtask_id'], $values['time_spent']);

        return $this->afterSave($project, $values);
      }

      $this->flash->failure(t('Unable to update your time tracking entry.'));
      return $this->edit($values, $errors);

    }



  /**
   * update time spent for the task
   *
   * @access private
   * @param int $task_id
   * @param int $time_spent
   * @return bool
   */

  private function updateTimespent($task_id)
  {
    return $this->taskTimesheetModel->updateTaskTimeTracking($task_id);
  }


  /**
   * Present another, empty form if add_another is activated
   *
   * @access private
   * @param array $project
   * @param array $values
   */
  private function afterSave(array $project, array &$values)
  {
    if (isset($values['add_another']) && $values['add_another'] == 1) {
      return $this->create(array(
        'project_id' => $this->getProject()['id'],
        'task_id' => $values['task_id'],
        'start' => $values['start'],
        'is_billable' => $values['is_billable'],
        'add_another' => 1,
      ));
    }

    return $this->response->redirect($this->helper->url->to('TaskViewController', 'timetracking', array('project_id' => $project['id'], 'task_id' => $values['task_id'])), true);
  }



  /**
   * @param array $values
   * @return array
   */
  protected function formatValueDates(array $values): array
  {
    $values = $this->dateParser->format($values, array('start'), $this->dateParser->getUserDateFormat());
    return $values;
  }
}
