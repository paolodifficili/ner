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
  { path: '/lista', component: Vue.defineAsyncComponent(()=>loadModule('./vue/lista.vue', options)) },
  { path: '/table', component: Vue.defineAsyncComponent(()=>loadModule('./vue/table.vue', options)) },
  { path: '/profile/:id', component: Vue.defineAsyncComponent(()=>loadModule('./vue/profile.vue', options)) },
  { path: '/batch', component: Vue.defineAsyncComponent(()=>loadModule('./vue/batch.vue', options)) },
  { path: '/batch/:id', component: Vue.defineAsyncComponent(()=>loadModule('./vue/batch_item.vue', options)) },
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


const app = createApp(
    Vue.defineAsyncComponent(() =>   loadModule('./vue/app.vue', options)  ),
)
.use(router)
.use(vuetify)
.mount('#app');