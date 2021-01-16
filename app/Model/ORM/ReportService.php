<?php

namespace FOL\Model\ORM;

use DateTime;
use dibi;
use Dibi\Connection;
use Dibi\DataSource;
use Dibi\Exception;
use Nette\Database\Explorer;
use Nette\DI\Container;
use Nette\Http\Request;
use Nette\Security\User;
use Nette\Utils\Image;

class ReportService extends AbstractService {

    protected Container $context;

    private Request $httpRequest;

    private User $user;

    public function __construct(Connection $connection, Explorer $explorer, Container $container, Request $httpRequest, User $user) {
        parent::__construct($explorer, $connection);
        $this->context = $container;
        $this->httpRequest = $httpRequest;
        $this->user = $user;
    }

    /**
     * @param $id
     * @return void
     * @throws Exception
     */
    public function find($id) {
        $this->getDibiConnection()->query("SELECT * FROM [report] WHERE [id_report] = %i", $id)->fetch();
    }

    /**
     * @return DataSource
     * @throws Exception
     */
    public function findAll(): DataSource {
        return $this->getDibiConnection()->dataSource("SELECT * FROM [report]");
    }

    /**
     * @param $year
     * @param null $lang
     * @param bool $published
     * @return DataSource
     * @throws Exception
     */
    public function findByYear($year, $lang = null, $published = true): DataSource {
        $res = $this->findAll()->where("DATE_FORMAT([year_date],'%Y%m')=%i", $year);
        if (!is_null($lang)) {
            $res = $res->where("[lang]=%s", $lang);
        }
        if ($published) {
            $res = $res->where("[published] IS NOT NULL");
        }
        return $res;
    }

    /**
     * @param $id_report
     * @return DataSource
     * @throws Exception
     */
    public function findImages($id_report): DataSource {
        return $this->getDibiConnection()->dataSource("SELECT * FROM [report_image]")
            ->where("[id_report]=%i", $id_report);
    }

    /**
     * @param $team
     * @param $id_team
     * @param $header
     * @param $text
     * @param $lang
     * @param $year_rank
     * @param $year_date
     * @param $images
     * @return void
     * @throws Exception
     */
    public function insert($team, $id_team, $header, $text, $lang, $year_rank, $year_date, $images) {
        $connection = $this->getDibiConnection();

        $now = new DateTime();
        $id_report = $connection->insert("report", [
            'team' => $team,
            'id_team' => $id_team,
            'header' => $header,
            'text' => $text,
            'lang' => $lang,
            'year_rank' => $year_rank,
            'year_date' => $year_date,
            'inserted' => $now,
            'publisher' => $this->user->id,
            'published' => $now,
        ])->execute(Dibi::IDENTIFIER);

        foreach ($images as $file) {
            $filename = sha1_file($file['image']->getTemporaryFile());
            $image = $file['image']->toImage();
            $image->resize(null, min([$this->context->parameters['reports']['imageHeight'], $image->getHeight()]));
            $image->save($this->getImagePath($filename), $this->context->parameters['reports']['jpgQuality'], Image::JPEG);
            $image->resize(null, min([$this->context->parameters['reports']['thumbnailHeight'], $image->getHeight()]));
            $image->save($this->getThumbnailPath($filename), $this->context->parameters['reports']['jpgQuality'], Image::JPEG);
            $connection->insert("report_image", [
                'id_report' => $id_report,
                'image_hash' => $filename,
                'caption' => $file['caption'],
            ])->execute();
        }
    }

    public function getImageUrl($filename) {
        return $this->httpRequest->url->baseUrl . $this->context->parameters['reports']['imagePath'] . '/' . $filename . '.jpg';
    }

    public function getThumbnailUrl($filename) {
        return $this->httpRequest->url->baseUrl . $this->context->parameters['reports']['thumbnailPath'] . '/' . $filename . '.jpg';
    }

    private function getImagePath($filename) {
        return $this->context->parameters['wwwDir'] . $this->context->parameters['reports']['imagePath'] . '/' . $filename . '.jpg';
    }

    private function getThumbnailPath($filename) {
        return $this->context->parameters['wwwDir'] . $this->context->parameters['reports']['thumbnailPath'] . '/' . $filename . '.jpg';
    }

    protected function getTableName(): string {
        return 'report';
    }
}
