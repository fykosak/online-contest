<?php
class TaskStatsComponent extends BaseComponent
{

	public function beforeRender() {
		$this->getTemplate()->tasks = Interlos::tasks()->findAllStats();
	}

}
