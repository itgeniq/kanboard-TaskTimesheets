<?php

require_once 'tests/units/Base.php';
use Kanboard\Model\ConfigModel;
use Kanboard\Model\TaskFinderModel;
use Kanboard\Model\TaskCreationModel;
use Kanboard\Model\ProjectModel;
use Kanboard\Model\UserModel;
use Kanboard\Core\Plugin\SchemaHandler;

use Kanboard\Plugin\TaskTimesheets\Model\TaskTimesheetModel;
use Kanboard\Plugin\TaskTimesheets\Plugin;

class PluginTest extends Base
{

    public function setUp()
    {
        parent::setUp();
        $p = new SchemaHandler($this->container);
        $p->loadSchema('TaskTimesheets');
        // ensure migrations have run.
    }

    public function testPlugin()
    {
        $plugin = new Plugin($this->container);
        $this->assertSame(null, $plugin->initialize());
        $this->assertSame(null, $plugin->onStartup());
        $this->assertNotEmpty($plugin->getPluginName());
        $this->assertNotEmpty($plugin->getPluginDescription());
        $this->assertNotEmpty($plugin->getPluginAuthor());
        $this->assertNotEmpty($plugin->getPluginVersion());
        $this->assertNotEmpty($plugin->getPluginHomepage());
    }


    public function testCreateTaskTimeSheet()
    {
        $projectModel = new ProjectModel($this->container);
        $taskCreationModel = new TaskCreationModel($this->container);
        $taskTimesheetModel = new TaskTimesheetModel($this->container);
        $userModel = new UserModel($this->container);

        $this->assertEquals(1, $projectModel->create(array('name' => 'test1')));
        $this->assertEquals(1, $taskCreationModel->create(array('title' => 'test 1', 'project_id' => 1, 'column_id' => 1, 'owner_id' => 1)));
        $this->assertEquals(2, $userModel->create(array('username' => 'user #1', 'password' => '123456', 'name' => 'User')));
        $this->assertEquals(1, $taskTimesheetModel->create(array( 'task_id' => 1, 'user_id' => 2, 'start' => '08/16/2020 00:00', 'time_spent' => 1.0 )));
    }
    

    public function testGetTaskTimeSheetById()
    {
        $projectModel = new ProjectModel($this->container);
        $taskCreationModel = new TaskCreationModel($this->container);
        $taskTimesheetModel = new TaskTimesheetModel($this->container);
        $userModel = new UserModel($this->container);

        $this->assertEquals(1, $projectModel->create(array('name' => 'test1')));
        $this->assertEquals(1, $taskCreationModel->create(array('title' => 'test 1', 'project_id' => 1, 'column_id' => 1, 'owner_id' => 1)));
        $this->assertEquals(2, $userModel->create(array('username' => 'user #1', 'password' => '123456', 'name' => 'User')));
        $this->assertEquals(1, $taskTimesheetModel->create(array( 'task_id' => 1, 'user_id' => 2, 'start' => '08/16/2020 00:00', 'time_spent' => 1.0 )));
        $ts = $taskTimesheetModel->getById(1);
        $this->assertEquals(1, $ts['id']);
        $this->assertEquals(1, $ts['task_id']);
        $this->assertEquals(2, $ts['user_id']);
    }

    public function testRemoveTaskTimeSheetById()
    {
        $projectModel = new ProjectModel($this->container);
        $taskCreationModel = new TaskCreationModel($this->container);
        $taskTimesheetModel = new TaskTimesheetModel($this->container);
        $userModel = new UserModel($this->container);

        $this->assertEquals(1, $projectModel->create(array('name' => 'test1')));
        $this->assertEquals(1, $taskCreationModel->create(array('title' => 'test 1', 'project_id' => 1, 'column_id' => 1, 'owner_id' => 1)));
        $this->assertEquals(2, $userModel->create(array('username' => 'user #1', 'password' => '123456', 'name' => 'User')));
        $this->assertEquals(1, $taskTimesheetModel->create(array( 'task_id' => 1, 'user_id' => 2, 'start' => '08/16/2020 00:00', 'time_spent' => 1.0 )));

        $ts = $taskTimesheetModel->getById(1);
        $this->assertNotEmpty($ts);

        $this->assertTrue($taskTimesheetModel->remove(1));

        $ts = $taskTimesheetModel->getById(1);
        $this->assertEmpty($ts);
    }

