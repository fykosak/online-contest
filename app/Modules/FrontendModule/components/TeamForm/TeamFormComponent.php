<?php

/**
 * This form provides inserting and updating of the team.
 *
 * @author Jan Papousek
 */

use FOL\Modules\FrontendModule\FrontendModule;
use FOL\Model\ORM\CompetitorsService;
use FOL\Model\ORM\SchoolsService;
use FOL\Model\ORM\TeamsService;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Application\UI\InvalidLinkException;
use Nette\InvalidStateException;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
use Nette\Utils\Html;
use App\Model\Authentication\TeamAuthenticator;
use Tracy\Debugger;
use Dibi\DriverException;

class TeamFormComponent extends BaseComponent {

    const NUMBER_OF_MEMBERS = 5;
    const OTHER_SCHOOL = 'other';

    protected TeamsService $teamsService;

    protected CompetitorsService $competitorsService;

    protected SchoolsService $schoolService;

    public function injectPrimary(SchoolsService $schoolService, CompetitorsService $competitorsService, TeamsService $teamsService): void {
        $this->teamsService = $teamsService;
        $this->competitorsService = $competitorsService;
        $this->schoolService = $schoolService;
    }

    /* SUBMITTED FORMS */
    /**
     * @param Form $form
     * @return void
     * @throws AbortException
     * @throws DataNotFoundException
     * @throws DriverException
     * @throws \Dibi\Exception
     */
    public function insertSubmitted(Form $form): void {
        $values = $form->getValues();
        $competitors = $this->loadCompetitorsFromValues($values);
        if (!$competitors) {
            $this->getPresenter()->flashMessage(_("Pokoušíte se vložit školu, která již existuje."), "danger");
            return;
        }
        // Check team name and e-mail because the database consistency
        $teamExists = $this->teamsService->findAll()->where("[name] = %s", $values["team_name"], " OR [email] = %s", $values["email"])->count();
        if ($teamExists != 0) {
            $this->getPresenter()->flashMessage(_("Tým se stejným názvem nebo kontaktním e-mailem již existuje"), "danger");
            return;
        }
        try {
            dibi::begin();
            // calculate category
            $values["category"] = $this->teamsService->getCategory($competitors);
            $names = $this->teamsService->getCategoryNames();
            $this->getPresenter()->flashMessage(sprintf(_("Přiřazena kategorie %s."), $names[$values["category"]]));
            // Insert team
            $insertedTeam = $this->teamsService->insert(
                $values["team_name"], $values["email"], $values["category"], $values["password"], $values["address"]
            );
            // Send e-mail
            $template = $this->createTemplate();
            $template->setFile(FrontendModule::getModuleDir() . "/templates/mail/registration." . $this->getPresenter()->lang . ".latte");
            $template->team_name = $values["team_name"];
            $template->password = $values["password"];
            $template->category = $names[$values["category"]];

            $mailConfig = $this->getPresenter()->context->parameters['mail'];
            $mail = new Message();
            $mail->setHtmlBody($template);
            $mail->addTo($values["email"]);
            $mail->setFrom($mailConfig['info'], $mailConfig['name']);
            $mail->setSubject(_("FoL registrace"));
            try {
                $mailer = new SendmailMailer;
                $mailer->send($mail);
            } catch (InvalidStateException $e) {
                $this->getPresenter()->flashMessage(_("Potvrzovací e-mail se nepodařilo odeslat."), "danger");
            }
            // Redirect
            $this->insertCompetitorsFromValues($insertedTeam, $values);
            dibi::commit();
            $this->getPresenter()->flashMessage(sprintf(_("Tým %s byl úspěšně zaregistrován."), $values["team_name"]), "success");
            $this->getPresenter()->redirect("Default:login");
        } catch (DriverException $e) {
            $this->getPresenter()->flashMessage(_("Chyba při práci s databází."), "danger");
            //Debug::processException($e);
            Debugger::log($e);
            throw $e; //TODO neccessary?
        }
    }

