<?php

namespace FOL\Model\ORM;

use Nette\Database\Table\Selection;

class TeamsService extends AbstractService {

    const HIGH_SCHOOL = 'high_school';
    const OPEN = 'open';
    const HIGH_SCHOOL_A = 'hs_a';
    const HIGH_SCHOOL_B = 'hs_b';
    const HIGH_SCHOOL_C = 'hs_c';
    const ABROAD = 'abroad';

    public function findAllWithScore(): Selection {
        return $this->explorer->table('tmp_total_result')->order('disqualified ASC')
            ->order('activity DESC')
            ->order('score DESC')
            ->order('last_time ASC');
    }

    public static function getCategoryNames(): array {
        return [
            //self::HIGH_SCHOOL => 'Středoškoláci',
            self::HIGH_SCHOOL_A => _('Středoškoláci A'),
            self::HIGH_SCHOOL_B => _('Středoškoláci B'),
            self::HIGH_SCHOOL_C => _('Středoškoláci C'),
            //self::ABROAD => _('Zahraniční SŠ'),
            self::OPEN => _('Open'),
        ];
    }
}
