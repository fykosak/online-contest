<?php

use Dibi\DriverException;
use FOL\Model\ORM\AnswersService;
use FOL\Model\ORM\PeriodService;
use FOL\Model\ORM\ScoreService;
use FOL\Model\ORM\TasksService;
use FOL\Model\ORM\YearsService;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Nette\InvalidStateException;
use Nette\Security\User;
use Nette\Utils\Html;
use Tracy\Debugger;

class AnswerFormComponent extends BaseComponent {

    const TASK_ELEMENT = 'task';
    const TASK_INFO_ELEMENT = 'answer-info';
    const SUBMIT_ELEMENT = 'solution_submit';

    protected TasksService $tasksService;
    protected PeriodService $periodService;
    protected AnswersService $answersService;
    protected ScoreService $scoreService;
    protected YearsService $yearsService;

    protected User $user;

    protected int $teamId;

    public function __construct(Container $container, int $teamId) {
        $this->teamId = $teamId;
        parent::__construct($container);
    }

    public function injectSecondary(
        TasksService $tasksService,
        PeriodService $periodService,
        AnswersService $answersService,
        ScoreService $scoreService,
        YearsService $yearsService,
        User $user
    ): void {
        $this->tasksService = $tasksService;
        $this->periodService = $periodService;
        $this->answersService = $answersService;
        $this->scoreService = $scoreService;
        $this->yearsService = $yearsService;
        $this->user = $user;
    }

    /**
     * @param Form $form
     * @return void
     * @throws AbortException
     */
    private function formSubmitted(Form $form): void {
        $values = $form->getValues();

        try {
            $task = $this->tasksService->find($values[self::TASK_ELEMENT]);
            $period = $this->periodService->findCurrent($task['id_group']);
            $solution = trim($values['solution'], ' ');
            $solution = strtr($solution, ',', '.');
            $team = $this->getPresenter()->getLoggedTeam()->id_team;

            if (!$period) {
                $this->log($team, 'solution_tried', 'The team tried to insert the solution of task [$task->id_task] with solution [$solution].');
                throw new InvalidStateException('There is no active submit period.', AnswersService::ERROR_OUT_OF_PERIOD);
            }
            $correct = TasksService::checkAnswer($task, $solution);
            $this->answersService->insert($team, $task, $solution, $period, $correct);
            //Environment::getCache()->clean(array(Cache::TAGS => array('problems/$team'))); // not used

            if ($correct) {
                $this->getPresenter()->flashMessage(_('Vaše odpověď je správně.'), 'success');
                $this->tasksService->updateSingleCounter($team, $task);
                $this->scoreService->updateAfterInsert($team, $task); //musi byt az po updatu counteru
            } else {
                $this->getPresenter()->flashMessage(_('Vaše odpověď je špatně.'), 'danger');
            }
        } catch (InvalidStateException $e) {
            if ($e->getCode() == AnswersService::ERROR_TIME_LIMIT) {
                $this->getPresenter()->flashMessage(sprintf(_('Lze odpovídat až za <span class="timesec">%d</span> sekund.'), $e->getMessage()), '!warning');
                return;
            } elseif ($e->getCode() == AnswersService::ERROR_OUT_OF_PERIOD) {
                $this->getPresenter()->flashMessage(_('Není aktuální žádné odpovídací období.'), 'danger');
                return;
            } else {
                $this->getPresenter()->flashMessage(_('Stala se neočekávaná chyba.'), 'danger');
                //Debug::processException($e, TRUE);
                Debugger::log($e);
                //error_log($e->getTraceAsString());
                return;
            }
        } catch (DriverException $e) {
            if ($e->getCode() == 1062) {
                $this->getPresenter()->flashMessage(_('Na zadaný úkol jste již takto jednou odpovídali.'), 'danger');
            } else {
                $this->getPresenter()->flashMessage(_('Stala se neočekávaná chyba.'), 'danger');
                //Debug::processException($e, TRUE);
                Debugger::log($e);
                //error_log($e->getTraceAsString());
            }
            return;
        } catch (Exception $e) {
            $this->getPresenter()->flashMessage(_('Stala se neočekávaná chyba.'), 'danger');
            //Debug::processException($e, TRUE);
            Debugger::log($e);
            //error_log($e->getTraceAsString());
            return;
        }
        $this->getPresenter()->redirect('this');
    }

