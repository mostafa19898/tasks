<?php
namespace Taskboard\Support;

final class Http
{
    /** CORS + Content-Type */
    public static function setupCors(string $allowedOrigin = '*'): void
    {
        header("Access-Control-Allow-Origin: {$allowedOrigin}");
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-HTTP-Method-Override');
        header('Content-Type: application/json; charset=utf-8');
    }

    public static function isPreflight(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS';
    }

    /** Decode JSON body (throws on invalid) */
    public static function json(): array
    {
        $raw = file_get_contents('php://input') ?: '';
        if ($raw === '') return [];
        $data = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            throw new \InvalidArgumentException('Invalid JSON payload');
        }
        return $data;
    }

    /** Low-level sender */
    public static function send(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        if ($status === 204) { return; }
        echo is_string($data) ? $data : json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    // Helpers
    public static function ok(mixed $data): void         { self::send($data, 200); }
    public static function created(mixed $data): void    { self::send($data, 201); }
    public static function noContent(): void             { self::send('', 204); }
    public static function badRequest(string $msg): void { self::send(['error'=>$msg], 400); }
    public static function notFound(string $msg='Not Found'): void { self::send(['error'=>$msg], 404); }
    public static function methodNotAllowed(array $allow): void {
        header('Allow: '.implode(', ', $allow));
        self::send(['error'=>'Method Not Allowed'], 405);
    }
    public static function serverError(): void           { self::send(['error'=>'Internal Server Error'], 500); }

    public static function toInt(mixed $v, int $default = 0): int
    {
        if (is_int($v)) return $v;
        if (is_string($v) && ctype_digit($v)) return (int)$v;
        return $default;
    }
}
