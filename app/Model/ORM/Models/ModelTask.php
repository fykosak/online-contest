<?php

namespace FOL\Model\ORM\Models;

use DateTimeInterface;
use Fykosak\Utils\Localization\GettextTranslator;
use Fykosak\Utils\ORM\AbstractModel;
use Nette\Database\Table\ActiveRow;
use Nette\InvalidArgumentException;

/**
 * @property-read int id_task
 * @property-read int id_group
 * @property-read ActiveRow group
 * @property-read int number
 * @property-read string name_cs
 * @property-read string name_en
 * @property-read string filename_cs
 * @property-read string filename_en
 * @property-read int points
 * @property-read int cancelled
 * @property-read string answer_type ('str','int','real')
 * @property-read string answer_str
 * @property-read int answer_int
 * @property-read double answer_real
 * @property-read string answer_unit
 * @property-read double real_tolerance
 * @property-read int real_sig_digits
 * @property-read DateTimeInterface inserted
 * @property-read DateTimeInterface updated
 */
class ModelTask extends AbstractModel {

    public const TYPE_STR = 'str';
    public const TYPE_INT = 'int';
    public const TYPE_REAL = 'real';

    public function getGroup(): ModelGroup {
        /** @var ModelGroup $group */
        $group = ModelGroup::createFromActiveRow($this->group);
        return $group;
    }

    public function getHint(): ?ModelTaskHint {
        $row = $this->related('task_hint')->fetch();
        return $row ? ModelTaskHint::createFromActiveRow($row) : null;
    }

    public function getOptions(): ?ModelAnswerOptions {
        $row = $this->related('answer_options')->fetch();
        return $row ? ModelAnswerOptions::createFromActiveRow($row) : null;
    }

    /**
     * @param ActiveRow|ModelTask $row
     * @return array
     * TODO implement task label
     */
    public static function __toArray(ActiveRow $row): array {
        return [
            'taskId' => $row->id_task,
            'group' => $row->id_group,
            'number' => $row->number,
            'name' => [
                'cs' => $row->name_cs,
                'en' => $row->name_en,
            ],
        ];
    }

    public function getLabel(string $lang): string {
        return $this->getGroup()->code_name . ': ' . GettextTranslator::i18nHelper($this, 'name', $lang);
    }

    public function checkAnswer(string $solution): bool {
        switch ($this->answer_type) {
            case self::TYPE_STR:
                return $solution == $this->answer_str;
            case self::TYPE_INT:
                return $solution == $this->answer_int;
            case self::TYPE_REAL:
                return ($this->answer_real - $this->real_tolerance <= $solution) && ($solution <= $this->answer_real + $this->real_tolerance);
        }
        throw new InvalidArgumentException();
    }
}
