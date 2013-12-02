<?php

class Interlos {

    private static $adminMessages = FALSE;
    private static $connection;
    private static $currentYear;
    private static $loggedTeam;
    private static $translator;
    private static $models = array();

    /** @return AnswersModel */
    public static function answers() {
        return self::getModel("answers");
    }

    /** @return ChatModel */
    public static function chat() {
        return self::getModel("chat");
    }

    /** @return CompetitorsModel */
    public static function competitors() {
        return self::getModel("competitors");
    }

    public static function createAdminMessages() {
        if (self::$adminMessages) {
            return;
        }
        $presenter = Environment::getApplication()->getPresenter();
        if (self::isAdminAccess()) {
            $presenter->flashMessage("Přístup administrátora schválen.", "info");
        }
        if (self::loadAdminProperty("game-end") !== NULL) {
            $presenter->flashMessage("Konec hry nastaven na " . (self::loadAdminProperty("game-end") ? "TRUE" : "FALSE") . ".");
        }
        if (self::loadAdminProperty("game-started") !== NULL) {
            $presenter->flashMessage("Začátek hry nastaven na " . (self::loadAdminProperty("game-started") ? "TRUE" : "FALSE") . ".");
        }
        if (self::loadAdminProperty("registration-end") !== NULL) {
            $presenter->flashMessage("Konec registrace nastaven na " . (self::loadAdminProperty("registration-end") ? "TRUE" : "FALSE") . ".");
        }
        if (self::loadAdminProperty("registration-started") != NULL) {
            $presenter->flashMessage("Začátek registrace nastaven na " . (self::loadAdminProperty("registration-started") ? "TRUE" : "FALSE") . ".");
        }
        if (self::loadAdminProperty("time") !== NULL) {
            $presenter->flashMessage("Herní čas nastaven na " . self::loadAdminProperty("time") . ".");
        }
        self::$adminMessages = TRUE;
    }

    /** @return DibiConnection */
    public static function getConnection() {
        if (empty(self::$connection)) {
            return dibi::getConnection();
        } else {
            return self::$connection;
        }
    }

    /** @return DibiRow */
    public static function getCurrentYear() {
        if (!isset(self::$currentYear)) {
            self::$currentYear = self::years()->findCurrent();
        }
        return self::$currentYear;
    }

    /** @return DibiRow */
    public static function getLoggedTeam() {
        if (!isset(self::$loggedTeam)) {
            if (Environment::getUser()->isLoggedIn()) {
                self::$loggedTeam = Interlos::teams()->find(Environment::getUser()->getIdentity()->id_team);
            } else {
                self::$loggedTeam = NULL;
            }
        }
        return self::$loggedTeam;
    }

    /** @return GettextTranslator */
    public static function getTranslator() {
        if (empty(self::$translator)) {
            return new GettextTranslator();
        } else {
            return self::$translator;
        }
    }

    public static function isAdminAccess() {
        return isset($_GET["admin-key"]) && Environment::getConfig("admin")->key == $_GET["admin-key"];
    }

    public static function isCronAccess() {
        return isset($_GET["cron-key"]) && Environment::getConfig("cron")->key == $_GET["cron-key"];
    }

    public static function isGameActive() {
        return self::isGameStarted() && !self::isGameEnd();
    }

    public static function isGameEnd() {
        if (self::loadAdminProperty("game-end")) {
            return self::loadAdminProperty("game-end");
        } else {
            return self::getCurrentTime() > strtotime(Interlos::getCurrentYear()->game_end);
        }
    }

    public static function isGameStarted() {
        if (self::loadAdminProperty("game-started") !== null) {
            return self::loadAdminProperty("game-started");
        } else {
            return strtotime(Interlos::getCurrentYear()->game_start) < self::getCurrentTime();
        }
    }

    public static function isRegistrationActive() {
        return self::isRegistrationStarted() && !self::isRegistrationEnd();
    }

    public static function isRegistrationEnd() {
        if (self::loadAdminProperty("registration-end")) {
            return self::loadAdminProperty("game-end");
        } else {
            return strtotime(Interlos::getCurrentYear()->registration_end) < self::getCurrentTime();
        }
    }

    public static function isRegistrationStarted() {
        if (self::loadAdminProperty("registration-started")) {
            return self::loadAdminProperty("game-started");
        } else {
            return strtotime(Interlos::getCurrentYear()->registration_start) < self::getCurrentTime();
        }
    }

