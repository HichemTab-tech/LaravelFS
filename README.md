# **LaravelFS - Community Laravel Full Starter-kits Installer compatible with Laravel 12**

**LaravelFS** was born from the idea of "Laravel Full Starter Kits" (or Full Stack) LOL – a way to bring back the legacy starter kits like Breeze and Jetstream that were removed from the official Laravel Installer, while still supporting the new Laravel 12 starter kits and community starter kits via `--using`.

> **Disclaimer:** This installer is **not officially supported by the Laravel team**.
> It's a **community-driven alternative** that extends the Laravel Installer by supporting **abandoned starter kits** like Breeze and Jetstream,
> as well as community starter kits via `--using`.  
We strive to keep it **up-to-date with Laravel's official installer** while offering extended flexibility.

> **Attribution:** LaravelFS is based on the open-source [laravel/installer](https://github.com/laravel/installer) project, licensed under MIT.

## **Official Documentation**
LaravelFS functions similarly to the Laravel Installer but with **extra capabilities**.

### **Features:**
- Install Laravel projects just like the official installer.  
- Support for **Breeze and Jetstream**, even if they are abandoned.  
- Install **community starter kits** via `--using` (Packagist or git URL).  
- Save and reuse project setups with **Templates**.  
- Easily **remove saved templates** when no longer needed.  
- Ensure that provided starter kits are **Composer packages of type `project`**.  
- CLI command to fetch additional details about a starter kit package.

**For Laravel's official installation guide, refer to the [Laravel documentation](https://laravel.com/docs/installation).**

## **Installation**
To install LaravelFS globally, run:

```sh
composer global require hichemtab-tech/laravelfs
```

Make sure **`~/.composer/vendor/bin`** (Mac/Linux) or **`%USERPROFILE%/AppData/Roaming/Composer/vendor/bin`** (Windows) is in your system's PATH to use the `laravelfs` command globally.

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

## **Templates**
If you want to reuse the same options for new projects, you can save a template.

### **Creating a Template**
To create a reusable template, use:

```sh
laravelfs template:new my-template
```

This will prompt you the same way as `laravelfs new`, but instead of creating a project, it **saves your setup** as a template.

> **Templates include:**
> - Selected starter kits (Breeze, Jetstream, Vue, React, Livewire)
> - Starter-kit selection options
> - Extra flags like `--typescript`, `--ssr`, `--api`, etc.

### **Viewing Saved Templates**
List all saved templates:

```sh
laravelfs templates
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

## **Removing Templates**
Need to clean up your templates? You can easily remove them.

### **Remove a Specific Template**
To delete a single template:

```sh
laravelfs template:remove my-template
```

### **Remove All Templates**
To remove **all saved templates** at once:

```sh
laravelfs template:remove --all
```

> **This action is irreversible.** Make sure you want to delete all templates before running this command.

## **Installing Community Starter Kits**
LaravelFS allows you to install **community Laravel starter kits** by providing a package name or git URL:

```sh
laravelfs new my-project --using=hichemtab-tech/forked-from-react-starter-kit
```

**What qualifies as a Laravel starter kit?**  
A starter kit should provide a full Laravel project setup. Packagist packages should be **Composer packages of type `project`** and published on Packagist ([Submit your package here](https://packagist.org/packages/submit)).

## **Ubuntu Users: Fixing LaravelFS Command Not Found Issue**

If you installed LaravelFS but **can’t run the `laravelfs` command**,
it might be because Composer's global bin folder is **not in your system's PATH**.

### **Solution: Add Composer Bin to PATH**
1. Open your terminal and edit the `~/.bashrc` file:
   ```sh
   nano ~/.bashrc
   ```  
    _(If needed, use `sudo nano ~/.bashrc`)_

2. Add this line at the **bottom** of the file:
   ```sh
   export PATH="$PATH:$HOME/.config/composer/vendor/bin"
   ```  

3. Save the file (`CTRL + X`, then `Y`, then `Enter`).

4. Apply the changes:
   ```sh
   source ~/.bashrc
   ```  

Now, try running `laravelfs` again; it should work.

## **Contributing**
Thank you for considering contributing to LaravelFS! We welcome contributions to improve the installer and keep it updated. Please submit issues and pull requests to the [GitHub repository](https://github.com/HichemTab-tech/LaravelFS).

## **Code of Conduct**
To ensure LaravelFS remains a welcoming project, please review and abide by our **Code of Conduct**.

## **Security Vulnerabilities**
If you discover a security vulnerability, please open an issue or contact the maintainers.

## **License**
LaravelFS is open-source software licensed under the **MIT license**.

### **Happy coding with LaravelFS**

<!-- GitAds-Verify: ZTMTL65QQLBK2M8QF7CQJ5349NYAFJGJ -->

## GitAds Sponsored
[![Sponsored by GitAds](https://gitads.dev/v1/ad-serve?source=hichemtab-tech/laravelfs@github)](https://gitads.dev/v1/ad-track?source=hichemtab-tech/laravelfs@github)


