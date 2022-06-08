<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Content\Warehouse\SalesChannel;

use Ambros\Warehouse\Core\Content\Warehouse\SalesChannel\AbstractWarehouseRoute;
use Ambros\Warehouse\Core\Content\Warehouse\SalesChannel\WarehouseRouteResponse;
use Ambros\Warehouse\Core\Content\Warehouse\WarehouseCollection;
use OpenApi\Annotations;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\Framework\Routing\Annotation\Entity;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Routing\Annotation\Since;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"store-api"})
 */
class WarehouseRoute extends AbstractWarehouseRoute
{
    /**
     * @var EntityRepositoryInterface
     */
    private EntityRepositoryInterface $warehouseRepository;

    public function __construct(EntityRepositoryInterface $warehouseRepository)
    {
        $this->warehouseRepository = $warehouseRepository;
    }

    public function getDecorated(): AbstractWarehouseRoute
    {
        throw new DecorationPatternException(self::class);
    }

    /**
     * @Entity("warehouse")
     * @Annotations\Post(
     *      path="/warehouse",
     *      summary="Loads all available warehouses",
     *      operationId="readWarehouse",
     *      tags={"Store API", "Warehouse"},
     *      @Annotations\Parameter(name="Api-Basic-Parameters"),
     *      @Annotations\Response(
     *          response="200",
     *          description="All available warehouses",
     *          @Annotations\JsonContent(ref="#/components/schemas/warehouse_flat")
     *     )
     * )
     * @Route("/store-api/warehouse", name="store-api.warehouse", methods={"GET", "POST"})
     */
    public function load(Request $request, SalesChannelContext $context, Criteria $criteria): WarehouseRouteResponse
    {
        /** @var WarehouseCollection $warehouseCollection */
        $warehouseCollection = $this->warehouseRepository->search($criteria, $context->getContext())->getEntities();
        return new WarehouseRouteResponse($warehouseCollection);
    }
}
