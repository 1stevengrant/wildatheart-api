export const objectIsEmpty = (object: Record<string, unknown>): boolean =>
    Object.keys(object).length === 0;
