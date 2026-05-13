import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from '@/router'
import '@/style.css'
import App from './App.vue'
import { useAuthStore } from '@/stores/auth'

const app = createApp(App)

// Pinia antes que cualquier store
app.use(createPinia())

const authStore = useAuthStore()

// initFromStorage DEBE completarse (éxito o error) antes de instalar el router.
// app.use(router) dispara la navegación inicial y con ella el beforeEach guard.
// Si el router se instala antes, el guard ve isAuthenticated=false (el token
// aún no se validó contra /auth/me) y redirige a /login aunque haya sesión.
authStore.initFromStorage().finally(() => {
  app.use(router)
  app.mount('#app')
})
