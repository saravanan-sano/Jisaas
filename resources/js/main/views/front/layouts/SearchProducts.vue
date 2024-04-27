<template>
    <a-row
        v-if="loading"
        type="flex"
        justify="center"
        style="min-height: 50vh; align-items: center"
    >
        <a-spin size="large" />
    </a-row>
    <a-row
        v-else-if="
            !loading &&
            filteredProducts.length == 0 &&
            props.SearchFilterValue.length >= 2
        "
        justify="center"
        style="min-height: 50vh; align-items: center"
    >
        <a-typography-title>
            Can't Able To Find The Product "{{ props.SearchFilterValue }}"
        </a-typography-title>
    </a-row>
    <a-row v-else type="flex" justify="center">
        <a-col :span="20">
            <div class="prdoct-card-list-body mt-20 mb-50">
                <a-row :gutter="[30, 30]">
                    <a-col
                        v-for="product in filteredProducts"
                        :xs="24"
                        :sm="12"
                        :md="8"
                        :lg="6"
                        :xl="4"
                        :key="product.id"
                    >
                        <ProductCard :product="product" :key="product.id" />
                    </a-col>
                </a-row>
            </div>
        </a-col>
    </a-row>

    {{ watchSearchValue }}
</template>
<script>
import { computed, defineComponent, onMounted, ref } from "vue";
import common from "../../../../common/composable/common";
import Fuse from "fuse.js";
import ProductCard from "../components/ProductCard.vue";
import { useRoute } from "vue-router";
export default defineComponent({
    components: {
        ProductCard,
    },
    props: ["SearchFilterValue", "products"],
    setup(props) {
        const { frontWarehouse } = common();
        const filteredProducts = ref([]);
        const loading = ref(false);
        const route = useRoute();

        const watchSearchValue = computed(() => {
            if (props.SearchFilterValue) {
                filteredProducts.value = [];
                getData();
            }
        });

        const getData = () => {
            loading.value = true;
            const fuseOptions = {
                keys: ["name", "slug", "category.name", "category.slug"],
                threshold: 0.3, // Adjust this threshold as needed
            };

            const fuse = new Fuse(props.products, fuseOptions);
            setTimeout(() => {
                filteredProducts.value = fuse
                    .search(
                        props.SearchFilterValue == ""
                            ? route.query.value
                            : props.SearchFilterValue
                    )
                    .map((result) => result.item);
                loading.value = false;
            }, 2000);
        };
        onMounted(() => {
            getData();
            props.SearchFilterValue == ""
                ? (props.SearchFilterValue = route.query.value)
                : props.SearchFilterValue;
        });

        return {
            props,
            watchSearchValue,
            filteredProducts,
            loading,
        };
    },
});
</script>
