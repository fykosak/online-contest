<?php

namespace FOL\Model\Task\Extension;

use FOL\Model\Task\Validation\Factory\DefaultValidationFactory;
use FOL\Model\Task\Input\InputFactory;
use FOL\Model\Task\Validation\Statement\IntCheck;
use FOL\Model\Task\Validation\Statement\LogicAnd;
use FOL\Model\Task\Validation\Statement\LogicOr;
use FOL\Model\Task\Validation\Statement\LogicXOr;
use FOL\Model\Task\Validation\Statement\Not;
use FOL\Model\Task\Validation\Statement\RealCheck;
use FOL\Model\Task\Validation\Statement\StringCheck;
use FOL\Model\Task\Factory\TaskFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\ServiceDefinition;
use Nette\DI\Statement;
use Tracy\Debugger;

/**
 * Class AnswerExtension
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TaskExtension extends CompilerExtension {

    private const MAP = [
        'or' => LogicOr::class,
        'xor' => LogicXOr::class,
        'and' => LogicAnd::class,
        'not' => Not::class,
        'int' => IntCheck::class,
        'string' => StringCheck::class,
        'real' => RealCheck::class,
    ];

    public function loadConfiguration(): void {
        parent::loadConfiguration();

        foreach ($this->config as $game => $tasks) {
            foreach ($tasks as $taskId => $taskData) {
                $this->createTaskFactory($game, $taskId, $taskData);
            }
        }
    }

    private function createAnswerFactory(string $uid, array $taskData): ServiceDefinition {
        return $this->getContainerBuilder()
            ->addDefinition($this->prefix($uid . '.answer'))
            ->setFactory(DefaultValidationFactory::class, [$this->compileAnswer($taskData['answer'])]);
    }

    private function createTaskFactory(string $game, int $taskId, array $taskData): void {
        $uid = $game . '.' . $taskId;
        $answerFactory = $this->createAnswerFactory($uid, $taskData);
        $factory = $this->getContainerBuilder()->addDefinition($this->prefix($uid))
            ->setFactory(TaskFactory::class)
            ->addSetup('setName', [$taskData['name']])
            ->addSetup('setFile', [$taskData['file']])
            ->addSetup('setAnswerFactory', [$answerFactory]);

        foreach ($taskData['inputs'] as $index => $inputData) {
            Debugger::barDump($inputData);
            $inputFactory = $this->createInputFactory($uid . '.' . $index, $inputData);
            $factory->addSetup('addInputFactory', [$index, $inputFactory]);
        }
    }

    private function createInputFactory(string $uid, array $inputData): ServiceDefinition {
        return $this->getContainerBuilder()
            ->addDefinition($this->prefix($uid . '.input'))
            ->setFactory(InputFactory::class)
            ->addSetup('setName', [$inputData['name']])
            ->addSetup('setDescription', [$taskData['description'] ?? []])
            ->addSetup('setUnit', [$taskData['unit'] ?? null]);
    }

    private function compileAnswer(Statement $statement): Statement {
        $args = [];
        foreach ($statement->arguments as $argument) {
            if ($argument instanceof Statement) {
                $args[] = $this->compileAnswer($argument);
            } else {
                $args[] = $argument;
            }
        }
        if (array_key_exists($statement->entity, self::MAP)) {
            return new Statement(self::MAP[$statement->entity], $args);
        }
        return new Statement($statement->entity, $args);
    }
}
