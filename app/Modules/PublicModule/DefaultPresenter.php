<?php

namespace FOL\Modules\PublicModule;

class DefaultPresenter extends BasePresenter {

    protected function startUp(): void {
        parent::startUp();
        switch ($this->getAction()) {
            case 'lastYears':
            case 'default':
                $this->redirect(':Game:Auth:login');
            case 'chat':
                $this->redirect(':Game:Chat:default');
        }
    }

    public function renderRules(): void {
        $this->setPageTitle(_('Pravidla'));
        $this->changeViewByLang();
    }

    public function renderFaq(): void {
        $this->setPageTitle(_('FAQ'));
        $this->changeViewByLang();
    }

    public function renderHowto(): void {
        $this->setPageTitle(_('Rychlý grafický návod ke hře'));
        $this->changeViewByLang();
    }

    public function renderTaskExamples(): void {
        $this->setPageTitle(_('Rozcvička'));
    }

    public function renderOtherEvents(): void {
        $this->setPageTitle(_('Další akce'));
        $this->changeViewByLang();
    }
}
