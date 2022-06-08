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
class NoWhitespace extends Regex
{
    public const NOWHITESPACE_FAILED_ERROR = '0473cde4-5c13-11ec-bf63-0242ac130002';
    
    protected static $errorNames = [
        self::NOWHITESPACE_FAILED_ERROR => 'NOWHITESPACE_FAILED_ERROR',
    ];
    
    public $message = 'This value contains whitespaces.';

    public function __construct()
    {
        parent::__construct('/^\S*$/');
    }
}