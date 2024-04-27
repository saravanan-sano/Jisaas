import { ref, computed, onMounted } from "vue";
import { useStore } from "vuex";
import { useI18n } from "vue-i18n";
import { useRoute } from "vue-router";
import common from "../../../../common/composable/common";

const fields = () => {
    const store = useStore();
    const { t } = useI18n();
    const { selectedWarehouse } = common();
    const route = useRoute();
    const orderType = ref(route.meta.orderType);
    const columns = ref([]);
    const hashableColumns = [
        "user_id",
        "warehouse_id",
        "staff_id",
        "referral_id",
    ];

    onMounted(() => {
        if (route.meta && route.meta.orderType) {
            orderType.value = route.meta.orderType;
        } else {
            orderType.value = "online-orders";
        }
    });

    const initData = {
        order_date: undefined,
        user_id: undefined,
        staff_id: undefined,
        referral_id: undefined,
        notes: "",
        order_status: undefined,
        tax_id: undefined,
        warehouse_id: undefined,
        discount: 0,
        shipping: 0,
        subtotal: 0,
        payment_mode_id: "",

        delivery_to: "",
        place_of_supply: "",
        reverse_charge: "",
        gr_rr_no: "",
        transport: "",
        vechile_no: "",
        station: "",
        buyer_order_no: "",
        discount_type: "percentage",
        discount_value: 0,
    };

    const initPaymentData = {
        date: undefined,
        payment_mode_id: undefined,
        amount: "",
        notes: "",
    };

    const orderItemColumns = [
        {
            title: "#",
            dataIndex: "sn",
            defaultSortOrder: "descend",
            sorter: (a, b) => a.sn - b.sn,
        },
        {
            title: t("product.name"),
            dataIndex: "name",
        },
        {
            title: t("product.identity_code"),
            dataIndex: "identity_code",
        },
        {
            title: t("product.quantity"),
            dataIndex: "unit_quantity",
        },
        ...(selectedWarehouse.value.show_mrp_on_invoice == 1 ? [{
            title: t("product.mrp"),
            dataIndex: "mrp",
        }] : []),
        {
            title: t("product.unit_price"),
            dataIndex: "single_unit_price",
        },
        {
            title: t("product.discount"),
            dataIndex: "total_discount",
        },
        {
            title: t("product.tax"),
            dataIndex: "total_tax",
        },
        {
            title: t("product.subtotal"),
            dataIndex: "subtotal",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const purchaseItemColumns = [
        {
            title: "#",
            dataIndex: "sn",
            defaultSortOrder: "descend",
            sorter: (a, b) => a.sn - b.sn,
        },
        {
            title: t("product.name"),
            dataIndex: "name",
        },
        {
            title: t("product.identity_code"),
            dataIndex: "identity_code",
        },
        {
            title: t("product.quantity"),
            dataIndex: "unit_quantity",
        },
        {
            title: t("product.unit_price"),
            dataIndex: "single_unit_price",
        },
        {
            title: t("product.sales_price"),
            dataIndex: "sales_price",
        },
        {
            title: t("product.discount"),
            dataIndex: "total_discount",
        },
        {
            title: t("product.tax"),
            dataIndex: "total_tax",
        },
        {
            title: t("product.subtotal"),
            dataIndex: "subtotal",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const orderReturnItemColumns = [
        {
            title: "#",
            dataIndex: "sn",
            defaultSortOrder: "descend",
            sorter: (a, b) => a.sn - b.sn,
        },
        {
            title: t("product.name"),
            dataIndex: "name",
        },
        {
            title: t("product.quantity"),
            dataIndex: "unit_quantity",
        },
        {
            title: t("product.unit_price"),
            dataIndex: "single_unit_price",
        },
        {
            title: t("product.discount"),
            dataIndex: "total_discount",
        },
        {
            title: t("product.tax"),
            dataIndex: "total_tax",
        },
        {
            title: t("product.subtotal"),
            dataIndex: "subtotal",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const orderItemDetailsColumns = [
        {
            title: t("product.product"),
            dataIndex: "product_id",
            defaultSortOrder: "descend",
            sorter: (a, b) => a.sn - b.sn,
        },
        {
            title: t("product.identity_code"),
            dataIndex: "identity_code",
        },
        {
            title: t("product.quantity"),
            dataIndex: "quantity",
        },
        {
            title: t("product.unit_price"),
            dataIndex: "single_unit_price",
        },
        {
            title: t("product.discount"),
            dataIndex: "total_discount",
        },
        {
            title: t("product.tax"),
            dataIndex: "total_tax",
        },
        {
            title: t("product.subtotal"),
            dataIndex: "subtotal",
        },
    ];

    const filterableColumns = [
        {
            key: "invoice_number",
            value: t("stock.invoice_number"),
        },
    ];

    const pageObject = computed(() => {
        var pageObjectDetails = {};

        if (orderType.value == "purchases") {
            pageObjectDetails = {
                type: "purchases",
                langKey: "purchase",
                menuKey: "purchases",
                userType: "suppliers",
                permission: "purchases",
            };
        } else if (orderType.value == "sales") {
            pageObjectDetails = {
                type: "sales",
                langKey: "sales",
                menuKey: "sales",
                userType: "customers",
                permission: "sales",
            };
        } else if (orderType.value == "purchase-returns") {
            pageObjectDetails = {
                type: "purchase-returns",
                langKey: "purchase_returns",
                menuKey: "purchase_returns",
                userType: "suppliers",
                permission: "purchase_returns",
            };
        } else if (orderType.value == "sales-returns") {
            pageObjectDetails = {
                type: "sales-returns",
                langKey: "sales_returns",
                menuKey: "sales_returns",
                userType: "customers",
                permission: "sales_returns",
            };
        } else if (orderType.value == "online-orders") {
            pageObjectDetails = {
                type: "online-orders",
                langKey: "online_orders",
                menuKey: "online_orders",
                userType: "customers",
                permission: "online_orders",
            };
        } else if (orderType.value == "quotations") {
            pageObjectDetails = {
                type: "quotations",
                langKey: "quotation",
                menuKey: "sales",
                userType: "customers",
                permission: "quotations",
            };
        } else if (orderType.value == "stock-transfers") {
            pageObjectDetails = {
                type: "stock-transfers",
                langKey: "stock_transfer",
                menuKey: "stock_transfer",
                userType: "customers",
                permission: "stock_transfers",
            };
        }

        return pageObjectDetails;
    });

    const setupTableColumns = () => {
        var allColumns = [
            {
                title: t(`stock.invoice_number`),
                dataIndex: "invoice_number",
            },
        ];

        if (pageObject.value.type == "stock-transfers") {
            allColumns.push({
                title: t("stock_transfer.warehouse"),
                dataIndex: "warehouse",
            });
        }

        allColumns.push({
            title: t(
                `${pageObject.value.langKey}.${pageObject.value.langKey}_date`
            ),
            dataIndex: "order_date",
        });

        if (pageObject.value.type != "stock-transfers") {
            allColumns.push({
                title: t(`${pageObject.value.langKey}.user`),
                dataIndex: "user_id",
            });
        }

        columns.value = [
            ...allColumns,
            {
                title: t(
                    `${pageObject.value.langKey}.${pageObject.value.langKey}_status`
                ),
                dataIndex: "order_status",
            },
            {
                title: t("payments.paid_amount"),
                dataIndex: "paid_amount",
            },
            {
                title: t("payments.total_amount"),
                dataIndex: "total_amount",
            },
            {
                title: t("payments.payment_status"),
                dataIndex: "payment_status",
            },
            {
                title: t("common.action"),
                dataIndex: "action",
            },
        ];
    };

    const orderPaymentsColumns = [
        {
            title: t("payments.date"),
            dataIndex: "date",
        },
        {
            title: t("payments.amount"),
            dataIndex: "amount",
        },
        {
            title: t("payments.payment_mode"),
            dataIndex: "payment_mode_id",
        },
    ];

    const salesDateColumns = [
        { title: "Invoice Number", dataIndex: "invoice_number" },
        { title: "Order Date", dataIndex: "order_date" },
        { title: "Customer Name", dataIndex: "customer_name" },
        { title: "Customer GR-RR/Tax No", dataIndex: "gr_rr_no" },
        { title: "Total Quantity", dataIndex: "total_quantity" },
        { title: "Tax Rate", dataIndex: "tax_rate" },
        { title: "Tax Amount", dataIndex: "tax_amount" },
        { title: "Discount", dataIndex: "discount" },
        { title: "Subtotal", dataIndex: "subtotal" },
        { title: "Total", dataIndex: "total" },
    ];

    const saleDataProduct = [
        { title: "Product Name", dataIndex: "name" },
        {
            title: "Purchase Price",
            dataIndex: "purchase_price",
        },
        { title: "Unit Price", dataIndex: "unit_price" },
        { title: "MRP", dataIndex: "mrp" },
        { title: "Quantity", dataIndex: "quantity" },
        { title: "Subtotal", dataIndex: "subtotal" },
    ];

    const defaultOptions = [
        { value: "invoice_number", label: "Invoice Number", is_required: true },
        { value: "order_date", label: "Order Date", is_required: true },
        { value: "customer_name", label: "Customer Name", is_required: true },
        { value: "tax_rate", label: "Order Tax Rate", is_required: true },
        { value: "tax_amount", label: "Order Tax Amount", is_required: true },
        { value: "discount", label: "Order Discount", is_required: true },
        { value: "subtotal", label: "Order Subtotal", is_required: true },
        { value: "total", label: "Order total", is_required: true },
        {
            value: "total_quantity",
            label: "Order Total Quantity",
            is_required: true,
        },
        {
            value: "gr_rr_no",
            label: "Customer GR-RR Number",
            is_required: true,
        },
        { value: "product", label: "Product Name", is_required: true },
        {
            value: "item_purchase_price",
            label: "Product Purchase Price",
            is_required: true,
        },
        {
            value: "item_unit_price",
            label: "Product Unit Price",
            is_required: true,
        },
        { value: "item_mrp", label: "Product MRP", is_required: true },
    ];

    return {
        initData,
        initPaymentData,
        columns,
        hashableColumns,
        setupTableColumns,
        filterableColumns,
        pageObject,
        orderType,
        orderItemColumns,
        orderReturnItemColumns,
        orderPaymentsColumns,
        orderItemDetailsColumns,
        purchaseItemColumns,
        salesDateColumns,
        saleDataProduct,
        defaultOptions,
    };
};

export default fields;
