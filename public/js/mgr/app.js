
const { createApp } = Vue;
const { loadModule } = window['vue3-sfc-loader'];


const options = {
  moduleCache: {
    vue: Vue,
  },
  getFile(url) {
    return fetch(url).then((resp) =>
      resp.ok ? resp.text() : Promise.reject(resp)
    );
  },
  addStyle(styleStr) {
    const style = document.createElement('style');
    style.textContent = styleStr;
    const ref = document.head.getElementsByTagName('style')[0] || null;
    document.head.insertBefore(style, ref);
  },
  log(type, ...args) {
    console.log(type, ...args);
  },
};

const routes = [
  { path: '/', component: Vue.defineAsyncComponent(()=>loadModule('./vue/home.vue', options)) },
  { path: '/upload', component: Vue.defineAsyncComponent(()=>loadModule('./vue/upload.vue', options)) },
  { path: '/file', component: Vue.defineAsyncComponent(()=>loadModule('./vue/file.vue', options)) },
  { path: '/profile/:id', component: Vue.defineAsyncComponent(()=>loadModule('./vue/profile.vue', options)) },
  { path: '/batch', component: Vue.defineAsyncComponent(()=>loadModule('./vue/batch.vue', options)) },
  { path: '/config', component: Vue.defineAsyncComponent(()=>loadModule('./vue/config.vue', options)) },
  { path: '/batch/:id', component: Vue.defineAsyncComponent(()=>loadModule('./vue/batch_item.vue', options)) },
  { path: '/job/:id', component: Vue.defineAsyncComponent(()=>loadModule('./vue/job_item.vue', options)) },
  { path: '/batchnew', component: Vue.defineAsyncComponent(()=>loadModule('./vue/batch_new.vue', options)) },
  { path: '/crud2', component: Vue.defineAsyncComponent(()=>loadModule('./vue/crud2.vue', options)) },
]

const router = VueRouter.createRouter({
  history: VueRouter.createWebHashHistory(),
  routes,
})
/*
const app = createApp({
  components: {
    VueMainComponent: Vue.defineAsyncComponent(() =>   loadModule('vue-main-component.vue', options)  ),
    VueFooter: Vue.defineAsyncComponent(() =>  loadModule('vue-footer.vue', options)  ),
  },
})
*/

const { createVuetify } = Vuetify
const vuetify = createVuetify()


console.log('INIT axios');

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';


console.log('INIT Reverb Push');

/*
REVERB_APP_ID=my-app-id
REVERB_APP_KEY=my-app-key
REVERB_APP_SECRET=my-app-secret
REVERB_HOST=localhost
REVERB_PORT=7888
REVERB_SCHEME=http


VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"


*/

console.log(window.location.hostname);

window.Pusher = Pusher;

window.Echo = new Echo({
  broadcaster: 'reverb',
  key: 'my-app-key',
  wsHost: window.location.hostname,
  wsPort: 7888,
  wssPort: 7888,
  forceTLS: false,
  enabledTransports: ['ws', 'wss'],
  disableStats: true,
})

console.log(window.Echo);

/*
window.Echo.channel('chat')
  .listen('GotMessage', (e) => {
    console.log('------------------- GotMessage----------------');
    console.log(e);
});
*/


const app = createApp(
  Vue.defineAsyncComponent(() =>   loadModule('./vue/app.vue', options)  ),
)
.use(router)
.use(vuetify)
.mount('#app');