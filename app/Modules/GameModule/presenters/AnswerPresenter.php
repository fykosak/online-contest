<?php

namespace FOL\Modules\GameModule\Presenters;

use Dibi\Exception;
use FOL\Components\RatingComponent;
use FOL\Components\SingleAnswerComponent;
use FOL\Model\ORM\AnswersService;
use Nette\Application\BadRequestException;

class AnswerPresenter extends BasePresenter {
    /**
     * @var int $id
     * @persistent
     */
    public $id;

    private AnswersService $answersService;

    public function injectSecondary(AnswersService $answersService): void {
        $this->answersService = $answersService;
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

    protected function createComponentAnswerForm(): \AnswerFormComponent {
        return new \AnswerFormComponent($this->getContext(), $this->getLoggedTeam()->id_team);
    }

    protected function createComponentAnswerHistory(): \AnswerHistoryComponent {
        return new \AnswerHistoryComponent($this->getContext());
    }

    protected function createComponentRating(): RatingComponent {
        return new RatingComponent($this->getContext(), $this->id, $this->getLoggedTeam());
    }
}
