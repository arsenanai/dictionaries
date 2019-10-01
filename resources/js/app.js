require('popper.js');
require('bootstrap');

import Vue from 'vue'
import VueRouter from 'vue-router'

Vue.use(VueRouter)

import App from './views/App'
/*import Hello from './views/Hello'
import Home from './views/Home'*/
import UsersIndex from './views/UsersIndex';
import UsersEdit from './views/UsersEdit';
import UsersCreate from './views/UsersCreate';
import GroupsIndex from './views/GroupsIndex';
import GroupsEdit from './views/GroupsEdit';
import GroupsCreate from './views/GroupsCreate';
import SubgroupsIndex from './views/SubgroupsIndex';
import SubgroupsEdit from './views/SubgroupsEdit';
import SubgroupsCreate from './views/SubgroupsCreate';
import CodesIndex from './views/CodesIndex';
import CodesEdit from './views/CodesEdit';
import CodesCreate from './views/CodesCreate';
import NotFound from './views/NotFound';
import Login from './views/Login';
import Logout from './views/Logout';
import {i18n} from './i18n';
import Settings from './views/Settings';

const router = new VueRouter({
    mode: 'history',
    routes: [
        {
            path: '/',
            name: 'auth.login',
            component: Login,
            meta: {
              public: true
            }
        },
        {
            path: '/logout',
            name: 'auth.logout',
            component: Logout,
        },

        {
            path: '/users',
            name: 'users.index',
            component: UsersIndex,
        },
        {
            path: '/profile',
            name: 'profile',
            component: UsersEdit,
        },
        {
            path: '/users/:id/edit',
            name: 'users.edit',
            component: UsersEdit,
        },
        {
            path: '/users/create',
            name: 'users.create',
            component: UsersCreate,
        },

        {
            path: '/groups',
            name: 'groups.index',
            component: GroupsIndex,
        },
        {
            path: '/groups/:id/edit',
            name: 'groups.edit',
            component: GroupsEdit,
        },
        {
            path: '/groups/create',
            name: 'groups.create',
            component: GroupsCreate,
        },

        {
            path: '/subgroups',
            name: 'subgroups.index',
            component: SubgroupsIndex,
        },
        {
            path: '/subgroups/:id/edit',
            name: 'subgroups.edit',
            component: SubgroupsEdit,
        },
        {
            path: '/subgroups/create',
            name: 'subgroups.create',
            component: SubgroupsCreate,
        },

        {
            path: '/codes',
            name: 'codes.index',
            component: CodesIndex,
        },
        {
            path: '/codes/:id/edit',
            name: 'codes.edit',
            component: CodesEdit,
        },
        {
            path: '/codes/create',
            name: 'codes.create',
            component: CodesCreate,
        },
        {
            path: '/settings',
            name: 'settings',
            component: Settings,
        },

        { path: '/404', name: '404', component: NotFound, meta: {public: true} },
        { path: '*', redirect: '/404', meta: {public: true} },
    ],
});

router.beforeEach((to, from, next) => {
  if (to.matched.some(record => record.meta.public)) {
    next()
  } else {
    if (localStorage.getItem('enstru_token')===null) {
      next({ name: 'auth.login' })
    } else {
      next()
    }
  }
})

const app = new Vue({
    i18n,
    el: '#app',
    components: { App },
    router,
});