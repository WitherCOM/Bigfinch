<?php

namespace App\Models\Gocardless;

use Carbon\Carbon;

class RequisitionDto
{
    public function __construct(
        public readonly string $id,
        public readonly string $gocardlessTokenId,
        public readonly ?string $status,
        public readonly ?string $institutionId,
        public readonly ?string $created,
        public readonly int $accountsCount,
        public readonly bool $active,
        public readonly ?string $integrationName,
        public readonly ?string $userName,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'gocardless_token_id' => $this->gocardlessTokenId,
            'status' => $this->status,
            'institution_id' => $this->institutionId,
            'created' => Carbon::parse($this->created)->setTimezone(config('app.timezone')),
            'accounts_count' => $this->accountsCount,
            'active' => $this->active,
            'integration_name' => $this->integrationName,
            'user_name' => $this->userName,
        ];
    }
}
