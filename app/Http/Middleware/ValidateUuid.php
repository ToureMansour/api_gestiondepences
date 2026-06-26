<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\InvalidUuidStringException;

class ValidateUuid
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $parameter = 'reference'): Response
    {
        $uuid = $request->route($parameter);

        if ($uuid && !$this->isValidUuid($uuid)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid UUID format for parameter: ' . $parameter
            ], 422);
        }

        return $next($request);
    }

    private function isValidUuid(string $uuid): bool
    {
        try {
            return Uuid::isValid($uuid);
        } catch (InvalidUuidStringException $e) {
            return false;
        }
    }
}
