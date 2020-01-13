<?php

namespace App\Service;

use GuzzleHttp\ClientInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class CopperService
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $apiEmail;

    /**
     * @var TagAwareCacheInterface
     */
    private $cache;

    /**
     * @var int
     */
    private $cacheLifetime;

    const CACHE_TAG = 'COPPER';

    const ENTITY_TYPE_PERSON = 'person';
    const ENTITY_TYPE_COMPANY = 'company';
    const ENTITY_TYPE_OPPORTUNITY = 'opportunity';

    public function __construct(ClientInterface $client, string $apiKey, string $apiEmail, TagAwareCacheInterface $cache, int $cacheLifetime)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
        $this->apiEmail = $apiEmail;
        $this->cache = $cache;
        $this->cacheLifetime = $cacheLifetime;
    }

    /**
     * @param string $method
     * @param string $action
     * @param array $params
     * @return array
     */
    protected function call($method, $action, array $params = [])
    {
        $uri = 'https://api.prosperworks.com/developer_api/v1/' . $action;
        $options = [
            'headers' => [
                'X-PW-AccessToken' => $this->apiKey,
                'X-PW-Application' => 'developer_api',
                'X-PW-UserEmail' => $this->apiEmail,
                'Content-Type' => 'application/json',
            ],
        ];
        if (!empty($params)) {
            $options['body'] = json_encode($params);
        }
        $response = $this->client->request($method, $uri, $options);
        return json_decode($response->getBody(), true);
    }

    /**
     * @param string $method
     * @param string $action
     * @param array $params
     * @return array
     */
    protected function pull($method, $action, array $params = [])
    {
        $id = self::CACHE_TAG . '_' . strtoupper(md5($action . '|' . json_encode($params)));
        $result = $this->cache->get($id, function ($item) use ($method, $action, $params) {
            /* @var $item ItemInterface */
            $item->expiresAfter($this->cacheLifetime);
            $item->tag(self::CACHE_TAG);
            return $this->call($method, $action, $params);
        });
        return $result;
    }

    /**
     * @param string $method
     * @param string $action
     * @param array $params
     * @return array
     */
    protected function push($method, $action, array $params = [])
    {
        $result = $this->call($method, $action, $params);
        $this->cache->invalidateTags([self::CACHE_TAG]);
        return $result;
    }

    /**
     * @param string $type
     * @return string
     */
    protected function getAction($type)
    {
        switch ($type) {
            case self::ENTITY_TYPE_COMPANY:
                $result = 'companies';
                break;
            case self::ENTITY_TYPE_OPPORTUNITY:
                $result = 'opportunities';
                break;
            case self::ENTITY_TYPE_PERSON:
                $result = 'people';
                break;
            default:
                throw new \Exception(sprintf('Unknown entity type "%s"', $type));
        }
        return $result;
    }

    /**
     * @param string $type
     * @param array $params
     * @return array
     */
    public function search($type, array $params = [])
    {
        $action = $this->getAction($type) . '/search';
        $result = $this->pull('POST', $action, $params);
        return $result;
    }

    /**
     * @param string $type
     * @param array $data
     * @return array
     */
    public function create($type, array $data)
    {
        $action = $this->getAction($type);
        $result = $this->push('POST', $action, $data);
        return $result;
    }

    /**
     * @param int $id
     * @param string $type
     * @param array $data
     * @return array
     */
    public function update($id, $type, array $data)
    {
        $action = $this->getAction($type) . '/' . $id;
        $result = $this->push('PUT', $action, $data);
        return $result;
    }

    /**
     * @param int $id
     * @param string $type
     * @return array
     */
    public function delete($id, $type)
    {
        $action = $this->getAction($type) . '/' . $id;
        $result = $this->push('DELETE', $action);
        return $result;
    }

    /**
     * @param int $id
     * @param string $type
     * @param mixed $relatedType
     * @return array
     */
    public function getRelatedItems($id, $type, $relatedType = null)
    {
        $action = $this->getAction($type) . '/' . $id . '/related/';
        if ($relatedType !== null) {
            $action .= $this->getAction($relatedType);
        }
        $result = $this->pull('GET', $action);
        return $result;
    }

    /**
     * @param int $id
     * @param string $type
     * @param int $relatedId
     * @param string $relatedType
     * @return array
     */
    public function addRelatedItem($id, $type, $relatedId, $relatedType)
    {
        $action = $this->getAction($type) . '/' . $id . '/related';
        $result = $this->push('POST', $action, [
            'resource' => [
                'id' => $relatedId,
                'type' => $relatedType,
            ]
        ]);
        return $result;
    }

    /**
     * @param int $id
     * @param string $type
     * @param int $relatedId
     * @param string $relatedType
     * @return array
     */
    public function deleteRelatedItem($id, $type, $relatedId, $relatedType)
    {
        $action = $this->getAction($type) . '/' . $id . '/related';
        $result = $this->push('DELETE', $action, [
            'resource' => [
                'id' => $relatedId,
                'type' => $relatedType,
            ]
        ]);
        return $result;
    }
}