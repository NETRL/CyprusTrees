/**
 * Generates ECharts theme configurations based on the active mode.
 * @param {boolean} isDarkMode - True if dark mode is active.
 * @returns {Object} ECharts theme object
 */
export function getThemeConfig(isDarkMode) {
  const textColor = isDarkMode ? '#9CA3AF' : '#6B7280'; // dark:text-gray-400 vs text-gray-500
  const backgroundColor = isDarkMode ? '#111827' : '#FFFFFF'; // dark:bg-gray-900 vs bg-white
  const lineColor = isDarkMode ? '#374151' : '#E5E7EB'; // dark:border-gray-700 vs border-gray-200

  return {
    title: {
      textStyle: {
        color: textColor,
        lineHeight: 22,
      }
    },
    legend: {
      textStyle: {
        color: textColor
      }
    },
    tooltip: {
      backgroundColor: isDarkMode ? 'rgba(31, 41, 55, 0.9)' : 'rgba(255, 255, 255, 0.9)', // dark:bg-gray-800
      borderColor: lineColor,
      textStyle: {
        color: textColor
      }
    },
    grid: {
      borderColor: lineColor,
      axisLine: {
        lineStyle: {
          color: lineColor
        }
      }
    },
    xAxis: {
      axisLabel: {
        color: textColor
      },
      axisLine: {
        lineStyle: {
          color: lineColor
        }
      }
    },
    yAxis: {
      axisLabel: {
        color: textColor
      },
      axisLine: {
        lineStyle: {
          color: lineColor
        }
      },
      splitLine: { // Grid lines
        lineStyle: {
          color: lineColor + '30', // Use a slightly transparent line color
          type: 'dashed'
        }
      }
    },
    color: [
      '#3498db', // Blue
      '#1abc9c', // Teal
      '#f1c40f', // Yellow
      '#e67e22', // Orange
      '#9b59b6', // Purple
      '#e74c3c', // Red
      '#34495e'  // Dark Blue
    ]
  };
}