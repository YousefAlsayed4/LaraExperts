# LaraExperts Form Builder

**LaraExperts Form Builder** is a powerful Laravel package that allows users to build fully customizable forms with ease. Built on top of the Filament admin panel, this package lets you add and manage dynamic fields such as inputs, text areas, paragraphs, date/time pickers, color pickers, file uploads, and more — all without writing any HTML manually.

---

## 🚀 Features

- Drag-and-drop form builder interface
- Custom fields (text, paragraph, file, date, color, etc.)
- Responsive and accessible forms
- Admin panel management via Filament
- API-ready for submission and management
- Image and media support
- SEO integration
- Multilingual support

---

## 📦 Installation

To install the package, follow the steps below.

### Step 1: Install via Composer

```bash
composer require lara-experts/forms
```

### Step 2: Run the Installation Command

```bash
php artisan form:install
```

This command sets up the initial package structure and optionally runs database migrations after user confirmation.

---

## ⚙️ Configuration & Setup

### Step 3: Set Up APIs and Controllers

Publish all components (optionally use `--force` if needed):

```bash
php artisan bolt:publish
```

Generate the FormController (manual completion may be required):

```bash
php artisan make:form-controller
```

### Step 4: Configure User Model

Add the following in your `app/Models/User.php` file:

```php
use LaraExperts\Bolt\Models\Concerns\BelongToBolt;

class User extends Authenticatable
{
    use BelongToBolt;
}
```

### Step 5: Set Up Image Support

```bash
php artisan make:image-support
```

### Step 6: Run Migrations

```bash
php artisan migrate
```

---

## 🧩 Required Dependencies

Ensure the following packages are installed for full functionality:

### ✅ Filament Admin Panel

```bash
composer require filament/filament
```

### 🌐 Multilingual Support

```bash
composer require filament/spatie-laravel-translatable-plugin:"^3.2" -W
```

#### Register the Plugin in Your Admin Panel Provider

```php
use LaraExperts\Bolt\BoltPlugin;
use Filament\SpatieLaravelTranslatablePlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            SpatieLaravelTranslatablePlugin::make()
                ->defaultLocales(['en', 'es']),
            BoltPlugin::make(),
        ]);
}
```

### Add This in the USER MODEL
```
add this 
"use LaraExperts\Bolt\Models\Concerns\BelongToBolt;
 use BelongToBolt;
"

in USER MODEL
```

### 🔍 SEO Tools

```bash
composer require artesaos/seotools
```

---

## 📘 Usage

After installation and setup, access the Filament dashboard and navigate to the **Form Builder** panel. Here you can:

- Create new forms
- Add any supported field type
- Configure field settings, validation, and design
- Save and manage submissions

These forms are ready to be displayed on the front-end and accept user input.

---

## 🛠 Customization

You can extend or override components such as:

- Fields (create your own or edit existing ones)
- Form layout templates
- Submission logic (custom controllers)
- Validation rules

Explore the published files and documentation for customization options.

---

## 🤝 Contributing

We welcome contributions! Whether it's a bug fix, enhancement, or suggestion — open an issue or submit a pull request.

---

## 📄 License

This package is open-source and licensed under the [MIT license](LICENSE).

---

**Developed with ❤️ by LaraExperts**