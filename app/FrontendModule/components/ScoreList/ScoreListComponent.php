<?php

class ScoreListComponent extends BaseComponent {

    protected function beforeRender() {
        parent::beforeRender();
        $this->getTemplate()->teams = Interlos::teams()
                ->findAllWithScore();
        $this->getTemplate()->score = Interlos::score()
                ->findAllTasks();
        $this->getTemplate()->skips = Interlos::score()
                ->findAllSkips();
        $this->getTemplate()->tasks = Interlos::tasks()
                ->findPossiblyAvailable();
        $this->getTemplate()->bonus = Interlos::score()
                ->findAllBonus();
        $this->getTemplate()->penality = Interlos::score()
                ->findAllPenality();
    }

}
