import { Category } from '@/types/category';

export interface SubCategory {
    id: number;
    category_id: number;
    name: string;
    slug: string;
    status: boolean;
    created_at: string;
    updated_at: string;
    category: Category;
}
