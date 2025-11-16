import { createRouter, createWebHistory } from 'vue-router';

// Import page components
import Home from '../pages/Home.vue';
import Books from '../pages/Books.vue';
import Profile from '../pages/Profile.vue';
import BookDiscussions from '../pages/BookDiscussions.vue';
import DiscussionDetail from '../pages/DiscussionDetail.vue';

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home,
    meta: { title: 'Sākums - Librorum' }
  },
  {
    path: '/books',
    name: 'Books',
    component: Books,
    meta: { title: 'Grāmatas - Librorum' }
  },
  {
    path: '/books/:isbn/discussions',
    name: 'BookDiscussions',
    component: BookDiscussions,
    meta: { title: 'Grāmatas diskusijas - Librorum' }
  },
  {
    path: '/books/:isbn/discussion/:discussionId',
    name: 'DiscussionDetail',
    component: DiscussionDetail,
    meta: { title: 'Diskusija - Librorum' }
  },
  {
    path: '/profile',
    name: 'Profile', 
    component: Profile,
    meta: { title: 'Profils - Librorum' }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Update page title on route change
router.beforeEach((to, from, next) => {
  document.title = to.meta?.title || 'Librorum';
  next();
});

export default router;