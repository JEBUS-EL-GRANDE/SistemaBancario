# -*- coding: utf-8 -*-
"""
Generador de Documento Académico PDF:
ESPECIFICACIÓN FORMAL DE HISTORIAS DE USUARIO (USER STORIES)
Sistema Bancario Modular de Alta Seguridad (Banco Unión & Banco Mercantil Santa Cruz)
Materia: Ingeniería de Software 1 - UAGRM FICH
Docente: Msc. Ing. Ligeron Rolando
"""

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
    """Canvas para encabezado y pie de página con número total de páginas."""
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
            return  # Portada limpia sin encabezado

        self.saveState()
        self.setFont("Helvetica-Bold", 8)
        self.setFillColor(colors.HexColor("#475569"))

        # Encabezado
        self.drawString(54, 752, "UAGRM FICH • INGENIERÍA DE SOFTWARE 1")
        self.setFont("Helvetica", 8)
        self.drawRightString(558, 752, "SISTEMA BANCARIO — HISTORIAS DE USUARIO (SCRUM)")
        self.setStrokeColor(colors.HexColor("#cbd5e1"))
        self.setLineWidth(0.6)
        self.line(54, 744, 558, 744)

        # Pie de página
        self.line(54, 45, 558, 45)
        self.setFont("Helvetica", 8)
        self.setFillColor(colors.HexColor("#64748b"))
        self.drawString(54, 32, "Docente: Msc. Ing. Ligeron Rolando | Multi-Tenant: Banco Unión & BMSC")
        page_text = f"Página {self._pageNumber} de {page_count}"
        self.drawRightString(558, 32, page_text)
        self.restoreState()

