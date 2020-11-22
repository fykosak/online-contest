<?php

namespace FOL\Components;

use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Database\UniqueConstraintViolationException;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Tracy\Debugger;

class RatingComponent extends BaseComponent {

    private int $taskId;
    private $team;
    private Context $context;

    public function __construct(Container $container, int $taskId, $team) {
        parent::__construct($container);
        $this->taskId = $taskId;
        $this->team = $team;
    }

    public function injectPrimary(Context $context): void {
        $this->context = $context;
    }

    protected function createComponentForm(): Form {
        $control = new Form();
        $control->addInteger('rating', _('Rating'))
            ->setAttribute('type', 'range')
            ->setAttribute('min', 0)
            ->setAttribute('max', 10)
            ->setAttribute('step', 1); // TODO copy paste this for new options

        $control->addSubmit(_('Send'))->onClick[] = function (SubmitButton $button) {
            $this->handleForm($button->getForm());
        };
        return $control;
    }

    public function render(): void {
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
        parent::render();
    }

    private function handleForm(\Nette\Forms\Form $form) {
        $values = $form->getValues();
        try {
            $this->context->table('rating')->insert([
                'team_id' => $this->team['id_team'],
                'task_id' => $this->taskId,
                'rating' => $values['rating'],
            ]);
            $this->getPresenter()->flashMessage(_('Your rating has been saved'), 'success');
        } catch (UniqueConstraintViolationException $exception) {
            $this->getPresenter()->flashMessage(_('za tuto ulohu bolo už hlasované'), 'danger');
        } catch (\Throwable $exception) {
            $this->getPresenter()->flashMessage(_('Error pičo'), 'danger');
        }
        $this->getPresenter()->redirect(':Game:Task:default');
    }
}
