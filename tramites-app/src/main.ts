import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from '@/router'
import '@/style.css'
import App from './App.vue'
import { useAuthStore } from '@/stores/auth'

const app = createApp(App)

// Pinia debe instalarse antes de usar cualquier store
app.use(createPinia())
app.use(router)

// Restaurar sesión desde localStorage antes de montar para evitar
// el flash de "no autenticado" si el usuario ya tenía una sesión activa.
const authStore = useAuthStore()
authStore
  .initFromStorage()
  .finally(() => app.mount('#app'))