    /**
     * @param Form $form
     * @return void
     * @throws \Dibi\Exception
     * @throws AbortException
     */
    public function updateSubmitted(Form $form): void {
        $values = $form->getValues();
        try {
            // Update the team
            $changes = [
                "email" => null,
                "address" => $values["address"],
            ];

            if ($this->yearsService->isRegistrationActive()) {
                $changes["category"] = $this->teamsService->getCategory($this->loadCompetitorsFromValues($values));
                $names = $this->teamsService->getCategoryNames();
                $this->getPresenter()->flashMessage(sprintf("Přiřazena kategorie %s.", $names[$changes["category"]]));
            } else {
                $this->getPresenter()->flashMessage(_("Kategorie zůstala stejná jako v průbehu registrace."), "success");
            }

            if (!empty($values["password"])) {
                $changes["password"] = TeamAuthenticator::passwordHash($values["password"]);
            }
            $this->teamsService->update($changes)->where("[id_team] = %i", $values["id_team"])->execute();
            // Update competitors
            $this->competitorsService->deleteByTeam($values["id_team"]);
            $this->insertCompetitorsFromValues($values["id_team"], $values);
            // Success
            $this->getPresenter()->flashMessage(_("Tým byl úspěšně aktualizován."), "success");
            $this->getPresenter()->redirect("this");
        } catch (InvalidArgumentException $e) {
            $this->getPresenter()->flashMessage(_("Tým musí mít alespoň jednoho člena."), "danger");
            Debugger::log($e);
        } catch (DuplicityException $e) {
            $this->getPresenter()->flashMessage(_("Daný tým již existuje."), "danger");
            Debugger::log($e);
        } catch (DriverException $e) {
            $this->getPresenter()->flashMessage(_("Chyba při práci s databází."), "danger");
            Debugger::log($e);
        }
    }

    /* PROTECTED METHODS */
    /**
     * @return BaseForm
     * @throws \Dibi\Exception
     * @throws InvalidLinkException
     */
    protected function createComponentTeamForm(): BaseForm {
        $form = new BaseForm($this->getContext());

        $form->addGroup("Tým");

        // Team name
        $form->addText("team_name", "Název týmu")->addRule(Form::FILLED, "Název týmu musí být vyplněn");

        // Password
        $form->addPassword("password", "Heslo");
        $form->addPassword("password_check", "Kontrola hesla")
            ->addConditionOn($form["password"], Form::FILLED)
            ->addRule(Form::EQUAL, "Heslo a kontrola hesla se neshodují.", $form["password"]);


        // Contatcs

        $form->addTextArea("address", "Kontaktní adresa", 35, 4)
            ->addRule(Form::FILLED, "Zadejte prosím kontatní adresu.")
            ->setOption("description", _("Pro zaslání případné odměny."));
        if (!$this->getPresenter()->user->isLoggedIn()) {
            $desc = Html::el();
            $desc->addText(_('Přečetl jsem si '));
            $desc->addHtml(Html::el('a')->href($this->getPresenter()->link('Default:rules'))->setText(_('pravidla soutěže')));
            $desc->addText('.');

            $form->addCheckbox('understand', null)
                ->addRule(Form::EQUAL, 'Je nutno si nejdříve přečíst pravidla.', true)
                ->setOption("description", $desc);
        }

        $schools = $this->schoolService->findAll()->orderBy("name")->fetchPairs("id_school", "name");
        $schools = [null => _("Nevyplněno")] + $schools + [self::OTHER_SCHOOL => _("Jiná")];
        $study_years = [
            _("ČR/SR") => [
                "0" => _("ZŠ"),
                "1" => _("1. ročník SŠ"),
                "2" => _("2. ročník SŠ"),
                "3" => _("3. ročník SŠ"),
                "4" => _("4. ročník SŠ"),
                "5" => _("ostatní")],
            _("zahraničí") => [
                "10" => _("střední škola"),
                "11" => _("ostatní"),
            ],
        ];

        // Members
        for ($i = 1; $i <= self::NUMBER_OF_MEMBERS; $i++) {
            $form->addGroup(sprintf(_("%d. člen"), $i));
            $form->addText("competitor_name_" . $i, "Jméno");
            $form->addSelect("school_" . $i, "Škola", $schools)
                ->addConditionOn($form["competitor_name_" . $i], Form::FILLED)
                ->addRule(~Form::EQUAL, sprintf(_("U %d. člena je vyplněno jméno, ale není u něj vyplněna škola."), $i), null)
                ->endCondition()
                ->addCondition(Form::EQUAL, self::OTHER_SCHOOL)
                ->toggle("frm" . $name . "-" . "otherschool_$i")
                ->toggle("frm" . $name . "-" . "otherschool_$i-label");
            $form->addText("otherschool_" . $i, "Jiná škola")
                ->addConditionOn($form["competitor_name_" . $i], Form::FILLED)
                ->addConditionOn($form["school_" . $i], Form::EQUAL, self::OTHER_SCHOOL)
                ->addRule(Form::FILLED, sprintf(_("U %d. člena je vyplněno jméno, ale není u něj vyplněna škola."), $i))
                ->addRule(Form::MIN_LENGTH, sprintf(_("U %d. člena musí být název školy alespoň %d znaků."), $i, 5), 5);
            $form["otherschool_" . $i]->getLabelPrototype()->id = "frm" . $name . "-" . "otherschool_$i-label";
            $email = $form->addText("email_$i", "Email");
            $email->addCondition(~Form::EQUAL, "")
                ->addRule(Form::EMAIL, sprintf(_("U %d. člena není platná e-mailová adresa."), $i));
            $email->addConditionOn($form["competitor_name_" . $i], Form::FILLED)
                ->addRule(Form::FILLED, sprintf(_("U %d. člena je vyplněno jméno, ale není u něj vyplněn e-mail."), $i));

            $schoolElement = $form->addHidden("study_year_$i");
            if (!$this->yearsService->isRegistrationActive()) {
                $schoolElement->setDisabled();
                $form->addHidden("study_year_hid_$i");
            }
            if ($i == 1) {
                $form["competitor_name_" . $i]->addRule(Form::FILLED, "Jméno prvního člena musí být vyplněno.");
            }
        }

        $defaults = [];

        $form->addGroup();

        if ($this->getPresenter()->user->isLoggedIn()) {
            $loggedTeam = $this->getPresenter()->getLoggedTeam();
            $defaults += [
                "team_name" => $loggedTeam->name,
                "email" => $loggedTeam->email,
                "address" => $loggedTeam->address,
                "category" => $loggedTeam->category,
                "id_team" => $loggedTeam->id_team,
            ];
            $competitors = $this->competitorsService->findAllByTeam($loggedTeam->id_team)->orderBy("id_competitor")->fetchAll();
            $counter = 1;
            foreach ($competitors as $competitor) {
                $defaults += [
                    "competitor_name_" . $counter => $competitor->name,
                    "school_" . $counter => $competitor->id_school,
                    "email_" . $counter => $competitor->email,
                    "study_year_" . $counter => $competitor->study_year,
                    "study_year_hid_" . $counter => $competitor->study_year,
                ];
                $counter++;
            }
            $form["team_name"]->setDisabled();
            $form->addHidden("id_team");
            $form->addSubmit("update", "Upravit");
            $form->onSubmit[] = [$this, "updateSubmitted"];
        } else {

            $form["password"]->addRule(Form::FILLED, "Není vyplněno heslo týmu.");
            $form->addSubmit("insert", "Registrovat");
            $form->onSubmit[] = [$this, "insertSubmitted"];
        }

        $form->setDefaults($defaults);
        return $form;
    }

