<template>
    <a-layout-header
        class="topbar-menu bg-color"
        :style="{ padding: '0 16px' }"
    >
        <a-row>
            <a-col :span="2">
                <a-space>
                    <MenuOutlined class="trigger" @click="showHideMenu" />
                </a-space>
            </a-col>
            <a-col :span="4">
                <a-space>
                    <CheckInCheckOut v-if="!permsArray.includes('admin')" />
                </a-space>
            </a-col>
            <a-col :span="18">
                <HeaderRightIcons>
                    <a-space>
                        <template
                            v-if="
                                innerWidth > 768 &&
                                (permsArray.includes('pos_view') ||
                                    permsArray.includes('admin')) &&
                                willSubscriptionModuleVisible('pos')
                            "
                        >
                            <a-button
                                v-if="pageTitle != 'Setup Company'"
                                @click="
                                    () => {
                                        $router.push({
                                            name: 'admin.pos.index',
                                        });
                                    }
                                "
                                type="link"
                            >
                                <ShoppingCartOutlined />
                                <span>{{ $t("menu.pos") }}</span>
                            </a-button>
                            <a-divider type="vertical" />
                        </template>
                        <template
                            v-if="
                                selectedWarehouse &&
                                selectedWarehouse.name &&
                                pageTitle != 'Setup Company'
                            "
                        >
                            <template
                                v-if="appSetting.shortcut_menus != 'bottom'"
                            >
                                <AffixButton position="top" class="mob" />
                                <a-divider type="vertical" />
                            </template>
                            <ChangeWarehouse />
                            <a-divider type="vertical" />
                        </template>
                        <a-dropdown
                            :placement="
                                appSetting.rtl ? 'bottomLeft' : 'bottomRight'
                            "
                            class="mob"
                        >
                            <a class="ant-dropdown-link" @click.prevent>
                                {{ selectedLang }}
                                <DownOutlined />
                            </a>
                            <template #overlay>
                                <a-menu>
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
                                </a-menu>
                            </template>
                        </a-dropdown>
                        <a-divider type="vertical" />
                        <!-- <a-button
                            type="link"
                            @click="
                                () => {
                                    $router.push({
                                        name: 'admin.settings.profile.index',
                                    });
                                }
                            "
                            class="p-0"
                        >
                            <a-avatar size="small" :src="user.profile_image_url" />
                        </a-button> -->

                        <!-- <a-button
                            type="link"
                            @click="logout"
                            class="p-0"
                        >
                         <LogoutOutlined />

                        </a-button> -->
                        <a-button type="link" @click="logout" class="p-0">
                            <LogoutOutlined />
                            <span>{{ $t("menu.logout") }}</span>
                        </a-button>
                    </a-space>
                </HeaderRightIcons>
            </a-col>
        </a-row>
    </a-layout-header>
</template>

<script>
import { ref, reactive, computed, onMounted } from "vue";
import { useStore } from "vuex";
import {
    MenuOutlined,
    DownOutlined,
    ShoppingCartOutlined,
    LogoutOutlined,
} from "@ant-design/icons-vue";
import { useI18n } from "vue-i18n";
import { loadLocaleMessages } from "../i18n";
import { HeaderRightIcons } from "./style";
import common from "../../common/composable/common";
import MenuMode from "./MenuMode.vue";
import ChangeWarehouse from "./ChangeWarehouse.vue";
import AffixButton from "./AffixButton.vue";
import { Modal, notification } from "ant-design-vue";
import CheckInCheckOut from "./CheckInCheckOut.vue";

export default {
    components: {
        MenuOutlined,
        DownOutlined,
        HeaderRightIcons,
        MenuMode,
        ChangeWarehouse,
        AffixButton,
        ShoppingCartOutlined,
        LogoutOutlined,
        CheckInCheckOut,
    },
    setup(props, { emit }) {
        const {
            user,
            appSetting,
            permsArray,
            menuCollapsed,
            willSubscriptionModuleVisible,
            selectedWarehouse,
            pageTitle,
        } = common();
        const store = useStore();
        const selectedLang = ref(store.state.auth.lang);
        const { locale, t } = useI18n();
        const themeMode = ref(
            window.config.theme_mode == "light" ? false : true
        );
        const themeModeLoading = ref(false);

        const langSelected = async (lang) => {
            store.commit("auth/updateLang", lang);
            await loadLocaleMessages(i18n, lang);

            selectedLang.value = lang;
            locale.value = lang;
        };

        const showHideMenu = () => {
            store.commit("auth/updateMenuCollapsed", !menuCollapsed.value);
        };

        const logout = () => {
            Modal.confirm({
                title: t("common.logoutheader") + "?",
                // icon: createVNode(ExclamationCircleOutlined),
                content: t("common.logoutmessage"),
                centered: true,
                okText: t("common.yes"),
                okType: "danger",
                cancelText: t("common.no"),
                onOk() {
                    store.dispatch("auth/logout");
                    localStorage.clear();
                },
                onCancel() {},
            });
        };

        const themeModeChanged = (checked) => {
            const mode = checked ? "dark" : "light";
            themeModeLoading.value = true;

            axiosAdmin
                .post("change-theme-mode", {
                    theme_mode: mode,
                })
                .then((response) => {
                    if (response.data.status == "success") {
                        window.location.reload();
                    }
                    themeModeLoading.value = false;
                });
        };

        return {
            selectedWarehouse,
            permsArray,
            pageTitle,
            appSetting,
            willSubscriptionModuleVisible,
            logout,
            showHideMenu,
            langSelected,
            selectedLang,
            langs: computed(() => store.state.auth.allLangs),

            user,

            themeMode,
            themeModeChanged,
            themeModeLoading,

            innerWidth: window.innerWidth,
        };
    },
};
</script>

<style lang="less">
.trigger {
    font-size: 18px;
    line-height: 64px;
    padding-top: 4px;
    cursor: pointer;
    transition: color 0.3s;
}

.trigger:hover {
    color: #1890ff;
}
</style>
