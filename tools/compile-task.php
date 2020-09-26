<?php

use FOL\Bootstrap;
use Nette\Database\Context;

require __DIR__ . '/../app/bootstrap.php';

$container = Bootstrap::boot()->createContainer();
/** @var Context $context */
$context = $container->getByType(Context::class);

foreach ($context->table('task') as $taskRow) {

}
