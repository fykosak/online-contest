<?php

namespace FOL\Model\Task\Factory;

use FOL\Model\Task\Input\InputFactory;
use FOL\Model\Task\Validation\Factory\IValidationFactory;
use Nette\Forms\Container;

class TaskFactory {

    private array $file;

    private array $name;

    private IValidationFactory $answerFactory;
    /** @var InputFactory[] */
    private array $inputFactories;

    public function setFile(array $file): void {
        $this->file = $file;
    }

    public function setName(array $name): void {
        $this->name = $name;
    }

    public function setAnswerFactory(IValidationFactory $factory): void {
        $this->answerFactory = $factory;
    }

    public function addInputFactory(int $index, InputFactory $inputFactory) {
        $this->inputFactories[$index] = $inputFactory;
    }

    public function hasFile(string $lang): bool {
        return isset($this->file[$lang]);
    }

    public function getFile(string $lang): ?string {
        return $this->file[$lang] ?? null;
    }

    public function getName(string $lang): ?string {
        return $this->name[$lang] ?? null;
    }

    public function getAnswerFactory(): IValidationFactory {
        return $this->answerFactory;
    }

    public function createContainer(string $lang): Container {
        $container = new Container();
        foreach ($this->inputFactories as $index => $inputFactory) {
            $container->addComponent($inputFactory->createInput($lang), $index);
        }
        return $container;
    }

}
