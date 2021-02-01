<?php

namespace FOL\Components\AnswerHistory;

use FOL\Components\BaseComponent;
use FOL\Model\ORM\Models\ModelTeam;
use FOL\Model\ORM\Services\ServiceAnswer;
use Nette\DI\Container;

class AnswerHistoryComponent extends BaseComponent {

    private string $lang;
    private ModelTeam $team;
    private ServiceAnswer $serviceAnswer;

    public function __construct(Container $container, ModelTeam $team, string $lang) {
        parent::__construct($container);
        $this->team = $team;
        $this->lang = $lang;
    }

    public function injectPrimary(ServiceAnswer $serviceAnswer): void {
        $this->serviceAnswer = $serviceAnswer;
    }

    protected function beforeRender(): void {
        // Load template
        $this->template->history = $this->serviceAnswer->getTable()
            ->where('id_team', $this->team->id_team)
            ->order('inserted DESC');
        $this->template->timeFormat = 'H:i:s';//_('__time'); // TODO i18n
        $this->template->lang = $this->lang;
    }

    public function render(): void {
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'answerHistory.latte');
        parent::render();
    }
}
