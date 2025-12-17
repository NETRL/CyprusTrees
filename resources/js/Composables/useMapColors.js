export function useMapColors() {

    // All colors have in HSL scale S=75% and L=50%
const STATUS_COLORS = [
  'existing',        '#20df80', // H=150 (Emerald)
  'newly_planted',   '#20df20', // H=120 (Green)
  'dead',            '#df8020', // H=30  (Orange)
  'stump',           '#808080', // *Note: HSL(0,0,50) for neutral; otherwise H=210 #2080df
  'removed',         '#df2020', // H=0   (Red)
  'missing',         '#dfdf20', // H=60  (Yellow)
  'pending_removal', '#df2050', // H=345 (Crimson)
  'proposed',        '#8020df', // H=270 (Purple)
  'vacant_pit',      '#20dfdf', // H=180 (Cyan)
  'unknown',         '#df20df', // H=300 (Magenta)
  '#2020df'                     // Default (Blue)
];

   const WATER_USE_COLORS = [
  'high',     '#20df20', // Low Water Need (Red = High Need)
  'moderate', '#dfdf20', // Medium Water Need (Yellow = Warning)
  'low',      '#df2020', // High Water Need (Green = Efficient)
  '#000000'              // Default Fallback
];

const SHADE_COLORS = [
  'S', '#dfdf20', // Small Canopy (Sunlight/Yellow)
  'M', '#20df80', // Medium Canopy (Growth/Emerald)
  'L', '#2080df', // Large Canopy (Deep Shade/Blue)
  '#000000'       // Default Fallback
];

const ORIGIN_COLORS = [
  'native',  '#20df50', // Environment
  'endemic', '#8020df', // Rare/Purple
  'exotic',  '#df8020', // Different/Orange
  '#000000'             // Default Fallback
];

    // --- Sequential Color Palette (Enhanced for visibility) ---
const POLLEN_RISK_COLORS = [
    '#808080', // Missing Data (Neutral Gray)
    // Safe (Green to Lime)
    1,  '#20df20', // H=120
    2,  '#50df20', // H=107
    3,  '#80df20', // H=93
    // Moderate (Yellow to Gold)
    4,  '#b0df20', // H=80
    5,  '#dfdf20', // H=60
    6,  '#dfb020', // H=47
    // High (Orange to Red)
    7,  '#df8020', // H=33
    8,  '#df5020', // H=20
    9,  '#df3520', // H=10
    10, '#df2020'  // H=0
];

    return {
        STATUS_COLORS,
        WATER_USE_COLORS,
        SHADE_COLORS,
        ORIGIN_COLORS,
        POLLEN_RISK_COLORS,
    };
}