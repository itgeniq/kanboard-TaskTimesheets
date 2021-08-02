<?php

namespace Kanboard\Plugin\TaskTimesheets;

use Kanboard\Core\Plugin\Base;
use Kanboard\Core\Translator;
use Kanboard\Plugin\TaskTimesheets\Model\TaskTimesheetModel;

class Plugin extends Base
{
    public function initialize()
    {
        
        $this->template->setTemplateOverride('task/time_tracking_details', 'taskTimesheets:task/time_tracking_details');
        $this->template->setTemplateOverride('user_view/timesheet', 'taskTimesheets:user/timesheet');
        $this->helper->register('taskTimesheetHelper', '\Kanboard\Plugin\TaskTimesheets\Helper\TaskTimesheetHelper');
        $this->template->hook->attach('template:task:show:before-subtasks', 'taskTimesheets:task/show');
        // $this->hook->on('template:task:sidebar:actions', function($data){
        //   return $data;
        // });
        $this->api->getProcedureHandler()->withCallback('test', function() {
            return ['this' => 'foobar'];
        });
        
    }

    public function getClasses()
    {
        return array(
            'Plugin\TaskTimesheets\Model' => array(
                'TaskTimesheetModel'
            ),
            // 'Plugin\Timetrackingeditor\Filter' => array(
            //   'SubtaskFilter',
            //   'SubtaskTaskFilter',
            //   'SubtaskTitleFilter'
            // ),
            // 'Plugin\Timetrackingeditor\Console' => array(
            //   'SubtaskTimeTrackingExportCommand',
            //   'AllSubtaskTimeTrackingExportCommand'
            // ),
            'Plugin\TaskTimesheets\Controller' => array(
              'TaskTimesheetController'
            ),
            // 'Plugin\Timetrackingeditor\Export' => array(
            //   'SubtaskTimeTrackingExport'
            // ),
            'Plugin\TaskTimesheets\Validator' => array(
              'TaskTimesheetValidator'
            ),
            // 'Plugin\Timetrackingeditor\Formatter' => array(
            //   'SubtaskAutoCompleteFormatter'
            // ),
            'Plugin\TaskTimesheets\Helper' => array(
                'TaskTimesheetHelper'
            ),
        );
    }

    public function onStartup()
    {
        Translator::load($this->languageModel->getCurrentLanguage(), __DIR__.'/Locale');
    }

    public function getPluginName()
    {
        return 'TaskTimesheets';
    }

    public function getPluginDescription()
    {
        return t('Adds Timesheet functionality to a Kanboard task.');
    }

    public function getPluginAuthor()
    {
        return 'ITgeniq';
    }

    public function getPluginVersion()
    {
        return '1.1.0';
    }

    public function getPluginHomepage()
    {
        return 'https://github.com/itgeniq/kanboard-TaskTimesheet';
    }
}

