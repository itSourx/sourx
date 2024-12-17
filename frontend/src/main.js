import './assets/main.css'
import './index.css'
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import VueTelInput from 'vue-tel-input'
import 'vue-tel-input/vue-tel-input.css'

import App from './App.vue'
import router from './router'
import ElementPlus from 'element-plus'
import * as ElementPlusIconsVue from '@element-plus/icons-vue'
import 'element-plus/dist/index.css'
import 'flag-icons/css/flag-icons.min.css'

const app = createApp(App)

for (const [key, component] of Object.entries(ElementPlusIconsVue)) {
  app.component(key, component)
}
app.use(createPinia())
app.use(VueTelInput)
app.use(ElementPlus)
app.use(router)

app.mount('#app')
