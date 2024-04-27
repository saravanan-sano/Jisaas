<template>
    <a-menu
        :theme="appSetting.left_sidebar_theme"
        :openKeys="openKeys"
        v-model:selectedKeys="selectedKeys"
        :mode="mode"
        @openChange="onOpenChange"
        :style="{ borderRight: 'none' }"
        class="top-header"
    >
        <a-menu-item
            @click="
                () => {
                    menuSelected();
                    $router.push({ name: 'admin.dashboard.index' });
                }
            "
            key="dashboard"
            v-if="permsArray.includes('admin') && pageTitle != 'Setup Company'"
        >
            <HomeOutlined />
            <span>{{ $t("menu.dashboard") }}</span>
        </a-menu-item>

        <a-sub-menu
            v-if="
                permsArray.includes('customers_view') ||
                permsArray.includes('suppliers_view') ||
                (permsArray.includes('admin') && pageTitle != 'Setup Company')
            "
            key="parties"
        >
            <template #title>
                <span>
                    <TeamOutlined />
                    <span>{{ $t("menu.parties") }}</span>
                </span>
            </template>
            <a-menu-item
                v-if="
                    permsArray.includes('customers_view') ||
                    (permsArray.includes('admin') &&
                        pageTitle != 'Setup Company')
                "
                @click="
                    () => {
                        menuSelected();
                        $router.push({ name: 'admin.customers.index' });
                    }
                "
                key="customers"
            >
                <UserOutlined />
                {{ $t("menu.customers") }}
            </a-menu-item>
            <a-menu-item
                v-if="
                    permsArray.includes('suppliers_view') ||
                    (permsArray.includes('admin') &&
                        pageTitle != 'Setup Company')
                "
                @click="
                    () => {
                        menuSelected();
                        $router.push({ name: 'admin.suppliers.index' });
                    }
                "
                key="suppliers"
            >
                <TeamOutlined />
                {{ $t("menu.suppliers") }}
            </a-menu-item>
        </a-sub-menu>

        <a-sub-menu
            key="product_manager"
            v-if="
                permsArray.includes('brands_view') ||
                permsArray.includes('categories_view') ||
                permsArray.includes('products_view') ||
                (permsArray.includes('admin') && pageTitle != 'Setup Company')
            "
        >
            <template #title>
                <span>
                    <AppstoreOutlined />
                    <span>{{ $t("menu.product_manager") }}</span>
                </span>
            </template>
            <a-menu-item
                @click="
                    () => {
                        menuSelected();
                        $router.push({ name: 'admin.brands.index' });
                    }
                "
                key="brands"
                v-if="
                    permsArray.includes('brands_view') ||
                    (permsArray.includes('admin') &&
                        pageTitle != 'Setup Company')
                "
            >
                <GoldOutlined />
                {{ $t("menu.brands") }}
            </a-menu-item>
            <a-menu-item
                @click="
                    () => {
                        menuSelected();
                        $router.push({ name: 'admin.categories.index' });
                    }
                "
                key="categories"
                v-if="
                    permsArray.includes('categories_view') ||
                    (permsArray.includes('admin') &&
                        pageTitle != 'Setup Company')
                "
            >
                <ClusterOutlined />
                {{ $t("menu.categories") }}
            </a-menu-item>
            <a-menu-item
                @click="
                    () => {
                        menuSelected();
                        $router.push({ name: 'admin.products.index' });
                    }
                "
                key="products"
                v-if="
                    permsArray.includes('products_view') ||
                    (permsArray.includes('admin') &&
                        pageTitle != 'Setup Company')
                "
            >
                <ShoppingOutlined />
                {{ $t("menu.products") }}
            </a-menu-item>
        </a-sub-menu>

        <a-sub-menu
            key="sales"
            v-if="
                permsArray.includes('sales_view') ||
                permsArray.includes('quotation_view') ||
                permsArray.includes('sales_returns_view') ||
                permsArray.includes('payment_in_view') ||
                (permsArray.includes('admin') && pageTitle != 'Setup Company')
            "
        >
            <template #title>
                <span>
                    <ShopOutlined />
                    <span>{{ $t("menu.sales") }}</span>
                </span>
            </template>
            <a-menu-item
                @click="
                    () => {
                        menuSelected();
                        $router.push({
                            name: 'admin.stock.sales.index',
                        });
                    }
                "
                key="sales"
                v-if="
                    permsArray.includes('sales_view') ||
                    (permsArray.includes('admin') &&
                        pageTitle != 'Setup Company')
                "
            >
                <ArrowRightOutlined />
                {{ $t("menu.sales") }}
            </a-menu-item>

            <a-menu-item
                @click="
                    () => {
                        menuSelected();
                        $router.push({
                            name: 'admin.stock.sales-returns.index',
                        });
                    }
                "
                key="sales_returns"
                v-if="
                    (permsArray.includes('sales_returns_view') ||
                        (permsArray.includes('admin') &&
                            pageTitle != 'Setup Company')) &&
                    willSubscriptionModuleVisible('sales_return')
                "
            >
                <RollbackOutlined />
                {{ $t("menu.sales_returns") }}
            </a-menu-item>

            <a-menu-item
                @click="
                    () => {
                        menuSelected();
                        $router.push({
                            name: 'admin.payments.in',
                        });
                    }
                "
                key="payment_in"
                v-if="
                    permsArray.includes('payment_in_view') ||
                    (permsArray.includes('admin') &&
                        pageTitle != 'Setup Company')
                "
            >
                <PlusOutlined />
                {{ $t("menu.payment_in") }}
            </a-menu-item>

            <a-menu-item
                @click="
                    () => {
                        menuSelected();
                        $router.push({
                            name: 'admin.stock.quotations.index',
                        });
                    }
                "
                key="quotations"
                v-if="
                    (permsArray.includes('quotation_view') ||
                        (permsArray.includes('admin') &&
                            pageTitle != 'Setup Company')) &&
                    willSubscriptionModuleVisible('quotation')
                "
            >
                <SnippetsOutlined />
                {{ $t("menu.quotation_estimate") }}
            </a-menu-item>

            <a-menu-item
                @click="menuSelected"
                key="online_orders"
                v-if="
                    (permsArray.includes('online_orders_view') ||
                        (permsArray.includes('admin') &&
                            pageTitle != 'Setup Company')) &&
                    willSubscriptionModuleVisible('online_store')
                "
            >
                <router-link :to="{ name: 'admin.online_orders.index' }">
                    <LaptopOutlined />
                    <span>{{ $t("menu.online_orders") }}</span>
                </router-link>
            </a-menu-item>
        </a-sub-menu>

        <a-sub-menu key="coupon" v-if="permsArray.includes('admin')">
            <template #title>
                <span>
                    <ShopOutlined />
                    <span>{{ $t("menu.coupon_management") }}</span>
                </span>
            </template>
            <a-menu-item
                @click="
                    () => {
                        menuSelected();
                        $router.push({
                            name: 'admin.coupons.index',
                        });
                    }
                "
                key="coupons"
                v-if="permsArray.includes('admin')"
            >
                <ArrowRightOutlined />
                {{ $t("menu.coupon") }}
            </a-menu-item>
            <a-menu-item
                @click="
                    () => {
                        menuSelected();
                        $router.push({
                            name: 'admin.coupons_history.index',
                        });
                    }
                "
                key="coupons_history"
                v-if="permsArray.includes('admin')"
            >
                <ArrowRightOutlined />
                {{ $t("menu.coupons_history") }}
            </a-menu-item>
        </a-sub-menu>

        <a-menu-item
            v-if="
                (permsArray.includes('pos_view') ||
                    (permsArray.includes('admin') &&
                        pageTitle != 'Setup Company')) &&
                willSubscriptionModuleVisible('pos')
            "
            @click="
                () => {
                    menuSelected();
                    $router.push({ name: 'admin.pos.index' });
                }
            "
            key="pos"
        >
            <ShoppingCartOutlined />
            <span>{{ $t("menu.pos") }}</span>
        </a-menu-item>

        <a-sub-menu
            v-if="
                ((permsArray.includes('purchases_view') ||
                    permsArray.includes('sales_view') ||
                    permsArray.includes('purchase_returns_view') ||
                    permsArray.includes('sales_returns_view')) &&
                    permsArray.includes('order_payments_view')) ||
                permsArray.includes('products_view') ||
                permsArray.includes('customers_view') ||
                permsArray.includes('suppliers_view') ||
                (permsArray.includes('admin') && pageTitle != 'Setup Company')
            "
            key="reports"
        >
            <template #title>
                <span>
                    <BarChartOutlined />
                    <span>{{ $t("menu.reports") }}</span>
                </span>
            </template>
            <a-menu-item
                v-if="
                    ((permsArray.includes('purchases_view') ||
                        permsArray.includes('sales_view') ||
                        permsArray.includes('purchase_returns_view') ||
                        permsArray.includes('sales_returns_view')) &&
                        permsArray.includes('order_payments_view')) ||
                    (permsArray.includes('admin') &&
                        pageTitle != 'Setup Company')
                "
                @click="
                    () => {
                        menuSelected();
                        $router.push({
                            name: 'admin.reports.payments.index',
                        });
                    }
                "
                key="payments"
            >
                <AccountBookOutlined />
                {{ $t("menu.payments") }}
            </a-menu-item>
            <a-menu-item
                v-if="
                    permsArray.includes('products_view') ||
                    (permsArray.includes('admin') &&
                        pageTitle != 'Setup Company')
                "
                @click="
                    () => {
                        menuSelected();
                        $router.push({ name: 'admin.reports.stock.index' });
                    }
                "
                key="stock_alert"
            >
                <ShareAltOutlined />
                {{ $t("menu.stock_alert") }}
            </a-menu-item>
            <a-menu-item
                v-if="
                    permsArray.includes('users_view') ||
                    (permsArray.includes('admin') &&
                        pageTitle != 'Setup Company')
                "
                @click="
                    () => {
                        menuSelected();
                        $router.push({
                            name: 'admin.reports.sales_summary.index',
                        });
                    }
                "
                key="sales_summary"
            >
                <StockOutlined />
                {{ $t("menu.sales_summary") }}
            </a-menu-item>
            <a-menu-item
                v-if="
                    permsArray.includes('products_view') ||
                    (permsArray.includes('admin') &&
                        pageTitle != 'Setup Company')
                "
                @click="
                    () => {
                        menuSelected();
                        $router.push({
                            name: 'admin.reports.stock_summary.index',
                        });
                    }
                "
                key="stock_summary"
            >
                <DotChartOutlined />
                {{ $t("menu.stock_summary") }}
            </a-menu-item>
            <a-menu-item
                v-if="
                    permsArray.includes('products_view') ||
                    (permsArray.includes('admin') &&
                        pageTitle != 'Setup Company')
                "
                @click="
                    () => {
                        menuSelected();
                        $router.push({
                            name: 'admin.reports.rate_list.index',
                        });
                    }
                "
                key="rate_list"
            >
                <BookOutlined />
                {{ $t("menu.rate_list") }}
            </a-menu-item>
            <a-menu-item
                v-if="
                    permsArray.includes('products_view') ||
                    (permsArray.includes('admin') &&
                        pageTitle != 'Setup Company')
                "
                @click="
                    () => {
                        menuSelected();
                        $router.push({
                            name: 'admin.reports.product_sales_summary.index',
                        });
                    }
                "
                key="product_sales_summary"
            >
                <PieChartOutlined />
                {{ $t("menu.product_sales_summary") }}
            </a-menu-item>
            <a-menu-item
                v-if="
                    permsArray.includes('customers_view') ||
                    permsArray.includes('suppliers_view') ||
                    (permsArray.includes('admin') &&
                        pageTitle != 'Setup Company')
                "
                @click="
                    () => {
                        menuSelected();
                        $router.push({ name: 'admin.reports.users.index' });
                    }
                "
                key="users_reports"
            >
                <AreaChartOutlined />
                {{ $t("menu.users_reports") }}
            </a-menu-item>
            <a-menu-item
                v-if="
                    (permsArray.includes('products_view') &&
                        permsArray.includes('purchases_view') &&
                        permsArray.includes('sales_view') &&
                        permsArray.includes('purchase_returns_view') &&
                        permsArray.includes('sales_returns_view')) ||
                    (permsArray.includes('admin') &&
                        pageTitle != 'Setup Company')
                "
                @click="
                    () => {
                        menuSelected();
                        $router.push({
                            name: 'admin.reports.profit_loss.index',
                        });
                    }
                "
                key="profit_loss"
            >
                <RiseOutlined />
                {{ $t("menu.profit_loss") }}
            </a-menu-item>
            <a-menu-item
                v-if="
                    permsArray.includes('sales_view') ||
                    (permsArray.includes('admin') &&
                        pageTitle != 'Setup Company')
                "
                @click="
                    () => {
                        menuSelected();
                        $router.push({
                            name: 'admin.reports.referral_report.index',
                        });
                    }
                "
                key="referral_report"
            >
                <ShareAltOutlined />
                {{ $t("menu.referral") }}
            </a-menu-item>
        </a-sub-menu>

        <a-sub-menu
            key="purchases"
            v-if="
                permsArray.includes('purchases_view') ||
                permsArray.includes('purchase_returns_view') ||
                permsArray.includes('payment_out_view') ||
                (permsArray.includes('admin') && pageTitle != 'Setup Company')
            "
        >
            <template #title>
                <span>
                    <ShoppingOutlined />
                    <span>{{ $t("menu.purchases") }}</span>
                </span>
            </template>
            <a-menu-item
                @click="
                    () => {
                        menuSelected();
                        $router.push({
                            name: 'admin.stock.purchases.index',
                        });
                    }
                "
                key="purchases"
                v-if="
                    permsArray.includes('purchases_view') ||
                    (permsArray.includes('admin') &&
                        pageTitle != 'Setup Company')
                "
            >
                <ArrowLeftOutlined />
                {{ $t("menu.purchase") }}
            </a-menu-item>
            <a-menu-item
                @click="
                    () => {
                        menuSelected();
                        $router.push({
                            name: 'admin.stock.purchase-returns.index',
                        });
                    }
                "
                key="purchase_returns"
                v-if="
                    (permsArray.includes('purchase_returns_view') ||
                        (permsArray.includes('admin') &&
                            pageTitle != 'Setup Company')) &&
                    willSubscriptionModuleVisible('purchase_return')
                "
            >
                <RetweetOutlined />
                {{ $t("menu.purchase_returns") }}
            </a-menu-item>

            <a-menu-item
                @click="
                    () => {
                        menuSelected();
                        $router.push({
                            name: 'admin.payments.out',
                        });
                    }
                "
                key="payment_out"
                v-if="
                    permsArray.includes('payment_out_view') ||
                    (permsArray.includes('admin') &&
                        pageTitle != 'Setup Company')
                "
            >
                <MinusOutlined />
                {{ $t("menu.payment_out") }}
            </a-menu-item>
        </a-sub-menu>

        <a-sub-menu
            key="inventory"
            v-if="
                permsArray.includes('stock_transfers_view') ||
                permsArray.includes('stock_adjustments_view') ||
                (permsArray.includes('admin') && pageTitle != 'Setup Company')
            "
        >
            <template #title>
                <span>
                    <CalculatorOutlined />
                    <span>{{ $t("menu.inventory") }}</span>
                </span>
            </template>
            <a-menu-item
                @click="
                    () => {
                        menuSelected();
                        $router.push({
                            name: 'admin.stock.stock-transfers.index',
                        });
                    }
                "
                key="stock_transfer"
                v-if="
                    (permsArray.includes('stock_transfers_view') ||
                        (permsArray.includes('admin') &&
                            pageTitle != 'Setup Company')) &&
                    willSubscriptionModuleVisible('stock_transfer')
                "
            >
                <CarOutlined />
                <span>{{ $t("menu.stock_transfer") }}</span>
            </a-menu-item>

            <a-menu-item
                @click="
                    () => {
                        menuSelected();
                        $router.push({ name: 'admin.stock_adjustments.index' });
                    }
                "
                key="stock_adjustment"
                v-if="
                    (permsArray.includes('stock_adjustments_view') ||
                        (permsArray.includes('admin') &&
                            pageTitle != 'Setup Company')) &&
                    willSubscriptionModuleVisible('stock_adjustment')
                "
            >
                <CalculatorOutlined />
                <span>{{ $t("menu.stock_adjustment") }}</span>
            </a-menu-item>
        </a-sub-menu>

        <a-sub-menu
            key="finance"
            v-if="
                permsArray.includes('cash_bank_view') ||
                permsArray.includes('expense_categories_view') ||
                permsArray.includes('expenses_view') ||
                (permsArray.includes('admin') &&
                    pageTitle != 'Setup Company' &&
                    willSubscriptionModuleVisible('expense'))
            "
        >
            <template #title>
                <span>
                    <ShoppingOutlined />
                    <span>{{ $t("menu.finance") }}</span>
                </span>
            </template>

            <a-menu-item
                v-if="
                    permsArray.includes('cash_bank_view') ||
                    (permsArray.includes('admin') &&
                        pageTitle != 'Setup Company')
                "
                @click="
                    () => {
                        menuSelected();
                        $router.push({ name: 'admin.reports.cash_bank.index' });
                    }
                "
                key="cash_bank"
            >
                <BankOutlined />
                <span>{{ $t("menu.cash_bank") }}</span>
            </a-menu-item>
            <a-menu-item
                @click="
                    () => {
                        menuSelected();
                        $router.push({
                            name: 'admin.expense_categories.index',
                        });
                    }
                "
                key="expense_categories"
                v-if="
                    permsArray.includes('expense_categories_view') ||
                    (permsArray.includes('admin') &&
                        pageTitle != 'Setup Company')
                "
            >
                <ApartmentOutlined />
                {{ $t("menu.expense_categories") }}
            </a-menu-item>
            <a-menu-item
                @click="
                    () => {
                        menuSelected();
                        $router.push({ name: 'admin.expenses.index' });
                    }
                "
                key="expenses"
                v-if="
                    permsArray.includes('expenses_view') ||
                    (permsArray.includes('admin') &&
                        pageTitle != 'Setup Company')
                "
            >
                <AccountBookOutlined />
                {{ $t("menu.expenses") }}
            </a-menu-item>
        </a-sub-menu>

        <a-sub-menu
            key="master"
            v-if="
                permsArray.includes('factorydelete_view') ||
                permsArray.includes('log_view') ||
                (permsArray.includes('admin') &&
                    willSubscriptionModuleVisible('pos'))
            "
        >
            <template #title>
                <span>
                    <DeleteRowOutlined />
                    <span>{{ $t("menu.master") }}</span>
                </span>
            </template>

            <a-menu-item
                v-if="
                    permsArray.includes('factorydelete_view') ||
                    permsArray.includes('admin')
                "
                @click="
                    () => {
                        menuSelected();
                        $router.push({
                            name: 'admin.factorydelete.index',
                        });
                    }
                "
                key="factorydelete"
            >
                <DeleteOutlined />
                {{ $t("menu.factorydelete") }}
            </a-menu-item>
        </a-sub-menu>

        <a-sub-menu
            key="administration"
            v-if="
                permsArray.includes('users_view') ||
                permsArray.includes('expense_categories_view') ||
                permsArray.includes('expenses_view') ||
                (permsArray.includes('admin') && pageTitle != 'Setup Company')
            "
        >
            <template #title>
                <span>
                    <UserOutlined />
                    <span>{{ $t("menu.administration") }}</span>
                </span>
            </template>

            <a-menu-item
                v-if="
                    permsArray.includes('users_view') ||
                    (permsArray.includes('admin') &&
                        pageTitle != 'Setup Company')
                "
                @click="
                    () => {
                        menuSelected();
                        $router.push({ name: 'admin.users.index' });
                    }
                "
                key="users"
            >
                <UserOutlined />
                <span>{{ $t("menu.staff_members") }}</span>
            </a-menu-item>

            <a-menu-item
                v-if="
                    permsArray.includes('product_cards_view') ||
                    (permsArray.includes('admin') &&
                        pageTitle != 'Setup Company')
                "
                @click="menuSelected"
                key="product_cards"
            >
                <router-link
                    :to="{
                        name: 'admin.website-setup.product-cards.index',
                    }"
                >
                    <DropboxOutlined />
                    {{ $t("menu.product_cards") }}
                </router-link>
            </a-menu-item>
            <a-menu-item
                v-if="
                    permsArray.includes('front_settings_edit') ||
                    (permsArray.includes('admin') &&
                        pageTitle != 'Setup Company')
                "
                @click="menuSelected"
                key="front_settings"
            >
                <router-link
                    :to="{
                        name: 'admin.website-setup.front-settings.edit',
                    }"
                >
                    <CodepenOutlined />
                    {{ $t("menu.front_settings") }}
                </router-link>
            </a-menu-item>

            <a-menu-item
                @click="
                    () => {
                        menuSelected();
                        $router.push({ name: 'admin.settings.profile.index' });
                    }
                "
                key="settings"
            >
                <SettingOutlined />
                <span>{{ $t("menu.settings") }}</span>
            </a-menu-item>

            <a-menu-item
                v-if="
                    appType == 'multiple' && appSetting.x_admin_id == user.xid
                "
                @click="
                    () => {
                        menuSelected();
                        $router.push({
                            name: 'admin.subscription.current_plan',
                        });
                    }
                "
                key="subscription"
            >
                <DollarCircleOutlined />
                <span>{{ $t("menu.subscription") }}</span>
            </a-menu-item>
        </a-sub-menu>

        <!-- <a-sub-menu
                        key="expense_manager"
                        v-if="
                            (permsArray.includes('expense_categories_view') ||
                                permsArray.includes('expenses_view') ||
                                (permsArray.includes('admin') &&
                        pageTitle != 'Setup Company')) &&
                            willSubscriptionModuleVisible('expense')
                        "
                    >
                        <template #title>
                            <span>
                                <WalletOutlined />
                                <span>{{ $t("menu.expense_manager") }}</span>
                            </span>
                        </template>

                    </a-sub-menu> -->

        <!-- <a-sub-menu
                        v-if="
                            (permsArray.includes('product_cards_view') ||
                                (permsArray.includes('admin') &&
                        pageTitle != 'Setup Company')) &&
                            willSubscriptionModuleVisible('online_store')
                        "
                        key="website_setup"
                    >
                        <template #title>
                            <span>
                                <RocketOutlined />
                                <span>{{ $t("menu.website_setup") }}</span>
                            </span>
                        </template>

                    </a-sub-menu> -->

        <component
            v-for="(appModule, index) in appModules"
            :key="index"
            v-bind:is="appModule + 'Menu'"
            @menuSelected="menuSelected"
        />

        <!--
                    <a-menu-item @click="logout" key="logout">
                        <LogoutOutlined />
                        <span>{{ $t("menu.logout") }}</span>
                    </a-menu-item> -->
    </a-menu>
