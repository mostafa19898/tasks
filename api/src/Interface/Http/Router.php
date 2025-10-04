<?php
namespace Taskboard\Interface\Http;

use Taskboard\Support\Http;

final class Router
{
    private array $routes = [];
    public function __construct(private ?object $controller = null) {}

    public function register(array $routes): void
    {
        foreach ($routes as [$m, $pattern, $handler]) {
            if (is_array($handler) && $this->controller && $handler[0] === $this->controller::class) {
                $handler = [$this->controller, $handler[1]];
            }
            $this->routes[] = [
                'method'  => strtoupper($m),
                'pattern' => $this->normalize($pattern),
                'handler' => $handler,
            ];
        }
    }

    public function handle(): void
    {
        Http::setupCors($_ENV['CORS_ALLOW_ORIGIN'] ?? '*');
        if (Http::isPreflight()) { Http::noContent(); return; }

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $rawPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';


        if (str_starts_with($rawPath, '/api')) {
            $rawPath = substr($rawPath, 4);
        }


        $rawPath = ltrim($rawPath, '/');
        if (str_starts_with($rawPath, 'task.php')) {
            $rawPath = substr($rawPath, strlen('task.php'));
        }

        $path = $this->normalize($rawPath === '' ? '/' : $rawPath);

        $matchedPattern = null;
        $allowedForPattern = [];

        try {
            foreach ($this->routes as $r) {
                $params = $this->match($r['pattern'], $path);
                if ($params === null) continue;

                $matchedPattern = $r['pattern'];
                $allowedForPattern[] = $r['method'];
                if ($r['method'] !== $method) continue;

                $payload = Http::json();
                [$obj, $meth] = $r['handler'];


                $result = match ($meth) {
                    'index'   => $obj->index(),
                    'show'    => $obj->show(Http::toInt($params['id'] ?? 0)),
                    'store'   => $obj->store($payload),
                    'update'  => $obj->update(Http::toInt($params['id'] ?? 0), $payload),
                    'destroy' => $obj->destroy(Http::toInt($params['id'] ?? 0)),
                    default   => throw new \RuntimeException('Unknown handler method '.$meth),
                };

                // Centralize responses
                if ($meth === 'store') { Http::created($result); return; }
                if ($meth === 'destroy') { Http::noContent(); return; }
                Http::ok($result); return;
            }

            if ($matchedPattern !== null) {
                Http::methodNotAllowed(array_values(array_unique($allowedForPattern)));
                return;
            }

            Http::notFound();
        } catch (\InvalidArgumentException $e) {
            Http::badRequest($e->getMessage());
        } catch (\Throwable $e) {
            error_log('[Router] '.$e->getMessage());
            Http::serverError();
        }
    }

    private function normalize(string $p): string { return '/' . trim($p, '/'); }

    private function match(string $pattern, string $path): ?array
    {
        $pattern = $this->normalize($pattern);
        $path    = $this->normalize($path);

        $names = [];
        $regex = preg_replace_callback(
            '#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#',
            function ($m) use (&$names) { $names[] = $m[1]; return '([^/]+)'; },
            $pattern
        );

        $regex = '#^' . str_replace('/', '\/', $regex) . '$#';
        if (!preg_match($regex, $path, $m)) return null;

        array_shift($m);
        $params = [];
        foreach ($m as $i => $val) {
            $params[$names[$i] ?? (string)$i] = ctype_digit($val) ? (int)$val : $val;
        }
        return $params;
    }
}
