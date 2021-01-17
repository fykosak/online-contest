<?php

namespace FOL\Model\ORM;

use Dibi\DataSource;
use Dibi\Exception;
use Dibi\Row;

class NotificationService extends AbstractService {

    /**
     * @param $id
     * @return Row|null
     * @throws Exception
     */
    public function find(int $id): ?Row {
        return $this->findAll()->where('[id_notification] = %i', $id)->fetch();
    }

    /**
     * @param string|null $lang
     * @return DataSource
     * @throws Exception
     */
    public function findAll(?string $lang = null): DataSource {
        $dataSource = $this->getDibiConnection()->dataSource('SELECT * FROM [notification]');

        if ($lang !== null) {
            return $dataSource->where('[lang] = %s', $lang);
        }
        return $dataSource;
    }

    /**
     * @param string|null $lang
     * @return DataSource
     * @throws Exception
     */
    public function findActive(?string $lang = null): DataSource {
        return $this->findAll($lang)->where('[created] < NOW()')->orderBy('created', 'DESC');
    }

    /**
     * @param $timestamp
     * @param string|null $lang
     * @return DataSource
     * @throws Exception
     */
    public function findNew($timestamp, ?string $lang = null): DataSource {
        return $this->findActive($lang)->where('[created] > %t', $timestamp)->orderBy('created');
    }

    /**
     * @param $message
     * @param $lang
     * @return void
     * @throws Exception
     */
    public function insert(string $message, string $lang): void {
        $this->getDibiConnection()->insert('notification', [
            'message' => $message,
            'lang' => $lang,
        ])->execute();
    }

    protected function getTableName(): string {
        return 'notification';
    }
}
