<?php

namespace App\Model;

use DateTime;
use dibi;
use Dibi\Connection;
use Dibi\DataSource;
use Nette\DI\Container;
use Nette\Http\Request;
use Nette\Security\User;
use Nette\Utils\Image;

class ReportModel extends AbstractModel {

    private Container $context;

    private Request $httpRequest;

    private User $user;

    public function __construct(Connection $connection, Container $context, Request $httpRequest, User $user) {
        parent::__construct($connection);
        $this->context = $context;
        $this->httpRequest = $httpRequest;
        $this->user = $user;
    }

    public function find($id) {
        $this->checkEmptiness($id, "id");
        $this->getConnection()->query("SELECT * FROM [report] WHERE [id_report] = %i", $id)->fetch();
    }

    public function findAll(): DataSource {
        return $this->getConnection()->dataSource("SELECT * FROM [report]");
    }

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

    public function findImages($id_report): DataSource {
        return $this->getConnection()->dataSource("SELECT * FROM [report_image]")
            ->where("[id_report]=%i", $id_report);
    }

    public function insert($team, $id_team, $header, $text, $lang, $year_rank, $year_date, $images) {
        $connection = $this->getConnection();

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
        ])->execute(dibi::IDENTIFIER);

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
}
