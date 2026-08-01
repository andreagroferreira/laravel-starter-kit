import { onUnmounted } from 'vue';

/**
 * Subscribes to a private Reverb channel with automatic cleanup.
 * Returns false when Echo is not configured, so callers can fall back to
 * polling instead of silently waiting for events that never arrive.
 */
export function useEchoChannel(
    channel: string | null | undefined,
    listeners: Record<string, (payload: never) => void>,
): boolean {
    if (!channel || !window.Echo) {
        return false;
    }

    const subscription = window.Echo.private(channel);

    for (const [event, handler] of Object.entries(listeners)) {
        subscription.listen(event, handler);
    }

    onUnmounted(() => window.Echo?.leave(channel));

    return true;
}
