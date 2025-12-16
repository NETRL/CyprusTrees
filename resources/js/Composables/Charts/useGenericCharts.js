import { getThemeConfig } from './getThemeConfig';

const COLOR_PALETTE = [
  '#3b82f6', // Blue 500
  '#10b981', // Emerald 500
  '#f59e0b', // Amber 500
  '#8b5cf6', // Violet 500
  '#ec4899', // Pink 500
  '#06b6d4', // Cyan 500
  '#6366f1', // Indigo 500
  '#f97316'  // Orange 500
];

/**
 * Donut Chart
 */
export function useDonutChartOptions(
  data,
  title = 'Data Distribution',
  isDarkMode = false,
  extra = {}
) {
  const theme = getThemeConfig(isDarkMode)
  const centerLabel = extra.centerLabel

  return {
    color: COLOR_PALETTE,
    title: [
      {
        text: title,
        left: 'center',
        top: 10, // Tighter to top to match card padding
        textStyle: {
          ...theme.title.textStyle,
          fontSize: 15,
          fontWeight: 600,
          fontFamily: 'Inter, system-ui, sans-serif',
          
        }
      },
      // Center Metric Label
      ...(centerLabel ? [{
        text: `${centerLabel.value}`,
        left: 'center',
        top: '44%',
        textStyle: {
          fontSize: 24,
          fontWeight: 700,
          color: theme.title.textStyle.color,
          fontFamily: 'Inter, system-ui, sans-serif'
        }
      },
      {
        text: centerLabel.label,
        left: 'center',
        top: '56%',
        textStyle: {
          fontSize: 12,
          color: theme.legend.textStyle.color,
          fontWeight: 500,
          fontFamily: 'Inter, system-ui, sans-serif',
        }
      }] : [])
    ],
    tooltip: {
      trigger: 'item',
      formatter: '{b}<br/><span style="font-weight:600">{c}</span> ({d}%)',
      backgroundColor: isDarkMode ? 'rgba(31, 41, 55, 0.95)' : 'rgba(255, 255, 255, 0.95)',
      borderColor: isDarkMode ? '#374151' : '#e5e7eb',
      borderWidth: 1,
      padding: [8, 12],
      textStyle: { color: isDarkMode ? '#f3f4f6' : '#1f2937' },
      extraCssText: 'box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border-radius: 8px;'
    },
    legend: {
      orient: 'horizontal',
      bottom: 0,
      icon: 'circle',
      textStyle: {
        ...theme.legend.textStyle,
        fontSize: 12
      },
      itemGap: 15
    },
    series: [{
      type: 'pie',
      radius: ['50%', '72%'],
      center: ['50%', '52%'],
      data,
      label: { show: false },
      itemStyle: {
        borderRadius: 5,
        borderColor: isDarkMode ? '#1f2937' : '#fff',
        borderWidth: 2
      },
      // Subtle emphasis, no heavy glow
      emphasis: {
        scale: true,
        scaleSize: 5,
        itemStyle: {
          shadowBlur: 5,
          shadowOffsetX: 0,
          shadowColor: 'rgba(0, 0, 0, 0.2)'
        }
      }
    }]
  }
}

/**
 * Line Chart
 */
