import './assets/main.css'
import './index.css'
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import VueTelInput from 'vue-tel-input'
import 'vue-tel-input/vue-tel-input.css'
import axios from 'axios'

import App from './App.vue'
import router from './router'
import ElementPlus from 'element-plus'
import * as ElementPlusIconsVue from '@element-plus/icons-vue'
import 'element-plus/dist/index.css'
import 'flag-icons/css/flag-icons.min.css'

axios.defaults.baseURL = 'https://sourxhr-backend-5190c64de794.herokuapp.com/api/v1'
// axios.defaults.baseURL = 'http://localhost:8000/api/v1'
axios.defaults.timeout = 10000
const app = createApp(App)

// Injection de l'instance axios
app.provide('axios', axios)

for (const [key, component] of Object.entries(ElementPlusIconsVue)) {
  app.component(key, component)
}
app.use(createPinia())
app.use(VueTelInput)
app.use(ElementPlus)
app.use(router)

app.mount('#app')
