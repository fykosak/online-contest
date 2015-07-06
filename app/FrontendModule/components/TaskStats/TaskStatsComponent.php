<?php

use App\Model\Interlos;

class TaskStatsComponent extends BaseComponent
{

	public function beforeRender() {
		$this->getTemplate()->tasks = Interlos::tasks()->findAllStats();
	}

}
