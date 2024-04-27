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
                <a-row :gutter="[20, 30]" class="mt-20">
                    <a-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <a-carousel autoplay>
                            <div
                                v-for="item in frontSettings.bottom_banners_1_details"
                                :key="item.uid"
                            >
                                <img
                                    :src="item.url"
                                    loading="lazy"
                                    :style="{ width: '100%' }"
                                />
                            </div>
                        </a-carousel>
                    </a-col>
                    <a-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <a-row :gutter="[20, 20]">
                            <a-col :span="12">
                                <a-carousel autoplay>
                                    <div
                                        v-for="item in frontSettings.bottom_banners_2_details"
                                        :key="item.uid"
                                    >
                                        <img
                                            loading="lazy"
                                            :src="item.url"
                                            :style="{ width: '100%' }"
                                        />
                                    </div>
                                </a-carousel>
                            </a-col>
                            <a-col :span="12">
                                <div
                                    v-for="item in frontSettings.bottom_banners_3_details"
                                    :key="item.uid"
                                >
                                    <img
                                        :src="item.url"
                                        loading="lazy"
                                        :style="{ width: '100%' }"
                                    />
                                </div>
                            </a-col>
                        </a-row>
                    </a-col>
                </a-row>

                <a-divider
                    v-if="
                        (frontSettings.bottom_banners_1_details &&
                            frontSettings.bottom_banners_1_details.length >
                                0) ||
                        (frontSettings.bottom_banners_2_details &&
                            frontSettings.bottom_banners_2_details.length >
                                0) ||
                        (frontSettings.bottom_banners_3_details &&
                            frontSettings.bottom_banners_3_details.length > 0)
                    "
                    dashed
                />

                <div
                    v-if="
                        frontSettings.featured_products_details &&
                        frontSettings.featured_products_details.length > 0
                    "
                >
                    <div style="text-align: center" class="mt-20 mb-20">
                        <a-typography-title
                            :level="3"
                            :style="{ marginBottom: '5px' }"
                        >
                            {{ frontSettings.featured_products_title }}
                        </a-typography-title>
                        <a-typography-title
                            v-if="frontSettings.featured_products_subtitle"
                            type="secondary"
                            :level="5"
                            :style="{ marginTop: '0px' }"
                        >
                            {{ frontSettings.featured_products_subtitle }}
                        </a-typography-title>
                    </div>

                    <div class="prdoct-card-list-body mt-20 mb-50">
                        <a-row :gutter="[30, 30]">
                            <a-col
                                v-for="product in featuredProducts"
                                :xs="24"
                                :sm="12"
                                :md="8"
                                :lg="6"
                                :xl="4"
                                :key="product.id"
                            >
                                <ProductCard
                                    :product="product"
                                    :key="product.id"
                                />
                            </a-col>
                        </a-row>
                    </div>
                    <a-divider dashed />
                </div>

                <!-- Featured categories -->
                <!-- <div
                    v-if="
                        frontSettings.featured_categories_details &&
                        frontSettings.featured_categories_details.length > 0
                    "
                >
                    <div style="text-align: center" class="mt-20 mb-20">
                        <a-typography-title
                            :level="3"
                            :style="{ marginBottom: '5px' }"
                        >
                            {{ frontSettings.featured_categories_title }}
                        </a-typography-title>
                        <a-typography-title
                            v-if="frontSettings.featured_categories_subtitle"
                            type="secondary"
                            :level="5"
                            :style="{ marginTop: '0px' }"
                        >
                            {{ frontSettings.featured_categories_subtitle }}
                        </a-typography-title>
                    </div>
                    <a-row
                        :gutter="[10, 10]"
                        class="mt-20 mb-50 featured-categories"
                    >
                        <a-col
                            :xs="24"
                            :sm="12"
                            :md="8"
                            :lg="4"
                            :xl="4"
                            v-for="featuredCategory in frontSettings.featured_categories_details"
                            :key="featuredCategory.id"
                        >
                            <div
                                style="
                                    padding: 0px 10px 0px 10px;
                                    border-radius: 10px;
                                    border: 1px solid #eee;
                                "
                            >
                                <a-list
                                    item-layout="horizontal"
                                    :data-source="[featuredCategory]"
                                >
                                    <template #renderItem="{ item }">
                                        <a-list-item>
                                            <a-list-item-meta>
                                                <template #title>
                                                    {{ item.name }}
                                                </template>
                                                <template #avatar>
                                                    <a-avatar
                                                        :src="item.image_url"
                                                    />
                                                </template>
                                            </a-list-item-meta>
                                        </a-list-item>
                                    </template>
                                </a-list>
                            </div>
                        </a-col>
                    </a-row>
                    <a-divider dashed />
                </div> -->

                <!-- All categories Filter Option -->
                <div v-if="frontCategories && frontCategories.length > 0">
                    <a-row
                        :gutter="[10, 10]"
                        class="mt-20 mb-50 featured-categories"
                    >
                        <a-radio-group
                            v-model:value="SelectedCategories"
                            button-style="solid"
                            style="
                                padding: 10px;
                                border-radius: 10px;
                                border: 1px solid #eee;
                                width: 100%;
                                display: flex;
                                gap: 1rem;
                                justify-content: flex-start;
                                align-items: center;
                                flex-wrap: wrap;
                            "
                        >
                            <a-radio-button value="all"> All </a-radio-button>
                            <a-radio-button
                                v-for="(
                                    frontCategorie, index
                                ) in frontCategories"
                                :key="index + 1"
                                :value="frontCategorie"
                                >{{ frontCategorie }}</a-radio-button
                            >
                        </a-radio-group>
                    </a-row>
                    <a-divider dashed />
                </div>

                <div v-if="frontProductCards && frontProductCards.length > 1">
                    <div
                        v-for="frontProductCard in frontProductCards"
                        :key="frontProductCard.id"
                        class="prdoct-card-list"
                    >
                        <div
                            v-if="
                                frontProductCard.title == SelectedCategories ||
                                SelectedCategories == 'all'
                            "
                            style="text-align: center"
                            class="mt-20 mb-20"
                        >
                            <a-typography-title
                                :level="3"
                                :style="{ marginBottom: '5px' }"
                            >
                                {{ frontProductCard.title }}
                            </a-typography-title>
                            <a-typography-title
                                v-if="frontProductCard.subtitle != ''"
                                type="secondary"
                                :level="5"
                                :style="{ marginTop: '0px' }"
                            >
                                {{ frontProductCard.subtitle }}
                            </a-typography-title>
                        </div>

                        <div
                            v-if="
                                frontProductCard.title == SelectedCategories ||
                                SelectedCategories == 'all'
                            "
                            class="prdoct-card-list-body mt-20 mb-50"
                        >
                            <a-row :gutter="[30, 30]">
                                <a-col
                                    v-for="product in frontProductCard.products_details"
                                    :xs="24"
                                    :sm="12"
                                    :md="8"
                                    :lg="8"
                                    :xl="6"
                                    :key="product.id"
                                >
                                    <ProductCard
                                        :product="product"
                                        :key="product.xid"
                                    />
                                </a-col>
                            </a-row>
                        </div>
                        <a-divider
                            dashed
                            v-if="
                                frontProductCard.title == SelectedCategories ||
                                SelectedCategories == 'all'
                            "
                        />
                    </div>
                </div>

                <div v-if="frontProductCards && frontProductCards.length == 1">
                    <div
                        v-for="frontProductCard in frontProductCards"
                        :key="frontProductCard.id"
                        class="prdoct-card-list"
                    >

                        <div class="prdoct-card-list-body mt-20 mb-50">
                            <a-row :gutter="[30, 30]">
                                <a-col
                                    v-for="product in frontProductCard.products_details"
                                    :xs="24"
                                    :sm="12"
                                    :md="6"
                                    :lg="6"
                                    :xl="6"
                                    :key="product.id"
                                ></a-col>
                                <a-col
                                    v-for="product in frontProductCard.products_details"
                                    :xs="24"
                                    :sm="12"
                                    :md="12"
                                    :lg="12"
                                    :xl="12"
                                    :key="product.id"
                                >
                                    <ProductCard
                                        :product="product"
                                        :key="product.xid"
                                    />
                                </a-col>
                            </a-row>
                        </div>
                        <a-divider dashed />
                    </div>
                </div>

                <a-row class="mt-20 mb-20">
                    <a-col :span="24">
                        <a-carousel arrows>
                            <template #prevArrow>
                                <div
                                    class="custom-slick-arrow"
                                    style="left: 10px; zindex: 1"
                                >
                                    <left-circle-outlined />
                                </div>
                            </template>
                            <template #nextArrow>
                                <div
                                    class="custom-slick-arrow"
                                    style="right: 10px"
                                >
                                    <RightCircleOutlined />
                                </div>
                            </template>
                            <div
                                v-for="item in frontSettings.top_banners_details"
                                :key="item.uid"
                            >
                                <img
                                    :src="item.url"
                                    loading="lazy"
                                    :style="{ width: '100%' }"
                                />
                            </div>
                        </a-carousel>
                    </a-col>
                </a-row>
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
        const { frontWarehouse } = common();
        const route = useRoute();
        const frontSettings = ref({});
        const frontProductCards = ref([]);
        const featuredProducts = ref([]);
        const frontCategories = ref([]);
        const SelectedCategories = ref("all");

        const loading = ref(false);

        onMounted(() => {
            loading.value = true;
            axiosFront
                .get(`front/homepage/${frontWarehouse.value.slug}`)
                .then((response) => {
                    frontSettings.value = response.data.front_settings;
                    frontProductCards.value = response.data.front_product_cards;
                    featuredProducts.value =
                        frontSettings.value.featured_products_details;
                    response.data.front_product_cards.map((item) => {
                        return frontCategories.value.push(item.title);
                    });
                    loading.value = false;
                });
        });

        return {
            frontSettings,
            loading,
            frontProductCards,
            featuredProducts,
            frontCategories,
            SelectedCategories,
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