</template>

<script>
import { defineComponent, ref, watch, onMounted, computed } from "vue";
import { Layout } from "ant-design-vue";
import { useStore } from "vuex";
import { useRoute } from "vue-router";
import {
    HomeOutlined,
    LogoutOutlined,
    UserOutlined,
    SettingOutlined,
    CloseOutlined,
    ShoppingOutlined,
    ShoppingCartOutlined,
    AppstoreOutlined,
    ShopOutlined,
    BarChartOutlined,
    CalculatorOutlined,
    TeamOutlined,
    WalletOutlined,
    BankOutlined,
    RocketOutlined,
    LaptopOutlined,
    CarOutlined,
    DollarCircleOutlined,
    GoldOutlined,
    ClusterOutlined,
    RollbackOutlined,
    ArrowLeftOutlined,
    SnippetsOutlined,
    PlusOutlined,
    ArrowRightOutlined,
    RetweetOutlined,
    MinusOutlined,
    ApartmentOutlined,
    AccountBookOutlined,
    StockOutlined,
    ShareAltOutlined,
    DotChartOutlined,
    PieChartOutlined,
    AreaChartOutlined,
    BookOutlined,
    RiseOutlined,
    DropboxOutlined,
    CodepenOutlined,
} from "@ant-design/icons-vue";
import common from "../../common/composable/common";
const { Sider } = Layout;

