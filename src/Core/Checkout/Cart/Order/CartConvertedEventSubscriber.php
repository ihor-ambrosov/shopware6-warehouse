<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Checkout\Cart\Order;

use Ambros\Warehouse\Core\Framework\ContextWarehouseExtension;
use Shopware\Core\Checkout\Cart\Order\CartConvertedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CartConvertedEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            CartConvertedEvent::class => 'onCartConverted'
        ];
    }

    public function onCartConverted(CartConvertedEvent $event): void
    {
        $context = $event->getContext();
        $warehouseId = $context->getExtension(ContextWarehouseExtension::KEY)->getWarehouseId();
        if (!$warehouseId) {
            return;
        }
        $convertedCart = $event->getConvertedCart();
        $convertedCart['warehouseId'] = $warehouseId;
        $event->setConvertedCart($convertedCart);
    }
}