<?php

class AnswerFormComponent extends BaseComponent {

    public function formSubmitted(Form $form) {
        $values = $form->getValues();

        try {
            $task = Interlos::tasks()->find($values["task"]);
            $period = Interlos::period()->findCurrent($task["id_group"]);
            $solution = trim($values["solution"], " ");
            $team = Interlos::getLoggedTeam()->id_team;

            if (!$period) {
                $this->log($team, "solution_tried", "The team tried to insert the solution of task [$task->id_task] with solution [$solution].");
                throw new InvalidStateException("There is no active submit period.", AnswersModel::ERROR_OUT_OF_PERIOD);
            }

            Interlos::answers()->insert($team, $task, $solution, $period);
            Environment::getCache()->clean(array(Cache::TAGS => array("problems/$team")));
            
            if (TasksModel::checkAnswer($task, $solution)) {
                $this->getPresenter()->flashMessage("Vaše odpověď je správně.", "success");
                Interlos::tasks()->updateCounter($team);
            } else {
                $this->getPresenter()->flashMessage("Vaše odpověď je špatně.", "error");
            }
        } catch (InvalidStateException $e) {
            if ($e->getCode() == AnswersModel::ERROR_TIME_LIMIT) {
                $this->getPresenter()->flashMessage("Od vaší poslední špatné odpovědi ještě neuplynulo " . $period["time_penalty"] . " sekund.", "error");
                return;
            } else if ($e->getCode() == AnswersModel::ERROR_OUT_OF_PERIOD) {
                $this->getPresenter()->flashMessage("Není aktuálí žádné odpovídací období.", "error");
                return;
            } else {
                $this->getPresenter()->flashMessage("Stala se neočekávaná chyba.", "error");
                Debug::processException($e, TRUE);
                //error_log($e->getTraceAsString());
                return;
            }
        } catch (DibiDriverException $e) {
            if ($e->getCode() == 1062) {
                $this->getPresenter()->flashMessage("Na zadaný úkol jste již takto jednou odpovídali.", "error");
            } else {
                $this->getPresenter()->flashMessage("Stala se neočekávaná chyba.", "error");
                Debug::processException($e, TRUE);
                //error_log($e->getTraceAsString());
            }
            return;
        } catch (Exception $e) {
            $this->getPresenter()->flashMessage("Stala se neočekávaná chyba.", "error");
            Debug::processException($e, TRUE);
            //error_log($e->getTraceAsString());
            return;
        }
        $this->getPresenter()->redirect("this");
    }

    protected function createComponentForm($name) {
        $form = new BaseForm($this, $name);

        // Tasks
        $tasks = Interlos::tasks()
                ->findSubmitAvailable(Interlos::getLoggedTeam()->id_team)
                ->fetchAll();
        $options = array();
        $rules = array(
            TasksModel::TYPE_STR => array(),
            TasksModel::TYPE_INT => array(),
            TasksModel::TYPE_REAL => array(),
        );
        foreach ($tasks as $task) {
            $options[$task["id_task"]] = $task["code_name"] . ': '. $task["name"] . ' (' . $task["answer_type"] . ')';
            $rules[$task["answer_type"]][] = $task["id_task"];
        }
        $tasks = array(NULL => " ---- Vybrat ---- ") + $options;
        $select = $form->addSelect("task", "Úkol", $tasks)
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
                    ->addRule(Form::FLOAT, "Výsledek musí být reálné číslo.");            
        }
        $text->setOption("description", "Desetinná čísla zadávejte s desetinnou tečkou.");



        $submit = $form->addSubmit("solution_submit", "Odeslat řešení");
        if (count($options) == 0) {
            $submit->setDisabled(true);
        }
        $form->onSubmit[] = array($this, "formSubmitted");

        return $form;
    }

    protected function startUp() {
        parent::startUp();
        if (!Environment::getUser()->isLoggedIn()) {
            throw new InvalidStateException("There is no logged team.");
        }
        if (Interlos::isGameEnd()) {
            $this->flashMessage("Čas vypršel.", "error");
            $this->getTemplate()->valid = FALSE;
        } else if (!Interlos::isGameStarted()) {
            $this->flashMessage("Hra ještě nezačala.", "error");
            $this->getTemplate()->valid = FALSE;
        } else {
            $this->getTemplate()->valid = TRUE;
        }
    }

}
