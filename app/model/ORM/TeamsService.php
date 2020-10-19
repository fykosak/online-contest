<?php

namespace FOL\Model\ORM;

use App\model\Authentication\TeamAuthenticator;
use DateTime;
use Dibi\Connection as DibiConnection;
use Dibi\DataSource;
use Dibi\Exception;
use Dibi\Fluent;
use Dibi\Row;
use Nette\Database\Context;

class TeamsService extends AbstractService {

    const HIGH_SCHOOL = 'high_school';
    const OPEN = 'open';
    const HIGH_SCHOOL_A = 'hs_a';
    const HIGH_SCHOOL_B = 'hs_b';
    const HIGH_SCHOOL_C = 'hs_c';
    const ABROAD = 'abroad';

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
        return $this->getDibiConnection()->dataSource("SELECT * FROM [tmp_total_result]")
            ->orderBy('disqualified', 'ASC')
            ->orderBy('activity', 'DESC')
            ->orderBy('score', 'DESC')
            ->orderBy('last_time', 'ASC');
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
        // init stats
        $olds = 0;
        $year = [0, 0, 0, 0, 0]; //0 - ZŠ, 1..4 - SŠ
        $abroad = 0;
        // calculate stats
        foreach ($competitors as $competitor) {
            switch ($competitor["study_year"]) {
                case 0:
                case 1:
                case 2:
                case 3:
                case 4:
                    $year[(int)$competitor["study_year"]] += 1;
                    break;
                case 5:
                    $olds += 1;
                    break;
                case 10:
                    $abroad += 1;
                    break;
                case 11:
                    $abroad += 1;
                    $olds += 1;
                    break;
            }
        }
        // evaluate stats
        if ($olds > 0) {
            return self::OPEN;
        } elseif ($abroad > 0) {
            return self::ABROAD;
        } else { //Czech/Slovak highschoolers (or lower)
            $sum = 0;
            $cnt = 0;
            for ($y = 0; $y <= 4; ++$y) {
                $sum += $year[$y] * $y;
                $cnt += $year[$y];
            }
            $avg = $sum / $cnt;
            if ($avg <= 2 && $year[4] == 0 && $year[3] <= 2) {
                return self::HIGH_SCHOOL_C;
            } elseif ($avg <= 3 && $year[4] <= 2) {
                return self::HIGH_SCHOOL_B;
            } else {
                return self::HIGH_SCHOOL_A;
            }
        }
    }

    public function getCategoryNames() {
        return [
            //self::HIGH_SCHOOL => "Středoškoláci",
            self::HIGH_SCHOOL_A => _("Středoškoláci A"),
            self::HIGH_SCHOOL_B => _("Středoškoláci B"),
            self::HIGH_SCHOOL_C => _("Středoškoláci C"),
            //self::ABROAD => _("Zahraniční SŠ"),
            self::OPEN => _("Open"),
        ];
    }

    protected function getTableName(): string {
        return 'teams';
    }
}
