export interface DashboardStatistics {
    total_revenue: number;
    today_revenue: number;
    yesterday_revenue: number;
    total_orders: number;
    pending_orders: number;
    total_products: number;
    pending_products: number;
    total_customers: number;
    total_vendors: number;
    pending_vendors: number;
    total_reviews: number;
    pending_reviews: number;
}

export interface OrderStats {
    [key: string]: number;
}

export interface PendingVendor {
    id: number;
    shop_name: string;
    created_at: string;
    user: {
        id: number;
        name: string;
        email: string;
        avatar?: string;
    };
}

export interface PendingProduct {
    id: number;
    name: string;
    thumb_image: string;
    price: number;
    created_at: string;
    vendor: {
        user: {
            id: number;
            name: string;
        };
    } | null;
    category: {
        id: number;
        name: string;
    };
}

export interface RecentOrder {
    id: number;
    invoice_id: number;
    amount: number;
    product_quantity: number;
    payment_method: string;
    payment_status: boolean;
    order_status: string;
    created_at: string;
    user: {
        id: number;
        name: string;
        email: string;
    };
}

export interface PendingReview {
    id: number;
    review: string;
    rating: number;
    created_at: string;
    product: {
        id: number;
        name: string;
    };
    user: {
        id: number;
        name: string;
    };
}

export interface DashboardProps {
    statistics: DashboardStatistics;
    orderStats: OrderStats;
    pendingVendors: PendingVendor[];
    pendingProducts: PendingProduct[];
    recentOrders: RecentOrder[];
    pendingReviews: PendingReview[];
}
