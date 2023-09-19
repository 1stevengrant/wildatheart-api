function objectIsEmpty(object: Record<string, unknown>): boolean {
    return Object.keys(object).length === 0;
}

export default objectIsEmpty;
