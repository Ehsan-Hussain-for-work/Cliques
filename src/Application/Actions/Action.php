<?php

declare(strict_types=1);

namespace App\Application\Actions;

use Symfony\Component\Yaml\Yaml;
use App\Domain\DomainException\DomainRecordNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

abstract class Action
{
    protected LoggerInterface $logger;

    protected Request $request;

    protected Response $response;

    protected array $args;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @throws HttpNotFoundException
     * @throws HttpBadRequestException
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;

        try {
            return $this->action();
        } catch (DomainRecordNotFoundException $e) {
            throw new HttpNotFoundException($this->request, $e->getMessage());
        }
    }

    /**
     * @throws DomainRecordNotFoundException
     * @throws HttpBadRequestException
     */
    abstract protected function action(): Response;

    /**
     * @return array|object
     */
    protected function getFormData()
    {
        return $this->request->getParsedBody();
    }

    /**
     * @return array
     */
    protected function getURLData()
    {
        return $this->request->getQueryParams();
    }

    /**
     * @return mixed
     * @throws HttpBadRequestException
     */
    protected function resolveArg(string $name)
    {
        if (!isset($this->args[$name])) {
            throw new HttpBadRequestException($this->request, "Could not resolve argument `{$name}`.");
        }

        return $this->args[$name];
    }

    /**
     * @param array|object|null $data
     */
    protected function respondWithData($data = null, int $statusCode = 200): Response
    {
        $payload = new ActionPayload($statusCode, $data);

        return $this->respond($payload);
    }

    protected function respond(ActionPayload $payload): Response
    {
        $media = $this->request->getAttribute('responseFormat', 'application/json');
        $data = $payload->getData();

        // PSR-17 response factory; mapped $view.
        $bytes = match ($media) {
            'application/json' => json_encode($data, JSON_PRETTY_PRINT),
            'application/xml'  => $this->encodeXml($data),
            'application/yaml' => $this->encodeYaml($data),
            default => json_encode($data, JSON_PRETTY_PRINT),
        };
        $type = $media === 'application/xml' ? $media . '; charset=utf-8' : $media;
        $this->response->getBody()->write($bytes);
        return $this->response
            ->withHeader('Content-Type', $type)
            ->withHeader('Vary', 'Accept')
            ->withHeader('Cache-Control', 'no-store')
            ->withStatus($payload->getStatusCode());
    }

    private function encodeXml(array $data): string
    {
        $xml = new \XMLWriter;
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('response');

        $this->arrayToXml($data, $xml);

        $xml->endElement();
        $xml->endDocument();
        return $xml->outputMemory();
    }

    private function encodeYaml(array $data): string
    {
        //5 = max inline dept & 3 = spaces per indent
        // return Yaml::dump($data, 5, 3);
        return 'todo: implement YAML encoding';
    }

    private function arrayToXml(array $data, \XMLWriter $xml): void
    {
        foreach ($data as $key => $value) {
            $key = is_numeric($key) ? 'item' : $key;

            $xml->startElement($key);

            if (\is_array($value) || \is_object($value)) {
                $this->arrayToXml($value, $xml);
            } elseif (\is_bool($value)) {
                $xml->text($value ? 'true' : 'false');
            } elseif ($value === null) {
                $xml->writeAttributeNS('xsi', 'nil', 'http://www.w3.org/2001/XMLSchema-instance', 'true');
            } else {
                $xml->text((string) $value);
            }

            $xml->endElement();
        }
    }
}
