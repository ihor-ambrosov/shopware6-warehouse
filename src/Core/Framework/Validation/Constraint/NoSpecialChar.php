<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Framework\Validation\Constraint;

use Symfony\Component\Validator\Constraints\Regex;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class NoSpecialChar extends Regex
{
    public const NOSPECIALCHAR_FAILED_ERROR = '1c0bea16-5c1a-11ec-bf63-0242ac130002';
    
    protected static $errorNames = [
        self::NOSPECIALCHAR_FAILED_ERROR => 'NOSPECIALCHAR_FAILED_ERROR',
    ];
    
    public $message = 'This value contains special chars.';
    
    public function __construct()
    {
        parent::__construct('/^[^:\$\{\}\[\]\(\)\.\*]*$/');
    }
}