import Admin from '../../common/layouts/Admin.vue';
import ChatBot from '../views/ChatBot.vue';

export default [
	{
		path: '/',
		component: Admin,
		children: [
			{
				path: '/admin/chatbot',
				component: ChatBot,
				name: 'admin.chatbot.index',
				meta: {
					requireAuth: true,
					menuParent: "chatbot",
					menuKey: route => "chatbot",
				}
			}
		]

	}
]
