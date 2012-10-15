<?php

class Frontend_BasePresenter extends Presenter {

    /** @persistent */
    public $lang; // = 'cs';

    public function setPageTitle($pageTitle) {
        $this->getTemplate()->pageTitle = $pageTitle;
    }

    // ----- PROTECTED METHODS

    protected function createComponentClock($name) {
        return new ClockComponent($this, $name);
    }

    protected function createComponentFlashMessages($name) {
        return new FlashMessagesComponent($this, $name);
    }

    protected function createTemplate() {
        $this->oldLayoutMode = false;

        $template = parent::createTemplate();
        $template->today = date("Y-m-d H:i:s");
        $template->setTranslator(new GettextTranslator($this->lang));

        return InterlosTemplate::loadTemplate($template);
    }

    protected function startUp() {
        parent::startup();
        $this->localize();


        Interlos::prepareAdminProperties();
        Interlos::createAdminMessages();
        $this->oldModuleMode = FALSE;
    }

    // -------------- l12n ------------------

    protected function localize() {
        $i18nConf = Environment::getConfig('i18n');
        $this->detectLang($i18nConf);
        $locale = GettextTranslator::$locales[$this->lang];

        setlocale(LC_MESSAGES, $locale);
        bindtextdomain('messages', $i18nConf->dir);
        bind_textdomain_codeset('messages', "utf-8");
        textdomain('messages');
    }

    protected function detectLang($i18nConf) {
        if ($this->lang === null) {
            if (array_search($this->getHttpRequest()->getUri()->host, explode(',', $i18nConf->en->hosts)) !== false) {
                $this->lang = 'en';
            } else {
                $this->lang = $this->getHttpRequest()->detectLanguage(GettextTranslator::$supportedLangs);
            }
        } 
        if (array_search($this->lang, GettextTranslator::$supportedLangs) === false) {
            $this->lang = $i18nConf->defaultLang;
        }
    }

    protected function changeViewByLang() {
        $this->setView($this->getView() . '.' . $this->lang);
    }

}