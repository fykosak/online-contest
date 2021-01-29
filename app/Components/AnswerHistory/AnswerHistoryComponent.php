<?php

namespace FOL\Components\AnswerHistory;

use FOL\Components\BaseComponent;
use FOL\Model\ORM\AnswersService;
use FOL\Model\ORM\Models\ModelTeam;
use FOL\Model\ORM\Services\ServiceAnswer;
use FOL\Model\ORM\Services\ServiceTask;
use FOL\Model\ORM\TasksService;
use Nette\DI\Container;

class AnswerHistoryComponent extends BaseComponent {

    protected AnswersService $answersService;
    protected TasksService $tasksService;
    private ModelTeam $team;
    private ServiceAnswer $serviceAnswer;

    public function __construct(Container $container, ModelTeam $team) {
        parent::__construct($container);
        $this->team = $team;
    }

    public function injectPrimary(TasksService $tasksService, AnswersService $answersService, ServiceAnswer $serviceAnswer): void {
        $this->tasksService = $tasksService;
        $this->answersService = $answersService;
        $this->serviceAnswer = $serviceAnswer;
    }

    protected function beforeRender(): void {
        // Load template
        $this->template->history = $this->serviceAnswer->getTable()
            ->where('id_team', $this->team->id_team)
            ->order('inserted DESC');
        $this->template->correct = $this->answersService->findAllCorrect($this->team->id_team)->fetchPairs('id_answer', 'id_answer');
        $this->template->timeFormat = 'H:i:s';//_('__time'); // TODO i18n
    }

    public function render(): void {
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'answerHistory.latte');
        parent::render();
    }
}
