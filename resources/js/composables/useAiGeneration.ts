export interface AiGenerationResult {
    id: string;
    agent: string;
    status: 'queued' | 'processing' | 'completed' | 'failed';
    output: Record<string, unknown> | null;
    error: string | null;
}

function xsrfToken(): string {
    return decodeURIComponent(
        document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? '',
    );
}

/**
 * Starts an AI generation (202 + generation_id) and polls the fallback
 * endpoint until it settles. Reverb listeners can short-circuit the polling
 * later; the polling path must always work on its own.
 */
export function useAiGeneration() {
    async function start(
        url: string,
        payload: Record<string, unknown>,
        {
            timeoutMs = 120_000,
            intervalMs = 1_500,
        }: { timeoutMs?: number; intervalMs?: number } = {},
    ): Promise<AiGenerationResult> {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-XSRF-TOKEN': xsrfToken(),
            },
            body: JSON.stringify(payload),
        });

        if (response.status === 402) {
            throw new Error('Sem créditos AI disponíveis neste período.');
        }

        if (!response.ok) {
            throw new Error('Não foi possível iniciar a geração.');
        }

        const { generation_id: generationId } = (await response.json()) as {
            generation_id: string;
        };

        const deadline = Date.now() + timeoutMs;

        while (Date.now() < deadline) {
            await new Promise((resolve) => setTimeout(resolve, intervalMs));

            const poll = await fetch(`/ai/generations/${generationId}`, {
                headers: { Accept: 'application/json' },
            });

            if (!poll.ok) {
                continue;
            }

            const generation = (await poll.json()) as AiGenerationResult;

            if (generation.status === 'completed') {
                return generation;
            }

            if (generation.status === 'failed') {
                throw new Error(generation.error ?? 'A geração falhou.');
            }
        }

        throw new Error('A geração excedeu o tempo limite.');
    }

    return { start };
}
