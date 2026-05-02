<?php

// Shop page - displays all products with filtering and sorting options
require __DIR__ . '/includes/bootstrap.php';

$selectedCategory = isset($_GET['category']) ? $_GET['category'] : 'All';
$selectedSize = isset($_GET['size']) ? $_GET['size'] : 'All';
$selectedCondition = isset($_GET['condition']) ? $_GET['condition'] : 'All';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'featured';

$products = sort_products(filter_products($selectedCategory, $selectedSize, $selectedCondition), $sortBy);
$activeFiltersCount = count(array_filter(array($selectedCategory, $selectedSize, $selectedCondition), static function ($value) {
    return $value !== 'All';
}));

render_page_start([
    'title' => 'Shop All | Pastimes',
    'page' => 'Shop',
]);
?>
<section class="section section--compact">
    <div class="container">
        <div class="page-heading">
            <h1>Shop All</h1>
            <p>Browse our curated collection of secondhand treasures</p>
        </div>

        <div class="filters-bar">
            <div class="filters-bar__left">
                <button class="button button--outline button--small filters-toggle" type="button" data-filters-toggle>
                    <?= site_icon('sliders-horizontal', 'icon icon--small') ?>
                    Filters
                    <span class="filters-toggle__count<?= $activeFiltersCount > 0 ? '' : ' is-hidden' ?>" data-active-filters><?= h((string) $activeFiltersCount) ?></span>
                </button>

                <div class="filters-desktop">
                    <select class="field-select" data-filter-control="category">
                        <?php foreach (site_categories() as $category): ?>
                            <option value="<?= h($category) ?>"<?= selected_attr($selectedCategory, $category) ?>>
                                <?= h($category === 'All' ? 'All Categories' : $category) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select class="field-select" data-filter-control="size">
                        <?php foreach (site_sizes() as $size): ?>
                            <option value="<?= h($size) ?>"<?= selected_attr($selectedSize, $size) ?>>
                                <?= h($size === 'All' ? 'All Sizes' : 'Size ' . $size) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select class="field-select" data-filter-control="condition">
                        <?php foreach (site_conditions() as $condition): ?>
                            <option value="<?= h($condition) ?>"<?= selected_attr($selectedCondition, $condition) ?>>
                                <?= h($condition === 'All' ? 'All Conditions' : $condition) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="text-button<?= $activeFiltersCount > 0 ? '' : ' is-hidden' ?>" type="button" data-clear-filters>
                        <?= site_icon('x', 'icon icon--tiny') ?> Clear filters
                    </button>
                </div>
            </div>

            <div class="filters-bar__right">
                <span class="filters-count"><span data-results-count><?= h((string) count($products)) ?></span> items</span>
                <select class="field-select" data-sort-control>
                    <option value="featured"<?= selected_attr($sortBy, 'featured') ?>>Featured</option>
                    <option value="new"<?= selected_attr($sortBy, 'new') ?>>New Arrivals</option>
                    <option value="price-low"<?= selected_attr($sortBy, 'price-low') ?>>Price: Low to High</option>
                    <option value="price-high"<?= selected_attr($sortBy, 'price-high') ?>>Price: High to Low</option>
                </select>
            </div>
        </div>

        <div class="filters-mobile" data-filters-panel>
            <div class="filters-mobile__top">
                <span>Filters</span>
                <button class="text-button<?= $activeFiltersCount > 0 ? '' : ' is-hidden' ?>" type="button" data-clear-filters>
                    Clear all
                </button>
            </div>
            <div class="filters-mobile__grid">
                <label>
                    <span>Category</span>
                    <select class="field-select" data-filter-control="category">
                        <?php foreach (site_categories() as $category): ?>
                            <option value="<?= h($category) ?>"<?= selected_attr($selectedCategory, $category) ?>>
                                <?= h($category === 'All' ? 'All Categories' : $category) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span>Size</span>
                    <select class="field-select" data-filter-control="size">
                        <?php foreach (site_sizes() as $size): ?>
                            <option value="<?= h($size) ?>"<?= selected_attr($selectedSize, $size) ?>>
                                <?= h($size === 'All' ? 'All Sizes' : 'Size ' . $size) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span>Condition</span>
                    <select class="field-select" data-filter-control="condition">
                        <?php foreach (site_conditions() as $condition): ?>
                            <option value="<?= h($condition) ?>"<?= selected_attr($selectedCondition, $condition) ?>>
                                <?= h($condition === 'All' ? 'All Conditions' : $condition) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
        </div>

        <div class="product-grid product-grid--shop" data-shop-grid>
            <?php foreach ($products as $product): ?>
                <?php render_product_card($product); ?>
            <?php endforeach; ?>
        </div>

        <div class="empty-state<?= count($products) > 0 ? ' is-hidden' : '' ?>" data-empty-state>
            <p>No items found matching your filters.</p>
            <button class="button button--outline" type="button" data-clear-filters>Clear Filters</button>
        </div>
    </div>
</section>
<?php render_page_end(); ?>
