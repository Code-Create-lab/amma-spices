<?php

namespace App\Console\Commands;

use App\Models\Blog;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    /**
     * The base URL is an option because APP_URL points at localhost in
     * development, and a sitemap full of localhost URLs is worse than no
     * sitemap at all.
     */
    protected $signature = 'sitemap:generate
                            {--base= : Base URL to write into the sitemap (defaults to config app.url)}
                            {--path= : Where to write the file (defaults to public/sitemap.xml)}';

    protected $description = "Rebuild public/sitemap.xml from the live catalogue, categories and blog posts";

    /**
     * Static pages worth indexing, with the priority each should carry.
     * Cart, wishlist, checkout, login and account pages are deliberately
     * absent — robots.txt disallows them, so listing them here would send
     * Google contradictory instructions.
     */
    private const STATIC_PAGES = [
        ['/', 1.0, Url::CHANGE_FREQUENCY_WEEKLY],
        ['/shop-page', 0.9, Url::CHANGE_FREQUENCY_WEEKLY],
        ['/about', 0.7, Url::CHANGE_FREQUENCY_MONTHLY],
        ['/gallery', 0.6, Url::CHANGE_FREQUENCY_MONTHLY],
        ['/blogs', 0.7, Url::CHANGE_FREQUENCY_WEEKLY],
        ['/contact-us', 0.6, Url::CHANGE_FREQUENCY_YEARLY],
        ['/bulk-buy', 0.6, Url::CHANGE_FREQUENCY_YEARLY],
        ['/privacy-policy', 0.3, Url::CHANGE_FREQUENCY_YEARLY],
        ['/terms-and-conditions', 0.3, Url::CHANGE_FREQUENCY_YEARLY],
        ['/return-and-exchange-policy', 0.3, Url::CHANGE_FREQUENCY_YEARLY],
        ['/cancellation-and-refund-policy', 0.3, Url::CHANGE_FREQUENCY_YEARLY],
        ['/shipping-and-delivery-policy', 0.3, Url::CHANGE_FREQUENCY_YEARLY],
    ];

    public function handle(): int
    {
        $base = rtrim($this->option('base') ?: config('app.url'), '/');
        $path = $this->option('path') ?: public_path('sitemap.xml');

        if (str_contains($base, 'localhost') || str_contains($base, '127.0.0.1')) {
            $this->warn("Base URL is {$base} — that is a local address.");
            $this->warn('Pass --base=https://ammasspices.com to write a sitemap fit for production.');
        }

        $sitemap = Sitemap::create();

        foreach (self::STATIC_PAGES as [$uri, $priority, $frequency]) {
            $sitemap->add(
                Url::create($base . $uri)
                    ->setPriority($priority)
                    ->setChangeFrequency($frequency)
            );
        }

        $products = Product::where('is_deleted', 0)
            ->whereNotNull('slug')
            ->whereHas('category', fn($q) => $q->where('is_deleted', 0))
            ->whereHas('variations', fn($q) => $q->where('is_deleted', 0))
            ->get(['slug', 'updated_at']);

        foreach ($products as $product) {
            $sitemap->add(
                Url::create($base . '/product/' . $product->slug)
                    ->setLastModificationDate($product->updated_at ?? now())
                    ->setPriority(0.8)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            );
        }

        // The site links categories through the getCatList route (/category/…),
        // so only that form goes in. The older /categories/… URLs render the
        // same listing and would register as duplicates.
        $categories = Category::where('is_deleted', 0)
            ->whereNotNull('slug')
            ->get(['slug', 'updated_at']);

        foreach ($categories as $category) {
            $sitemap->add(
                Url::create($base . '/category/' . $category->slug)
                    ->setLastModificationDate($category->updated_at ?? now())
                    ->setPriority(0.7)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            );
        }

        $blogs = Blog::published()->get(['slug', 'updated_at', 'published_at']);

        foreach ($blogs as $blog) {
            $sitemap->add(
                Url::create($base . '/blog/' . $blog->slug)
                    ->setLastModificationDate($blog->updated_at ?? $blog->published_at ?? now())
                    ->setPriority(0.6)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
            );
        }

        $sitemap->writeToFile($path);

        $total = count(self::STATIC_PAGES) + $products->count() + $categories->count() + $blogs->count();

        $this->info("Sitemap written to {$path}");
        $this->line(sprintf(
            '  %d static  %d products  %d categories  %d blog posts  = %d URLs',
            count(self::STATIC_PAGES),
            $products->count(),
            $categories->count(),
            $blogs->count(),
            $total
        ));

        return self::SUCCESS;
    }
}
