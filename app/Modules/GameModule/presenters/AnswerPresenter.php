<?php

namespace App\GameModule\Presenters;

use FOL\Model\AnswerValidation\Factory\IValidationFactory;
use FOL\Model\Task\Factory\TaskFactory;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;

class AnswerPresenter extends BasePresenter {
    /**
     * @var int $id
     * @persistent
     */
    public $id;

    private TaskFactory $factory;

    /**
     * @return void
     * @throws BadRequestException
     */
    public function actionEntry(): void {
        $factory = $this->getContext()->getService('tasks.fol2020.' . $this->id);
        if (!$factory instanceof TaskFactory) {
            throw new BadRequestException();
        }
        $this->factory = $factory;
    }

    protected function createComponentEntryForm(): Form {
        $control = new Form();
        $control->addComponent($this->factory->createContainer('cs'), 'answer');
        $control->addSubmit('submit', _('Submit'));

        $control->onSuccess[] = function (Form $form) {
            $status = $this->factory->getAnswerFactory()->validate($form->getValues(true)['answer']);
            $this->flashMessage($status, 'info');
        };
        return $control;
    }

}
