import { computed, type ComputedRef, type Ref } from 'vue';

/**
 * RFC7807 problem+json error shape.
 */
export interface Rfc7807Error {
    field: string;
    message: string;
}

export interface Rfc7807Response {
    errors?: Rfc7807Error[];
}

/**
 * Map a RFC7807 problem+json error response into Inertia-style errors
 * (a flat `Record<string, string>` keyed by field name).
 */
export function useFormErrors(
    response: Ref<Rfc7807Response | null | undefined>,
): ComputedRef<Record<string, string>> {
    return computed(() => {
        const errors: Record<string, string> = {};
        for (const err of response.value?.errors ?? []) {
            errors[err.field] = err.message;
        }
        return errors;
    });
}
