import { ref } from "vue";
import { useI18n } from "vue-i18n";
import { useRoute } from "vue-router";

const fields = () => {
    const { t } = useI18n();
    const staffMemberAddEditUrl = "users";
    const customerAddEditUrl = "customers";
    const DeliveryPartnerAddEditUrl = "users";
    const supplierAddEditUrl = "suppliers";
    const referralAddEditUrl = "referral";
    const route = useRoute();
    const userType = ref(route.meta.menuKey);

    const commonInitData = {
        warehouse_id: undefined,
        name: "",
        email: "",
        profile_image: undefined,
        profile_image_url: undefined,
        phone: "",
        address: "",
        status: "enabled",
        tax_number: "",
        business_type: "",
        location: "",
        login_access: 0,
    };

    const customerSupplierInitData = {
        shipping_address: "",
        opening_balance: "",
        opening_balance_type: "receive",
        credit_period: "30",
        credit_limit: "",
        tax_number: undefined,
    };

    const customerInitData = {
        ...commonInitData,
        ...customerSupplierInitData,
        pincode: "",
        business_type: "",
        location: "",
        user_type: "customers",
        is_wholesale_customer: false,
        assign_to: undefined,
        password: "",
    };

    const staffMemberInitData = {
        ...commonInitData,
        pincode: "",
        business_type: "",
        location: "",
        user_type: "staff_members",
        role_id: undefined,
        accounttype: 0,
        login_access: 0,
        password: "",
    };

    const DeliveryPartnerInitData = {
        ...commonInitData,
        pincode: "",
        business_type: "",
        location: "",
        user_type: "delivery_partner",
        role_id: undefined,
        accounttype: 0,
        password: "",
    };

    const referralInitData = {
        ...commonInitData,
        pincode: "",
        business_type: "",
        location: "",
        user_type: "referral",
        role_id: undefined,
        password: "",
    };

    const supplierInitData = {
        ...commonInitData,
        ...customerSupplierInitData,
        pincode: "",
        business_type: "",
        location: "",
        user_type: "suppliers",
    };

    const UserInitData = {
        warehouse_id: undefined,
        name: "",
        email: "",
        profile_image: undefined,
        profile_image_url: undefined,
        phone: "",
        address: "",
        status: "enabled",
        tax_number: "",
        shipping_address: "",
        opening_balance: "",
        opening_balance_type: "receive",
        credit_period: "30",
        credit_limit: "",
        tax_number: undefined,
        user_type: "referral",
        role_id: undefined,
        password: "",
        pincode: "",
        business_type: "",
        location: "",
        user_type: "",
        accounttype: 0,
        login_access: 0,
        is_wholesale_customer: false,
        assign_to: undefined,
    };

    const columns = [
        {
            title: t("user.name"),
            dataIndex: "name",
            key: "name",
        },
        {
            title: t("user.email"),
            dataIndex: "email",
        },
        {
            title: t("user.created_at"),
            dataIndex: "created_at",
        },
        {
            title: t("user.status"),
            dataIndex: "status",
            key: "status",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const supplierCustomerColumns = [
        {
            title: t("user.name"),
            dataIndex: "name",
            key: "name",
        },
        {
            title: t("user.email"),
            dataIndex: "email",
        },
        {
            title: t("user.created_at"),
            dataIndex: "created_at",
        },
        ...(userType.value === "customers"
            ? [
                {
                    title: "Assign To",
                    dataIndex: "assign_to",
                },
              ]
            : []),

        {
            title: t("common.balance"),
            dataIndex: "balance",
        },
        {
            title: t("user.status"),
            dataIndex: "status",
            key: "status",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];
    const StaffColumns = [
        {
            title: t("user.name"),
            dataIndex: "name",
            key: "name",
        },
        {
            title: t("user.role"),
            dataIndex: "role",
        },
        {
            title: t("user.email"),
            dataIndex: "email",
        },
        {
            title: t("user.created_at"),
            dataIndex: "created_at",
        },
        {
            title: t("user.status"),
            dataIndex: "status",
            key: "status",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const filterableColumns = [
        {
            key: "name",
            value: t("user.name"),
        },
        {
            key: "email",
            value: t("user.email"),
        },
        {
            key: "phone",
            value: t("user.phone"),
        },
    ];

    return {
        customerInitData,
        staffMemberInitData,
        supplierInitData,
        DeliveryPartnerInitData,
        columns,
        supplierCustomerColumns,
        filterableColumns,
        staffMemberAddEditUrl,
        customerAddEditUrl,
        DeliveryPartnerAddEditUrl,
        supplierAddEditUrl,
        referralInitData,
        referralAddEditUrl,
        UserInitData,
        StaffColumns,
    };
};

export default fields;
