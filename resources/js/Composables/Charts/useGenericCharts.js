
import { getThemeConfig } from './getThemeConfig';

/**
 * @param {Array<Object>} data - Array of objects: [{name: string, value: number}, ...]
 * @param {string} title - The title of the chart.
 */
export function useDonutChartOptions(data, title = 'Data Distribution', isDarkMode = false) {
  const theme = getThemeConfig(isDarkMode);
  return {
    title: {
      text: title,
      left: 'center',
      textStyle: theme.title.textStyle
    },
    tooltip: {
      trigger: 'item',
      formatter: '{b} <br/>{c} ({d}%)', // Shows name, value, and percentage
      ...theme.tooltip,
    },
    legend: {
      orient: 'horizontal',
      left: 'center',
      bottom: '5%',
      textStyle: theme.legend.textStyle,
    },
    series: [
      {
        name: title,
        type: 'pie',
        radius: ['40%', '70%'], // Creates the donut shape
        center: ['50%', '45%'], // Adjust center to accommodate legend on the left
        data: data,
        emphasis: {
          itemStyle: {
            shadowBlur: 10,
            shadowOffsetX: 0,
            shadowColor: 'rgba(0, 0, 0, 0.5)'
          }
        },
        label: {
          show: true,
          formatter: '{d}%', // Show percentage on the slices
          position: 'inside'
        }
      }
    ]
  };
}


/**
 * @param {Object} data - {xAxisData: string[], seriesData: Array<{name: string, data: number[]}>}
 * @param {string} title - The title of the chart.
 * @param {string} yAxisName - The label for the Y-axis.
 */
export function useLineChartOptions(data, title = 'Timeline Data', yAxisName = 'Value', isDarkMode = false, withZoom = true) {
  const theme = getThemeConfig(isDarkMode);

  const options = {
    color: theme.color,
    title: {
      text: title,
      left: 'center',
      textStyle: theme.title.textStyle
    },
    tooltip: {
      trigger: 'axis',
      axisPointer: { type: 'cross' },
      ...theme.tooltip,
    },
    legend: {
      data: data.seriesData.map(s => s.name),
      bottom: 5,
      textStyle: theme.legend.textStyle,
    },
    grid: { top: '25%', left: '3%', right: '4%', bottom: '15%', containLabel: true },
    xAxis: {
      type: 'category',
      data: data.xAxisData,
      boundaryGap: false, // Makes the line start at the edge
      axisLabel: {
        rotate: 45,
        interval: 0
      },
      axisLine: theme.xAxis.axisLine
    },
    yAxis: {
      type: 'value',
      name: yAxisName,
      axisLabel: theme.yAxis.axisLabel,
      axisLine: theme.yAxis.axisLine,
      splitLine: theme.yAxis.splitLine
    },
    series: data.seriesData.map(seriesItem => ({
      name: seriesItem.name,
      type: 'line',
      data: seriesItem.data,
      smooth: true, // Smooth lines for a cleaner look
      symbol: 'circle',
      symbolSize: 8
    })),
    ...(withZoom && {
      dataZoom: [
        {
          type: 'inside',
          xAxisIndex: 0,
          filterMode: 'filter',

          moveOn: 'mousemove|touch', // Allows pan/move with single touch
          zoomOn: 'mouseWheel|pinch', // Allows zoom with two-finger pinch
          zoomLock: false,
        },
        {
          type: 'inside',
          yAxisIndex: 0,
          filterMode: 'none',
          // Ensure vertical pan is enabled if needed
          moveOn: 'mousemove|touch',
          // Keep zoomOn disabled here so pinch only affects X-axis if needed
          zoomOn: false,
        }
      ]
    })
  };

  return options;
}


/**
 * @param {Object} data - {xAxisData: string[], seriesData: number[]}
 * @param {string} title - The title of the chart.
 * @param {string} yAxisName - The label for the Y-axis.
 * @param {string} seriesName - The name of the data series.
 */
export function useHistogramOptions(data, title = 'Frequency Distribution', yAxisName = 'Count', seriesName = 'Frequency', isDarkMode = false) {
  const theme = getThemeConfig(isDarkMode);
  return {
    title: {
      text: title,
      left: 'center',
      textStyle: theme.title.textStyle
    },
    tooltip: {
      trigger: 'axis',
      axisPointer: { type: 'shadow' },
      ...theme.tooltip,
    },
    grid: { top: '25%', left: '3%', right: '4%', bottom: '15%', containLabel: true },
    xAxis: {
      type: 'category',
      data: data.xAxisData,
      axisTick: { alignWithLabel: true },
      axisLabel: { rotate: 30 },
      axisLabel: {
        rotate: 45,
        interval: 0
      }
    },
    yAxis: {
      type: 'value',
      name: yAxisName
    },
    series: [
      {
        name: seriesName,
        type: 'bar',
        data: data.seriesData,
        itemStyle: {
          color: '#3498db', // Default blue color
          borderRadius: [5, 5, 0, 0]
        }
      }
    ]
  };
}


/**
 * @param {Object} data - {xAxisData: string[], seriesData: Array<{name: string, data: number[]}>}
 * @param {string} title - The title of the chart.
 * @param {string} yAxisName - The label for the Y-axis.
 */
export function useStackedBarChartOptions(data, title = 'Stacked Comparison', yAxisName = 'Count', isDarkMode = false) {
  const theme = getThemeConfig(isDarkMode);
  return {
    title: {
      text: title,
      left: 'center',
      textStyle: theme.title.textStyle
    },
    tooltip: {
      trigger: 'axis',
      axisPointer: { type: 'shadow' },
      ...theme.tooltip,
    },
    legend: {
      data: data.seriesData.map(s => s.name),
      bottom: 5,
      textStyle: theme.legend.textStyle,
    },
    grid: { top: '25%', left: '3%', right: '4%', bottom: '15%', containLabel: true },
    xAxis: {
      type: 'category',
      data: data.xAxisData,
      axisLabel: {
        rotate: 45,
        interval: 0
      }
    },
    yAxis: {
      type: 'value',
      name: yAxisName
    },
    series: data.seriesData.map(seriesItem => ({
      name: seriesItem.name,
      type: 'bar',
      stack: 'total', // Crucial for stacking
      emphasis: { focus: 'series' },
      data: seriesItem.data,
      label: {
        show: false,
      }
    }))
  };
}