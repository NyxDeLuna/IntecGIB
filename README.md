# IntecGIB - Plataforma Web Empresarial

**Última actualización:** Abril 28, 2026 | **Versión:** 3.0 | **Estado:** ✅ Finalizado

---

## 📋 Descripción General


**IntecGIB** es una plataforma web empresarial profesional diseñada para una empresa de construcción e ingeniería especializada en automatización de proyectos residenciales y comerciales.

### ✨ Características Principales

- ✅ **Sitio Web Corporativo:** Presentación completa (9 páginas HTML/PHP)
- ✅ **Panel Administrativo:** Gestión integral con autenticación segura
- ✅ **Sistema de Calendario:** Gestión interactiva de servicios (vista mensual color-coded)
- ✅ **Portafolio Visual:** Galería de proyectos con carousel autoplay inteligente
- ✅ **Sistema de Reseñas:** 67 comentarios verificados, distribuidos por estrellas
- ✅ **Integración PayPal:** Procesamiento seguro de pagos
- ✅ **Generación de PDFs:** Facturas automáticas en PDF
- ✅ **Autenticación Segura:** Login con MD5 + sesiones PHP
- ✅ **Gestión Completa:** Proyectos, servicios, órdenes y clientes
- ✅ **Importación de datos de sesión en services.html:** modal que rellena el formulario con los datos de usuario autenticado
- ✅ **Historial de compras:** `purchase_history.php` muestra las órdenes del usuario con sesión iniciada
- ✅ **Logout animado:** `logout.php` muestra mensaje de cierre y redirige automáticamente a `index.html`

### 🔧 Stack Tecnológico

**Backend:**
- PHP 8.2+ | MySQL 5.7+ | Composer

**Frontend:**
- HTML5 | CSS3 | JavaScript ES6 Vanilla

**Dependencias:**
- DomPDF (PDFs) | PHPMailer (Email) | PayPal SDK

**Requisitos del Servidor:**
- Apache 2.4+ (puertos 80, 443)
- MySQL/MariaDB
- Módulos: mod_php, mod_ssl, mod_rewrite

---

## 📁 Estructura del Proyecto

```
intecgib/
├── 📄 Páginas Públicas
│   ├── index.html              # Página principal (landing)
│   ├── about.html              # Acerca de la empresa
│   ├── residential.html        # Soluciones residenciales
│   ├── business.html           # Soluciones comerciales
│   ├── projects.html           # Galería de proyectos ⭐
│   ├── services.html           # Calculadora y reserva
│   ├── purchase_history.php    # Historial de compras de usuario
│   ├── contact.html            # Contacto
│   └── cookies.html            # Política de cookies
│
├── 🔐 Autenticación
│   ├── login.php               # Panel de login
│   ├── logout.php              # Cierre de sesión
│   ├── auth.php                # Validación de sesión
│   └── admin.php               # Panel administrativo ⭐
│
├── 🔌 API REST
│   ├── api/get_calendar_services.php      # Obtiene servicios por mes
│   └── api/update_service_status.php      # Actualiza estado servicio
│
├── ⚙️ Configuración
│   ├── config/database.php          # PDO + MySQLi
│   ├── config/invoice_config.php    # Rutas de facturas
│   ├── config/generate_invoice.php  # Generador PDF
│   ├── config/paypal.php            # Credenciales PayPal
│   ├── config/send_email.php        # SMTP config
│   └── config/intecgib_db.sql       # Script BD
│
├── 🎨 Estilos
│   ├── css/style.css           # Estilos principales (3000+ líneas)
│   └── css/calendar.css        # Estilos del calendario
│
├── 📜 JavaScript
│   ├── js/main.js              # Lógica principal (670+ líneas)
│   ├── js/services.js          # PayPal + cálculos
│   └── js/calendar.js          # Widget calendario (274 líneas)
│
├── 📋 Plantillas
│   └── templates/invoice_template.php  # Template factura PDF
│
├── 🖼️ Multimedia
│   └── img/
│       ├── about/              # Imágenes acerca
│       ├── business/           # Imágenes comercial
│       ├── residential/        # Imágenes residencial
│       ├── projects/           # Imágenes proyectos
│       ├── team/               # Fotos equipo
│       ├── certifications/     # Logos certificaciones
│       ├── partners/           # Logos partners
│       ├── icons/              # Iconos SVG
│       ├── misc/               # Misc
│       └── uploads/            # PDFs e imágenes generadas
│
├── 💾 Datos
│   ├── data/invoices.json      # Registro de facturas
│   └── logs/                   # Logs de aplicación
│
├── 📦 Dependencias
│   ├── vendor/phpmailer/       # Email SMTP
│   ├── vendor/dompdf/          # Generador PDFs
│   └── vendor/...              # Otros (composer install)
│
└── 📄 Documentación
    ├── README.md               # Este archivo
    ├── memoria_proyecto.docx   # Memoria técnica completa
    └── docs/guia-mantenimiento.txt  # Guía de mantenimiento
```

