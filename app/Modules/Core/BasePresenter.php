<?php

namespace FOL\Modules\Core;

use FOL\Model\ORM\Models\ModelTeam;
use FOL\Model\ORM\Models\ModelYear;
use FOL\Model\ORM\Services\ServiceTeam;
use FOL\Model\ORM\Services\ServiceYear;
use Fykosak\Utils\Localization\GettextTranslator;
use FOL\Components\NotificationMessages\NotificationMessagesComponent;
use FOL\Tools\InterlosTemplate;
use FOL\Components\Navigation\Navigation;
use Fykosak\Utils\Localization\UnsupportedLanguageException;
use Nette\Application\AbortException;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Template;

abstract class BasePresenter extends Presenter {

    /** @persistent */
    public ?string $lang = null;

    private string $customScript = '';

    private ?ModelTeam $loggedTeam2;


    protected GettextTranslator $translator;
    protected ServiceTeam $serviceTeam;
    public ServiceYear $serviceYear;

    private ModelYear $currentYear;

    public function injectServices( GettextTranslator $translator, ServiceTeam $serviceTeam, ServiceYear $serviceYear): void {
        $this->translator = $translator;
        $this->serviceTeam = $serviceTeam;
        $this->serviceYear = $serviceYear;
    }

    public function setPageTitle(string $pageTitle): void {
        $this->template->pageTitle = $pageTitle;
    }

// ----- PROTECTED METHODS

    protected function createComponentNotificationMessages(): NotificationMessagesComponent {
        return new NotificationMessagesComponent($this->getContext());
    }

    protected function createTemplate(): Template {
        //$this->oldLayoutMode = false;

        $template = parent::createTemplate();
        $template->today = date('Y-m-d H:i:s');
        $template->lang = $this->lang;
        $template->customScript = '';
        $template->setTranslator($this->translator);
        $template->isGameStarted = $this->getCurrentYear()->isGameStarted();
        $template->isGameEnd = $this->getCurrentYear()->isGameEnd();
        $template->getLatte()->addFilter('i18n', GettextTranslator::class . '::i18nHelper');

        return InterlosTemplate::loadTemplate($template);
    }

    public function addCustomScript(string $script): void {
        $this->customScript .= $script;
    }

    public function getCustomScript(): string {
        return $this->customScript;
    }

    /* temporary hack for DI */

    /**
     * @return void
     * @throws AbortException
     * @throws UnsupportedLanguageException
     */
    protected function startUp(): void {
        parent::startup();
        $this->machineRedirect();
        $this->localize();
    }

// -------------- l12n ------------------

    /**
     * @throws UnsupportedLanguageException
     */
    protected function localize(): void {
        $i18nConf = $this->context->parameters['i18n'];
        $this->detectLang($i18nConf);
        $this->translator->setLang($this->lang);
    }

    protected function detectLang(array $i18nConf): void {
        if (!isset($this->lang)) {
            if (array_search($this->getHttpRequest()->getUrl()->host, explode(',', $i18nConf['en']['hosts'])) !== false) {
                $this->lang = 'en';
            } else {
                $this->lang = $this->getHttpRequest()->detectLanguage($this->translator->getSupportedLanguages());
            }
        }
        if (array_search($this->lang, $this->translator->getSupportedLanguages()) === false) {
            $this->lang = $i18nConf['defaultLang'];
        }
    }

    public function getOpenGraphLang(): ?string {
        return $this->getHttpRequest()->getHeader('X-Facebook-Locale');
    }

    protected function changeViewByLang(): void {
        $this->setView($this->getView() . '.' . $this->lang);
    }

    // -------------- game server ------------------

    /**
     * @return void
     * @throws AbortException
     */
    private function machineRedirect(): void {
        $machine = $this->context->parameters['machine'];
        if (!$machine['game']) {
            $this->redirectUrl($machine['url']);
        }
    }

    public function getLoggedTeam(): ?ModelTeam {
        if (!isset($this->loggedTeam2)) {
            if ($this->getUser()->isLoggedIn()) {
                $this->loggedTeam2 = $this->serviceTeam->findByPrimary($this->getUser()->getIdentity()->id_team);
            } else {
                $this->loggedTeam2 = null;
            }
        }
        return $this->loggedTeam2;
    }

    protected function createComponentNavigation(): Navigation {
        return new Navigation($this->getContext());
    }

    protected function getCurrentYear(): ModelYear {
        if (!isset($this->currentYear)) {
            $this->currentYear = $this->serviceYear->getCurrent();
        }
        return $this->currentYear;
    }
}
