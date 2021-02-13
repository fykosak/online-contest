<?php

namespace FOL\Modules\GameModule;

use FOL\Components\Rating\RatingComponent;
use FOL\Components\AnswerForm\AnswerFormComponent;
use FOL\Components\AnswerHistory\AnswerHistoryComponent;
use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\Services\ServiceTask;
use Nette\Application\AbortException;
use Nette\InvalidStateException;

class AnswerPresenter extends BasePresenter {

    /**
     * @persistent
     */
    public ?int $id = null;

    private ServiceTask $serviceTask;

    private ?ModelTask $task;

    public function injectSecondary(ServiceTask $serviceTask): void {
        $this->serviceTask = $serviceTask;
    }

    /**
     * @throws AbortException
     */
    public function actionDefault(): void {
        if ($this->gameSetup->isGameEnd()) {
            $this->flashMessage(_('Čas vypršel.'), 'danger');
            $this->redirect(':Game:Task:default');
        } elseif (!$this->gameSetup->isGameStarted()) {
            $this->flashMessage(_('Hra ještě nezačala.'), 'danger');
            $this->redirect(':Game:Task:default');
        }
    }

    public function renderDefault(): void {
        $this->setPageTitle(sprintf(_('Odevzdat řešení "%s"'), $this->getTask()->getLabel($this->lang)));
    }

    public function renderHistory(): void {
        $this->setPageTitle(_('Historie odpovědí'));
    }

    public function renderRating(): void {
        $this->setPageTitle(sprintf(_('Rate the task "%s"'), $this->getTask()->getLabel($this->lang)));
    }

    protected function createComponentAnswerForm(): AnswerFormComponent {
        return new AnswerFormComponent($this->getContext(), $this->getLoggedTeam(), $this->getTask());
    }

    protected function getTask(): ModelTask {
        if (!isset($this->task)) {
            $this->task = $this->serviceTask->findByPrimary($this->id);
        }
        return $this->task;
    }

    protected function createComponentAnswerHistory(): AnswerHistoryComponent {
        return new AnswerHistoryComponent($this->getContext(), $this->getLoggedTeam(), $this->lang);
    }

    protected function createComponentRating(): RatingComponent {
        return new RatingComponent($this->getContext(), $this->getTask(), $this->getLoggedTeam());
    }
}
