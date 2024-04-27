import Admin from '../../common/layouts/Admin.vue';
import UserIndex from '../views/users/index.vue';

export default [
	{
		path: '/',
		component: Admin,
		children: [
			{
				path: '/admin/users',
				component: UserIndex,
				name: 'admin.users.index',
				meta: {
					requireAuth: true,
					menuParent: "users",
					menuKey: "staff_members",
                    crudKey: "users",
                    langKey: "staff_member",
					permission: "users_view",
				}
			},
			{
				path: '/admin/customers',
				component: UserIndex,
				name: 'admin.customers.index',
				meta: {
					requireAuth: true,
					menuParent: "parties",
					menuKey: "customers",
					crudKey: "customers",
                    langKey: "customer",
					permission: "customers_view",
				}
			},
			{
				path: '/admin/suppliers',
				component: UserIndex,
				name: 'admin.suppliers.index',
				meta: {
					requireAuth: true,
					menuParent: "parties",
					menuKey: "suppliers",
					crudKey: "suppliers",
                    langKey: "supplier",
					permission: "suppliers_view"
				}
			},
			{
				path: '/admin/referral',
				component: UserIndex,
				name: 'admin.referral.index',
				meta: {
					requireAuth: true,
					menuParent: "parties",
					menuKey: "referral",
					crudKey: "referral",
                    langKey: "referral",
					permission: "referral_view"
				}
			},
			{
				path: '/admin/delivery-partner',
				component: UserIndex,
				name: 'admin.delivery_partner.index',
				meta: {
					requireAuth: true,
					menuParent: "users",
					menuKey: "delivery_partner",
					crudKey: "users",
                    langKey: "delivery_partner",
					permission: "delivery_partner_view",
				}
			}
		]

	}
]
