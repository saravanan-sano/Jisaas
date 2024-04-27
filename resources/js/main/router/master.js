import Admin from '../../common/layouts/Admin.vue';
import Factorydelete from '../views/master-delete/factorydelete/index.vue';
export default [
	{
		path: '/',
		component: Admin,
		children: [
			{
				path: '/admin/factorydelete',
				component: Factorydelete,
				name: 'admin.factorydelete.index',
				meta: {
					requireAuth: true,
					menuParent: "master",
					menuKey: route => "factorydelete",
					permission: "factorydelete_view",
				}
			},
		]

	}
];
