<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'price',
        'unit_of_measure',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // Produtos que compõem este produto
    public function components()
    {
        return $this->belongsToMany(
            Product::class,
            'product_components',
            'product_id',
            'component_id'
        )->withPivot('quantity')->withTimestamps();
    }

    // Produtos dos quais este produto faz parte
    public function parents()
    {
        return $this->belongsToMany(
            Product::class,
            'product_components',
            'component_id',
            'product_id'
        )->withPivot('quantity')->withTimestamps();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function formattedPrice(): string
    {
        return 'R$ ' . number_format($this->price, 2, ',', '.');
    }
}
