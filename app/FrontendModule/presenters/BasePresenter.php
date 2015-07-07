<?php

namespace App\FrontendModule\Presenters;

use Nette,
    App\Model\Translator\GettextTranslator,
    App\Model\Interlos,
    App\Tools\InterlosTemplate;

class BasePresenter extends Nette\Application\UI\Presenter {

    /** @persistent */
    public $lang; // = 'cs';
    
    /** @var string */
    private $customScript = '';
    
    /** @var \App\Model\Interlos */
    private $interlos;

    public function setPageTitle($pageTitle) {
        $this->getTemplate()->pageTitle = $pageTitle;
    }

// ----- PROTECTED METHODS

    protected function createComponentClock($name) {
        return new \ClockComponent($this, $name);
    }

    protected function createComponentFlashMessages($name) {
        return new \FlashMessagesComponent($this, $name);
    }

    protected function createTemplate() {
        //$this->oldLayoutMode = false;

        $template = parent::createTemplate();
        $template->today = date("Y-m-d H:i:s");
        $template->lang = $this->lang;
        $template->customScript = '';
        $template->setTranslator(Interlos::getTranslator());
        $template->registerHelper('i18n', 'GettextTranslator::i18nHelper');

        return InterlosTemplate::loadTemplate($template);
    }
    
    public function addCustomScript($script) {
        $this->customScript .= $script;
    }
    
    public function getCustomScript(){
        return $this->customScript;
    }
    
    /* temporary hack for DI */
    public function __construct(\App\Model\Interlos $interlos) {
        parent::__construct();
        $this->interlos = $interlos;
    }

    protected function startUp() {
        parent::startup();
        $this->machineRedirect();

        $this->localize();


        //Interlos::prepareAdminProperties();
        //Interlos::createAdminMessages();
        //$this->oldModuleMode = FALSE;
    }

// -------------- l12n ------------------

    protected function localize() {
        $i18nConf = $this->context->parameters['i18n'];
        $this->detectLang($i18nConf);
        $locale = isset(GettextTranslator::$locales[$this->lang]) ? GettextTranslator::$locales[$this->lang] : 'cs_CZ.utf-8';

        setlocale(LC_MESSAGES, $locale);
        setlocale(LC_TIME, $locale);
        bindtextdomain('messages', $i18nConf['dir']);
        bind_textdomain_codeset('messages', "utf-8");
        textdomain('messages');
    }

    protected function detectLang($i18nConf) {
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

    protected function changeViewByLang() {
        $this->setView($this->getView() . '.' . $this->lang);
    }

    // -------------- game server ------------------
    private function machineRedirect() {
        $machine=$this->context->parameters['machine'];
        if (!$machine['game']) {
            $this->redirectUrl($machine['url']);
        }
    }

}
