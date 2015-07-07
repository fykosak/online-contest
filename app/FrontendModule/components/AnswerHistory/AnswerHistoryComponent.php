<?php

use App\Model\Interlos;

class AnswerHistoryComponent extends BaseListComponent
{

    protected function beforeRender() {
	// Paginator
	$paginator = $this->getPaginator();
	$this->getSource()->applyLimit($paginator->itemsPerPage, $paginator->offset);
	// Load template
        $id_team = Interlos::getLoggedTeam($this->getPresenter()->user)->id_team;
	$this->getTemplate()->history	= $this->getSource()->fetchAll();
	$this->getTemplate()->correct	= Interlos::answers()->findAllCorrect($id_team)->fetchPairs("id_answer", "id_answer");
	$this->getTemplate()->tasks	= Interlos::tasks()->findAll()->fetchAssoc("id_task");
    }

}