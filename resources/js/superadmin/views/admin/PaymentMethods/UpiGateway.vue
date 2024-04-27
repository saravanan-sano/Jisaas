<template>
    <template v-if="!qrcode">
        <a-form layout="vertical">
            <div class="mb-20">
                <a-alert v-if="errorText != ''" :message="errorText" type="error" />
            </div>

            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item label="Transation Amount" name="txnAmount"
                        :help="rules.txnAmount ? rules.txnAmount.message : null"
                        :validateStatus="rules.txnAmount ? 'error' : null" class="required">
                        <a-input v-model:value="planAmount" :placeholder="$t('common.placeholder_default_text', [
                            $t('payment_settings.stripe_customer_name'),
                        ])
                            " disabled />
                    </a-form-item>
                </a-col>
            </a-row>
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item label="Name" name="customerName"
                        :help="rules.customerName ? rules.customerName.message : null"
                        :validateStatus="rules.customerName ? 'error' : null" class="required">
                        <a-input v-model:value="formData.customerName" :placeholder="$t('common.placeholder_default_text', [
                            $t('payment_settings.stripe_customer_name'),
                        ])
                            " />
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item label="Mobile No:" name="customerMobile"
                        :help="rules.customerMobile ? rules.customerMobile.message : null"
                        :validateStatus="rules.customerMobile ? 'error' : null" class="required">
                        <a-input v-model:value="formData.customerMobile" :placeholder="$t('common.placeholder_default_text', [
                            $t('payment_settings.stripe_customer_name'),
                        ])
                            " />
                    </a-form-item>
                </a-col>
            </a-row>
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item label="E-Mail:" name="customerEmail"
                        :help="rules.customerEmail ? rules.customerEmail.message : null"
                        :validateStatus="rules.customerEmail ? 'error' : null" class="required">
                        <a-input v-model:value="formData.customerEmail" :placeholder="$t('common.placeholder_default_text', [
                            $t('payment_settings.stripe_customer_name'),
                        ])
                            " />
                    </a-form-item>
                </a-col>
            </a-row>
            <a-row :gutter="16" class="mt-20">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-button type="primary" @click="pay" :loading="loading" block>
                        Pay
                    </a-button>
                </a-col>
            </a-row>
        </a-form>
    </template>
    <template v-else>
       <iframe :src="qrvalue" style="width: 100%;height: 600px;border: 0;"></iframe>
    </template>
</template>

<script>
import { useI18n } from "vue-i18n";
import { message } from "ant-design-vue";
import { useStore } from "vuex";
import common from "../../../../common/composable/common";
import axios from "axios";
import { ref } from "vue";

export default {
    props: ["paymentMethod", "subscribePlan", "planType"],
    components: {
    },
    setup(props, { emit }) {
        const { t } = useI18n();
        const store = useStore();
        const { appSetting, countryCode } = common();
        const upiKey = ref(props.paymentMethod.credentials.upi_gateway_key);
        const planAmount = props.subscribePlan[`${props.planType}_price`];
        const qrcode = ref(false)
        const qrvalue = ref("");

        console.table(props.subscribePlan)




        const formData = ref({
            txnAmount: planAmount,
            customerName: "",
            customerMobile: "",
            customerEmail: "",
        });

        const rules = ref({});
        const loading = ref(false);
        const errorText = ref("");

        const pay = () => {
            loading.value = true;
            var formError = false;
            rules.value = {};
            errorText.value = "";
            if (formData.value.customerName == "") {
                rules.value = {
                    ...rules.value,
                    customerName: {
                        message: t("payment_settings.required_message", [
                            t("payment_settings.stripe_customer_name"),
                        ]),
                    },
                };

                loading.value = false;
                formError = true;
            }
            if (formData.value.customerMobile == "") {
                rules.value = {
                    ...rules.value,
                    customerMobile: {
                        message: "Mobile No is required"

                    },
                };

                loading.value = false;
                formError = true;
            }
            if (formData.value.customerEmail == "") {
                rules.value = {
                    ...rules.value,
                    customerEmail: {
                        message: "Email Id is required"
                    },
                };

                loading.value = false;
                formError = true;
            }


            if (!formError) {
                axiosAdmin.post("upi-qrcode", {
                    txnAmount: planAmount,
                    customerName: formData.value.customerName,
                    customerMobile: formData.value.customerMobile,
                    customerEmail: formData.value.customerEmail,
                    plan_Id: props.subscribePlan.xid,
                }).then((res) => (

                    qrvalue.value = res.data[0].payment_url,
                    qrcode.value = true
                ));
            }

        }



        return {
            planAmount,
            formData,
            pay,
            loading,
            errorText,
            rules,
            qrcode,
            qrvalue
        };
    },
};
</script>

<style lang="less" scoped>
.loading-app-container {
    height: 100%;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
