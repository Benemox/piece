<?php

namespace App\Shared\Infrastructure\Redis;

use App\Shared\Infrastructure\Compressor\StringCompressionServiceInterface;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use SymfonyBundles\RedisBundle\Redis\ClientInterface;

class RedisService implements CacheServiceInterface
{
    public const TTL_MINUTE = 60;
    public const TTL_HOUR = self::TTL_MINUTE * 60;
    public const TTL_DAY = self::TTL_HOUR * 24;
    public const TTL_MONTH = self::TTL_DAY * 30;

    public const FORMAT = 'json';

    private Serializer $serializer;

    public function __construct(
        private readonly ClientInterface $client,
        private readonly LoggerInterface $logger,
        private StringCompressionServiceInterface $stringCompressionService,
        #[Autowire(env: 'APP_NAME')]
        private readonly string $namespace = 'app:cache:'
    ) {
        $this->serializer = SerializerBuilder::create()->build();
    }

    public function getRawData(string $key): ?string
    {
        $key = md5($key);
        $this->ensureConnected();
        try {
            return $this->client->get($this->getNamespacedKey($key));
        } catch (\Exception $e) {
            $this->logger->error('Failed to get raw data from Redis', ['key' => $key, 'error' => $e->getMessage()]);
        } finally {
            $this->closeConnection();
        }

        return null;
    }

    public function keyExist(string $key): bool
    {
        $key = md5($key);

        $this->ensureConnected();
        $exist = false;
        try {
            $exist = !empty($this->client->get($this->getNamespacedKey($key)));
        } catch (\Exception $e) {
            $this->logger->error('Failed to get key from Redis', ['key' => $key, 'error' => $e->getMessage()]);
        } finally {
            $this->closeConnection();
        }

        return $exist;
    }

    public function invalidate(string $key): void
    {
        $key = md5($key);
        $this->ensureConnected();
        try {
            $this->client->del([$this->getNamespacedKey($key)]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to invalidate key from Redis', ['key' => $key, 'error' => $e->getMessage()]);
        } finally {
            $this->closeConnection();
        }
    }

    public function store(string $key, mixed $value, int $ttl, string $model): void
    {
        $key = md5($key);
        $this->ensureConnected();
        try {
            $data = $this->serializeData($value, $model);
            $compressedData = base64_encode($this->stringCompressionService->compress($data));
            $this->set($key, $compressedData, $ttl);
        } catch (\Exception $e) {
            $this->logError($key, $e);
        } finally {
            $this->closeConnection();
        }
    }

    public function getFromStore(string $key, string $model): mixed
    {
        $this->ensureConnected();
        try {
            if (!$this->keyExist($key)) {
                return null;
            }

            $key = md5($key);

            $data = $this->client->get($this->getNamespacedKey($key));
            if (!$data) {
                return null;
            }
            $decompressedData = $this->stringCompressionService->decompress(base64_decode($data));

            // Validate JSON before deserialization
            if ('array' === $model || 'string' === $model) {
                if (!$this->isValidJson($decompressedData)) {
                    $this->logger->error('Invalid JSON format', ['key' => $key, 'data' => $decompressedData]);

                    return null;
                }
            }

            return $this->deserializeData($decompressedData, $model);
        } catch (\Exception $e) {
            $this->logError('Failed to retrieve data from Redis: '.$key, $e);
        } finally {
            $this->closeConnection();
        }

        return null;
    }

    private function set(string $key, string $value, int $ttl = 0): void
    {
        $key = $this->getNamespacedKey($key);
        $this->ensureConnected();
        if ($ttl > 0) {
            $this->client->setex($key, $ttl, $value);
        } else {
            $this->client->set($key, $value);
        }
        $this->closeConnection();
    }

    public function getDefaultTtl(): int
    {
        return self::TTL_HOUR;
    }

    private function getNamespacedKey(string $key): string
    {
        return $this->namespace.':'.$key;
    }

    public function openConnection(): void
    {
        try {
            $this->client->connect();
        } catch (\Exception $e) {
            $this->logger->error('Failed to connect to Redis', ['error' => $e->getMessage()]);
        }
    }

    public function closeConnection(): void
    {
        try {
            // @phpstan-ignore-next-line
            if ($this->client->isConnected()) {
                $this->client->disconnect();
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to disconnect from Redis', ['error' => $e->getMessage()]);
        }
    }

    private function ensureConnected(): void
    {
        // @phpstan-ignore-next-line
        if (!$this->client->isConnected()) {
            $this->openConnection();
        }
    }

    /**
     * @throws \JsonException
     */
    private function serializeData(mixed $value, string $model): string
    {
        if ('string' === $model) {
            return (string) $value;
        }

        if ('array' === $model) {
            return json_encode($value, JSON_THROW_ON_ERROR);
        }

        return $this->serializer->serialize($value, self::FORMAT);
    }

    /**
     * @throws \JsonException
     */
    private function deserializeData(string $data, string $model): mixed
    {
        if ('string' === $model) {
            return $data;
        }

        if ('array' === $model) {
            return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        }

        return $this->serializer->deserialize($data, $model, self::FORMAT);
    }

    private function logError(?string $key, \Exception $e): void
    {
        $this->logger->error('Failed to store data in Redis', [
            'key' => $key,
            'error' => $e->getMessage(),
        ]);
    }

    private function isValidJson(string $data): bool
    {
        json_decode($data, true);

        return JSON_ERROR_NONE === json_last_error();
    }

    public function getMonthTtl(): int
    {
        return self::TTL_MONTH;
    }
}
