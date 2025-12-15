<script setup>
import { computed } from 'vue'
import BaseChart from '@/Charts/Components/BaseChart.vue'
import { useResponsiveText } from '@/Composables/Charts/useResponsiveText'

const props = defineProps({
  title: { type: String, default: '' },
  data: { type: Array, default: () => [] },
  height: { type: [Number, String], default: 280 },
})

const { isMobile, textSize } = useResponsiveText()

const option = computed(() => ({
  title: props.title ? { 
    text: props.title, 
    left: 'center',
    textStyle: { fontSize: textSize.value.title }
  } : undefined,
  tooltip: { 
    trigger: 'item',
    textStyle: { fontSize: textSize.value.tooltip }
  },
  legend: { 
    bottom: 0, 
    left: 'center',
    textStyle: { fontSize: textSize.value.legend },
    type: isMobile.value ? 'scroll' : 'plain',
    orient: isMobile.value ? 'horizontal' : 'horizontal'
  },
  series: [
    {
      type: 'pie',
      radius: isMobile.value ? ['40%', '65%'] : ['45%', '70%'],
      avoidLabelOverlap: true,
      label: { show: false },
      emphasis: { 
        label: { 
          show: true, 
          fontSize: textSize.value.legend 
        } 
      },
      data: props.data,
    },
  ],
}))
</script>
<template>
  <BaseChart :option="option" :height="height" />
</template>