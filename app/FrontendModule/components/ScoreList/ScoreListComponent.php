<?php
class ScoreListComponent extends BaseComponent
{

	protected function startUp() {
		$this->getTemplate()->teams = Interlos::teams()
			->findAllWithScore()
			->fetchAll();
		$this->getTemplate()->score = Interlos::score()
			->findAllTasks()
			->fetchAssoc("id_team,id_task");
                $this->getTemplate()->skips = Interlos::score()
			->findAllSkips()
			->fetchAssoc("id_team,id_task");
		$this->getTemplate()->tasks = Interlos::tasks()
			->findPossiblyAvailable();
		$this->getTemplate()->bonus = Interlos::score()
			->findAllBonus()
			->fetchAssoc("id_team");
		$this->getTemplate()->penality = Interlos::score()
			->findAllPenality()
			->fetchAssoc("id_team");
	}

}
