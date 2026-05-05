<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending    = 'pending';
    case InProgress = 'in_progress';
    case Completed  = 'completed';
    case Delivered  = 'delivered';

    public function label(): string
    {
        return match($this) {
            self::Pending    => 'Pending',
            self::InProgress => 'In Progress',
            self::Completed  => 'Completed',
            self::Delivered  => 'Delivered',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending    => 'yellow',
            self::InProgress => 'blue',
            self::Completed  => 'green',
            self::Delivered  => 'zinc',
        };
    }
}
