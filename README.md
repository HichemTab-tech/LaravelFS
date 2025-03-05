# **LaravelFS - Community Laravel Full Starter-kits Installer compatible with Laravel 12**

**LaravelFS** was born from the idea of "Laravel Full Starter Kits" (or Full Stack) LOL – a way to bring back the legacy starter kits like Breeze and Jetstream that were removed from the official Laravel Installer, while still supporting the new Laravel 12 starter kits and custom solutions via Composer.

> 🚨 **Disclaimer:** This installer is **not officially supported by the Laravel team**. It's a **community-driven alternative** that extends the Laravel Installer by supporting **abandoned starter kits** like Breeze and Jetstream, as well as allowing **custom starter kits** via Composer.  
We strive to keep it **up-to-date with Laravel's official installer** while offering extended flexibility. 🚀

---

## **Official Documentation**
LaravelFS functions similarly to the Laravel Installer but with **extra capabilities**.

### **Features:**
✅ Install Laravel projects just like the official installer.  
✅ Support for **Breeze and Jetstream**, even if they are abandoned.  
✅ Install **custom starter kits** from Packagist.  
✅ Save and reuse project setups with **Templates**.  
✅ Ensure that provided starter kits are **Composer packages of type `project`**.  
✅ CLI command to fetch additional details about a starter kit package.

📖 **For Laravel's official installation guide, refer to the [Laravel documentation](https://laravel.com/docs/installation).**

---

## **Installation**
To install LaravelFS globally, run:

```sh
composer global require hichemtab-tech/laravelfs
```

Make sure **`~/.composer/vendor/bin`** (Mac/Linux) or **`%USERPROFILE%/AppData/Roaming/Composer/vendor/bin`** (Windows) is in your system's PATH to use the `laravelfs` command globally.

---

## **Usage**
LaravelFS works similarly to the Laravel Installer. You can create a new project using:

```sh
laravelfs new my-project
```

### **Installing with Breeze or Jetstream**
To create a Laravel project with Breeze or Jetstream, use:

```sh
laravelfs new my-project --breeze
laravelfs new my-project --jet
```

Even if these starter kits are abandoned, LaravelFS ensures they remain **available for installation**.

---

## **🚀 New Feature: Templates!**
Tired of typing the same options for every new Laravel project? With **LaravelFS Templates**, you can save your preferred project setup and reuse it anytime!

### **Creating a Template**
To create a reusable template, use:

```sh
laravelfs template:new my-template
```

This will prompt you the same way as `laravelfs new`, but instead of creating a project, it **saves your setup** as a template.

> 📝 **Templates include:**
> - Selected starter kits (Breeze, Jetstream, Vue, React, Livewire)
> - Custom starter-kit options
> - Extra flags like `--typescript`, `--ssr`, `--api`, etc.

### **Viewing Saved Templates**
List all saved templates:

```sh
laravelfs template:show
```

Or view a specific template:

```sh
laravelfs template:show my-template
```

### **Using a Template**
Once saved, you can use your template anytime:

```sh
laravelfs use my-template my-project
```

This runs the exact same command as if you typed everything manually!

---

## **Installing Custom Starter Kits**
LaravelFS allows you to install **custom Laravel starter kits** from Packagist by providing the package name:

```sh
laravelfs new my-project --custom-starter=hichemtab-tech/forked-from-react-starter-kit
```

🔹 **What qualifies as a Laravel starter kit?**  
A starter kit must meet the following requirements:
- It must be a **Composer package of type `project`**.
- It must be **published on Packagist** ([Submit your package here](https://packagist.org/packages/submit)).
- It should provide a full Laravel project setup.
- Check this repo for a reference [Forked from React Starter Kit](https://github.com/HichemTab-tech/forked-from-react-starter-kit)

---

## **Contributing**
Thank you for considering contributing to LaravelFS! We welcome contributions to improve the installer and keep it updated. Please submit issues and pull requests to the [GitHub repository](https://github.com/HichemTab-tech/LaravelFS).

---

## **Code of Conduct**
To ensure LaravelFS remains a welcoming project, please review and abide by our **Code of Conduct**.

---

## **Security Vulnerabilities**
If you discover a security vulnerability, please open an issue or contact the maintainers.

---

## **License**
LaravelFS is open-source software licensed under the **MIT license**.

---

### 🎉 **Happy coding with LaravelFS!** 🚀