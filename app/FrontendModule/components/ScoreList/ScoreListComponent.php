<?php

use App\Model\Interlos;

class ScoreListComponent extends BaseComponent {

    protected function beforeRender(): void {
        parent::beforeRender();
        $this->getTemplate()->teams = Interlos::teams()
            ->findAllWithScore();
        $this->getTemplate()->score = Interlos::score()
            ->findAllTasks();
        $this->getTemplate()->tasks = Interlos::tasks()
            ->findPossiblyAvailable();
        $this->getTemplate()->bonus = Interlos::score()
            ->findAllBonus();
        $this->getTemplate()->penality = Interlos::score()
            ->findAllPenality();
        $this->template->lang = $this->presenter->lang;
        $this->getTemplate()->categories = Interlos::teams()->getCategoryNames();
    }

}
