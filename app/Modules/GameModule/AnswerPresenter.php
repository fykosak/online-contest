<?php

namespace FOL\Modules\GameModule;

use Dibi\Exception;
use FOL\Components\Rating\RatingComponent;
use FOL\Model\ORM\AnswersService;
use FOL\Components\AnswerForm\AnswerFormComponent;
use FOL\Components\AnswerHistory\AnswerHistoryComponent;
use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\Services\ServiceTask;

class AnswerPresenter extends BasePresenter {

    /**
     * @persistent
     */
    public ?int $id = null;

    private AnswersService $answersService;
    private ServiceTask $serviceTask;

    public function injectSecondary(AnswersService $answersService, ServiceTask $serviceTask): void {
        $this->answersService = $answersService;
        $this->serviceTask = $serviceTask;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function actionHistory(): void {
        //has to be loaded in action due to pagination
        $this->getComponent('answerHistory')->setSource(
            $this->answersService->findAll()
                ->where('[id_team] = %i', $this->getLoggedTeam()->id_team)
                ->orderBy('inserted', 'DESC')
        );
        $this->getComponent('answerHistory')->setLimit(50);
    }

    public function renderDefault(): void {
        $this->setPageTitle(_('Odevzdat řešení'));
    }

    public function renderHistory(): void {
        $this->setPageTitle(_('Historie odpovědí'));
    }

    public function renderRating(): void {
        $this->setPageTitle(_('Rate this task'));
    }

    protected function createComponentAnswerForm(): AnswerFormComponent {
        return new AnswerFormComponent($this->getContext(), $this->getLoggedTeam());
    }

    protected function createComponentAnswerHistory(): AnswerHistoryComponent {
        return new AnswerHistoryComponent($this->getContext(), $this->getLoggedTeam());
    }

    private ?ModelTask $task;

    protected function getTask(): ?ModelTask {
        if (!isset($this->task)) {
            $this->task = $this->serviceTask->findByPrimary($this->id);
        }
        return $this->task;
    }

    protected function createComponentRating(): RatingComponent {
        return new RatingComponent($this->getContext(), $this->getTask(), $this->getLoggedTeam());
    }
}
