<?php

namespace FOL\Model\ORM;

use Dibi\DataSource;
use Dibi\Row;

interface IService {

    function find(int $id): ?Row;

    function findAll(): DataSource;

}
