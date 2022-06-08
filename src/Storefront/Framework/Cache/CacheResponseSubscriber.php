<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Storefront\Framework\Cache;

use Ambros\Warehouse\Core\System\SalesChannel\Context\SalesChannelContextService;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\Routing\MaintenanceModeResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CacheResponseSubscriber implements EventSubscriberInterface
{
    public const WAREHOUSE_COOKIE = 'eb-warehouse';

    private bool $httpCacheEnabled;

    private MaintenanceModeResolver $maintenanceResolver;

    public function __construct(
        bool $httpCacheEnabled,
        MaintenanceModeResolver $maintenanceModeResolver
    ) {
        $this->httpCacheEnabled = $httpCacheEnabled;
        $this->maintenanceResolver = $maintenanceModeResolver;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => [
                ['setResponseCache', -1500],
            ],
        ];
    }

    public function setResponseCache(ResponseEvent $event): void
    {
        if (!$this->httpCacheEnabled) {
            return;
        }
        $request = $event->getRequest();
        if ($this->maintenanceResolver->isMaintenanceRequest($request)) {
            return;
        }
        $context = $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);
        if (!$context instanceof SalesChannelContext) {
            return;
        }
        $route = $request->attributes->get('_route');
        if ($route === 'frontend.checkout.configure') {
            $this->setWarehouseCookie($request, $event->getResponse());
        }
    }

    private function setWarehouseCookie(Request $request, Response $response): void
    {
        $warehouseId = $request->get(SalesChannelContextService::WAREHOUSE_ID);
        if (!$warehouseId) {
            return;
        }
        $cookie = Cookie::create(self::WAREHOUSE_COOKIE, $warehouseId);
        $cookie->setSecureDefault($request->isSecure());
        $response->headers->setCookie($cookie);
    }
}