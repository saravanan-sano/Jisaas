import Admin from '../../common/layouts/Admin.vue';
import Coupons from '../views/coupon-manager/coupon/Index.vue';
import CouponsHistory from '../views/coupon-manager/coupon/Index.vue';

export default [
	{
		path: '/',
		component: Admin,
		children: [
			{
				path: '/admin/coupons',
				component: Coupons,
				name: 'admin.coupons.index',
				meta: {
					requireAuth: true,
					menuParent: "coupons",
					menuKey: route => "coupons",
				}
			},
			{
				path: '/admin/coupons-history',
				component: CouponsHistory,
				name: 'admin.coupons_history.index',
				meta: {
					requireAuth: true,
					menuParent: "coupons_history",
					menuKey: route => "coupons_history",
				}
			}
		]

	}
]
