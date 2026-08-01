import type { InjectionKey } from 'vue';

export interface FormField {
    name: string;
    type: string;
    required?: boolean;
}

export interface FormDefinition {
    id: string;
    name: string;
    fields: FormField[];
}

/**
 * The renderer provides these; the editor does not, so form blocks render
 * inert on the canvas instead of posting leads while you design.
 */
export const RESOLVE_FORM: InjectionKey<(id: string) => FormDefinition | null> =
    Symbol('resolveForm');

export const SUBMIT_LEAD: InjectionKey<
    (formId: string, values: Record<string, string>) => Promise<void>
> = Symbol('submitLead');
