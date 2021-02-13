<?php

namespace FOL\Components\Navigation;

use FOL\Components\BaseComponent;

/**
 * Class Navigation
 * @author Michal Červeňák <miso@fykos.cz>
 */
class Navigation extends BaseComponent {
    private array $items = [];

    public function render(): void {
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'navigation.latte');
        $this->template->items = $this->items;
        $this->template->competition = $this->getContext()->getParameters()['competition'];
        $this->template->lang = $this->getPresenter()->lang;
        $this->template->supportedLangs = $this->translator->getSupportedLanguages();
        parent::render();
    }

    public function addNavItem(NavItem $item): void {
        $this->items[] = $item;
    }

    public static function mapLangToIcon(string $lang): string {
        switch ($lang) {
            case 'en':
                return 'flag-icon flag-icon-us';
            case 'cs':
                return 'flag-icon flag-icon-cz';
            default:
                return 'flag-icon flag-icon-' . $lang;
        }
    }
}
