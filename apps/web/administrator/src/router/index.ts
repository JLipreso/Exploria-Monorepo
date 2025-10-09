
import { createRouter, createWebHistory } from 'vue-router'
import { project } from '@exploria/shared-core'

const router = createRouter({
  history: createWebHistory(project.app.base_url),
  routes: [
    {
      path: '/',
      redirect: 'signin'
    },
    {
      path: '/signin',
      name: 'signin',
      component: () => import("../pages/SignInPage.vue"),
    },
  ],
})

export default router
