
export const safeJsonParse = (data, defaultValue = null) => {
    if (data === null || data === undefined) {
        return defaultValue;
    }

    // If it's already an object (or array), just return it
    if (typeof data === 'object') {
        return data;
    }

    // Attempt to parse if it's a string
    if (typeof data === 'string') {
        try {
            const parsed = JSON.parse(data);
            return parsed ?? defaultValue;
        } catch (e) {
            console.error('Failed to parse JSON string:', e);
            return defaultValue;
        }
    }

    // Fallback
    return defaultValue;
};
