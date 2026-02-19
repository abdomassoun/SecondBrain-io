<?php

namespace App\Application\Users\Commands;

use App\Domain\Languages\Entities\Language;

final class UpdateUserCommand
{
    public function __construct(
        public string $id,
        public ?string $email = null,
        public ?string $companyUuid = null,
        public ?string $brandUuid = null,
        public ?string $branchUuid = null,
        public ?string $directManagerUuid = null,
        public ?int $statusId = null,
        public ?string $defaultLanguage = null
    ) {}
}
