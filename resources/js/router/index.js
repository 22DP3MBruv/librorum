import { createRouter, createWebHistory } from 'vue-router';

// Import page components
import Home from '../pages/Home.vue';
import Books from '../pages/Books.vue';
import MyReading from '../pages/MyReading.vue';
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
    path: '/my-reading',
    name: 'MyReading',
    component: MyReading,
    meta: { title: 'Mana lasīšana - Librorum' }
  },
  {
    path: '/books/:isbn',
    name: 'BookDiscussions',
    component: BookDiscussions,
    meta: { title: 'Grāmatas diskusijas - Librorum' }
  },
  {
    path: '/books/:isbn/:discussionId',
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