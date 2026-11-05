<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Middleware;
use Core\Request;

/** Journalisation simple dans storage/logs/app.log. */
final class LoggingMiddleware implements Middleware
{
    public function handle(Request $request, callable $next): mixed
    {
        $line = sprintf(
            "[%s] %s %s\n",
            date('Y-m-d H:i:s'),
            $request->method(),
            $request->path()
        );

        $dir = BASE_PATH . '/storage/logs';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($dir . '/app.log', $line, FILE_APPEND);

        return $next($request);
    }
}
