<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class TimeValid extends Constraint
{
    /**
     * @var string
     */
    public string $message = 'The time "{{ string }}" is not valid for input. Please insert time what answers the rules!';

    /**
     * @return string
     */
    public function validatedBy(): string
    {
        return static::class.'Validator';
    }
}
