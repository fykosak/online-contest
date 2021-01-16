<?php

namespace FOL\Components\Rating;

use FOL\Components\BaseComponent;
use FOL\Modules\FrontendModule\Components\BaseForm;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Database\Explorer;
use Nette\Database\UniqueConstraintViolationException;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Throwable;

class RatingComponent extends BaseComponent {

    private int $taskId;
    private $team;
    private Explorer $explorer;

    public function __construct(Container $container, int $taskId, $team) {
        parent::__construct($container);
        $this->taskId = $taskId;
        $this->team = $team;
    }

    public function injectPrimary(Explorer $explorer): void {
        $this->explorer = $explorer;
    }

    protected function createComponentForm(): Form {
        $control = new BaseForm($this->getContext());
        $control->addInteger('rating', _('Rating'))
            ->setHtmlAttribute('class', 'form-control-range')
            ->setHtmlAttribute('type', 'range')
            ->setHtmlAttribute('min', 0)
            ->setHtmlAttribute('max', 100)
            ->setHtmlAttribute('step', 1)
            ->setOption('description', _('bad ⇔ good'))
            ->setDefaultValue(50); // TODO copy paste this for new options

        $control->addSubmit('submit', _('Send rating'))->onClick[] = function (SubmitButton $button) {
            $this->handleForm($button->getForm());
        };
        $control->addSubmit('skip', _('Skip rating'))->setAttribute('class', 'btn btn-secondary')->onClick[] = function () {
            $this->getPresenter()->redirect(':Game:Task:default');
        };
        return $control;
    }

    public function render(): void {
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
        parent::render();
    }

    /**
     * @param \Nette\Forms\Form $form
     * @throws AbortException
     */
    private function handleForm(\Nette\Forms\Form $form) {
        $values = $form->getValues();
        try {
            $this->explorer->table('rating')->insert([
                'team_id' => $this->team['id_team'],
                'task_id' => $this->taskId,
                'rating' => $values['rating'],
            ]);
            $this->getPresenter()->flashMessage(_('Your rating has been saved'), 'success');
        } catch (UniqueConstraintViolationException $exception) {
            $this->getPresenter()->flashMessage(_('You have already rated this task'), 'danger');
        } catch (Throwable $exception) {
            $this->getPresenter()->flashMessage(_('You can not rate this task right now'), 'danger');
        }
        $this->getPresenter()->redirect(':Game:Task:default');
    }
}
