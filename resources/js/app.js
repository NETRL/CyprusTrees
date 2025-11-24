import '../css/main.css';
import './bootstrap';

// import "primevue/resources/themes/lara-light-indigo/theme.css";
// import "primeflex/primeflex.css";
// import "primevue/resources/primevue.min.css";
import "primeicons/primeicons.css";
// import "/node_modules/flag-icons/css/flag-icons.min.css";


import { createInertiaApp, Head } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { Link } from '@inertiajs/vue3';
import AppWrapper from './Pages/App.vue'


// PrimeVue services
import ConfirmationService from 'primevue/confirmationservice'
import ToastService from 'primevue/toastservice'

import Checkbox from 'primevue/checkbox';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import PrimeVue from 'primevue/config';
import Ripple from 'primevue/ripple';
import Accordion from 'primevue/accordion';
import AccordionTab from 'primevue/accordiontab';
import Avatar from 'primevue/avatar';
import AvatarGroup from 'primevue/avatargroup';
import Carousel from 'primevue/carousel';
import Chip from 'primevue/chip';
import ConfirmDialog from 'primevue/confirmdialog';
import Dialog from 'primevue/dialog';
import Divider from 'primevue/divider';
import Badge from 'primevue/badge';
import Calendar from 'primevue/calendar';
import Toast from 'primevue/toast';
import StyleClass from 'primevue/styleclass';
import HasPermissionDirective from '@/Components/Directives/HasPermissionDirective.vue';
import HasRoleDirective from '@/Components/Directives/HasRoleDirective.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Toolbar from 'primevue/toolbar';
import BadgeDirective from 'primevue/badgedirective';
import Menu from 'primevue/menu';
import Paginator from 'primevue/paginator';
import MultiSelect from 'primevue/multiselect';
import Password from 'primevue/password';
import ProgressBar from 'primevue/progressbar';
import RadioButton from 'primevue/radiobutton';
import Textarea from 'primevue/textarea';
import InputSwitch from 'primevue/inputswitch';
import InputNumber from 'primevue/inputnumber';
import FileUpload from 'primevue/fileupload';
import Dropdown from 'primevue/dropdown';
import Rating from 'primevue/rating';
import Sidebar from 'primevue/sidebar';
import TabPanel from 'primevue/tabpanel';
import Knob from 'primevue/knob';
import TabView from 'primevue/tabview';
import TabMenu from 'primevue/tabmenu';
import Tag from 'primevue/tag';
import Tooltip from 'primevue/tooltip';

import { __ } from './lang-handler';
import TailAdminTheme from './primevue-theme';

import Aura from '@primeuix/themes/aura'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

(function applyInitialTheme() {
  const storageKey = 'theme';
  const saved = localStorage.getItem(storageKey);
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  const isDark = saved ? saved === 'dark' : prefersDark;

  const root = document.documentElement;
  if (isDark) {
    root.classList.add('dark');
  } else {
    root.classList.remove('dark');
  }
})();

createInertiaApp({
  title: (title) => title ? `${title} - ${appName}` : appName,
  resolve: (name) =>
    resolvePageComponent(
      `./Pages/${name}.vue`,
      import.meta.glob('./Pages/**/*.vue'),
    ),
  setup({ el, App, props, plugin }) {
    return createApp({
      render: () =>
        h(AppWrapper, null, { default: () => h(App, props) }),
    })
      .use(plugin)
      .use(ZiggyVue)
      .component('Link', Link)
      .component('Head', Head)
      .mixin({ methods: { __ } })
      // .mixin({ methods: { route } })

      .use(PrimeVue, {
        unstyled: false,
        theme: {
          preset: TailAdminTheme,
          options: { darkModeSelector: '.dark' }
        }
      })
      .use(ToastService)
      .use(ConfirmationService)
      .component('Accordion', Accordion)
      .component('AccordionTab', AccordionTab)
      .component('AvatarGroup', AvatarGroup)
      .component('Avatar', Avatar)
      .component('Badge', Badge)
      .component('Calendar', Calendar)
      .component('Carousel', Carousel)
      .component('Chip', Chip)
      .component('ConfirmDialog', ConfirmDialog)
      .component('Dialog', Dialog)
      .component('Divider', Divider)
      .component('InputText', InputText)
      .component('Button', Button)
      .component('Checkbox', Checkbox)
      .component('Message', Message)
      .component('Menu', Menu)
      .component('Toast', Toast)
      .component('DataTable', DataTable)
      .component('Column', Column)
      .component('Toolbar', Toolbar)
      .component('Paginator', Paginator)
      .component('MultiSelect', MultiSelect)
      .component('Password', Password)
      .component('Dropdown', Dropdown)
      .component('FileUpload', FileUpload)
      .component('InputNumber', InputNumber)
      .component('InputSwitch', InputSwitch)
      .component('Textarea', Textarea)
      .component('RadioButton', RadioButton)
      .component('ProgressBar', ProgressBar)
      .component('Rating', Rating)
      .component('Sidebar', Sidebar)
      .component('TabPanel', TabPanel)
      .component('Knob', Knob)
      .component('TabView', TabView)
      .component('TabMenu', TabMenu)
      .component('Tag', Tag)
      .directive('ripple', Ripple)
      .directive('tooltip', Tooltip)
      .directive('styleclass', StyleClass)
      .directive('badge', BadgeDirective)
      .directive('has-permission', HasPermissionDirective)
      .directive('has-role', HasRoleDirective)
      .mount(el);
  },
  progress: {
    color: '#4B5563',
  },
});
