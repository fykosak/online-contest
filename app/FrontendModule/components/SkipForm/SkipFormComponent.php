<?php

use App\Model\Interlos;
use App\Model\AnswersModel;
use Nette\Application\UI\Form;
use Tracy\Debugger;

class SkipFormComponent extends BaseComponent {

    private function formSubmitted(Form $form): void {
        if (!$this->getPresenter()->user->isAllowed('task', 'skip')) {
            $this->getPresenter()->error(_('Již jste vyčerpali svůj limit pro počet přeskočených úloh.'), Nette\Http\Response::S403_FORBIDDEN);
        }

        $values = $form->getValues();

        try {
            $task = Interlos::tasks()->find($values["task"]);
            $team = Interlos::getLoggedTeam($this->getPresenter()->user)->id_team;


            Interlos::tasks()->skip($team, $task);
            //Environment::getCache()->clean(array(Cache::TAGS => array("problems/$team"))); not used

            $this->getPresenter()->flashMessage(sprintf(_("Úloha %s přeskočena."), $task->code_name), "success");
            Interlos::tasks()->updateSingleCounter($team, $task);
            Interlos::score()->updateAfterSkip($team);
        } catch (Nette\InvalidStateException $e) {
            if ($e->getCode() == AnswersModel::ERROR_SKIP_OF_PERIOD) {
                $this->getPresenter()->flashMessage(_("V tomto období není možno přeskakovat úlohy této série."), "danger");
                return;
            } elseif ($e->getCode() == AnswersModel::ERROR_SKIP_OF_ANSWERED) {
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

        /*to avoid error after skipping last possible*/
        if ($this->getPresenter()->user->isAllowed('task', 'skip')) {
            $this->getPresenter()->redirect("this");
        } else {
            $this->getPresenter()->redirect("Game:default");
        }
    }

    protected function createComponentForm(): BaseForm {
        $form = new BaseForm();
        $team = Interlos::getLoggedTeam($this->getPresenter()->user)->id_team;

        // Tasks
        $tasks = Interlos::tasks()
            ->findSubmitAvailable($team)
            ->fetchAll();
        $skippableGroups = Interlos::groups()->findAllSkippable()->fetchPairs('id_group', 'id_group');
        $answers = Interlos::answers()->findAllCorrect($team)->fetchPairs('id_task', 'id_task');
        $options = [];
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

        $form->onSuccess[] = function (Form $form) {
            $this->formSubmitted($form);
        };

        return $form;
    }

    protected function startUp(): void {
        parent::startUp();
        if (!$this->getPresenter()->user->isLoggedIn()) {
            throw new Nette\InvalidStateException("There is no logged team.");
        }
        if (Interlos::isGameEnd()) {
            $this->flashMessage(_("Čas vypršel."), "danger");
            $this->getTemplate()->valid = false;
        } elseif (!Interlos::isGameStarted()) {
            $this->flashMessage(_("Hra ještě nezačala."), "danger");
            $this->getTemplate()->valid = false;
        } else {
            $this->getTemplate()->valid = true;
        }
    }
}
