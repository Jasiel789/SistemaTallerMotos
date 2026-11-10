# Sistema de Inventario y Servicio para Taller de Motos

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat-square&logo=mysql&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-646CFF?style=flat-square&logo=vite&logoColor=white)
![Estado](https://img.shields.io/badge/Estado-en%20desarrollo-orange?style=flat-square)

Sistema web desarrollado en equipo con **Laravel** para administrar un taller de motos: control de inventario de refacciones, compras a proveedores, ventas y órdenes de servicio, con usuarios diferenciados por rol.

## Descripción

El sistema centraliza la operación diaria del taller. Permite llevar el stock de refacciones con sus precios de compra y de venta, registrar las compras a proveedores, vender refacciones tanto en mostrador como ligadas a una orden de servicio, y dar seguimiento a los clientes y a los servicios realizados.

## Módulos

| Módulo | Descripción |
| :--- | :--- |
| **Productos y stock** | Catálogo de refacciones con existencias, precio de compra y precio de venta. |
| **Proveedores y compras** | Registro de proveedores y de las compras de refacciones. |
| **Ventas** | Ventas de mostrador y ventas asociadas a una orden de servicio. |
| **Órdenes de servicio** | Registro y seguimiento de los servicios del taller. |
| **Clientes** | Registro de clientes y su historial. |
| **Usuarios y permisos** | Acceso diferenciado para el propietario y los empleados. |
| **Reportes** | Exportación a PDF y Excel. |

El diseño está pensado para usarse también desde el celular.

## Tecnologías

| Capa | Tecnología |
| :--- | :--- |
| Backend | Laravel (PHP) |
| Base de datos | MySQL |
| Frontend | Tailwind CSS, Vite |
| Entorno local | XAMPP |
| Control de versiones | Git, GitHub |

## Requisitos

- PHP y Composer (versión indicada en `composer.json`)
- Node.js y npm
- MySQL (por ejemplo, mediante XAMPP)

## Instalación

```bash
# 1. Clonar el repositorio
git clone https://github.com/Jasiel789/SistemaTallerMotos.git
cd SistemaTallerMotos

# 2. Instalar dependencias
composer install
npm install

# 3. Configurar el entorno
cp .env.example .env
php artisan key:generate
```

4. Crea una base de datos en MySQL (por ejemplo, desde phpMyAdmin en XAMPP) y configura en el archivo `.env` los valores `DB_CONNECTION=mysql`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`.

```bash
# 5. Crear las tablas
php artisan migrate

# 6. Compilar los recursos y levantar el servidor
npm run build
php artisan serve
```

Abre `http://127.0.0.1:8000` en el navegador.

## Estado del proyecto

Proyecto en desarrollo activo.

- [x] Estructura base del proyecto
- [x] Usuarios con roles (propietario y empleado)
- [ ] Productos y stock
- [ ] Proveedores y compras
- [ ] Ventas
- [ ] Órdenes de servicio y clientes
- [ ] Reportes en PDF y Excel

## Equipo

- **Pablo Jasiel Bernal Garcia**: [GitHub](https://github.com/Jasiel789)

## Derechos

Todos los derechos reservados. El código se muestra con fines de portafolio y no se otorga licencia de uso, copia ni distribución sin autorización de los autores.
