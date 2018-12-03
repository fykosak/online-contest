<?php

namespace App\Model;

class TeamsModel extends AbstractModel {

    const HIGH_SCHOOL = 'high_school';
    const OPEN = 'open';
    const HIGH_SCHOOL_A = 'hs_a';
    const HIGH_SCHOOL_B = 'hs_b';
    const HIGH_SCHOOL_C = 'hs_c';
    const ABROAD = 'abroad';

    public function find($id) {
        $this->checkEmptiness($id, "id");
        return $this->findAll()->where("[id_team] = %i", $id)->fetch();
    }

    public function findAll() {
        return $this->getConnection()->dataSource("SELECT * FROM [view_team] WHERE [id_year] = %i", Interlos::years()->findCurrent()->id_year)
            ->orderBy('category')
            ->orderBy('inserted');
    }
    
    public function findByEmail($email) {
        return $this->getConnection()->dataSource("SELECT [wt].* FROM [view_team] [wt] JOIN [view_competitor] [wc] USING([id_team]) WHERE [wc].[email] = %s", $email)->fetch();
    }

    /**
     * @return \DibiDataSource
     */
    public function findAllWithScore() {
        return $this->getConnection()->dataSource("SELECT * FROM [tmp_total_result]")
            ->orderBy('disqualified', 'ASC')
            ->orderBy('activity', 'DESC')
            ->orderBy('score', 'DESC')
            ->orderBy('last_time', 'ASC');
    }

    public function insert($name, $email, $category, $password, $address) {
        $this->checkEmptiness($name, "name");
        $this->checkEmptiness($email, "email");
        $this->checkEmptiness($category, "category");
        $this->checkEmptiness($password, "password");
        $this->checkEmptiness($address, "address");
        $passwordHash = Authentication\TeamAuthenticator::passwordHash($password);
        $this->getConnection()->insert("team", array(
            "name" => $name,
            "email" => $email,
            "category" => $category,
            "password" => $passwordHash,
            "address" => $address,
            "inserted" => new \DateTime(),
            "id_year" => Interlos::years()->findCurrent()->id_year
        ))->execute();
        $return = $this->getConnection()->insertId();
        $this->log($return, "team_inserted", "The team [$name] has been inserted.");
        return $return;
    }

    /** @return \DibiFluent */
    public function update(array $changes) {
        return $this->getConnection()->update("team", $changes);
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
        $year = array(0, 0, 0, 0, 0); //0 - ZŠ, 1..4 - SŠ
        $abroad = 0;
        // calculate stats
        foreach ($competitors as $competitor) {
            switch ($competitor["study_year"]) {
                case 0:
                case 1:
                case 2:
                case 3:
                case 4:
                    $year[(int) $competitor["study_year"]] += 1;
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
        return array(
            //self::HIGH_SCHOOL => "Středoškoláci",
            self::HIGH_SCHOOL_A => _("Středoškoláci A"),
            self::HIGH_SCHOOL_B => _("Středoškoláci B"),
            self::HIGH_SCHOOL_C => _("Středoškoláci C"),
            //self::ABROAD => _("Zahraniční SŠ"),
            self::OPEN => _("Open"),
        );
    }

}