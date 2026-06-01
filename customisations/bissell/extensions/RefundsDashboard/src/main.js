import { createApp } from 'vue'
import Dashboard from './Dashboard.vue'

import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import { aliases, mdi } from 'vuetify/iconsets/mdi'

const vuetify = createVuetify({
  icons: {
    defaultSet: 'mdi',
    aliases,
    sets: {
      mdi
    }
  },
  theme: {
    options: {
      customProperties: true
    },
    themes: {
      light: {
        colors: {
          bissellBackground: "#001F5B",
          bissellButton: "#3598DC",
          bissellButtonRed: "#BA1419"
        }
      }
    }
  }
});

const dashboard = createApp(Dashboard);
dashboard.use(vuetify);
dashboard.mount('#RefundsDashboard');