<template>
  <AdminPageHeader>
    <template #header>
      <a-page-header :title="$t(`menu.payment_${paymentType}`)" class="p-0">
        <template
          v-if="
            permsArray.includes(
              paymentType == 'in' ? 'payment_in_create' : 'payment_out_create'
            ) || permsArray.includes('admin')
          "
          #extra
        >
          <a-button type="primary" @click="addItem" v-if="!showCollection">
            <PlusOutlined />
            {{ $t("payments.add") }}
          </a-button>
          <ExportPaymentInReport
            v-if="showCollection"
            :date="extraFilters.dates"
            :reportData="reportData"
            :staffMembers="staffs"
            :staffUserId="filters.staff_user_id"
          />
        </template>
      </a-page-header>
    </template>
    <template #breadcrumb>
      <a-breadcrumb separator="-" style="font-size: 12px">
        <a-breadcrumb-item>
          <router-link :to="{ name: 'admin.dashboard.index' }">
            {{ $t(`menu.dashboard`) }}
          </router-link>
        </a-breadcrumb-item>
        <a-breadcrumb-item>
          {{ $t(`menu.${menuParent}`) }}
        </a-breadcrumb-item>
        <a-breadcrumb-item>
          {{ $t(`menu.payment_${paymentType}`) }}
        </a-breadcrumb-item>
      </a-breadcrumb>
    </template>
  </AdminPageHeader>

  <a-card class="page-content-container">
    <AddEdit
      :addEditType="addEditType"
      :visible="addEditVisible"
      :url="addEditUrl"
      @addEditSuccess="addEditSuccess"
      @closed="onCloseAddEdit"
      :formData="formData"
      :data="viewData"
      :pageTitle="pageTitle"
      :successMessage="successMessage"
      :type="paymentType"
    />

    <a-row style="margin-bottom: 20px">
      <a-input-group>
        <a-row :gutter="8">
          <a-col :xs="24" :sm="24" :md="12" :lg="5" :xl="5">
            <a-input-group compact>
              <a-select
                style="width: 25%"
                v-model:value="table.searchColumn"
                :placeholder="$t('common.select_default_text', [''])"
                :disabled="showCollection"
              >
                <a-select-option
                  v-for="filterableColumn in filterableColumns"
                  :key="filterableColumn.key"
                >
                  {{ filterableColumn.value }}
                </a-select-option>
              </a-select>
              <a-input-search
                style="width: 75%"
                v-model:value="table.searchString"
                show-search
                @change="onTableSearch"
                @search="onTableSearch"
                :loading="table.filterLoading"
                :disabled="showCollection"
              />
            </a-input-group>
          </a-col>
          <a-col :xs="24" :sm="24" :md="12" :lg="5" :xl="5">
            <a-select
              v-model:value="filters.user_id"
              :placeholder="$t('common.select_default_text', [$t(`user.user`)])"
              :allowClear="true"
              style="width: 100%"
              optionFilterProp="title"
              show-search
              @change="setUrlData"
              :disabled="showCollection"
            >
              <a-select-option
                v-for="user in users"
                :key="user.xid"
                :title="user.name"
                :value="user.xid"
              >
                {{ user.name }}
              </a-select-option>
            </a-select>
          </a-col>
          <a-col :xs="24" :sm="24" :md="12" :lg="5" :xl="5">
            <a-select
              v-model:value="filters.staff_user_id"
              :placeholder="
                $t('common.select_default_text', [$t(`staff_member.staff`)])
              "
              :allowClear="true"
              style="width: 100%"
              optionFilterProp="title"
              show-search
              @change="setUrlData"
            >
              <a-select-option
                v-for="staff in staffs"
                :key="staff.xid"
                :title="staff.name"
                :value="staff.xid"
              >
                {{ staff.name }}
              </a-select-option>
            </a-select>
          </a-col>
          <a-col :xs="24" :sm="24" :md="12" :lg="4" :xl="4">
            <DateRangePicker
              :dateRange="extraFilters.dates"
              @dateTimeChanged="
                (changedDateTime) => {
                  changedDateTime.length > 0
                    ? (extraFilters.dates = [
                        dayjs(changedDateTime[0])
                          .startOf('day')
                          .add(1, 'day')
                          .format('YYYY-MM-DD'),
                        dayjs(changedDateTime[1])
                          .endOf('day')
                          .format('YYYY-MM-DD'),
                      ])
                    : (extraFilters.dates = undefined);
                  setUrlData();
                }
              "
            />
          </a-col>
          <a-col
            :xs="24"
            :sm="24"
            :md="12"
            :lg="4"
            :xl="4"
            v-if="paymentType == 'in'"
            style="
              display: flex;
              justify-content: flex-start;
              align-items: center;
            "
          >
            <a-checkbox v-model:checked="showCollection" @change="handleShow">
              Show Collection
            </a-checkbox>
          </a-col>
        </a-row>
      </a-input-group>
    </a-row>

    <a-row>
      <a-col :span="24">
        <div class="table-responsive">
          <a-table
            :columns="columns"
            :row-key="(record) => record.xid"
            :data-source="table.data"
            :pagination="table.pagination"
            :loading="table.loading"
            @change="handleTableChange"
            v-if="!showCollection"
          >
            <template #bodyCell="{ column, record }">
              <template v-if="column.dataIndex === 'user_id'">
                <UserInfo :user="record.user" />
              </template>
              <template v-if="column.dataIndex === 'staff_member'">
                <a-badge v-if="record.staff_member">
                  <a-avatar
                    :size="30"
                    :src="record.staff_member.profile_image_url"
                  />
                  {{ record.staff_member.name }}
                </a-badge>
                <a-badge v-else> </a-badge>
              </template>
              <template v-if="column.dataIndex === 'amount'">
                {{ formatAmountCurrency(record.amount) }}
              </template>
              <template v-if="column.dataIndex === 'action'">
                <a-button
                  v-if="
                    permsArray.includes(
                      paymentType == 'in'
                        ? 'payment_in_edit'
                        : 'payment_out_edit'
                    ) || permsArray.includes('admin')
                  "
                  type="primary"
                  @click="editItem(record)"
                  style="margin-left: 4px"
                >
                  <template #icon><EditOutlined /></template>
                </a-button>
                <a-button
                  v-if="
                    permsArray.includes(
                      paymentType == 'in'
                        ? 'payment_in_delete'
                        : 'payment_out_delete'
                    ) || permsArray.includes('admin')
                  "
                  type="primary"
                  @click="showDeleteConfirm(record.xid)"
                  style="margin-left: 4px"
                >
                  <template #icon><DeleteOutlined /></template>
                </a-button>
                <a-button
                  type="primary"
                  @click="showPaymentInvoice(record)"
                  style="margin-left: 4px"
                >
                  <template #icon><PrinterOutlined /></template>
                </a-button>
              </template>
            </template>
          </a-table>
          <div v-else>
            <div class="profit-margin-card-wrapper">
              <div
                class="card"
                v-for="(value, key) in collectionAmount"
                :key="key"
              >
                <span class="title">{{ key.toUpperCase() }}</span>
                <p class="amount">
                  {{ formatAmountCurrency(value) }}
                </p>
              </div>
            </div>
            <a-table
              :columns="paymentInReportColumn"
              :row-key="(record) => record.xid"
              :data-source="reportTableData"
              :pagination="{ default: 10 }"
              :loading="loading"
            >
              <template #bodyCell="{ column, record }">
                <template v-if="column.dataIndex === 'date'">
                  {{ record.date }}
                </template>
                <template v-if="column.dataIndex === 'payment_number'">
                  {{ record.payment_number }}
                </template>
                <template v-if="column.dataIndex === 'user_id'">
                  <UserInfo :user="record.user" />
                </template>
                <template v-if="column.dataIndex === 'amount'">
                  {{ formatAmountCurrency(record.amount) }}
                </template>
                <template v-if="column.dataIndex === 'payment_mode'">
                  {{ record.payment_mode.name }}
                </template>
              </template>
            </a-table>
          </div>
        </div>
      </a-col>
    </a-row>
  </a-card>
  <PaymentA4Invoice
    :data="paymentInvoiceData"
    :visible="paymentInvoiceVisible"
    @closed="paymentInvoiceVisible = false"
  />
