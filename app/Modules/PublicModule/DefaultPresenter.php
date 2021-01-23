<?php

namespace FOL\Modules\PublicModule;

class DefaultPresenter extends BasePresenter {

    protected function startUp(): void {
        parent::startUp();
        if ($this->getAction() === 'chat') {
            $this->forward(':Game:Chat:default');
        }
    }



    public function renderDefault(): void {
        $this->setPageTitle(_('Mezinárodní soutež ve fyzice'));
        $this->template->year = $this->getCurrentYear();
        $this->changeViewByLang();
    }

    public function renderLastYears(): void {
        $this->setPageTitle(_('Minulé ročníky'));
        $this->changeViewByLang();
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
