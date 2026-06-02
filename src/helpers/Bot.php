<?php
namespace verbb\bugsnag\helpers;

use Craft;

use Jaybizzle\CrawlerDetect\CrawlerDetect;

class Bot
{
    // Static
    // =========================================================================

    private static ?CrawlerDetect $_crawlerDetect = null;


    // Public Methods
    // =========================================================================

    public static function isCurrentRequestCrawler(): bool
    {
        $request = Craft::$app->getRequest();

        if (!method_exists($request, 'getUserAgent')) {
            return false;
        }

        return self::isCrawler($request->getUserAgent());
    }

    public static function isCrawler(?string $userAgent): bool
    {
        if (!$userAgent) {
            return false;
        }

        return self::_getCrawlerDetect()->isCrawler($userAgent);
    }


    // Private Methods
    // =========================================================================

    private static function _getCrawlerDetect(): CrawlerDetect
    {
        if (self::$_crawlerDetect === null) {
            self::$_crawlerDetect = new CrawlerDetect();
        }

        return self::$_crawlerDetect;
    }
}
