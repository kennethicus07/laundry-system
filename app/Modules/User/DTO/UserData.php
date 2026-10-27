<?php
namespace App\Modules\User\DTO;

use App\Enums\UserStatus;

class UserData
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $password,
        public array $roles = [],
        public UserStatus $status,
        public bool $autoVerify = false,
        public array $overrides = [],
    ) {}

    public static function from(array $data): self
    {
        return new self(
            name: (string) ($data['name'] ?? ''),
            email: (string) ($data['email'] ?? ''),

            password: $data['password'] ?? null,

            roles: array_map('intval', $data['roles'] ?? []),

            status: isset($data['status'])
                ? UserStatus::from((int) $data['status'])
                : UserStatus::ACTIVE,

            autoVerify: (bool) ($data['auto_verify'] ?? false),

            overrides: self::normalizeOverrides($data['overrides'] ?? []),
        );
    }

    /**
     * Ensure overrides always have clean structure:
     * [
     *   ['permission_id' => 1, 'effect' => 1]
     * ]
     */
    private static function normalizeOverrides(array $overrides): array
    {
        return collect($overrides)
            ->map(function ($item) {
                return [
                    'permission_id' => (int) ($item['permission_id'] ?? 0),
                    'effect' => (int) ($item['effect'] ?? 0),
                ];
            })
            ->filter(fn ($i) => $i['permission_id'] > 0)
            ->values()
            ->toArray();
    }
}