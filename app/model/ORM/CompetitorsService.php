<?php

namespace FOL\Model\ORM;

use Dibi\DataSource;
use Dibi\Exception;
use Dibi\Result;
use Dibi\Row;

class CompetitorsService extends AbstractService {
    /**
     * @param $team
     * @return void
     * @throws Exception
     */
    public function deleteByTeam($team) {

        $this->getDibiConnection()->delete("competitor")
            ->where("[id_team] = %i", $team)
            ->execute();
        $this->log($team, "competitors_deleted", "The competitors of team [$team] have been deleted.");
    }

    /**
     * @param $id
     * @return Row|false
     * @throws Exception
     */
    public function find($id) {
        return $this->findAll()->where("[id_competitor] = %i", $id)->fetch();
    }

    /**
     * @return DataSource
     * @throws Exception
     */
    public function findAll(): DataSource {
        return $this->getDibiConnection()->dataSource("SELECT * FROM [view_competitor]");
    }

    /**
     * @param $team
     * @return DataSource
     * @throws Exception
     */
    public function findAllByTeam($team): DataSource {
        return $this->findAll()->where("[id_team] = %i", $team);
    }

    /**
     * @param $team
     * @param $school
     * @param $name
     * @param $email
     * @param $study_year
     * @return Result|int
     * @throws Exception
     */
    public function insert($team, $school, $name, $email, $study_year) {

        $return = $this->getDibiConnection()->insert("competitor", [
            "id_team" => $team,
            "id_school" => $school,
            "name" => $name,
            "email" => $email,
            "study_year" => $study_year,
        ])->execute();
        $this->log($team, "competitor_inserted", "The new competitor [$name] has been inserted and joined to team.");
        return $return;
    }

    protected function getTableName(): string {
        return 'competitors';
    }
}