def build_pdf(filename="Documento_Historias_de_Usuario_Sistema_Bancario.pdf"):
    doc = SimpleDocTemplate(
        filename,
        pagesize=letter,
        leftMargin=54,
        rightMargin=54,
        topMargin=54,
        bottomMargin=54
    )

    styles = getSampleStyleSheet()

    # Colores institucionales y de diseño
    c_primary = colors.HexColor("#0f172a")     # Slate 900
    c_blue = colors.HexColor("#1e3a8a")        # Blue 900 (Banco Unión)
    c_teal = colors.HexColor("#047857")        # Emerald 700 (BMSC)
    c_amber = colors.HexColor("#d97706")       # Amber 600
    c_dark = colors.HexColor("#1e293b")        # Slate 800
    c_muted = colors.HexColor("#475569")       # Slate 600
    c_light = colors.HexColor("#f8fafc")       # Slate 50
    c_border = colors.HexColor("#e2e8f0")      # Slate 200

    # Estilos tipográficos
    title_style = ParagraphStyle(
        'CoverTitle',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=24,
        leading=30,
        textColor=c_blue,
        alignment=0,
    )

    subtitle_style = ParagraphStyle(
        'CoverSubtitle',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=12,
        leading=16,
        textColor=c_muted,
        alignment=0,
    )

    h1_style = ParagraphStyle(
        'Heading1_Custom',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=15,
        leading=19,
        textColor=c_blue,
        spaceBefore=14,
        spaceAfter=6,
        keepWithNext=True,
    )

    h2_style = ParagraphStyle(
        'Heading2_Custom',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=12,
        leading=15,
        textColor=c_dark,
        spaceBefore=10,
        spaceAfter=4,
        keepWithNext=True,
    )

    body_style = ParagraphStyle(
        'Body_Custom',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=9.5,
        leading=14,
        textColor=colors.HexColor("#334155"),
        spaceAfter=6,
    )

    body_bold = ParagraphStyle(
        'Body_Bold',
        parent=body_style,
        fontName='Helvetica-Bold',
    )

    meta_style = ParagraphStyle(
        'Meta_Style',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=9,
        leading=13,
        textColor=colors.HexColor("#475569"),
    )

    table_header = ParagraphStyle(
        'TableHeader',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=8.5,
        leading=11,
        textColor=colors.white,
        alignment=1,
    )

    table_cell = ParagraphStyle(
        'TableCell',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8,
        leading=11,
        textColor=colors.HexColor("#1e293b"),
    )

    table_cell_bold = ParagraphStyle(
        'TableCellBold',
        parent=table_cell,
        fontName='Helvetica-Bold',
    )

    hu_card_title = ParagraphStyle(
        'HUCardTitle',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=11,
        leading=14,
        textColor=colors.HexColor("#0f172a"),
    )

    hu_body = ParagraphStyle(
        'HUBody',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=9,
        leading=13,
        textColor=colors.HexColor("#1e293b"),
    )

    story = []

    # =========================================================================
    # PORTADA ACADÉMICA FORMAL
    # =========================================================================
    story.append(Paragraph("UNIVERSIDAD AUTÓNOMA GABRIEL RENÉ MORENO", ParagraphStyle(
        'UagrmHeader', fontName='Helvetica-Bold', fontSize=13, leading=16, textColor=c_blue
    )))
    story.append(Paragraph("FACULTAD INTEGRAL DEL CHACO (FICH) — CAMIRI", ParagraphStyle(
        'FichHeader', fontName='Helvetica-Bold', fontSize=10, leading=13, textColor=c_muted
    )))
    story.append(Paragraph("Carrera de Ingeniería de Sistemas / Ingeniería Informática", ParagraphStyle(
        'CarreraHeader', fontName='Helvetica', fontSize=9, leading=12, textColor=c_muted
    )))
    story.append(Spacer(1, 14))

    # Barra decorativa institucional
    banner_data = [[
        Paragraph("<font color='white'><b>INGENIERÍA DE SOFTWARE 1 • PROYECTO FINAL DE SISTEMA BANCARIO</b></font>", ParagraphStyle(
            'BannerText', fontName='Helvetica-Bold', fontSize=9.5, leading=13, alignment=1
        ))
    ]]
    banner_table = Table(banner_data, colWidths=[504])
    banner_table.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, -1), c_blue),
        ('ALIGN', (0, 0), (-1, -1), 'CENTER'),
        ('VALIGN', (0, 0), (-1, -1), 'MIDDLE'),
        ('TOPPADDING', (0, 0), (-1, -1), 6),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 6),
    ]))
    story.append(banner_table)
    story.append(Spacer(1, 18))

    story.append(Paragraph("DOCUMENTO DE ESPECIFICACIÓN DE HISTORIAS DE USUARIO (USER STORIES)", title_style))
    story.append(Spacer(1, 6))
    story.append(Paragraph("Pila de Requisitos Ágiles (Product Backlog), Criterios de Aceptación INVEST, Matriz de Correspondencia con Requisitos Funcionales y Gestión de Tareas Técnicas", subtitle_style))
    story.append(Spacer(1, 16))

    # Ficha técnica de la portada
    info_data = [
        [Paragraph("<b>Materia:</b>", meta_style), Paragraph("Ingeniería de Software 1 (INF-312)", meta_style)],
        [Paragraph("<b>Docente Evaluador:</b>", meta_style), Paragraph("<b>Msc. Ing. Ligeron Rolando</b>", meta_style)],
        [Paragraph("<b>Software Desarrollado:</b>", meta_style), Paragraph("Sistema Bancario Modular de Alta Seguridad (Multi-Tenant)", meta_style)],
        [Paragraph("<b>Entidades Integradas:</b>", meta_style), Paragraph("Banco Unión S.A. (BUN) y Banco Mercantil Santa Cruz (BMSC)", meta_style)],
        [Paragraph("<b>Arquitectura Tecnológica:</b>", meta_style), Paragraph("PHP 8.2 • Laravel 12 • Inertia.js v2 • Vue 3 • PostgreSQL 16 • WebAuthn", meta_style)],
        [Paragraph("<b>Metodología Aplicada:</b>", meta_style), Paragraph("Metodología Ágil Scrum / XP • Criterios INVEST de Mike Cohn y Ron Jeffries", meta_style)],
        [Paragraph("<b>Fecha de Presentación:</b>", meta_style), Paragraph("Noviembre de 2026", meta_style)],
        [Paragraph("<b>Estado del Entregable:</b>", meta_style), Paragraph("<font color='#059669'><b>100% Implementado, Verificado en Código y Desplegado</b></font>", meta_style)],
    ]
    info_table = Table(info_data, colWidths=[140, 364])
    info_table.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, -1), colors.HexColor("#f1f5f9")),
        ('GRID', (0, 0), (-1, -1), 0.5, colors.HexColor("#cbd5e1")),
        ('TOPPADDING', (0, 0), (-1, -1), 5),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 5),
        ('LEFTPADDING', (0, 0), (-1, -1), 8),
        ('RIGHTPADDING', (0, 0), (-1, -1), 8),
    ]))
    story.append(info_table)
    story.append(Spacer(1, 20))

    # Cuadro resumen ejecutivo
    resumen_data = [[
        Paragraph(
            "<b>Objetivo del Presente Documento:</b><br/>"
            "El presente informe formaliza el catálogo completo de <b>Historias de Usuario (HU-001 al HU-009)</b> "
            "que guían el desarrollo del Sistema Bancario. Cada historia sigue el estándar internacional: "
            "<i>'Como [Rol], Quiero [Funcionalidad], Para [Beneficio de Negocio]'</i>, definiendo rigurosos criterios "
            "de aceptación, reglas de negocio atómicas, control transaccional ACID y trazabilidad hacia el código fuente.",
            ParagraphStyle('ResumenText', parent=body_style, fontSize=9, leading=13)
        )
    ]]
    resumen_table = Table(resumen_data, colWidths=[504])
    resumen_table.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, -1), colors.HexColor("#eff6ff")),
        ('BOX', (0, 0), (-1, -1), 1, colors.HexColor("#3b82f6")),
        ('TOPPADDING', (0, 0), (-1, -1), 8),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 8),
        ('LEFTPADDING', (0, 0), (-1, -1), 10),
        ('RIGHTPADDING', (0, 0), (-1, -1), 10),
    ]))
    story.append(resumen_table)

    story.append(PageBreak())

    # =========================================================================
    # SECCIÓN 1: MARCO CONCEPTUAL DE HISTORIAS DE USUARIO EN EL PROYECTO
    # =========================================================================
    story.append(Paragraph("1. Marco Teórico y Metodológico de las Historias de Usuario", h1_style))
    story.append(HRFlowable(width="100%", thickness=1, color=c_blue, spaceBefore=2, spaceAfter=8))

    story.append(Paragraph(
        "En el marco de la materia <b>Ingeniería de Software 1</b>, las Historias de Usuario representan la técnica "
        "fundamental para la captura y especificación de requisitos funcionales en metodologías ágiles (Scrum y Extreme Programming). "
        "A diferencia de las especificaciones monolíticas tradicionales (IEEE 830), las historias de usuario ponen el foco "
        "en el <b>valor entregado al usuario final</b> y en la verificabilidad inmediata a través de pruebas de aceptación.",
        body_style
    ))

    # Las 3 C's de Ron Jeffries y los principios INVEST
    c_cards_data = [
        [
            Paragraph("<b>Las 3 C's de Ron Jeffries</b>", table_header),
            Paragraph("<b>Criterios de Calidad INVEST (Mike Cohn)</b>", table_header),
        ],
        [
            Paragraph(
                "• <b>Card (Tarjeta):</b> Descripción sintética del requisito en formato estándar.<br/>"
                "• <b>Conversation (Conversación):</b> Diálogo continuo entre el Product Owner, el docente y el equipo para clarificar reglas de negocio.<br/>"
                "• <b>Confirmation (Confirmación):</b> Criterios de aceptación objetivos y verificables que determinan cuándo la historia está 'Terminada' (Definition of Done).",
                table_cell
            ),
            Paragraph(
                "• <b>I - Independiente:</b> Cada HU puede construirse y probarse sin bloqueos.<br/>"
                "• <b>N - Negociable:</b> Los detalles técnicos se acuerdan durante el sprint.<br/>"
                "• <b>V - Valiosa:</b> Aporta valor financiero o de seguridad al banco.<br/>"
                "• <b>E - Estimable:</b> Dimensionada en Story Points de complejidad.<br/>"
                "• <b>S - Small (Pequeña):</b> Abarcable dentro de una iteración.<br/>"
                "• <b>T - Testable (Comprobable):</b> Verificable con tests unitarios y de integración.",
                table_cell
            ),
        ]
    ]
    c_cards_table = Table(c_cards_data, colWidths=[250, 254])
    c_cards_table.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (0, 0), c_dark),
        ('BACKGROUND', (1, 0), (1, 0), c_blue),
        ('GRID', (0, 0), (-1, -1), 0.5, c_border),
        ('TOPPADDING', (0, 0), (-1, -1), 6),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 6),
        ('LEFTPADDING', (0, 0), (-1, -1), 8),
        ('RIGHTPADDING', (0, 0), (-1, -1), 8),
    ]))
    story.append(c_cards_table)
    story.append(Spacer(1, 12))

    story.append(Paragraph("Roles y Actores Identificados en el Dominio Bancario", h2_style))
    story.append(Paragraph(
        "El sistema modela cuatro roles con privilegios estrictamente delimitados (RBAC - Role Based Access Control) "
        "y aislamiento multi-tenant por entidad bancaria (Banco Unión ID: 1, Banco Mercantil Santa Cruz ID: 2):",
        body_style
    ))

    roles_data = [
        [
            Paragraph("<b>Rol del Sistema</b>", table_header),
            Paragraph("<b>Actor en el Dominio</b>", table_header),
            Paragraph("<b>Responsabilidades Clave en el Software</b>", table_header)
        ],
        [
            Paragraph("<b>Administrador</b>", table_cell_bold),
            Paragraph("Jefe de Operaciones / TI", table_cell),
            Paragraph("Gestión de usuarios y funcionarios, asignación de roles, auditoría de seguridad y reportes ASFI.", table_cell),
        ],
        [
            Paragraph("<b>Cajero</b>", table_cell_bold),
            Paragraph("Operador de Ventanilla", table_cell),
            Paragraph("Apertura de cuentas, recepción de depósitos, procesamiento de retiros biométricos y cobro de préstamos.", table_cell),
        ],
        [
            Paragraph("<b>Ejecutivo de Crédito</b>", table_cell_bold),
            Paragraph("Oficial de Préstamos", table_cell),
            Paragraph("Evaluación de solicitudes, simulación de amortización (Francés/Alemán), aprobación y desembolso.", table_cell),
        ],
        [
            Paragraph("<b>Cliente</b>", table_cell_bold),
            Paragraph("Usuario Final / Titular", table_cell),
            Paragraph("Consulta de saldos de cuentas de ahorro/corriente, transferencias web y simulación de créditos.", table_cell),
        ],
    ]
    roles_table = Table(roles_data, colWidths=[100, 110, 294])
    roles_table.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, 0), c_blue),
        ('GRID', (0, 0), (-1, -1), 0.5, c_border),
        ('ROWBACKGROUNDS', (0, 1), (-1, -1), [colors.white, colors.HexColor("#f8fafc")]),
        ('TOPPADDING', (0, 0), (-1, -1), 4),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 4),
        ('LEFTPADDING', (0, 0), (-1, -1), 6),
        ('RIGHTPADDING', (0, 0), (-1, -1), 6),
    ]))
    story.append(roles_table)
    story.append(Spacer(1, 14))

    # =========================================================================
    # SECCIÓN 2: MATRIZ DE CORRESPONDENCIA (HU vs REQUISITOS FUNCIONALES)
    # =========================================================================
    story.append(Paragraph("2. Matriz de Correspondencia (Historias de Usuario vs Requisitos)", h1_style))
    story.append(HRFlowable(width="100%", thickness=1, color=c_blue, spaceBefore=2, spaceAfter=8))

    story.append(Paragraph(
        "A continuación se presenta la matriz de trazabilidad que vincula cada Historia de Usuario con el módulo "
        "arquitectónico correspondiente, los Requisitos Funcionales del Silabario y su estimación de esfuerzo:",
        body_style
    ))

    matriz_data = [
        [
            Paragraph("<b>Historia</b>", table_header),
            Paragraph("<b>Título de la Historia de Usuario</b>", table_header),
            Paragraph("<b>Módulo del Sistema</b>", table_header),
            Paragraph("<b>Requisitos (RF)</b>", table_header),
            Paragraph("<b>Prioridad</b>", table_header),
            Paragraph("<b>Pts</b>", table_header),
        ],
        [
            Paragraph("<b>HU-001</b>", table_cell_bold),
            Paragraph("Gestión de usuarios, roles y permisos", table_cell),
            Paragraph("Usuarios y Seguridad", table_cell),
            Paragraph("RF-001 al RF-003", table_cell),
            Paragraph("<font color='#059669'><b>Alta</b></font>", table_cell),
            Paragraph("5", table_cell),
        ],
        [
            Paragraph("<b>HU-002</b>", table_cell_bold),
            Paragraph("Registro y administración de clientes", table_cell),
            Paragraph("Expediente Clientes (KYC)", table_cell),
            Paragraph("RF-004 y RF-005", table_cell),
            Paragraph("<font color='#059669'><b>Alta</b></font>", table_cell),
            Paragraph("5", table_cell),
        ],
        [
            Paragraph("<b>HU-003</b>", table_cell_bold),
            Paragraph("Registro de autenticación biométrica WebAuthn", table_cell),
            Paragraph("Biometría y Criptografía", table_cell),
            Paragraph("RF-016", table_cell),
            Paragraph("<font color='#d97706'><b>Muy Alta</b></font>", table_cell),
            Paragraph("8", table_cell),
        ],
        [
            Paragraph("<b>HU-004</b>", table_cell_bold),
            Paragraph("Apertura y administración de cuentas bancarias", table_cell),
            Paragraph("Cuentas de Ahorro/Corriente", table_cell),
            Paragraph("RF-006 al RF-008", table_cell),
            Paragraph("<font color='#059669'><b>Alta</b></font>", table_cell),
            Paragraph("5", table_cell),
        ],
        [
            Paragraph("<b>HU-005</b>", table_cell_bold),
            Paragraph("Procesamiento de depósitos bancarios", table_cell),
            Paragraph("Caja y Ventanilla", table_cell),
            Paragraph("RF-009 al RF-013", table_cell),
            Paragraph("<font color='#059669'><b>Alta</b></font>", table_cell),
            Paragraph("5", table_cell),
        ],
        [
            Paragraph("<b>HU-006</b>", table_cell_bold),
            Paragraph("Retiros bancarios con verificación biométrica", table_cell),
            Paragraph("Caja y Ventanilla Segura", table_cell),
            Paragraph("RF-014 al RF-019", table_cell),
            Paragraph("<font color='#d97706'><b>Muy Alta</b></font>", table_cell),
            Paragraph("8", table_cell),
        ],
        [
            Paragraph("<b>HU-007</b>", table_cell_bold),
            Paragraph("Transferencias entre cuentas bancarias", table_cell),
            Paragraph("Banca Digital / Pagos", table_cell),
            Paragraph("RF-011 y RF-017", table_cell),
            Paragraph("<font color='#059669'><b>Alta</b></font>", table_cell),
            Paragraph("5", table_cell),
        ],
        [
            Paragraph("<b>HU-008</b>", table_cell_bold),
            Paragraph("Gestión, simulación y desembolso de préstamos", table_cell),
            Paragraph("Créditos y Amortización", table_cell),
            Paragraph("RF-020 al RF-025", table_cell),
            Paragraph("<font color='#059669'><b>Alta</b></font>", table_cell),
            Paragraph("8", table_cell),
        ],
        [
            Paragraph("<b>HU-009</b>", table_cell_bold),
            Paragraph("Auditoría, trazabilidad y reportes de seguridad", table_cell),
            Paragraph("Auditoría y Supervisión", table_cell),
            Paragraph("RF-026 al RF-030", table_cell),
            Paragraph("<font color='#0284c7'><b>Media</b></font>", table_cell),
            Paragraph("5", table_cell),
        ],
    ]
    matriz_table = Table(matriz_data, colWidths=[48, 170, 110, 86, 60, 30])
    matriz_table.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, 0), c_dark),
        ('GRID', (0, 0), (-1, -1), 0.5, c_border),
        ('ROWBACKGROUNDS', (0, 1), (-1, -1), [colors.white, colors.HexColor("#f8fafc")]),
        ('ALIGN', (0, 1), (0, -1), 'CENTER'),
        ('ALIGN', (3, 1), (3, -1), 'CENTER'),
        ('ALIGN', (4, 1), (-1, -1), 'CENTER'),
        ('TOPPADDING', (0, 0), (-1, -1), 4),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 4),
        ('LEFTPADDING', (0, 0), (-1, -1), 4),
        ('RIGHTPADDING', (0, 0), (-1, -1), 4),
    ]))
    story.append(matriz_table)

    story.append(PageBreak())

    # =========================================================================
    # SECCIÓN 3: ESPECIFICACIÓN DETALLADA DE LAS 9 HISTORIAS DE USUARIO
    # =========================================================================
    story.append(Paragraph("3. Especificación Detallada de Historias de Usuario (Backlog Oficial)", h1_style))
    story.append(HRFlowable(width="100%", thickness=1, color=c_blue, spaceBefore=2, spaceAfter=8))

    # Helper para renderizar cada HU como una tarjeta formal
    def render_hu_card(hu_id, title, role, want, benefit, criteria_list, technical_details, priority="Alta", points="5"):
        card_content = []

        header_text = f"<b>{hu_id}: {title}</b> &nbsp;&nbsp;|&nbsp;&nbsp; Prioridad: <b>{priority}</b> &nbsp;|&nbsp; Estimación: <b>{points} Pts</b>"
        card_content.append([
            Paragraph(f"<font color='white'>{header_text}</font>", table_header)
        ])

        body_html = (
            f"<b>Como:</b> {role}<br/>"
            f"<b>Quiero:</b> {want}<br/>"
            f"<b>Para:</b> {benefit}"
        )
        card_content.append([Paragraph(body_html, hu_card_title)])

        crit_items = "".join([f"• {c}<br/>" for c in criteria_list])
        crit_html = (
            f"<b>Criterios de Aceptación (Definition of Done):</b><br/>"
            f"{crit_items}"
            f"<b>Implementación y Componentes Técnicos en el Código:</b><br/>"
            f"<i>{technical_details}</i>"
        )
        card_content.append([Paragraph(crit_html, hu_body)])

        t = Table(card_content, colWidths=[504])
        t.setStyle(TableStyle([
            ('BACKGROUND', (0, 0), (0, 0), c_blue),
            ('BACKGROUND', (0, 1), (0, 1), colors.HexColor("#f8fafc")),
            ('BACKGROUND', (0, 2), (0, 2), colors.white),
            ('BOX', (0, 0), (-1, -1), 1, colors.HexColor("#cbd5e1")),
            ('LINEBELOW', (0, 0), (0, 0), 1, c_blue),
            ('LINEBELOW', (0, 1), (0, 1), 0.5, colors.HexColor("#e2e8f0")),
            ('TOPPADDING', (0, 0), (-1, -1), 5),
            ('BOTTOMPADDING', (0, 0), (-1, -1), 5),
            ('LEFTPADDING', (0, 0), (-1, -1), 8),
            ('RIGHTPADDING', (0, 0), (-1, -1), 8),
        ]))
        return KeepTogether([t, Spacer(1, 10)])

    # HU-001
    story.append(render_hu_card(
        hu_id="HU-001",
        title="Gestión de usuarios, roles y permisos",
        role="Administrador del banco",
        want="Registrar, modificar, activar, desactivar y asignar roles a los usuarios del sistema",
        benefit="Controlar el acceso a las funcionalidades según las responsabilidades de cada funcionario y asegurar el aislamiento institucional multi-tenant.",
        criteria_list=[
            "El administrador puede registrar usuarios asignando uno de los roles: Administrador, Cajero, Ejecutivo de Crédito o Cliente.",
            "Las contraseñas se almacenan mediante el algoritmo criptográfico Bcrypt / Argon2 (Hash seguro).",
            "Los usuarios con estado 'inactivo' tienen el acceso revocado automáticamente en el middleware de autenticación.",
            "Cada funcionario está asociado de forma obligatoria a un banco específico (Banco Unión o BMSC).",
            "La vista de gestión implementa paginación, filtros de búsqueda y confirmación modal antes de cambiar estados.",
        ],
        technical_details="Controlador: UsuarioController.php | Modelo: User.php | Trait: BelongsToBanco | Vistas Vue: resources/js/pages/usuarios/ (Index, Create, Edit).",
        priority="Alta",
        points="5"
    ))

    # HU-002
    story.append(render_hu_card(
        hu_id="HU-002",
        title="Registro y administración de clientes (KYC)",
        role="Funcionario bancario (Cajero / Ejecutivo)",
        want="Registrar, consultar, modificar y habilitar acceso web a los clientes del banco",
        benefit="Mantener un expediente financiero actualizado y facilitar la apertura de productos con prevención de duplicidad.",
        criteria_list=[
            "Registro completo: Nombres, apellidos, documento de identidad (CI), teléfono, dirección y correo electrónico.",
            "Validación estricta en backend y frontend para evitar carnets de identidad duplicados dentro del mismo banco.",
            "Asignación automática del banco correspondiente según la sesión activa del funcionario.",
            "Posibilidad de generar credenciales de 'Acceso Web' para que el cliente ingrese a Mi Banca Digital.",
            "Cumplimiento de la política de autorización que impide a clientes ver expedientes de terceros.",
        ],
        technical_details="Controlador: ClienteController.php | Modelo: Cliente.php | Reglas de Validación: ClienteRequest | Rutas: /clientes.",
        priority="Alta",
        points="5"
    ))

    # HU-003
    story.append(render_hu_card(
        hu_id="HU-003",
        title="Registro de autenticación biométrica WebAuthn",
        role="Cliente bancario / Cajero operativo",
        want="Registrar un autenticador biométrico compatible con huella dactilar mediante el estándar WebAuthn",
        benefit="Disponer de un mecanismo criptográfico de alta seguridad y no repudio para autorizar retiros de ventanilla.",
        criteria_list=[
            "El proceso de enrolamiento solicita la captura de la huella a través de la API WebAuthn del navegador.",
            "El servidor almacena exclusivamente el ID de credencial y la Clave Pública, nunca la imagen en bruto de la huella.",
            "Se valida criptográficamente el challenge y la firma digital emitida por el autenticador.",
            "El cliente o cajero puede verificar el estado de enrolamiento (badge verde 'Enrolado').",
            "Manejo de errores si el sensor no responde o el dispositivo no cuenta con hardware biométrico compatible.",
        ],
        technical_details="Controlador: BiometriaController.php | Modelo: HuellaBiometrica.php | Componente Vue: BiometricScannerModal.vue.",
        priority="Muy Alta",
        points="8"
    ))

    # HU-004
    story.append(render_hu_card(
        hu_id="HU-004",
        title="Apertura y administración de cuentas bancarias",
        role="Funcionario bancario (Cajero / Administrador)",
        want="Crear y administrar cuentas de ahorro y cuentas corrientes asociadas a un cliente titular",
        benefit="Permitir a los clientes realizar operaciones de depósito, retiro y transferencias con numeración única.",
        criteria_list=[
            "El sistema genera un número de cuenta único con prefijo de entidad (10 dígitos estructurados).",
            "Se define el tipo de cuenta (Caja de Ahorro o Cuenta Corriente) y la moneda oficial (BOB - Bolivianos).",
            "Se lleva el registro separado y consistente del Saldo Contable y del Saldo Disponible.",
            "Permite suspender o bloquear cuentas por orden judicial o administrativa, impidiendo nuevos débitos.",
            "El cliente puede consultar en tiempo real el saldo de sus cuentas en la interfaz de 'Mis Cuentas'.",
        ],
        technical_details="Controlador: CuentaController.php | Modelo: Cuenta.php | Migración: create_cuentas_table | Vistas: cuentas/Index.vue, Show.vue.",
        priority="Alta",
        points="5"
    ))

    story.append(PageBreak())

    # HU-005
    story.append(render_hu_card(
        hu_id="HU-005",
        title="Procesamiento de depósitos bancarios en ventanilla",
        role="Cajero bancario",
        want="Registrar depósitos en efectivo o cheque en las cuentas de los clientes",
        benefit="Acreditar fondos de forma inmediata, garantizando la actualización atómica del saldo y emitiendo comprobante fiscal.",
        criteria_list=[
            "El cajero busca la cuenta destino y el sistema autocompleta el nombre del titular para confirmación visual.",
            "La acreditación de saldo se ejecuta dentro de una transacción atómica de base de datos (DB::transaction).",
            "Si ocurre cualquier excepción de conexión o base de datos, se ejecuta un Rollback automático.",
            "Generación de comprobante digital imprimible con código de referencia único, fecha, hora y cajero responsable.",
            "Botones de acceso rápido para montos frecuentes (Bs. 50, 100, 200, 500, 1000) para acelerar la atención.",
        ],
        technical_details="Controlador: DepositoController.php | Modelo: Deposito.php | Eventos: TransaccionRealizada | Componente: ComprobanteBancario.vue.",
        priority="Alta",
        points="5"
    ))

    # HU-006
    story.append(render_hu_card(
        hu_id="HU-006",
        title="Retiros bancarios con verificación biométrica",
        role="Cajero bancario",
        want="Procesar retiros de dinero en ventanilla exigiendo la verificación biométrica de la huella del cliente",
        benefit="Garantizar que solo el titular legítimo retire fondos, erradicando la suplantación de identidad y sobregiros.",
        criteria_list=[
            "Verificación previa de que el saldo disponible sea estrictamente mayor o igual al monto solicitado.",
            "Comprobación de límites diarios de retiro fijados por la normativa de la entidad.",
            "Invocación obligatoria de la validación biométrica WebAuthn antes de liberar los fondos.",
            "Si la biometría es rechazada, la operación se cancela y se registra el intento fallido en la auditoría.",
            "Al aprobarse, se descuenta el saldo contable y disponible de manera atómica y se emite el comprobante de caja.",
        ],
        technical_details="Controlador: RetiroController.php | Modelo: Retiro.php | Integración: BiometricScannerModal.vue con WebAuthn API.",
        priority="Muy Alta",
        points="8"
    ))

    # HU-007
    story.append(render_hu_card(
        hu_id="HU-007",
        title="Transferencias de fondos entre cuentas bancarias",
        role="Cliente bancario / Funcionario",
        want="Realizar transferencias de dinero entre cuentas habilitadas con confirmación de titular destino",
        benefit="Enviar dinero de forma ágil y segura tanto en ventanilla como en línea desde Mi Banca Digital.",
        criteria_list=[
            "Búsqueda reactiva de la cuenta destino con consulta a la API para mostrar el titular antes de confirmar.",
            "Validación estricta de que la cuenta de origen y destino no sean la misma.",
            "El débito de la cuenta emisora y el abono a la cuenta receptora se ejecutan en un solo bloque transaccional atómico.",
            "Modal de confirmación con doble validación de monto y destinatario antes del envío final.",
            "Registro de la referencia de transferencia y emisión del comprobante bancario para respaldo del cliente.",
        ],
        technical_details="Controlador: TransferenciaController.php | Modelo: Transferencia.php | Endpoint: /api/cuentas/buscar | Vista: transferencias/Create.vue.",
        priority="Alta",
        points="5"
    ))

    # HU-008
    story.append(render_hu_card(
        hu_id="HU-008",
        title="Gestión, simulación y desembolso de préstamos",
        role="Ejecutivo de crédito / Prestatario",
        want="Simular cuotas bajo los métodos Francés y Alemán, registrar solicitudes y procesar el desembolso",
        benefit="Determinar la capacidad de pago del cliente y formalizar el otorgamiento de préstamos bancarios.",
        criteria_list=[
            "Simulador interactivo que genera el plan de pagos completo (cuota mensual, interés, amortización y saldo deudor).",
            "Cálculo exacto bajo el Sistema Francés (cuotas constantes) y Sistema Alemán (amortización de capital constante).",
            "Flujo de estados del crédito: Solicitado -> En Evaluación -> Aprobado / Rechazado -> Desembolsado -> Pagado.",
            "Al aprobarse, el desembolso abona automáticamente el capital en la cuenta bancaria del prestatario.",
            "Módulo de pago de cuotas con actualización en tiempo real de la tabla de amortización de la deuda.",
        ],
        technical_details="Controlador: PrestamoController.php, PagoPrestamoController.php | Modelos: Prestamo.php, CuotaAmortizacion.php | Simulador.vue.",
        priority="Alta",
        points="8"
    ))

    # HU-009
    story.append(render_hu_card(
        hu_id="HU-009",
        title="Auditoría, trazabilidad y reportes de seguridad",
        role="Administrador del banco / Auditor",
        want="Consultar los registros históricos de auditoría de todas las operaciones y exportar reportes",
        benefit="Garantizar la trazabilidad total, no repudio y cumplimiento de las normativas de fiscalización de la ASFI.",
        criteria_list=[
            "Registro automático e inmutable de cada evento sensible: Login, Logout, Depósito, Retiro, Transferencia y Crédito.",
            "Captura de metadatos forenses: ID de usuario, rol, banco, IP de origen, User-Agent, fecha y timestamp preciso.",
            "Filtros de búsqueda por rango de fechas, usuario responsable, tipo de acción y módulo del sistema.",
            "Exportación de reportes de auditoría en formato CSV y PDF para entregas regulatorias externas.",
            "Visualización de métricas consolidadas en el Dashboard administrativo.",
        ],
        technical_details="Controlador: ReporteController.php | Modelo: Auditoria.php | Middleware: RegistrarAuditoria | Vista: reportes/Index.vue.",
        priority="Media",
        points="5"
    ))

    story.append(PageBreak())

    # =========================================================================
    # SECCIÓN 4: GESTIÓN DE TAREAS E INCONVENIENTES TÉCNICOS
    # =========================================================================
    story.append(Paragraph("4. Gestión de Tareas Técnicas e Inconvenientes Superados", h1_style))
    story.append(HRFlowable(width="100%", thickness=1, color=c_blue, spaceBefore=2, spaceAfter=8))

    story.append(Paragraph(
        "Como parte de la disciplina de <b>Ingeniería de Software</b>, durante la implementación de las historias "
        "de usuario surgieron desafíos técnicos complejos inherentes a la concurrencia bancaria, la seguridad criptográfica "
        "y el modelo multi-tenant. A continuación se documentan las tareas críticas ejecutadas y la resolución de cada obstáculo:",
        body_style
    ))

    inconvenientes_data = [
        [
            Paragraph("<b>Historia / Módulo</b>", table_header),
            Paragraph("<b>Inconveniente Técnico Encontrado</b>", table_header),
            Paragraph("<b>Solución de Ingeniería Aplicada</b>", table_header),
        ],
        [
            Paragraph("<b>HU-005 y HU-006<br/>(Caja y Ventanilla)</b>", table_cell_bold),
            Paragraph(
                "<b>Riesgo de Condición de Carrera (Race Condition):</b><br/>"
                "Si dos transacciones (ej. un retiro en ventanilla y una transferencia web) debitan fondos "
                "de la misma cuenta en el mismo milisegundo, existía el riesgo de provocar saldo negativo (Doble Gasto).",
                table_cell
            ),
            Paragraph(
                "<b>Solución con Bloqueo Pesimista (Pessimistic Locking):</b><br/>"
                "Se implementó <code>DB::transaction()</code> combinado con <code>Cuenta::where('id', $id)->lockForUpdate()->first()</code>. "
                "Esto asegura que la fila en PostgreSQL se bloquee a nivel de base de datos hasta que la transacción actual confirme (COMMIT) o revierta (ROLLBACK).",
                table_cell
            ),
        ],
        [
            Paragraph("<b>HU-003 y HU-006<br/>(Biometría WebAuthn)</b>", table_cell_bold),
            Paragraph(
                "<b>Cumplimiento de Privacidad y No Repudio:</b><br/>"
                "Las normativas bancarias prohíben estrictamente almacenar imágenes o mapas de bits de huellas dactilares "
                "en la base de datos por riesgos de filtración.",
                table_cell
            ),
            Paragraph(
                "<b>Solución con Criptografía Asimétrica WebAuthn:</b><br/>"
                "El sensor biométrico actúa como autenticador FIDO2. Solo se transmite al servidor un reto firmado "
                "y se almacena el ID de credencial junto a la <b>Clave Pública SHA-256</b>. Si la base de datos fuera comprometida, es matemáticamente imposible reconstruir la huella.",
                table_cell
            ),
        ],
        [
            Paragraph("<b>HU-008<br/>(Préstamos)</b>", table_cell_bold),
            Paragraph(
                "<b>Discrepancia de Decimales en Amortización:</b><br/>"
                "Al dividir las cuotas en plazos largos (ej. 36 o 60 meses), la acumulación de centavos causaba que la última "
                "cuota no liquidara exactamente a cero el saldo residual.",
                table_cell
            ),
            Paragraph(
                "<b>Algoritmo de Ajuste Residual en Última Cuota:</b><br/>"
                "Se calibró la fórmula financiera con redondeo a dos decimales (<code>round($monto, 2)</code>) y en la cuota final <i>N</i> "
                "se calcula el capital amortizado exactamente igual al saldo anterior pendiente, forzando un saldo final de Bs. 0.00.",
                table_cell
            ),
        ],
        [
            Paragraph("<b>HU-001 al HU-009<br/>(Multi-Tenant)</b>", table_cell_bold),
            Paragraph(
                "<b>Aislamiento Estricto entre Banco Unión y BMSC:</b><br/>"
                "Riesgo de fuga de información donde un cajero del Banco Mercantil visualice accidentalmente clientes o cuentas del Banco Unión.",
                table_cell
            ),
            Paragraph(
                "<b>Global Scope en Eloquent (BelongsToBanco):</b><br/>"
                "Se creó un Trait global que intercepta todas las consultas Eloquent (<code>SELECT * FROM clientes WHERE banco_id = ?</code>), "
                "inyectando automáticamente el ID del banco del usuario autenticado sin requerir cláusulas manuales en cada controlador.",
                table_cell
            ),
        ],
    ]
    inconvenientes_table = Table(inconvenientes_data, colWidths=[100, 190, 214])
    inconvenientes_table.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, 0), c_dark),
        ('GRID', (0, 0), (-1, -1), 0.5, c_border),
        ('ROWBACKGROUNDS', (0, 1), (-1, -1), [colors.white, colors.HexColor("#f8fafc")]),
        ('TOPPADDING', (0, 0), (-1, -1), 5),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 5),
        ('LEFTPADDING', (0, 0), (-1, -1), 6),
        ('RIGHTPADDING', (0, 0), (-1, -1), 6),
    ]))
    story.append(inconvenientes_table)
    story.append(Spacer(1, 14))

    # =========================================================================
    # SECCIÓN 5: CONCLUSIONES Y FIRMAS
    # =========================================================================
    story.append(Paragraph("5. Conclusión y Verificación del Producto", h1_style))
    story.append(HRFlowable(width="100%", thickness=1, color=c_blue, spaceBefore=2, spaceAfter=8))

    story.append(Paragraph(
        "El 100% de las 9 Historias de Usuario especificadas han sido desarrolladas, probadas con datos reales de "
        "demostración y verificadas en la plataforma web interactiva. El sistema bancario cumple con las directivas de "
        "integridad referencial en PostgreSQL, autenticación WebAuthn y estándares de usabilidad (Nielsen Norman Group y Garrett), "
        "constituyendo un sistema robusto, escalable y académicamente solvente.",
        body_style
    ))
    story.append(Spacer(1, 20))

    # Cuadro de firmas académicas
    firmas_data = [
        [
            Paragraph("<b>EQUIPO DE DESARROLLO</b><br/>Ingeniería de Software 1<br/>UAGRM Facultad Integral del Chaco", ParagraphStyle('Firma1', alignment=1, fontSize=8.5, leading=12)),
            Paragraph("<b>DOCENTE EVALUADOR</b><br/>Msc. Ing. Ligeron Rolando<br/>Docente Titular INF-312", ParagraphStyle('Firma2', alignment=1, fontSize=8.5, leading=12)),
        ],
        [
            Paragraph("<br/><br/>_____________________________________<br/><b>Firma del Estudiante / Equipo</b>", ParagraphStyle('Firma3', alignment=1, fontSize=8, leading=11)),
            Paragraph("<br/><br/>_____________________________________<br/><b>Sello y Firma de Aprobación</b>", ParagraphStyle('Firma4', alignment=1, fontSize=8, leading=11)),
        ]
    ]
    firmas_table = Table(firmas_data, colWidths=[250, 254])
    firmas_table.setStyle(TableStyle([
        ('GRID', (0, 0), (-1, -1), 0.5, colors.HexColor("#cbd5e1")),
        ('BACKGROUND', (0, 0), (-1, 0), colors.HexColor("#f1f5f9")),
        ('TOPPADDING', (0, 0), (-1, -1), 8),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 8),
    ]))
    story.append(firmas_table)

    # Construir documento con canvas numerado
    doc.build(story, canvasmaker=NumberedCanvas)
    print(f"PDF generado con éxito: {filename}")

if __name__ == "__main__":
    import shutil
    target_path = "c:/xampp/htdocs/sistemaBancario/Documento_Historias_de_Usuario_Sistema_Bancario.pdf"
    build_pdf(target_path)
    shutil.copyfile(target_path, "c:/xampp/htdocs/sistemaBancario/public/Documento_Historias_de_Usuario_Sistema_Bancario.pdf")
    shutil.copyfile(target_path, "c:/xampp/htdocs/Bancario/Documento_Historias_de_Usuario_Sistema_Bancario.pdf")
    print("PDF copiado a public/ y a Bancario/")
