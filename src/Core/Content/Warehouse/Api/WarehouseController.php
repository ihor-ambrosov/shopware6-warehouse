<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Content\Warehouse\Api;

use Ambros\Warehouse\Defaults as WarehouseDefaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\Annotation\Acl;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Routing\Annotation\Since;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"api"})
 */
class WarehouseController extends AbstractController
{
    /**
     * @Since("6.0.0.0")
     * @Route("/api/_action/warehouse/get-default-id", name="api.action.core.warehouse.get-default-id", methods={"GET"})
     * @Acl({"warehouse:read"})
     */
    public function getDefaultId(Request $request, Context $context): JsonResponse
    {
        return new JsonResponse(WarehouseDefaults::WAREHOUSE_ID);
    }
}