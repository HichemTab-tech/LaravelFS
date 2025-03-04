# **LaravelFS - Community Laravel Full Starter-kits Installer**
[![Latest Stable Version](https://poser.pugx.org/hichemtabtech/laravelfs/v/stable)](https://packagist.org/packages/hichemtabtech/laravelfs)
[![Total Downloads](https://poser.pugx.org/hichemtabtech/laravelfs/downloads)](https://packagist.org/packages/hichemtabtech/laravelfs)
[![License](https://poser.pugx.org/hichemtabtech/laravelfs/license)](LICENSE)

---

🚨 **Disclaimer:** This installer is **not officially supported by the Laravel team**. It's a **community-driven alternative** that extends the Laravel Installer by supporting **abandoned starter kits** like Breeze and Jetstream, as well as allowing **custom starter kits** via Composer.  
We strive to keep it **up-to-date with Laravel's official installer** while offering extended flexibility. 🚀

---

## **Official Documentation**
LaravelFS functions similarly to the Laravel Installer but with **extra capabilities**.

### **Features:**
✅ Install Laravel projects just like the official installer.  
✅ Support for **Breeze and Jetstream**, even if they are abandoned.  
✅ Install **custom starter kits** from Packagist.  
✅ Ensure that provided starter kits are **Composer packages of type `project`**.  
✅ CLI command to fetch additional details about a starter kit package.

📖 **For Laravel's official installation guide, refer to the [Laravel documentation](https://laravel.com/docs/installation).**

---

## **Installation**
To install LaravelFS globally, run:

```sh
composer global require hichemtabtech/laravelfs
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

## **Installing Custom Starter Kits**
LaravelFS allows you to install **custom Laravel starter kits** from Packagist by providing the package name:

```sh
laravelfs new my-project --custom-starter=hichemtabtech/forked-from-react-starter-kit
```

🔹 **What qualifies as a Laravel starter kit?**  
A starter kit must meet the following requirements:
- It must be a **Composer package of type `project`**.
- It must be **published on Packagist** ([Submit your package here](https://packagist.org/packages/submit)).
- It should provide a full Laravel project setup.
- Check this repo for a reference [Forked from React Starter Kit](https://github.com/hichemtabtech/forked-from-react-starter-kit)

---

## **Contributing**
Thank you for considering contributing to LaravelFS! We welcome contributions to improve the installer and keep it updated. Please submit issues and pull requests to the [GitHub repository](https://github.com/HichemTab-tech/laravelfs).

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