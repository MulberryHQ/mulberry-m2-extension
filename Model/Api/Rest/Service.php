<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2019 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Model\Api\Rest;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Mulberry\Warranty\Api\Config\HelperInterface;
use Mulberry\Warranty\Api\Rest\ServiceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class Service implements ServiceInterface
{
    /**
     * Holds headers to be sent in HTTP request
     *
     * @var array
     */
    private $headers = [];

    /**
     * The base URL to interact with
     *
     * @var string
     */
    private $uri = '';

    /**
     * @var Client $client
     */
    private $client;

    /**
     * @var Json $serializer
     */
    private $serializer;

    /**
     * @var LoggerInterface $log
     */
    private $log;

    /**
     * @var HelperInterface $configHelper
     */
    private $configHelper;

    /**
     * Service constructor.
     *
     * @param LoggerInterface $log
     * @param Json $serializer
     * @param HelperInterface $configHelper
     */
    public function __construct(LoggerInterface $log, Json $serializer, HelperInterface $configHelper)
    {
        $this->log = $log;
        $this->serializer = $serializer;
        $this->configHelper = $configHelper;
        $this->uri = $configHelper->getPartnerUrl();

        /**
         * Client cannot be injected in constructor because Magento Object Manager in 2.1 has problems with it
         */
        $this->client = new Client(['timeout' => 5, 'connect_timeout' => 5]);
    }

    /**
     * {@inheritdoc}
     */
    public function setHeader($header, $value = null)
    {
        if (!$value) {
            unset($this->headers[$header]);

            return;
        }

        $this->headers[$header] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function makeRequest($url, $body = '', $method = ServiceInterface::GET): array
    {
        $response = [
            'is_successful' => false,
        ];

        try {
            $this->setHeader('Content-Type', 'application/json');
            $this->setHeader('Authorization', sprintf('Bearer %s', $this->configHelper->getApiToken()));

            if (!$this->uri) {
                throw new LocalizedException(__('Partner URL setting is not set'));
            }

            $data = [
                'headers' => $this->headers,
                'json' => $body,
            ];

            /** @var ResponseInterface $response */
            $response = $this->client->$method($this->uri . $url, $data);
            $response = $this->processResponse($response);

            $response['is_successful'] = true;
        } catch (BadResponseException $e) {
            $this->log->error('Bad Response: ' . $e->getMessage());
            $this->log->error((string)$e->getRequest()->getBody());

            $response['response_status_code'] = $e->getResponse()->getStatusCode();
            $response['response_status_message'] = $e->getResponse()->getReasonPhrase();
            $response = $this->processResponse($response);

            if ($e->hasResponse()) {
                $errorResponse = $e->getResponse();
                $this->log->error($errorResponse->getStatusCode() . ' ' . $errorResponse->getReasonPhrase());
                $body = $this->processResponse($errorResponse);
                $response = array_merge($response, $body);
            }

            $response['exception_code'] = $e->getCode();
        } catch (LocalizedException $e) {
            $this->log->error('Exception: ' . $e->getMessage());
            $response['exception_code'] = $e->getCode();
        }

        $this->logRequestResponse($body, $response, $url);

        return $response;
    }

    /**
     * Process the response and return an array
     *
     * @param $response
     *
     * @return array|mixed
     */
    private function processResponse($response)
    {
        $data = [];

        if (is_array($response)) {
            return $response;
        }

        try {
            $data['result'] = $this->serializer->unserialize((string)$response->getBody());
        } catch (\Exception $e) {
            $data = [
                'exception' => $e->getMessage(),
            ];
        }

        $data['response_object'] = [
            'headers' => $response->getHeaders(),
            'body' => $response->getBody()->getContents(),
        ];

        $data['response_status_code'] = $response->getStatusCode();
        $data['response_status_message'] = $response->getReasonPhrase();

        return $data;
    }

    /**
     * @param $request
     * @param $response
     * @param $url
     *
     * @return void
     */
    private function logRequestResponse($request, $response, $url)
    {
        $req = [
            'headers' => $this->headers,
            'body' => $request,
        ];

        $context = [
            'action' => $url,
        ];

        if ($this->configHelper->isForceLoggingEnabled()) {
            $this->log->info(json_encode(['HEADERS' => $this->headers]));
            $this->log->info(json_encode(['REQUEST' => $req]), $context);
            $this->log->info(json_encode(['RESPONSE' => $response]), $context);
        } else {
            $this->log->debug(json_encode(['HEADERS' => $this->headers]));
            $this->log->debug(json_encode(['REQUEST' => $req]), $context);
            $this->log->debug(json_encode(['RESPONSE' => $response]), $context);
        }
    }
}
