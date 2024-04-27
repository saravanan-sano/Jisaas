import { ref } from "vue";
import { useI18n } from "vue-i18n";
import { useStore } from "vuex";
import { find } from "lodash-es";
import common from "../../../../common/composable/common";

const fields = () => {
    const { t } = useI18n();
    const store = useStore();
    const taxes = ref([]);
    const warehouses = ref([]);
    const customers = ref([]);
    const brands = ref([]);
    const categories = ref([]);
    const productLists = ref([]);
    const posDefaultCustomer = ref({});
    const { selectedWarehouse, user, permsArray, filterCustomers } = common();

    const formData = ref({
        user_id: undefined,
        tax_id: undefined,
        category_id: undefined,
        brand_id: undefined,
        tax_id: undefined,
        tax_rate: 0,
        tax_amount: 0,
        discount_type: "percentage",
        discount_value: 0,
        discount: 0,
        shipping: 0,
        subtotal: 0,
        payment_mode_id: undefined,
        amount: 0,
        notes: "",
        invoice_number: "",
        mode: 0,
        square_device_id: undefined,
    });

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
            title: t("product.quantity"),
            dataIndex: "unit_quantity",
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

    const DraftColumns = [
        {
            title: "Name",
            dataIndex: "name",
        },
        {
            title: "Send To Order",
            dataIndex: "action",
        },
    ];

    const orderItemColumns1 = [
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
            title: t("product.price"),
            dataIndex: "unit_price",
        },
        {
            title: t("product.discount"),
            dataIndex: "discount_rate",
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

    const customerUrl = `customers?fields=id,xid,user_type,name,email,profile_image,profile_image_url,assign_to,tax_number,is_walkin_customer,phone,address,shipping_address,status,is_wholesale_customer,created_at,details{opening_balance,opening_balance_type,credit_period,credit_limit,due_amount,warehouse_id,x_warehouse_id},details:warehouse{id,xid,name},role_id,role{id,xid,name,display_name},warehouse_id,x_warehouse_id,warehouse{xid,name}${filterCustomers}&limit=10000`;

    const getPreFetchData = () => {
        const taxesPromise = axiosAdmin.get("taxes?limit=10000");
        const customersPromise = axiosAdmin.get(customerUrl);
        const categoriesPromise = axiosAdmin.get("categories?limit=10000");
        const brandsPromise = axiosAdmin.get("brands?limit=10000");
        const productsPromise = axiosAdmin.post("pos/products", {
            brand_id: formData.value.brand_id,
            category_id: formData.value.category_id,
        });
        const defaultWalkinCustomerPromise = axiosAdmin.get(
            "default-walkin-customer"
        );

        Promise.all([
            taxesPromise,
            customersPromise,
            productsPromise,
            categoriesPromise,
            brandsPromise,
            defaultWalkinCustomerPromise,
        ]).then(
            ([
                taxesResponse,
                customersResponse,
                productResponse,
                caegoriesResponse,
                brandsResponse,
                defaultWalkinCustomerResponse,
            ]) => {
                taxes.value = taxesResponse.data;
                //! Removed By saravanan for Staff Based Customer.
                customers.value = customersResponse.data;
                categories.value = caegoriesResponse.data;
                brands.value = brandsResponse.data;
                productLists.value = productResponse.data.products;

                var defaultWalkInCustomer = find(customers.value, [
                    "xid",
                    defaultWalkinCustomerResponse.data.customer.xid,
                ]);
                if (defaultWalkInCustomer) {
                    posDefaultCustomer.value = defaultWalkInCustomer;
                    formData.value = {
                        ...formData.value,
                        user_id: defaultWalkInCustomer.xid,
                    };
                }
            }
        );
    };

    return {
        taxes,
        customers,
        brands,
        categories,
        productLists,
        formData,

        customerUrl,

        orderItemColumns,
        orderItemColumns1,
        getPreFetchData,
        posDefaultCustomer,
        DraftColumns,
    };
};

export default fields;
