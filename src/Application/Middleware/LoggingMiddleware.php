<?php

namespace App\Application\Middleware;

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger; // Make sure Logger is imported
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class LoggingMiddleware implements MiddlewareInterface
{
    public function process(Request $request, Handler $next): Response
    {
        $startTime = hrtime(true);
        
        $response = $next->handle($request);
        
        $durationMs = (hrtime(true) - $startTime) / 1_000_000;

        // Monolog 2 syntax:
        $logger = new Logger('cliques-api');
        
        // Use Logger::INFO instead of Level::Info
        $handler = new StreamHandler(__DIR__ . '/../../../logs/app.log', Logger::INFO);
        $handler->setFormatter(new JsonFormatter());
        $logger->pushHandler($handler);

        $logger->info('http.request.completed', [
            'requestId' => $request->getAttribute('requestId', 'unknown'),
            'method' => $request->getMethod(),
            'path' => $request->getUri()->getPath(),
            'status' => $response->getStatusCode(),
            'mediaType' => $response->getHeaderLine('Content-Type'),
            'durationMs' => round($durationMs, 2),
        ]);

        return $response;
    }
}