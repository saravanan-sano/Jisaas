import { useI18n } from "vue-i18n";
const fields = () => {
    const addEditUrl = "addcoupon";
    const { t } = useI18n();

    const initData = {
        coupon_code: "",
        discount_type: "percentage",
        discount_amount: "0",
        start_date: "",
        end_date: "",
        status: "0",
        // Optional
        // minimum_spend: "",
        // maximum_spend: "",
        // use_limit: "",
        // use_same_ip_limit: "",
        // user_limit: "",
        // use_device: "",
        // multiple_use: "",
        // vendor_id: "",
        // coupon_style: "",
    };

    const filterableColumns = [
        {
            key: "name",
            value: "coupon.coupon_code",
        },
    ];
    const DummyJson = [
        {
            coupon: {
                coupon_code: "ABCD12",
                discount_type: "percentage",
                discount_amount: "10",
                start_date: "12-12-2023",
                end_date: "28-12-2023",
                status: "1",
                minimum_spend: "100",
                maximum_spend: "1000",
                use_limit: "100",
                use_same_ip_limit: "10",
                user_limit: "1",
                use_device: "",
                multiple_use: "",
                vendor_id: "",
            },
            coupon_style: "",
        },
    ];

    return { DummyJson, addEditUrl, initData, filterableColumns };
};

export default fields;
