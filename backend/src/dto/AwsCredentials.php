<?php

namespace App\Dto;

use App\Settings\Settings;
use App\Traits\CloneWithProps;

final readonly class AwsCredentials implements \JsonSerializable
{
    use CloneWithProps;

    public function __construct(
        public string $key,
        public string $secret,
        public ?string $token = null,  // null = permanent IAM keys
        public string $region = Settings::DEFAULT_REGION,
        public ?\DateTimeImmutable $expires = null,
        public ?\DateTimeImmutable $loggedInAt = null,
    ) {
        if (empty($key) || empty($secret)) {
            throw new \InvalidArgumentException(
                'AWS key (access Key Id) and secret (secret key) are required'
            );
        }
    }

    public function cloneWithLoginAt(\DateTimeImmutable $dt): self
    {
        return self::cloneWithProps($this, ['loggedInAt' => $dt]);
    }

    /**
     * Create from the array we currently store in JWT payload.
     */
    public static function fromArray(array $data): self
    {
        $expires = null;
        if (!empty($data['expires'])) {
            $expires = is_int($data['expires'])
                ? (new \DateTimeImmutable())->setTimestamp($data['expires'])
                : new \DateTimeImmutable($data['expires']);
        }

        return new self(
            key: $data['key'] ?? throw new \InvalidArgumentException('key (Access Key Id) is missing'),
            secret: $data['secret'] ?? throw new \InvalidArgumentException('secret (Secret Key) is missing'),
            token: $data['token'] ?? null,
            region: $data['region'] ?? Settings::getDefaultRegion(),
            expires: $expires,
        );
    }

    public static function fromArgsList(
        string $key,
        string $secret,
        ?string $token = null,  // null = permanent IAM keys
        string $region = Settings::DEFAULT_REGION,
        ?\DateTimeImmutable $expires = null,
        ?\DateTimeImmutable $loggedInAt = null,
    ): self {
        return new self(
            $key,
            $secret,
            $token,
            $region,
            $expires,
            $loggedInAt
        );
    }

    /**
     * Turn the credentials into array (for JWT encryption or logging).
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'secret' => $this->secret,
            'token' => $this->token,
            'region' => $this->region,
            'expires' => $this->expires?->getTimestamp(),
        ];
    }

    public function toArrayWithLogin(): array
    {
        return [
            'key' => $this->key,
            'secret' => $this->secret,
            'token' => $this->token,
            'region' => $this->region,
            'expires' => $this->expires?->getTimestamp(),
            'login_at' => $this->loggedInAt,
        ];
    }

    public function isExpired(): bool
    {
        return null !== $this->expires && $this->expires < new \DateTimeImmutable();
    }

    public function hasSessionToken(): bool
    {
        return null !== $this->token;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