    public static function prepareAdminProperties() {
        if (!self::isAdminAccess()) {
            return;
        }
        $propertiesToStore = array("game-end", "game-started", "registration-end", "registration-started", "time");
        $session = Environment::getSession("admin.property");
        if (self::isAdminPropertyAvailableInURL("reset-admin-properties")) {
            foreach ($propertiesToStore AS $property) {
                $session[$property] = NULL;
            }
        }
        foreach ($propertiesToStore AS $property) {
            if (self::isAdminPropertyAvailableInURL($property)) {
                self::storeAdminProperty($property);
            }
        }
    }

    public static function resetTemporaryTables() {
        if ((!self::isCronAccess() && !self::isAdminAccess())) {
            return;
        }

        $src = 'view_'; // view
        $result = 'tmp_'; // resulting cache

        $tables = array(
            'task_result' => 'task_result',
            'task_stat' => 'task_stat',
            'penality' => 'penality',
            'bonus_cached' => 'bonus',
            'total_result_cached' => 'total_result',
        );

        foreach ($tables as $view => $table) {
            Debug::timer();
            self::getConnection()->query("DROP TABLE IF EXISTS [$result$table]");
            self::getConnection()->query("CREATE TABLE [$result$table] AS SELECT * FROM [$src$view]");
            echo "$table: " . Debug::timer() . "<br>";
        }

    }

    /** @return GroupsModel */
    public static function groups() {
        return self::getModel("groups");
    }

    /** @return PeriodModel */
    public static function period() {
        return self::getModel("period");
    }

    /** @return SchoolsModel */
    public static function schools() {
        return self::getModel("schools");
    }

    /** @return ScoreModel */
    public static function score() {
        return self::getModel("score");
    }

    public static function setConnection(DibiConnection $connection) {
        self::$connection = $connection;
    }

    /** @return TasksModel */
    public static function tasks() {
        return self::getModel("tasks");
    }

    /** @return TeamsModel */
    public static function teams() {
        return self::getModel("teams");
    }

    /** @return YearsModel */
    public static function years() {
        return self::getModel("years");
    }

    // ---- PRIVATE METHODS

    private static function createModel($name) {
        $className = ucfirst($name) . "Model";
        // Check whether the model class exist
        if (!class_exists($className)) {
            throw new InvalidStateException("The class [$className] does not exists.");
        }
        // Check whether the class is really the model class
        $key = ExtraArray::keysOf(class_implements($className), "InterlosModel");
        if (empty($key)) {
            throw new InvalidStateException("The class [$className] does not implement interface [InterlosModel]");
        }
        // Return new instance of model class
        return new $className(self::getConnection());
    }

    public static function getCurrentTime() {
        if (self::loadAdminProperty("time") !== NULL) {
            return strtotime(self::loadAdminProperty("time"));
        } else {
            return time();
        }
    }

    private static function getModel($name) {
        if (empty(self::$models[$name])) {
            self::$models[$name] = self::createModel($name);
        }
        return self::$models[$name];
    }

    private static function getAdminPropertyValueFromURL($property) {
        if (!self::isAdminAccess()) {
            return false;
        }
        if (!isset($_GET[$property])) {
            return false;
        }
        $trues = array("1", "TRUE", "true", "yes", "YES");
        $falses = array("0", "FALSE", "false", "no", "NO");
        if (in_array($_GET[$property], $trues)) {
            return true;
        } else if (in_array($_GET[$property], $falses)) {
            return false;
        } else {
            return $_GET[$property];
        }
    }

    private static function isAdminPropertyAvailableInURL($property) {
        if (!self::isAdminAccess()) {
            return false;
        }
        if (!isset($_GET[$property])) {
            return false;
        }
        return true;
    }

    private static function loadAdminProperty($property) {
        $session = Environment::getSession("admin.property");
        if (isset($session[$property])) {
            return $session[$property];
        } else {
            return NULL;
        }
    }

    private static function storeAdminProperty($property) {
        $session = Environment::getSession("admin.property");
        if (!self::isAdminPropertyAvailableInURL($property)) {
            return;
        }
        $session[$property] = self::getAdminPropertyValueFromURL($property);
    }

}
