<?php

namespace App\FrontendModule\Presenters;

use Nette\Application\Responses\TextResponse;

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
        
        public function actionDetail($year) {
            if(!is_numeric($year)){
                $this->error();
            }
            $this->sendResponse(new TextResponse(readfile(__DIR__.'/../templates/Archive/a' . $year . 'detail.' . $this->lang . '.latte')));
        }
}