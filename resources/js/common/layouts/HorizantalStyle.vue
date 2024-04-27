<template>
    <a-layout>
        <MainHeader :cssSettings="cssSettings">
            <a-layout-header class="main-header">
                <a-row>
                    <a-col :span="1">
                        <div class="logo">
                            <img
                                :src="
                                    appSetting.left_sidebar_theme == 'dark'
                                        ? appSetting.small_dark_logo_url
                                        : appSetting.small_light_logo_url
                                "
                            />
                        </div>
                    </a-col>
                    <a-col :span="22" style="padding: 0px 12px">
                        <MainMenus />
                    </a-col>
                    <a-col :span="1">
                        <div class="horizontal-left">
                            <a-menu
                                :theme="appSetting.left_sidebar_theme"
                                mode="horizontal"
                            >
                                <a-sub-menu key="profile">
                                    <template #title>
                                        <a-avatar
                                            size="small"
                                            :src="user.profile_image_url"
                                        />
                                    </template>

                                    <a-menu-item
                                        v-if="pageTitle != 'Setup Company'"
                                        @click="
                                            () => {
                                                $router.push({
                                                    name: 'admin.settings.profile.index',
                                                });
                                            }
                                        "
                                        key="profile"
                                    >
                                        <IdcardOutlined />
                                        <span
                                            >{{ $t("menu.profile") }}
                                            <span>{{
                                                $t("menu.settings")
                                            }}</span></span
                                        >
                                    </a-menu-item>
                                    <a-sub-menu key="language">
                                        <template #title>
                                            <TranslationOutlined />
                                            <span>{{
                                                selectedLang
                                            }}</span></template
                                        >
                                        <a-menu-item
                                            v-for="lang in langs"
                                            :key="lang.key"
                                            @click="langSelected(lang.key)"
                                        >
                                            <a-space>
                                                <a-avatar
                                                    shape="square"
                                                    size="small"
                                                    :src="lang.image_url"
                                                />
                                                {{ lang.name }}
                                            </a-space>
                                        </a-menu-item>
                                    </a-sub-menu>
                                    <a-menu-item @click="logout" key="log_out">
                                        <LogoutOutlined />
                                        <span>{{ $t("menu.logout") }}</span>
                                    </a-menu-item>
                                </a-sub-menu>
                            </a-menu>
                        </div>
                    </a-col>
                </a-row>
            </a-layout-header>
        </MainHeader>
        <a-layout-content>
            <MainContentArea :cssSettings="cssSettings">
                <router-view></router-view>
                <ChatBotButton
                    v-if="
                        (permsArray.includes('bot_view') ||
                            permsArray.includes('admin')) &&
                        !(pageTitle == 'menu.chatbot') &&
                        !(pageTitle == 'Setup Company')
                    "
                />
            </MainContentArea>
        </a-layout-content>
    </a-layout>
</template>

<script>
import { ref, computed } from "vue";
import TopBar from "./TopBar.vue";
import LeftSidebarBar from "./LeftSidebar.vue";
import { Div, MainArea, MainContentArea, MainHeader } from "./style";
import common from "../composable/common";
import MainMenus from "./MainMenus.vue";
import MenuMode from "./MenuMode.vue";
import { useStore } from "vuex";
import { loadLocaleMessages } from "../i18n";
import { useI18n } from "vue-i18n";
import {
    IdcardOutlined,
    LogoutOutlined,
    TranslationOutlined,
} from "@ant-design/icons-vue";
import ChatBotButton from "./ChatBotButton.vue";
import { Modal, notification } from "ant-design-vue";
export default {
    components: {
        TopBar,
        LeftSidebarBar,
        Div,
        MainArea,
        MainMenus,
        MainContentArea,
        MainHeader,
        MenuMode,
        LogoutOutlined,
        IdcardOutlined,
        TranslationOutlined,
        ChatBotButton,
    },
    setup() {
        const { appSetting, cssSettings, user, permsArray, pageTitle } =
            common();
        const collapsed = ref(false);
        const store = useStore();
        const { locale, t } = useI18n();

        const menuClicked = (showHide) => {
            collapsed.value = showHide;
        };

        const menuSelected = () => {
            if (innerWidth <= 991) {
                collapsed.value = true;
            }
        };

        const logout = () => {
            Modal.confirm({
            title: t("common.resetheading") + "?",
            // icon: createVNode(ExclamationCircleOutlined),
            content: t("common.resetmessage"),
            centered: true,
            okText: t("common.yes"),
            okType: "danger",
            cancelText: t("common.no"),
            onOk() {
            store.dispatch("auth/logout");
            localStorage.clear();
            },
            onCancel() { },
        });

        };

        const selectedLang = ref(store.state.auth.lang);
        const langSelected = async (lang) => {
            store.commit("auth/updateLang", lang);
            await loadLocaleMessages(i18n, lang);

            selectedLang.value = lang;
            locale.value = lang;
        };

        return {
            collapsed,
            menuClicked,
            menuSelected,
            user,
            langSelected,
            selectedLang,
            pageTitle,
            langs: computed(() => store.state.auth.allLangs),

            appSetting,
            cssSettings,
            logout,
            permsArray,

            innerWidth: window.innerWidth,
        };
    },
};
</script>

<style lang="less" scoped>
.logo {
    float: left;
    margin-top: -3px;
    margin-right: 24px;
    img {
        height: 32px;
    }
}
.horizontal-left {
    float: right;
    margin-top: -3px;
    margin-left: 24px;
}
</style>
