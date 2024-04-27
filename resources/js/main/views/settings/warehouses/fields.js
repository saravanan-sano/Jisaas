import { ref } from "vue";
import { useI18n } from "vue-i18n";

const fields = () => {
    const addEditUrl = "warehouses";
    const url =
        "warehouses?fields=id,xid,logo,logo_url,gst_in_no,qr_code,upi_id,dark_logo,dark_logo_url,name,slug,email,phone,address,show_email_on_invoice,show_phone_on_invoice,show_mrp_on_invoice,show_discount_tax_on_invoice,is_last_history,is_staff_base,mobile_print_invoice,is_app_online,is_billing,is_negative_stock,is_card_gateway,square_access_key,cardpayment_method,upi_gateway,paytm_gateway,terms_condition,bank_details,signature,signature_url,online_store_enabled,default_pos_order_status,customers_visibility,is_login_document,ecom_visibility,suppliers_visibility,products_visibility,prefix_invoice,invoice_spliter,reset_invoice,suffix_invoice,first_invoice_no,set_pos_type,product_tax_type,pincode,location,business_type,default_invoice";
    const { t } = useI18n();

    const initData = {
        name: "",
        slug: "",
        email: "",
        phone: "",
        logo: undefined,
        logo_url: undefined,
        dark_logo: undefined,
        dark_logo_url: undefined,
        show_email_on_invoice: 0,
        show_phone_on_invoice: 0,
        show_mrp_on_invoice: 1,
        show_discount_tax_on_invoice: 1,
        is_last_history: 0,
        is_card_gateway: 0,
        square_access_key: null,
        cardpayment_method: null,
        address: "",
        terms_condition: `1. Goods once sold will not be taken back or exchanged
        2. All disputes are subject to [ENTER_YOUR_CITY_NAME] jurisdiction only`,
        bank_details: "",
        signature: undefined,
        signature_url: undefined,
        default_pos_order_status: "delivered",
        set_pos_type: 1,
        prefix_invoice: "PRE",
        invoice_spliter: "-",
        reset_invoice: "January",
        suffix_invoice: "",
        first_invoice_no: 0,
        customers_visibility: "all",
        suppliers_visibility: "all",
        products_visibility: "all",
        is_login_document: 0,
        ecom_visibility: 0,
        qr_code: undefined,
        qr_code_url: undefined,
        upi_id: "",
        gst_in_no: "",
        is_staff_base: 0,
        is_billing: 1,
        is_negative_stock: 1,
        upi_gateway: 0,
        paytm_gateway: 0,
        mobile_print_invoice: 0,
        is_app_online: 1,
        product_tax_type: "inclusive",
        pincode: [],
        location: [],
        business_type: [],
        default_invoice: "a4_invoice",
    };

    const columns = [
        {
            title: t("warehouse.logo"),
            dataIndex: "logo",
        },
        {
            title: t("warehouse.name"),
            dataIndex: "name",
            sorter: true,
        },
        {
            title: t("warehouse.email"),
            dataIndex: "email",
            sorter: true,
        },
        {
            title: t("warehouse.phone"),
            dataIndex: "phone",
        },
        {
            title: t("warehouse.online_store"),
            dataIndex: "online_store_enabled",
        },

        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const filterableColumns = [
        {
            key: "name",
            value: t("warehouse.name"),
        },
        {
            key: "email",
            value: t("warehouse.email"),
        },
        {
            key: "phone",
            value: t("warehouse.phone"),
        },
    ];

    return {
        url,
        addEditUrl,
        initData,
        columns,
        filterableColumns,
    };
};

export default fields;
