import Front from "../views/front/layouts/Front.vue";
import Home from "../views/front/Home.vue";
import LogIn from "../views/front/Login.vue";
import Categories from "../views/front/Categories.vue";
import Dashboard from "../views/front/dashboard/Dashboard.vue";
import Profile from "../views/front/dashboard/Profile.vue";
import Orders from "../views/front/dashboard/Orders.vue";
import Search from "../views/front/layouts/SearchProducts.vue";
import Checkout from "../views/front/dashboard/Checkout.vue";
import CheckoutSuccess from "../views/front/dashboard/CheckoutSuccess.vue";
import Pages from "../views/front/Pages.vue";
import ProductView from "../views/front/ProductView.vue";

export default [
    {
        path: "/",
        component: Front,
        children: [
            {
                path: "/store/:warehouse",
                component: Home,
                name: "front.homepage",
                meta: {
                    requireUnauth: true,
                    menuKey: "homepage",
                },
            },
            {
                path: "/store/:warehouse/:id",
                component: ProductView,
                name: "front.product",
                meta: {
                    requireUnauth: true,
                    menuKey: "product",
                },
            },
            {
                path: "/store/:warehouse/categories/:slug*",
                component: Categories,
                name: "front.categories",
                meta: {
                    requireUnauth: true,
                    menuKey: "categories",
                },
            },
            {
                path: "/store/:warehouse/dashboard",
                component: Dashboard,
                name: "front.dashboard",
                meta: {
                    requireAuth: true,
                    menuKey: "dashboard",
                },
            },
            {
                path: "/store/:warehouse/profile",
                component: Profile,
                name: "front.profile",
                meta: {
                    requireAuth: true,
                    menuKey: "profile",
                },
            },
            {
                path: "/store/:warehouse/orders",
                component: Orders,
                name: "front.orders",
                meta: {
                    requireAuth: true,
                    menuKey: "orders",
                },
            },
            {
                path: "/store/:warehouse/search",
                component: Search,
                name: "front.search",
                meta: {
                    requireAuth: true,
                    menuKey: "search",
                },
            },
            {
                path: "/store/:warehouse/checkout",
                component: Checkout,
                name: "front.checkout",
                meta: {
                    requireAuth: true,
                    menuKey: "orders",
                },
            },
            {
                path: "/store/:warehouse/checkout-success/:uniqueId",
                component: CheckoutSuccess,
                name: "front.checkout.success",
                meta: {
                    requireAuth: true,
                    menuKey: "orders",
                },
            },
            {
                path: "/store/:warehouse/pages/:uniqueId",
                component: Pages,
                name: "front.pages",
                meta: {
                    requireUnauth: true,
                    menuKey: "pages",
                },
            },
        ],
    },
    {
        path: "/store/:warehouse/login",
        component: LogIn,
        name: "front.login",
        meta: {
            requireUnauth: true,
            menuKey: "login",
        },
    },
];
