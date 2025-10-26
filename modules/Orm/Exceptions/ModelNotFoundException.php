<?php
declare(strict_types=1);

namespace Modules\Orm\Exceptions;

/**
 * Thrown when a Model::find fails to locate a record.
 */
class ModelNotFoundException extends OrmException {}
