<?php

namespace FOL\Model\ORM\Models;

use DateTimeInterface;
use Fykosak\Utils\ORM\AbstractModel;

/**
 * @property-read int id_group
 * @property-read int id_year
 * @property-read DateTimeInterface to_show
 * @property-read string type
 * @property-read string code_name
 * @property-read string text_cs
 * @property-read string text_en
 * @property-read bool allow_zeroes
 * @property-read DateTimeInterface inserted
 * @property-read DateTimeInterface updated
 */
final class ModelGroup extends AbstractModel {

    public function getActivePeriod(): ?ModelPeriod {
        $row = $this->related('period')->where('begin <= NOW() AND end > NOW()')->fetch();
        return $row ? ModelPeriod::createFromActiveRow($row) : null;
    }

    public function getColorByGroup(): string {
        switch ($this->id_group) {
            default:
            case 1:
                return '#00f';
            case 2:
                return '#0f0';
            case 3:
                return '#f00';
            case 4:
                return '#ff0';
            case 5:
                return '#f0f';
            case 6:
                return '#0ff';
        }
    }

}
