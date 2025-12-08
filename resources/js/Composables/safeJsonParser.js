/**
 * Safely parses a potential JSON string into an array or object.
 * Handles null/undefined inputs and errors during parsing.
 * * @param {string | any} data - The data property that might be a JSON string.
 * @returns {Array<any> | Object | null} The parsed data or an empty array/null on failure.
 */
export const safeJsonParse = (data) => {
    // 1. Handle missing/null input
    if (data === null || data === undefined) {
        return [];
    }

    let result = data;

    // 2. Attempt to parse if it's a string
    if (typeof result === 'string') {
        try {
            result = JSON.parse(result);
        } catch (e) {
            console.error("Failed to parse JSON string:", e);
            return []; // Return empty array on parse error
        }
    }

    // 3. Final safety check: ensure result is an array (or return a useful default)
    return Array.isArray(result) ? result : [];
};