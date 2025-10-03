<?php
namespace App\Exceptions;

use DomainException;

class SchemaValidationException extends DomainException
{
    /** @var array<int,string> */
    private array $missing;

    /**
     * @param array<int,string> $missing
     */
    public function __construct(array $missing)
    {
        parent::__construct('schema_validation_failed');
        $this->missing = $missing;
    }

    /**
     * @return array<int,string>
     */
    public function missing(): array
    {
        return $this->missing;
    }
}
