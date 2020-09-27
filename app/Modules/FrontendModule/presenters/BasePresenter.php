<?php

namespace FOL\Modules\FrontendModule\Presenters;

use DataNotFoundException;
use Dibi\Exception;
use Dibi\Row;
use FlashMessagesComponent;
use App\Model\Translator\GettextTranslator;
use App\Tools\InterlosTemplate;
use FOL\Components\Navigation\Navigation;
use FOL\Components\Navigation\NavItem;
use FOL\Model\ORM\TeamsService;
use FOL\Model\ORM\YearsService;
use Nette\Application\AbortException;
use Nette\Application\UI\ITemplate;
use Nette\Localization\ITranslator;
use NotificationMessagesComponent;
use Tracy\Debugger;

abstract class BasePresenter extends \FOL\Modules\Core\BasePresenter {

    /** @persistent */
    public $lang; // = 'cs';

    private string $customScript = '';

    private ?Row $loggedTeam;

    public YearsService $yearsService;

    protected TeamsService $teamsService;

    protected ITranslator $translator;

    public function injectServices(YearsService $yearsService, TeamsService $teamsService, ITranslator $translator): void {
        $this->yearsService = $yearsService;
        $this->teamsService = $teamsService;
        $this->translator = $translator;
    }

    public function setPageTitle($pageTitle): void {
        $this->getTemplate()->pageTitle = $pageTitle;
    }

// ----- PROTECTED METHODS

    protected function createComponentFlashMessages(): FlashMessagesComponent {
        return new FlashMessagesComponent($this->getContext());
    }

    protected function createComponentNotificationMessages(): NotificationMessagesComponent {
        return new NotificationMessagesComponent($this->getContext());
    }

    /**
     * @return ITemplate
     * @throws DataNotFoundException
     */
    protected function createTemplate(): ITemplate {
        //$this->oldLayoutMode = false;

        $template = parent::createTemplate();
        $template->today = date("Y-m-d H:i:s");
        $template->lang = $this->lang;
        $template->customScript = '';
        $template->setTranslator($this->translator);
        $template->getLatte()->addFilter('i18n', '\App\Model\Translator\GettextTranslator::i18nHelper');

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
     * @throws Exception
     */
    protected function startUp(): void {
        parent::startup();
        $this->machineRedirect();
        $this->localize();

        /** @var Navigation $navigation */
        $navigation = $this->getComponent('navigation');

        $navigation->addNavItem(new NavItem(':Public:Default:lastYears', [], _('Archiv'), 'visible-sm-inline glyphicon glyphicon-compressed'));
        $navigation->addNavItem(new NavItem(':Public:Default:rules', [], _('Pravidla'), 'visible-sm-inline glyphicon glyphicon-exclamation-sign'));
        $navigation->addNavItem(new NavItem(':Public:Default:faq', [], _('FAQ'), 'visible-sm-inline glyphicon glyphicon-question-sign'));
        $navigation->addNavItem(new NavItem(':Public:Default:howto', [], _('Návod'), 'visible-sm-inline glyphicon glyphicon-info-sign'));

        if ($this->yearsService->isRegistrationStarted()) {
            $navigation->addNavItem(new NavItem(':Public:Default:chat', [], _('Fórum'), 'visible-sm-inline glyphicon glyphicon-comment'));
            $navigation->addNavItem(new NavItem(':Public:Team:list', [], _('Týmy'), 'visible-sm-inline glyphicon glyphicon-list'));
            if ($this->yearsService->isGameStarted()) {
                $navigation->addNavItem(new NavItem(':Public:Stats:default', [], _('Výsledky'), 'visible-sm-inline glyphicon glyphicon-stats'));
                $navigation->addNavItem(new NavItem(':Frontend:Noticeboard:default', [], _('Nástěnka'), 'visible-sm-inline glyphicon glyphicon-pushpin'));
                if ($this->getUser()->isLoggedIn()) {
                    $navigation->addNavItem(new NavItem(':Game:Game:default', [], _('Hra'), 'visible-sm-inline glyphicon glyphicon-tower'));
                }
            }
        }

        if ($this->yearsService->isRegistrationActive()) {
            if (!$this->getUser()->isLoggedIn()) {
                $navigation->addNavItem(new NavItem(':Public:Team:registration', [], _('Registrace'), 'visible - sm - inline glyphicon glyphicon-edit'));
            }
        }
        if ($this->yearsService->isRegistrationStarted()) {
            if (!$this->getUser()->isLoggedIn()) {
                $navigation->addNavItem(new NavItem(':Public:Auth:login', [], _('Přihlásit se'), 'visible-sm-inline glyphicon glyphicon-log-in'));
            } else {
                $navigation->addNavItem(new NavItem(':Public:Auth:logout', [], _('Odhlásit se'), 'visible-sm-inline glyphicon glyphicon-log-out'));
            }
        }
    }

// -------------- l12n ------------------

    protected function localize(): void {
        $i18nConf = $this->context->parameters['i18n'];
        $this->detectLang($i18nConf);
        $locale = isset(GettextTranslator::$locales[$this->lang]) ? GettextTranslator::$locales[$this->lang] : 'cs_CZ.utf-8';

        putenv("LANGUAGE=$locale");
        setlocale(LC_MESSAGES, $locale);
        setlocale(LC_TIME, $locale);
        bindtextdomain('messages', $i18nConf['dir']);
        bind_textdomain_codeset('messages', "utf-8");
        textdomain('messages');
    }

    protected function detectLang($i18nConf): void {
        if ($this->lang === null) {
            if (array_search($this->getHttpRequest()->getUrl()->host, explode(',', $i18nConf['en']['hosts'])) !== false) {
                $this->lang = 'en';
            } else {
                $this->lang = $this->getHttpRequest()->detectLanguage(GettextTranslator::$supportedLangs);
            }
        }
        if (array_search($this->lang, GettextTranslator::$supportedLangs) === false) {
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

    /**
     * @return Row|null
     * @throws Exception
     */
    public function getLoggedTeam(): ?Row {
        if (!isset($this->loggedTeam)) {
            if ($this->getUser()->isLoggedIn()) {
                $this->loggedTeam = $this->teamsService->find($this->getUser()->getIdentity()->id_team) ?: null;
            } else {
                $this->loggedTeam = null;
            }
        }
        return $this->loggedTeam;
    }

    protected function createComponentNavigation(): Navigation {
        return new Navigation($this->getContext());
    }
}
