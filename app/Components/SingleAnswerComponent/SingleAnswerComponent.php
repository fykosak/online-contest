<?php

namespace FOL\Components;

use BaseForm;
use FOL\Model\Task\Factory\TaskFactory;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\DI\Container;

class SingleAnswerComponent extends BaseComponent {
    protected TaskFactory $taskFactory;

    /**
     * SingleAnswerComponent constructor.
     * @param Container $container
     * @param int $taskId
     * @throws BadRequestException
     */
    public function __construct(Container $container, int $taskId) {
        parent::__construct($container);
        $this->createFactory($taskId);
    }

    /**
     * @param int $taskId
     * @return void
     * @throws BadRequestException
     */
    protected function createFactory(int $taskId): void {
        $factory = $this->getContext()->getService('tasks.fol2020.' . $taskId);
        if (!$factory instanceof TaskFactory) {
            throw new BadRequestException();
        }
        $this->taskFactory = $factory;
    }

    public function render(): void {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'singleAnswer.latte');
        parent::render();
    }

    protected function createComponentForm(): Form {
        $control = new BaseForm($this->getContext());
        $control->addComponent($this->taskFactory->createContainer('cs'), 'answer');
        $control->addSubmit('submit', _('Submit'));

        $control->onSuccess[] = function (Form $form) {
            $status = $this->taskFactory->getAnswerFactory()->validate($form->getValues(true)['answer']);
            $this->flashMessage($status, 'info');
        };
        return $control;
    }

    protected function getTemplateFile(): string {
        return __DIR__ . 'singleAnswer.latte';
    }
}
