<?php

namespace FOL\Components\AnswerHistory;

use Dibi\Exception;
use FOL\Model\ORM\AnswersService;
use FOL\Model\ORM\Models\ModelTeam;
use FOL\Model\ORM\TasksService;
use FOL\Components\BaseListComponent;
use Nette\DI\Container;

class AnswerHistoryComponent extends BaseListComponent {

    protected AnswersService $answersService;
    protected TasksService $tasksService;
    private ModelTeam $team;

    public function __construct(Container $container, ModelTeam $team) {
        parent::__construct($container);
        $this->team = $team;
    }

    public function injectPrimary(TasksService $tasksService, AnswersService $answersService): void {
        $this->tasksService = $tasksService;
        $this->answersService = $answersService;
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function beforeRender(): void {
        // Paginator
        $paginator = $this->getPaginator();
        $this->getSource()->applyLimit($paginator->itemsPerPage, $paginator->offset);
        // Load template
        $this->template->history = $this->getSource()->fetchAll();
        $this->template->correct = $this->answersService->findAllCorrect($this->team->id_team)->fetchPairs('id_answer', 'id_answer');
        $this->template->tasks = $this->tasksService->findAll()->fetchAssoc('id_task');
        $this->template->timeFormat = 'H:i:s';//_('__time'); // TODO i18n
    }

    public function render(): void {
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'answerHistory.latte');
        parent::render();
    }
}
