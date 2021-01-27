<?php

namespace FOL\Components\AnswerForm;

use Exception;
use FOL\Model\Card\CardFactory;
use FOL\Model\Card\DoublePointsCard;
use FOL\Model\ORM\AnswersService;
use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\Models\ModelTeam;
use FOL\Model\ORM\ScoreService;
use FOL\Model\ORM\Services\ServicePeriod;
use FOL\Model\ORM\Services\ServiceTask;
use FOL\Model\ORM\Services\ServiceYear;
use FOL\Model\ORM\TasksService;
use FOL\Components\BaseForm;
use FOL\Model\ScoreStrategy;
use Fykosak\Utils\Localization\GettextTranslator;
use Fykosak\Utils\Logging\FlashMessageDump;
use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\ORM\TypedTableSelection;
use Nette\Application\AbortException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Form;
use Nette\Database\DriverException;
use Nette\DI\Container;
use Nette\InvalidStateException;
use Nette\Security\User;
use Nette\Utils\Html;
use Throwable;
use Tracy\Debugger;
use FOL\Components\BaseComponent;

class AnswerFormComponent extends BaseComponent {

    const TASK_ELEMENT = 'task';
    const TASK_INFO_ELEMENT = 'answer-info';
    const SUBMIT_ELEMENT = 'solution_submit';

    protected TasksService $tasksService;
    protected AnswersService $answersService;
    protected ScoreService $scoreService;
    protected ServiceYear $serviceYear;
    protected User $user;
    protected ModelTeam $team;
    protected DoublePointsCard $doublePointsCard;
    protected ServiceTask $serviceTask;
    private ServicePeriod $servicePeriod;
    private ScoreStrategy $scoreStrategy;

    public function __construct(Container $container, ModelTeam $team) {
        $this->team = $team;
        parent::__construct($container);
    }

    public function injectSecondary(
        TasksService $tasksService,
        ServicePeriod $servicePeriod,
        AnswersService $answersService,
        ScoreService $scoreService,
        ServiceYear $serviceYear,
        ServiceTask $serviceTask,
        User $user,
        CardFactory $cardFactory,
        ScoreStrategy $scoreStrategy
    ): void {
        $this->tasksService = $tasksService;
        $this->answersService = $answersService;
        $this->scoreService = $scoreService;
        $this->serviceYear = $serviceYear;
        $this->serviceTask = $serviceTask;
        $this->servicePeriod = $servicePeriod;
        $this->user = $user;
        $this->scoreStrategy = $scoreStrategy;
        $this->doublePointsCard = $cardFactory->createForTeam($this->team)['double_points'];
    }

