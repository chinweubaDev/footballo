<?php

/**
 * Curated catalog of football leagues grouped by country.
 *
 * This powers the admin "Discover Leagues" screen so leagues can be browsed,
 * searched and enabled even when the live API-Football quota is exhausted.
 * IDs are API-Football v3 league IDs. League logos are derived from the ID via
 * https://media.api-sports.io/football/leagues/{id}.png
 */

return [
    [
        'country' => 'International',
        'code' => 'world',
        'leagues' => [
            ['id' => 1,   'name' => 'World Cup', 'type' => 'Cup'],
            ['id' => 2,   'name' => 'UEFA Champions League', 'type' => 'Cup'],
            ['id' => 3,   'name' => 'UEFA Europa League', 'type' => 'Cup'],
            ['id' => 848, 'name' => 'UEFA Europa Conference League', 'type' => 'Cup'],
            ['id' => 4,   'name' => 'European Championship', 'type' => 'Cup'],
            ['id' => 5,   'name' => 'UEFA Nations League', 'type' => 'Cup'],
            ['id' => 9,   'name' => 'Copa America', 'type' => 'Cup'],
            ['id' => 15,  'name' => 'FIFA Club World Cup', 'type' => 'Cup'],
            ['id' => 10,  'name' => 'International Friendlies', 'type' => 'Friendly'],
        ],
    ],
    [
        'country' => 'England',
        'code' => 'gb',
        'leagues' => [
            ['id' => 39, 'name' => 'Premier League', 'type' => 'League'],
            ['id' => 40, 'name' => 'Championship', 'type' => 'League'],
            ['id' => 41, 'name' => 'League One', 'type' => 'League'],
            ['id' => 42, 'name' => 'League Two', 'type' => 'League'],
            ['id' => 43, 'name' => 'National League', 'type' => 'League'],
            ['id' => 45, 'name' => 'FA Cup', 'type' => 'Cup'],
            ['id' => 46, 'name' => 'EFL Cup', 'type' => 'Cup'],
            ['id' => 48, 'name' => 'EFL Trophy', 'type' => 'Cup'],
        ],
    ],
    [
        'country' => 'Spain',
        'code' => 'es',
        'leagues' => [
            ['id' => 140, 'name' => 'La Liga', 'type' => 'League'],
            ['id' => 141, 'name' => 'Segunda División', 'type' => 'League'],
            ['id' => 143, 'name' => 'Copa del Rey', 'type' => 'Cup'],
        ],
    ],
    [
        'country' => 'Germany',
        'code' => 'de',
        'leagues' => [
            ['id' => 78, 'name' => 'Bundesliga', 'type' => 'League'],
            ['id' => 79, 'name' => '2. Bundesliga', 'type' => 'League'],
            ['id' => 80, 'name' => '3. Liga', 'type' => 'League'],
            ['id' => 81, 'name' => 'DFB Pokal', 'type' => 'Cup'],
        ],
    ],
    [
        'country' => 'Italy',
        'code' => 'it',
        'leagues' => [
            ['id' => 135, 'name' => 'Serie A', 'type' => 'League'],
            ['id' => 136, 'name' => 'Serie B', 'type' => 'League'],
            ['id' => 138, 'name' => 'Coppa Italia', 'type' => 'Cup'],
            ['id' => 547, 'name' => 'Supercoppa Italiana', 'type' => 'Cup'],
        ],
    ],
    [
        'country' => 'France',
        'code' => 'fr',
        'leagues' => [
            ['id' => 61, 'name' => 'Ligue 1', 'type' => 'League'],
            ['id' => 62, 'name' => 'Ligue 2', 'type' => 'League'],
            ['id' => 65, 'name' => 'Coupe de France', 'type' => 'Cup'],
        ],
    ],
    [
        'country' => 'Netherlands',
        'code' => 'nl',
        'leagues' => [
            ['id' => 88, 'name' => 'Eredivisie', 'type' => 'League'],
            ['id' => 89, 'name' => 'Eerste Divisie', 'type' => 'League'],
            ['id' => 90, 'name' => 'KNVB Beker', 'type' => 'Cup'],
        ],
    ],
    [
        'country' => 'Portugal',
        'code' => 'pt',
        'leagues' => [
            ['id' => 94, 'name' => 'Primeira Liga', 'type' => 'League'],
            ['id' => 95, 'name' => 'Segunda Liga', 'type' => 'League'],
            ['id' => 96, 'name' => 'Taça de Portugal', 'type' => 'Cup'],
        ],
    ],
    [
        'country' => 'Belgium',
        'code' => 'be',
        'leagues' => [
            ['id' => 144, 'name' => 'Pro League', 'type' => 'League'],
            ['id' => 145, 'name' => 'Challenger Pro League', 'type' => 'League'],
            ['id' => 146, 'name' => 'Belgian Cup', 'type' => 'Cup'],
        ],
    ],
    [
        'country' => 'Scotland',
        'code' => 'gb',
        'leagues' => [
            ['id' => 179, 'name' => 'Premiership', 'type' => 'League'],
            ['id' => 180, 'name' => 'Championship', 'type' => 'League'],
        ],
    ],
    [
        'country' => 'Turkey',
        'code' => 'tr',
        'leagues' => [
            ['id' => 203, 'name' => 'Süper Lig', 'type' => 'League'],
            ['id' => 204, 'name' => '1. Lig', 'type' => 'League'],
            ['id' => 206, 'name' => 'Turkish Cup', 'type' => 'Cup'],
        ],
    ],
    [
        'country' => 'Greece',
        'code' => 'gr',
        'leagues' => [
            ['id' => 197, 'name' => 'Super League 1', 'type' => 'League'],
        ],
    ],
    [
        'country' => 'Austria',
        'code' => 'at',
        'leagues' => [
            ['id' => 218, 'name' => 'Austrian Bundesliga', 'type' => 'League'],
        ],
    ],
    [
        'country' => 'Switzerland',
        'code' => 'ch',
        'leagues' => [
            ['id' => 207, 'name' => 'Super League', 'type' => 'League'],
        ],
    ],
    [
        'country' => 'Denmark',
        'code' => 'dk',
        'leagues' => [
            ['id' => 119, 'name' => 'Superliga', 'type' => 'League'],
        ],
    ],
    [
        'country' => 'Sweden',
        'code' => 'se',
        'leagues' => [
            ['id' => 113, 'name' => 'Allsvenskan', 'type' => 'League'],
        ],
    ],
    [
        'country' => 'Norway',
        'code' => 'no',
        'leagues' => [
            ['id' => 103, 'name' => 'Eliteserien', 'type' => 'League'],
        ],
    ],
    [
        'country' => 'Poland',
        'code' => 'pl',
        'leagues' => [
            ['id' => 106, 'name' => 'Ekstraklasa', 'type' => 'League'],
        ],
    ],
    [
        'country' => 'Czech Republic',
        'code' => 'cz',
        'leagues' => [
            ['id' => 345, 'name' => 'Fortuna Liga', 'type' => 'League'],
        ],
    ],
    [
        'country' => 'Croatia',
        'code' => 'hr',
        'leagues' => [
            ['id' => 210, 'name' => 'HNL', 'type' => 'League'],
        ],
    ],
    [
        'country' => 'Serbia',
        'code' => 'rs',
        'leagues' => [
            ['id' => 286, 'name' => 'Super Liga', 'type' => 'League'],
        ],
    ],
    [
        'country' => 'Romania',
        'code' => 'ro',
        'leagues' => [
            ['id' => 283, 'name' => 'SuperLiga', 'type' => 'League'],
        ],
    ],
    [
        'country' => 'Bulgaria',
        'code' => 'bg',
        'leagues' => [
            ['id' => 172, 'name' => 'First League', 'type' => 'League'],
        ],
    ],
    [
        'country' => 'Russia',
        'code' => 'ru',
        'leagues' => [
            ['id' => 235, 'name' => 'Premier League', 'type' => 'League'],
        ],
    ],
    [
        'country' => 'Brazil',
        'code' => 'br',
        'leagues' => [
            ['id' => 71, 'name' => 'Serie A', 'type' => 'League'],
            ['id' => 72, 'name' => 'Serie B', 'type' => 'League'],
            ['id' => 73, 'name' => 'Copa do Brasil', 'type' => 'Cup'],
        ],
    ],
    [
        'country' => 'Argentina',
        'code' => 'ar',
        'leagues' => [
            ['id' => 128, 'name' => 'Liga Profesional', 'type' => 'League'],
            ['id' => 129, 'name' => 'Primera Nacional', 'type' => 'League'],
            ['id' => 130, 'name' => 'Copa Argentina', 'type' => 'Cup'],
        ],
    ],
    [
        'country' => 'United States',
        'code' => 'us',
        'leagues' => [
            ['id' => 253, 'name' => 'MLS', 'type' => 'League'],
        ],
    ],
    [
        'country' => 'Mexico',
        'code' => 'mx',
        'leagues' => [
            ['id' => 262, 'name' => 'Liga MX', 'type' => 'League'],
        ],
    ],
    [
        'country' => 'Japan',
        'code' => 'jp',
        'leagues' => [
            ['id' => 98, 'name' => 'J1 League', 'type' => 'League'],
            ['id' => 99, 'name' => 'J2 League', 'type' => 'League'],
        ],
    ],
    [
        'country' => 'South Korea',
        'code' => 'kr',
        'leagues' => [
            ['id' => 292, 'name' => 'K League 1', 'type' => 'League'],
            ['id' => 293, 'name' => 'K League 2', 'type' => 'League'],
        ],
    ],
    [
        'country' => 'China',
        'code' => 'cn',
        'leagues' => [
            ['id' => 169, 'name' => 'Chinese Super League', 'type' => 'League'],
        ],
    ],
    [
        'country' => 'Australia',
        'code' => 'au',
        'leagues' => [
            ['id' => 188, 'name' => 'A-League', 'type' => 'League'],
        ],
    ],
    [
        'country' => 'Saudi Arabia',
        'code' => 'sa',
        'leagues' => [
            ['id' => 307, 'name' => 'Saudi Pro League', 'type' => 'League'],
        ],
    ],
    [
        'country' => 'United Arab Emirates',
        'code' => 'ae',
        'leagues' => [
            ['id' => 319, 'name' => 'UAE Pro League', 'type' => 'League'],
        ],
    ],
    [
        'country' => 'Qatar',
        'code' => 'qa',
        'leagues' => [
            ['id' => 322, 'name' => 'Qatar Stars League', 'type' => 'League'],
        ],
    ],
    [
        'country' => 'Egypt',
        'code' => 'eg',
        'leagues' => [
            ['id' => 333, 'name' => 'Egyptian Premier League', 'type' => 'League'],
        ],
    ],
    [
        'country' => 'South Africa',
        'code' => 'za',
        'leagues' => [
            ['id' => 350, 'name' => 'Premier Soccer League', 'type' => 'League'],
        ],
    ],
    [
        'country' => 'Nigeria',
        'code' => 'ng',
        'leagues' => [
            ['id' => 357, 'name' => 'Nigerian Professional Football League', 'type' => 'League'],
        ],
    ],
    [
        'country' => 'Ghana',
        'code' => 'gh',
        'leagues' => [
            ['id' => 364, 'name' => 'Ghana Premier League', 'type' => 'League'],
        ],
    ],
    [
        'country' => 'Kenya',
        'code' => 'ke',
        'leagues' => [
            ['id' => 371, 'name' => 'Kenyan Premier League', 'type' => 'League'],
        ],
    ],
];
