<template>
    <a-layout-sider
        :width="240"
        :style="{
            margin: '0 0 0 0',
            overflowY: 'auto',
            position: 'fixed',
            paddingTop: '8px',
            zIndex: 998,
            height: '100%',
        }"
        :trigger="null"
        :collapsed="menuCollapsed"
        :theme="appSetting.left_sidebar_theme"
        class="sidebar-right-border"
    >
        <div v-if="menuCollapsed" class="logo">
            <img
                :style="{
                    width: '100%',
                }"
                :src="
                    appSetting.left_sidebar_theme == 'dark'
                        ? appSetting.small_dark_logo_url
                        : appSetting.small_light_logo_url
                "
            />
        </div>
        <div
            style="display: flex; justify-content: center; align-items: center"
            v-else
        >
            <img
                :style="{
                    width: '100%',
                    scale: '.8',
                }"
                :src="
                    appSetting.left_sidebar_theme == 'dark'
                        ? appSetting.dark_logo_url
                        : appSetting.light_logo_url
                "
            />
            <CloseOutlined
                v-if="innerWidth <= 991"
                :style="{
                    marginLeft: appSetting.rtl ? '0px' : '45px',
                    marginRight: appSetting.rtl ? '45px' : '0px',
                    verticalAlign: 'super',
                    color:
                        appSetting.left_sidebar_theme == 'dark'
                            ? '#fff'
                            : '#000000',
                }"
                @click="menuSelected"
            />
        </div>

        <div class="main-sidebar">
            <perfect-scrollbar
                :options="{
                    wheelSpeed: 1,
                    swipeEasing: true,
                    suppressScrollX: true,
                }"
            >
                <a-menu
                    v-if="pageTitle != 'Setup Company'"
                    :theme="appSetting.left_sidebar_theme"
                    :openKeys="openKeys"
                    v-model:selectedKeys="selectedKeys"
                    :mode="mode"
                    @openChange="onOpenChange"
                    :style="{ borderRight: 'none' }"
                >
                    <a-menu-item
                        @click="
                            () => {
                                menuSelected();
                                $router.push({ name: 'admin.dashboard.index' });
                            }
                        "
                        key="dashboard"
                    >
                        <HomeOutlined />
                        <span>{{ $t("menu.dashboard") }}</span>
                    </a-menu-item>

                    <a-sub-menu
                        v-if="
                            permsArray.includes('customers_view') ||
                            permsArray.includes('suppliers_view') ||
                            permsArray.includes('admin')
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
                                permsArray.includes('admin')
                            "
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({
                                        name: 'admin.customers.index',
                                    });
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
                                permsArray.includes('admin')
                            "
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({
                                        name: 'admin.suppliers.index',
                                    });
                                }
                            "
                            key="suppliers"
                        >
                            <TeamOutlined />
                            {{ $t("menu.suppliers") }}
                        </a-menu-item>
                        <a-menu-item
                            v-if="
                                permsArray.includes('referral_view') ||
                                permsArray.includes('admin')
                            "
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({
                                        name: 'admin.referral.index',
                                    });
                                }
                            "
                            key="referral"
                        >
                            <TeamOutlined />
                            {{ $t("menu.referral") }}
                        </a-menu-item>
                    </a-sub-menu>

                    <a-sub-menu
                        key="product_manager"
                        v-if="
                            permsArray.includes('brands_view') ||
                            permsArray.includes('categories_view') ||
                            permsArray.includes('products_view') ||
                            permsArray.includes('admin')
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
                                    $router.push({
                                        name: 'admin.brands.index',
                                    });
                                }
                            "
                            key="brands"
                            v-if="
                                permsArray.includes('brands_view') ||
                                permsArray.includes('admin')
                            "
                        >
                            <GoldOutlined />
                            {{ $t("menu.brands") }}
                        </a-menu-item>
                        <a-menu-item
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({
                                        name: 'admin.categories.index',
                                    });
                                }
                            "
                            key="categories"
                            v-if="
                                permsArray.includes('categories_view') ||
                                permsArray.includes('admin')
                            "
                        >
                            <ClusterOutlined />
                            {{ $t("menu.categories") }}
                        </a-menu-item>
                        <a-menu-item
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({
                                        name: 'admin.products.index',
                                    });
                                }
                            "
                            key="products"
                            v-if="
                                permsArray.includes('products_view') ||
                                permsArray.includes('admin')
                            "
                        >
                            <ShoppingOutlined />
                            {{ $t("menu.products") }}
                        </a-menu-item>
                        <a-menu-item
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({
                                        name: 'admin.barcode.index',
                                    });
                                }
                            "
                            key="barcode"
                            v-if="
                                permsArray.includes('products_view') ||
                                permsArray.includes('admin')
                            "
                        >
                            <ShoppingOutlined />
                            {{ $t("menu.barcode") }}
                        </a-menu-item>
                    </a-sub-menu>

                    <a-sub-menu
                        key="sales"
                        v-if="
                            permsArray.includes('sales_view') ||
                            permsArray.includes('quotations_view') ||
                            permsArray.includes('sales_returns_view') ||
                            permsArray.includes('payment_in_view') ||
                            permsArray.includes('admin')
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
                                        name: 'admin.pos.index',
                                    });
                                }
                            "
                            key="pos"
                            v-if="
                                permsArray.includes('sales_view') ||
                                permsArray.includes('admin')
                            "
                        >
                            <ShoppingCartOutlined />
                            {{ $t("menu.pos") }}
                        </a-menu-item>
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
                                permsArray.includes('admin')
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
                                    permsArray.includes('admin')) &&
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
                                permsArray.includes('admin')
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
                                (permsArray.includes('quotations_view') ||
                                    permsArray.includes('admin')) &&
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
                                    permsArray.includes('admin')) &&
                                willSubscriptionModuleVisible('online_store')
                            "
                        >
                            <router-link
                                :to="{ name: 'admin.online_orders.index' }"
                            >
                                <LaptopOutlined />
                                <span>{{ $t("menu.online_orders") }}</span>
                            </router-link>
                        </a-menu-item>
                    </a-sub-menu>

                    <!-- <a-sub-menu
                        key="coupon"
                        v-if="permsArray.includes('admin')"
                    >
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
                    </a-sub-menu> -->

                    <a-sub-menu
                        key="purchases"
                        v-if="
                            permsArray.includes('purchases_view') ||
                            permsArray.includes('purchase_returns_view') ||
                            permsArray.includes('payment_out_view') ||
                            permsArray.includes('admin')
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
                                permsArray.includes('admin')
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
                                    permsArray.includes('admin')) &&
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
                                permsArray.includes('admin')
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
                            permsArray.includes('admin')
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
                                    permsArray.includes('admin')) &&
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
                                    $router.push({
                                        name: 'admin.stock_adjustments.index',
                                    });
                                }
                            "
                            key="stock_adjustment"
                            v-if="
                                (permsArray.includes(
                                    'stock_adjustments_view'
                                ) ||
                                    permsArray.includes('admin')) &&
                                willSubscriptionModuleVisible(
                                    'stock_adjustment'
                                )
                            "
                        >
                            <CalculatorOutlined />
                            <span>{{ $t("menu.stock_adjustment") }}</span>
                        </a-menu-item>
                    </a-sub-menu>

                    <!-- <a-menu-item
                        v-if="
                            (permsArray.includes('pos_view') ||
                                permsArray.includes('admin')) &&
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
                    </a-menu-item> -->

                    <a-sub-menu
                        key="finance"
                        v-if="
                            permsArray.includes('cash_bank_view') ||
                            permsArray.includes('expense_categories_view') ||
                            permsArray.includes('expenses_view') ||
                            (permsArray.includes('admin') &&
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
                                permsArray.includes('admin')
                            "
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({
                                        name: 'admin.reports.cash_bank.index',
                                    });
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
                                permsArray.includes(
                                    'expense_categories_view'
                                ) || permsArray.includes('admin')
                            "
                        >
                            <ApartmentOutlined />
                            {{ $t("menu.expense_categories") }}
                        </a-menu-item>
                        <a-menu-item
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({
                                        name: 'admin.expenses.index',
                                    });
                                }
                            "
                            key="expenses"
                            v-if="
                                permsArray.includes('expenses_view') ||
                                permsArray.includes('admin')
                            "
                        >
                            <AccountBookOutlined />
                            {{ $t("menu.expenses") }}
                        </a-menu-item>
                    </a-sub-menu>

                    <a-sub-menu
                        v-if="
                            permsArray.includes('payment_report') ||
                            permsArray.includes('stock_alert_report') ||
                            permsArray.includes('sales_summary_report') ||
                            permsArray.includes('stock_summary_report') ||
                            permsArray.includes('rate_list_report') ||
                            permsArray.includes('product_expiry_report') ||
                            permsArray.includes(
                                'product_sales_summary_report'
                            ) ||
                            permsArray.includes('sales_margin_report') ||
                            permsArray.includes('gst_report') ||
                            permsArray.includes('user_report') ||
                            permsArray.includes('referral_report') ||
                            permsArray.includes('staff_report') ||
                            permsArray.includes('staff_onboarding_report') ||
                            permsArray.includes('day_report') ||
                            permsArray.includes('attendance_report') ||
                            permsArray.includes('category_wise_report') ||
                            permsArray.includes(
                                'customer_product_wise_report'
                            ) ||
                            permsArray.includes('profit_loss_report') ||
                            permsArray.includes('admin')
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
                                permsArray.includes('payment_report') ||
                                permsArray.includes('admin')
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
                                permsArray.includes('stock_alert_report') ||
                                permsArray.includes('admin')
                            "
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({
                                        name: 'admin.reports.stock.index',
                                    });
                                }
                            "
                            key="stock_alert"
                        >
                            <ShareAltOutlined />
                            {{ $t("menu.stock_alert") }}
                        </a-menu-item>
                        <a-menu-item
                            v-if="
                                permsArray.includes('sales_summary_report') ||
                                permsArray.includes('admin')
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
                                permsArray.includes('stock_summary_report') ||
                                permsArray.includes('admin')
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
                                permsArray.includes('rate_list_report') ||
                                permsArray.includes('admin')
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
                                permsArray.includes('product_expiry_report') ||
                                permsArray.includes('admin')
                            "
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({
                                        name: 'admin.reports.product_expiry.index',
                                    });
                                }
                            "
                            key="product_expiry"
                        >
                            <CalendarOutlined />
                            {{ $t("menu.product_expiry") }}
                        </a-menu-item>
                        <a-menu-item
                            v-if="
                                permsArray.includes(
                                    'product_sales_summary_report'
                                ) || permsArray.includes('admin')
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
                                permsArray.includes('sales_margin_report') ||
                                permsArray.includes('admin')
                            "
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({
                                        name: 'admin.reports.sales_margin.index',
                                    });
                                }
                            "
                            key="sales_margin"
                        >
                            <UpCircleOutlined />
                            {{ $t("menu.sales_margin") }}
                        </a-menu-item>
                        <a-menu-item
                            v-if="
                                permsArray.includes('gst_report') ||
                                permsArray.includes('admin')
                            "
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({
                                        name: 'admin.reports.gst_report.index',
                                    });
                                }
                            "
                            key="gst_report"
                        >
                            <TableOutlined />
                            GST Report
                        </a-menu-item>
                        <a-menu-item
                            v-if="
                                permsArray.includes('user_report') ||
                                permsArray.includes('admin')
                            "
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({
                                        name: 'admin.reports.users.index',
                                    });
                                }
                            "
                            key="users_reports"
                        >
                            <AreaChartOutlined />
                            {{ $t("menu.users_reports") }}
                        </a-menu-item>
                        <a-menu-item
                            v-if="
                                permsArray.includes('referral_report') ||
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
                            {{ $t("menu.referral_report") }}
                        </a-menu-item>
                        <a-menu-item
                            v-if="
                                permsArray.includes('staff_report') ||
                                (permsArray.includes('admin') &&
                                    pageTitle != 'Setup Company')
                            "
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({
                                        name: 'admin.reports.staff_report.index',
                                    });
                                }
                            "
                            key="staff_report"
                        >
                            <SolutionOutlined />
                            {{ $t("menu.staff") }}
                        </a-menu-item>
                        <a-menu-item
                            v-if="
                                permsArray.includes(
                                    'staff_onboarding_report'
                                ) || permsArray.includes('admin')
                            "
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({
                                        name: 'admin.reports.assigned_staff_report.index',
                                    });
                                }
                            "
                            key="assigned_staff_report"
                        >
                            <UserAddOutlined />
                            {{ $t("menu.assigned_staff_report") }}
                        </a-menu-item>
                        <a-menu-item
                            v-if="
                                permsArray.includes('day_report') ||
                                permsArray.includes('admin')
                            "
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({
                                        name: 'admin.reports.daily_report.index',
                                    });
                                }
                            "
                            key="daily_report"
                        >
                            <AreaChartOutlined />
                            {{ $t("menu.daily_report") }}
                        </a-menu-item>

                        <a-menu-item
                            v-if="
                                permsArray.includes('attendance_report') ||
                                permsArray.includes('admin')
                            "
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({
                                        name: 'admin.reports.check_in_report.index',
                                    });
                                }
                            "
                            key="check_in_report"
                        >
                            <CalendarOutlined />
                            {{ $t("menu.check_in_report") }}
                        </a-menu-item>

                        <a-menu-item
                            v-if="
                                permsArray.includes('category_wise_report') ||
                                permsArray.includes('admin')
                            "
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({
                                        name: 'admin.reports.category_report.index',
                                    });
                                }
                            "
                            key="category_report"
                        >
                            <CalendarOutlined />
                            {{ $t("menu.category_report") }}
                        </a-menu-item>
                        <a-menu-item
                            v-if="
                                permsArray.includes('customer_product_wise_report') ||
                                permsArray.includes('admin')
                            "
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({
                                        name: 'admin.reports.customer_product_report.index',
                                    });
                                }
                            "
                            key="customer_product_report"
                        >
                            <CalendarOutlined />
                            {{ $t("menu.customer_product_report") }}
                        </a-menu-item>
                        <a-menu-item
                        v-if="
                                permsArray.includes('profit_loss_report') ||
                                permsArray.includes('admin')
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
                    </a-sub-menu>

                    <a-sub-menu
                        key="administration"
                        v-if="pageTitle != 'Setup Company'"
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
                                permsArray.includes('admin')
                            "
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({ name: 'admin.users.index' });
                                }
                            "
                            key="staff_members"
                        >
                            <UserOutlined />
                            <span>{{ $t("menu.staff_members") }}</span>
                        </a-menu-item>
                        <a-menu-item
                            v-if="
                                permsArray.includes('delivery_partner_view') ||
                                permsArray.includes('admin')
                            "
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({
                                        name: 'admin.delivery_partner.index',
                                    });
                                }
                            "
                            key="delivery_partner"
                        >
                            <TeamOutlined />
                            {{ $t("menu.delivery_partner") }}
                        </a-menu-item>

                        <a-menu-item
                            v-if="
                                permsArray.includes('product_cards_view') ||
                                permsArray.includes('admin')
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
                                permsArray.includes('front_settings_view') ||
                                permsArray.includes('admin')
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
                                    $router.push({
                                        name: 'admin.settings.profile.index',
                                    });
                                }
                            "
                            key="settings"
                        >
                            <SettingOutlined />
                            <span>{{ $t("menu.settings") }}</span>
                        </a-menu-item>

                        <!-- <a-menu-item
                            v-if="
                                appType == 'multiple' &&
                                appSetting.x_admin_id == user.xid
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
                        </a-menu-item> -->
                    </a-sub-menu>

                    <!-- <a-sub-menu
                        key="expense_manager"
                        v-if="
                            (permsArray.includes('expense_categories_view') ||
                                permsArray.includes('expenses_view') ||
                                permsArray.includes('admin')) &&
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
                                permsArray.includes('admin')) &&
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
            </perfect-scrollbar>
        </div>
    </a-layout-sider>
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
    FolderViewOutlined,
    DeleteRowOutlined,
    DeleteOutlined,
    StockOutlined,
    ShareAltOutlined,
    DotChartOutlined,
    PieChartOutlined,
    AreaChartOutlined,
    BookOutlined,
    RiseOutlined,
    DropboxOutlined,
    CodepenOutlined,
    SolutionOutlined,
    TableOutlined,
    CalendarOutlined,
    UpCircleOutlined,
    UserAddOutlined,
} from "@ant-design/icons-vue";
import common from "../../common/composable/common";
const { Sider } = Layout;

