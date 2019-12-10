import Vue from 'vue'
import App from './App.vue'
import './registerServiceWorker'
import router from './router'
import { createProvider } from './vue-apollo'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faTrash } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

library.add(faTrash)
Vue.component('font-awesome-icon', FontAwesomeIcon)

Vue.config.productionTip = false

new Vue({
    router,
    components: { App },
    el: '#app',
    apolloProvider: createProvider(),
    render: h => h(App)
}).$mount('#app')
