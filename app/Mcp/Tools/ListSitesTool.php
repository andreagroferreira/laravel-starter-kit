<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Site;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('List all sites of the current tenant with their status.')]
final class ListSitesTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $sites = Site::query()->latest()->limit(50)->get()
            ->map(fn (Site $site): array => [
                'id' => $site->id,
                'name' => $site->name,
                'slug' => $site->slug,
                'type' => $site->type->value,
                'status' => $site->status,
            ])->all();

        return Response::text((string) json_encode(['sites' => $sites], JSON_PRETTY_PRINT));
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