export default defineComponent({
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
        FolderViewOutlined,
        DeleteRowOutlined,
        DeleteOutlined,
        StockOutlined,
        ShareAltOutlined,
        DotChartOutlined,
        PieChartOutlined,
        AreaChartOutlined,
        BookOutlined,
        RiseOutlined,
        DropboxOutlined,
        CodepenOutlined,
        SolutionOutlined,
        CalendarOutlined,
        TableOutlined,
        UpCircleOutlined,
        UserAddOutlined,
    },
    setup(props, { emit }) {
        const {
            appSetting,
            appType,
            user,
            permsArray,
            appModules,
            menuCollapsed,
            pageTitle,
            willSubscriptionModuleVisible,
        } = common();
        const rootSubmenuKeys = [
            "dashboard",
            "product_manager",
            "stock_management",
            "pos",
            "stock_transfer",
            "stock_adjustment",
            "sales",
            "purchases",
            "expense_manager",
            "users",
            "parties",
            "reports",
            "settings",
            "online_orders",
            "website_setup",
            "cash_bank",
            "subscription",
            "inventory",
            "finance",
            "administration",
            "crm",
        ];
        const store = useStore();
        const route = useRoute();

        const innerWidth = window.innerWidth;
        const openKeys = ref([]);
        const selectedKeys = ref([]);
        const mode = ref("inline");

        onMounted(() => {
            var menuKey =
                typeof route.meta.menuKey == "function"
                    ? route.meta.menuKey(route)
                    : route.meta.menuKey;

            if (route.meta.menuParent == "settings") {
                menuKey = "settings";
            }

            if (route.meta.menuParent == "subscription") {
                menuKey = "subscription";
            }

            if (innerWidth <= 991) {
                openKeys.value = [];
            } else {
                openKeys.value = menuCollapsed.value
                    ? []
                    : [route.meta.menuParent];
            }

            selectedKeys.value = [menuKey.replace("-", "_")];
        });

        const logout = () => {
            store.dispatch("auth/logout");
        };

        const menuSelected = () => {
            if (innerWidth <= 991) {
                store.commit("auth/updateMenuCollapsed", true);
            }
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

        watch(route, (newVal, oldVal) => {
            const menuKey =
                typeof newVal.meta.menuKey == "function"
                    ? newVal.meta.menuKey(newVal)
                    : newVal.meta.menuKey;

            if (innerWidth <= 991) {
                openKeys.value = [];
            } else {
                openKeys.value = [newVal.meta.menuParent];
            }

            if (newVal.meta.menuParent == "settings") {
                selectedKeys.value = ["settings"];
            } else if (newVal.meta.menuParent == "subscription") {
                selectedKeys.value = ["subscription"];
            } else {
                selectedKeys.value = [menuKey.replace("-", "_")];
            }
        });

        watch(
            () => menuCollapsed.value,
            (newVal, oldVal) => {
                const menuKey =
                    typeof route.meta.menuKey == "function"
                        ? route.meta.menuKey(route)
                        : route.meta.menuKey;

                if (innerWidth <= 991 && menuCollapsed.value) {
                    openKeys.value = [];
                } else {
                    openKeys.value = menuCollapsed.value
                        ? []
                        : [route.meta.menuParent];
                }

                if (
                    route.meta.menuParent &&
                    route.meta.menuParent == "settings"
                ) {
                    selectedKeys.value = ["settings"];
                } else if (
                    route.meta.menuParent &&
                    route.meta.menuParent == "subscription"
                ) {
                    selectedKeys.value = ["subscription"];
                } else {
                    selectedKeys.value = [menuKey.replace("-", "_")];
                }
            }
        );

        return {
            mode,
            selectedKeys,
            openKeys,
            logout,
            innerWidth: window.innerWidth,

            onOpenChange,
            menuSelected,
            menuCollapsed,
            appSetting,
            appType,
            user,
            permsArray,
            pageTitle,
            appModules,
            willSubscriptionModuleVisible,
        };
    },
});
</script>

<style lang="less">
.main-sidebar .ps {
    height: calc(100vh - 180px);
}

@media only screen and (max-width: 1150px) {
    .ant-layout-sider.ant-layout-sider-collapsed {
        left: -80px !important;
    }
}
.ant-menu-vertical-left .ant-menu-submenu,
.ant-menu-vertical-right .ant-menu-submenu,
.ant-menu-inline .ant-menu-submenu {
    padding-bottom: 0.02px;
    border-bottom: 1px #374047 solid !important;
}

.sidebar-right-border > .ant-layout-sider-children > .logo {
    width: 100% !important;
    margin: 0 !important;
    height: unset !important;
    padding: 20px 5px !important;
}
</style>
