<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\System\SalesChannel\Event;

use Ambros\Warehouse\Core\System\SalesChannel\Context\SalesChannelContextService as WarehouseSalesChannelContextService;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Validation\EntityExists;
use Shopware\Core\Framework\Validation\DataValidationDefinition;
use Shopware\Core\Framework\Validation\DataValidator;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextPersister;
use Shopware\Core\System\SalesChannel\Event\SalesChannelContextSwitchEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SalesChannelContextSwitchEventSubscriber implements EventSubscriberInterface
{
    private const WAREHOUSE_ID = WarehouseSalesChannelContextService::WAREHOUSE_ID;
    
    /**
     * @var DataValidator
     */
    protected DataValidator $validator;

    /**
     * @var SalesChannelContextPersister
     */
    protected SalesChannelContextPersister $contextPersister;

    public function __construct(
        DataValidator $validator,
        SalesChannelContextPersister $contextPersister
    )
    {
        $this->validator = $validator;
        $this->contextPersister = $contextPersister;
    }
    
    public static function getSubscribedEvents(): array
    {
        return [
            SalesChannelContextSwitchEvent::class => 'onSwitch'
        ];
    }

    public function onSwitch(SalesChannelContextSwitchEvent $event)
    {
        $salesChannelContext = $event->getSalesChannelContext();
        $context = $salesChannelContext->getContext();
        $token = $salesChannelContext->getToken();
        $salesChannelId = $salesChannelContext->getSalesChannel()->getId();
        $customer = $salesChannelContext->getCustomer();
        $customerId = $customer ? $customer->getId() : null;
        $data = $event->getRequestDataBag();
        $definition = new DataValidationDefinition('context_switch_warehouse');
        $parameters = $data->only(self::WAREHOUSE_ID);
        $warehouseCriteria = new Criteria();
        $definition->add(self::WAREHOUSE_ID, new EntityExists(['entity' => 'warehouse', 'context' => $context, 'criteria' => $warehouseCriteria]));
        $this->validator->validate($parameters, $definition);
        $this->contextPersister->save($token, $parameters, $salesChannelId, $customerId);
    }
}