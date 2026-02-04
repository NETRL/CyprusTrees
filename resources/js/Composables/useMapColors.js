export function useMapColors() {
  const DEFAULT_COLOR = '#4A5568'  // Darker default for better visibility
  
  // ENHANCED COLORS FOR LIGHT MAP
  // Increased saturation and adjusted lightness for better visibility on white/light backgrounds
  // All colors now have HSL scale with S=85-90% and L=45-48% for punch
  
  const STATUS_COLORS = [
    'existing',        '#0DC278', // Emerald - more saturated
    'newly_planted',   '#10B810', // Green - deeper
    'dead',            '#E87020', // Orange - richer
    'stump',           '#6B7280', // Gray - darker for visibility
    'removed',         '#DC2626', // Red - more vibrant
    'missing',         '#D4AF37', // Yellow-Gold - more visible than pure yellow
    'pending_removal', '#E11D48', // Crimson - stronger
    'proposed',        '#7C3AED', // Purple - deeper
    'vacant_pit',      '#06B6D4', // Cyan - more saturated
    'unknown',         '#D946EF', // Magenta - brighter
    DEFAULT_COLOR
  ];

  const WATER_USE_COLORS = [
    'high',     '#DC2626', // Changed: Red for HIGH need (intuitive)
    'moderate', '#D97706', // Orange for moderate
    'low',      '#059669', // Green for LOW need (efficient/good)
    DEFAULT_COLOR
  ];

  const SHADE_COLORS = [
    'S', '#EAB308', // Small Canopy - Gold/Yellow
    'M', '#059669', // Medium - Emerald
    'L', '#2563EB', // Large - Deep Blue
    DEFAULT_COLOR
  ];

  const ORIGIN_COLORS = [
    'native',  '#059669', // Green - belongs here
    'endemic', '#7C3AED', // Purple - special/rare
    'exotic',  '#EA580C', // Orange - foreign
    DEFAULT_COLOR
  ];

  // POLLEN RISK - Enhanced gradient
  const POLLEN_RISK_COLORS = [
    DEFAULT_COLOR,
    1,  '#10B981', // Safe - Emerald
    2,  '#34D399',
    3,  '#84CC16', // Lime
    4,  '#A3E635',
    5,  '#EAB308', // Yellow
    6,  '#FBBF24',
    7,  '#F59E0B', // Amber
    8,  '#F97316', // Orange
    9,  '#EF4444', // Red
    10, '#DC2626'  // Dark Red
  ];

  return {
    STATUS_COLORS,
    WATER_USE_COLORS,
    SHADE_COLORS,
    ORIGIN_COLORS,
    POLLEN_RISK_COLORS,
  };
}

// USAGE NOTE:
// These colors are optimized for LIGHT backgrounds (white/light gray maps)
// They have:
// - Higher saturation (more vibrant)
// - Slightly darker lightness (better contrast)
// - Adjusted hues for maximum distinction