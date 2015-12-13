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
            if(!is_numeric($year)){
                $this->error();
            }
            $this->setView('a' . $year . 'tasks.' . $this->lang);
        }
        
        public function renderDetail($year) {
            if(!is_numeric($year)){
                $this->error();
            }
            $this->setView('a' . $year . 'detail.' . $this->lang);
        }
}