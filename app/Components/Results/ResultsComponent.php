<?php

namespace FOL\Components\Results;

use FOL\Model\FOF2021ScoreStrategy;
use FOL\Model\GameSetup;
use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\Models\ModelTeam;
use FOL\Model\ORM\Services\ServiceTask;
use FOL\Model\ORM\Services\ServiceTeam;
use FOL\Components\BaseComponent;
use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Nette\Security\User;
use Throwable;

final class ResultsComponent extends BaseComponent {

    private GameSetup $gameSetup;
    private ServiceTeam $serviceTeam;
    private ServiceTask $serviceTask;
    private Cache $cache;
    private FOF2021ScoreStrategy $scoreStrategy;
    private User $user;

    public function injectPrimary(
        GameSetup $gameSetup,
        ServiceTeam $serviceTeam,
        ServiceTask $serviceTask,
        Storage $storage,
        FOF2021ScoreStrategy $scoreStrategy,
        User $user
    ): void {
        $this->serviceTeam = $serviceTeam;
        $this->gameSetup = $gameSetup;
        $this->serviceTask = $serviceTask;
        $this->cache = new Cache($storage, self::class);
        $this->scoreStrategy = $scoreStrategy;
        $this->user = $user;
    }

    /**
     * @throws Throwable
     */
    public function render(): void {
        $isOrg = $this->user->isInRole('org');
        $this->template->teams = $this->cache->load('data', function (&$dep) {
            $dep[Cache::EXPIRATION] = '+120 second';
            $data = [];
            /** @var ModelTeam $team */
            foreach ($this->serviceTeam->getTable() as $team) {
                $bonus = $this->scoreStrategy->getBonusForTeam($team);
                $data[$team->id_team] = [
                    'model' => $team->toArray(),
                    'bonus' => $this->scoreStrategy->getBonusForTeam($team),
                    'score' => $bonus + $team->related('task_state')->sum('points'),
                ];
            }
            return $data;
        });
        $this->template->categories = ServiceTeam::getCategoryNames();

        $maxBonus = 0;
        $maxPoints = 0;
        /** @var ModelTask $task */
        foreach ($this->serviceTask->getTable() as $task) {
            $maxPoints += $task->points;
            $maxBonus++;
        }
        $maxPoints += $maxBonus;
        $this->template->maxPoints = $maxPoints;
        $this->template->maxBonus = $maxBonus;
        $this->template->visible = $isOrg || $this->gameSetup->isResultsVisible();
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'results.latte');
        parent::render();
    }
}

