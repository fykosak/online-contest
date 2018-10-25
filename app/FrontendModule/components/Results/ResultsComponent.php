<?php

use App\Model\Interlos;

class ResultsComponent extends BaseComponent
{
        private $display;
    
        public function render($display = 'all') {
            $this->display = $display;
            parent::render();
        }

	protected function beforeRender() {
                $this->getTemplate()->display = $this->display;
            
		$this->getTemplate()->teams = Interlos::teams()
			->findAllWithScore();
                
                $competitors = Interlos::competitors()->findAll();
                $teamCountries = [];
                foreach($competitors as $competitor) {
                    if(!array_key_exists($competitor->id_team, $teamCountries)) {
                        $teamCountries[$competitor->id_team] = [];
                    }
                    $teamCountries[$competitor->id_team][] = $competitor->country_iso;
                }
                $this->getTemplate()->teamCountries = $teamCountries;

		$this->getTemplate()->categories = Interlos::teams()->getCategoryNames();

		$this->getTemplate()->bonus = Interlos::score()
			->findAllBonus();
			
		$this->getTemplate()->penality = Interlos::score()
			->findAllPenality();

	}

}

