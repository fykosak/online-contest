<?php

namespace FOL\Model;

class GameSetup {

    public string $gameStart;
    public string $gameEnd;

    public string $resultsHide;
    public string $resultsDisplay;

    public bool $hardVisible;

    public int $refreshDelay;
    public bool $isGameMigrated = true; // TODO

    public string $streamURL;

    public function __construct(array $config) {
        $this->gameStart = $config['game']['start'];
        $this->gameEnd = $config['game']['end'];

        $this->resultsHide = $config['results']['hide'];
        $this->resultsDisplay = $config['results']['show'];

        $this->hardVisible = $config['hardVisible'];
        $this->refreshDelay = $config['refreshDelay'];

        $this->streamURL = $config['streamURL'];
    }

    public function isResultsVisible(): bool {
        if ($this->hardVisible) {
            return true;
        }
        $before = (time() < strtotime($this->resultsHide));
        $after = (time() > strtotime($this->resultsDisplay));
        return ($before && $after);
    }

    public function isGameEnd(): bool {
        return time() > strtotime($this->gameEnd);
    }

    public function isGameStarted(): bool {
        return strtotime($this->gameStart) < time();
    }

    public function isGameActive(): bool {
        return $this->isGameStarted() && !$this->isGameEnd();
    }
}
