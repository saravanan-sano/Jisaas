import Login from '../views/auth/Login.vue';
import Register from '../views/auth/RegisterForm.vue'
import MobileLogin from '../views/auth/MobileLogin.vue';
import CommonInvoice from '../../main/views/stock-management/invoice-template/common/CommonInvoice.vue'
import CommonReceipt from '../../main/views/stock-management/invoice-template/common/CommonReceipt.vue'

export default [
    {
        path: '/admin/login',
        component: Login,
        name: 'admin.login',
        meta: {
            requireUnauth: false,
            menuKey: route => "login",
        }
    },
    {
        path: '/register',
        component: Register,
        name: 'admin.register',
        meta: {
            requireUnauth: false,
            menuKey: route => "register",
        }
    },
    {
        path: '/admin/mobilelogin',
        component: MobileLogin,
        name: 'admin.mobilelogin',
        meta: {
            requireUnauth: false,
            menuKey: route => "mobilelogin",
        }
    },
    {
        path: '/common/invoice/:id',
        component: CommonInvoice,
        name: 'admin.common_invoice',
        meta: {
            requireUnauth: true,
            menuKey: route => "common_invoice",
        }
    },
    {
        path: '/common/receipt/:id',
        component: CommonReceipt,
        name: 'admin.common_receipt',
        meta: {
            requireUnauth: true,
            menuKey: route => "common_receipt",
        }
    },
]
