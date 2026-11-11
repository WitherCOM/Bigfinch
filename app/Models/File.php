<?php

namespace App\Models;

use Database\Factories\FileFactory;
use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids as HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    /** @use HasFactory<FileFactory> */
    use HasFactory;

    use HasUuids;

    protected $fillable = [
        'name',
        'path',
        'mime_type',
        'size',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (File $file) {
            $file->user_id ??= auth()->id();
        });

        static::deleted(function (File $file) {
            Storage::delete($file->path);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(Transaction::class)->withTimestamps();
    }

    /**
     * Convert the stored image to grayscale in place and refresh its size.
     * Non-image files and unsupported formats are left untouched.
     */
    public function convertToGrayscale(): void
    {
        $encoders = [
            'image/jpeg' => fn ($image) => imagejpeg($image, null, 85),
            'image/png' => fn ($image) => imagepng($image),
            'image/webp' => fn ($image) => imagewebp($image, null, 85),
        ];

        if (! isset($encoders[$this->mime_type])) {
            return;
        }

        $image = @imagecreatefromstring(Storage::get($this->path));

        if ($image === false) {
            return;
        }

        imagefilter($image, IMG_FILTER_GRAYSCALE);
        imagesavealpha($image, true);

        ob_start();
        $encoders[$this->mime_type]($image);
        $contents = ob_get_clean();

        Storage::put($this->path, $contents);
        $this->size = strlen($contents);
    }
}
