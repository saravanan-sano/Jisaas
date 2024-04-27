import { ref } from "vue";
import { reactive } from "vue";
import common from "../../../../common/composable/common";
import { debounce } from "lodash-es";
import { useI18n } from "vue-i18n";

const fields = () => {
    const { t } = useI18n();
    const { formatAmount, orderType, orderPageObject, selectedWarehouse } =
        common();
    const state = reactive({
        orderSearchTerm: [],
        productFetching: false,
        products: [],
    });

    const formData = ref({
        order_type: "sales",
        warehouse_id:
            orderType.value == "stock-transfers"
                ? undefined
                : selectedWarehouse.value.xid,
    });

    const selectedProducts = ref([]);
    const selectedProductIds = ref([]);
    const removedOrderItemsIds = ref([]);

    const fetchProducts = debounce((value) => {
        state.products = [];

        if (value != "") {
            state.productFetching = true;
            let url = `search-product`;

            axiosAdmin
                .post(url, {
                    order_type: "sales",
                    search_term: value,
                    warehouse_id: formData.value.warehouse_id,
                    products: selectedProductIds.value,
                    userid: formData.value.user_id,
                })
                .then((response) => {
                    state.products = response.data;
                    state.productFetching = false;
                });
        }
    }, 300);

    const searchValueSelected = (value, option) => {
        const newProduct = option.product;
        selectedProductIds.value.push(newProduct.xid);

        selectedProducts.value.push({
            ...newProduct,
            sn: selectedProducts.value.length + 1,
            count: 1,
        });
        state.orderSearchTerm = [];
        state.products = [];
    };


    const showDeleteConfirm = (product) => {
        // Delete selected product and rearrange SN
        const newResults = [];
        let counter = 1;
        selectedProducts.value.map((selectedProduct) => {

            if (selectedProduct.item_id != null) {
                removedOrderItemsIds.value = [...removedOrderItemsIds.value, selectedProduct.item_id];
            }

            if (selectedProduct.xid != product.xid) {
                newResults.push({
                    ...selectedProduct,
                    sn: counter,
                    count: 1,
                });

                counter++;
            }
        });
        selectedProducts.value = newResults;

        // Remove deleted product id from lists
        const filterProductIdArray = selectedProductIds.value.filter((newId) => {
            return newId != product.xid;
        });
        selectedProductIds.value = filterProductIdArray;
    };

    const BarcodeColumns = [
        {
            title: "#",
            dataIndex: "sn",
        },
        {
            title: t("product.name"),
            dataIndex: "name",
        },
        {
            title: "Barcode",
            dataIndex: "barcode",
        },
        {
            title: "Count",
            dataIndex: "count",
        },
        {
            title: "Action",
            dataIndex: "action",
        },
    ];

    return {
        state,
        fetchProducts,
        selectedProducts,
        searchValueSelected,
        BarcodeColumns,
        showDeleteConfirm
    };
};

export default fields;
