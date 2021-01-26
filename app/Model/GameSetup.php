<?php

namespace FOL\Model;

class GameSetup {

    public string $gameStart;
    public string $gameEnd;

    public string $resultsHide;
    public string $resultsDisplay;

    public bool $hardVisible;

    public int $refreshDelay;

    public function __construct(array $config) {
        $this->gameStart = $config['game']['start'];
        $this->gameEnd = $config['game']['end'];

        $this->resultsHide = $config['results']['hide'];
        $this->resultsDisplay = $config['results']['show'];

        $this->hardVisible = $config['hardVisible'];
        $this->refreshDelay = $config['refreshDelay'];
    }
}
