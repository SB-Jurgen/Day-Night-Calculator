<?php
namespace App\Validator\Constraints;

use DateTimeImmutable;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TimeValidValidator extends ConstraintValidator
{
    /**
     * @param $value
     * @param Constraint $constraint
     * @return void
     */
    public function validate($value, Constraint $constraint): void
    {
        /* @var $constraint TimeValid */
        if (!$this->isValidTimeFormat($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value->format('H:i'))
                ->addViolation();
        }
    }

    /**
     * @param DateTimeImmutable $time
     * @return bool
     */
    private function isValidTimeFormat(DateTimeImmutable $time): bool
    {
        [$hours, $minutes] = explode(':', $time->format('H:i'));
        return in_array($minutes, ['00', '15', '30', '45']);
    }
}