export function useLineChartOptions(
  data,
  title = 'Timeline Data',
  yAxisName = 'Value',
  isDarkMode = false,
  withZoom = true
) {
  const theme = getThemeConfig(isDarkMode);

  return {
    color: COLOR_PALETTE,
    title: {
      text: title,
      top: 10,
      left: 0,
      textStyle: {
        ...theme.title.textStyle,
        fontSize: 15,
        fontWeight: 600,
        fontFamily: 'Inter, system-ui, sans-serif',
        
      }
    },
    tooltip: {
      trigger: 'axis',
      axisPointer: {
        type: 'line',
        lineStyle: {
          color: theme.xAxis.axisLine.lineStyle.color,
          width: 1,
          type: 'solid'
        }
      },
      backgroundColor: isDarkMode ? 'rgba(31, 41, 55, 0.95)' : 'rgba(255, 255, 255, 0.95)',
      borderColor: isDarkMode ? '#374151' : '#e5e7eb',
      borderWidth: 1,
      padding: [8, 12],
      textStyle: { color: isDarkMode ? '#f3f4f6' : '#1f2937' },
      extraCssText: 'box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border-radius: 8px;'
    },
    legend: {
      data: data.seriesData.map(s => s.name),
      bottom: 0,
      icon: 'roundRect',
      textStyle: {
        ...theme.legend.textStyle,
        fontSize: 12
      },
      itemGap: 20
    },
    // Adjusted grid to ensure Title fits without HTML header
    grid: {
      top: 90,
      left: 10,
      right: 20,
      bottom: 60,
      containLabel: true
    },
    xAxis: {
      type: 'category',
      data: data.xAxisData,
      boundaryGap: false,
      axisLabel: {
        rotate: 0, // Try horizontal first for cleanliness
        hideOverlap: true, // ECharts auto-hides if crowded
        fontSize: 11,
        color: theme.xAxis.axisLine.lineStyle.color,
        margin: 15
      },
      axisLine: { show: false }, // Cleaner look without bottom line
      axisTick: { show: false }
    },
    yAxis: {
      type: 'value',
      name: yAxisName,
      nameTextStyle: {
        fontSize: 10,
        align: 'center',
        color: theme.yAxis.axisLabel.color
      },
      axisLabel: {
        fontSize: 11,
        color: theme.yAxis.axisLabel.color
      },
      splitLine: {
        lineStyle: {
          color: isDarkMode ? '#374151' : '#f1f5f9', // Slate-100 or Slate-700
          type: 'dashed'
        }
      }
    },
    series: data.seriesData.map(seriesItem => ({
      name: seriesItem.name,
      type: 'line',
      data: seriesItem.data,
      smooth: true,
      symbol: 'circle',
      symbolSize: 6,
      showSymbol: false, // Only show on hover for cleaner look
      lineStyle: {
        width: 2.5
      },
      areaStyle: {
        opacity: 0.05 // Very subtle fill
      },
      emphasis: {
        focus: 'series',
        itemStyle: {
          borderWidth: 2,
          borderColor: '#fff',
          shadowBlur: 5,
          shadowColor: 'rgba(0,0,0,0.2)'
        }
      }
    })),
    ...(withZoom && {
      dataZoom: [
        {
          type: 'inside',
          xAxisIndex: 0,
          filterMode: 'filter',
          zoomLock: false,
        },
        // Removed Y-Axis zoom for stability
      ]
    })
  };
}

/**
 * Clean Histogram
 * Flat gradient, no heavy shadows
 */
export function useHistogramOptions(
  data,
  title = 'Frequency Distribution',
  yAxisName = 'Count',
  seriesName = 'Frequency',
  isDarkMode = false
) {
  const theme = getThemeConfig(isDarkMode);

  return {
    title: {
      text: title,
      top: 10,
      left: 0,
      textStyle: {
        ...theme.title.textStyle,
        fontSize: 15,
        fontWeight: 600,
        fontFamily: 'Inter, system-ui, sans-serif',
        

      }
    },
    tooltip: {
      trigger: 'axis',
      axisPointer: { type: 'shadow' },
      backgroundColor: isDarkMode ? 'rgba(31, 41, 55, 0.95)' : 'rgba(255, 255, 255, 0.95)',
      borderColor: isDarkMode ? '#374151' : '#e5e7eb',
      borderWidth: 1,
      padding: [8, 12],
      textStyle: { color: isDarkMode ? '#f3f4f6' : '#1f2937' },
      extraCssText: 'box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border-radius: 8px;'
    },
    grid: {
      top: 90,
      left: 10,
      right: 20,
      bottom: 20,
      containLabel: true
    },
    xAxis: {
      type: 'category',
      data: data.xAxisData,
      axisTick: { show: false },
      axisLine: { show: false },
      axisLabel: {
        rotate: 45,
        interval: 0, // Can remove if crowded
        fontSize: 11,
        color: theme.xAxis.axisLine.lineStyle.color
      }
    },
    yAxis: {
      type: 'value',
      name: yAxisName,
      nameTextStyle: {
        fontSize: 10,
        align: 'center',
        padding: [0, 0, 0, 10],
        color: theme.yAxis.axisLabel.color
      },
      axisLabel: {
        fontSize: 11,
        color: theme.yAxis.axisLabel.color
      },
      splitLine: {
        lineStyle: {
          color: isDarkMode ? '#374151' : '#f1f5f9',
          type: 'dashed'
        }
      }
    },
    series: [
      {
        name: seriesName,
        type: 'bar',
        data: data.seriesData,
        itemStyle: {
          // Subtle vertical gradient, not too shiny
          color: {
            type: 'linear',
            x: 0, y: 0, x2: 0, y2: 1,
            colorStops: [
              { offset: 0, color: '#60a5fa' }, // Blue 400
              { offset: 1, color: '#3b82f6' }  // Blue 500
            ]
          },
          borderRadius: [4, 4, 0, 0] // Slightly sharper corners
        },
        barMaxWidth: 40,
        emphasis: {
          itemStyle: {
            color: '#3b82f6' // Flat color on hover
          }
        }
      }
    ]
  };
}

