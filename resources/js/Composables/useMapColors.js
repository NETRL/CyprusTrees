export function useMapColors() {
    // --- Enhanced Categorical Color Palettes (Optimized for Dark Maps) ---
    // Slightly brighter versions for better visibility on dark backgrounds
    
    const STATUS_COLORS = [
        'existing', '#10b981',      // Brighter emerald green
        'newly_planted', '#6ee7b7', // Brighter mint green
        'dead', '#C4A484',          // Lighter brown (more visible)
        'stump', '#9ca3af',         // Lighter grey
        'removed', '#d4d4d4',       // Light grey
        'missing', '#fbbf24',       // Brighter yellow
        'pending_removal', '#ef4444', // Brighter red
        'proposed', '#60a5fa',      // Brighter blue
        'vacant_pit', '#14b8a6',    // Brighter teal
        'unknown', '#ffffff',       // White
        '#000000' // Default Fallback
    ];
    
    const WATER_USE_COLORS = [
        'high', '#fef3c7',      // Light Yellow/Tan (Low Water Need)
        'moderate', '#60a5fa',  // Brighter blue (Medium Water Need)
        'low', '#0284c7',       // Brighter dark blue (High Water Need)
        '#000000' // Default Fallback
    ];
    
    const SHADE_COLORS = [
        'S', '#86efac', // Light Green (Small Canopy)
        'M', '#14b8a6', // Teal (Medium Canopy)
        'L', '#059669', // Brighter forest green (Large Canopy)
        '#000000' // Default Fallback
    ];
    
    const ORIGIN_COLORS = [
        'native', '#16a34a',        // Brighter forest green
        'endemic', '#06b6d4',       // Cyan (stands out more)
        'exotic', '#3b82f6',        // Brighter blue
        '#000000' // Default Fallback
    ];
    
    // --- Sequential Color Palette (Enhanced for visibility) ---
    const POLLEN_RISK_COLORS = [
        // Default/Fallback for missing data
        '#000000', 
        // --- Safe/Very Low Risk (1-3) - Brighter greens ---
        1, '#10b981', 
        2, '#34d399', 
        3, '#6ee7b7', 
        // --- Moderate Risk (4-6) - More visible yellows/oranges ---
        4, '#fde047', 
        5, '#fbbf24', 
        6, '#fb923c', 
        // --- High/Extreme Risk (7-10) - Brighter reds ---
        7, '#ef4444', 
        8, '#dc2626', 
        9, '#b91c1c', 
        10, '#7f1d1d',
    ];
    
    return {
        STATUS_COLORS,
        WATER_USE_COLORS,
        SHADE_COLORS,
        ORIGIN_COLORS,
        POLLEN_RISK_COLORS,
    };
}