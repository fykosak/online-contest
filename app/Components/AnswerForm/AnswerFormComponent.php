<?php

namespace FOL\Components\AnswerForm;

use Exception;
use FOL\Model\Card\CardFactory;
use FOL\Model\Card\DoublePointsCard;
use FOL\Model\GameSetup;
use FOL\Model\ORM\AnswersService;
use FOL\Model\ORM\Models\ModelCardUsage;
use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\Models\ModelTeam;
use FOL\Model\ORM\ScoreService;
use FOL\Model\ORM\TasksService;
use FOL\Components\BaseForm;
use FOL\Model\ScoreStrategy;
use Fykosak\Utils\Logging\FlashMessageDump;
use Fykosak\Utils\Logging\MemoryLogger;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
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

final class AnswerFormComponent extends BaseComponent {

    private TasksService $tasksService;
    private AnswersService $answersService;
    private ScoreService $scoreService;
    private User $user;
    private ModelTeam $team;
    private DoublePointsCard $doublePointsCard;
    private ScoreStrategy $scoreStrategy;
    private ModelTask $task;
    private GameSetup $gameSetup;

    public function __construct(Container $container, ModelTeam $team, ModelTask $task) {
        $this->team = $team;
        $this->task = $task;
        parent::__construct($container);
    }

    /**
     * @param TasksService $tasksService
     * @param AnswersService $answersService
     * @param ScoreService $scoreService
     * @param User $user
     * @param CardFactory $cardFactory
     * @param ScoreStrategy $scoreStrategy
     * @param GameSetup $gameSetup
     * @throws BadRequestException
     */
    public function injectSecondary(
        TasksService $tasksService,
        AnswersService $answersService,
        ScoreService $scoreService,
        User $user,
        CardFactory $cardFactory,
        ScoreStrategy $scoreStrategy,
        GameSetup $gameSetup
    ): void {
        $this->tasksService = $tasksService;
        $this->answersService = $answersService;
        $this->scoreService = $scoreService;
        $this->user = $user;
        $this->scoreStrategy = $scoreStrategy;
        $this->gameSetup = $gameSetup;
        $this->doublePointsCard = $cardFactory->create($this->team, ModelCardUsage::TYPE_DOUBLE_POINTS);
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
            if (isset($values['double_points']) && $values['double_points']) {
                if ($this->doublePointsCard->wasUsed()) {
                    throw new ForbiddenRequestException();
                }
                $isDoublePoints = true;
            }

            $solution = trim($values['solution'], ' ');
            $solution = strtr($solution, ',', '.');
            $correct = $this->task->checkAnswer($solution);
            $this->serviceLog->getContext()->beginTransaction();
            $answer = $this->answersService->insert($this->team, $this->task, $solution, $correct, $isDoublePoints);

            // Handle card usage
            if ($isDoublePoints) {
                $logger = new MemoryLogger();
                $this->doublePointsCard->handle($logger, [
                    'correct' => $correct,
                    'answer_id' => $answer->id_answer,
                    'task_id' => $this->task->id_task,
                ]);
                FlashMessageDump::dump($logger, $this->getPresenter());
                $this->getPresenter()->flashMessage(_('Použili jste kartičku za dvojnásobek bodů.'));
            }

            if ($correct) {
                $this->getPresenter()->flashMessage(_('Vaše odpověď je správně.'), 'success');
                $this->tasksService->updateSingleCounter($this->team, $this->task->getGroup());
                $this->scoreService->updateAfterInsert($this->team, $this->task); //musi byt az po updatu counteru
                $this->serviceLog->getContext()->commit();
                $this->getPresenter()->redirect('rating', ['id' => $this->task->id_task]);
            } else {
                $this->getPresenter()->flashMessage(_('Vaše odpověď je špatně.'), 'danger');
            }
        } catch (AbortException $exception) {
            throw $exception;
        } catch (InvalidStateException $e) {
            $this->serviceLog->getContext()->rollBack();
            if ($e->getCode() == AnswersService::ERROR_TIME_LIMIT) {
                $this->getPresenter()->flashMessage(
                    Html::el('span')
                        ->addText(_('Lze odpovídat až za') . ' ')
                        ->addHtml(
                            Html::el('span')
                                ->addAttributes(['class' => 'timesec'])
                                ->addHtml($e->getMessage())
                        )
                        ->addText(' ' . _('sekund.')), 'warning');
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
            $this->serviceLog->getContext()->rollBack();
            if ($e->getCode() == 1062) {
                $this->getPresenter()->flashMessage(_('Na zadaný úkol jste již takto jednou odpovídali.'), 'danger');
            } else {
                $this->getPresenter()->flashMessage(_('Stala se neočekávaná chyba.'), 'danger');
                Debugger::log($e);
            }
            return;
        } catch (Exception $e) {
            $this->serviceLog->getContext()->rollBack();
            $this->getPresenter()->flashMessage(_('Stala se neočekávaná chyba.'), 'danger');
            Debugger::log($e);
            return;
        }
        $this->getPresenter()->redirect('this');
    }

    protected function createComponentForm(): BaseForm {
        $form = new BaseForm($this->getContext());

        // Solution
        $text = $form->addText('solution', _('Odpověď'))
            ->addRule(Form::FILLED, _('Vyplňte prosím řešení úkolu.'));

        $checkBox = $form->addCheckbox('double_points', _('Use card double points'));
        if ($this->doublePointsCard->wasUsed()) {
            $checkBox->setDisabled(true);
        }

        if ($this->task->answer_type === ModelTask::TYPE_INT) {
            $text->addRule(Form::INTEGER, 'Výsledek musí být celé číslo.');
        }
        if ($this->task->answer_type === ModelTask::TYPE_REAL) {
            $text->addRule(Form::PATTERN, 'Výsledek musí být reálné číslo.', '[-+]?[0-9]*[\.,]?[0-9]+([eE][-+]?[0-9]+)?');
        }

        $desc = Html::el('span');
        $text->setOption('description', $desc);

        $form->addSubmit('submit', 'Odeslat řešení');
        $form->onSuccess[] = function (Form $form) {
            $this->formSubmitted($form);
        };

        return $form;
    }

    /**
     * @throws ForbiddenRequestException
     */
    protected function startUp(): void {
        parent::startUp();
        if (!$this->team->getSubmitAvailableTasks()->where('id_task', $this->task->id_task)->fetch()) {
            throw new ForbiddenRequestException();
        }
    }

    public function render(): void {
        $this->template->taskInfo = [
            'sig_digits' => $this->task->real_sig_digits,
            'unit' => $this->task->answer_unit,
            'type' => $this->task->answer_type,
            'maxPoints' => $this->task->points,
            'curPoints' => $this->scoreStrategy->getSingleTaskScore($this->team, $this->task),
        ];

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'answerForm.latte');
        parent::render();
    }
}