    // ---- PRIVATE METHODS

    /**
     * @param $team
     * @param $values
     * @return void
     * @throws \Dibi\Exception
     */
    private function insertCompetitorsFromValues($team, $values) {
        $competitors = $this->loadCompetitorsFromValues($values);
        $insertedSchools = [];
        foreach ($competitors as $competitor) {
            if ($competitor['school'] == self::OTHER_SCHOOL && !empty($competitor['otherschool'])) {
                if (array_key_exists($competitor['otherschool'], $insertedSchools)) {
                    $competitor['school'] = $insertedSchools[$competitor["otherschool"]];
                } else {
                    $competitor['school'] = $this->schoolService->insert($competitor['otherschool']);
                    $insertedSchools[$competitor["otherschool"]] = $competitor['school']; // nevkládáme vícekrát
                }
            }
            $this->competitorsService->insert($team, $competitor['school'], $competitor['name'], $competitor['email'], $competitor['study_year']);
        }
    }

    /**
     * @param $values
     * @return array|false
     * @throws \Dibi\Exception
     */
    private function loadCompetitorsFromValues($values) {
        $competitors = [];
        $schoolsToInsert = [];
        for ($i = 1; $i <= self::NUMBER_OF_MEMBERS; $i++) {
            if (!empty($values["competitor_name_" . $i])) {
                $competitor = [];
                $competitor["name"] = $values["competitor_name_" . $i];
                $competitor["school"] = $values["school_" . $i];
                $competitor["otherschool"] = $values["otherschool_" . $i];
                $competitor["email"] = $values["email_" . $i];
                $competitor["study_year"] = isset($values["study_year_" . $i]) ? $values["study_year_" . $i] : $values["study_year_hid_" . $i];
                if ($values["school_" . $i] == self::OTHER_SCHOOL && !empty($competitor["otherschool"])) {
                    $schoolsToInsert[$competitor["otherschool"]] = true; // unikátnost názvu nových škol
                }
                $competitors[] = $competitor;
            }
        }
        $schoolsToInsert = array_keys($schoolsToInsert);
        $schoolExists = $this->schoolService->findAll()->where("[name] IN %l", $schoolsToInsert)->count();
        if ($schoolExists) {
            return false;
        } else {
            return $competitors;
        }
    }


    public function render(): void {
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'teamForm.latte');
        parent::render();
    }

}
