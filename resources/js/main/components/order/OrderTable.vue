<template>
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
                >
                    <!-- :rowSelection="{
                        selectedRowKeys: selectedRowKeys,
                        onChange: (key) => onSelectedRow(key),
                    }" -->
                    <template #bodyCell="{ column, record }">
                        <template v-if="column.dataIndex === 'order_date'">
                            {{ formatDateTime(record.order_date) }}
                        </template>
                        <template v-if="column.dataIndex === 'warehouse'">
                            <span
                                v-if="record.warehouse && record.warehouse.xid"
                            >
                                {{ record.warehouse.name }}
                            </span>
                        </template>
                        <template v-if="column.dataIndex === 'user_id'">
                            <user-info :user="record.user" />
                        </template>
                        <template v-if="column.dataIndex === 'paid_amount'">
                            {{ formatAmountCurrency(record.paid_amount) }}
                        </template>
                        <template v-if="column.dataIndex === 'total_amount'">
                            {{ formatAmountCurrency(record.total) }}
                        </template>
                        <template v-if="column.dataIndex === 'payment_status'">
                            <PaymentStatus
                                :paymentStatus="record.payment_status"
                            />
                        </template>
                        <template v-if="column.dataIndex === 'order_status'">
                            <OrderStatus :data="record" />
                        </template>
                        <template v-if="column.dataIndex === 'action'">
                            <a-space
                                v-if="
                                    record.order_type == 'online-orders' &&
                                    record.order_status != 'delivered' &&
                                    !record.cancelled
                                "
                            >
                                <!--! View -->
                                <a-tooltip
                                    placement="topLeft"
                                    :title="$t('stock.view_order')"
                                    v-if="
                                        permsArray.includes(
                                            'online_orders_view_details'
                                        ) || permsArray.includes('admin')
                                    "
                                >
                                    <a-button
                                        type="primary"
                                        @click="viewOrder(record)"
                                    >
                                        <template #icon>
                                            <EyeOutlined />
                                        </template>
                                    </a-button>
                                </a-tooltip>
                                <!--! Status Buttons -->
                                <!-- Change to Confirm -->
                                <a-tooltip
                                    v-if="
                                        record.order_status == 'ordered' &&
                                        !record.cancelled &&
                                        (permsArray.includes(
                                            'online_order_confirm'
                                        ) ||
                                            permsArray.includes('admin'))
                                    "
                                    placement="topLeft"
                                    :title="$t('common.confirm')"
                                >
                                    <a-button
                                        type="primary"
                                        @click="confirmOrder(record)"
                                    >
                                        <template #icon>
                                            <CheckOutlined />
                                        </template>
                                    </a-button>
                                </a-tooltip>
                                <!-- change to processing -->
                                <a-tooltip
                                    v-if="
                                        record.order_status == 'confirmed' &&
                                        !record.cancelled &&
                                        (permsArray.includes(
                                            'online_order_processing'
                                        ) ||
                                            permsArray.includes('admin'))
                                    "
                                    placement="topLeft"
                                    :title="$t('common.processing')"
                                >
                                    <a-button
                                        type="primary"
                                        @click="
                                            changeOrderStatus(
                                                record,
                                                'processing'
                                            )
                                        "
                                    >
                                        <template #icon>
                                            <ClockCircleOutlined />
                                        </template>
                                    </a-button>
                                </a-tooltip>
                                <!-- Change to Shipping -->
                                <a-tooltip
                                    v-if="
                                        record.order_status == 'processing' &&
                                        !record.cancelled &&
                                        (permsArray.includes(
                                            'online_orders_shipping'
                                        ) ||
                                            permsArray.includes('admin'))
                                    "
                                    placement="topLeft"
                                    :title="$t('common.shipping')"
                                >
                                    <a-button
                                        type="primary"
                                        @click="
                                            changeOrderStatus(
                                                record,
                                                'shipping'
                                            )
                                        "
                                    >
                                        <template #icon>
                                            <InsertRowBelowOutlined />
                                        </template>
                                    </a-button>
                                </a-tooltip>
                                <!-- Change to Delivered -->
                                <a-tooltip
                                    v-if="
                                        record.order_status == 'shipping' &&
                                        !record.cancelled &&
                                        (permsArray.includes(
                                            'online_orders_confirm_delivery'
                                        ) ||
                                            permsArray.includes('admin'))
                                    "
                                    placement="topLeft"
                                    :title="
                                        $t('online_orders.confirm_delivery')
                                    "
                                >
                                    <a-button
                                        type="primary"
                                        @click="confirmDelivery(record)"
                                    >
                                        <template #icon>
                                            <SendOutlined />
                                        </template>
                                    </a-button>
                                </a-tooltip>
                                <!--! Cancelled  -->
                                <a-tooltip
                                    v-if="
                                        !record.cancelled &&
                                        record.order_status != 'delivered' &&
                                        (permsArray.includes(
                                            'online_order_cancel'
                                        ) ||
                                            permsArray.includes('admin'))
                                    "
                                    placement="topLeft"
                                    :title="$t('common.cancel')"
                                >
                                    <a-button
                                        type="primary"
                                        @click="cancelOrder(record)"
                                        danger
                                    >
                                        <template #icon>
                                            <StopOutlined />
                                        </template>
                                    </a-button>
                                </a-tooltip>
                                <!--* Options  -->
                                <a-dropdown placement="bottomRight">
                                    <MoreOutlined />
                                    <template #overlay>
                                        <a-menu>
                                            <a-menu-item
                                                key="view"
                                                v-if="
                                                    (permsArray.includes(
                                                        `${pageObject.permission}_view`
                                                    ) ||
                                                        permsArray.includes(
                                                            'admin'
                                                        )) &&
                                                    record.order_type ==
                                                        'quotations'
                                                "
                                                @click="convertToSale(record)"
                                            >
                                                <SisternodeOutlined />
                                                {{
                                                    $t(
                                                        "quotation.convert_to_sale"
                                                    )
                                                }}
                                            </a-menu-item>
                                            <a-menu-item
                                                key="view"
                                                v-if="
                                                    permsArray.includes(
                                                        `${pageObject.permission}_view`
                                                    ) ||
                                                    permsArray.includes('admin')
                                                "
                                                @click="viewItem(record)"
                                            >
                                                <EyeOutlined />
                                                {{ $t("common.view") }}
                                            </a-menu-item>
                                            <a-menu-item
                                                key="edit"
                                                v-if="
                                                    permsArray.includes(
                                                        `${pageObject.permission}_edit`
                                                    ) ||
                                                    permsArray.includes('admin')
                                                "
                                                @click="
                                                    () =>
                                                        $router.push({
                                                            name: `admin.stock.${pageObject.type}.edit`,
                                                            params: {
                                                                id: record.xid,
                                                            },
                                                        })
                                                "
                                            >
                                                <EditOutlined />
                                                {{ $t("common.edit") }}
                                            </a-menu-item>
                                            <a-menu-item
                                                key="delete"
                                                v-if="
                                                    record.order_type !=
                                                        'online-orders' &&
                                                    (permsArray.includes(
                                                        `${pageObject.permission}_delete`
                                                    ) ||
                                                        permsArray.includes(
                                                            'admin'
                                                        )) &&
                                                    record.payment_status ==
                                                        'unpaid'
                                                "
                                                @click="
                                                    showDeleteConfirm(
                                                        record.xid
                                                    )
                                                "
                                            >
                                                <DeleteOutlined />
                                                {{ $t("common.delete") }}
                                            </a-menu-item>
                                            <a-menu-item
                                                @click="copyUrl(record.xid)"
                                            >
                                                <copy-outlined />
                                                Copy URL
                                            </a-menu-item>

                                            <!-- B2B Section -->
                                            <a-menu-item>
                                                <a-typography-link
                                                    :href="`/common/invoice/${record.xid}`"
                                                    target="_blank"
                                                >
                                                    <DownloadOutlined />
                                                    {{
                                                        $t(
                                                            `menu.${orderPageObject.menuKey}`
                                                        )
                                                    }}
                                                    Invoice URL
                                                </a-typography-link>
                                            </a-menu-item>
                                            <a-menu-item
                                                @click="
                                                    () =>
                                                        showB2B(
                                                            record.xid,
                                                            false
                                                        )
                                                "
                                            >
                                                <FileDoneOutlined />
                                                Show
                                                {{
                                                    $t(
                                                        `menu.${orderPageObject.menuKey}`
                                                    )
                                                }}
                                                Invoice
                                            </a-menu-item>
                                            <a-menu-item
                                                @click="
                                                    () =>
                                                        showB2B(
                                                            record.xid,
                                                            true
                                                        )
                                                "
                                            >
                                                <FileExclamationOutlined />
                                                Duplicate
                                                {{
                                                    $t(
                                                        `menu.${orderPageObject.menuKey}`
                                                    )
                                                }}
                                                Invoice
                                            </a-menu-item>
                                            <!-- Receipt Section -->
                                            <a-menu-item>
                                                <a-typography-link
                                                    :href="`/common/receipt/${record.xid}`"
                                                    target="_blank"
                                                >
                                                    <ExportOutlined />
                                                    Receipt Share URL
                                                </a-typography-link>
                                            </a-menu-item>
                                            <a-menu-item
                                                @click="
                                                    () =>
                                                        showReciptInvoice(
                                                            record.xid,
                                                            false
                                                        )
                                                "
                                            >
                                                <FileDoneOutlined />
                                                Show Receipt
                                            </a-menu-item>
                                            <a-menu-item
                                                @click="
                                                    () =>
                                                        showReciptInvoice(
                                                            record.xid,
                                                            true
                                                        )
                                                "
                                            >
                                                <FileExclamationOutlined />
                                                Duplicate Receipt
                                            </a-menu-item>
                                        </a-menu>
                                    </template>
                                </a-dropdown>
                            </a-space>
                            <!-- Action For Stock Transfer -->
                            <a-dropdown
                                v-else-if="
                                    record.order_type == 'stock-transfers'
                                "
                                placement="bottomRight"
                            >
                                <MoreOutlined />
                                <template #overlay>
                                    <a-menu>
                                        <a-menu-item
                                            key="view"
                                            v-if="
                                                permsArray.includes(
                                                    `${pageObject.permission}_view`
                                                ) ||
                                                permsArray.includes('admin')
                                            "
                                            @click="viewItem(record)"
                                        >
                                            <EyeOutlined />
                                            {{ $t("common.view") }}
                                        </a-menu-item>
                                        <a-menu-item
                                            key="edit"
                                            v-if="
                                                filters.transfer_type ==
                                                    'transfered' &&
                                                (permsArray.includes(
                                                    `${pageObject.permission}_edit`
                                                ) ||
                                                    permsArray.includes(
                                                        'admin'
                                                    ))
                                            "
                                            @click="
                                                () =>
                                                    $router.push({
                                                        name: `admin.stock.${pageObject.type}.edit`,
                                                        params: {
                                                            id: record.xid,
                                                        },
                                                    })
                                            "
                                        >
                                            <EditOutlined />
                                            {{ $t("common.edit") }}
                                        </a-menu-item>
                                        <a-menu-item
                                            key="delete"
                                            v-if="
                                                filters.transfer_type ==
                                                    'transfered' &&
                                                (permsArray.includes(
                                                    `${pageObject.permission}_delete`
                                                ) ||
                                                    permsArray.includes(
                                                        'admin'
                                                    )) &&
                                                record.payment_status ==
                                                    'unpaid'
                                            "
                                            @click="
                                                showDeleteConfirm(record.xid)
                                            "
                                        >
                                            <DeleteOutlined />
                                            {{ $t("common.delete") }}
                                        </a-menu-item>
                                        <a-menu-item key="download">
                                            <a-typography-link
                                                :href="`/common/invoice/${record.xid}`"
                                                target="_blank"
                                            >
                                                <DownloadOutlined />
                                                {{ $t("common.download") }} &
                                                Print
                                            </a-typography-link>
                                        </a-menu-item>
                                    </a-menu>
                                </template>
                            </a-dropdown>
                            <!-- Action For Sales, Sales-Return, Purchase-Returns, Quotation, Purchase -->
                            <a-dropdown v-else placement="bottomRight">
                                <MoreOutlined />
                                <template #overlay>
                                    <a-menu>
                                        <!-- Convert to Sales -->
                                        <a-menu-item
                                            key="view"
                                            v-if="
                                                (permsArray.includes(
                                                    `${pageObject.permission}_view`
                                                ) ||
                                                    permsArray.includes(
                                                        'admin'
                                                    )) &&
                                                record.order_type ==
                                                    'quotations'
                                            "
                                            @click="convertToSale(record)"
                                        >
                                            <SisternodeOutlined />
                                            {{
                                                $t("quotation.convert_to_sale")
                                            }}
                                        </a-menu-item>
                                        <!-- View -->
                                        <a-menu-item
                                            key="view"
                                            v-if="
                                                permsArray.includes(
                                                    `${pageObject.permission}_view`
                                                ) ||
                                                permsArray.includes('admin')
                                            "
                                            @click="viewItem(record)"
                                        >
                                            <EyeOutlined />
                                            {{ $t("common.view") }}
                                        </a-menu-item>
                                        <!-- Edit -->
                                        <a-menu-item
                                            key="edit"
                                            v-if="
                                                permsArray.includes(
                                                    `${pageObject.permission}_edit`
                                                ) ||
                                                permsArray.includes('admin')
                                            "
                                            @click="
                                                () =>
                                                    $router.push({
                                                        name: `admin.stock.${pageObject.type}.edit`,
                                                        params: {
                                                            id: record.xid,
                                                        },
                                                    })
                                            "
                                        >
                                            <EditOutlined />
                                            {{ $t("common.edit") }}
                                        </a-menu-item>
                                        <!-- Delete -->
                                        <a-menu-item
                                            key="delete"
                                            v-if="
                                                record.order_type !=
                                                    'online-orders' &&
                                                (permsArray.includes(
                                                    `${pageObject.permission}_delete`
                                                ) ||
                                                    permsArray.includes(
                                                        'admin'
                                                    )) &&
                                                record.payment_status ==
                                                    'unpaid'
                                            "
                                            @click="
                                                showDeleteConfirm(record.xid)
                                            "
                                        >
                                            <DeleteOutlined />
                                            {{ $t("common.delete") }}
                                        </a-menu-item>
                                        <!-- Copy URL -->
                                        <a-menu-item
                                            @click="copyUrl(record.xid)"
                                        >
                                            <copy-outlined />
                                            Copy URL
                                        </a-menu-item>
                                        <!-- B2B Section -->
                                        <a-menu-item>
                                            <a-typography-link
                                                :href="`/common/invoice/${record.xid}`"
                                                target="_blank"
                                            >
                                                <ExportOutlined />
                                                {{
                                                    $t(
                                                        `menu.${orderPageObject.menuKey}`
                                                    )
                                                }}
                                                Invoice URL
                                            </a-typography-link>
                                        </a-menu-item>
                                        <a-menu-item
                                            @click="
                                                () => showB2B(record.xid, false)
                                            "
                                        >
                                            <FileDoneOutlined />
                                            Show
                                            {{
                                                $t(
                                                    `menu.${orderPageObject.menuKey}`
                                                )
                                            }}
                                            A4Invoice
                                        </a-menu-item>
                                        <a-menu-item
                                            @click="
                                                () => showModal2(record.xid, false)
                                            "
                                        >
                                            <FileDoneOutlined />
                                            Show
                                            {{
                                                $t(
                                                    `menu.${orderPageObject.menuKey}`
                                                )
                                            }}
                                            A4Invoice 2
                                        </a-menu-item>
                                        <a-menu-item
                                            @click="
                                                () => showB2B(record.xid, true)
                                            "
                                        >
                                            <FileExclamationOutlined />
                                            Duplicate
                                            {{
                                                $t(
                                                    `menu.${orderPageObject.menuKey}`
                                                )
                                            }}
                                            A4Invoice
                                        </a-menu-item>
                                        <a-menu-item
                                            @click="
                                                () =>
                                                    showLetterHeadInvoice(
                                                        record.xid,
                                                        true
                                                    )
                                            "
                                        >
                                            <BorderOuterOutlined />
                                            Show Letterhead Invoice
                                        </a-menu-item>
                                        <a-menu-item
                                            v-if="
                                                record.order_type ==
                                                    'online-orders' ||
                                                record.order_type == 'sales'
                                            "
                                            @click="
                                                () => showA5(record.xid, true)
                                            "
                                        >
                                            <FileDoneOutlined />
                                            Show
                                            {{
                                                $t(
                                                    `menu.${orderPageObject.menuKey}`
                                                )
                                            }}
                                            A5 Invoice
                                        </a-menu-item>
                                        <a-menu-item
                                            v-if="
                                                record.order_type ==
                                                    'online-orders' ||
                                                record.order_type == 'sales'
                                            "
                                            @click="
                                                () => showA5(record.xid, false)
                                            "
                                        >
                                            <FileDoneOutlined />
                                            Show
                                            {{
                                                $t(
                                                    `menu.${orderPageObject.menuKey}`
                                                )
                                            }}
                                            A5 without MRP
                                        </a-menu-item>
                                        <!-- Receipt Section -->
                                        <a-menu-item
                                            v-if="
                                                record.order_type == 'sales' ||
                                                record.order_type ==
                                                    'quotations'
                                            "
                                        >
                                            <a-typography-link
                                                :href="`/common/receipt/${record.xid}`"
                                                target="_blank"
                                            >
                                                <ExportOutlined />
                                                Receipt Share URL
                                            </a-typography-link>
                                        </a-menu-item>
                                        <a-menu-item
                                            v-if="
                                                record.order_type == 'sales' ||
                                                record.order_type ==
                                                    'quotations'
                                            "
                                            @click="
                                                () =>
                                                    showReciptInvoice(
                                                        record.xid,
                                                        false
                                                    )
                                            "
                                        >
                                            <FileDoneOutlined />
                                            Show Receipt
                                        </a-menu-item>
                                        <a-menu-item
                                            v-if="
                                                record.order_type == 'sales' ||
                                                record.order_type ==
                                                    'quotations'
                                            "
                                            @click="
                                                () =>
                                                    showReciptInvoice(
                                                        record.xid,
                                                        true
                                                    )
                                            "
                                        >
                                            <FileExclamationOutlined />
                                            Duplicate Receipt
                                        </a-menu-item>
                                    </a-menu>
                                </template>
                            </a-dropdown>
                        </template>
                    </template>
                    <template #expandedRowRender="orderItemData">
                        <a-table
                            v-if="
                                orderItemData &&
                                orderItemData.record &&
                                orderItemData.record.items
                            "
                            :row-key="(record) => record.xid"
                            :columns="orderItemDetailsColumns"
                            :data-source="orderItemData.record.items"
                            :pagination="false"
                        >
                            <template #bodyCell="{ column, record }">
                                <template
                                    v-if="column.dataIndex === 'product_id'"
                                >
                                    <a-badge>
                                        <a-avatar
                                            shape="square"
                                            :src="record.product.image_url"
                                        />
                                        {{ record.product.name }}
                                    </a-badge>
                                </template>

                                <template
                                    v-if="column.dataIndex === 'identity_code'"
                                >
                                    {{ record.identity_code }}
                                </template>
                                <template
                                    v-if="column.dataIndex === 'quantity'"
                                >
                                    {{
                                        `${record.quantity} ${record.product.unit.short_name}`
                                    }}
                                </template>
                                <template
                                    v-if="
                                        column.dataIndex === 'single_unit_price'
                                    "
                                >
                                    {{
                                        formatAmountCurrency(record.unit_price)
                                    }}
                                </template>
                                <template
                                    v-if="column.dataIndex === 'total_discount'"
                                >
                                    {{
                                        formatAmountCurrency(
                                            record.total_discount
                                        )
                                    }}
                                </template>
                                <template
                                    v-if="column.dataIndex === 'total_tax'"
                                >
                                    {{ formatAmountCurrency(record.total_tax) }}
                                </template>
                                <template
                                    v-if="column.dataIndex === 'subtotal'"
                                >
                                    {{ formatAmountCurrency(record.subtotal) }}
                                </template>
                            </template>
                        </a-table>
                    </template>
                </a-table>
            </div>
        </a-col>
    </a-row>

    <OrderDetails
        :visible="detailsDrawerVisible"
        :order="selectedItem"
        @close="onDetailDrawerClose"
        @goBack="restSelectedItem"
        @reloadOrder="paymentSuccess"
    />

    <ConfirmOrder
        :visible="confirmModalVisible"
        :data="modalData"
        @closed="confirmModalVisible = false"
        @confirmSuccess="initialSetup"
    />

    <B2BInvoiceModal
        :visible="B2BInvoiceModalVisible"
        :order="B2BOrders"
        @closed="B2BInvoiceModalVisible = false"
        :routeBack="routeBack"
    />
    <B2BInvoiceModal2
        :visible="B2BInvoiceModal2Visible"
        :order="B2BOrders"
        @closed="B2BInvoiceModal2Visible = false"
        :routeBack="routeBack"
    />

    <A5InvoiceModal
        :visible="A5InvoiceModalVisible"
        :order="B2BOrders"
        @closed="A5InvoiceModalVisible = false"
        :routeBack="routeBack"
    />

    <B2BLetterHeadInvoice
        :visible="LetterHeadInvoiceVisible"
        :order="LetterHeadInvoice"
        @closed="LetterHeadInvoiceVisible = false"
        :routeBack="routeBack"
    />
    <RecieptInvoiceModal
        :visible="RecieptInvoiceModalVisible"
        :order="RecieptOrders"
        @closed="RecieptInvoiceModalVisible = false"
        :routeBack="routeBack"
    />

    <ViewOrder
        :visible="viewModalVisible"
        :order="modalData"
        @closed="viewModalVisible = false"
    />
