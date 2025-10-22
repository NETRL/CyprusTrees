// Custom TailAdmin Theme extending PrimeVue Aura
import { definePreset } from '@primeuix/themes';
// import Aura from '@primevue/themes/aura';
import Aura from '@primeuix/themes/aura'

const TailAdminTheme = definePreset(Aura, {
  semantic: {
    primary: {
       50:  'var(--color-brand-50)',
      100: 'var(--color-brand-100)',
      200: 'var(--color-brand-200)',
      300: 'var(--color-brand-300)',
      400: 'var(--color-brand-400)',
      500: 'var(--color-brand-500)',
      600: 'var(--color-brand-600)',
      700: 'var(--color-brand-700)',
      800: 'var(--color-brand-800)',
      900: 'var(--color-brand-900)',
      950: 'var(--color-brand-950)',
    },
    css: {
      '.p-datatable': {
        '--p-datatable-header-background': 'rgba(0, 0, 0, 0) !important',
        '--p-datatable-header-cell-background': 'rgba(0, 0, 0, 0) !important',
        '--p-datatable-row-background': 'rgba(0, 0, 0, 0) !important',
        '--p-paginator-background': 'rgba(0, 0, 0, 0) !important',
        '--p-checkbox-background': 'rgba(0, 0, 0, 0) !important',
        '--p-inputtext-background': 'rgba(0, 0, 0, 0) !important',
        '--p-select-background': 'rgba(0, 0, 0, 0) !important',
      },
      '.p-inputtext': {
        ' --p-inputtext-background':' rgba(0, 0, 0, 0) !important',
      },
      '.p-multiselect': {
         '--p-multiselect-background': 'rgba(0, 0, 0, 0) !important',
      },
    },
  },
});

export default TailAdminTheme;