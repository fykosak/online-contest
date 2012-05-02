<?php
/**
 * This form provides inserting and updating of the team.
 *
 * @author Jan Papousek
 */
class TeamFormComponent extends BaseComponent {

	const NUMBER_OF_MEMBERS = 5;

	/* SUBMITTED FORMS */

	public function insertSubmitted(Form $form) {
		$values		= $form->getValues();
		$competitors= $this->loadCompetitorsFromValues($values);
		if (!$competitors) {
			$this->getPresenter()->flashMessage("Pokoušíte se vložit školu, která již existuje.", "error");
			return;
		}
		// Check team name and e-mail because the database consistency
		$teamExists = Interlos::teams()->findAll()->where("[name] = %s", $values["team_name"], " OR [email] = %s", $values["email"])->count();
		if ($teamExists != 0) {
			$this->getPresenter()->flashMessage("Tým se stejným názvem nebo kontaktním e-mailem již existuje", "error");
			return;
		}
		try {
			// Insert team
			$insertedTeam = Interlos::teams()->insert(
					$values["team_name"],
					$values["email"],
					$values["category"],
					$values["password"],
                                        $values["address"]
			);
			// Send e-mail
			$template = InterlosTemplate::loadTemplate(new Template());
			$template->setFile(FrontendModule::getModuleDir() . "/templates/mail/registration.phtml");
			$template->team = $values["team_name"];
			$mail = new Mail();
			$mail->setBody($template);
			$mail->addTo($values["email"]);
			$mail->setFrom(Environment::getConfig("mail")->info, Environment::getConfig("mail")->name);
			$mail->setSubject("Interlos - registrace");
			// TODO: doresit odesilani e-mailu
			//$mail->send();
			// Redirect
			$this->insertCompetitorsFromValues($insertedTeam, $values);
			$this->getPresenter()->flashMessage("Tým '".$values["team_name"]."' byl úspěšně zaregistrován.", "success");
			$this->getPresenter()->redirect("Default:login");
		}
		catch (DibiDriverException $e) {
			$this->getPresenter()->flashMessage("Chyba při práci s databází.", "error");
			Debug::processException($e);
			return;
		}


	}

	public function updateSubmitted(Form $form) {
		$values = $form->getValues();
		try {
			// Update the team
			$changes = array(
					"email"	    => $values["email"],
					"category"  => $values["category"],
                                        "address"   => $values["address"],
			);
			if (!empty($values["password"])) {
				$changes["password"] = TeamAuthenticator::passwordHash($values["password"]);
			}
			Interlos::teams()->update($changes)->where("[id_team] = %i", $values["id_team"])->execute();
			// Update competitors
			Interlos::competitors()->deleteByTeam($values["id_team"]);
			$this->insertCompetitorsFromValues($values["id_team"], $values);
			// Success
			$this->getPresenter()->flashMessage("Tým byl úspěšně aktualizován.", "success");
			$this->getPresenter()->redirect("this");
		}
		catch (InvalidArgumentException $e) {
			$this->getPresenter()->flashMessage("Tým musí mít alespoň jednoho člena.", "error");
			Debug::processException($e);
		}
		catch (DuplicityException $e) {
			$this->getPresenter()->flashMessage("Daný tým již existuje.", "error");
			Debug::processException($e);
		}
		catch (DibiDriveException $e) {
			$this->getPresenter()->flashMessage("Chyba při práci s databází.", "error");
			Debug::processException($e);
		}
	}

	/* PROTECTED METHODS */

