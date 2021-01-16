<?php

namespace FOL\Modules\CliModule;

use Dibi\DriverException;
use Dibi\Exception;
use Nette\Application\UI\Presenter;
use Tracy\Debugger;

/**
 * So far used only for generating dummy data.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class CliPresenter extends Presenter {

    private $year;
    private $teams;

    /**
     * @return void
     * @throws Exception
     */
    public function actionDefault(): void {
        $this->year = $this->getParameter('year', 1);
        $teams = $this->getParameter('teams', 10);
        $answers = $this->getParameter('answers', 100);

        if ($this->getParameter('ao', 0) == 1) {
            $this->loadTeams();
            $this->generateAnswers($answers, $this->getParameter('sleep', 0));
        } else {
            \dibi::query("DELETE FROM [group_state]");
            \dibi::query("DELETE FROM [team] WHERE name NOT LIKE '%test%'");
            \dibi::query("DELETE FROM [answer]");

            $this->generateTeams($teams);
            $this->generateAnswers($answers);
        }
        echo "\n---\n";
        echo "\n";
    }

    /**
     * @param $n
     * @return void
     * @throws Exception
     */
    private function generateTeams($n) {
        $words = ['world', 'super', 'class', 'team', 'of', 'brutus', 'cup', 'over', 'star', 'medieval', 'portal', 'quantum', 'physics', 'porn'];

        $used = [];
        $this->teams = [];
        for ($j = 0; $j < $n; ++$j) {
            do {
                $len = rand(1, 3);
                $teamwords = [];
                for ($i = 0; $i < $len; ++$i) {
                    $teamwords[] = $words[rand(0, count($words) - 1)];
                }
                $name = implode(' ', $teamwords);
            } while (isset($used[$name]));
            $used[$name] = true;

            \dibi::insert('team', [
                'name' => $name,
                'id_year' => $this->year,
                'email' => $name,
                'password' => '',
                'category' => 'open',
                'address' => 'adresa',
                'inserted' => \dibi::datetime(),
            ])->execute();
            $teamId = \dibi::insertId();

            $this->teams[$teamId] = new TeamData();
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    private function loadTeams() {
        $teams = \dibi::fetchAll('SELECT * FROM [view_team]');
        $this->teams = [];
        foreach ($teams as $team) {
            $this->teams[$team['id_team']] = new TeamData();
        }
    }

    /**
     * @param $n
     * @param int $sleep
     * @return void
     * @throws Exception
     */
    private function generateAnswers($n, $sleep = 0) {
        $tasks = \dibi::fetchAll('SELECT * FROM [view_task]');
        $teamIds = array_keys($this->teams);
        Debugger::timer();
        $dbTime = 0;
        $phpTime = 0;
        for ($j = 0; $j < $n; ++$j) {
            do {
                $team = $teamIds[rand(0, count($teamIds) - 1)];
                $task = $tasks[rand(0, count($tasks) - 1)];
            } while (isset($this->teams[$team]->corrects[$task->id_task]));
            switch ($task['answer_type']) {
                case 'string':
                    $suff = 'str';
                    break;
                case 'real':
                    $suff = 'real';
                    break;
                default:
                    $suff = 'int';
            }

            if (rand(0, 1) == 0) { // correct
                $answer = $task['answer_' . $suff];
                $this->teams[$team]->corrects[$task->id_task] = true;
            } else {
                $answer = $task['answer_' . $suff] * rand(2, 5);
            }
            $phpTime += Debugger::timer();
            do {
                $exp = false;
                try {
                    \dibi::insert('answer', [
                        'id_team' => $team,
                        'id_task' => $task['id_task'],
                        'answer_' . $suff => $answer,
                        'inserted' => \dibi::datetime(),//TODO
                    ])->execute();
                } catch (DriverException $e) {
                    $exp = true;
                }
                $dbTime += Debugger::timer();
                if ($sleep) {
                    usleep($sleep * 1000);
                }
            } while ($exp);
        }

        echo "Inserted $n answers in $dbTime s + $phpTime s (DB + PHP).";
    }

}

class TeamData extends \stdClass {

    public $corrects = [];

}
