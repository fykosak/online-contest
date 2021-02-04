<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use FOL\Bootstrap;

require __DIR__ . '/../app/Bootstrap.php';

$container = Bootstrap::boot()->createContainer();
$teamsModel = $container->getByType(\FOL\Model\ORM\Services\ServiceTeam::class);
//$teams = $teamsModel->findAllWithScore()->fetchAll();

$rank = [];
$rankTotal = 1;
foreach ($teams as $team) {
    if ($team->activity != 1 || $team->disqualified == 1) {
        continue;
    }

    if (!key_exists($team->category, $rank)) {
        $rank[$team->category] = 1;
    }

    $teamId = $team->id_team;
    $score = !is_null($team->score) ? $team->score : 0;
    $rankCategory = $rank[$team->category];
    echo "UPDATE e_fyziklani_team SET points=$score, rank_category=$rankCategory, rank_total=$rankTotal WHERE e_fyziklani_team_id=$teamId;\n";

    $rankTotal++;
    $rank[$team->category]++;
}
