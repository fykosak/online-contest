<?php

namespace App\FrontendModule\Presenters;

use Dibi\Exception;
use FOL\Model\ORM\ReportService;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;

class ArchivePresenter extends BasePresenter {

    protected ReportService $reportModel;

    public function injectSecondary(ReportService $reportModel): void {
        $this->reportModel = $reportModel;
    }

    public function renderYear2009(): void {
        $this->setPageTitle("2009");
    }

    public function renderTasks(int $year): void {
        $this->setPageTitle(_("Statistika úkolů"));
        $this->setView('a' . $year . 'tasks.' . $this->lang);
    }

    /**
     * @param int $year
     * @return void
     * @throws AbortException
     * @throws BadRequestException
     */
    public function actionDetail(int $year): void {
        $success = @readfile(__DIR__ . '/../templates/Archive/a' . $year . 'detail.' . $this->lang . '.latte');
        if (!$success) {
            $this->error();
        }
        $this->terminate();
    }

    /**
     * @param int $year
     * @return void
     * @throws Exception
     */
    public function renderReports(int $year): void {
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