    public function testTaskTimeSheetModification()
    {
        $projectModel = new ProjectModel($this->container);
        $taskCreationModel = new TaskCreationModel($this->container);
        $taskTimesheetModel = new TaskTimesheetModel($this->container);
        $userModel = new UserModel($this->container);

        $this->assertEquals(1, $projectModel->create(array('name' => 'test1')));
        $this->assertEquals(1, $taskCreationModel->create(array('title' => 'test 1', 'project_id' => 1, 'column_id' => 1, 'owner_id' => 1)));
        $this->assertEquals(2, $userModel->create(array('username' => 'user #1', 'password' => '123456', 'name' => 'User')));
        $this->assertEquals(1, $taskTimesheetModel->create(array( 'task_id' => 1, 'user_id' => 2, 'start' => '08/16/2020 00:00', 'time_spent' => 1.0 )));
        
        $time  = time();
        $this->assertTrue($taskTimesheetModel->update(array('id' => 1, 'task_id' => 1, 'user_id' => 1, 'start' => $time)));

        $ts = $taskTimesheetModel->getById(1);
        $this->assertNotEmpty($ts);
        $this->assertEquals(1, $ts['id']);
        $this->assertEquals(1, $ts['task_id']);
        $this->assertEquals($time, $ts['start']);
    }

    public function testGetTaskTimesheetsByTaskId()
    {
        $projectModel = new ProjectModel($this->container);
        $taskCreationModel = new TaskCreationModel($this->container);
        $taskTimesheetModel = new TaskTimesheetModel($this->container);
        $userModel = new UserModel($this->container);

        $this->assertEquals(1, $projectModel->create(array('name' => 'test1')));
        $this->assertEquals(1, $taskCreationModel->create(array('title' => 'test 1', 'project_id' => 1, 'column_id' => 1, 'owner_id' => 1)));
        $this->assertEquals(2, $userModel->create(array('username' => 'user #1', 'password' => '123456', 'name' => 'User')));
        $this->assertEquals(1, $taskTimesheetModel->create(array( 'task_id' => 1, 'user_id' => 2, 'start' => time() - 100000, 'time_spent' => 1)));
        $this->assertEquals(2, $taskTimesheetModel->create(array( 'task_id' => 1, 'user_id' => 2, 'start' => time() - 80000, 'time_spent' => 1)));
        $this->assertEquals(3, $taskTimesheetModel->create(array( 'task_id' => 1, 'user_id' => 2, 'start' => time() - 60000 )));

        $results = $taskTimesheetModel->getAll(1);

        $this->assertNotEmpty($results);
        $this->assertCount(3, $results);
        
    }

    public function testGetTaskTimesheetsByTaskIdQuery()
    {
        $projectModel = new ProjectModel($this->container);
        $taskCreationModel = new TaskCreationModel($this->container);
        $taskTimesheetModel = new TaskTimesheetModel($this->container);
        $userModel = new UserModel($this->container);

        $this->assertEquals(1, $projectModel->create(array('name' => 'test1')));
        $this->assertEquals(1, $taskCreationModel->create(array('title' => 'test 1', 'project_id' => 1, 'column_id' => 1, 'owner_id' => 1)));
        $this->assertEquals(2, $userModel->create(array('username' => 'user #1', 'password' => '123456', 'name' => 'User')));
        $this->assertEquals(1, $taskTimesheetModel->create(array( 'task_id' => 1, 'user_id' => 2, 'start' => time() - 100000, 'time_spent' => 1)));
        $this->assertEquals(2, $taskTimesheetModel->create(array( 'task_id' => 1, 'user_id' => 2, 'start' => time() - 80000, 'time_spent' => 1)));
        $this->assertEquals(3, $taskTimesheetModel->create(array( 'task_id' => 1, 'user_id' => 2, 'start' => time() - 60000 )));

        $results = $taskTimesheetModel->getTaskQuery(1)->findAll();

        $this->assertNotEmpty($results);
        $this->assertCount(3, $results);
        
    }

