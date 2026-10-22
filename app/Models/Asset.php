<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    protected $guarded = ['id'];

    protected $table = 'assets';

    protected function casts(): array
    {
        return ['active' => 'boolean', 'value' => 'decimal:2', 'registered_on' => 'date'];
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'entity_id')->where('entity_type', 'assets');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(OrganizationalUnit::class, 'organizational_unit_id');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'current_person_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function budgetItem(): BelongsTo
    {
        return $this->belongsTo(BudgetItem::class, 'budget_item_id');
    }

    public function physicalStatus(): BelongsTo
    {
        return $this->belongsTo(PhysicalStatus::class, 'physical_status_id');
    }

    public function administrativeStatus(): BelongsTo
    {
        return $this->belongsTo(AdministrativeStatus::class, 'administrative_status_id');
    }
}
