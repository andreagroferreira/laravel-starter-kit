<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

final class CustomerTokenResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        /** @var array{customer: Customer, token: string} $resource */
        $resource = (array) $this->resource;

        return [
            'customer' => CustomerResource::make($resource['customer']),
            'token' => $resource['token'],
            'token_type' => 'Bearer',
        ];
    }
}
