<?php

namespace FOL\Model\ORM\Services;

use FOL\Model\ORM\Models\ModelChat;
use Fykosak\Utils\ORM\AbstractService;
use Fykosak\Utils\ORM\TypedTableSelection;
use Nette\Database\Conventions;
use Nette\Database\Explorer;

class ServiceChat extends AbstractService {

    public function __construct(Explorer $connection, Conventions $conventions) {
        parent::__construct($connection, $conventions, 'chat', ModelChat::class);
    }

    public function getAll(?string $lang = null): TypedTableSelection {
        $selection = $this->getTable();

        if ($lang) {
            $selection->where('lang', $lang);
        }
        return $selection;
    }

    public function getAllRoot(?string $lang = null): TypedTableSelection {
        return $this->getAll($lang)->where('id_parent IS NULL');
    }

    public function getDescendants(int $parentId, ?string $lang = null): TypedTableSelection {
        return $this->getAll($lang)->where('id_parent', $parentId);
    }
}
