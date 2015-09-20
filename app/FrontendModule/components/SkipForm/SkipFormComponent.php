<?php

use App\Model\Interlos,
    App\Model\AnswersModel,
    Nette\Application\UI\Form,
    Tracy\Debugger;

class SkipFormComponent extends BaseComponent {

    public function formSubmitted(Form $form) {
        $values = $form->getValues();

        try {
            $task = Interlos::tasks()->find($values["task"]);
            $team = Interlos::getLoggedTeam($this->getPresenter()->user)->id_team;


            Interlos::tasks()->skip($team, $task);
            //Environment::getCache()->clean(array(Cache::TAGS => array("problems/$team"))); not used
            
            $this->getPresenter()->flashMessage(sprintf(_("Úloha %s přeskočena."), $task->code_name), "success");
            Interlos::tasks()->updateSingleCounter($team, $task);
        } catch (Nette\InvalidStateException $e) {
            if ($e->getCode() == AnswersModel::ERROR_SKIP_OF_PERIOD) {
                $this->getPresenter()->flashMessage(_("V tomto období není možno přeskakovat úlohy této série."), "danger");
                return;
            } else if ($e->getCode() == AnswersModel::ERROR_SKIP_OF_ANSWERED) {
                $this->getPresenter()->flashMessage(_("Není možno přeskočit úlohu, na níž již bylo odpovídáno."), "danger");
                return;
            } else {
                $this->getPresenter()->flashMessage(_("Stala se neočekávaná chyba."), "danger");
                //Debug::processException($e, TRUE);
                Debugger::log($e);
                //error_log($e->getTraceAsString());
                return;
            }
        } catch (Exception $e) {
            $this->getPresenter()->flashMessage(_("Stala se neočekávaná chyba."), "danger");
            //Debug::processException($e, TRUE);
            Debugger::log($e);
            //error_log($e->getTraceAsString());
            return;
        }
        $this->getPresenter()->redirect("this");
    }

    protected function createComponentForm($name) {
        $form = new BaseForm($this, $name);
        $team = Interlos::getLoggedTeam($this->getPresenter()->user)->id_team;

        // Tasks
        $tasks = Interlos::tasks()
                ->findSubmitAvailable($team)
                ->fetchAll();
        $skippableGroups = Interlos::groups()->findAllSkippable()->fetchPairs('id_group', 'id_group');
        $answers = Interlos::answers()->findAllCorrect($team)->fetchPairs('id_task', 'id_task');
        $options = array();
        foreach ($tasks as $task) {
            if (array_key_exists($task["id_group"], $skippableGroups) && !array_key_exists($task["id_task"], $answers)) {
                $options[$task["id_task"]] = $task["code_name"] . ' (' . $task["name_" . $this->getPresenter()->lang] . ')';
            }
        }
        $tasks = $options;
        $select = $form->addSelect("task", "Úkol", $tasks)
                ->setPrompt(_(" ---- Vybrat ---- "))
                ->addRule(Form::FILLED, "Vyberte prosím úkol k přeskočení.");


        $submit = $form->addSubmit("task_skip", "Přeskočit úkol");
        if (count($options) == 0) {
            $submit->setDisabled(true);
        }

        $form->onSuccess[] = array($this, "formSubmitted");

        return $form;
    }

    protected function startUp() {
        parent::startUp();
        if (!$this->getPresenter()->user->isLoggedIn()) {
            throw new Nette\InvalidStateException("There is no logged team.");
        }
        if (Interlos::isGameEnd()) {
            $this->flashMessage(_("Čas vypršel."), "danger");
            $this->getTemplate()->valid = FALSE;
        } else if (!Interlos::isGameStarted()) {
            $this->flashMessage(_("Hra ještě nezačala."), "danger");
            $this->getTemplate()->valid = FALSE;
        } else {
            $this->getTemplate()->valid = TRUE;
        }
    }

}