    /**
     * @param Form $form
     * @return void
     * @throws AbortException
     * @throws Throwable
     */
    private function formSubmitted(Form $form): void {
        $values = $form->getValues();

        try {
            $isDoublePoints = false;
            if (isset($values['double_points'])) {
                if ($this->doublePointsCard->wasUsed()) {
                    throw new ForbiddenRequestException();
                }
                $isDoublePoints = true; // TODO continue implementation
            }
            /** @var ModelTask $task */
            $task = $this->serviceTask->findByPrimary($values[self::TASK_ELEMENT]);

            $period = $this->servicePeriod->findCurrent($task->getGroup());
            $solution = trim($values['solution'], ' ');
            $solution = strtr($solution, ',', '.');

            if (!$period) {
                $this->serviceLog->log($this->team->id_team, 'solution_tried', 'The team tried to insert the solution of task [$task->id_task] with solution [$solution].');
                throw new InvalidStateException('There is no active submit period.', AnswersService::ERROR_OUT_OF_PERIOD);
            }
            // Handle card usage

            $correct = TasksService::checkAnswer($task, $solution);
            $results = $this->answersService->insert($this->team, $task, $solution, $period, $correct, $isDoublePoints);
            //Environment::getCache()->clean(array(Cache::TAGS => array('problems/$team'))); // not used

            if ($isDoublePoints) {
                $logger = new MemoryLogger();
                $this->doublePointsCard->handle($logger, [
                    'correct' => $correct,
                    'answer_id' => $results,
                    'task_id' => $task->id_task,
                ]);
                FlashMessageDump::dump($logger, $this->getPresenter());
            }

            if ($correct) {
                $this->getPresenter()->flashMessage(_('Vaše odpověď je správně.'), 'success');
                $this->tasksService->updateSingleCounter($this->team, $task);
                $this->scoreService->updateAfterInsert($this->team, $task); //musi byt az po updatu counteru
                $this->getPresenter()->redirect('rating', ['id' => $task->id_task]);
            } else {
                $this->getPresenter()->flashMessage(_('Vaše odpověď je špatně.'), 'danger');
            }
        } catch (AbortException $exception) {
            throw $exception;
        } catch (InvalidStateException $e) {
            if ($e->getCode() == AnswersService::ERROR_TIME_LIMIT) {
                $this->getPresenter()->flashMessage(sprintf(_('Lze odpovídat až za <span class="timesec">%d</span> sekund.'), $e->getMessage()), '!warning');
                return;
            } elseif ($e->getCode() == AnswersService::ERROR_OUT_OF_PERIOD) {
                $this->getPresenter()->flashMessage(_('Není aktuální žádné odpovídací období.'), 'danger');
                return;
            } else {
                $this->getPresenter()->flashMessage(_('Stala se neočekávaná chyba.'), 'danger');
                Debugger::log($e);
                return;
            }
        } catch (DriverException $e) {
            if ($e->getCode() == 1062) {
                $this->getPresenter()->flashMessage(_('Na zadaný úkol jste již takto jednou odpovídali.'), 'danger');
            } else {
                $this->getPresenter()->flashMessage(_('Stala se neočekávaná chyba.'), 'danger');
                Debugger::log($e);
            }
            return;
        } catch (Exception $e) {
            $this->getPresenter()->flashMessage(_('Stala se neočekávaná chyba.'), 'danger');
            Debugger::log($e);
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
        /** @var ModelTask $task */
        foreach ($this->tasks as $task) {
            $options[$task->id_task] = $task->getGroup()->code_name . ': ' . GettextTranslator::i18nHelper($task, 'name', $this->getPresenter()->lang);
            $rules[$task->answer_type][] = $task->id_task;
        }
        $tasks = $options;
        $select = $form->addSelect(self::TASK_ELEMENT, 'Úkol', $tasks)
            ->setPrompt(' ---- Vybrat ---- ')
            ->addRule(Form::FILLED, 'Vyberte prosím řešený úkol.');

        // Solution
        $text = $form->addText('solution', _('Odpověď'))
            ->addRule(Form::FILLED, _('Vyplňte prosím řešení úkolu.'));

        $checkBox = $form->addCheckbox('double_points', _('Use card double points'));
        if ($this->doublePointsCard->wasUsed()) {
            $checkBox->setDisabled(true);
        }

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

    protected function startUp(): void {
        parent::startUp();
        if (!$this->user->isLoggedIn()) {
            throw new InvalidStateException('There is no logged team.');
        }
        if ($this->serviceYear->getCurrent()->isGameEnd()) {
            $this->flashMessage(_('Čas vypršel.'), 'danger');
        } elseif (!$this->serviceYear->getCurrent()->isGameStarted()) {
            $this->flashMessage(_('Hra ještě nezačala.'), 'danger');
        } else {
            $this->initTasks();
        }
    }

    private ?TypedTableSelection $tasks = null;
    private array $tasksInfo;

    private function initTasks(): void {
        $this->tasks = $this->serviceTask->getTable()
            ->where('id_task', $this->tasksService->findSubmitAvailable($this->team)->fetchPairs('id_task', 'id_task'))
            ->order('id_group')
            ->order('number');

        $this->tasksInfo = [];
        /** @var ModelTask $task */
        foreach ($this->tasks as $task) {
            $this->tasksInfo[$task->id_task] = [
                'sig_digits' => $task->real_sig_digits,
                'unit' => $task->answer_unit,
                'type' => $task->answer_type,
                'maxPoints' => $task->points,
                'curPoints' => $this->scoreStrategy->getSingleTaskScore($this->team, $task),
            ];
        }
    }

    public function render(): void {
        $this->template->tasks = $this->tasks;
        $this->template->tasksInfo = $this->tasksInfo;
        $this->template->tasksInfoElement = self::TASK_INFO_ELEMENT;
        $this->template->submitElement = self::SUBMIT_ELEMENT;

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'answerForm.latte');
        parent::render();
    }
}
