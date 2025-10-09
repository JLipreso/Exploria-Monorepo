
import { createRouter, createWebHistory } from 'vue-router'
import { config } from '@exploria/shared-core'

const router = createRouter({
  history: createWebHistory(config.app.base_url),
  routes: [],
})

export default router
