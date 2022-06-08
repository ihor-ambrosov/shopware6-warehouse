<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Storefront\Framework\Cache\Event;

use Ambros\Warehouse\Storefront\Framework\Cache\CacheResponseSubscriber;
use Shopware\Storefront\Framework\Cache\Event\HttpCacheGenerateKeyEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HttpCacheGenerateKeyEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            HttpCacheGenerateKeyEvent::class => 'execute'
        ];
    }

    public function execute(HttpCacheGenerateKeyEvent $event): void
    {
        $request = $event->getRequest();
        $warehouseId = $request->cookies->get(CacheResponseSubscriber::WAREHOUSE_COOKIE);
        $event->setHash($event->getHash().'-'.$warehouseId);
    }
}