</template>
<script>
import { onMounted, reactive, ref, watch } from "vue";
import {
  PlusOutlined,
  EditOutlined,
  DeleteOutlined,
  PrinterOutlined,
} from "@ant-design/icons-vue";
import fields from "./fields";
import crud from "../../../../common/composable/crud";
import common from "../../../../common/composable/common";
import AddEdit from "./AddEdit.vue";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import UserInfo from "../../../../common/components/user/UserInfo.vue";
import DateRangePicker from "../../../../common/components/common/calendar/DateRangePicker.vue";
import dayjs from "dayjs";
import ExportPaymentInReport from "./ExportPaymentInReport.vue";
import PaymentA4Invoice from "../invoice-template/payment/PaymentA4Invoice.vue";

export default {
  components: {
    PlusOutlined,
    EditOutlined,
    DeleteOutlined,
    PrinterOutlined,
    AddEdit,
    AdminPageHeader,
    UserInfo,
    DateRangePicker,
    ExportPaymentInReport,
    PaymentA4Invoice,
  },
  setup() {
    const {
      addEditUrl,
      initData,
      columns,
      paymentType,
      menuParent,
      hashableColumns,
      filterableColumns,
      paymentInReportColumn,
    } = fields();
    const crudVariables = crud();
    const { permsArray, formatAmountCurrency, selectedWarehouse } = common();
    const filters = reactive({
      user_id: undefined,
      staff_user_id: undefined,
    });
    const extraFilters = ref({
      dates: undefined,
    });
    const showCollection = ref(false);
    const paymentInvoiceVisible = ref(false);
    const paymentInvoiceData = ref({});

    const staffUrl = `users?user_type=staff_members&fields=id,xid,user_type,name,email,profile_image,profile_image_url,tax_number,is_walkin_customer,phone,address,pincode,shipping_address,status,created_at,details{opening_balance,opening_balance_type,credit_period,credit_limit,due_amount,warehouse_id,x_warehouse_id},details:warehouse{id,xid,name},role_id,role{id,xid,name,display_name},warehouse_id,x_warehouse_id,warehouse{xid,name}`;

    const showPaymentInvoice = (record) => {
      console.log(record);
      paymentInvoiceData.value = record;
      paymentInvoiceVisible.value = true;
    };
    const users = ref({});
    const staffs = ref({});
    const reportTableData = ref([]);
    const loading = ref(true);
    const collectionAmount = ref({});
    const reportData = ref([]);

    onMounted(() => {
      getInitialData();
      setUrlData();
    });

    const handleShow = (value) => {
      filters.user_id = undefined;
      filters.staff_user_id = undefined;
      if (value) {
        extraFilters.value.dates = [
          dayjs().startOf("day").format("YYYY-MM-DD"),
          dayjs().endOf("day").format("YYYY-MM-DD"),
        ];
      } else {
        extraFilters.value.dates = undefined;
      }
      setUrlData();
    };

    const setUrlData = () => {
      if (!showCollection.value) {
        crudVariables.tableUrl.value = {
          url: `payment-${paymentType}?fields=id,xid,date,amount,notes,payment_mode_id,payment_number,payment_type,payment_mode_id,x_payment_mode_id,paymentMode{id,xid,name},user_id,x_user_id,user{id,xid,name,profile_image,profile_image_url,user_type},staff_user_id,x_staff_user_id,staffMember{id,xid,name,profile_image,profile_image_url,user_type}`,
          filterString: `payment_type eq "${paymentType}"`,
          filters,
          extraFilters: {
            dates: extraFilters.value.dates,
          },
        };
        crudVariables.table.filterableColumns = filterableColumns;

        crudVariables.fetch({
          page: 1,
        });

        crudVariables.crudUrl.value = addEditUrl;
        crudVariables.langKey.value = "payments";
        crudVariables.initData.value = { ...initData };
        crudVariables.formData.value = { ...initData };
        crudVariables.hashableColumns.value = [...hashableColumns];
      } else {
        loading.value = true;
        axiosAdmin
          .post("reports/payment-in", {
            staff_user_id: filters.staff_user_id,
            dates: extraFilters.value.dates,
          })
          .then((res) => {
            reportTableData.value = res.paymentList;
            collectionAmount.value = res.total;
            reportData.value = res;
            loading.value = false;
          })
          .catch((err) => {
            console.log(err);
          });
      }
    };

    const getInitialData = () => {
      const usersPromise = axiosAdmin.post("customer-suppliers");
      const staffsPromise = axiosAdmin.get(staffUrl);

      Promise.all([usersPromise, staffsPromise]).then(
        ([usersResponse, staffsResponse]) => {
          users.value = usersResponse.data;
          staffs.value = staffsResponse.data;
        }
      );
    };

    watch(selectedWarehouse, (newVal, oldVal) => {
      setUrlData();
    });

    return {
      columns,
      ...crudVariables,
      permsArray,
      formatAmountCurrency,
      paymentType,
      menuParent,
      filters,
      extraFilters,
      users,
      staffs,
      setUrlData,
      filterableColumns,
      dayjs,
      showCollection,
      handleShow,
      paymentInReportColumn,
      reportTableData,
      loading,
      collectionAmount,
      reportData,
      showPaymentInvoice,
      paymentInvoiceData,
      paymentInvoiceVisible,
    };
  },
};
</script>
