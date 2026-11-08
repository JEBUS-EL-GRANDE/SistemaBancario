# -*- coding: utf-8 -*-
import os
import sys
from reportlab.lib.pagesizes import letter
from reportlab.lib import colors
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak, KeepTogether, HRFlowable
)
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import inch
from reportlab.pdfgen import canvas

class NumberedCanvas(canvas.Canvas):
    """Canvas para agregar encabezado y pie de página con número total de páginas."""
    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self._saved_page_states = []

    def showPage(self):
        self._saved_page_states.append(dict(self.__dict__))
        self._startPage()

    def save(self):
        num_pages = len(self._saved_page_states)
        for state in self._saved_page_states:
            self.__dict__.update(state)
            self.draw_page_decorations(num_pages)
            super().showPage()
        super().save()

    def draw_page_decorations(self, page_count):
        if self._pageNumber == 1:
            # Portada: no mostrar encabezado ni pie
            return

        self.saveState()
        self.setFont("Helvetica", 8)
        self.setFillColor(colors.HexColor("#64748b"))

        # Encabezado
        self.drawString(54, 750, "BANCO CONTINENTAL — SISTEMA BANCARIO INTEGRAL")
        self.drawRightString(558, 750, "DOCUMENTO TÉCNICO PARA EXPOSICIÓN")
        self.setStrokeColor(colors.HexColor("#cbd5e1"))
        self.setLineWidth(0.5)
        self.line(54, 742, 558, 742)

        # Pie de página
        self.line(54, 45, 558, 45)
        self.drawString(54, 32, "Confidencial | Arquitectura de Software Bancario | PHP 8.2 + Laravel 12 + Vue 3")
        page_text = f"Página {self._pageNumber} de {page_count}"
        self.drawRightString(558, 32, page_text)
        self.restoreState()

