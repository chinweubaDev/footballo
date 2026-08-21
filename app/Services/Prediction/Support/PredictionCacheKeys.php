<?php

namespace App\Services\Prediction\Support;

class PredictionCacheKeys
{
    public const SURE_PICKS = 'predictions.homepage.sure-picks';

    public const FEATURED = 'predictions.homepage.featured';

    public const LEAGUE_PAGE = 'predictions.league';

    /**
     * Cache key for the live model-performance dashboard summary.
     */
    public static function performanceDashboard(): string
    {
        return 'predictions.performance.dashboard';
    }
}
