import * as echarts from 'echarts/core'

// charts
import { PieChart, BarChart, LineChart } from 'echarts/charts'

// components
import {
  GridComponent,
  TooltipComponent,
  LegendComponent,
  TitleComponent,
  DatasetComponent,
  TransformComponent,
  VisualMapComponent,
  ToolboxComponent,
} from 'echarts/components'

// renderer
import { CanvasRenderer } from 'echarts/renderers'

echarts.use([
  PieChart,
  BarChart,
  LineChart,
  GridComponent,
  TooltipComponent,
  LegendComponent,
  TitleComponent,
  DatasetComponent,
  TransformComponent,
  VisualMapComponent,
  ToolboxComponent,
  CanvasRenderer,
])

// Keep background transparent. Let the card/container decide bg via Tailwind.
const lightTheme = {
  backgroundColor: 'transparent',
  textStyle: { color: '#0f172a' }, // slate-900
}

const darkTheme = {
  backgroundColor: 'transparent',
  textStyle: { color: '#e2e8f0' }, // slate-200
}

echarts.registerTheme('light', lightTheme)
echarts.registerTheme('dark', darkTheme)

export { default as VChart } from 'vue-echarts'
export { echarts }
