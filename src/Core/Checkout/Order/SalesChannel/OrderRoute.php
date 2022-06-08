<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Checkout\Order\SalesChannel;

use OpenApi\Annotations as OA;
use Shopware\Core\Checkout\Order\SalesChannel\OrderRoute as ParentOrderRoute;
use Shopware\Core\Checkout\Order\SalesChannel\OrderRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Annotation\Entity;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Routing\Annotation\Since;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OrderRoute extends ParentOrderRoute
{
    /**
     * @var ParentOrderRoute
     */
    private ParentOrderRoute $decorated;

    public function __construct(
        ParentOrderRoute $decorated
    )
    {
        $this->decorated = $decorated;
    }

    public function getDecorated(): ParentOrderRoute
    {
        return $this->decorated;
    }

    /**
     * @Since("6.2.0.0")
     * @Entity("order")
     * @OA\Post(
     *      path="/order",
     *      summary="Listing orders",
     *      operationId="readOrder",
     *      tags={"Store API", "Order"},
     *      @OA\Parameter(name="Api-Basic-Parameters"),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="checkPromotion", description="Wether to check the Promotions of orders", type="string"),
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="",
     *          @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/order_flat"))
     *     )
     * )
     * @Route(path="/store-api/order", name="store-api.order", methods={"GET", "POST"})
     */
    public function load(Request $request, SalesChannelContext $context, Criteria $criteria): OrderRouteResponse
    {
        $criteria->addAssociation('warehouse');
        return $this->getDecorated()->load($request, $context, $criteria);
    }
}