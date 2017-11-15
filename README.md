# Laravel HTML Minify

### For Laravel 4
## About

This package compresses the HTML output from your Laravel 4 application, seamlessly reducing the overall response size of your pages.

Other scripts that I've seen will compress the HTML output on-the-fly for each request. Instead, this package extends the Blade compiler to save the compiled template files to disk in their compressed state, reducing the overhead for each request.


## Installation

1. Add `"megaads-vn/laravel-minify-html":  "1.*"` to **composer.json**.
2. Run `composer update`
```
    Or can using command composer require megaads-vn/laravel-minify-html
```
3. Add `MegaAds\LaravelHtmlMinify\LaravelHtmlMinifyServiceProvider` to the list of providers in **app/config/app.php**.
4. **Important:** You won't see any changes until you edit your `*.blade.php` template files. Once Laravel detects a change, it will recompile them, which is when this package will go to work. To force all views to be recompiled, just run this command: `find . -name "*.blade.php" -exec touch {} \;`

## Config

Optionally, you can choose to customize how the minifier functions for different environments. Publish the configuration file and edit accordingly.

    $ php artisan config:publish megaads-vn/laravel-minify-html

### Options

- **`enabled`** - *boolean*, default **true**

If you are using a javascript framework that conflicts with Blade's tags, you can change them.

- **`blade.contentTags`** - *array*, default `{{` and `}}`
- **`blade.escapedContentTags`** - *array*, default `{{{` and `}}}`

#### Skipping minification

To prevent the minification of a view file, add `skipmin` somewhere in the view.

```
[[-- skipmin --]]

```
