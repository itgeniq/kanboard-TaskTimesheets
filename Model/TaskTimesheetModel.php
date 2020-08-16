<?php
namespace Kanboard\Plugin\TaskTimesheets\Model;
use Kanboard\Core\Base;
use Kanboard\Model\TaskModel;
use Kanboard\Model\UserModel;
use Kanboard\Model\SubtaskTimeTrackingModel;

/**
 * 
 * TaskTimesheetModel
 *
 * @package  TaskTimesheet\Model
 * @author   itgeniq
 * 
 */

class TaskTimesheetModel extends Base
{
    /**
     * SQL table name
     *
     * @var string
     */
    const TABLE = 'task_time_tracking';

    /**
     * Get a task timesheet entry by the id
     *
     * @access public
     * @param  integer   $tasktimesheetId
     * @return array
     */
    public function getById($tasktimesheetId)
    {
        return $this->db->table(self::TABLE)
            ->columns(
                self::TABLE.'.id',
                self::TABLE.'.task_id',
                self::TABLE.'.end',
                self::TABLE.'.start',
                self::TABLE.'.time_spent',
                self::TABLE.'.user_id',
                self::TABLE.'.comment',
                self::TABLE.'.is_billable',
                TaskModel::TABLE.'.title AS title',
                TaskModel::TABLE.'.project_id',
                UserModel::TABLE.'.username',
                UserModel::TABLE.'.name AS user_fullname'
            )
            ->join(TaskModel::TABLE, 'id', 'task_id')
            ->join(UserModel::TABLE, 'id', 'user_id')
            ->eq(self::TABLE.'.id', $tasktimesheetId)
            ->findOne();
    }


    /**
     * Get all timesheets for a given task
     *
     * @access public
     * @param  integer   $taskId
     * @return array
     */
    public function getAll($taskId)
    {
      return $this->db
                    ->table(TaskTimesheetModel::TABLE)
                    ->eq(TaskTimesheetModel::TABLE.'.task_id', $taskId)
                    ->findAll();
    }

    /**
     * Update a task timesheet entry
     *
     * @access public
     * @param  array $values
     * @return bool
     */
    public function update(array $values)
    {
        $this->prepare($values);
        $result = $this->db->table(self::TABLE)->eq('id', $values['id'])->save($values);

        return $result;
    }

    /**
     * Remove
     *
     * @access public
     * @param  integer $tasktimesheetId
     * @return bool
     */
    public function remove($tasktimesheetId)
    {
        // $this->subtaskEventJob->execute($tasktimesheetId, array(self::EVENT_DELETE));
        return $this->db->table(self::TABLE)->eq('id', $tasktimesheetId)->remove();
    }    

    /**
     * Create a new timesheet entry
     *
     * @access public
     * @param  array    $values    Form values
     * @return bool|integer
     */
    public function create(array $values)
    {
        $this->prepare($values);
        $timesheetId = $this->db->table(self::TABLE)->persist($values);

        // if ($timesheetId !== false) {
        //     $this->subtaskTimeTrackingModel->updateTaskTimeTracking($values['task_id']);
        //     $this->queueManager->push($this->subtaskEventJob->withParams(
        //         $subtaskId, 
        //         array(self::EVENT_CREATE_UPDATE, self::EVENT_CREATE)
        //     ));
        // }

        return $timesheetId;
    }


    /**
     * Prepare data
     *
     * @access public
     * @param  array    $values    Form values
     */
    public function prepare(array &$values)
    {
        if ($this->userSession->isLogged()) {
            $values['user_id'] = $this->userSession->getId();
        }

        $this->helper->model->removeFields($values, array('project_id', 'add_another'));

        // Calculate end time
        $values = $this->dateParser->convert($values, array('start'), true);
        if (array_key_exists('time_spent', $values)) {
          $values["end"] = $values["start"] + ($values['time_spent']*60*60);
        }
    }


    /**
     * Get query for task timesheet (pagination)
     *
     * @access public
     * @param  integer    $task_id    Task id
     * @return \PicoDb\Table
     */
    public function getTaskQuery($task_id)
    {
        return $this->db
                    ->table(self::TABLE)
                    ->columns(
                        self::TABLE.'.id',
                        self::TABLE.'.task_id',
                        self::TABLE.'.end',
                        self::TABLE.'.start',
                        self::TABLE.'.time_spent',
                        self::TABLE.'.user_id',
                        TaskModel::TABLE.'.title AS title',
                        TaskModel::TABLE.'.project_id',
                        UserModel::TABLE.'.username',
                        UserModel::TABLE.'.name AS user_fullname'
                    )
                    ->join(TaskModel::TABLE, 'id', 'task_id')
                    ->join(UserModel::TABLE, 'id', 'user_id', self::TABLE)
                    ->eq(TaskModel::TABLE.'.id', $task_id);
    }


    /**
     * Get query for user timesheet (pagination)
     *
     * @access public
     * @param  integer    $user_id    User id
     * @return \PicoDb\Table
     */
    public function getUserQuery($user_id)
    {
        return $this->db
                    ->table(self::TABLE)
                    ->columns(
                        self::TABLE.'.id',
                        self::TABLE.'.task_id',
                        self::TABLE.'.end',
                        self::TABLE.'.start',
                        self::TABLE.'.time_spent',
                        self::TABLE.'.user_id',
                        TaskModel::TABLE.'.title AS task_title',
                        TaskModel::TABLE.'.project_id',
                        UserModel::TABLE.'.username',
                        UserModel::TABLE.'.name AS user_fullname'
                    )
                    ->join(TaskModel::TABLE, 'id', 'task_id')
                    ->join(UserModel::TABLE, 'id', 'user_id', self::TABLE)
                    ->eq(UserModel::TABLE.'.id', $user_id);
    }



    /**
     * Update task time tracking based on time sheets
     *
     * @access public
     * @param  integer   $task_id    Task id
     * @return bool
     */
    public function updateTaskTimeTracking($task_id)
    {
        $taskTimes = $this->calculateTaskTime($task_id);
        $subtaskTimes = $this->subtaskTimeTrackingModel->calculateSubtaskTime($task_id);

        $total = $taskTimes['time_spent'] + $subtaskTimes['time_spent'];

        //$higherEstimate = $this->keepHigherEstimateFromTask( $task_id, $values['time_estimated'] );
        //$values['time_estimated'] = $higherEstimate;

        return $this->db
            ->table(TaskModel::TABLE)
            ->eq('id', $task_id)
            ->update(array('time_spent' => $total));
    }  
    
    

    /**
     * Sum time spent and time estimated for task
     *
     * @access public
     * @param  integer   $task_id    Task id
     * @return array
     */
    public function calculateTaskTime($task_id)
    {
        return $this->db
                    ->table(self::TABLE)
                    ->eq('task_id', $task_id)
                    ->columns(
                        'SUM(time_spent) AS time_spent',
                    )
                    ->findOne();
    }    
}