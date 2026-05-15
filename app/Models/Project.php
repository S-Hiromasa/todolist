<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'team_id',
        'owner_id',
        'name',
        'description',
        'status',
        'deadline',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'date',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function todos(): HasMany
    {
        return $this->hasMany(Todo::class);
    }

    public function progressRate(): int
    {
        $total = $this->todos()->count();

        if ($total === 0) {
            return 0;
        }

        $done = $this->todos()
            ->where('status', Todo::STATUS_DONE)
            ->count();

        return (int) round(($done / $total) * 100);
    }
}
