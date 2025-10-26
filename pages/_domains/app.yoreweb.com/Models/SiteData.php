<?php
declare(strict_types=1);

namespace Domain\AppYorewebCom\Models;

use Modules\Orm\Core\Model;

final class SiteData extends Model
{
    protected static string $table = 'sitedata';
    protected static string $primaryKey = 'id';
    protected static array  $fillable = ['k','v','updated_at'];

    // Convenience helpers
    public static function get(string $key, mixed $default = null): mixed
    {
        $row = static::where('k', '=', $key)->first();
        return $row?->getAttribute('v') ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $existing = static::where('k', '=', $key)->first();
        if ($existing instanceof self) {
            $existing->setAttribute('v', $value);
            $existing->save();
        } else {
            static::create(['k'=>$key, 'v'=>$value, 'updated_at'=>date('c')]);
        }
    }
}
