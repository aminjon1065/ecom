// types/product.ts
export interface Product {
    id: number;
    name: string;
    slug: string;
    thumb_image: string;
    price: string;
    offer_price: string | null;
    qty: number;
    sku: string | null;
    code: number;
    status: boolean;
    is_approved: boolean;
    first_source_link?: string | null;
    second_source_link?: string | null;
    category: {
        id: number;
        name: string;
    };
    brand: {
        id: number;
        name: string;
    };
}