export default defineComponent({
    props: ["collapsed"],
    components: {
        Sider,
        Layout,

        HomeOutlined,
        LogoutOutlined,
        UserOutlined,
        SettingOutlined,
        CloseOutlined,
        ShoppingOutlined,
        ShoppingCartOutlined,
        AppstoreOutlined,
        ShopOutlined,
        BarChartOutlined,
        CalculatorOutlined,
        TeamOutlined,
        WalletOutlined,
        BankOutlined,
        RocketOutlined,
        LaptopOutlined,
        CarOutlined,
        DollarCircleOutlined,
        GoldOutlined,
        ClusterOutlined,
        RollbackOutlined,
        ArrowLeftOutlined,
        SnippetsOutlined,
        PlusOutlined,
        ArrowRightOutlined,
        RetweetOutlined,
        MinusOutlined,
        ApartmentOutlined,
        AccountBookOutlined,
        StockOutlined,
        ShareAltOutlined,
        DotChartOutlined,
        PieChartOutlined,
        AreaChartOutlined,
        BookOutlined,
        RiseOutlined,
        DropboxOutlined,
        CodepenOutlined,
    },
    setup(props, { emit }) {
        const {
            appSetting,
            user,
            appType,
            permsArray,
            appModules,
            cssSettings,
            pageTitle,
            willSubscriptionModuleVisible,
        } = common();
        const rootSubmenuKeys = [
            "dashboard",
            "product_manager",
            "stock_management",
            "pos",
            "stock_adjustment",
            "expense_manager",
            "users",
            "reports",
            "settings",
            "online_orders",
            "website_setup",
        ];
        const store = useStore();
        const route = useRoute();

        const openKeys = ref([]);
        const selectedKeys = ref([]);
        const mode = ref("horizontal");

        onMounted(() => {
            setSelectedKeys(route);
        });

        const logout = () => {
            store.dispatch("auth/logout");
        };

        const menuSelected = () => {
            emit("menuSelected");
        };

        const onOpenChange = (currentOpenKeys) => {
            const latestOpenKey = currentOpenKeys.find(
                (key) => openKeys.value.indexOf(key) === -1
            );

            if (rootSubmenuKeys.indexOf(latestOpenKey) === -1) {
                openKeys.value = currentOpenKeys;
            } else {
                openKeys.value = latestOpenKey ? [latestOpenKey] : [];
            }
        };

        const setSelectedKeys = (newVal) => {
            const menuKey =
                typeof newVal.meta.menuKey == "function"
                    ? newVal.meta.menuKey(newVal)
                    : newVal.meta.menuKey;

            if (newVal.meta.menuParent == "settings") {
                selectedKeys.value = ["settings"];
            } else {
                selectedKeys.value = [menuKey.replace("-", "_")];
            }

            if (cssSettings.value.headerMenuMode == "horizontal") {
                openKeys.value = [];
            } else {
                openKeys.value = [newVal.meta.menuParent];
            }
        };

        watch(route, (newVal, oldVal) => {
            setSelectedKeys(newVal);
        });

        return {
            mode,
            selectedKeys,
            openKeys,
            logout,
            innerWidth: window.innerWidth,
            willSubscriptionModuleVisible,
            onOpenChange,
            menuSelected,
            appType,

            appSetting,
            user,
            permsArray,
            appModules,
            pageTitle,
        };
    },
});
</script>

<style lang="less">
.top-header.ant-menu-dark.ant-menu-horizontal > .ant-menu-item,
.ant-menu-dark.ant-menu-horizontal > .ant-menu-submenu {
    padding: 0px 10px !important;
}
</style>
