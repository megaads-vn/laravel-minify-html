# HTML Minify for Laravel 4
## About
This package compresses HTML output from Laravel 4 application and reducing the overall response size of pages.

## Installation
  1. Add `"fitztrev/laravel-html-minify": "1.*"` to **compoer.json**
  2. Run `composer update`
  3. Add `Fitztrev\LaravelHtmlMinify\LaravelHtmlMinifyServiceProvider` to the list of providers in **app/config/app.php**
  4. **Important**:You won't see any changes until you edit your `*.blade.php` template files. Once Laravel detects a change, it will recompile them, which is when this package will go to work. To force all views to be recompiled, just run this command: `find . -name "*.blade.php" -exec touch {} \;`

## Config:
Optionally, you can choose to customize how the minifier functions for different environments. Publish the configuration file and edit accordingly.
`$ php artisan config:publish fitztrev/laravel-html-minify`

## Options
