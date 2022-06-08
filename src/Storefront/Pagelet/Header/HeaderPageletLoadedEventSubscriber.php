<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Storefront\Pagelet\Header;

use Ambros\Warehouse\Core\Content\Warehouse\SalesChannel\AbstractWarehouseRoute;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Storefront\Pagelet\Header\HeaderPageletLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;

class HeaderPageletLoadedEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var AbstractWarehouseRoute
     */
    private AbstractWarehouseRoute $warehouseRoute;

    public function __construct(
        AbstractWarehouseRoute $warehouseRoute
    )
    {
        $this->warehouseRoute = $warehouseRoute;
    }
    
    public static function getSubscribedEvents(): array
    {
        return [
            HeaderPageletLoadedEvent::class => 'onLoaded'
        ];
    }

    public function onLoaded(HeaderPageletLoadedEvent $event)
    {
        $pagelet = $event->getPagelet();
        $salesChannelContext = $event->getSalesChannelContext();
        $criteria = new Criteria();
        $criteria->addSorting(new FieldSorting('priority', FieldSorting::DESCENDING));
        $criteria->setTitle('header::warehouses');
        $warehouses = $this->warehouseRoute->load(new Request(), $salesChannelContext, $criteria)
            ->getWarehouses();
        $activeWarehouse = $salesChannelContext->getExtension(\Ambros\Warehouse\Core\System\SalesChannel\SalesChannelContextWarehouseExtension::KEY)->getWarehouse();
        $pagelet->addExtension(
            \Ambros\Warehouse\Storefront\Pagelet\Header\HeaderPageletWarehouseExtension::KEY,
            new \Ambros\Warehouse\Storefront\Pagelet\Header\HeaderPageletWarehouseExtension($warehouses, $activeWarehouse)
        );
    }
}