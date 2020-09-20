<?php

namespace App\FrontendModule\Presenters;

use ClockComponent;
use FlashMessagesComponent;
use App\Model\Translator\GettextTranslator;
use App\Model\Interlos;
use App\Tools\InterlosTemplate;
use Nette\Application\UI\ITemplate;
use Nette\Application\UI\Presenter;
use NotificationMessagesComponent;

class BasePresenter extends Presenter {

    /** @persistent */
    public $lang; // = 'cs';

    private string $customScript = '';

    private Interlos $interlos;

    public function __construct(Interlos $interlos) {
        parent::__construct();
        $this->interlos = $interlos;
    }

    public function setPageTitle($pageTitle): void {
        $this->getTemplate()->pageTitle = $pageTitle;
    }

// ----- PROTECTED METHODS

    protected function createComponentClock(): ClockComponent {
        return new ClockComponent();
    }

    protected function createComponentFlashMessages(): FlashMessagesComponent {
        return new FlashMessagesComponent();
    }

    protected function createComponentNotificationMessages(): NotificationMessagesComponent {
        return new NotificationMessagesComponent();
    }

    protected function createTemplate(): ITemplate {
        //$this->oldLayoutMode = false;

        $template = parent::createTemplate();
        $template->today = date("Y-m-d H:i:s");
        $template->lang = $this->lang;
        $template->customScript = '';
        $template->setTranslator(Interlos::getTranslator());
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


    protected function startUp() {
        parent::startup();
        $this->machineRedirect();

        $this->localize();


        //Interlos::prepareAdminProperties();
        //Interlos::createAdminMessages();
        //$this->oldModuleMode = FALSE;
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
    private function machineRedirect(): void {
        $machine = $this->context->parameters['machine'];
        if (!$machine['game']) {
            $this->redirectUrl($machine['url']);
        }
    }

}
