<?php
class ResultsComponent extends BaseComponent
{

	protected function beforeRender() {
		$this->getTemplate()->teams = Interlos::teams()
			->findAllWithScore();

		$this->getTemplate()->categories = Interlos::teams()->getCategoryNames();

		$this->getTemplate()->bonus = Interlos::score()
			->findAllBonus();
			
		$this->getTemplate()->penality = Interlos::score()
			->findAllPenality();

	}

}

