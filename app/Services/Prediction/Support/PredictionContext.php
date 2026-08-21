<?php

namespace App\Services\Prediction\Support;

use App\Models\Fixture;

/**
 * Prepared, structured data used by all prediction models.
 *
 * The context is built ONCE by the DataCollector so individual models never
 * issue their own API-Football requests.
 */
class PredictionContext
{
    public function __construct(
        public Fixture $fixture,
        public int $homeTeamId,
        public int $awayTeamId,
        public int $leagueId,
        public ?string $season,
        /** @var list<array{result:string,goals_for:int,goals_against:int,is_home:bool}> */
        public array $homeForm = [],
        /** @var list<array{result:string,goals_for:int,goals_against:int,is_home:bool}> */
        public array $awayForm = [],
        /** @var list<array{result:string,goals_for:int,goals_against:int,is_home:bool}> */
        public array $homeHomeForm = [],
        /** @var list<array{result:string,goals_for:int,goals_against:int,is_home:bool}> */
        public array $awayAwayForm = [],
        /** @var array<string,mixed> */
        public array $homeTeamStats = [],
        /** @var array<string,mixed> */
        public array $awayTeamStats = [],
        /** @var array<string,mixed> */
        public array $h2h = [],
        /** @var array<string,mixed> */
        public array $odds = [],
        /** @var array<string,mixed> */
        public array $apiPrediction = [],
        /** @var array<string,mixed> */
        public array $standings = [],
        /** @var array<string,mixed> */
        public array $injuries = [],
    ) {
    }
}
