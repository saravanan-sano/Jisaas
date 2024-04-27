import Verify from "../views/auth/Verify.vue";

export default [
    {
        path: "/admin/verify",
        component: Verify,
        name: "admin.verify.index",
        meta: {
            requireAuth: true,
            menuParent: "verify",
            menuKey: (route) => "verify",
        },
    },
];
