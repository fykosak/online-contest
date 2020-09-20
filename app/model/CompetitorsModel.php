<?php

namespace App\Model;

use Dibi\DataSource;

class CompetitorsModel extends AbstractModel {

    public function deleteByTeam($team) {
        $this->checkEmptiness($team, "team");
        $this->getConnection()->delete("competitor")
            ->where("[id_team] = %i", $team)
            ->execute();
        $this->log($team, "competitors_deleted", "The competitors of team [$team] have been deleted.");
    }

    public function find($id) {
        $this->checkEmptiness($id, "id");
        return $this->findAll()->where("[id_competitor] = %i", $id)->fetch();
    }

    public function findAll(): DataSource {
        return $this->getConnection()->dataSource("SELECT * FROM [view_competitor]");
    }

    public function findAllByTeam($team): DataSource {
        $this->checkEmptiness($team, "team");
        return $this->findAll()->where("[id_team] = %i", $team);
    }

    public function insert($team, $school, $name, $email, $study_year) {
        $this->checkEmptiness($team, "team");
        $this->checkEmptiness($school, "school");
        $this->checkEmptiness($name, "name");
        $return = $this->getConnection()->insert("competitor", [
            "id_team" => $team,
            "id_school" => $school,
            "name" => $name,
            "email" => $email,
            "study_year" => $study_year,
        ])->execute();
        $this->log($team, "competitor_inserted", "The new competitor [$name] has been inserted and joined to team.");
        return $return;
    }
}
