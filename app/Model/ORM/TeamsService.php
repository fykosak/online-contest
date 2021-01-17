<?php

namespace FOL\Model\ORM;

use FOL\model\Authentication\TeamAuthenticator;
use DateTime;
use Dibi\Connection as DibiConnection;
use Dibi\DataSource;
use Dibi\Exception;
use Dibi\Fluent;
use Dibi\Row;
use FOL\Model\ORM\Services\ServiceYear;
use Nette\Database\Explorer;

class TeamsService extends AbstractService {

    const HIGH_SCHOOL = 'high_school';
    const OPEN = 'open';
    const HIGH_SCHOOL_A = 'hs_a';
    const HIGH_SCHOOL_B = 'hs_b';
    const HIGH_SCHOOL_C = 'hs_c';
    const ABROAD = 'abroad';

    protected ServiceYear $serviceYear;

    public function __construct(Explorer $explorer, DibiConnection $dibiConnection, ServiceYear $serviceYear) {
        parent::__construct($explorer, $dibiConnection);
        $this->serviceYear = $serviceYear;
    }

    /**
     * @param $id
     * @return Row|null
     * @throws Exception
     */
    public function find($id): ?Row {
        return $this->findAll()->where('[id_team] = %i', $id)->fetch();
    }

    /**
     * @return DataSource
     * @throws Exception
     */
    public function findAll(): DataSource {
        return $this->getDibiConnection()->dataSource('SELECT * FROM [view_team] WHERE [id_year] = %i', $this->serviceYear->getCurrent()->id_year)
            ->orderBy('category')
            ->orderBy('inserted');
    }

    /**
     * @param string $email
     * @return Row|null
     * @throws Exception
     */
    public function findByEmail(string $email): ?Row {
        return $this->getDibiConnection()->dataSource('SELECT [wt].* FROM [view_team] [wt] JOIN [view_competitor] [wc] USING([id_team]) WHERE [wc].[email] = %s', $email)->fetch();
    }

    /**
     * @return DataSource
     * @throws Exception
     */
    public function findAllWithScore(): DataSource {
        return $this->getDibiConnection()->dataSource('SELECT * FROM [tmp_total_result]')
            ->orderBy('disqualified', 'ASC')
            ->orderBy('activity', 'DESC')
            ->orderBy('score', 'DESC')
            ->orderBy('last_time', 'ASC');
    }

    /**
     * @param string $name
     * @param string $email
     * @param string $category
     * @param string $password
     * @param string $address
     * @return int
     * @throws Exception
     */
    public function insert(string $name, string $email, string $category, string $password, string $address): int {
        $passwordHash = TeamAuthenticator::passwordHash($password);
        $this->getDibiConnection()->insert('team', [
            'name' => $name,
            'email' => $email,
            'category' => $category,
            'password' => $passwordHash,
            'address' => $address,
            'inserted' => new DateTime(),
            'id_year' => $this->serviceYear->getCurrent()->id_year,
        ])->execute();
        $return = $this->getDibiConnection()->getInsertId();
        $this->log($return, 'team_inserted', 'The team [$name] has been inserted.');
        return $return;
    }

    public function update(array $changes): Fluent {
        return $this->getDibiConnection()->update('team', $changes);
    }

    public function getCategoryNames(): array {
        return [
            //self::HIGH_SCHOOL => 'Středoškoláci',
            self::HIGH_SCHOOL_A => _('Středoškoláci A'),
            self::HIGH_SCHOOL_B => _('Středoškoláci B'),
            self::HIGH_SCHOOL_C => _('Středoškoláci C'),
            //self::ABROAD => _('Zahraniční SŠ'),
            self::OPEN => _('Open'),
        ];
    }

    protected function getTableName(): string {
        return 'teams';
    }
}
