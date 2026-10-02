<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class HomecareService extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Ikon garis yang boleh dipilih pada form layanan.
     * Nama harus sama dengan kunci pada components/icon.blade.php.
     */
    public const ICON_OPTIONS = [
        'stethoscope' => 'Stetoskop',
        'activity' => 'Tanda vital',
        'heart' => 'Hati',
        'clipboard' => 'Klip papan',
        'document' => 'Dokumen',
        'shield' => 'Perisai',
        'chart' => 'Grafik',
        'search' => 'Pencarian',
        'sparkles' => 'Kilau',
        'star' => 'Bintang',
        'plus' => 'Tambah',
        'list' => 'Daftar',
        'question' => 'Tanya',
        'user' => 'Satu orang',
        'users' => 'Beberapa orang',
        'home' => 'Rumah',
        'money' => 'Uang',
        'wallet' => 'Dompet',
        'calendar' => 'Kalender',
        'clock' => 'Jam',
        'ambulance' => 'Ambulans',
        'bell' => 'Lonceng',
        'image' => 'Gambar',
        'mail' => 'Surat',
        'phone' => 'Telepon',
        'check' => 'Centang',
    ];

    protected $fillable = [
        'code',
        'name',
        'slug',
        'icon',
        'thumbnail',
        'category',
        'description',
        'short_description',
        'duration_minutes',
        'price',
        'jasa_sarana',
        'jasa_pelayanan',
        'price_note',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'jasa_sarana' => 'decimal:2',
            'jasa_pelayanan' => 'decimal:2',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function requestItems(): HasMany
    {
        return $this->hasMany(HomecareRequestItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public static function slugify(string $name): string
    {
        return Str::slug($name);
    }

    /**
     * Ikon garis yang ditampilkan bila layanan tidak punya thumbnail.
     */
    public function displayIcon(): string
    {
        return $this->icon ?: 'heart';
    }

    /**
     * URL thumbnail ilustrasi (SVG) — null bila belum diunggah.
     */
    public function thumbnailUrl(): ?string
    {
        if (! $this->thumbnail) {
            return null;
        }

        if (Str::startsWith($this->thumbnail, ['http://', 'https://', '//'])) {
            return $this->thumbnail;
        }

        return asset(ltrim($this->thumbnail, '/'));
    }

    public function hasTariffBreakdown(): bool
    {
        return (float) $this->jasa_sarana > 0 || (float) $this->jasa_pelayanan > 0;
    }

    public function formattedPrice(): string
    {
        return self::formatRupiah($this->price);
    }

    public function formattedSarana(): string
    {
        return self::formatRupiah($this->jasa_sarana);
    }

    public function formattedPelayanan(): string
    {
        return self::formatRupiah($this->jasa_pelayanan);
    }

    public static function formatRupiah(mixed $amount): string
    {
        return 'Rp '.number_format((float) $amount, 0, ',', '.');
    }
}
