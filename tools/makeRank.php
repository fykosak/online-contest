<?php

use FOL\Bootstrap;
use FOL\Model\FOF2021ScoreStrategy;
use FOL\Model\ORM\Models\ModelTeam;
use FOL\Model\ORM\Services\ServiceTeam;

require __DIR__ . '/../app/Bootstrap.php';

$container = Bootstrap::boot()->createContainer();
$serviceTeam = $container->getByType(ServiceTeam::class);
$scoreStrategy = $container->getByType(FOF2021ScoreStrategy::class);
echo '<pre>';

$sortCB = function (array $a, array $b) {
    if ($b['score'] < $a['score']) {
        return -1;
    }
    if ($b['score'] > $a['score']) {
        return +1;
    }
    return $a['bonus'] <=> $b['bonus'];
};
$data = [];
/** @var ModelTeam $team */
foreach ($serviceTeam->getTable()->where('category != ', 'open') as $team) {
    $bonus = $scoreStrategy->getBonusForTeam($team);
    $data[] = [
        'model' => $team,
        'rank_total' => null,
        'rank_category' => null,
        'bonus' => $scoreStrategy->getBonusForTeam($team),
        'score' => $bonus + $team->related('task_state')->sum('points'),
    ];
}
usort($data, $sortCB);
foreach ($data as $key => &$datum) {
    $datum['rank_total'] = ($key + 1);
    // var_dump($datum);
    // echo 'UPDATE e_fyziklani_team set rank_total = ' . ($key + 1) . ' where e_fyziklani_team_id = ' . $datum['model']->id_team . ';' . "\n";
}
/*
foreach ($data as $key => $datum) {
    // var_dump($datum);
    echo 'UPDATE e_fyziklani_team set points = ' . ($datum['score']) . ' where e_fyziklani_team_id = ' . $datum['model']->id_team . ';' . "\n";
}*/

foreach (ServiceTeam::getCategoryNames() as $key => $name) {
    $cData = [];
    foreach ($serviceTeam->getTable()->where('category', $key) as $team) {
        $bonus = $scoreStrategy->getBonusForTeam($team);
        $cData[] = [
            'model' => $team,
            'bonus' => $scoreStrategy->getBonusForTeam($team),
            'score' => $bonus + $team->related('task_state')->sum('points'),
        ];
    }
    usort($cData, $sortCB);
    foreach ($cData as $index => $datum) {
        foreach ($data as &$team) {
            if ($team['model']->id_team === $datum['model']->id_team) {
                $team['rank_category'] = $index + 1;
            }
        }
    }
}
foreach ($data as $team) {
    echo 'UPDATE e_fyziklani_team 
set rank_category = ' . $team['rank_category'] . ',
points = ' . $team['score'] . ',
rank_total = ' . $team['rank_total'] . '
where e_fyziklani_team_id = ' . $team['model']->id_team . ';' . "\n";
}



