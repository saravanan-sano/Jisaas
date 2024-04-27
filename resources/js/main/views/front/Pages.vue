<template>
    <div class="bg-white">
        <a-row
            type="flex"
            justify="center"
            class="front-skeleton"
            v-if="loading"
        >
            <a-col :span="20">
                <a-row
                    type="flex"
                    justify="center"
                    :gutter="[20, 30]"
                    class="mt-20"
                >
                    <a-skeleton-input
                        class="carousel-skeleton"
                        size="large"
                        style="width: 100%"
                        :active="true"
                        shape="round"
                    />
                    <a-divider dashed />
                    <a-skeleton-input
                        size="small"
                        style="width: 30%"
                        :active="true"
                        shape="round"
                    />
                    <a-divider dashed />
                    <a-skeleton size="large" />
                    <a-divider dashed />
                    <a-skeleton size="large" />
                </a-row>
            </a-col>
        </a-row>
        <a-row v-else type="flex" justify="center">
            <a-col :span="20">
                <div class="ql-editor">
                    <div v-html="content"></div>
                </div>
            </a-col>
        </a-row>
    </div>
</template>
<script>
import { defineComponent, ref, onMounted, watch } from "vue";
import {
    RightOutlined,
    RightCircleOutlined,
    LeftCircleOutlined,
} from "@ant-design/icons-vue";
import { useRoute } from "vue-router";
import ProductCard from "./components/ProductCard.vue";
import CategoryHeader from "./includes/CategoryHeader.vue";
import common from "../../../common/composable/common";

export default defineComponent({
    components: {
        RightOutlined,
        RightCircleOutlined,
        LeftCircleOutlined,
        ProductCard,
        CategoryHeader,
    },
    setup() {
        const { frontWarehouse, frontAppSetting } = common();
        const route = useRoute();
        const frontSettings = ref({});
        const frontProductCards = ref([]);
        const featuredProducts = ref([]);
        const content = ref("Loading");

        const loading = ref(false);

        const params = route.params;
        const uniqueId = params.uniqueId;

        loading.value = true;

        for (let i = 0; i < frontAppSetting.value.pages_widget.length; i++) {
            if (frontAppSetting.value.pages_widget[i].value == uniqueId) {
                content.value = frontAppSetting.value.pages_widget[i].content;
            }
        }

        onMounted(() => {
            axiosFront
                .get(`front/homepage/${frontWarehouse.value.slug}`)
                .then((response) => {
                    frontSettings.value = response.data.front_settings;
                    frontProductCards.value = response.data.front_product_cards;
                    featuredProducts.value =
                        frontSettings.value.featured_products_details;

                    loading.value = false;
                });
        });

        return {
            frontSettings,
            loading,
            frontProductCards,
            featuredProducts,
            frontAppSetting,
            content,
        };
    },
});
</script>

<style lang="less">
.prdoct-card-list {
    margin-bottom: 20px;
    margin-top: 20px;
    border-radius: 10px;
}

.prdoct-card-list-body {
    padding: 10px 0px 20px 0px;
}

.featured-categories .ant-list-item-meta-title {
    margin-top: 6px;
}
.ant-divider-dashed {
    background: none;
    border-color: rgba(0, 0, 0, 0.2);
    border-style: dashed;
    border-width: 1px 0 0;
}
.front-skeleton .carousel-skeleton .ant-skeleton-input.ant-skeleton-input-lg {
    width: 100% !important;
    height: 72vh !important;
}
.product-top img {
    height: 100% !important;
    max-width: 100%;
    width: 100%;
    -o-object-fit: cover;
    object-fit: cover;
}
</style>
