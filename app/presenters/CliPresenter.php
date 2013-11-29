<?php

/**
 * So far used only for generating dummy data.
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class CliPresenter extends Presenter {

    private $year;
    private $teams;

    public function actionDefault() {
        dibi::query("DELETE FROM [team] WHERE name NOT LIKE '%test%'");
        dibi::query("DELETE FROM [answer]");

        $this->year = $this->getParam('year', 1);
        $teams = $this->getParam('teams', 10);
        $answers = $this->getParam('answers', 100);

        $this->generateTeams($teams);
        $this->generateAnswers($answers);
        echo "AHOJ";
        echo "\n";
    }

    protected function createTemplate() {
        return null;
    }

    private function generateTeams($n) {
        $words = array('world', 'super', 'class', 'team', 'of', 'brutus', 'cup', 'over', 'star', 'medieval', 'portal', 'quantum', 'physics', 'porn');

        $used = array();
        $this->teams = array();
        for ($j = 0; $j < $n; ++$j) {
            do {
                $len = rand(1, 3);
                $teamwords = array();
                for ($i = 0; $i < $len; ++$i) {
                    $teamwords[] = $words[rand(0, count($words) - 1)];
                }
                $name = implode(' ', $teamwords);
            } while (isset($used[$name]));
            $used[$name] = true;
            dibi::insert('team', array(
                'name' => $name,
                'id_year' => $this->year,
                'email' => $name,
                'password' => '',
                'category' => 'open',
                'address' => 'adresa',
                'inserted' => dibi::datetime()
            ))->execute();

            $teamId = dibi::insertId();

            $this->teams[$teamId] = new TeamData();
        }
    }

    private function generateAnswers($n) {
        $tasks = dibi::fetchAll('SELECT * FROM [view_task]');
        $teamIds = array_keys($this->teams);

        for ($j = 0; $j < $n; ++$j) {
            do {
                $team = $teamIds[rand(0, count($teamIds) - 1)];
                $task = $tasks[rand(0, count($tasks) - 1)];
            } while (isset($this->teams[$team]->corrects[$task->id_task]));

            $suff = ($task['answer_type'] == 'string') ? 'str' :
                    ($task['answer_type'] == 'real') ? 'real' : 'int';

            if (rand(0, 1) == 0) { // correct
                $answer = $task['answer_' . $suff];
                $this->teams[$team]->corrects[$task->id_task] = true;
            } else {
                $answer = $task['answer_' . $suff] * rand(2, 5);
            }

            dibi::insert('answer', array(
                'id_team' => $team,
                'id_task' => $task['id_task'],
                'answer_' . $suff => $answer,
                'inserted' => dibi::datetime(),
            ))->execute();
        }
    }

}

class TeamData extends stdClass {

    public $corrects = array();

}
