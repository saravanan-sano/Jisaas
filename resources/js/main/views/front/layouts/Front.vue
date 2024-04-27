<template>
    <a-layout v-if="frontWarehouse.online_store_enabled == 1">
        <a-layout-header
            :style="{
                background: `${frontAppSetting.company.primary_color}`,
                padding: 0,
            }"
        >
            <a-row type="flex" justify="center">
                <a-col :span="20">
                    <a-row type="flex" justify="space-between">
                        <a-col
                            :xs="12"
                            :sm="12"
                            :md="innerWidth >= 768 ? 6 : 12"
                            :lg="4"
                            :xl="4"
                            v-if="frontWarehouse && frontWarehouse.slug"
                        >
                            <!-- <LeftSidebar /> -->

                            <router-link
                                :to="{
                                    name: 'front.homepage',
                                    params: { warehouse: frontWarehouse.slug },
                                }"
                                :style="{ color: '#fff' }"
                            >
                                {{ $t("front.welcome_text") }}
                                {{ frontAppSetting.warehouse.name }}
                            </router-link>
                        </a-col>
                        <a-col
                            v-if="innerWidth >= 768"
                            :md="12"
                            :lg="12"
                            :xl="12"
                        >
                        </a-col>
                        <a-col
                            :xs="12"
                            :sm="12"
                            :md="innerWidth >= 768 ? 6 : 12"
                            :lg="8"
                            :xl="8"
                        >
                            <div
                                :style="{
                                    textAlign: 'right',
                                    color: '#ffffff',
                                }"
                            >
                                <CustomerServiceOutlined />
                                {{ frontAppSetting.warehouse.phone }}
                            </div>
                        </a-col>
                    </a-row>
                </a-col>
            </a-row>
        </a-layout-header>
        <div class="front-header">
            <router-link
                v-if="innerWidth >= 768"
                :to="{
                    name: 'front.homepage',
                    params: { warehouse: frontWarehouse.slug },
                }"
            >
                <img
                    :style="{
                        width: innerWidth >= 768 ? '150px' : '110px',
                    }"
                    :src="frontWarehouse.dark_logo_url"
                />
            </router-link>
            <a-auto-complete
                v-model:value="SearchFilterValue"
                :placeholder="`Search from ${frontAppSetting.warehouse.name}`"
                :options="products"
                size="large"
                :filter-option="filterOption"
                @select="onSearchSelect"
                style="width: 100%"
            >
                <template #option="item">
                    <div style="display: flex; justify-content: space-between">
                        <span>{{ item.value }}</span>
                    </div>
                </template>
            </a-auto-complete>
            <div
                style="
                    text-align: right;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                "
            >
                <Login
                    :modalVisible="loginModalVisible"
                    @modalClosed="loginModalClosed"
                />
                <CheckoutDrawer @openLoginModal="openLoginModal" />
            </div>
        </div>
        <!-- </a-layout-header> -->
        <div v-if="SearchFilterValue != ''">
            <search-products
                :SearchFilterValue="SearchFilterValue"
                :products="allProducts"
            />
        </div>
        <a-layout-content v-else>
            <div>
                <div :style="{ background: '#fff' }" class="subheader">
                    <a-row type="flex" justify="center">
                        <a-col :span="20">
                            <a-row>
                                <div class="subheader-menu-lists">
                                    <a-space
                                        v-if="
                                            frontAppSetting &&
                                            frontAppSetting.pages_widget
                                        "
                                    >
                                        <a
                                            v-for="(item, index) in values"
                                            :key="index"
                                            class="subheader-menu ml-25"
                                            :href="
                                                '/store/' +
                                                frontWarehouse.slug +
                                                '/pages/' +
                                                item.value
                                            "
                                        >
                                            {{ item.title }}
                                        </a>
                                    </a-space>
                                </div>
                            </a-row>
                        </a-col>
                    </a-row>
                </div>

                <router-view></router-view>
                <Footer />
            </div>
        </a-layout-content>
    </a-layout>

    <div v-else class="no-online-store-container">
        <a-result
            status="warning"
            :title="$t('warehouse.no_online_store_exists')"
        ></a-result>
    </div>