</template>

<script>
import { onMounted, watch, ref, createVNode } from "vue";
import {
    EyeOutlined,
    PlusOutlined,
    EditOutlined,
    DeleteOutlined,
    ExclamationCircleOutlined,
    MoreOutlined,
    DownloadOutlined,
    ExportOutlined,
    CopyOutlined,
    PrinterOutlined,
    CheckOutlined,
    StopOutlined,
    SendOutlined,
    SisternodeOutlined,
    FileDoneOutlined,
    FileExclamationOutlined,
    ClockCircleOutlined,
    InsertRowBelowOutlined,
    BorderOuterOutlined,
} from "@ant-design/icons-vue";
import { Modal, message, notification } from "ant-design-vue";
import { useRoute, useRouter } from "vue-router";
import { find } from "lodash-es";
import { useI18n } from "vue-i18n";
import fields from "../../views/stock-management/purchases/fields";
import common from "../../../common/composable/common";
import datatable from "../../../common/composable/datatable";
import PaymentStatus from "../../../common/components/order/PaymentStatus.vue";
import OrderStatus from "../../../common/components/order/OrderStatus.vue";
import Details from "../../views/stock-management/purchases/Details.vue";
import UserInfo from "../../../common/components/user/UserInfo.vue";
import OrderDetails from "./OrderDetails.vue";
import ConfirmOrder from "../../views/stock-management/online-orders/ConfirmOrder.vue";
import ViewOrder from "../../views/stock-management/online-orders/ViewOrder.vue";
import VuePdfEmbed from "vue-pdf-embed";
import B2BInvoiceModal from "../../views/stock-management/invoice-template/a4/A4Invoice.vue";
import B2BInvoiceModal2 from "../../views/stock-management/invoice-template/a4/A4Invoice2.vue";
import A5InvoiceModal from "../../views/stock-management/invoice-template/a5/A5Invoice.vue";
import B2BLetterHeadInvoice from "../../views/stock-management/invoice-template/a4/A4LetterHeadInvoice.vue";
import RecieptInvoiceModal from "../../views/stock-management/invoice-template/SalesRecieptInvoice.vue";

