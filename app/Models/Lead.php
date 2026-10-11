<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_ARCHIVED = 'archived';

    public const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_CONTACTED,
        self::STATUS_ARCHIVED,
    ];

    /**
     * The budget bands offered on the public contact form, in INR. Single
     * source of truth: the form renders these and the controller validates
     * against them, so a tampered or mis-encoded hidden input fails
     * validation cleanly instead of reaching the database. Leads saved
     * before the switch from USD keep their original text.
     */
    public const BUDGETS = [
        'Under ₹2 lakh',
        '₹2 – 5 lakh',
        '₹5 – 15 lakh',
        '₹15 lakh+',
        'Not sure yet',
    ];

    protected $fillable = [
        'name',
        'email',
        'company',
        'budget',
        'brief',
        'status',
        'ip_address',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }
}
