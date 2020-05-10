require('./bootstrap');
import ViewUI from 'view-design';
import 'view-design/dist/styles/iview.css';
window.Vue = require('vue');
Vue.use(ViewUI);

Vue.component('companyUpload', require('./components/CompanyUpload').default);
Vue.component('companyInfo', require('./components/CompanyInfo').default);
Vue.component('export', require('./components/ExportAndApply').default);

const app = new Vue({
    el: '#app',
});
