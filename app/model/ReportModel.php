<?php

namespace App\Model;

use Nette\DI\Container,
 Nette\Http\Request,
 Nette\Security\User;

class ReportModel extends AbstractModel {
        
        /** @var \Nette\DI\Container */
        private $context;
        
        /** @var \Nette\Http\Request */
        private $httpRequest;
        
        /** @var \Nette\Security\User */
        private $user;
    
        public function __construct(\DibiConnection $connection, Container $context, Request $httpRequest, User $user) {
            parent::__construct($connection);
            $this->context = $context;
            $this->httpRequest = $httpRequest;
            $this->user = $user;
        }

        public function find($id) {
		$this->checkEmptiness($id, "id");
		$this->getConnection()->query("SELECT * FROM [report] WHERE [id_report] = %i", $id)->fetch();
	}

	/** @return \DibiDataSource */
	public function findAll() {
		return $this->getConnection()->dataSource("SELECT * FROM [report]");
	}
        
        /** @return \DibiDataSource */
        public function findByYear($year, $lang, $published=TRUE) {
            $res = $this->findAll()->where("DATE_FORMAT([year_date],'%Y%m')=%i", $year)
                    ->where("[lang]=%s", $lang);
            if($published){
                return $res->where("[published] IS NOT NULL");
            }
            return $res;
        }
        
        /** @return \DibiDataSource */
        public function findImages($id_report) {
            return $this->getConnection()->dataSource("SELECT * FROM [report_image]")
                    ->where("[id_report]=%i", $id_report);
        }

        public function insert($team, $id_team, $header, $text, $lang, $year_rank, $year_date, $images) {
            $connection = $this->getConnection();
            $fileBase = $this->context->parameters['wwwDir'].$this->context->parameters['reports']['imagePath'].'/';
            
            $now = new \DateTime();
            $id_report = $connection->insert("report", array(
                'team' => $team,
                'id_team' => $id_team,
                'header' => $header,
                'text' => $text,
                'lang' => $lang,
                'year_rank' => $year_rank,
                'year_date' => $year_date,
                'inserted' => $now,
                'publisher' => $this->user->id,
                'published' => $now
            ))->execute(\dibi::IDENTIFIER);
            
            foreach($images as $image){
                $filename=sha1_file($image['image']->getTemporaryFile());
                $image['image']->move($fileBase.$filename);
                $connection->insert("report_image", array(
                    'id_report' => $id_report,
                    'image_hash' => $filename,
                    'caption' => $image['caption']
                ))->execute();
            }
        }
        
        public function getImageUrl($filename){
            return $this->httpRequest->url->baseUrl.$this->context->parameters['reports']['imagePath'].'/'.$filename;
        }

}