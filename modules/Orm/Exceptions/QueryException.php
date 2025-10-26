<?php
declare(strict_types=1);

namespace Modules\Orm\Exceptions;

/**
 * Thrown when an SQL statement fails to execute.
 * Should include enough context (SQL snippet and params) in the message for debugging.
 */
class QueryException extends OrmException {}
