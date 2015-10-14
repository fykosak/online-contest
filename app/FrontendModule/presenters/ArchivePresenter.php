<?php

namespace App\FrontendModule\Presenters;

class ArchivePresenter extends BasePresenter
{

	public function renderYear2009() {
		$this->setPageTitle("2009");
	}
        
//        protected function beforeRender() {
//            parent::beforeRender();
//            $this->changeViewByLang();
//        }
        
        public function renderTasks($year) {
            $this->setView('a' . $year . 'tasks.' . $this->lang);
        }

}