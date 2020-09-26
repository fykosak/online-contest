<?php

namespace FOL\Model\ORM\Model;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

/**
 * copy-pasted ORM from FKSDB
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
abstract class AbstractModel extends ActiveRow {

    /**
     * AbstractModelSingle constructor.
     * @param array $data
     * @param Selection $table
     */
    public function __construct(array $data, Selection $table) {
        parent::__construct($data, $table);
    }


    public static function createFromActiveRow(ActiveRow $row): self {
        if ($row instanceof static) {
            return $row;
        }
        return new static($row->toArray(), $row->getTable());
    }
}