</template>
<script>
import { computed, defineComponent, onMounted, ref } from "vue";
import { useStore } from "vuex";
import {
    DownOutlined,
    MenuOutlined,
    AppstoreOutlined,
    PhoneOutlined,
    CustomerServiceOutlined,
} from "@ant-design/icons-vue";
import common from "../../../../common/composable/common";
import ProductCard from "../components/ProductCard.vue";
import Footer from "./Footer.vue";
import CheckoutDrawer from "../components/CheckoutDrawer.vue";
import Login from "../components/Login.vue";
import LeftSidebar from "./LeftSidebar.vue";
import LeftSidebarMenu from "./LeftSidebarMenu.vue";
import { useRouter } from "vue-router";
import _ from "lodash-es";
import SearchProducts from "./SearchProducts.vue";

export default defineComponent({
    components: {
        DownOutlined,
        MenuOutlined,
        AppstoreOutlined,
        PhoneOutlined,
        ProductCard,
        Footer,
        CheckoutDrawer,
        Login,
        LeftSidebar,
        LeftSidebarMenu,
        CustomerServiceOutlined,
        SearchProducts,
    },
    setup() {
        const store = useStore();
        const { frontWarehouse, frontAppSetting, frontUserToken } = common();
        const loginModalVisible = ref(false);
        const SearchFilterValue = ref("");
        const user = store.state.front.user;
        const router = useRouter();
        const openLoginModal = () => {
            loginModalVisible.value = true;
        };
        const content = ref([]);
        const values = ref([]);
        const products = ref([]);
        const allProducts = ref([]);
        const isLoggedIn = computed(() => {
            return store.getters["front/isLoggedIn"];
        });
        onMounted(() => {
            if (
                frontAppSetting.value.warehouse.ecom_visibility == 1 &&
                !isLoggedIn.value
            ) {
                router.push({
                    name: "front.login",
                });
            } else {
                axiosFront
                    .get(`front/homepage/${frontWarehouse.value.slug}`)
                    .then((response) => {
                        response.data.front_product_cards.map((item) => {
                            item.products_details.map((product) => {
                                allProducts.value.push(product);
                                products.value.push({ value: product.name });
                            });
                        });
                    });

                for (
                    let i = 0;
                    i < frontAppSetting.value.pages_widget.length;
                    i++
                ) {
                    const isMenu = frontAppSetting.value.pages_widget[i].isMenu;

                    // Check if isMenu is not null and the first element is 1
                    if (isMenu && isMenu[0] == 1) {
                        values.value.push(
                            frontAppSetting.value.pages_widget[i]
                        );
                    }
                }
            }
        });

        const loginModalClosed = () => {
            loginModalVisible.value = false;
        };

        const filterOption = (input, option) => {
            return option.value.toUpperCase().indexOf(input.toUpperCase()) >= 0;
        };

        const onSearchSelect = (value) => {
            SearchFilterValue.value = value;
        };

        return {
            frontAppSetting,
            openLoginModal,
            loginModalClosed,
            loginModalVisible,
            SearchFilterValue,
            frontWarehouse,
            content,
            values,
            products,
            innerWidth: window.innerWidth,
            onSearchSelect,
            openKeys: ref([]),
            selectedKeys: ref([]),
            user,
            filterOption,
            allProducts,
        };
    },
});
</script>
<style lang="less" scoped>
.ant-carousel :deep(.slick-arrow.custom-slick-arrow) {
    width: 25px;
    height: 25px;
    font-size: 25px;
    color: #fff;
    background-color: rgba(31, 45, 61, 0.11);
    opacity: 0.3;
    z-index: 1;
}
.ant-carousel :deep(.custom-slick-arrow:before) {
    display: none;
}
.ant-carousel :deep(.custom-slick-arrow:hover) {
    opacity: 0.5;
}

.subheader {
    border-bottom: 1px solid #e5e7eb;

    .subheader-menu-lists {
        padding-top: 15px;
        padding-bottom: 15px;
    }

    .subheader-menu {
        font-size: 16px;
        font-weight: 500;
        color: rgba(0, 0, 0, 0.85);
    }
}

.top-dropdown-box {
    .ant-dropdown-content {
        margin-top: 50px;
    }
}

.no-online-store-container {
    height: 100%;
    width: 100%;
    display: flex;
    position: fixed;
    align-items: center;
    justify-content: center;
    background: #f8f8ff;
}
.ant-layout-header {
    height: 40px;
    padding: 0 50px;
    color: rgba(0, 0, 0, 0.85);
    line-height: 40px;
    background: #001529;
}

.front-header {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 10px 5px;
    background: #fff;
}

button.ant-btn.ant-btn-primary.ant-btn-lg.ant-input-search-button {
    background: #000 !important;
}
</style>
