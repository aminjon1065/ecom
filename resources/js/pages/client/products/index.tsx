import { Head, router } from '@inertiajs/react';
import { useCallback, useEffect, useRef, useState } from 'react';
import AppHeaderLayout from '@/layouts/app/client/app-header-layout';
import { ProductCard } from '@/components/client/product-card';
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
import { Loader2, Search, SlidersHorizontal, X } from 'lucide-react';

interface Product {
  id: number;
  name: string;
  slug: string;
  thumb_image: string;
  price: number;
  offer_price?: number | null;
  offer_start_date?: string | null;
  offer_end_date?: string | null;
  product_type?: string | null;
  category?: {
    id: number;
    name: string;
  };
  brand?: {
    id: number;
    name: string;
  };
  reviews_avg_rating?: number | null;
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
  category?: number | string;
  brand?: number | string;
  min_price?: number | string;
  max_price?: number | string;
  sort?: string;
}

interface ProductsMeta {
  current_page: number;
  last_page: number;
  total: number;
}

interface Props {
  products: Product[];
  productsMeta: ProductsMeta;
  categories: Category[];
  brands: Brand[];
  filters: Filters;
}

export default function ProductsIndex({ products, productsMeta, categories, brands, filters: rawFilters }: Props) {
  const normalizedFilters = (rawFilters && !Array.isArray(rawFilters) ? rawFilters : {}) as Filters;
  const filters: Filters = {
    ...normalizedFilters,
    category: normalizedFilters.category ? Number(normalizedFilters.category) : undefined,
    brand: normalizedFilters.brand ? Number(normalizedFilters.brand) : undefined,
    min_price: normalizedFilters.min_price ?? undefined,
    max_price: normalizedFilters.max_price ?? undefined,
  };
  const [searchQuery, setSearchQuery] = useState(filters.search || '');
  const [minPrice, setMinPrice] = useState(filters.min_price?.toString() || '');
  const [maxPrice, setMaxPrice] = useState(filters.max_price?.toString() || '');
  const [sortValue, setSortValue] = useState(filters.sort || 'latest');
  const [mobileFiltersOpen, setMobileFiltersOpen] = useState(false);
  const [loading, setLoading] = useState(false);
  const loadMoreRef = useRef<HTMLDivElement>(null);
  const observerInitializedRef = useRef(false);
  const blockInfiniteScrollUntilRef = useRef(0);

  const hasMore = productsMeta.current_page < productsMeta.last_page;

  const loadMore = useCallback(() => {
    if (loading || !hasMore) return;

    setLoading(true);
    router.get(
      '/products',
      { ...filters, page: productsMeta.current_page + 1 },
      {
        preserveState: true,
        preserveScroll: true,
        only: ['products', 'productsMeta'],
        onFinish: () => setLoading(false),
      },
    );
  }, [loading, hasMore, filters, productsMeta.current_page]);

  useEffect(() => {
    setSearchQuery(filters.search || '');
    setMinPrice(filters.min_price?.toString() || '');
    setMaxPrice(filters.max_price?.toString() || '');
    setSortValue(filters.sort || 'latest');
  }, [filters.search, filters.min_price, filters.max_price, filters.sort]);

  useEffect(() => {
    const el = loadMoreRef.current;
    if (!el) return;
    observerInitializedRef.current = false;

    const observer = new IntersectionObserver(
      (entries) => {
        if (entries[0].isIntersecting) {
          if (Date.now() < blockInfiniteScrollUntilRef.current) {
            return;
          }
          if (!observerInitializedRef.current) {
            observerInitializedRef.current = true;
            return;
          }
          loadMore();
        } else {
          observerInitializedRef.current = true;
        }
      },
      { rootMargin: '200px' },
    );

    observer.observe(el);
    return () => observer.disconnect();
  }, [loadMore]);

  const handleFilterChange = (newFilters: Partial<Filters>) => {
    const updatedFilters = { ...filters, ...newFilters };

    Object.keys(updatedFilters).forEach(key => {
      const value = updatedFilters[key as keyof Filters];
      if (value === '' || value === null || value === undefined) {
        delete updatedFilters[key as keyof Filters];
      }
    });

    router.get('/products', updatedFilters, {
      preserveState: true,
      preserveScroll: false,
      reset: ['products'],
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
    setSortValue('latest');
    router.get('/products', {}, { preserveState: false });
  };

  const activeFiltersCount = [
    filters.search,
    filters.category,
    filters.brand,
    filters.min_price,
    filters.max_price,
  ].filter(Boolean).length;
  const selectedCategory = categories.find((category) => category.id === filters.category);
  const selectedBrand = brands.find((brand) => brand.id === filters.brand);
  const activeFilterChips: { key: string; label: string; clear: () => void }[] = [];

  if (filters.search) {
    activeFilterChips.push({
      key: 'search',
      label: `Поиск: ${filters.search}`,
      clear: () => {
        setSearchQuery('');
        handleFilterChange({ search: undefined });
      },
    });
  }

  if (selectedCategory) {
    activeFilterChips.push({
      key: 'category',
      label: `Категория: ${selectedCategory.name}`,
      clear: () => handleFilterChange({ category: undefined }),
    });
  }

  if (selectedBrand) {
    activeFilterChips.push({
      key: 'brand',
      label: `Бренд: ${selectedBrand.name}`,
      clear: () => handleFilterChange({ brand: undefined }),
    });
  }

  if (filters.min_price || filters.max_price) {
    activeFilterChips.push({
      key: 'price',
      label: `Цена: ${filters.min_price ?? 0} - ${filters.max_price ?? '∞'} сом.`,
      clear: () => {
        setMinPrice('');
        setMaxPrice('');
        handleFilterChange({ min_price: undefined, max_price: undefined });
      },
    });
  }

  if (filters.sort && filters.sort !== 'latest') {
    const sortLabel = filters.sort === 'price_asc'
      ? 'Цена ↑'
      : filters.sort === 'price_desc'
        ? 'Цена ↓'
        : 'Популярные';

    activeFilterChips.push({
      key: 'sort',
      label: `Сортировка: ${sortLabel}`,
      clear: () => {
        setSortValue('latest');
        handleFilterChange({ sort: 'latest' });
      },
    });
  }

  const FilterSidebar = () => (
    <div className="space-y-6">
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

            <div className="flex items-center gap-2">
              <Label className="text-sm text-muted-foreground whitespace-nowrap hidden md:inline">
                Сортировка:
              </Label>
              <Select
                value={sortValue}
                onValueChange={(value) => {
                  setSortValue(value);
                  handleFilterChange({ sort: value });
                }}
              >
                <SelectTrigger className="w-45">
                  <SelectValue placeholder="Новинки" />
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

        <div className="mb-4 flex flex-wrap gap-2">
          <Button
            variant={sortValue === 'latest' ? 'default' : 'outline'}
            size="sm"
            onClick={() => {
              setSortValue('latest');
              handleFilterChange({ sort: 'latest' });
            }}
          >
            Новинки
          </Button>
          <Button
            variant={sortValue === 'popular' ? 'default' : 'outline'}
            size="sm"
            onClick={() => {
              setSortValue('popular');
              handleFilterChange({ sort: 'popular' });
            }}
          >
            Популярные
          </Button>
          <Button
            variant={sortValue === 'price_asc' ? 'default' : 'outline'}
            size="sm"
            onClick={() => {
              setSortValue('price_asc');
              handleFilterChange({ sort: 'price_asc' });
            }}
          >
            Сначала дешевле
          </Button>
          <Button
            variant={sortValue === 'price_desc' ? 'default' : 'outline'}
            size="sm"
            onClick={() => {
              setSortValue('price_desc');
              handleFilterChange({ sort: 'price_desc' });
            }}
          >
            Сначала дороже
          </Button>
        </div>

        <div className="flex flex-col md:flex-row gap-6">
          <aside className="hidden md:block w-64 shrink-0">
            <FilterSidebar />
          </aside>

          {mobileFiltersOpen && (
            <div className="md:hidden mb-6">
              <FilterSidebar />
            </div>
          )}

          <div className="flex-1">
            <div className="mb-4 text-sm text-muted-foreground">
              Найдено товаров: {productsMeta.total}
            </div>
            {activeFilterChips.length > 0 && (
              <div className="mb-4 flex flex-wrap gap-2">
                {activeFilterChips.map((chip) => (
                  <button
                    key={chip.key}
                    onClick={chip.clear}
                    className="inline-flex items-center gap-1 rounded-full border bg-muted px-3 py-1 text-xs transition-colors hover:bg-muted/70"
                  >
                    {chip.label}
                    <X className="h-3.5 w-3.5" />
                  </button>
                ))}
                <Button
                  size="sm"
                  variant="ghost"
                  className="h-7 px-2 text-xs"
                  onClick={clearFilters}
                >
                  Сбросить всё
                </Button>
              </div>
            )}

            {products.length > 0 ? (
              <>
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                  {products.map((product) => (
                    <ProductCard
                      key={product.id}
                      product={product}
                      onQuickAction={() => {
                        blockInfiniteScrollUntilRef.current = Date.now() + 2000;
                      }}
                    />
                  ))}
                </div>

                <div ref={loadMoreRef} className="flex justify-center py-8">
                  {loading && (
                    <Loader2 className="h-6 w-6 animate-spin text-muted-foreground" />
                  )}
                </div>
              </>
            ) : (
              <Card>
                <CardContent className="py-12 text-center">
                  <p className="font-medium">Товары не найдены</p>
                  <p className="mt-2 text-sm text-muted-foreground">
                    Попробуйте расширить диапазон цены, убрать часть фильтров или выбрать другую категорию.
                  </p>
                  <div className="mt-4 flex flex-wrap justify-center gap-2">
                    {activeFiltersCount > 0 && (
                      <Button
                        onClick={clearFilters}
                        variant="outline"
                      >
                        Сбросить фильтры
                      </Button>
                    )}
                    <Button
                      variant="ghost"
                      onClick={() => {
                        setSearchQuery('');
                        handleFilterChange({ search: undefined });
                      }}
                    >
                      Очистить поиск
                    </Button>
                    <Button
                      variant="ghost"
                      onClick={() => {
                        setSortValue('popular');
                        handleFilterChange({ sort: 'popular' });
                      }}
                    >
                      Показать популярные
                    </Button>
                  </div>
                </CardContent>
              </Card>
            )}
          </div>
        </div>
      </div>
    </AppHeaderLayout>
  );
}
