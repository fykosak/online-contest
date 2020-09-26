<?php

namespace FOL\Model\Task\Input;

use Nette\ComponentModel\IComponent;
use Nette\Forms\Controls\TextInput;

class InputFactory {
    private array $name;

    private array $description;

    private ?string $unit = null;

    public function setDescription(array $description): void {
        $this->description = $description;
    }

    public function setName(array $name): void {
        $this->name = $name;
    }

    public function setUnit(?string $unit): void {
        $this->unit = $unit;
    }

    public function getName(string $lang): ?string {
        return $this->name[$lang] ?? null;
    }

    public function getDescription(string $lang): ?string {
        return $this->description[$lang] ?? null;
    }

    public function getUnit(): ?string {
        return $this->unit;
    }

    public function createInput(string $lang): IComponent {
        return new TextInput($this->getName($lang));
    }
}
