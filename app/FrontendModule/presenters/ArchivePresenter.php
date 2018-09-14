<?php

namespace App\FrontendModule\Presenters;

use Nette\Application\Responses\TextResponse;

class ArchivePresenter extends BasePresenter
{
    
        /** @var \App\Model\ReportModel @inject*/
        public $reportModel;

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
            $this->setPageTitle(_("Statistika úkolů"));
            $this->setView('a' . $year . 'tasks.' . $this->lang);
        }
        
        public function actionDetail($year) {
            if(!is_numeric($year)){
                $this->error();
            }
            
            $success = @readfile(__DIR__.'/../templates/Archive/a' . $year . 'detail.' . $this->lang . '.latte');
            if(!$success){
                $this->error();
            }
            $this->terminate();
        }
        
        public function renderReports($year) {
            if(!is_numeric($year)){
                $this->error();
            }
            if($this->lang == 'cs') {
                $reportData = $this->reportModel->findByYear($year, null, TRUE)->orderBy("lang");
            }
            else {
                $reportData = $this->reportModel->findByYear($year, $this->lang, TRUE);
            }
            $this->template->reports = array();
            foreach($reportData as $report){
                $imageData = $this->reportModel->findImages($report['id_report']);
                $images = array();
                foreach($imageData as $image){
                    $images[] = array(
                        'caption' => $image['caption'],
                        'img-url' => $this->reportModel->getImageUrl($image['image_hash']),
                        'thumb-url' => $this->reportModel->getThumbnailUrl($image['image_hash'])
                    );
                }
                $this->template->reports[] = array(
                    'header' => $report['header'],
                    'team' => $report['team'],
                    'text' => $report['text'],
                    'images' => $images
                );
            }
            $this->setPageTitle(_("Ohlasy účastníků"));
        }
}