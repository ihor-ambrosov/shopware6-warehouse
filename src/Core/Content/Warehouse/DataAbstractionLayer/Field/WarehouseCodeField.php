<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Content\Warehouse\DataAbstractionLayer\Field;

use Ambros\Warehouse\Core\Content\Warehouse\DataAbstractionLayer\FieldSerializer\WarehouseCodeFieldSerializer;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;

class WarehouseCodeField extends StringField
{
    public function __construct() {
        parent::__construct(
            'code',
            'code',
            255
        );
        $this->addFlags(new ApiAware(), new Required(), new SearchRanking(SearchRanking::MIDDLE_SEARCH_RANKING));
    }
    
    protected function getSerializerClass(): string
    {
        return WarehouseCodeFieldSerializer::class;
    }
}
