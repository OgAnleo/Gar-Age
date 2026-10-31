<?php

namespace GarAge\Billing\Domain;

enum FeeStatus: string
{
    case Pending = 'pending';
    case Overdue = 'overdue';
    case Paid = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Overdue => 'Vencida',
            self::Paid => 'Pagada',
        };
    }
}