export default {
    props: [
        "orderType",
        "filters",
        "extraFilters",
        "pagination",
        "perPageItems",
        "routeBack",
    ],
    components: {
        EyeOutlined,
        PlusOutlined,
        EditOutlined,
        DeleteOutlined,
        MoreOutlined,
        DownloadOutlined,
        ExportOutlined,
        CopyOutlined,
        PrinterOutlined,
        ExclamationCircleOutlined,
        SisternodeOutlined,
        CheckOutlined,
        StopOutlined,
        SendOutlined,
        FileDoneOutlined,
        FileExclamationOutlined,
        B2BInvoiceModal,
        B2BInvoiceModal2,
        A5InvoiceModal,
        B2BLetterHeadInvoice,
        RecieptInvoiceModal,
        Details,
        UserInfo,
        Details,
        PaymentStatus,
        OrderStatus,
        OrderDetails,
        ConfirmOrder,
        ViewOrder,
        VuePdfEmbed,
        ClockCircleOutlined,
        InsertRowBelowOutlined,
        BorderOuterOutlined,
    },
    methods: {
        print: function () {
            //Print the content of the Document Editor.
            this.$refs.documenteditor.print();
        },
    },

    setup(props) {
        const {
            columns,
            hashableColumns,
            setupTableColumns,
            filterableColumns,
            pageObject,
            orderType,
            orderStatus,
            orderItemDetailsColumns,
        } = fields();
        const datatableVariables = datatable();
        const {
            formatAmountCurrency,
            invoiceBaseUrl,
            viewinvoiceBaseUrl,
            permsArray,
            calculateOrderFilterString,
            formatDateTime,
            selectedWarehouse,
            selectedLang,
            orderStatusColors,
            orderPageObject,
            baseUrl,
        } = common();
        const { t } = useI18n();
        const detailsDrawerVisible = ref(false);

        const selectedItem = ref({});

        // For Online Orders
        const confirmModalVisible = ref(false);
        const viewModalVisible = ref(false);
        const modalData = ref({});
        // End For Online Orders

        const B2BInvoiceModalVisible = ref(false);
        const B2BInvoiceModal2Visible = ref(false);
        const A5InvoiceModalVisible = ref(false);
        const B2BOrders = ref({});
        const RecieptInvoiceModalVisible = ref(false);
        const RecieptOrders = ref({});
        const LetterHeadInvoice = ref({});
        const LetterHeadInvoiceVisible = ref(false);

        const showLetterHeadInvoice = (id, duplicate) => {
            axiosAdmin.get(`sales/${id}`).then((order) => {
                LetterHeadInvoice.value = {
                    ...order.data,
                    duplicate: duplicate,
                };
                LetterHeadInvoiceVisible.value = true;
            });
        };
        const showB2B = (id, duplicate) => {
            axiosAdmin.get(`sales/${id}`).then((order) => {
                B2BOrders.value = { ...order.data, duplicate: duplicate };
                B2BInvoiceModalVisible.value = true;
            });
        };
        const showModal2 = (id, duplicate) => {
            axiosAdmin.get(`sales/${id}`).then((order) => {
                B2BOrders.value = { ...order.data, duplicate: duplicate };
                B2BInvoiceModal2Visible.value = true;
            });
        };

        const showA5 = (id, show_mrp) => {
            axiosAdmin.get(`sales/${id}`).then((order) => {
                B2BOrders.value = { ...order.data, show_mrp: show_mrp };
                A5InvoiceModalVisible.value = true;
            });
        };

        const showReciptInvoice = (id, duplicate) => {
            axiosAdmin.get(`sales/${id}`).then((order) => {
                RecieptOrders.value = { ...order.data, duplicate: duplicate };
                RecieptInvoiceModalVisible.value = true;
            });
        };

        const selectedRowKeys = ref([]);

        const onSelectedRow = (key) => {
            selectedRowKeys.value = key;
        };

        onMounted(() => {
            initialSetup();
        });

        const copyUrl = (id) => {
            const urlToCopy = `${window.location.origin}/common/invoice/${id}`;
            const el = document.createElement("textarea");
            el.value = urlToCopy;
            document.body.appendChild(el);
            el.select();
            document.execCommand("copy");
            document.body.removeChild(el);
            message.success("URL Copied.");
        };

        const initialSetup = () => {
            orderType.value = props.orderType;
            if (props.perPageItems) {
                datatableVariables.table.pagination.pageSize =
                    props.perPageItems;
            }
            datatableVariables.table.pagination.current = 1;
            datatableVariables.table.pagination.currentPage = 1;
            datatableVariables.hashable.value = hashableColumns;
            datatableVariables.table.sorter.field = "order_date";
            setupTableColumns();
            setUrlData();
        };

        const findUserAccess = () => {
            if (
                permsArray.value.includes("admin") ||
                permsArray.value.includes("view_all_customer")
            ) {
                return true;
            } else {
                return false;
            }
        };

        const setUrlData = () => {
            const tableFilter = props.filters;

            const filterString = calculateOrderFilterString(tableFilter);

            var extraFilterObject = {};
            if (tableFilter.dates) {
                extraFilterObject.dates = tableFilter.dates;
            }
            if (tableFilter.transfer_type) {
                extraFilterObject.transfer_type = tableFilter.transfer_type;
            }

            datatableVariables.tableUrl.value = {
                url: `${
                    props.orderType
                }?view_all_customer=${findUserAccess()}&identity_code=${
                    tableFilter.identity_code
                }&fields=id,xid,unique_id,warehouse_id,x_warehouse_id,warehouse{id,xid,name},from_warehouse_id,x_from_warehouse_id,fromWarehouse{id,xid,name},invoice_number,invoice_type,order_type,order_date,tax_amount,discount,shipping,subtotal,paid_amount,due_amount,order_status,payment_status,total,tax_rate,shipping_type,staff_user_id,x_staff_user_id,staffMember{id,xid,name,profile_image,profile_image_url,user_type},user_id,x_user_id,user{id,xid,user_type,name,profile_image,profile_image_url,phone,assign_to,pincode,location,address,email,business_type},orderPayments{id,xid,amount,payment_id,x_payment_id},orderPayments:payment{id,xid,amount,payment_mode_id,x_payment_mode_id,date,notes},orderPayments:payment:paymentMode{id,xid,name},items{id,xid,product_id,x_product_id,single_unit_price,unit_price,quantity,tax_rate,total_tax,tax_type,total_discount,subtotal,identity_code},items:product{id,xid,name,image,image_url,unit_id,x_unit_id},items:product:unit{id,xid,name,short_name},items:product:details{id,xid,warehouse_id,x_warehouse_id,product_id,x_product_id,current_stock},cancelled,terms_condition,shippingAddress{id,xid,order_id,name,email,phone,address,shipping_address,city,state,country,zipcode}`,
                filterString,
                filters: {
                    user_id: tableFilter.user_id
                        ? tableFilter.user_id
                        : undefined,
                    warehouse_id: tableFilter.warehouse_id
                        ? tableFilter.warehouse_id
                        : undefined,
                },
                extraFilters: extraFilterObject,
            };
            datatableVariables.table.filterableColumns = filterableColumns;

            if (
                tableFilter.searchColumn &&
                tableFilter.searchString &&
                tableFilter.searchString != ""
            ) {
                datatableVariables.table.searchColumn =
                    tableFilter.searchColumn;
                datatableVariables.table.searchString =
                    tableFilter.searchString;
            } else {
                datatableVariables.table.searchColumn = undefined;
                datatableVariables.table.searchString = "";
            }

            datatableVariables.fetch({
                page: 1,
            });
        };

        const showDeleteConfirm = (id) => {
            Modal.confirm({
                title: t("common.delete") + "?",
                icon: createVNode(ExclamationCircleOutlined),
                content: t(`${pageObject.value.langKey}.delete_message`),
                centered: true,
                okText: t("common.yes"),
                okType: "danger",
                cancelText: t("common.no"),
                onOk() {
                    axiosAdmin.delete(`${props.orderType}/${id}`).then(() => {
                        setUrlData();
                        notification.success({
                            message: t("common.success"),
                            description: t(
                                `${pageObject.value.langKey}.deleted`
                            ),
                        });
                    });
                },
                onCancel() {},
            });
        };

        const viewItem = (record) => {
            selectedItem.value = record;
            detailsDrawerVisible.value = true;
        };

        const restSelectedItem = () => {
            selectedItem.value = {};
        };

        const paymentSuccess = () => {
            datatableVariables.fetch({
                page: datatableVariables.currentPage.value,
                success: (results) => {
                    const searchResult = find(results, (result) => {
                        return result.xid == selectedItem.value.xid;
                    });

                    if (searchResult != undefined) {
                        selectedItem.value = searchResult;
                    }
                },
            });
        };

        const onDetailDrawerClose = () => {
            detailsDrawerVisible.value = false;
        };

        // For Online Orders
        const confirmOrder = (order) => {
            modalData.value = order;
            confirmModalVisible.value = true;
        };

        const viewOrder = (order) => {
            modalData.value = order;
            viewModalVisible.value = true;
        };

        // const changeOrderStatus = (order) => {
        //     processRequest({
        //         url: `online-orders/change-status/${order.unique_id}`,
        //         data: { order_status: order.order_status },
        //         success: (res) => {
        //             // Toastr Notificaiton
        //             notification.success({
        //                 placement: "bottomRight",
        //                 message: t("common.success"),
        //                 description: t("online_orders.order_status_changed"),
        //             });
        //         },
        //         error: (errorRules) => {},
        //     });
        // };

        const convertToSale = (order) => {
            Modal.confirm({
                title: t("quotation.convert_to_sale") + "?",
                icon: createVNode(ExclamationCircleOutlined),
                content: t(`quotation.convert_message`),
                centered: true,
                okText: t("common.yes"),
                okType: "danger",
                cancelText: t("common.no"),
                onOk() {
                    axiosAdmin
                        .post(`quotations/convert-to-sale/${order.unique_id}`)
                        .then(() => {
                            datatableVariables.fetch();

                            // Toastr Notificaiton
                            notification.success({
                                placement: "bottomRight",
                                message: t("common.success"),
                                description: t(
                                    "quotation.quotation_converted_to_sales"
                                ),
                            });
                        });
                },
                onCancel() {},
            });
        };

        const cancelOrder = (order) => {
            Modal.confirm({
                title: t("online_orders.cancel_order") + "?",
                icon: createVNode(ExclamationCircleOutlined),
                content: t(`online_orders.cancel_message`),
                centered: true,
                okText: t("common.yes"),
                okType: "danger",
                cancelText: t("common.no"),
                onOk() {
                    axiosAdmin
                        .post(`online-orders/cancel/${order.unique_id}`)
                        .then(() => {
                            initialSetup();
                            notification.success({
                                message: t("common.success"),
                                description: t(`online_orders.order_cancelled`),
                                placement: "bottomRight",
                            });
                        });
                },
                onCancel() {},
            });
        };

        const confirmDelivery = (order) => {
            Modal.confirm({
                title: t("common.delivered") + "?",
                icon: createVNode(ExclamationCircleOutlined),
                content: t(`online_orders.deliver_message`),
                centered: true,
                okText: t("common.yes"),
                okType: "danger",
                cancelText: t("common.no"),
                onOk() {
                    axiosAdmin
                        .post(`online-orders/delivered/${order.unique_id}`)
                        .then(() => {
                            initialSetup();
                            notification.success({
                                message: t("common.success"),
                                description: t(`online_orders.order_delivered`),
                                placement: "bottomRight",
                            });
                        });
                },
                onCancel() {},
            });
        };
        const changeOrderStatus = (order, status) => {
            Modal.confirm({
                title: t(`common.${status}`) + "?",
                icon: createVNode(ExclamationCircleOutlined),
                content: t(`online_orders.${status}_message`),
                centered: true,
                okText: t("common.yes"),
                okType: "danger",
                cancelText: t("common.no"),
                onOk() {
                    axiosAdmin
                        .post(
                            `online-orders/change-status/${order.unique_id}`,
                            { order_status: status }
                        )
                        .then(() => {
                            initialSetup();
                            notification.success({
                                message: t("common.success"),
                                description: t(`online_orders.order_${status}`),
                                placement: "bottomRight",
                            });
                        });
                },
                onCancel() {},
            });
        };
        // End For Online Orders

        watch(props, (newVal, oldVal) => {
            initialSetup();
            restSelectedItem();
        });

        watch(selectedWarehouse, (newVal, oldVal) => {
            setUrlData();
        });

        return {
            columns,
            ...datatableVariables,
            filterableColumns,
            pageObject,

            formatDateTime,
            orderStatus,
            orderStatusColors,

            setUrlData,
            formatAmountCurrency,
            invoiceBaseUrl,
            viewinvoiceBaseUrl,
            permsArray,

            selectedItem,
            viewItem,
            restSelectedItem,
            paymentSuccess,

            showDeleteConfirm,

            detailsDrawerVisible,
            onDetailDrawerClose,
            orderItemDetailsColumns,
            selectedLang,
            initialSetup,

            convertToSale,

            // For Online Orders
            confirmOrder,
            cancelOrder,
            viewOrder,
            confirmDelivery,
            changeOrderStatus,
            confirmModalVisible,
            viewModalVisible,
            modalData,
            // End For Online Orders

            // Copy Function
            copyUrl,

            B2BInvoiceModalVisible,
            A5InvoiceModalVisible,
            B2BOrders,
            showB2B,
            showA5,
            RecieptInvoiceModalVisible,
            RecieptOrders,
            showReciptInvoice,
            showLetterHeadInvoice,
            LetterHeadInvoice,
            LetterHeadInvoiceVisible,
            orderPageObject,
            selectedRowKeys,
            onSelectedRow,
            B2BInvoiceModal2Visible,
            showModal2
        };
    },
};
</script>

<style>
@media print {
    .document-link {
        display: block !important;
    }
}

.document-link {
    display: none;
}
</style>
