<?php

namespace FOL\Model\ORM\Selection;

use FOL\Model\ORM\Model\AbstractModel;
use Nette\Database\Context;
use Nette\Database\IConventions;
use Nette\Database\Table\Selection;

/**
 * copy-pasted ORM from FKSDB
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class TypedTableSelection extends Selection {

    protected string $modelClassName;

    /**
     * TypedTableSelection constructor.
     * @param string $modelClassName
     * @param string $table
     * @param Context $connection
     * @param IConventions $conventions
     */
    public function __construct(string $modelClassName, string $table, Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, $table);
        $this->modelClassName = $modelClassName;
    }

    /**
     * This override ensures returned objects are of correct class.
     *
     * @param array $row
     * @return AbstractModel
     */
    protected function createRow(array $row): AbstractModel {
        $className = $this->modelClassName;
        return new $className($row, $this);
    }
}
