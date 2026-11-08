# 🏦 Sistema Bancario - Plataforma de Gestión Financiera

Sistema de Gestión Bancaria desarrollado con **Laravel 12**, **Inertia.js**, **Vue.js 3** y **Tailwind CSS**. La aplicación permite administrar clientes, cuentas bancarias (corrientes, ahorro, plazo fijo), préstamos con cálculo de cuotas de amortización, depósitos, retiros, transferencias entre cuentas y un módulo de biometría/bitácora para control de accesos y auditoría.

---

## 🚀 Tecnologías Utilizadas

* **Backend:** PHP 8.2+ / Laravel 12
* **Frontend:** Vue.js 3 (Composition API / `<script setup>`), Inertia.js, Tailwind CSS
* **Base de Datos:** MySQL / PostgreSQL
* **Autenticación:** Laravel Breeze / Inertia (Session Guard & Middleware de Roles)

---

## 📌 Características Principales

* 👤 **Gestión de Clientes y Usuarios:** Registro, actualización de perfil, asignación de roles y estados de verificación.
* 💳 **Administración de Cuentas:** Apertura y seguimiento de cuentas bancarias (Ahorro, Corriente, Plazo Fijo) asociadas a entidades bancarias.
* 💸 **Operaciones Financieras:**
  * Depósitos y Retiros en cuenta.
  * Transferencias inmediatas entre cuentas.
  * Emisión y gestión de tarjetas de débito.
* 📑 **Préstamos y Amortizaciones:** Solicitud de préstamos y generación automática de cronogramas de cuotas de amortización.
* 🔐 **Seguridad y Auditoría:**
  * Middleware `VerificarRol` y `RegistrarAcceso`.
  * Módulo de Biometría (Huella Biométrica) y Bitácora de accesos.
* 📊 **Dashboard Estadístico:** Visualización interactiva de métricas generales del sistema.

---
