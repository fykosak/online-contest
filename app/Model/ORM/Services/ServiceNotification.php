<?php

namespace FOL\Model\ORM\Services;

use FOL\Model\ORM\Models\ModelNotification;
use Fykosak\Utils\ORM\AbstractService;
use Fykosak\Utils\ORM\TypedTableSelection;
use Nette\Database\Conventions;
use Nette\Database\Explorer;

class ServiceNotification extends AbstractService {

    public function __construct(Explorer $connection, Conventions $conventions) {
        parent::__construct($connection, $conventions, 'notification', ModelNotification::class);
    }

    public function getAll(?string $lang = null): TypedTableSelection {
        $selection = $this->getTable();
        if ($lang !== null) {
            $selection->where('lang', $lang);
        }
        return $selection;
    }

    public function getActive(?string $lang = null): TypedTableSelection {
        return $this->getAll($lang)->where('created < NOW()')->order('created DESC');
    }

    public function getNew(int $timestamp, ?string $lang = null): TypedTableSelection {
        return $this->getActive($lang)->where('created > ', $timestamp)->order('created');
    }
}