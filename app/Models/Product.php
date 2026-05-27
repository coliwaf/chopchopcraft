<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Product extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, HasSlug, InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'long_description',
        'base_price',
        'wood_type',
        'finish',
        'is_active',
        'is_featured',
        'sort_order',
        'care_instructions',
        'dimensions',
    ];

    protected $casts = [
        'base_price'         => 'decimal:2',
        'is_active'          => 'boolean',
        'is_featured'        => 'boolean',
        'care_instructions'  => 'array',
        'dimensions'         => 'array',
    ];

    // ─── Spatie Sluggable ─────────────────────────────────────────────────────
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ─── Spatie MediaLibrary ─────────────────────────────────────────────────
    // 
    // RULE: never mix registerMediaCollections() inline closures WITH a
    // separate registerMediaConversions() method on the same model.
    // Use registerMediaConversions() only — it applies to all collections.
    //
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useDisk('public')
            ->useFallbackUrl('/images/placeholder-board.jpg')
            ->useFallbackPath(public_path('images/placeholder-board.jpg'));

        $this->addMediaCollection('thumbnail')
            ->useDisk('public')
            ->singleFile()
            ->useFallbackUrl('/images/placeholder-board.jpg')
            ->useFallbackPath(public_path('images/placeholder-board.jpg'));
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // Called once per media item on any collection — define all conversions here.

        // spatie/image v3 (medialibrary v11+): performOnCollections() and
        // nonQueued() were removed. Conversions now apply to all collections
        // by default. Use MEDIA_QUEUE=sync in .env for immediate conversion in dev
        $this
            ->addMediaConversion('thumb')
             ->width(400)
             ->height(400)
            //  ->sharpen(10)
             ->format('jpg');        // force JPEG — avoids WebP generation issues

        $this->addMediaConversion('card')
             ->width(800)
             ->height(600)
            //  ->sharpen(5)
             ->format('jpg');
    }

    // ─── Relationships ────────────────────────────────────────────────────────
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    public function activeVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->whereHas('variants', fn($q) => $q->where('stock_qty', '>', 0));
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────
    public function getMinPriceAttribute(): float
    {
        $min = $this->variants->min('effective_price');
        return $min ?? $this->base_price;
    }

    public function getTotalStockAttribute(): int
    {
        return $this->variants->sum('stock_qty');
    }

    public function isInStock(): bool
    {
        return $this->variants->sum('stock_qty') > 0;
    }
}
