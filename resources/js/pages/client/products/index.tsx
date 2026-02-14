import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import AppHeaderLayout from '@/layouts/app/client/app-header-layout';
import { ProductCard } from '@/components/client/product-card';
import { Pagination } from '@/components/pagination';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Search, SlidersHorizontal, X } from 'lucide-react';

interface Review {
  rating: number;
}

interface Product {
  id: number;
  name: string;
  slug: string;
  price: number;
  image_url?: string;
  category?: {
    id: number;
    name: string;
  };
  brand?: {
    id: number;
    name: string;
  };
  reviews_avg_rating?: number;
  reviews_count?: number;
}

interface Category {
  id: number;
  name: string;
  products_count: number;
}

interface Brand {
  id: number;
  name: string;
  products_count: number;
}

interface Filters {
  search?: string;
  category?: number;
  brand?: number;
  min_price?: number;
  max_price?: number;
  sort?: string;
}

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface PaginatedProducts {
  data: Product[];
  links: PaginationLink[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

interface Props {
  products: PaginatedProducts;
  categories: Category[];
  brands: Brand[];
  filters: Filters;
}

export default function ProductsIndex({ products, categories, brands, filters }: Props) {
  const [searchQuery, setSearchQuery] = useState(filters.search || '');
  const [minPrice, setMinPrice] = useState(filters.min_price?.toString() || '');
  const [maxPrice, setMaxPrice] = useState(filters.max_price?.toString() || '');
  const [mobileFiltersOpen, setMobileFiltersOpen] = useState(false);

  const handleFilterChange = (newFilters: Partial<Filters>) => {
    const updatedFilters = { ...filters, ...newFilters };

    // Remove empty filters
    Object.keys(updatedFilters).forEach(key => {
      const value = updatedFilters[key as keyof Filters];
      if (value === '' || value === null || value === undefined) {
        delete updatedFilters[key as keyof Filters];
      }
    });

    router.get('/products', updatedFilters, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  const handleSearchSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    handleFilterChange({ search: searchQuery });
  };

  const handlePriceFilter = () => {
    handleFilterChange({
      min_price: minPrice ? Number(minPrice) : undefined,
      max_price: maxPrice ? Number(maxPrice) : undefined,
    });
  };

  const handleCategoryToggle = (categoryId: number) => {
    if (filters.category === categoryId) {
      handleFilterChange({ category: undefined });
    } else {
      handleFilterChange({ category: categoryId });
    }
  };

  const handleBrandToggle = (brandId: number) => {
    if (filters.brand === brandId) {
      handleFilterChange({ brand: undefined });
    } else {
      handleFilterChange({ brand: brandId });
    }
  };

  const clearFilters = () => {
    setSearchQuery('');
    setMinPrice('');
    setMaxPrice('');
    router.get('/products', {}, { preserveState: true });
  };

  const activeFiltersCount = [
    filters.search,
    filters.category,
    filters.brand,
    filters.min_price,
    filters.max_price,
  ].filter(Boolean).length;

  const FilterSidebar = () => (
    <div className="space-y-6">
      {/* Categories Filter */}
      <Card>
        <CardHeader>
          <CardTitle className="text-base">Категории</CardTitle>
        </CardHeader>
        <CardContent className="space-y-2">
          {categories.map((category) => (
            <div
              key={category.id}
              className="flex items-center justify-between"
            >
              <button
                onClick={() => handleCategoryToggle(category.id)}
                className={`flex-1 text-left text-sm hover:text-primary transition-colors ${
                  filters.category === category.id
                    ? 'font-semibold text-primary'
                    : 'text-muted-foreground'
                }`}
              >
                {category.name}
              </button>
              <Badge variant="secondary" className="text-xs">
                {category.products_count}
              </Badge>
            </div>
          ))}
        </CardContent>
      </Card>

      {/* Brands Filter */}
      <Card>
        <CardHeader>
          <CardTitle className="text-base">Бренды</CardTitle>
        </CardHeader>
        <CardContent className="space-y-2">
          {brands.map((brand) => (
            <div
              key={brand.id}
              className="flex items-center justify-between"
            >
              <button
                onClick={() => handleBrandToggle(brand.id)}
                className={`flex-1 text-left text-sm hover:text-primary transition-colors ${
                  filters.brand === brand.id
                    ? 'font-semibold text-primary'
                    : 'text-muted-foreground'
                }`}
              >
                {brand.name}
              </button>
              <Badge variant="secondary" className="text-xs">
                {brand.products_count}
              </Badge>
            </div>
          ))}
        </CardContent>
      </Card>

      {/* Price Range Filter */}
      <Card>
        <CardHeader>
          <CardTitle className="text-base">Цена</CardTitle>
        </CardHeader>
        <CardContent className="space-y-3">
          <div className="grid grid-cols-2 gap-2">
            <div>
              <Label htmlFor="min-price" className="text-xs">
                От
              </Label>
              <Input
                id="min-price"
                type="number"
                placeholder="0"
                value={minPrice}
                onChange={(e) => setMinPrice(e.target.value)}
                className="h-9"
              />
            </div>
            <div>
              <Label htmlFor="max-price" className="text-xs">
                До
              </Label>
              <Input
                id="max-price"
                type="number"
                placeholder="99999"
                value={maxPrice}
                onChange={(e) => setMaxPrice(e.target.value)}
                className="h-9"
              />
            </div>
          </div>
          <Button
            onClick={handlePriceFilter}
            variant="secondary"
            size="sm"
            className="w-full"
          >
            Применить
          </Button>
        </CardContent>
      </Card>

      {/* Clear Filters */}
      {activeFiltersCount > 0 && (
        <Button
          onClick={clearFilters}
          variant="outline"
          className="w-full"
        >
          <X className="mr-2 h-4 w-4" />
          Сбросить фильтры
        </Button>
      )}
    </div>
  );

  return (
    <AppHeaderLayout>
      <Head title="Каталог" />

      <div className="container mx-auto px-4 py-8">
        {/* Search and Sort Bar */}
        <div className="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <form onSubmit={handleSearchSubmit} className="flex-1 max-w-md">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
              <Input
                type="search"
                placeholder="Поиск товаров..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="pl-10"
              />
            </div>
          </form>

          <div className="flex items-center gap-3">
            {/* Mobile Filter Toggle */}
            <Button
              variant="outline"
              size="sm"
              className="md:hidden"
              onClick={() => setMobileFiltersOpen(!mobileFiltersOpen)}
            >
              <SlidersHorizontal className="mr-2 h-4 w-4" />
              Фильтры
              {activeFiltersCount > 0 && (
                <Badge variant="default" className="ml-2">
                  {activeFiltersCount}
                </Badge>
              )}
            </Button>

            {/* Sort Dropdown */}
            <div className="flex items-center gap-2">
              <Label className="text-sm text-muted-foreground whitespace-nowrap hidden md:inline">
                Сортировка:
              </Label>
              <Select
                value={filters.sort || 'latest'}
                onValueChange={(value) => handleFilterChange({ sort: value })}
              >
                <SelectTrigger className="w-[180px]">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="latest">Новинки</SelectItem>
                  <SelectItem value="price_asc">Цена ↑</SelectItem>
                  <SelectItem value="price_desc">Цена ↓</SelectItem>
                  <SelectItem value="popular">Популярные</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
        </div>

        <div className="flex flex-col md:flex-row gap-6">
          {/* Sidebar - Desktop */}
          <aside className="hidden md:block w-64 flex-shrink-0">
            <FilterSidebar />
          </aside>

          {/* Sidebar - Mobile */}
          {mobileFiltersOpen && (
            <div className="md:hidden mb-6">
              <FilterSidebar />
            </div>
          )}

          {/* Main Content */}
          <div className="flex-1">
            {/* Results Count */}
            <div className="mb-4 text-sm text-muted-foreground">
              Найдено товаров: {products.total}
            </div>

            {/* Product Grid */}
            {products.data.length > 0 ? (
              <>
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
                  {products.data.map((product) => (
                    <ProductCard key={product.id} product={product} />
                  ))}
                </div>

                {/* Pagination */}
                {products.last_page > 1 && (
                  <div className="mt-8">
                    <Pagination currentPage={products.current_page} lastPage={products.last_page} path="/products" />
                  </div>
                )}
              </>
            ) : (
              <Card>
                <CardContent className="py-12 text-center">
                  <p className="text-muted-foreground">
                    Товары не найдены. Попробуйте изменить фильтры.
                  </p>
                  {activeFiltersCount > 0 && (
                    <Button
                      onClick={clearFilters}
                      variant="outline"
                      className="mt-4"
                    >
                      Сбросить фильтры
                    </Button>
                  )}
                </CardContent>
              </Card>
            )}
          </div>
        </div>
      </div>
    </AppHeaderLayout>
  );
}
