<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class LoggingService
{
    private string $context;

    public function __construct()
    {
        $this->context = 'api';
    }

    public function logInfo(string $message, array $context = []): void
    {
        Log::info($message, array_merge(['context' => $this->context], $context));
    }

    public function logError(string $message, array $context = []): void
    {
        Log::error($message, array_merge(['context' => $this->context], $context));
    }

    public function logWarning(string $message, array $context = []): void
    {
        Log::warning($message, array_merge(['context' => $this->context], $context));
    }

    public function logDebug(string $message, array $context = []): void
    {
        Log::debug($message, array_merge(['context' => $this->context], $context));
    }

    public function logAction(string $action, string $entity, string $reference, ?int $userId = null, array $additionalData = []): void
    {
        $logData = array_merge([
            'action' => $action,
            'entity' => $entity,
            'reference' => $reference,
            'user_id' => $userId ?? auth()->id(),
            'timestamp' => now()->toIso8601String(),
        ], $additionalData);

        $this->logInfo("Action performed: {$action} on {$entity}", $logData);
    }

    public function logAuthAttempt(string $email, bool $success, ?string $ip = null): void
    {
        $logData = [
            'action' => 'auth_attempt',
            'email' => $email,
            'success' => $success,
            'ip' => $ip ?? request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ];

        if ($success) {
            $this->logInfo('Authentication successful', $logData);
        } else {
            $this->logWarning('Authentication failed', $logData);
        }
    }

    public function logValidationError(string $endpoint, array $errors): void
    {
        $logData = [
            'action' => 'validation_error',
            'endpoint' => $endpoint,
            'errors' => $errors,
            'user_id' => auth()->id(),
            'timestamp' => now()->toIso8601String(),
        ];

        $this->logWarning('Validation failed', $logData);
    }

    public function logException(\Exception $exception, string $context = ''): void
    {
        $logData = [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'context' => $context,
            'user_id' => auth()->id(),
            'timestamp' => now()->toIso8601String(),
        ];

        $this->logError('Exception occurred', $logData);
    }
}
