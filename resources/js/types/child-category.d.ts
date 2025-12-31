import { Category } from '@/types/category';
import { SubCategory } from '@/types/sub-category';

export interface ChildCategory {
    id: number;
    category_id: number;
    sub_category_id: number;
    name: string;
    slug: string;
    status: boolean;
    created_at: string;
    updated_at: string;
    category?: Category;
    sub_category?: SubCategory;
}