---

## 🌟 Funcionalidades Destacadas

### 1️⃣ Carousel Avanzado en Proyectos

**Ubicación:** `projects.html` → Modal de proyecto

**Características:**
- ⏱️ **Autoplay adaptativo:**
  - 5 segundos en vista grid
  - 10 segundos en modal
- 📊 **Progress bar visual:**
  - Animación suave con `requestAnimationFrame`
  - Gradiente dinámico (#acd90c → #88aa00)
  - Movimiento lineal sin saltos (60 FPS)
- 🎮 **Controles interactivos:**
  - Anterior/siguiente manual
  - Números de diapositiva
  - Pausa automática al pasar mouse
- ✨ **Transiciones:**
  - Fade out 0.15s
  - Fade in 0.15s
  - Smooth y natural

**Código:** `js/main.js` líneas 274-670

---

### 2️⃣ Calendario de Servicios (Admin)

**Ubicación:** `admin.php` → Tab "📅 Calendar"

**Características:**
- 📅 **Vista mensual** completa
- 🎨 **Color-coding por estado:**
  - 🔴 Pending (Rojo)
  - 🟠 Paid (Naranja)
  - 🟢 Confirmed (Verde)
  - 🔵 Completed (Azul)
  - ⚪ Cancelled (Gris)
- 📋 **Panel lateral** con detalles
- ⚡ **Actualización en tiempo real** sin refresco
- 🔌 **API REST** para comunicación

**Endpoints:**
```
POST /api/get_calendar_services.php
  - Parámetros: month, year
  - Respuesta: { "servicios": [...], "fecha": "..." }

POST /api/update_service_status.php
  - Parámetros: service_id, new_status
  - Respuesta: { "success": true, "message": "..." }
```

**Código:** `js/calendar.js` (274 líneas) + `css/calendar.css` (411 líneas)

---

### 3️⃣ Sistema de Reseñas Completo

**Base de Datos:**
```sql
67 comentarios verificados distribuidos:
- 22% ⭐⭐⭐⭐⭐ (15 comentarios excelentes)
- 13% ⭐⭐⭐⭐ (9 comentarios buenos)
- 33% ⭐⭐⭐ (22 comentarios neutrales)
- 21% ⭐⭐ (14 comentarios malos)
- 10% ⭐ (7 comentarios pésimos)
```

**Panel Admin Completo:**
- 📧 Tabla con columna email (links `mailto:`)
- ✅ Aprobación/rechazo de reseñas
- 🗑️ Eliminación de registros
- 🔍 Filtrado por estrellas
- 📊 Estadísticas en tiempo real

**Formato emails:**
- `firstname+3digits@gmail.com`
- `firstname+3digits@hotmail.com`

---

### 4️⃣ Generación de Facturas PDF

**Flujo:**
```
Usuario → services.html → Completa formulario
  ↓
Pago PayPal → process_service.php
  ↓
generate_invoice.php → DomPDF → template/invoice_template.php
  ↓
PDF generado → Almacenado en img/uploads/invoices/
  ↓
data/invoices.json → Mapeo de tokens
  ↓
download_invoice.php → Sirve PDF con nombre: invoice_[ID].pdf
```

**Características:**
- 💳 Integración PayPal completa
- 📊 Datos dinámicos (cliente, servicio, precio)
- 💾 Almacenamiento seguro en servidor
- 🔐 Acceso por token único
- 📥 Descarga/impresión desde modal

---

### 5️⃣ Gestión de Órdenes de Servicio

**Tabla: `service_orders`**
```sql
- id              (Primary Key)
- service_type    (maintenance | installation)
- technicians     (1-5)
- hours           (1-8)
- date            (DATE)
- time            (TIME)
- customer_name   (VARCHAR)
- customer_email  (VARCHAR)
- customer_phone  (VARCHAR)
- address         (TEXT)
- total_price     (DECIMAL)
- status          (pending | paid | confirmed | completed | cancelled)
- transaction_id  (PayPal ID)
- created_at      (TIMESTAMP)
```

**Estados:**
- 🟡 **pending:** Reserva sin pagar
- 🟠 **paid:** Pago completado
- 🟢 **confirmed:** Aprobado por admin
- 🔵 **completed:** Servicio finalizado
- ⚪ **cancelled:** Cancelado

---

## 🔐 Seguridad Implementada

### Autenticación
- ✅ Login con usuario/contraseña
- ✅ Hashing MD5 de contraseñas
- ✅ Sesiones PHP validadas en cada página admin
- ✅ Logout limpia sesión completamente
- ✅ Historial de compras protegido por sesión en `purchase_history.php`

### Validación de Datos
- ✅ Validación cliente (HTML5 + JavaScript)
- ✅ Validación servidor (PHP)
- ✅ Sanitización de entradas (trim, htmlspecialchars)
- ✅ Prepared statements en BD

### Protección
- ✅ Tokens únicos para PDFs
- ✅ Verificación de sesión en endpoints
- ✅ Headers de seguridad HTTP
- ✅ HTTPS recomendado en producción

---

## 🚀 Guía de Instalación Rápida

### Requisitos Previos
```bash
# Verificar versiones instaladas
php -v          # Debe ser 8.2+
mysql -V        # Debe ser 5.7+
composer -V     # Si no está, descargar de getcomposer.org
```

### Pasos de Instalación

**1. Clonar Repositorio**
```bash
git clone https://github.com/NyxDeLuna/IntecGIB.git
cd IntecGIB
```

**2. Instalar Dependencias**
```bash
composer install
```

**3. Crear Base de Datos**
```bash
mysql -u root -p < config/intecgib_db.sql
```

**4. Configurar Conexión BD**
Editar `config/database.php`:
```php
$host = "localhost";
$user = "root";
$password = "tu_contraseña";
$database = "intecgib_db";
```

**5. Crear Directorios (si no existen)**
```bash
mkdir -p img/uploads/invoices
mkdir -p logs
chmod 755 img/uploads/invoices logs
```

**6. Iniciar Apache (XAMPP)**
```bash
# Windows: Abrir XAMPP Control Panel → Start Apache
# Linux: sudo systemctl start apache2
# macOS: sudo apachectl start
```

**7. Acceder a la Aplicación**
```
http://localhost/intecgib
```

**🔓 Credenciales Admin (por defecto):**
- Usuario: `admin`
- Contraseña: `admin123`
- ⚠️ **CAMBIAR EN PRODUCCIÓN**

---

## 📊 Base de Datos

### Esquema Completo

**Tabla: `users`**
```sql
- id (INT, PK)
- username (VARCHAR 255, UNIQUE)
- password (VARCHAR 255, MD5)
- email (VARCHAR 255)
- created_at (TIMESTAMP)
```

**Tabla: `service_orders`**
```sql
- id (INT, PK)
- service_type (ENUM: maintenance, installation)
- technicians (INT: 1-5)
- hours (INT: 1-8)
- date (DATE)
- time (TIME)
- customer_name (VARCHAR 255)
- customer_email (VARCHAR 255)
- customer_phone (VARCHAR 20)
- address (TEXT)
- total_price (DECIMAL 10,2)
- status (ENUM: pending, paid, confirmed, completed, cancelled)
- transaction_id (VARCHAR 255)
- created_at (TIMESTAMP)
```

**Tabla: `reviews`**
```sql
- id (INT, PK)
- name (VARCHAR 255)
- email (VARCHAR 255)
- rating (INT: 1-5)
- comment (TEXT)
- approved (BOOLEAN, DEFAULT 0)
- created_at (TIMESTAMP)
```

**Tabla: `projects`**
```sql
- id (INT, PK)
- title (VARCHAR 255)
- description (TEXT)
- category (ENUM: residential, commercial)
- images (JSON array)
- featured (BOOLEAN)
- created_at (TIMESTAMP)
```

---

## 💰 Precios de Servicios

| Servicio | Tarifa |
|----------|--------|
| Maintenance | £80/hora |
| Installation | £150/hora |

**Cálculo:** `precio = tarifa × cantidad_técnicos × horas`

---

## 📈 Estadísticas del Proyecto

| Métrica | Valor |
|---------|-------|
| Líneas CSS | 3000+ |
| Líneas JavaScript | 900+ |
| Archivos PHP | 15+ |
| Archivos HTML | 8 |
| Tablas BD | 4 |
| Endpoints API | 2 |
| Reseñas | 67 |
| Imágenes | 50+ |

---

## 🔧 Mantenimiento

### Respaldo de Base de Datos
```bash
mysqldump -u root -p intecgib_db > backup_`date +%Y%m%d`.sql
```

### Restaurar Base de Datos
```bash
mysql -u root -p intecgib_db < backup_20260220.sql
```

### Actualizar Dependencias
```bash
composer update
```

### Limpiar Logs Antiguos
```bash
# Logs de PayPal (opcional)
rm logs/process_service_response*.html
```

---

## 📚 Documentación Adicional

- 📄 **Memoria Técnica (50 páginas):** `Memoria_IntecGIB_50pag.docx`
- 📖 **Guía de Mantenimiento:** `docs/guia-mantenimiento.txt`
- 🗄️ **Script Base de Datos:** `config/intecgib_db.sql`

---

## 🌱 Propuestas de Ampliación

### Corto Plazo (1-3 meses)
- [ ] Notificaciones por email (confirmación servicios)
- [ ] Dashboard con estadísticas en tiempo real
- [ ] Exportación de reportes (Excel, PDF)
- [ ] Sistema de tickets de soporte

### Mediano Plazo (3-6 meses)
- [ ] Aplicación móvil (React Native)
- [ ] Blog integrado con noticias
- [ ] Multi-idioma (EN/ES)
- [ ] Integración WhatsApp API

### Largo Plazo (6+ meses)
- [ ] CRM integrado
- [ ] Sistema de afiliados
- [ ] Marketplace de servicios
- [ ] Integración SAP/Oracle para facturación

---

## 📞 Soporte y Contacto

| Canal | Información |
|-------|-------------|
| 📧 Email | info@intecgib.com |
| 📱 Teléfono | +34 XXX XXX XXX |
| 🐛 Issues | github.com/NyxDeLuna/IntecGIB/issues |
| 💬 Mensajes | support@intecgib.com |

---

## 👥 Información del Proyecto

- **Propietario:** IntecGIB
- **Desarrollador:** NyxDeLuna
- **Repositorio:** https://github.com/NyxDeLuna/IntecGIB
- **Licencia:** Propietaria © 2026

---

**Última actualización:** Febrero 20, 2026  
**Versión:** 2.0 Completa  
**Estado:** ✅ Funcional en Producción

### 2. **about.html** - Acerca de la Empresa
**Propósito:** Información detallada sobre IntecGIB, historia, misión y equipo.

**Contenido:**
- **Hero Section:** Introducción con gradiente personalizado
- **Story Section:** Historia de la empresa con milestones (grid 3 columnas)
- **Mission & Vision:** Dos tarjetas con propósito y visión
- **Values Section:** Grid de valores corporativos (Integridad, Innovación, Excelencia)
- **Team Section:** Grid de miembros del equipo con foto y rol
- **Certifications Section:** Logotipos de certificaciones
- **Why Choose Us:** Beneficios diferenciadores
- **CTA Section:** Llamada a acción final

**Características:**
- Milestones con números destacados
- Tarjetas hover con animaciones
- Responsive (2 columnas en desktop, 1 en móvil)

---

### 3. **residential.html** - Soluciones Residenciales
**Propósito:** Detalle de servicios de automatización para hogares.

**Contenido:**
- **Hero Section:** Imagen de fondo + texto introductorio
- **Intro Section:** Descripción + lista de features
- **Services Grid:** 6 tarjetas con servicios:
  - Lighting Control Systems
  - Climate Control & HVAC
  - Security & Surveillance
  - Audio & Video Distribution
  - Smart Blinds & Shades
  - Energy Management
- **Benefits Section:** 4 tarjetas numeradas (01-04) con beneficios
- **Partners Section:** Logos de marcas tecnológicas
- **CTA Section:** Llamada a consultoría gratuita

**Características:**
- Tarjetas con bordes coloreados en superior
- Benefits grid: 2x2 en tablet, 4x1 en desktop grande
- Hover effects con elevación (-10px)

---

### 4. **business.html** - Soluciones Comerciales
**Propósito:** Automatización para empresas y oficinas.

**Contenido:**
- **Hero Section:** Background gradiente verde-azul
- **Solutions Grid:** 6 tarjetas de soluciones empresariales
- **Industries Section:** Grid de industrias (retail, oficinas, hoteles, etc.)
- **Benefits Section:** Similar a residential
- **Partners Section:** Marcas tecnológicas
- **CTA Section:** Call-to-action con botones primary/secondary

**Características:**
- Diseño similar a residential para consistencia
- Gradientes específicos de colores business
- Tarjetas con iconos y listas de features

---

### 5. **services.html** - Calculadora y Reserva de Servicios
**Propósito:** Permitir a usuarios calcular precios y reservar servicios con PayPal.

**Contenido:**
- **Service Information:** `info-grid-services` con 2 tarjetas (50% cada una)
  - Maintenance Service: £80/hora
  - Installation Service: £150/hora
- **Service Calculator:** Formulario multi-campo
  - Tipo de servicio (select)
  - Nº technicians (1-5)
  - Nº hours (1-8)
  - Fecha y hora
  - Datos del cliente (nombre, email, teléfono)
  - Dirección y detalles
- **Price Summary:** Resumen dinámico del precio
- **PayPal Integration:** Botones de pago con integración PayPal SDK
- **Success Modal:** Modal con:
  - Número de referencia
  - Monto pagado
  - Botones para descargar/imprimir factura (PDF)
  - Opción de hacer otra reserva

**Características:**
- Cálculo de precio en tiempo real
- Validación de formulario
- Integración PayPal completa (sandbox/live)
- Modal de éxito con funcionalidad de factura
- Print invoice abre PDF en nueva pestaña
- Download invoice descarga con nombre `invoice_[orderid].pdf`
- Modal de importación de sesión: ofrece cargar datos de usuario existentes en el formulario

**Archivos relacionados:**
- `js/services.js` — Lógica PayPal, validación, cálculo de precio
- `process_service.php` — Procesa la reserva y genera factura
- `download_invoice.php` — Sirve PDFs por token

---

### 5.1 **purchase_history.php** - Historial de Compras
**Propósito:** Mostrar las compras que haya realizado el usuario con sesión activa.

**Contenido:**
- **Tabla de órdenes:** reference number, servicio, cantidad, fecha, estado y total.
- **Filtro de usuario:** sólo se cargan pedidos asociados al email del usuario en sesión.
- **Acceso seguro:** redirige a `login.php` si no hay sesión activa.

**Características:**
- Historial de compras personal por usuario autenticado.
- Mensajes de no-ordenes cuando no hay compras registradas.
- Integración con `auth.php` para validar sesión de usuario.

**Archivos relacionados:** `purchase_history.php`, `auth.php`

### 6. **projects.html** - Galería de Proyectos
**Propósito:** Mostrar proyectos completados de la empresa.

**Contenido:**
- **Proyecto Cards:** Grid de proyectos con:
  - Imagen destacada
  - Nombre
  - Botón "Ver proyecto"
- **Project Detail Modal:**
  - Carrusel de imágenes (anterior/siguiente)
  - Miniaturas para navegación
  - Zoom modal (lightbox)
  - Contador de imágenes
  - Información del proyecto

**Características:**
- Filtros por categoría (residencial, comercial, etc.)
- Busca por nombre
- Grid responsive
- Carrusel interactivo con teclado y mouse

---

### 7. **contact.html** - Contacto
**Propósito:** Formulario de contacto directo.

**Contenido:**
- **Contact Form:** 
  - Nombre, email, asunto, mensaje
  - Envía a través de `mailto:` con datos pre-rellenados
  - Panel de copia al portapapeles
- **Contact Info:**
  - Email: support@intecgib.com
  - Teléfono
  - Dirección

**Características:**
- Envío por mailto (no requiere backend)
- Opción de copiar datos al portapapeles
- Validación de email
- Guardado local en localStorage (opcional)

---

### 8. **cookies.html** - Política de Cookies
**Propósito:** Información sobre uso de cookies.

**Contenido:**
- **Policy Header:** Encabezado con gradiente verde
- **8 Secciones principales:**
  1. Qué son las cookies
  2. Cómo las usamos
  3. Tipos de cookies (Essential, Performance, Preference, Third-party)
  4. Gestión y control (instrucciones por navegador)
  5. Cookies de terceros
  6. Derechos del usuario
  7. Cambios a la política
  8. Contacto
- **Footer:** Resumen y botón "Back to Home"

**Características:**
- Diseño profesional con colores brand
- Bordes verdes en títulos
- Tablas de navegadores (Chrome, Firefox, Safari, Edge)
- Responsive (1 columna en móvil)

---

### 9. **login.php** - Panel de Autenticación
**Propósito:** Control de acceso a paneles administrativos (en desarrollo).

**Estado:** Placeholder para futura funcionalidad de admin.

---

## 🎨 Cambios Versión V10.4 (Diciembre 2025)

### 1. **Icono Flotante de Reseñas** ⭐
- Botón circular en esquina inferior izquierda en TODAS las páginas
- Click abre modal emergente con formulario de reseña
- Campos: Nombre, Email (opcional), Calificación (⭐ interactivas), Comentario
- Validación cliente y servidor
- Animaciones suaves (slide-up, fade-in)
- **Archivo:** `js/floating-review-button.js`

### 2. **Color de Botones Actualizado**
- Color anterior: `#007bff` (azul)
- **Color nuevo: `#acd90c`** (amarillo verdoso/lima)
- Hover: `#95b908` (oscurecer el mismo tono)
- Aplicado a: `.cta-button`, formularios, botones principales
- Cambios en: `css/style.css`

### 3. **Animaciones en Residential Page**
- Añadidas clases `animate-fade-in` y `animate-fade-in-down`
- Sincronización visual con `about.html`
- Efecto: Elementos aparecen gradualmente al scroll
- **Archivo:** `residential.html`

### 4. **Base de Datos - MySQLi + PDO**
- `config/database.php` ahora proporciona AMBAS conexiones:
  - `$pdo` — PDO (moderno)
  - `$conn` — MySQLi (legacy)
- Tabla `reviews` con campos completos: id, name, email, rating, comment, page, approved, timestamps

### 5. **Admin Panel**
- **Reviews Management:** Tabla interactiva con filtros, approve/delete, export PDF
- **Projects Management:** Gestión de proyectos con edición y eliminación
- **Estadísticas:** Cards con totales (reviews totales, aprobadas, pendientes, proyectos)
- **Responsive design** con sidebar navegación

---

## 📋 Sistema de Reseñas (Completo)

### Flujo del Usuario
```
Usuario abre página → Ve icono ⭐ → Click → Modal se abre → Completa formulario
→ Click "Enviar" → Reseña guardada (pendiente de aprobación) → Modal muestra "✓ Gracias"
```

### Endpoints API
| Endpoint | Método | Descripción |
|----------|--------|-------------|
| `api/save_review.php` | POST | Guarda nueva reseña (pendiente) |
| `api/get_reviews.php` | GET | Reseñas aprobadas; `?all=1` lista todas (admin) |
| `api/approve_review.php` | POST | Aprueba reseña (admin) |
| `api/delete_review.php` | POST | Elimina reseña (admin) |
| `api/filter_reviews.php` | GET | Filtrado avanzado (admin) |
| `api/export_reviews_pdf.php` | GET | Export a PDF (admin) |

### Base de Datos
```sql
CREATE TABLE `reviews` (
  `id` varchar(36) PRIMARY KEY,
  `name` varchar(100) NOT NULL,
  `email` varchar(100),
  `rating` int(11) CHECK (rating >= 1 AND rating <= 5),
  `comment` longtext NOT NULL,
  `page` varchar(50),
  `approved` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp()
);
```

---

## 🔐 Seguridad en Reseñas

- ✅ XSS Prevention: `htmlspecialchars()` en PHP, `.textContent` en JS
- ✅ SQL Injection: Prepared statements con MySQLi
- ✅ Session Authentication: Verificación de `$_SESSION['logged_in']` en endpoints admin
- ✅ Email Privacy: Campo email no se muestra públicamente
- ✅ CSRF: Validación implícita en sesión

---

## 🔐 Admin Panel

**Acceso:** `admin.php` (requiere login)

**Vistas:**
1. **Reviews Management** — Lista todas las reseñas (pendientes y aprobadas) ordenadas por fecha descendente.
   - Botón "Approve" para pasar reseña a `approved: true`.
   - Botón "Delete" para eliminar permanentemente.

2. **Projects Management** — Lista todos los proyectos con estado (Completed/In Progress/Future).
   - Botón "+ Add New Project" redirige a `projects.html`.
   - Botón "Edit" para editar (redirige a `edit_project.php`).
   - Botón "Delete" para eliminar con confirmación.

**Seguridad:** 
- Requiere autenticación via `auth.php` (sesión `$_SESSION['user_id']`).
- Endpoints de API verifican `$_SESSION['user_id']` antes de actuar.

---

## 🚀 Instrucciones de Instalación

```bash
# 1. Clonar o descargar
cd C:\xampp\htdocs\intecgib

# 2. Instalar dependencias (si Composer está en PATH)
composer install

# 3. Crear carpetas de datos
mkdir data
mkdir img/uploads/invoices
mkdir logs

# 4. Configurar PayPal en config/paypal.php

# 5. Acceder a http://localhost/intecgib/index.html
```

---

## 📈 Mejoras Futuras

- [x] Panel de admin para gestión de proyectos y reseñas
- [x] Sistema de reseñas de clientes (con moderación)
- [x] Icono flotante de reseñas
- [ ] Sincronización con APIs de terceros
- [ ] Analytics avanzado
- [ ] Chatbot de soporte
- [ ] Blog de noticias y tutoriales
- [ ] Integración con redes sociales

---

## 📧 Soporte

Para preguntas o reportar bugs, contacta a **support@intecgib.com**

**Última revisión:** Diciembre 8, 2025

```
intecgib/
├── index.html                    # Página principal
├── about.html                    # Acerca de
├── residential.html              # Soluciones residenciales
├── business.html                 # Soluciones comerciales
├── projects.html                 # Galería de proyectos
├── services.html                 # Calculadora y reserva de servicios
├── contact.html                  # Contacto
├── cookies.html                  # Política de cookies
├── login.php                      # Panel de auth
├── README.md                      # Este archivo
│
├── config/                       # Configuración y helpers
│   ├── database.php             # Conexión a base de datos (PDO)
│   ├── invoice_config.php       # Rutas de facturas
│   ├── generate_invoice.php     # Generador de PDF (Dompdf)
│   ├── paypal.php               # Credenciales PayPal
│   └── send_email.php           # Helper de email (en desuso)
│
├── css/
│   ├── style.css                # Estilos principales
│   └── animations.css           # Animaciones CSS
│
├── js/
│   ├── main.js                  # Lógica global (auth, UI)
│   └── services.js              # Integración PayPal y validación
│
├── templates/
│   ├── invoice_template.php     # Template HTML para facturas PDF
│   └── [otros templates]
│
├── img/
│   ├── misc/                    # Logo, iconos
│   ├── residential/             # Imágenes residenciales
│   ├── business/                # Imágenes comerciales
│   ├── projects/                # Imágenes de proyectos
│   ├── team/                    # Fotos del equipo
│   ├── partners/                # Logos de partners
│   └── uploads/
│       ├── invoices/            # PDFs generados
│       └── projects/            # Imágenes de proyectos subidas
│
├── data/
│   └── invoices.json            # Mapeo token → archivo de factura
│
├── logs/
│   ├── invoice_generation.log   # Log de generación de PDFs
│   └── invoice_downloads.log    # Log de descargas de facturas
│
├── vendor/                      # Dependencias (Dompdf, PHPMailer)
│   ├── dompdf/dompdf/
│   ├── phpmailer/phpmailer/
│   └── [otros]
│
└── composer.json                # Dependencias del proyecto
```

---

## 🔧 Stack Tecnológico

| Tecnología | Versión | Uso |
|------------|---------|-----|
| PHP | 8.2 | Backend (XAMPP) |
| HTML5 | — | Markup |
| CSS3 | — | Estilos y animaciones |
| JavaScript (Vanilla) | ES6 | Frontend interactivo |
| MySQL/PDO | — | Base de datos (optional) |
| Dompdf | 3.1.4 | Generación de PDFs |
| PHPMailer | 7.0.1 | Envío de emails |
| PayPal SDK | Latest | Integración de pagos |

---

## 💳 Flujo de Pago y Facturas

### 1. Reserva de Servicio
```
Usuario → services.html → Completa formulario → Click "Pay with PayPal"
```

### 2. Pago PayPal
```
PayPal SDK → Botones interactivos → Usuario autoriza pago → Captura de transacción
```

### 3. Procesamiento Backend
```
process_service.php → Valida PayPal → Guarda en DB → Genera PDF → Devuelve URL
```

### 4. Generación de Factura
```
generate_invoice.php → Dompdf → Renderiza template → Guarda PDF → Crea mapping token
```

### 5. Descarga
```
download_invoice.php?token=... → Busca en data/invoices.json → Sirve PDF con nombre invoice_[id].pdf
```

---

## 🔐 Seguridad

**Implementado:**
- Validación de datos en cliente (HTML5) y servidor (PHP)
- Verificación de transacciones PayPal con API
- Tokens aleatorios para descargas de facturas
- Implementar HTTPS
- Logs de auditoría para transacciones
---

## 📝 Configuración

### PayPal (sandbox/live)
Edita `config/paypal.php`:
```php
$cfg = [
    'mode' => 'sandbox', // o 'live'
    'client_id' => 'tu_client_id',
    'secret' => 'tu_secret',
];
```

### Email (opcional)
Edita `webhook.php` función `send_invoice_email_inline()`:
```php
$mail->Host = 'smtp.gmail.com';
$mail->Username = 'tu_email@gmail.com';
$mail->Password = 'app_password';
```

### Base de Datos
Edita `config/database.php` (si usas MySQL):
```php
$pdo = new PDO('mysql:host=localhost;dbname=intecgib', 'root', '');
```

---

## 📊 Notas Técnicas

- **Dompdf:** Genera PDFs desde HTML/CSS. Guarda en `img/uploads/invoices/`
- **Tokens:** Aleatorios de 32 caracteres hex, mapeados en `data/invoices.json`
- **Responsive:** Mobile-first, breakpoints: 560px, 900px, 1024px, 1200px
- **Animaciones:** CSS puras (no requieren JS)
- **Cookies:** Banner silencioso, no persiste automáticamente
- **Grid Layouts:** 
  - `.info-grid`: 3 columnas (responsive)
  - `.benefits-grid`: 2x2 (4 columnas en pantallas > 1200px)
  - `.info-grid-services`: 50/50 split (servicios)

---

## ⭐ Customer Reviews Feature

Se ha añadido un sistema de reseñas con moderación manual (admin):

- **Comportamiento:** Los usuarios pueden dejar su nombre, valoración (1–5) y comentario desde `services.html` y `projects.html`.
- **Almacenamiento:** Reseñas guardadas en `data/reviews.json` con campo `approved: false` (requiere moderación).
- **Endpoints:** 
  - `api/save_review.php` (POST JSON) — guardar reseña (pendiente de aprobación).
  - `api/get_reviews.php` (GET) — listar reseñas aprobadas; `?all=1` (admin) lista todas.
  - `api/approve_review.php` (POST) — cambiar estado a aprobado (admin).
  - `api/delete_review.php` (POST) — eliminar reseña (admin).
- **Frontend:** `js/reviews.js` muestra solo reseñas aprobadas en páginas públicas; con escaping HTML para prevenir XSS.

---

## 🔐 Admin Panel

**Acceso:** `admin.php` (requiere login)

**Vistas:**
1. **Reviews Management** — Lista todas las reseñas (pendientes y aprobadas) ordenadas por fecha descendente.
   - Botón "Approve" para pasar reseña a `approved: true`.
   - Botón "Delete" para eliminar permanentemente.

2. **Projects Management** — Lista todos los proyectos con estado (Completed/In Progress/Future).
   - Botón "+ Add New Project" redirige a `projects.html`.
   - Botón "Edit" para editar (redirige a `edit_project.php`).
   - Botón "Delete" para eliminar con confirmación.

**Seguridad:** 
- Requiere autenticación via `auth.php` (sesión `$_SESSION['user_id']`).
- Endpoints de API verifican `$_SESSION['user_id']` antes de actuar.

---

## 🚀 Instrucciones de Instalación

```bash
# 1. Clonar o descargar
cd C:\xampp\htdocs\intecgib

# 2. Instalar dependencias (si Composer está en PATH)
composer install

# 3. Crear carpetas de datos
mkdir data
mkdir img/uploads/invoices
mkdir logs

# 4. Configurar PayPal en config/paypal.php

# 5. Acceder a http://localhost/intecgib/index.html
```

## 📧 Soporte

Para preguntas o reportar bugs, contacta a **support@intecgib.com**

**Última revisión:** Diciembre 8, 2025
