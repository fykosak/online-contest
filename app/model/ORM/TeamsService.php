<?php

namespace FOL\Model\ORM;

use App\model\Authentication\TeamAuthenticator;
use DateTime;
use Dibi\Connection as DibiConnection;
use Dibi\DataSource;
use Dibi\Exception;
use Dibi\Fluent;
use Dibi\NotImplementedException;
use Dibi\Row;
use Nette\Database\Context;
use Nette\DeprecatedException;

class TeamsService extends AbstractService {

    const JUNIOR_CS = 'cs_j';
    const SENIOR_CS = 'cs_s';
    const JUNIOR_HU = 'hu_j';
    const SENIOR_HU = 'hu_s';
    const JUNIOR_PL = 'pl_j';
    const SENIOR_PL = 'pl_s';
    const JUNIOR_RU = 'ru_j';
    const SENIOR_RU = 'ru_s';
    const JUNIOR_SK = 'sk_j';
    const SENIOR_SK = 'sk_s';

    protected YearsService $yearService;

    public function __construct(Context $connection, DibiConnection $dibiConnection, YearsService $yearService) {
        parent::__construct($connection, $dibiConnection);
        $this->yearService = $yearService;
    }

    /**
     * @param $id
     * @return Row|false
     * @throws Exception
     */
    public function find($id) {
        return $this->findAll()->where("[id_team] = %i", $id)->fetch();
    }

    /**
     * @return DataSource
     * @throws Exception
     */
    public function findAll(): DataSource {
        return $this->getDibiConnection()->dataSource("SELECT * FROM [view_team] WHERE [id_year] = %i", $this->yearService->findCurrent()->id_year)
            ->orderBy('category')
            ->orderBy('inserted');
    }

    /**
     * @param $email
     * @return Row|false
     * @throws Exception
     */
    public function findByEmail($email) {
        return $this->getDibiConnection()->dataSource("SELECT [wt].* FROM [view_team] [wt] JOIN [view_competitor] [wc] USING([id_team]) WHERE [wc].[email] = %s", $email)->fetch();
    }

    /**
     * @return DataSource
     * @throws Exception
     */
    public function findAllWithScore(): DataSource {
        return $this->getDibiConnection()->dataSource("SELECT * FROM [tmp_total_result]");
    }

    /**
     * @param $name
     * @param $email
     * @param $category
     * @param $password
     * @param $address
     * @return int
     * @throws Exception
     */
    public function insert($name, $email, $category, $password, $address) {
        $passwordHash = TeamAuthenticator::passwordHash($password);
        $this->getDibiConnection()->insert("team", [
            "name" => $name,
            "email" => $email,
            "category" => $category,
            "password" => $passwordHash,
            "address" => $address,
            "inserted" => new DateTime(),
            "id_year" => $this->yearService->findCurrent()->id_year,
        ])->execute();
        $return = $this->getDibiConnection()->insertId();
        $this->log($return, "team_inserted", "The team [$name] has been inserted.");
        return $return;
    }


    public function update(array $changes): Fluent {
        return $this->getDibiConnection()->update("team", $changes);
    }

    /**
     *   Open (staří odkudkoliv - pokazí to i jeden člen týmu)
     *   Zahraniční
     *   ČR - A - (3,4]
     *   ČR - B - (2,3] - max. 2 ze 4. ročníku
     *   ČR - C - [0,2] - nikdo ze 4. ročníku, max. 2 z 3 ročníku
     */
    public function getCategory($competitors) {
        throw new DeprecatedException();
    }

    public function getCategoryNames() {
        return [
            self::JUNIOR_CS => _("Junior") . " Czech Republic",
            self::SENIOR_CS => _("Senior") . " Czech Republic",
            self::JUNIOR_HU => _("Junior") . " Hungary",
            self::SENIOR_HU => _("Senior") . " Hungary",
            self::JUNIOR_PL => _("Junior") . " Poland",
            self::SENIOR_PL => _("Senior") . " Poland",
            self::JUNIOR_RU => _("Junior") . " Russia",
            self::SENIOR_RU => _("Senior") . " Russia",
            self::JUNIOR_SK => _("Junior") . " Slovakia",
            self::SENIOR_SK => _("Senior") . " Slovakia",
        ];
    }

    protected function getTableName(): string {
        return 'teams';
    }
}
