<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use FOL\Bootstrap;

$event_id = 122;

require __DIR__ . '/../app/Bootstrap.php';
$container = Bootstrap::boot()->createContainer();
$teamsModel = $container->getByType(\FOL\Model\ORM\Services\ServiceTeam::class);
$teams = $teamsModel->findAllWithScore()->fetchAll();

foreach ($teams as $team) {
    $teamId = $team->id_team;
    $status = $team->activity == 1 ? 'participated' : 'missed';
    if ($team->disqualified == 1) {
        $status = 'disqualified';
    }
    echo "UPDATE e_fyziklani_team SET status='$status' WHERE e_fyziklani_team_id=$teamId;\n";
}

echo "UPDATE event_participant ep INNER JOIN e_fyziklani_participant efp ON ep.event_participant_id=efp.event_participant_id "
    . "INNER JOIN e_fyziklani_team eft ON eft.e_fyziklani_team_id=efp.e_fyziklani_team_id "
    . "SET ep.status=eft.status WHERE ep.event_id=$event_id;\n";
