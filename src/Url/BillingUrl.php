<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Url;

use GuzzleHttp\ClientInterface;
use Otis22\VetmanagerUrl\Url;
use Otis22\VetmanagerUrl\Url\BillingApi;
use Otis22\VetmanagerUrl\Url\FromJson;
use Otis22\VetmanagerUrl\Url\Part\Domain;

use function Otis22\VetmanagerUrl\url;

/** @internal */
final class BillingUrl
{
    public static function resolve(string $domainName, ClientInterface $client): Url
    {
        if (class_exists(FromJson::class) && is_callable([FromJson::class, 'fromDomainAndBillingApiUsingClient'])) {
            $billingApi = new BillingApi('https://billing-api.vetmanager.ru');
            return FromJson::fromDomainAndBillingApiUsingClient(new Domain($domainName), $billingApi, $client);
        }
        return url($domainName);
    }
}
