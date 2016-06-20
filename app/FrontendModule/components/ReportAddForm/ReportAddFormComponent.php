<?php

use Nette\Application\UI\Form,
    Nette\ComponentModel\IContainer,
    App\Model\ReportModel,
    App\Model\Authentication\OrgAuthenticator;

class ReportAddFormComponent extends BaseComponent
{
    /** @var ReportModel */
    private $reportModel;

    public function __construct(ReportModel $reportModel, IContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);
        $this->reportModel = $reportModel;
    }


    public function formSucceeded(Form $form) {
        if(!$this->getPresenter()->user->isAllowed('report', 'add')) {
            $this->getPresenter()->error('Nemáte oprávnění pro nahrání reportu.', Nette\Http\Response::S403_FORBIDDEN);
        }
        
        $values = $form->getValues();
        
        $images=array();
        foreach ($values['images'] as $image){
            if($image->isOk()){
                $images[] = array('image' => $image, 'caption' => null);
            }
        }
        
        $this->reportModel->insert($values['team'], $values['id_team'], $values['header'], 
                $values['text'], $values['lang'], $values['year_rank'], $values['year_date'], $images);
        $this->getPresenter()->flashMessage(_("Děkujeme za vložení reportu"), "info");
        $this->getPresenter()->redirect('Org:report');
    }
    
    protected function createComponentForm($name) {
        $form = new BaseForm($this, $name);
        
        $form->addText("year_rank", "Ročník")
                ->setType('number')
                ->addRule(Form::MIN, "Ročník musí být větší než %d.",1)
                ->setRequired("Ročník musí být vyplněn.");
        $form->addText("year_date", "Datum konání")
                ->setType('date')
                ->addRule(Form::PATTERN, "Datum musí mít podobu YYYY-mm-dd.", "[0-9]{4}-[0-9]{2}-[0-9]{2}")
                ->setRequired("Ročník musí být vyplněn.");
        $form->addText("id_team", "FKSDB ID týmu.")
                ->setType('number')
                ->addRule(Form::MIN, "ID týmu musí být větší než %d.",1)
                ->setRequired("ID týmu musí být vyplněno.");
        $form->addText("team", "Tým, za který se vkládá.")
                ->setRequired("Tým musí být vyplněn.");
        $form->addSelect("lang", "Jazyk reportu.")
                ->setItems(array("cs","en"), FALSE)
                ->setPrompt("----Jazyk----")
                ->setRequired("Jazyk reportu musí být vyplněn.");
        $form->addText("header", "Nadpis reportu.")
                ->setRequired("Nadpis reportu musí být vyplněn.");
        $form->addTextArea("text", "Text reportu.", 80, 200)
                ->setRequired("Text reportu musí být vyplněn.");
        $form->addMultiUpload("images", "Fotky.")
                ->addRule(Form::MAX_LENGTH, "Je možné nahrát maximálně 10 fotek.", 10)
                ->addRule(Form::MAX_FILE_SIZE, "Fotografie nesmí být větší, než 500 kB.", 500*1024)
                ->addRule(Form::IMAGE, "Fotografie musí být ve formátu JPEG, GIF nebo PNG.");
/*
        $form->addUpload("image", "Fotografie.")
                ->addRule(Form::MAX_FILE_SIZE, "Fotografie nesmí být větší, než 500 kB.", 500*1024)
                ->addRule(Form::IMAGE, "Fotografie musí být ve formátu JPEG, GIF nebo PNG.");
        $form->addText("caption", "Popisek fotky.");
 */
        $form->addSubmit("submit", "Odeslat");
        $form->onSuccess[] = array($this, "formSucceeded");
        
        return $form;
    }
}