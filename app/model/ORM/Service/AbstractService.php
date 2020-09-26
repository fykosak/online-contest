<?php

namespace FOL\Model\ORM\Service;

use FOL\Model\ORM\Model\AbstractModel;
use FOL\Model\ORM\Selection\TypedTableSelection;
use Nette\Application\BadRequestException;
use Nette\Database\Connection;
use Nette\Database\Context;
use Nette\Database\IConventions;
use Nette\Database\Table\Selection;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;
use PDOException;

/**
 * Class AbstractService
 * copy-pasted ORM from FKSDB
 */
class AbstractService extends Selection {
    /** @var string|AbstractModel */
    private string $modelClassName;

    private string $tableName;

    /**
     * AbstractService constructor.
     * @param Context $connection
     * @param IConventions $conventions
     * @param string $tableName
     * @param string $modelClassName
     */
    public function __construct(Context $connection, IConventions $conventions, string $tableName, string $modelClassName) {
        $this->tableName = $tableName;
        $this->modelClassName = $modelClassName;
        parent::__construct($connection, $conventions, $tableName);
    }

    /**
     * @param array $data
     * @return AbstractModel
     * @throws BadRequestException
     */
    public function createNewModel(array $data): AbstractModel {
        $modelClassName = $this->getModelClassName();
        $data = $this->filterData($data);
        try {
            $result = $this->getTable()->insert($data);
            if ($result !== false) {
                return ($modelClassName)::createFromActiveRow($result);
            }
        } catch (PDOException $exception) {
            throw new BadRequestException('Error when storing model.', null, $exception);
        }
        $code = $this->getConnection()->getPdo()->errorCode();
        throw new BadRequestException("$code: Error when storing a model.");
    }

    /**
     * Syntactic sugar.
     *
     * @param int $key
     * @return AbstractModel|null
     */
    public function findByPrimary(int $key): ?AbstractModel {
        /** @var AbstractModel|null $result */
        $result = $this->getTable()->get($key);
        if ($result !== false) {
            return $result;
        } else {
            return null;
        }
    }


    /**
     * @param AbstractModel $model
     * @return AbstractModel|null
     */
    public function refresh(AbstractModel $model): AbstractModel {
        return $this->findByPrimary($model->getPrimary(true));
    }

    /**
     * @param AbstractModel $model
     * @param array $data
     * @return bool
     * @throws BadRequestException
     */
    public function updateModel(AbstractModel $model, array $data): bool {
        try {
            $this->checkType($model);
            $data = $this->filterData($data);
            return $model->update($data);
        } catch (PDOException $exception) {
            throw new BadRequestException('Error when storing model.', null, $exception);
        }
    }

    /**
     * Use this method to delete a model!
     * (Name chosen not to collide with parent.)
     *
     * @param AbstractModel $model
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function dispose(AbstractModel $model): void {
        $this->checkType($model);
        if ($model->delete() === false) {
            $code = $this->context->getConnection()->getPdo()->errorCode();
            throw new BadRequestException("$code: Error when deleting a model.");
        }
    }

    public function getTable(): TypedTableSelection {
        return new TypedTableSelection($this->getModelClassName(), $this->getTableName(), $this->context, $this->conventions);
    }

    public function getConnection(): Connection {
        return $this->context->getConnection();
    }

    public function getContext(): Context {
        return $this->context;
    }

    public function getConventions(): IConventions {
        return $this->conventions;
    }

    /**
     * @param AbstractModel| $model
     * @throws InvalidArgumentException
     */
    private function checkType(AbstractModel $model) {
        $modelClassName = $this->getModelClassName();
        if (!$model instanceof $modelClassName) {
            throw new InvalidArgumentException('Service for class ' . $this->getModelClassName() . ' cannot store ' . get_class($model));
        }
    }

    /** @var array|null */
    protected ?array $defaults = null;

    /**
     * Default data for the new model.
     * TODO is this really needed?
     * @return array
     */
    protected function getDefaultData(): ?array {
        if ($this->defaults == null) {
            $this->defaults = [];
            foreach ($this->getColumnMetadata() as $column) {
                if ($column['nativetype'] == 'TIMESTAMP' && isset($column['default'])
                    && !preg_match('/^[0-9]{4}/', $column['default'])) {
                    continue;
                }
                $this->defaults[$column['name']] = isset($column['default']) ? $column['default'] : null;
            }
        }
        return $this->defaults;
    }

    /**
     * Omits array elements whose keys aren't columns in the table.
     *
     * @param array|null $data
     * @return array|null
     */
    protected function filterData(?array $data): ?array {
        if ($data === null) {
            return null;
        }
        $result = [];
        foreach ($this->getColumnMetadata() as $column) {
            $name = $column['name'];
            if (array_key_exists($name, $data)) {
                $result[$name] = $data[$name];
            }
        }
        return $result;
    }

    private array $columns;

    private function getColumnMetadata(): array {
        if (!isset($this->columns)) {
            $this->columns = $this->context->getConnection()->getSupplementalDriver()->getColumns($this->getTableName());
        }
        return $this->columns;
    }

    final protected function getTableName(): string {
        return $this->tableName;
    }

    /**
     * @return string|AbstractModel
     */
    final public function getModelClassName(): string {
        return $this->modelClassName;
    }
}
