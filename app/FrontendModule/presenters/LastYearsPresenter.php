<?php
class Frontend_ArchivePresenter extends Frontend_BasePresenter
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