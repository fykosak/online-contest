<?php

namespace FOL\Model\ORM\Services;

use FOL\Model\ORM\Models\ModelTeam;
use Fykosak\Utils\ORM\AbstractService;
use Nette\Database\Conventions;
use Nette\Database\Explorer;
use Nette\Database\Table\Selection;

class ServiceTeam extends AbstractService {

    const OPEN = 'open';
    const HIGH_SCHOOL_A = 'hs_a';
    const HIGH_SCHOOL_B = 'hs_b';
    const HIGH_SCHOOL_C = 'hs_c';

    public function __construct(Explorer $connection, Conventions $conventions) {
        parent::__construct($connection, $conventions, 'team', ModelTeam::class);
    }

    public function findAllWithScore(): Selection {
        return $this->explorer->table('tmp_total_result')
            ->order('disqualified ASC')
            ->order('activity DESC')
            ->order('score DESC')
            ->order('last_time ASC');
    }

    public static function getCategoryNames(): array {
        return [
            self::HIGH_SCHOOL_A => _('Středoškoláci A'),
            self::HIGH_SCHOOL_B => _('Středoškoláci B'),
            self::HIGH_SCHOOL_C => _('Středoškoláci C'),
            self::OPEN => _('Open'),
        ];
    }
}