	protected function createComponentTeamForm($name) {
		$form = new BaseForm($this, $name);

		$form->addGroup("Tým");

		// Team name
		$form->addText("team_name", "Název týmu")->addRule(Form::FILLED, "Název týmu musí být vyplněn");

		// Password
		$form->addPassword("password", "Heslo");
		$form->addPassword("password_check", "Kontrola hesla")
				->addConditionOn($form["password"], Form::FILLED)
				->addRule(Form::EQUAL, "Heslo a kontrola hesla se neshodují.", $form["password"]);

		// Category
		$form->addSelect("category", "Kategorie", array(
				TeamsModel::HIGH_SCHOOL	=> "Středoškoláci",
				TeamsModel::OPEN	=> "Open",
		));

		// Contatcs
		$form->addText("email", "E-mail")
                        ->addRule(Form::FILLED, "Zadejte prosím kontatní e-mail.")
                        ->addRule(Form::EMAIL, "E-mail nemá správný formát.");
                
                $form->addTextArea("address", "Kontaktní adresa", 35, 4)
                        ->addRule(Form::FILLED, "Zadejte prosím kontatní adresu.")
                        ->setOption("description", "Pro zaslání případné odměny.");

		$schools = Interlos::schools()->findAll()->orderBy("name")->fetchPairs("id_school", "name");
		$schools = array(NULL => "Nevyplněno") + $schools + array("other" => "Jiná");

		// Members
		for ($i=1; $i<=self::NUMBER_OF_MEMBERS; $i++) {
			$form->addGroup("$i. člen");
			$form->addText($i."_competitor_name", "Jméno");
			$form->addSelect($i."_school", "Škola", $schools)
					->addConditionOn($form[$i."_competitor_name"], Form::FILLED)
					->addRule(~Form::EQUAL, "U $i. člena je vyplněno jméno, ale není u něj vyplněna škola.", NULL)
					->endCondition()
					->addCondition(Form::EQUAL, "other")
					->toggle("frm".$name."-".$i."_otherschool")
					->toggle("frm".$name."-".$i."_otherschool-label");
			$form->addText($i."_otherschool", "Jiná škola")
					->addConditionOn($form[$i."_competitor_name"], Form::FILLED)
					->addConditionOn($form[$i."_school"], Form::EQUAL, "other")
					->addRule(Form::FILLED, "U $i. člena je vyplněno jméno, ale není u něj vyplněna škola.");
			$form[$i."_otherschool"]->getLabelPrototype()->id = "frm".$name."-".$i."_otherschool-label";
			if ($i == 1) {
				$form[$i."_competitor_name"]->addRule(Form::FILLED, "Jméno prvního člena musí být vyplněno.");
			}
		}

		$defaults = array();

		$form->addGroup();

		if (Environment::getUser()->isLoggedIn()) {
			$loggedTeam = Interlos::getLoggedTeam();
			$defaults += array(
					"team_name"	=> $loggedTeam->name,
					"email"	=> $loggedTeam->email,
					"category"	=> $loggedTeam->category,
					"id_team"	=> $loggedTeam->id_team
			);
			$competitors = Interlos::competitors()->findAllByTeam($loggedTeam->id_team)->orderBy("id_competitor")->fetchAll();
			$counter = 1;
			foreach($competitors AS $competitor) {
				$defaults += array(
						$counter . "_competitor_name"	=> $competitor->name,
						$counter . "_school"		=> $competitor->id_school
				);
				$counter++;
			}
			$form["team_name"]->setDisabled();
			$form->addHidden("id_team");
			$form->addSubmit("update", "Upravit");
			$form->onSubmit[] = array($this, "updateSubmitted");
		}
		else {
			$form["password"]->addRule(Form::FILLED, "Není vyplněno heslo týmu.");
			$form->addSubmit("insert", "Registrovat");
			$form->onSubmit[] = array($this, "insertSubmitted");
		}

		$form->setDefaults($defaults);
		return $form;
	}

	// ---- PRIVATE METHODS

	private function insertCompetitorsFromValues($team, $values) {
		$competitors = $this->loadCompetitorsFromValues($values);
		foreach($competitors AS $competitor) {
			if (!empty($competitor['otherschool'])) {
				$competitor['school'] = Interlos::schools()->insert($competitor['otherschool']);
			}
			Interlos::competitors()->insert($team, $competitor['school'], $competitor['name']);
		}
	}

	private function loadCompetitorsFromValues($values) {
		$competitors = array();
		$schoolsToInsert = array();
		for($i=1; $i <= 5; $i++) {
			if (!empty($values[$i."_competitor_name"])) {
				$competitor = array();
				$competitor["name"]		= $values[$i."_competitor_name"];
				$competitor["school"]	= $values[$i."_school"];
				$competitor["otherschool"]	= $values[$i."_otherschool"];
				if (!empty($competitor["otherschool"])) {
					$schoolsToInsert[] = $competitor["otherschool"];
				}
				$competitors[] = $competitor;
			}
		}
		$schoolExists = Interlos::schools()->findAll()->where("[name] IN %l", $schoolsToInsert)->count();
		if ($schoolExists) {
			return FALSE;
		}
		else {
			return $competitors;
		}
	}

}