    protected function createComponentForm(): BaseForm {
        $form = new BaseForm($this->getContext());

        // Tasks

        $options = [];
        $rules = [
            TasksService::TYPE_STR => [],
            TasksService::TYPE_INT => [],
            TasksService::TYPE_REAL => [],
        ];

        foreach ($this->tasks as $task) {
            $options[$task['id_task']] = $task['code_name'] . ': ' . $task['name_' . $this->getPresenter()->lang];
            $rules[$task['answer_type']][] = $task['id_task'];
        }
        $tasks = $options;
        $select = $form->addSelect(self::TASK_ELEMENT, 'Úkol', $tasks)
            ->setPrompt(' ---- Vybrat ---- ')
            ->addRule(Form::FILLED, 'Vyberte prosím řešený úkol.');

        // Solution
        $text = $form->addText('solution', _('Odpověď'))
            ->addRule(Form::FILLED, _('Vyplňte prosím řešení úkolu.'));

        if (count($rules[TasksService::TYPE_INT])) {
            $text->addConditionOn($select, Form::IS_IN, $rules[TasksService::TYPE_INT])
                ->addRule(Form::INTEGER, 'Výsledek musí být celé číslo.');
        }
        if (count($rules[TasksService::TYPE_REAL])) {
            $text->addConditionOn($select, Form::IS_IN, $rules[TasksService::TYPE_REAL])
                ->addRule(Form::PATTERN, 'Výsledek musí být reálné číslo.', '[-+]?[0-9]*[\.,]?[0-9]+([eE][-+]?[0-9]+)?');
        }

        $desc = Html::el('span');
        $desc->addAttributes(['id' => self::TASK_INFO_ELEMENT]);
        $text->setOption('description', $desc);


        $submit = $form->addSubmit(self::SUBMIT_ELEMENT, 'Odeslat řešení');
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
    protected function startUp(): void {
        parent::startUp();
        if (!$this->user->isLoggedIn()) {
            throw new InvalidStateException('There is no logged team.');
        }
        if ($this->yearsService->isGameEnd()) {
            $this->flashMessage(_('Čas vypršel.'), 'danger');
            $this->valid = false;
        } elseif (!$this->yearsService->isGameStarted()) {
            $this->flashMessage(_('Hra ještě nezačala.'), 'danger');
            $this->valid = false;
        } else {
            $this->valid = true;
            $this->initTasks();
        }
    }

    private bool $valid;

    private ?array $tasks = null;
    private array $tasksInfo;

    /**
     * @return void
     * @throws \Dibi\Exception
     */
    private function initTasks(): void {

        $this->tasks = $this->tasksService->findSubmitAvailable($this->teamId)->fetchAll();

        $this->tasksInfo = [];
        foreach ($this->tasks as $task) {
            $this->tasksInfo[$task['id_task']] = [
                'sig_digits' => $task['real_sig_digits'],
                'unit' => $task['answer_unit'],
                'type' => $task['answer_type'],
                'maxPoints' => $task['points'],
                'curPoints' => $this->scoreService->getSingleTaskScore($this->teamId, $task),
            ];
        }
    }

    public function render(): void {
        $this->template->tasks = $this->tasks;
        $this->template->valid = $this->valid;
        $this->template->tasksInfo = $this->tasksInfo;
        $this->template->tasksInfoElement = self::TASK_INFO_ELEMENT;
        $this->template->submitElement = self::SUBMIT_ELEMENT;

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'answerForm.latte');
        parent::render();
    }
}