    public function testGetTaskTimesheetsByUserIdQuery()
    {
        $projectModel = new ProjectModel($this->container);
        $taskCreationModel = new TaskCreationModel($this->container);
        $taskTimesheetModel = new TaskTimesheetModel($this->container);
        $userModel = new UserModel($this->container);

        $this->assertEquals(1, $projectModel->create(array('name' => 'test1')));
        $this->assertEquals(1, $taskCreationModel->create(array('title' => 'test 1', 'project_id' => 1, 'column_id' => 1, 'owner_id' => 1)));
        $this->assertEquals(2, $userModel->create(array('username' => 'user #1', 'password' => '123456', 'name' => 'User')));
        $this->assertEquals(1, $taskTimesheetModel->create(array( 'task_id' => 1, 'user_id' => 2, 'start' => time() - 100000, 'time_spent' => 1)));
        $this->assertEquals(2, $taskTimesheetModel->create(array( 'task_id' => 1, 'user_id' => 2, 'start' => time() - 80000, 'time_spent' => 1)));
        $this->assertEquals(3, $taskTimesheetModel->create(array( 'task_id' => 1, 'user_id' => 2, 'start' => time() - 60000 )));

        $results = $taskTimesheetModel->getUserQuery(2)->findAll();

        $this->assertNotEmpty($results);
        $this->assertCount(3, $results);
    }



    public function testGetTaskTimesheetsCalculateTaskTime()
    {
        $projectModel = new ProjectModel($this->container);
        $taskCreationModel = new TaskCreationModel($this->container);
        $taskTimesheetModel = new TaskTimesheetModel($this->container);
        $userModel = new UserModel($this->container);

        $this->assertEquals(1, $projectModel->create(array('name' => 'test1')));
        $this->assertEquals(1, $taskCreationModel->create(array('title' => 'test 1', 'project_id' => 1, 'column_id' => 1, 'owner_id' => 1)));
        $this->assertEquals(2, $userModel->create(array('username' => 'user #1', 'password' => '123456', 'name' => 'User')));
        $this->assertEquals(1, $taskTimesheetModel->create(array( 'task_id' => 1, 'user_id' => 2, 'start' => time() - 100000, 'time_spent' => 1)));
        $this->assertEquals(2, $taskTimesheetModel->create(array( 'task_id' => 1, 'user_id' => 2, 'start' => time() - 80000, 'time_spent' => 2.5)));    

        $this->assertEquals(3.5, $taskTimesheetModel->calculateTaskTime(1)['time_spent']);

    }


    public function testGetTaskTimesheetsTaskTimeUpdated()
    {
        $projectModel = new ProjectModel($this->container);
        $taskCreationModel = new TaskCreationModel($this->container);
        $taskTimesheetModel = new TaskTimesheetModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);
        $userModel = new UserModel($this->container);

        $this->assertEquals(1, $projectModel->create(array('name' => 'test1')));
        $this->assertEquals(1, $taskCreationModel->create(array('title' => 'test 1', 'project_id' => 1, 'column_id' => 1, 'owner_id' => 1)));
        $this->assertEquals(2, $userModel->create(array('username' => 'user #1', 'password' => '123456', 'name' => 'User')));
        $this->assertEquals(1, $taskTimesheetModel->create(array( 'task_id' => 1, 'user_id' => 2, 'start' => time() - 100000, 'time_spent' => 1)));
        $this->assertEquals(2, $taskTimesheetModel->create(array( 'task_id' => 1, 'user_id' => 2, 'start' => time() - 80000, 'time_spent' => 2.5)));    
        $taskTimesheetModel->updateTaskTimeTracking(1);
        $this->assertEquals(3.5, $taskFinderModel->getById(1)['time_spent']);

    }

}
