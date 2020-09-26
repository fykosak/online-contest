<?php

namespace FOL\Model\ORM\Model;

use FOL\Model\AnswerValidation\Factory\IValidationFactory;

/**
 * Class ModelTeam
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ModelTeam extends AbstractModel {

    public function hasTimeout(): bool {
        return $this->related('answer')
                ->where('created>?', time() - 60)
                ->where('state NOT', IValidationFactory::ANSWER_CORRECT
                )->count() > 0;
    }
}