/**
 * Clean Stacked Bar
 * Aligns with the StackedBar snapshot
 */
export function useStackedBarChartOptions(
  data,
  title = 'Stacked Comparison',
  yAxisName = 'Count',
  isDarkMode = false
) {
  const theme = getThemeConfig(isDarkMode);

  return {
    color: COLOR_PALETTE,
    title: {
      text: title,
      top: 10,
      left: 0,
      textStyle: {
        ...theme.title.textStyle,
        fontSize: 15,
        fontWeight: 600,
        fontFamily: 'Inter, system-ui, sans-serif',
        
      }
    },
    tooltip: {
      trigger: 'axis',
      axisPointer: { type: 'shadow' },
      backgroundColor: isDarkMode ? 'rgba(31, 41, 55, 0.95)' : 'rgba(255, 255, 255, 0.95)',
      borderColor: isDarkMode ? '#374151' : '#e5e7eb',
      borderWidth: 1,
      padding: [8, 12],
      textStyle: { color: isDarkMode ? '#f3f4f6' : '#1f2937' },
      extraCssText: 'box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border-radius: 8px;'
    },
    legend: {
      data: data.seriesData.map(s => s.name),
      bottom: 0,
      icon: 'roundRect',
      textStyle: {
        ...theme.legend.textStyle,
        fontSize: 12
      },
      itemGap: 15
    },
    grid: {
      top: 90,
      left: 10,
      right: 20,
      bottom: 90,
      containLabel: true
    },
    xAxis: {
      type: 'category',
      data: data.xAxisData,
      axisTick: { show: false },
      axisLine: { show: false },
      axisLabel: {
        rotate: 45,
        fontSize: 11,
        color: theme.xAxis.axisLine.lineStyle.color
      }
    },
    yAxis: {
      type: 'value',
      name: yAxisName,
      nameTextStyle: {
        fontSize: 10,
        align: 'right',
        color: theme.yAxis.axisLabel.color
      },
      axisLabel: {
        fontSize: 11,
        color: theme.yAxis.axisLabel.color
      },
      splitLine: {
        lineStyle: {
          color: isDarkMode ? '#374151' : '#f1f5f9',
          type: 'dashed'
        }
      }
    },
    series: data.seriesData.map(seriesItem => ({
      name: seriesItem.name,
      type: 'bar',
      stack: 'total',
      data: seriesItem.data,
      label: { show: false },
      itemStyle: {
        borderRadius: [2, 2, 0, 0] // Very subtle rounding
      },
      barMaxWidth: 40,
      emphasis: {
        focus: 'series',
        itemStyle: {
          shadowBlur: 0, // Clean flat hover
          opacity: 0.9
        }
      }
    }))
  };
}