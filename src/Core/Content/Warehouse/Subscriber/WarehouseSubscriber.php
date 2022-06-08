<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Content\Warehouse\Subscriber;

use Doctrine\DBAL\Connection;
use Ambros\Warehouse\Core\Content\Warehouse\WarehouseDefinition;
use Ambros\Warehouse\Defaults as WarehouseDefaults;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityDeletedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\DeleteCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\InsertCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\UpdateCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\WriteCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Validation\PreWriteValidationEvent;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\WriteConstraintViolationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;

class WarehouseSubscriber implements EventSubscriberInterface
{
    public const VIOLATION_DEFAULT_WAREHOUSE_DELETE = 'default_warehouse_delete_violation';
    public const VIOLATION_DEFAULT_WAREHOUSE_CODE = 'default_warehouse_code_violation';
    public const VIOLATION_WAREHOUSE_UNIQUE_CODE = 'warehouse_unique_code_violation';

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PreWriteValidationEvent::class => 'preValidate',
            'warehouse.deleted' => 'onDeleted',
        ];
    }

    public function onDeleted(EntityDeletedEvent $event): void
    {
        $warehouseId = Uuid::fromHexToBytes(WarehouseDefaults::WAREHOUSE_ID);
        $this->connection->executeUpdate(
            'UPDATE `order` SET `warehouse_id` = :warehouseId WHERE `warehouse_id` IS NULL',
            [ 'warehouseId' => $warehouseId ]
        );
    }

    public function preValidate(PreWriteValidationEvent $event): void
    {
        if (!\count($this->getWarehouseCommands($event))) {
            return;
        }
        $this->validateDefaultWarehouseDelete($event);
        $this->validateDefaultWarehouseCode($event);
        $this->validateWarehouseUniqueCode($event);
    }
    
    private function validateDefaultWarehouseDelete(PreWriteValidationEvent $event): void
    {
        foreach ($this->getWarehouseDeleteCommands($event) as $command) {
            $warehouseId = $this->getCommandWarehouseId($command);
            if ($warehouseId !== WarehouseDefaults::WAREHOUSE_ID) {
                continue;
            }
            $violations = new ConstraintViolationList();
            $violations->add(
                $this->buildViolation(
                    'The default warehouse cannot be deleted.',
                    [],
                    'id',
                    $warehouseId,
                    self::VIOLATION_DEFAULT_WAREHOUSE_DELETE
                )
            );
            $event->getExceptions()->add(new WriteConstraintViolationException($violations, $command->getPath()));
        }
    }
    
    private function validateDefaultWarehouseCode(PreWriteValidationEvent $event): void
    {
        foreach ($this->getWarehouseInsertOrUpdateCommands($event) as $command) {
            $payload = $command->getPayload();
            $warehouseId = $this->getCommandWarehouseId($command);
            if ($warehouseId !== WarehouseDefaults::WAREHOUSE_ID) {
                continue;
            }
            if (!\array_key_exists('code', $payload)) {
                continue;
            }
            $code = $payload['code'];
            if ($code === WarehouseDefaults::WAREHOUSE_CODE) {
                continue;
            }
            $violations = new ConstraintViolationList();
            $violations->add(
                $this->buildViolation(
                    'The default warehouse code can\'t be changed.',
                    [],
                    'code',
                    $code,
                    self::VIOLATION_DEFAULT_WAREHOUSE_CODE
                )
            );
            $event->getExceptions()->add(new WriteConstraintViolationException($violations, $command->getPath()));
        }
    }
    
    private function validateWarehouseUniqueCode(PreWriteValidationEvent $event): void
    {
        foreach ($this->getWarehouseInsertOrUpdateCommands($event) as $command) {
            $payload = $command->getPayload();
            if (!\array_key_exists('code', $payload)) {
                continue;
            }
            $warehouseId = $this->getCommandWarehouseId($command);
            $code = $payload['code'];
            if (!$this->isWarehouseCodeExists($code, $warehouseId)) {
                continue;
            }
            $violations = new ConstraintViolationList();
            $violations->add(
                $this->buildViolation(
                    'The warehouse code {{ code }} is already in use.',
                    ['{{ code }}' => $code],
                    'code',
                    $code,
                    self::VIOLATION_WAREHOUSE_UNIQUE_CODE
                )
            );
            $event->getExceptions()->add(new WriteConstraintViolationException($violations, $command->getPath()));
        }
    }

    private function isWarehouseCodeExists(string $code, string $excludeWarehouseId): bool
    {
        $statement = $this->connection->executeQuery(
            'SELECT COUNT(*) FROM warehouse warehouse WHERE warehouse.code = :code AND warehouse.id != :excludeWarehouseId',
            [
                'code' => $code,
                'excludeWarehouseId' => Uuid::fromHexToBytes($excludeWarehouseId),
            ]
        );
        return (bool) $statement->fetchOne();
    }

    private function getWarehouseCommands(PreWriteValidationEvent $event): array
    {
        return \array_filter($event->getCommands(), function (WriteCommand $command) {
            return $command->getDefinition()->getClass() === WarehouseDefinition::class;
        });
    }
    
    private function getWarehouseDeleteCommands(PreWriteValidationEvent $event): array
    {
        return \array_filter($this->getWarehouseCommands($event), function (WriteCommand $command) {
            return $command instanceof DeleteCommand;
        });
    }
    
    private function getWarehouseInsertOrUpdateCommands(PreWriteValidationEvent $event): array
    {
        return \array_filter($this->getWarehouseCommands($event), function (WriteCommand $command) {
            return ($command instanceof InsertCommand) || ($command instanceof UpdateCommand);
        });
    }
    
    private function getCommandWarehouseId(WriteCommand $command)
    {
        return \mb_strtolower(Uuid::fromBytesToHex($command->getPrimaryKey()['id']));
    }

    private function buildViolation(
        string $messageTemplate,
        array $parameters,
        ?string $propertyPath,
        $invalidValue,
        string $code
    ): ConstraintViolationInterface {
        return new ConstraintViolation(
            \str_replace(\array_keys($parameters), \array_values($parameters), $messageTemplate),
            $messageTemplate,
            $parameters,
            null,
            '/'.$propertyPath,
            $invalidValue,
            null,
            $code
        );
    }
}