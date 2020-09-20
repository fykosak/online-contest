<?php

namespace App\FrontendModule\Presenters;

use App\Model\ReportModel;

class ArchivePresenter extends BasePresenter {

    protected ReportModel $reportModel;

    public function injectSecondary(ReportModel $reportModel): void {
        $this->reportModel = $reportModel;
    }

    public function renderYear2009(): void {
        $this->setPageTitle("2009");
    }

//        protected function beforeRender() {
//            parent::beforeRender();
//            $this->changeViewByLang();
//        }

    public function renderTasks($year): void {
        if (!is_numeric($year)) {
            $this->error();
        }
        $this->setPageTitle(_("Statistika úkolů"));
        $this->setView('a' . $year . 'tasks.' . $this->lang);
    }

    public function actionDetail($year): void {
        if (!is_numeric($year)) {
            $this->error();
        }

        $success = @readfile(__DIR__ . '/../templates/Archive/a' . $year . 'detail.' . $this->lang . '.latte');
        if (!$success) {
            $this->error();
        }
        $this->terminate();
    }

    public function renderReports($year): void {
        if (!is_numeric($year)) {
            $this->error();
        }
        if ($this->lang == 'cs') {
            $reportData = $this->reportModel->findByYear($year, null, true)->orderBy("lang");
        } else {
            $reportData = $this->reportModel->findByYear($year, $this->lang, true);
        }
        $this->template->reports = [];
        foreach ($reportData as $report) {
            $imageData = $this->reportModel->findImages($report['id_report']);
            $images = [];
            foreach ($imageData as $image) {
                $images[] = [
                    'caption' => $image['caption'],
                    'img-url' => $this->reportModel->getImageUrl($image['image_hash']),
                    'thumb-url' => $this->reportModel->getThumbnailUrl($image['image_hash']),
                ];
            }
            $this->template->reports[] = [
                'header' => $report['header'],
                'team' => $report['team'],
                'text' => $report['text'],
                'images' => $images,
            ];
        }
        $this->setPageTitle(_("Ohlasy účastníků"));
    }
}
