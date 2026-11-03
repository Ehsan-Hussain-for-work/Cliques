<?php

declare(strict_types=1);

use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },

        PDO::class => function () {
            // Database Configuration
            $host = '127.0.0.1'; //'localhost';
            $db = 'cliques_db';
            $user = 'root'; // Update with your DB credentials if different
            $pass = '';
            $charset = 'utf8mb4';

            // Data Source Name
            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

            // PDO Options
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                $pdo = new PDO($dsn, $user, $pass, $options);
                return $pdo;
            } catch (\PDOException $e) {
                http_response_code(500); // 500 Internal Server Error
                echo json_encode(["error" => "Database connection failed"]);
                exit;
            }
        }
    ]);
};
