<?php

use App\Model\AnswersModel,
    App\Model\TeamsModel,
    App\Model\TasksModel,
    Nette\ComponentModel\IContainer;

class AnswerStatsComponent extends BaseListComponent {
    
    /** @var AnswersModel */
    private $answersModel;
    
    /** @var TasksModel */
    private $tasksModel;
    
    /** @var TeamsModel */
    private $teamsModel;

    public function __construct(AnswersModel $answersModel, TeamsModel $teamsModel, TasksModel $tasksModel, IContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);
        $this->answersModel = $answersModel;
        $this->teamsModel = $teamsModel;
        $this->tasksModel = $tasksModel;
    }
    
    protected function beforeRender() {
        $answers = $this->answersModel->findAll()->fetchAll();
        $tasks = $this->tasksModel->findAll()->fetchAssoc('id_task');
        $teams = $this->teamsModel->findAll()->fetchAssoc('id_team');
        $taskData = array();
        
        foreach ($answers as $answer) {
            $taskNo = $tasks[$answer->id_task]['id_group'].'-'.$tasks[$answer->id_task]['number'];
            
            if(isset($answer->answer_int)) {
                $trueValue = $answer->answer_int;
                $correctValue = $tasks[$answer->id_task]['answer_int'];
                $value = $trueValue-$correctValue;
            }
            else {
                $trueValue = $answer->answer_real;
                $correctValue = $tasks[$answer->id_task]['answer_real'];
                $tolerance = $tasks[$answer->id_task]['real_tolerance'];
                $value = log(1.0+abs($trueValue-$correctValue)/$tolerance, 2.0);
            }

            $taskData[$taskNo]['answers'][]=array(
                'value' => $value,
                'trueValue'=> $trueValue,
                'team' => $teams[$answer->id_team]['name'],
                'inserted' => $answer->inserted->getTimestamp()
            );
        }
        
        foreach ($taskData as $taskNo => $task) {
            $count = count($task['answers']);
            
            $sum = 0;
            foreach ($task['answers'] as $answer) {
                $sum += $answer['value'];
            }
            $mu = $sum/$count;
            
            $sum = 0;
            foreach ($task['answers'] as $answer) {
                $sum += ($answer['value']-$mu)*($answer['value']-$mu);
            }
            $sigma = sqrt($sum/($count-1));
            
            $taskData[$taskNo]['mu']=$mu;
            $taskData[$taskNo]['sigma']=$sigma;
        }
        $this->getTemplate()->taskData = $taskData;
    }
}