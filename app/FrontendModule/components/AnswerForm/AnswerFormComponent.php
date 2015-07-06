<?php

use Nette,
    Nette\Application\UI\Form,
    Nette\Utils\Html,
    App\Model\Interlos,
    App\Model\AnswersModel,
    App\Model\TasksModel;

class AnswerFormComponent extends BaseComponent {

    const TASK_ELEMENT = 'task';
    const TASK_INFO_ELEMENT = 'answer-info';
    const SUBMIT_ELEMENT = 'solution_submit';

    public function formSubmitted(Form $form) {
        $values = $form->getValues();

        try {
            $task = Interlos::tasks()->find($values[self::TASK_ELEMENT]);
            $period = Interlos::period()->findCurrent($task["id_group"]);
            $solution = trim($values["solution"], " ");
            $solution = strtr($solution, ",", ".");
            $team = Interlos::getLoggedTeam()->id_team;

            if (!$period) {
                $this->log($team, "solution_tried", "The team tried to insert the solution of task [$task->id_task] with solution [$solution].");
                throw new Nette\InvalidStateException("There is no active submit period.", AnswersModel::ERROR_OUT_OF_PERIOD);
            }

            Interlos::answers()->insert($team, $task, $solution, $period);
            //Environment::getCache()->clean(array(Cache::TAGS => array("problems/$team"))); // not used

            if (TasksModel::checkAnswer($task, $solution)) {
                $this->getPresenter()->flashMessage(_("Vaše odpověď je správně."), "success");
                Interlos::tasks()->updateSingleCounter($team, $task);
            } else {
                $this->getPresenter()->flashMessage(_("Vaše odpověď je špatně."), "danger");
            }
        } catch (Nette\InvalidStateException $e) {
            if ($e->getCode() == AnswersModel::ERROR_TIME_LIMIT) {
                $this->getPresenter()->flashMessage(sprintf(_("Lze odpovídat až za <span class='timesec'>%d</span> sekund."), $e->getMessage()), "!warning");
                return;
            } else if ($e->getCode() == AnswersModel::ERROR_OUT_OF_PERIOD) {
                $this->getPresenter()->flashMessage(_("Není aktuální žádné odpovídací období."), "danger");
                return;
            } else {
                $this->getPresenter()->flashMessage(_("Stala se neočekávaná chyba."), "danger");
                Debug::processException($e, TRUE);
                //error_log($e->getTraceAsString());
                return;
            }
        } catch (DibiDriverException $e) {
            if ($e->getCode() == 1062) {
                $this->getPresenter()->flashMessage(_("Na zadaný úkol jste již takto jednou odpovídali."), "danger");
            } else {
                $this->getPresenter()->flashMessage(_("Stala se neočekávaná chyba."), "danger");
                Debug::processException($e, TRUE);
                //error_log($e->getTraceAsString());
            }
            return;
        } catch (Exception $e) {
            $this->getPresenter()->flashMessage(_("Stala se neočekávaná chyba."), "danger");
            Debug::processException($e, TRUE);
            //error_log($e->getTraceAsString());
            return;
        }
        $this->getPresenter()->redirect("this");
    }

    protected function createComponentForm($name) {
        $form = new BaseForm($this, $name);

        // Tasks

        $options = array();
        $rules = array(
            TasksModel::TYPE_STR => array(),
            TasksModel::TYPE_INT => array(),
            TasksModel::TYPE_REAL => array(),
        );

        foreach ($this->tasks as $task) {
            $options[$task["id_task"]] = $task["code_name"] . ': ' . $task["name_" . $this->getPresenter()->lang];
            $rules[$task["answer_type"]][] = $task["id_task"];
        }
        $tasks = array(NULL => " ---- Vybrat ---- ") + $options;
        $select = $form->addSelect(self::TASK_ELEMENT, "Úkol", $tasks)
                ->skipFirst()
                ->addRule(Form::FILLED, "Vyberte prosím řešený úkol.");

        // Solution
        $text = $form->addText("solution", "Odpověď")
                ->addRule(Form::FILLED, "Vyplňte prosím řešení úkolu.");

        if (count($rules[TasksModel::TYPE_INT])) {
            $text->addConditionOn($select, Form::IS_IN, $rules[TasksModel::TYPE_INT])
                    ->addRule(Form::INTEGER, "Výsledek musí být celé číslo.");
        }
        if (count($rules[TasksModel::TYPE_REAL])) {
            $text->addConditionOn($select, Form::IS_IN, $rules[TasksModel::TYPE_REAL])
                    ->addRule(Form::REGEXP, "Výsledek musí být reálné číslo.", '/[-+]?[0-9]*[\.,]?[0-9]+([eE][-+]?[0-9]+)?/');
        }

        $desc = Html::el('span');
        $desc->id(self::TASK_INFO_ELEMENT);
        $text->setOption("description", $desc);



        $submit = $form->addSubmit(self::SUBMIT_ELEMENT, "Odeslat řešení");
        if (count($options) == 0) {
            $submit->setDisabled(true);
        }
        $form->onSubmit[] = array($this, "formSubmitted");

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
            $this->initTasks();
        }
    }

    private $tasks;
    private $tasksInfo;

    private function initTasks() {
        $this->tasks = Interlos::tasks()
                ->findSubmitAvailable(Interlos::getLoggedTeam()->id_team)
                ->fetchAll();

        $this->tasksInfo = array();
        foreach ($this->tasks as $task) {
            $this->tasksInfo[$task["id_task"]] = array(
                "sig_digits" => $task["real_sig_digits"],
                "unit" => $task["answer_unit"],
                "type" => $task["answer_type"],
            );
        }
        $this->getTemplate()->tasksInfo = $this->tasksInfo;
        $this->getTemplate()->tasksInfoElement = self::TASK_INFO_ELEMENT;
        $this->getTemplate()->submitElement = self::SUBMIT_ELEMENT;

        $this->getTemplate()->realHint = _("Pí lze zapsat jako: 3.14; 3,14; 314e-2 nebo 0.314e1.");
        $this->getTemplate()->expected = _("Očekávaný počet platných cifer");
        $this->getTemplate()->unit = _("Jednotka");
    }

}
