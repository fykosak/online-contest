<?php

namespace FOL\Model\Card;

use Dibi\Exception;
use FOL\Model\ORM\Models\ModelAnswerOptions;
use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\Services\ServiceAnswerOptions;
use Fykosak\Utils\Localization\GettextTranslator;
use Fykosak\Utils\Logging\Logger;
use Nette\Forms\Container;
use Nette\Utils\Html;

class OptionsCard extends Card {

    protected ServiceAnswerOptions $serviceAnswerOptions;

    public function injectPrimary(ServiceAnswerOptions $serviceAnswerOptions): void {
        $this->serviceAnswerOptions = $serviceAnswerOptions;
    }

    protected function innerHandle(Logger $logger, array $values): void {
        // TODO: Implement innerHandle() method.
    }

    public function getType(): string {
        return 'options';
    }

    public function getTitle(): string {
        return _('4 options');
    }

    public function getDescription(): Html {
        // TODO: Implement getDescription() method.
        return Html::el('span')->addText('TODO');
    }

    /**
     * @param Container $container
     * @param string $lang
     * @throws Exception
     */
    public function decorateFormContainer(Container $container, string $lang): void {
        $items = [];
        foreach ($this->getTasks() as $task) {
            // TODO has every answer options?
            $items[$task->id_task] = $task['name_' . $lang];
        }
        $container->addSelect('task', _('Task'), $items);
    }

    public function checkRequirements(): void {
        parent::checkRequirements();
        // TODO: Implement isInnerAvailable() method.
    }

    protected function innerRenderUsage(string $lang, Html $mainContainer): void {
        $data = $this->deserializeData();
        /** @var ModelTask $task */
        $task = $this->serviceTask->findByPrimary($data['task']);
        /** @var ModelAnswerOptions $answerOptions */
        $answerOptions = $this->serviceAnswerOptions->findByPrimary($task->id_task);
        $container = Html::el('ol');
        for ($i = 1; $i <= 4; $i++) {
            $container->addHtml($this->addOption($answerOptions, $i, $lang));
        }
        $mainContainer->addHtml($container);

    }

    private function addOption(ModelAnswerOptions $answerOptions, int $count, string $lang): Html {
        return Html::el('li')->addHtml(GettextTranslator::i18nHelper($answerOptions, 'option_' . $count, $lang));
    }
}
