// types/product.ts
export interface Product {
    id: number;
    name: string;
    slug: string;
    thumb_image: string;
    price: string;
    offer_price: string | null;
    qty: number;
    status: boolean;
    is_approved: boolean;
    category: {
        id: number;
        name: string;
    };
    brand: {
        id: number;
        name: string;
    };
}
