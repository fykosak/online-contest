<?php


namespace FOL\Model\ORM\Model;

/**
 * Class ModelInstance
 * @author Michal Červeňák <miso@fykos.cz>3
 * @property-read int instance_id
 * @property-read int game_id
 * @property-read \DateTimeInterface game_begin
 */
class ModelInstance extends AbstractModel {

    public function getGame(): ModelGame {
        $gameRow = $this->ref('game');
        return ModelGame::createFromActiveRow($gameRow);
    }
}
