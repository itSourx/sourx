import './index.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import VueTelInput from 'vue-tel-input'
import 'vue-awesome-paginate/dist/style.css'
import '../node_modules/vue-tel-input/dist/vue-tel-input.css';
import App from './App.vue'
import router from './router'
import lucideIcons from './lucide-icons'
import { useUserStore } from '@/stores/UserStore/UserStore'
import VueAwesomePaginate from 'vue-awesome-paginate'
import LoaderComponent from '@/components/LoaderComponent.vue'
import DocumentIcon from '@/components/DocumentIcon.vue'

import Vue3Toastify from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

const globalOptions = {
  mode: 'auto'
}

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.use(lucideIcons)
app.use(Vue3Toastify, {
  autoClose: 3000,
  position: 'top-right'
})
app.use(VueAwesomePaginate)
app.use(VueTelInput, globalOptions)

app.component('LoaderComponent', LoaderComponent)
app.component('DocumentIcon', DocumentIcon)

const userStore = useUserStore()

userStore.loadUserFromLocalStorage().then(() => {
  app.mount('#app')
})
