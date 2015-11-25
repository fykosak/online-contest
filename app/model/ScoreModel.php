<?php

namespace App\Model;

use Nette;

class ScoreModel extends AbstractModel {
	public function find($id) {
		throw new Nette\NotSupportedException();
	}


	public function findAll() {
		throw new Nette\NotSupportedException();
	}

	/** @return \DibiDataSource */
	public function findAllBonus() {
		return $this->getConnection()->dataSource("SELECT * FROM [tmp_bonus]");
	}

	/** @return \DibiDataSource */
	public function findAllTasks() {
		return $this->getConnection()->dataSource("SELECT * FROM [tmp_task_result]");
	}

	/** @return \DibiDataSource */
	public function findAllPenality() {
		return $this->getConnection()->dataSource("SELECT * FROM [tmp_penality]");
	}
        
        /** @return \DibiDataSource */
	public function findAllSkips() {
		return $this->getConnection()->dataSource("SELECT * FROM [task_state] WHERE skipped = 1");
	}
        
        public function updateAfterSkip($teamId) {
            $this->getConnection()->query("UPDATE [team] SET score = score-1 WHERE id_team = %i", $teamId);
        }
        
        public function updateAfterCancel($task) {
            //TODO
        }
        
        public function updateAfterInsert($teamId, $task) {
            try{
                $group = Interlos::groups()->find($task->id_group);
                $hurry = ($group->allow_zeroes == 1)? true : false; //dle SQL id_group=2,3,4
            
                $score = $this->getSingleTaskScore($teamId, $task, $group);
            
                if($hurry) {
                    $count = $this->getConnection()->query("SELECT task_counter FROM [group_state] WHERE %and", [
                        array('id_team = %i', $teamId),
                        array('id_group = %i', $task->id_group),
                    ])->fetch();
                    if($count == 5) {//TODO
                        $groupTasks = Interlos::tasks()->findAll()->where("[id_group] = %i", $task->id_group)->fetchAll();
                        foreach ($groupTasks as $groupTask) {
                            $score += $this->getSingleTaskScore($teamId, $groupTask, $group);
                        }
                    }
                }
            
                $this->getConnection()->query("UPDATE [team] SET score = score + %i", $score,"WHERE id_team = %i", $teamId);
            }
            catch(Exception $e) {
                Debugger::log($e);
            }
        }
        
        private function getSingleTaskScore($teamId, $task, $group) {
            $answerCount = $this->getConnection()->query("SELECT COUNT(*) FROM [answer] WHERE %and", [
                array('id_team = %i', $teamId),
                array('id_task = %i', $task->id_task),
            ])->fetch();
            
            return $this->getPointCount($task->points, $answerCount-1, $group->allow_zeroes);
        }
        
        private function getPointCount($maxPoints, $wrongTries, $allowZeroes) {
            $score = 0;
            
            if($maxPoints >= 4) {
                switch ($wrongTries) {
                    case 0:
                        $score = $maxPoints;
                        break;
                    case 1:
                        $score = ceil(0.6*$maxPoints);
                        break;
                    case 2:
                        $score = ceil(0.4*$maxPoints);
                        break;
                    case 3:
                        $score = ceil(0.2*$maxPoints);
                        break;
                    default:
                        $score = 0;
                        break;
                }
            }
            else if($maxPoints == 0) {
                return 0;
            }
            else {
                $score = $maxPoints - $wrongTries;
            }
            
            return ($allowZeroes)?max(0,$score):max(1,$score);
        }
}