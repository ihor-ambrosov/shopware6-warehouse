<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Content\Warehouse\DataAbstractionLayer\FieldSerializer;

use Ambros\Warehouse\Core\Framework\Validation\Constraint\NoSpecialChar;
use Ambros\Warehouse\Core\Framework\Validation\Constraint\NoWhitespace;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\FieldSerializer\StringFieldSerializer;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Field;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class WarehouseCodeFieldSerializer extends StringFieldSerializer
{
    private ContainerInterface $container;

    public function __construct(
        ValidatorInterface $validator,
        DefinitionInstanceRegistry $definitionRegistry,
        ContainerInterface $container
    ) {
        $this->container = $container;
        if (\Ambros\Warehouse\Version::isVersionGreaterOrEqual('6.4.3.0')) {
            $sanitizer = $this->container->get(\Shopware\Core\Framework\Util\HtmlSanitizer::class);
            parent::__construct($validator, $definitionRegistry, $sanitizer);
        } else {
            parent::__construct($validator, $definitionRegistry);
        }
    }
    
    /**
     * @param StringField $field
     *
     * @return Constraint[]
     */
    protected function getConstraints(Field $field): array
    {
        return [
            new Type('string'),
            new Length(['max' => $field->getMaxLength()]),
            new NoSpecialChar(),
            new NoWhitespace(),
            new NotBlank(),
        ];
    }
}