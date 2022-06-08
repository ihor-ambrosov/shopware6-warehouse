<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\System\SalesChannel\Context;

use Ambros\Warehouse\Core\Content\Warehouse\WarehouseEntity;
use Ambros\Warehouse\Core\Framework\ContextWarehouseExtension;
use Ambros\Warehouse\Core\System\SalesChannel\Context\SalesChannelContextService;
use Ambros\Warehouse\Core\System\SalesChannel\SalesChannelContextWarehouseExtension;
use Ambros\Warehouse\Defaults as WarehouseDefaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\Context\AbstractSalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class SalesChannelContextFactory extends AbstractSalesChannelContextFactory
{
    /**
     * @var AbstractSalesChannelContextFactory
     */
    private AbstractSalesChannelContextFactory $decorated;

    /**
     * @var EntityRepositoryInterface
     */
    private EntityRepositoryInterface $warehouseRepository;

    public function __construct(
        AbstractSalesChannelContextFactory $decorated,
        EntityRepositoryInterface $warehouseRepository
    ) {
        $this->decorated = $decorated;
        $this->warehouseRepository = $warehouseRepository;
    }

    public function getDecorated(): AbstractSalesChannelContextFactory
    {
        return $this->decorated;
    }

    public function create(string $token, string $salesChannelId, array $options = []): SalesChannelContext
    {
        $salesChannelContext = $this->getDecorated()->create($token, $salesChannelId, $options);
        $context = $salesChannelContext->getContext();
        $warehouse = null;
        if (!empty($options[SalesChannelContextService::WAREHOUSE_ID])) {
            $warehouse = $this->getWarehouse($context, $options[SalesChannelContextService::WAREHOUSE_ID]);
        }
        if (empty($warehouse)) {
            $warehouse = $this->getWarehouse($context, WarehouseDefaults::WAREHOUSE_ID);
        }
        $salesChannelContext->addExtension(SalesChannelContextWarehouseExtension::KEY, new SalesChannelContextWarehouseExtension($warehouse));
        $context->addExtension(ContextWarehouseExtension::KEY, new ContextWarehouseExtension($warehouse->getId()));
        return $salesChannelContext;
    }
    
    private function getWarehouse(Context $context, string $warehouseId): ?WarehouseEntity
    {
        $criteria = new Criteria([$warehouseId]);
        $criteria->setTitle('context-factory::warehouse');
        return $this->warehouseRepository->search($criteria, $context)->get($warehouseId);
    }
}