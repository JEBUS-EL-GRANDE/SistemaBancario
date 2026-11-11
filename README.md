<h1 id="descripcionA"> Sistema Bancario - Plataforma de Gestion Financiera </h1>
Sistema web para la gestion Bancaria, desarrollado como proyecto de la materia **Ingenieria de software 1(INF-422)** . La aplicacion permite administrar clientes, cuentas bancarias (corrientes, ahorro, plazo fijo), prestamos con calculo de cuotas de amortizacion, depositos, retiros, transferencias entre cuentas y un modulo de biometria/bitacora para control de accesos y auditoria.

### Proyecto compilado
![video de proyecto ya compilado](public/gimnasio.gif "video de mi proyecto ya compilado")

<h2 id="integrantesA">Integrantes del proyecto:</h2>

- Benjamin.

- Carlos.

- Juan.

## Informacion directa
- [Instalacion](#InstalacionA)
- [Descripcion breve](#descripcionA)
- [modulos](#modulosA)
- [stack Tegnologico](#tegnologiaA)
- [Modelo de base de datos](#basededatosA)
- [tipos de Roles](#rolesA)
- [tu primer usuario](#usuarioA)

<h2 id="modulosA">Caracteristicas principales(modulos)</h2>

El sistema permite administrar los principales procesos bancarios mediante diferentes modulos:

- **Gestion de Clientes**: Registro, modificacion, consulta y administracion de clientes bancarios.
- **Gestion de Cuentas**: Apertura, estado de cuenta, vinculacion de tipos de cuenta (ahorro, corriente) y control de saldos.
- **Gestion de Transacciones**: Registro de depositos, retiros y transferencias interbancarias/entre cuentas, incluyendo almacenamiento de comprobantes.
- **Gestion de Tipos de Cuentas y Tasas**: Administracion de planes de cuenta, tasas de interes y comisiones.
- **Gestion de Promociones/Beneficios**: Administracion de beneficios o promociones para clientes.
- **Gestion de Turnos y Cajas**: Configuracion y administracion de horarios y cajas de atencion.
- **Gestion de Usuarios**: Registro, modificacion y control de acceso de los usuarios/empleados del sistema.
- **Generacion de Reportes**: Consultas e informes financieros, flujo de caja, transacciones e ingresos.
- **Dashboard Estadistico**: Visualización interactiva de métricas generales del sistema.

<h2 id="tegnologiaA">Stack Tecnologico utilizado</h2>

| Capa | Tecnologia |
|------|-----------|
| **Backend** | PHP 8.3, Laravel 12 |
| **Frontend** | Vue.js, php |
| **Estilos** | HTML5, CSS3, Tailwind CSS |
| **Base de Datos** | MySQL / PostgreSQL |
 **Arquitectura** | MVC (Modelo-Vista-Controlador) |
| **Autenticacion** | Laravel Fortify + sistema de roles personalizado |
| **Build Tool** | Vite,  |
| **Servidor Web** | Apache 2.4 |
| **Gestor de dependencias Backend** | Composer |
| **Gestor de dependencias Frontend** | npm |
---
<h2 id="arquitecturaA">Arquitectura General</h2>
El sistema utiliza una arquitectura web de tres capas, organizada mediante el patron Modelo-Vista-Controlador (MVC) proporcionado por Laravel.

1. **Capa de Presentacion:** Encargada de proporcionar las interfaces graficas con las que interactuan los usuarios.

**Tecnologias utilizadas:** Vue.js, Inertia.js, HTML5, CSS3, JavaScript, Tailwind CSS.

2.  **Capa de Negocio:** Encargada de procesar las solicitudes, aplicar las reglas de negocio y controlar las operaciones del sistema.

**Componentes principales:** Controllers, Models, Middleware, Validaciones, Sistema de autenticacion y autorizacion.

3. **Capa de Datos:** Encargada del almacenamiento, administracion y recuperación de la informacion.

**Tecnologias utilizadas:** PostgreSQL, Eloquent ORM, Migraciones de Laravel, Relaciones mediante claves primarias y foraneas.

<h2 id="rolesA">Roles del Sistema</h2>
El sistema cuenta con **2 roles**

#### 1. ADMINISTRADOR 
Usuario encargado de administrar y controlar el funcionamiento general del sistema bancario.
- Gestion completa de clientes y usuarios.
- Administracion de tipos de cuentas y servicios bancarios.
- Gestion de promociones, tasas y beneficios
- Administracion de turnos y asignacion de cajas.
- Consulta y generacion de reportes financieros globales.
- Gestion de copias de seguridad y restauracion de la base de datos.
- Control de operaciones administrativas y auditoria.

#### 2. CAJERO
Usuario encargado de realizar las operaciones cotidianas de atencion al cliente y transacciones en caja.

- Registro de nuevos clientes.
- Consulta y actualizacion de informacion de clientes.
- Apertura de cuentas bancarias.
- Registro de depositos, retiros y transferencias.
- Consulta de informacion de saldos y movimientos.
- Generacion de reportes operativos segon los permisos asignados\
--------------------------------------------------------------------------------
## Modelo de Base de Datos relacional (9 tablas)

### Tablas principales
- **persona (informacion general)**.
- **usuario (roles)**.


<h2 id="basededatosA">tabla de base de datos relacional: </h2> 

<img src="public/DBbancario.png">

---
<h2 id="InstalacionA">Instalación Y Configuración local en diferentes Sistemas Operativos:</h2>
recuerda actualizar sus paquetes ante, para evitar errores al momento de la instalacion.

```bash
#dentro del proyecto (cd BecaComedor)
php --ini                                       #para buscar el php.ini descomenta la bd que usaras
composer install                                #instalar composer
#composer update --ignore-platform-reqs         #(si da error el composer install)
npm install @vitejs/plugin-vue --save-dev       #instalar vue
cp .env.example .env                            #crear el .env y configuralo
php artisan key:generate                        #genera la clave de aplicacion de Laravel
php artisan migrate                             #migrar las tablas a la bd  

php artisan serve        #(terminal 1)          #Iniciar el servidor
npm run dev              #(terminal 2)          #levanta Frontend Vue/Vite
```

<h3  style="color: blue; font-size: 50px">Instalacion en Windows: <img src="public/windows.svg" width="20" height="20"></h3> 

```bash
# 1. Clonar el repositorio
git clone https://github.com/JEBUS-EL-GRANDE/SistemaBancario.git
cd BecaComedor
#busca y descarga php (.zip) de la web oficial y composer e instalalos
```
<h3>instalacion en Arch linux: <img src="public/arch-linux.svg" width="20" height="20"></h3>

```bash
sudo pacman -S php                              #instalar php
sudo pacman -S composer                         #instalar composer
sudo pacman -S nodejs npm                       #instalar npm
```

<h3> Instalacion en debian: <img src="public/debian.svg" width="20" height="20"></h3>

```bash
sudo apt install php php-cli php-mysql php-mbstring php-xml php-curl php-zip
sudo apt install composer                       #Instalar Composer
sudo apt install nodejs npm -y                  #instala npm
```

<h3 id="usuarioA"> crea usuario admin en tu BASE DE DATOS : <img src="public/mariadb.svg" width="20" height="20">  <img src="public/postgresql.svg" width="20" height="20">  <img src="public/mongodb.svg" width="20" height="20"></h3>
con este primer usuario podras crear al resto de usuarios que tienen roles:

<img src="public/BDimg.jpeg">

**Tabla personas:**
```bash
INSERT INTO personas (ci,nombre, apellido,sexo, fecha_registro) 
VALUES ('9800001', 'juan', 'perez', 'M', '2000-01-01');
```
**Tabla turnos:**
```bash
INSERT INTO turnos (nombre_turno, hora_inicio, hora_fin) 
VALUES ('mediodia', '08:00:00', '17:00:00');
```
**Tabla usuarios:**
```bash
INSERT INTO usuarios (id_persona,id_turno,email,contrasenia,login,rol,fecha_contrato,estado)VALUES (1,'1','admin@gmail.com','$2y$12$3qeoc6WEOwvoObsZvEf4gu69aU59gEPpLABryASrE1etWLQY1noEO','admin','Administrador',NOW(),1);
```
con esto podras acceder al proyecto, solo pon en el login **"admin"**   y la contraseña es **"12345678"** que viene siendo la contraseña encriptada que pusiste de la tabla **usuarios**
