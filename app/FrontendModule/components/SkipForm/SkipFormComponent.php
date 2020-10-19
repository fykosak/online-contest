<?php

use FOL\Model\ORM\AnswersService;
use FOL\Model\ORM\GroupsService;
use FOL\Model\ORM\ScoreService;
use FOL\Model\ORM\TasksService;
use FOL\Model\ORM\YearsService;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Http\Response;
use Nette\InvalidStateException;
use Nette\Security\User;
use Tracy\Debugger;

class SkipFormComponent extends BaseComponent {

    protected AnswersService $answersService;
    protected GroupsService $groupsService;
    protected TasksService $tasksService;
    protected ScoreService $scoreService;
    protected YearsService $yearsService;
    protected User $user;

    public function injectPrimary(
        AnswersService $answersService,
        GroupsService $groupsService,
        TasksService $tasksService,
        ScoreService $scoreService,
        YearsService $yearsService,
        User $user
    ): void {
        $this->answersService = $answersService;
        $this->groupsService = $groupsService;
        $this->tasksService = $tasksService;
        $this->scoreService = $scoreService;
        $this->yearsService = $yearsService;
        $this->user = $user;
    }

    /**
     * @param Form $form
     * @return void
     * @throws AbortException
     * @throws BadRequestException
     */
    private function formSubmitted(Form $form): void {
        if (!$this->getPresenter()->user->isAllowed('task', 'skip')) {
            $this->getPresenter()->error(_('Již jste vyčerpali svůj limit pro počet přeskočených úloh.'), Response::S403_FORBIDDEN);
        }

        $values = $form->getValues();

        try {
            $task = $this->tasksService->find($values["task"]);
            $team = $this->getPresenter()->getLoggedTeam()->id_team;


            $this->tasksService->skip($team, $task);
            //Environment::getCache()->clean(array(Cache::TAGS => array("problems/$team"))); not used

            $this->getPresenter()->flashMessage(sprintf(_("Úloha %s přeskočena."), $task->code_name), "success");
            $this->tasksService->updateSingleCounter($team, $task);
            $this->scoreService->updateAfterSkip($team);
        } catch (InvalidStateException $e) {
            if ($e->getCode() == AnswersService::ERROR_SKIP_OF_PERIOD) {
                $this->getPresenter()->flashMessage(_("V tomto období není možno přeskakovat úlohy této série."), "danger");
                return;
            } elseif ($e->getCode() == AnswersService::ERROR_SKIP_OF_ANSWERED) {
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
            Debugger::barDump($e);
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

    /**
     * @return BaseForm
     * @throws \Dibi\Exception
     */
    protected function createComponentForm(): BaseForm {
        $form = new BaseForm($this->getContext());
        $team = $this->getPresenter()->getLoggedTeam()->id_team;

        // Tasks
        $tasks = $this->tasksService->findSubmitAvailable($team)
            ->fetchAll();
        $skippableGroups = $this->groupsService->findAllSkippable()->fetchPairs('id_group', 'id_group');
        $answers = $this->answersService->findAllCorrect($team)->fetchPairs('id_task', 'id_task');
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

    /**
     * @return void
     * @throws \Dibi\Exception
     */
    protected function beforeRender(): void {
        parent::beforeRender();
        if ($this->yearsService->isGameEnd()) {
            $this->flashMessage(_("Čas vypršel."), "danger");
            $this->getTemplate()->valid = false;
        } elseif (!$this->yearsService->isGameStarted()) {
            $this->flashMessage(_("Hra ještě nezačala."), "danger");
            $this->getTemplate()->valid = false;
        } else {
            $this->getTemplate()->valid = true;
        }
    }

    protected function startUp(): void {
        parent::startUp();
        if (!$this->user->isLoggedIn()) {
            throw new InvalidStateException("There is no logged team.");
        }
    }


    public function render(): void {
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'skipForm.latte');
        parent::render();
    }
}
