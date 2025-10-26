<?php
declare(strict_types=1);

namespace Domain\AppYorewebCom\Models;

use Modules\Orm\Core\Model;

final class User extends Model
{
    protected static string $table = 'users';
    protected static string $primaryKey = 'id';
    protected static array  $fillable = ['name','email','is_active','created_at','updated_at'];
}
