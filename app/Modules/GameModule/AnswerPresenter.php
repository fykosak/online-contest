<?php

namespace FOL\Modules\GameModule;

use FOL\Components\Rating\RatingComponent;
use FOL\Components\AnswerForm\AnswerFormComponent;
use FOL\Components\AnswerHistory\AnswerHistoryComponent;
use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\Services\ServiceTask;

class AnswerPresenter extends BasePresenter {

    /**
     * @persistent
     */
    public ?int $id = null;

    private ServiceTask $serviceTask;

    public function injectSecondary(ServiceTask $serviceTask): void {
        $this->serviceTask = $serviceTask;
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