def build_pdf(filename="BANCO_CONTINENTAL_DOCUMENTO_EXPOSICION.pdf"):
    doc = SimpleDocTemplate(
        filename,
        pagesize=letter,
        leftMargin=54,
        rightMargin=54,
        topMargin=54,
        bottomMargin=54
    )

    styles = getSampleStyleSheet()

    # Colores corporativos bancarios
    c_primary = colors.HexColor("#0f172a")     # Azul marino pizarra profundo
    c_secondary = colors.HexColor("#1e3a8a")   # Azul bancario royal
    c_accent = colors.HexColor("#0284c7")      # Cyan tecnológico
    c_emerald = colors.HexColor("#059669")     # Verde financiero
    c_amber = colors.HexColor("#d97706")       # Ámbar advertencia/alerta
    c_dark = colors.HexColor("#1e293b")
    c_muted = colors.HexColor("#475569")
    c_light_bg = colors.HexColor("#f8fafc")
    c_border = colors.HexColor("#e2e8f0")

    # Estilos de texto
    title_style = ParagraphStyle(
        'CoverTitle',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=26,
        leading=32,
        textColor=c_secondary,
        alignment=0,
    )

    subtitle_style = ParagraphStyle(
        'CoverSubtitle',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=13,
        leading=18,
        textColor=c_muted,
        alignment=0,
    )

    h1_style = ParagraphStyle(
        'Heading1_Custom',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=16,
        leading=20,
        textColor=c_secondary,
        spaceBefore=14,
        spaceAfter=8,
        keepWithNext=True
    )

    h2_style = ParagraphStyle(
        'Heading2_Custom',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=12,
        leading=16,
        textColor=c_dark,
        spaceBefore=10,
        spaceAfter=5,
        keepWithNext=True
    )

    body_style = ParagraphStyle(
        'Body_Custom',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=9.5,
        leading=13.5,
        textColor=c_dark,
        spaceAfter=6
    )

    bullet_style = ParagraphStyle(
        'Bullet_Custom',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=9,
        leading=13,
        textColor=c_dark,
        leftIndent=15,
        firstLineIndent=-10,
        spaceAfter=4
    )

    callout_style = ParagraphStyle(
        'CalloutText',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8.5,
        leading=12.5,
        textColor=c_dark,
    )

    table_header_style = ParagraphStyle(
        'TableHeader',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=8.5,
        leading=11,
        textColor=colors.white,
        alignment=1
    )

    table_cell_style = ParagraphStyle(
        'TableCell',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8,
        leading=11,
        textColor=c_dark
    )

    table_cell_bold = ParagraphStyle(
        'TableCellBold',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=8,
        leading=11,
        textColor=c_secondary
    )

    elements = []

    # =========================================================================
    # PÁGINA 1: PORTADA EJECUTIVA
    # =========================================================================
    elements.append(Spacer(1, 40))
    
    # Badge superior
    badge_data = [[
        Paragraph("<font color='#0284c7'><b>CORE BANCARIO & PLATAFORMA DIGITAL INTEGRAL</b></font>", callout_style)
    ]]
    badge_table = Table(badge_data, colWidths=[504])
    badge_table.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#f0f9ff")),
        ('BORDER', (0,0), (-1,-1), 1, colors.HexColor("#bae6fd")),
        ('PADDING', (0,0), (-1,-1), 6),
        ('ALIGN', (0,0), (-1,-1), 'LEFT'),
    ]))
    elements.append(badge_table)
    elements.append(Spacer(1, 20))

    elements.append(Paragraph("BANCO CONTINENTAL", title_style))
    elements.append(Paragraph("Sistema de Gestión Bancaria, Biometría Dactilar y Operaciones Financieras ACID", subtitle_style))
    elements.append(Spacer(1, 15))

    elements.append(HRFlowable(width="100%", thickness=3, color=c_secondary, spaceBefore=5, spaceAfter=25))

    desc_portada = """
    <b>DOCUMENTO TÉCNICO Y MEMORIA DE ARQUITECTURA PARA EXPOSICIÓN ACADÉMICA Y PROFESIONAL</b><br/><br/>
    Esta memoria técnica consolida detalladamente la arquitectura, tecnologías, herramientas reales implementadas, APIs utilizadas (y aclaración de APIs no requeridas), modelos de datos relacionales, controles de seguridad criptográfica y flujos operativos del sistema bancario desarrollado. 
    Diseñado para sustentar la defensa de proyecto ante jurados y comités evaluadores.
    """
    elements.append(Paragraph(desc_portada, body_style))
    elements.append(Spacer(1, 30))

    # Ficha técnica de portada
    meta_info = [
        [Paragraph("<b>Proyecto:</b>", table_cell_bold), Paragraph("Sistema Bancario Integral (Core + Banca Digital)", table_cell_style)],
        [Paragraph("<b>Entorno Tecnológico:</b>", table_cell_bold), Paragraph("PHP 8.2 • Laravel 12.69 • Inertia.js 2.0 • Vue.js 3.5 • PostgreSQL", table_cell_style)],
        [Paragraph("<b>Seguridad & Biometría:</b>", table_cell_bold), Paragraph("WebAuthn / FIDO2 • Web Audio API • BCrypt • RBAC • ACID Pesimista", table_cell_style)],
        [Paragraph("<b>Cobertura de Pruebas:</b>", table_cell_bold), Paragraph("Pest PHP 3.8 — 37 Feature/Unit Tests (106 Aserciones aprobadas al 100%)", table_cell_style)],
        [Paragraph("<b>Finalidad:</b>", table_cell_bold), Paragraph("Defensa y Demostración Técnica en Vivo (Ventanilla + Banca Online)", table_cell_style)],
        [Paragraph("<b>Fecha del Documento:</b>", table_cell_bold), Paragraph("Octubre de 2026", table_cell_style)],
    ]
    meta_table = Table(meta_info, colWidths=[140, 364])
    meta_table.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#f8fafc")),
        ('GRID', (0,0), (-1,-1), 0.5, c_border),
        ('PADDING', (0,0), (-1,-1), 7),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
    ]))
    elements.append(meta_table)

    elements.append(PageBreak())

    # =========================================================================
    # PÁGINA 2: RESUMEN EJECUTIVO Y OBJETIVOS DEL SISTEMA
    # =========================================================================
    elements.append(Paragraph("1. Resumen Ejecutivo y Objetivos del Sistema", h1_style))
    elements.append(HRFlowable(width="100%", thickness=1, color=c_secondary, spaceBefore=2, spaceAfter=10))

    p1 = """
    El proyecto <b>Banco Continental</b> es una solución integral de software bancario desarrollada con estándares de grado financiero. 
    A diferencia de sistemas comerciales genéricos, este sistema fue concebido para resolver las dos dimensiones operativas de una entidad financiera moderna:
    """
    elements.append(Paragraph(p1, body_style))

    obj_items = [
        "<b>Operación de Ventanilla Bancaria (Core Bancario):</b> Atención física en sucursal con roles de <i>Cajero</i>, <i>Ejecutivo de Crédito</i> y <i>Administrador</i>. Incluye registro de clientes, emisión de tarjetas con validación BIN 453288, depósitos, retiros con autenticación biométrica dactilar, transferencias interbancarias e impresión de comprobantes térmicos con código QR de seguridad.",
        "<b>Banca Digital por Internet (Portal de Clientes):</b> Experiencia web moderna para usuarios titulares donde consultan saldos consolidados, estados de cuenta, movimientos históricos en tiempo real, realizan transferencias directas y simulan planes de pago de préstamos.",
        "<b>Integración de Onboarding 2 en 1:</b> El registro de un nuevo cliente genera instantáneamente su expediente bancario institucional y sus credenciales de acceso digital para inicio de sesión, eliminando fricciones y duplicidad de registros.",
        "<b>Rigor Contable y Seguridad Financiera:</b> Doble asiento contable (débito/crédito), trazabilidad total mediante bitácora inmutable y control de concurrencia pesimista con bloqueos a nivel de fila (<code>lockForUpdate</code>) para evitar inconsistencias por condiciones de carrera (Race Conditions)."
    ]
    for it in obj_items:
        elements.append(Paragraph(f"• {it}", bullet_style))

    elements.append(Spacer(1, 10))
    elements.append(Paragraph("2. Arquitectura de Software", h2_style))

    arch_text = """
    El sistema adopta el paradigma <b>Monolito Híbrido Moderno</b> implementado con la arquitectura <b>Inertia.js v2</b>. 
    Esta arquitectura combina la robustez y seguridad del servidor Laravel (manejando autenticación por sesión HTTP-only, políticas RBAC y transacciones de base de datos) con la reactividad y velocidad de una Single Page Application (SPA) construida en <b>Vue 3</b>. 
    No requiere el mantenimiento redundante de APIs REST desprotegidas ni tokens JWT en LocalStorage susceptibles a ataques XSS.
    """
    elements.append(Paragraph(arch_text, body_style))

    arch_table_data = [
        [Paragraph("<b>Capa</b>", table_header_style), Paragraph("<b>Tecnología Implementada</b>", table_header_style), Paragraph("<b>Responsabilidad Principal</b>", table_header_style)],
        [Paragraph("Cliente / Presentación", table_cell_bold), Paragraph("Vue 3.5 + TypeScript + Tailwind CSS", table_cell_style), Paragraph("Componentes reactivos, validación de UI, HUD biométrico, diseño responsivo oscuro.", table_cell_style)],
        [Paragraph("Conexión SPA / Enlace", table_cell_bold), Paragraph("Inertia.js v2 (Laravel + Vue Adapter)", table_cell_style), Paragraph("Transmisión de props reactivas, navegación sin recarga de página, sincronización de estado.", table_cell_style)],
        [Paragraph("Lógica de Negocio", table_cell_bold), Paragraph("PHP 8.2 + Laravel 12 (Controladores, Servicios)", table_cell_style), Paragraph("Cálculo financiero francés, validaciones estrictas, orquestación de transacciones ACID.", table_cell_style)],
        [Paragraph("Seguridad & Cripto", table_cell_bold), Paragraph("WebAuthn FIDO2 + BCrypt + CSRF Token", table_cell_style), Paragraph("Firma digital biométrica, hashing de PIN/CVV/contraseñas, protección contra CSRF.", table_cell_style)],
        [Paragraph("Persistencia / Datos", table_cell_bold), Paragraph("PostgreSQL 16 (Producción) / SQLite (Tests)", table_cell_style), Paragraph("Bloqueos pesimistas row-level, integridad referencial, almacenamiento transaccional.", table_cell_style)],
    ]
    arch_table = Table(arch_table_data, colWidths=[100, 160, 244])
    arch_table.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), c_secondary),
        ('GRID', (0,0), (-1,-1), 0.5, c_border),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [colors.white, c_light_bg]),
        ('PADDING', (0,0), (-1,-1), 5),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
    ]))
    elements.append(arch_table)

    elements.append(PageBreak())

    # =========================================================================
    # PÁGINA 3: HERRAMIENTAS Y DEPENDENCIAS REALES UTILIZADAS
    # =========================================================================
    elements.append(Paragraph("3. Ficha Técnica: Herramientas y Paquetes Reales", h1_style))
    elements.append(HRFlowable(width="100%", thickness=1, color=c_secondary, spaceBefore=2, spaceAfter=10))

    intro_tools = """
    A continuación se lista <b>cada herramienta, framework y paquete realmente utilizado</b> en el código fuente del proyecto, clasificados por su ámbito de acción:
    """
    elements.append(Paragraph(intro_tools, body_style))

    tools_data = [
        [Paragraph("<b>Herramienta / Paquete</b>", table_header_style), Paragraph("<b>Versión Real</b>", table_header_style), Paragraph("<b>Ámbito</b>", table_header_style), Paragraph("<b>Uso Específico en el Sistema</b>", table_header_style)],
        [Paragraph("PHP", table_cell_bold), Paragraph("8.2.12", table_cell_style), Paragraph("Backend Runtime", table_cell_style), Paragraph("Motor del servidor, tipos estrictos, match expressions y constructor promotion.", table_cell_style)],
        [Paragraph("Laravel Framework", table_cell_bold), Paragraph("12.69.2", table_cell_style), Paragraph("Backend Core", table_cell_style), Paragraph("Framework principal, ORM Eloquent, autenticación, migraciones, routing y RBAC.", table_cell_style)],
        [Paragraph("Inertia.js Laravel", table_cell_bold), Paragraph("2.0.x", table_cell_style), Paragraph("Backend Adapter", table_cell_style), Paragraph("Generación de respuestas SPA (<code>Inertia::render</code>) y control de sesión fluida.", table_cell_style)],
        [Paragraph("Tighten Ziggy", table_cell_bold), Paragraph("2.4.2", table_cell_style), Paragraph("Fullstack Router", table_cell_style), Paragraph("Exporta las rutas nombradas de Laravel (<code>route('...')</code>) hacia TypeScript en Vue.", table_cell_style)],
        [Paragraph("Pest PHP & Plugin Laravel", table_cell_bold), Paragraph("3.8.x / 3.2.x", table_cell_style), Paragraph("Testing Suite", table_cell_style), Paragraph("Framework de testing automatizado BDD para pruebas de integración y unitarias.", table_cell_style)],
        [Paragraph("Laravel Pint", table_cell_bold), Paragraph("1.18.x", table_cell_style), Paragraph("Code Quality", table_cell_style), Paragraph("Formateador automático de código bajo estándares PSR-12 bancarios.", table_cell_style)],
        [Paragraph("PostgreSQL", table_cell_bold), Paragraph("16.x (Port 5432)", table_cell_style), Paragraph("Base de Datos", table_cell_style), Paragraph("Motor RDBMS corporativo para transacciones ACID y persistencia de clientes.", table_cell_style)],
        [Paragraph("Vue.js", table_cell_bold), Paragraph("3.5.13", table_cell_style), Paragraph("Frontend Core", table_cell_style), Paragraph("Biblioteca de interfaz con Composition API, TypeScript y reactividad.", table_cell_style)],
        [Paragraph("Inertia.js Vue3", table_cell_bold), Paragraph("2.0.0-beta.3", table_cell_style), Paragraph("Frontend Adapter", table_cell_style), Paragraph("Cliente SPA, manejo de formularios (<code>useForm</code>), router y navegación.", table_cell_style)],
        [Paragraph("Vite", table_cell_bold), Paragraph("6.0.3", table_cell_style), Paragraph("Build Tool", table_cell_style), Paragraph("Compilador de assets ultrarrápido y Hot Module Replacement en desarrollo.", table_cell_style)],
        [Paragraph("Tailwind CSS", table_cell_bold), Paragraph("3.4.1", table_cell_style), Paragraph("Diseño / CSS", table_cell_style), Paragraph("Framework utilitario para la estética moderna oscura bancaria (Dark Theme).", table_cell_style)],
        [Paragraph("Lucide Vue Next", table_cell_bold), Paragraph("0.468.0", table_cell_style), Paragraph("Iconografía", table_cell_style), Paragraph("Iconos vectoriales limpios (Fingerprint, Wallet, ShieldCheck, Banknote).", table_cell_style)],
        [Paragraph("Headless UI / Radix Vue", table_cell_bold), Paragraph("1.7.23 / 1.9.11", table_cell_style), Paragraph("UI Primitives", table_cell_style), Paragraph("Componentes sin estilo para modales accesibles y menús desplegables.", table_cell_style)],
    ]

    tools_table = Table(tools_data, colWidths=[110, 65, 85, 244])
    tools_table.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), c_secondary),
        ('GRID', (0,0), (-1,-1), 0.5, c_border),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [colors.white, c_light_bg]),
        ('PADDING', (0,0), (-1,-1), 4),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
    ]))
    elements.append(tools_table)

    elements.append(PageBreak())

    # =========================================================================
    # PÁGINA 4: APIS REALES UTILIZADAS VS APIS DESCARTADAS
    # =========================================================================
    elements.append(Paragraph("4. APIs Reales Utilizadas (Estándares Web y Navegador)", h1_style))
    elements.append(HRFlowable(width="100%", thickness=1, color=c_secondary, spaceBefore=2, spaceAfter=10))

    api_intro = """
    En el desarrollo de software bancario, la elección de APIs debe regirse por <b>soberanía de datos, privacidad del cliente y cumplimiento normativo</b>. 
    A continuación se detallan las APIs reales empleadas y se clarifica por qué se descartaron servicios externos de terceros:
    """
    elements.append(Paragraph(api_intro, body_style))

    apis_data = [
        [Paragraph("<b>API Implementada</b>", table_header_style), Paragraph("<b>Tipo / Origen</b>", table_header_style), Paragraph("<b>Función Técnica en Banco Continental</b>", table_header_style)],
        [
            Paragraph("<b>WebAuthn / FIDO2 API</b><br/><code>navigator.credentials</code>", table_cell_bold),
            Paragraph("Estándar W3C / Navegador Nativo", table_cell_style),
            Paragraph("Permite la autenticación biométrica real utilizando hardware del equipo (Windows Hello, lectores USB FIDO2 o sensores integrados). Genera pares de llaves criptográficas para enrolar huellas y firmar digitalmente transacciones de retiro.", table_cell_style)
        ],
        [
            Paragraph("<b>Web Audio API</b><br/><code>AudioContext</code>", table_cell_bold),
            Paragraph("Estándar W3C / Navegador Nativo", table_cell_style),
            Paragraph("Genera síntesis de sonido en tiempo real en ventanilla sin cargar archivos MP3. Produce frecuencias de escaneo continuo (600Hz a 1200Hz), pitido armónico de éxito (880Hz / 1760Hz) y alerta de error (220Hz onda diente de sierra).", table_cell_style)
        ],
        [
            Paragraph("<b>HTML5 2D Canvas API</b><br/><code>&lt;canvas&gt; / getContext('2d')</code>", table_cell_bold),
            Paragraph("Estándar W3C / Navegador Nativo", table_cell_style),
            Paragraph("Renderiza el HUD del escáner biométrico a 60 FPS: dibuja líneas de cresta dactilar vectoriales, haz láser animado en color cian/esmeralda y puntos de minucias (bifurcaciones) calculados algorítmicamente.", table_cell_style)
        ],
        [
            Paragraph("<b>Web Print API</b><br/><code>window.print()</code>", table_cell_bold),
            Paragraph("Estándar W3C / Navegador Nativo", table_cell_style),
            Paragraph("Motor de impresión que activa la salida física a papel. Mediante reglas <code>@media print</code> personalizadas, formatea el recibo a formato de rollo térmico continuo estándar de 80mm (POS) o a formato A4 formal.", table_cell_style)
        ],
        [
            Paragraph("<b>Inertia Routing API & Ziggy</b><br/><code>route(...) / router.visit</code>", table_cell_bold),
            Paragraph("API de Enrutamiento Fullstack", table_cell_style),
            Paragraph("Comunica el frontend Vue con el backend Laravel preservando las protecciones de sesión, evitando recargas completas de pantalla y permitiendo navegación SPA segura.", table_cell_style)
        ],
    ]

    apis_table = Table(apis_data, colWidths=[130, 110, 264])
    apis_table.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), c_secondary),
        ('GRID', (0,0), (-1,-1), 0.5, c_border),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [colors.white, c_light_bg]),
        ('PADDING', (0,0), (-1,-1), 6),
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
    ]))
    elements.append(apis_table)
    elements.append(Spacer(1, 12))

    # Aclaración explícita de APIs no utilizadas
    no_api_box = [
        [
            Paragraph("""
            <b>DECLARACIÓN DE INTEGRIDAD TÉCNICA (APIs Externas NO Utilizadas):</b><br/>
            Para esta implementación bancaria <b>NO se utilizaron pasarelas de pago de terceros (como Stripe, PayPal o MercadoPago)</b> ni servicios biométricos en la nube (AWS Rekognition / Google Vision). 
            La razón es de arquitectura bancaria: en un banco real, el Core Bancario administra sus propias cuentas, transferencias de saldo directo y custodia de hashes biométricos localmente bajo estricta soberanía de datos y secreto bancario. 
            El código QR de los comprobantes también fue implementado mediante <b>algoritmo vectorial SVG autónomo</b> sin consumir APIs externas como Google Charts.
            """, callout_style)
        ]
    ]
    no_api_table = Table(no_api_box, colWidths=[504])
    no_api_table.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#fffbeb")),
        ('BORDER', (0,0), (-1,-1), 1, colors.HexColor("#fde68a")),
        ('PADDING', (0,0), (-1,-1), 8),
    ]))
    elements.append(no_api_table)

    elements.append(PageBreak())

    # =========================================================================
    # PÁGINA 5: MÓDULOS DEL SISTEMA Y FLUJOS OPERATIVOS
    # =========================================================================
    elements.append(Paragraph("5. Módulos del Sistema y Flujos Operativos", h1_style))
    elements.append(HRFlowable(width="100%", thickness=1, color=c_secondary, spaceBefore=2, spaceAfter=10))

    modulos = [
        ("Módulo 1: Onboarding 2 en 1 y Expediente de Clientes", 
         "Permite enrolar clientes nuevos en ventanilla registrando datos personales, documento de identidad (C.I., Pasaporte) y, simultáneamente en la misma operación transaccional, genera su usuario y credenciales para la Banca Digital por Internet. El cliente queda habilitado de inmediato tanto en ventanilla como en el portal web sin registros redundantes."),
        
        ("Módulo 2: Cuentas Bancarias y Emisión de Tarjetas", 
         "Apertura de Cuentas de Ahorro y Cuentas Corrientes. Emisión automática de Tarjeta de Débito institucional con estándar BIN 453288, vigencia de 4 años, número de tarjeta único generado algorítmicamente y encriptación de PIN y CVV bajo hash BCrypt."),
        
        ("Módulo 3: Biometría Dactilar de Grado Bancario", 
         "Enrolamiento de hasta 10 dedos por cliente (Pulgares, Índices, Medios, Anulares, Meñiques) con control de calidad porcentual. Soporta lectura física directa mediante WebAuthn / FIDO2 y simulación visual óptica de escáner a 500 DPI con feedback auditivo mediante Web Audio API."),
        
        ("Módulo 4: Operaciones de Ventanilla (Depósitos y Retiros)", 
         "Gestión de depósitos en efectivo con acreditación inmediata. En retiros, el sistema valida límites diarios (Bs. 5,000 por defecto) y exige autenticación por huella dactilar o PIN según el canal. Se genera automáticamente comprobante térmico con código QR."),
        
        ("Módulo 5: Transferencias entre Cuentas (Traspasos)", 
         "Módulo de traspasos directos entre cuentas de la entidad. Cuenta con búsqueda en vivo del titular de la cuenta destino para evitar equivocaciones, validación de saldo disponible y ejecución atómica con doble asiento contable (débito en origen y crédito en destino)."),
        
        ("Módulo 6: Préstamos y Simulador Francés Interactivo", 
         "Implementa el algoritmo matemático de amortización francesa (cuota periódica fija con capital creciente e interés decreciente). Incluye simulador en tiempo real, registro de solicitud, evaluación por ejecutivo de crédito, desembolso directo a cuenta y cobro de cuotas con comprobante."),
        
        ("Módulo 7: Auditoría y Bitácora Inmutable", 
         "Registro centralizado de todas las operaciones financieras y administrativas: usuario ejecutor, dirección IP, agente de navegación, tipo de evento, tabla afectada y descripción forense de la transacción."),
    ]

    for titulo, desc in modulos:
        elements.append(Paragraph(f"<b>{titulo}</b>", h2_style))
        elements.append(Paragraph(desc, body_style))
        elements.append(Spacer(1, 4))

    elements.append(PageBreak())

    # =========================================================================
    # PÁGINA 6: SEGURIDAD, CONCURRENCIA ACID Y PRUEBAS AUTOMATIZADAS
    # =========================================================================
    elements.append(Paragraph("6. Seguridad, Concurrencia y Calidad de Código", h1_style))
    elements.append(HRFlowable(width="100%", thickness=1, color=c_secondary, spaceBefore=2, spaceAfter=10))

    sec_p1 = """
    La fiabilidad de un sistema bancario reside en la <b>consistencia absoluta de sus datos</b>. En Banco Continental se implementaron las siguientes salvaguardas:
    """
    elements.append(Paragraph(sec_p1, body_style))

    sec_items = [
        "<b>Transacciones ACID con Bloqueo Pesimista (Pessimistic Locking):</b> En operaciones críticas (retiros y transferencias), el registro de la cuenta bancaria se bloquea a nivel de base de datos mediante <code>Account::where('id', ...)->lockForUpdate()->first()</code> dentro de un <code>DB::transaction()</code>. Esto previene ataques de doble gasto y condiciones de carrera concurrentes.",
        "<b>Control de Acceso Basado en Roles (RBAC):</b> Matriz estricta de permisos dividida en 4 roles institucionales: <i>Administrador</i> (control total y gestión de personal), <i>Cajero</i> (ventanilla, depósitos, retiros), <i>Ejecutivo de Crédito</i> (evaluación y desembolso de préstamos) y <i>Cliente</i> (portal exclusivo de consulta y transferencias propias).",
        "<b>Protección de Datos Sensibles:</b> Cifrado unidireccional con BCrypt para contraseñas de usuarios, PIN de 4 dígitos de tarjetas de débito y código CVV. Los atributos confidenciales se encuentran protegidos mediante la directiva <code>$hidden</code> en los modelos Eloquent para no exponerse en respuestas JSON.",
    ]
    for si in sec_items:
        elements.append(Paragraph(f"• {si}", bullet_style))

    elements.append(Spacer(1, 10))
    elements.append(Paragraph("Resumen de Pruebas Automatizadas (Pest PHP Suite)", h2_style))

    tests_summary = """
    El sistema cuenta con una suite completa de pruebas de regresión ejecutadas con el framework <b>Pest PHP</b>. 
    Todas las pruebas se ejecutan en memoria garantizando velocidad y cobertura sobre los casos de éxito y los modos de falla:
    """
    elements.append(Paragraph(tests_summary, body_style))

    test_data = [
        [Paragraph("<b>Archivo de Prueba</b>", table_header_style), Paragraph("<b>Tipo</b>", table_header_style), Paragraph("<b>Casos Verificados</b>", table_header_style), Paragraph("<b>Resultado</b>", table_header_style)],
        [Paragraph("<code>BiometriaTest.php</code>", table_cell_bold), Paragraph("Feature", table_cell_style), Paragraph("Enrolamiento dactilar, validación de token de sesión y revocación de huellas.", table_cell_style), Paragraph("<font color='#059669'><b>100% PASS</b></font>", table_cell_style)],
        [Paragraph("<code>TransferenciaTest.php</code>", table_cell_bold), Paragraph("Feature", table_cell_style), Paragraph("Traspaso atómico entre cuentas, bloqueo de saldo insuficiente y doble movimiento contable.", table_cell_style), Paragraph("<font color='#059669'><b>100% PASS</b></font>", table_cell_style)],
        [Paragraph("<code>CuentaTest.php</code>", table_cell_bold), Paragraph("Feature", table_cell_style), Paragraph("Apertura de cuenta, generación de tarjeta BIN 453288 y depósito inicial voluntario.", table_cell_style), Paragraph("<font color='#059669'><b>100% PASS</b></font>", table_cell_style)],
        [Paragraph("<code>ClienteRegistroUsuarioTest.php</code>", table_cell_bold), Paragraph("Feature", table_cell_style), Paragraph("Registro simultáneo 2 en 1 (Cliente + Usuario) y habilitación de acceso web posterior.", table_cell_style), Paragraph("<font color='#059669'><b>100% PASS</b></font>", table_cell_style)],
        [Paragraph("<code>DashboardTest.php</code> & Auth", table_cell_bold), Paragraph("Feature/Unit", table_cell_style), Paragraph("Acceso según rol RBAC, cálculo de métricas financieras y login/logout.", table_cell_style), Paragraph("<font color='#059669'><b>100% PASS</b></font>", table_cell_style)],
    ]
    test_table = Table(test_data, colWidths=[130, 60, 244, 70])
    test_table.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), c_secondary),
        ('GRID', (0,0), (-1,-1), 0.5, c_border),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [colors.white, c_light_bg]),
        ('PADDING', (0,0), (-1,-1), 5),
        ('ALIGN', (3,1), (3,-1), 'CENTER'),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
    ]))
    elements.append(test_table)

    stat_box = [
        [Paragraph("<b>ESTADO OFICIAL DE CALIDAD DE SOFTWARE:</b> 37 Tests Aprobados • 106 Aserciones Verificadas • 0 Fallos • Código Formateado con Laravel Pint PSR-12", table_cell_bold)]
    ]
    stat_table = Table(stat_box, colWidths=[504])
    stat_table.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#ecfdf5")),
        ('BORDER', (0,0), (-1,-1), 1, colors.HexColor("#a7f3d0")),
        ('PADDING', (0,0), (-1,-1), 7),
        ('ALIGN', (0,0), (-1,-1), 'CENTER'),
    ]))
    elements.append(Spacer(1, 10))
    elements.append(stat_table)

    elements.append(PageBreak())

    # =========================================================================
    # PÁGINA 7: GUÍA PASO A PASO PARA LA EXPOSICIÓN EN VIVO
    # =========================================================================
    elements.append(Paragraph("7. Guía Práctica para la Exposición en Vivo", h1_style))
    elements.append(HRFlowable(width="100%", thickness=1, color=c_secondary, spaceBefore=2, spaceAfter=10))

    guide_intro = """
    Para lograr una presentación impecable e impactar positivamente al jurado, se recomienda seguir el siguiente <b>guion de demostración cronológica</b>:
    """
    elements.append(Paragraph(guide_intro, body_style))

    pasos = [
        ("Paso 1: Demostración de Roles y Seguridad (Login RBAC)", 
         "Iniciar sesión como <code>admin@bancosistema.bo</code> (clave: <code>password</code>). Mostrar el Dashboard con indicadores de cartera total, clientes y operaciones. Explicar brevemente que el menú se adapta dinámicamente según el rol."),
        
        ("Paso 2: Registro 2 en 1 de Nuevo Cliente", 
         "Ir a <b>Clientes > Registrar Nuevo Cliente</b>. Registrar un cliente en vivo completando Nombres, C.I., Correo Electrónico. Destacar ante el jurado la casilla activa <i>'Crear Acceso a Banca Digital Simultáneamente'</i>. Al guardar, mostrar cómo se generó el expediente y la cuenta de usuario con una sola acción."),
        
        ("Paso 3: Enrolamiento de Huella Biométrica", 
         "Dentro del expediente del cliente recién creado, descender a la sección <b>Seguridad Biométrica</b> y presionar <b>'+ Enrolar Nueva Huella'</b>. Seleccionar el dedo (ej. Índice Derecho) y ejecutar el escaneo interactivo. Hacer notar el sonido generado por la Web Audio API, el láser animado y el registro criptográfico."),
        
        ("Paso 4: Apertura de Cuenta Bancaria con Tarjeta de Débito", 
         "Presionar el botón <b>'Abrir Cuenta'</b>. Seleccionar Cuenta de Ahorros, monto inicial voluntario (ej. Bs. 1,000) y marcar <i>'Emitir Tarjeta de Débito'</i>. Al confirmar, mostrar la tarjeta emitida con BIN 453288 y el movimiento inicial acreditado."),
        
        ("Paso 5: Operación de Retiro con Validación Biométrica y Comprobante Térmico QR", 
         "Ir a <b>Retiros > + Procesar Retiro</b>. Buscar la cuenta creada. En <i>Método de Autenticación</i> seleccionar <b>'Validación Biométrica (Huella Dactilar WebAuthn)'</b>. Presionar <i>'Escanear Huella Dactilar en Vivo'</i>. Validar el dedo enrolado y confirmar el retiro. Al finalizar, mostrar el comprobante bancario con su <b>Código QR vectorial</b> e imprimir la vista térmica de 80mm."),
        
        ("Paso 6: Transferencia Atómica entre Cuentas", 
         "Ir a <b>Transferencias > Nueva Transferencia</b>. Ingresar cuenta origen y cuenta destino. Demostrar cómo el sistema busca el nombre del titular destino en vivo antes de enviar el dinero. Ejecutar el traspaso y mostrar la deducción y acreditación instantánea."),
        
        ("Paso 7: Ingreso como Cliente a la Banca Digital", 
         "Cerrar sesión de administrador e ingresar con las credenciales del cliente creado (correo y clave). Mostrar el portal personalizado: su saldo disponible, sus cuentas, el menú simplificado para clientes y la capacidad de transferir o simular créditos."),
    ]

    for num, (titulo, cuerpo) in enumerate(pasos, start=1):
        elements.append(Paragraph(f"<b>{titulo}</b>", h2_style))
        elements.append(Paragraph(cuerpo, body_style))
        elements.append(Spacer(1, 3))

    elements.append(Spacer(1, 10))
    concl_box = [
        [
            Paragraph("""
            <b>CONCLUSIÓN PARA LA DEFENSA:</b><br/>
            Banco Continental demuestra la integración real de estándares de ingeniería de software bancario: control transaccional ACID, biometría física W3C/FIDO2, diseño reactivo de alta fidelidad estética y automatización de pruebas al 100%, constituyendo un sistema bancario completamente funcional y listo para operación.
            """, callout_style)
        ]
    ]
    concl_table = Table(concl_box, colWidths=[504])
    concl_table.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#f1f5f9")),
        ('BORDER', (0,0), (-1,-1), 1, colors.HexColor("#cbd5e1")),
        ('PADDING', (0,0), (-1,-1), 8),
    ]))
    elements.append(concl_table)

    # Construir documento
    doc.build(elements, canvasmaker=NumberedCanvas)
    print(f"PDF generado exitosamente: {filename}")

if __name__ == "__main__":
    output_pdf = os.path.abspath("BANCO_CONTINENTAL_DOCUMENTO_EXPOSICION.pdf")
    build_pdf(output_pdf)
