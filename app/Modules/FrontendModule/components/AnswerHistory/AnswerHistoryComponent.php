<?php

use FOL\Model\ORM\AnswersService;
use FOL\Model\ORM\TasksService;

class AnswerHistoryComponent extends BaseListComponent {

    protected AnswersService $answersService;
    protected TasksService $tasksService;

    public function injectPrimary(TasksService $tasksService, AnswersService $answersService): void {
        $this->tasksService = $tasksService;
        $this->answersService = $answersService;
    }

    /**
     * @return void
     * @throws \Dibi\Exception
     */
    protected function beforeRender(): void {
        // Paginator
        $paginator = $this->getPaginator();
        $this->getSource()->applyLimit($paginator->itemsPerPage, $paginator->offset);
        // Load template
        $id_team = $this->getPresenter()->getLoggedTeam()->id_team;
        $this->getTemplate()->history = $this->getSource()->fetchAll();
        $this->getTemplate()->correct = $this->answersService->findAllCorrect($id_team)->fetchPairs("id_answer", "id_answer");
        $this->getTemplate()->tasks = $this->tasksService->findAll()->fetchAssoc("id_task");
    }

    public function render(): void {
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'answerHistory.latte');
        parent::render();
    }

}
