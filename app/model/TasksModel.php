<?php

class TasksModel extends AbstractModel {

    public function find($id) {
        $this->checkEmptiness($id, "id");
        return $this->findAll()->where("[id_task] = %i", $id)->fetch();
    }

    /**
     * @return DibiDataSource
     */
    public function findAll() {
        return $this->getConnection()->dataSource("SELECT * FROM [view_task]");
    }

    /**
     * @return DibiDataSource
     */
    public function findPossiblyAvailable($teamId = NULL) {
        $source = $this->getConnection()->dataSource("SELECT * FROM [view_possibly_available_task]");
        return $source;
    }

    /**
     * @return DibiDataSource
     */
    public function findProblemAvailable($teamId) {
        $source = $this->getConnection()->dataSource("SELECT * FROM [view_available_task] WHERE [id_team] = %i", $teamId);
        return $source;
    }

    /**
     * @return DibiDataSource
     */
    public function findSubmitAvailable($teamId) {
        $source = $this->getConnection()->dataSource("SELECT * FROM [view_submit_available_task] WHERE [id_team] = %i", $teamId);

        // Find solved tasks
        $solved = Interlos::answers()
                ->findAllCorrect()
                ->where("[id_team] = %i", $teamId)
                ->fetchPairs("id_task", "id_task");
        // Remove solved tasks from the source
        if (!empty($solved)) {
            $source->where("[id_task] NOT IN %l", $solved);
        }
        return $source;
    }

    public function findAllStats() {
        return $this->getConnection()->dataSource("SELECT * FROM [tmp_task_stat]");
    }

    public function insert($name, $number, $serie, $type, $code) {
        $this->checkEmptiness($name, "name");
        $this->checkEmptiness($number, "number");
        $this->checkEmptiness($serie, "serie");
        $this->checkEmptiness($type, "type");
        $this->checkEmptiness($code, "code");
        $return = $this->getConnection()->insert("task", array(
                    "name" => $name,
                    "number" => $number,
                    "serie" => $serie,
                    "type" => $type,
                    "code" => $code,
                    "inserted" => $inserted
                ))->execute();
        $this->log(NULL, "task_inserted", "The task [$name] has been inserted.");
        return $return;
    }

}
