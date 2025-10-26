<?php
declare(strict_types=1);

namespace Domain\AppYorewebCom\Models;

use Modules\Orm\Core\Model;

final class Text extends Model
{
    protected static string $table = 'texts';
    protected static string $primaryKey = 'id';
    protected static array  $fillable = ['from','to','name','address','body','datetime','created_at','updated_at'];
}
