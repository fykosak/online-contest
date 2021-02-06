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
                return '#1f77b4';
            case 2:
                return '#ff7f0e';
            case 3:
                return '#2ca02c';
            case 4:
                return '#d62728';
            case 5:
                return '#9467bd';
            case 6:
                return '#8c564b';
            case 7:
                return '#e377c2';
            case 8:
                return '#cccccc';
        }
    }

}
