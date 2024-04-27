import Resetpassword from "../views/auth/ResetPassword"

export default [
    {
        path: "/admin/resetpassword",
        component: Resetpassword,
        name: "admin.resetpassword.index",
        meta: {
            requireAuth: true,
            menuParent: "resetpassword",
            menuKey: (route) => "resetpassword",
        },
    },
];
