# -*- coding: utf-8 -*-
"""
Generador de Documento Académico PDF:
ESPECIFICACIÓN FORMAL DE HISTORIAS DE USUARIO (USER STORIES)
DISTRIBUCIÓN EN 3 ROLES / RESPONSABLES DEL EQUIPO SCRUM
Carlos D. Ugarte (Product Owner + Analista)
Benjamín Yucra (Scrum Master + Backend)
Juan E. Cordero (Developer - Frontend)

Materia: Ingeniería de Software 1 - UAGRM FICH
Docente: Msc. Ing. Ligeron Rolando
"""

import os
import sys
import shutil
from reportlab.lib.pagesizes import letter
from reportlab.lib import colors
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak, KeepTogether, HRFlowable
)
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
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
        self.drawString(54, 752, "INGENIERÍA DE SOFTWARE I • SISTEMA BANCARIO MODULAR DE ALTA SEGURIDAD")
        self.setFont("Helvetica", 8)
        self.drawRightString(558, 752, "HISTORIAS DE USUARIO (SCRUM EN 3 ROLES)")
        self.setStrokeColor(colors.HexColor("#cbd5e1"))
        self.setLineWidth(0.6)
        self.line(54, 744, 558, 744)

        # Pie de página
        self.line(54, 45, 558, 45)
        self.setFont("Helvetica", 8)
        self.setFillColor(colors.HexColor("#64748b"))
        self.drawString(54, 32, "UAGRM FICH • Docente: Msc. Ing. Ligeron Rolando | Multi-Tenant: Banco Unión & BMSC")
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
    c_sky = colors.HexColor("#0284c7")         # Sky 600
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
        fontSize=23,
        leading=28,
        textColor=c_blue,
        alignment=0,
    )

    subtitle_style = ParagraphStyle(
        'CoverSubtitle',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=11,
        leading=15,
        textColor=c_muted,
        alignment=0,
    )

    h1_style = ParagraphStyle(
        'Heading1_Custom',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=14,
        leading=18,
        textColor=c_blue,
        spaceBefore=14,
        spaceAfter=6,
        keepWithNext=True,
    )

    h2_style = ParagraphStyle(
        'Heading2_Custom',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=11.5,
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
        fontSize=9,
        leading=13.5,
        textColor=colors.HexColor("#334155"),
        spaceAfter=6,
    )

    meta_style = ParagraphStyle(
        'Meta_Style',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8.5,
        leading=12,
        textColor=colors.HexColor("#475569"),
    )

    table_header = ParagraphStyle(
        'TableHeader',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=8,
        leading=11,
        textColor=colors.white,
        alignment=1,
    )

    table_cell = ParagraphStyle(
        'TableCell',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=7.8,
        leading=10.5,
        textColor=colors.HexColor("#1e293b"),
    )

    table_cell_bold = ParagraphStyle(
        'TableCellBold',
        parent=table_cell,
        fontName='Helvetica-Bold',
    )

    card_team_title = ParagraphStyle(
        'CardTeamTitle',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=10,
        leading=13,
        textColor=colors.white,
    )

    card_team_sub = ParagraphStyle(
        'CardTeamSub',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=7.5,
        leading=10,
        textColor=colors.HexColor("#38bdf8"),  # Sky 400
    )

    card_team_body = ParagraphStyle(
        'CardTeamBody',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=7.8,
        leading=11,
        textColor=colors.HexColor("#e2e8f0"),
    )

    hu_card_title = ParagraphStyle(
        'HUCardTitle',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=10,
        leading=13,
        textColor=colors.HexColor("#0f172a"),
    )

    hu_body = ParagraphStyle(
        'HUBody',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8.5,
        leading=12,
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
    story.append(Spacer(1, 12))

    banner_data = [[
        Paragraph("<font color='white'><b>INGENIERÍA DE SOFTWARE I • SISTEMA BANCARIO MODULAR DE ALTA SEGURIDAD</b></font>", ParagraphStyle(
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
    story.append(Spacer(1, 14))

    story.append(Paragraph("DOCUMENTO DE ESPECIFICACIÓN DE HISTORIAS DE USUARIO (USER STORIES)", title_style))
    story.append(Spacer(1, 5))
    story.append(Paragraph("Estructura de Requisitos Ágiles, Criterios de Aceptación INVEST y Distribución de Responsabilidades en el Equipo de Desarrollo (3 Roles)", subtitle_style))
    story.append(Spacer(1, 14))

    # Ficha técnica de la portada
    info_data = [
        [Paragraph("<b>Materia:</b>", meta_style), Paragraph("Ingeniería de Software 1 (INF-312)", meta_style)],
        [Paragraph("<b>Docente Evaluador:</b>", meta_style), Paragraph("<b>Msc. Ing. Ligeron Rolando</b>", meta_style)],
        [Paragraph("<b>Software Desarrollado:</b>", meta_style), Paragraph("Sistema Bancario Modular de Alta Seguridad (Multi-Tenant)", meta_style)],
        [Paragraph("<b>Entidades Integradas:</b>", meta_style), Paragraph("Banco Unión S.A. (BUN) y Banco Mercantil Santa Cruz (BMSC)", meta_style)],
        [Paragraph("<b>Arquitectura Tecnológica:</b>", meta_style), Paragraph("PHP 8.2 • Laravel 12 • Inertia.js v2 • Vue 3 • PostgreSQL 16 • WebAuthn", meta_style)],
        [Paragraph("<b>Metodología Aplicada:</b>", meta_style), Paragraph("Metodología Ágil Scrum / XP • Criterios INVEST de Mike Cohn y Ron Jeffries", meta_style)],
        [Paragraph("<b>Equipo de Desarrollo (3 Roles):</b>", meta_style), Paragraph("<b>Carlos D. Ugarte</b> (Product Owner + Analista)<br/><b>Benjamín Yucra</b> (Scrum Master + Backend)<br/><b>Juan E. Cordero</b> (Developer – Frontend)", meta_style)],
        [Paragraph("<b>Estado del Entregable:</b>", meta_style), Paragraph("<font color='#059669'><b>100% Implementado, Verificado en Código y Desplegado</b></font>", meta_style)],
    ]
    info_table = Table(info_data, colWidths=[140, 364])
    info_table.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, -1), colors.HexColor("#f1f5f9")),
        ('GRID', (0, 0), (-1, -1), 0.5, colors.HexColor("#cbd5e1")),
        ('TOPPADDING', (0, 0), (-1, -1), 4),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 4),
        ('LEFTPADDING', (0, 0), (-1, -1), 8),
        ('RIGHTPADDING', (0, 0), (-1, -1), 8),
    ]))
    story.append(info_table)
    story.append(Spacer(1, 14))

    # Resumen
    resumen_data = [[
        Paragraph(
            "<b>Objetivo y Distribución del Proyecto:</b><br/>"
            "El presente informe formaliza el catálogo completo de <b>Historias de Usuario (HU-001 al HU-009)</b>, "
            "organizadas formalmente en <b>3 frentes de desarrollo</b> asignados a los tres miembros del equipo Scrum: "
            "<b>Carlos D. Ugarte</b> (Requisitos, Seguridad y Expedientes), <b>Benjamín Yucra</b> (Lógica de Negocio, Transacciones y Créditos) "
            "y <b>Juan E. Cordero</b> (Frontend, Interfaces Reactivas y Biometría WebAuthn).",
            ParagraphStyle('ResumenText', parent=body_style, fontSize=8.5, leading=12)
        )
    ]]
    resumen_table = Table(resumen_data, colWidths=[504])
    resumen_table.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, -1), colors.HexColor("#eff6ff")),
        ('BOX', (0, 0), (-1, -1), 1, colors.HexColor("#3b82f6")),
        ('TOPPADDING', (0, 0), (-1, -1), 7),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 7),
        ('LEFTPADDING', (0, 0), (-1, -1), 9),
        ('RIGHTPADDING', (0, 0), (-1, -1), 9),
    ]))
    story.append(resumen_table)

    story.append(PageBreak())

    # =========================================================================
    # SECCIÓN 1: EQUIPO DE DESARROLLO Y DISTRIBUCIÓN DE RESPONSABILIDADES (3 CARDS)
    # =========================================================================
    story.append(Paragraph("1. Equipo de Desarrollo y Distribución de Responsabilidades", h1_style))
    story.append(HRFlowable(width="100%", thickness=1, color=c_blue, spaceBefore=2, spaceAfter=8))

    story.append(Paragraph(
        "En concordancia con el marco de trabajo <b>Scrum</b> y la organización en 3 capas de la ingeniería de software, "
        "las responsabilidades técnicas y funcionales del proyecto se distribuyen estrictamente de la siguiente manera:",
        body_style
    ))

    # 3 Tarjetas Visuales (Identicas a la imagen del usuario)
    card1_content = [
        Paragraph("<b>CARLOS D. UGARTE</b>", card_team_title),
        Paragraph("PRODUCT OWNER + ANALISTA", card_team_sub),
        HRFlowable(width="100%", thickness=0.5, color=colors.HexColor("#334155"), spaceBefore=3, spaceAfter=4),
        Paragraph("• Definición de requisitos e historias de usuario.", card_team_body),
        Paragraph("• Priorización del Product Backlog.", card_team_body),
        Paragraph("• Diseño de base de datos PostgreSQL 16.", card_team_body),
        Paragraph("• Documentación y validación de entregables.", card_team_body),
        Spacer(1, 3),
        Paragraph("<b>HU Asignadas:</b><br/>HU-001 (Usuarios), HU-002 (Clientes), HU-009 (Auditoría).", ParagraphStyle('HuAsig1', parent=card_team_body, textColor=colors.HexColor("#93c5fd"))),
    ]

    card2_content = [
        Paragraph("<b>BENJAMÍN YUCRA</b>", card_team_title),
        Paragraph("SCRUM MASTER + BACKEND", card_team_sub),
        HRFlowable(width="100%", thickness=0.5, color=colors.HexColor("#334155"), spaceBefore=3, spaceAfter=4),
        Paragraph("• Coordinación y facilitación de Scrum.", card_team_body),
        Paragraph("• Desarrollo con Laravel 12 y PHP 8.2.", card_team_body),
        Paragraph("• Reglas de negocio y arquitectura multi-tenant.", card_team_body),
        Paragraph("• Seguridad y transacciones financieras.", card_team_body),
        Spacer(1, 3),
        Paragraph("<b>HU Asignadas:</b><br/>HU-005 (Depósitos), HU-006 (Retiros), HU-008 (Préstamos).", ParagraphStyle('HuAsig2', parent=card_team_body, textColor=colors.HexColor("#93c5fd"))),
    ]

    card3_content = [
        Paragraph("<b>JUAN E. CORDERO</b>", card_team_title),
        Paragraph("DEVELOPER — FRONTEND", card_team_sub),
        HRFlowable(width="100%", thickness=0.5, color=colors.HexColor("#334155"), spaceBefore=3, spaceAfter=4),
        Paragraph("• Desarrollo con Vue 3 y TypeScript.", card_team_body),
        Paragraph("• Integración mediante Inertia.js v2.", card_team_body),
        Paragraph("• Interfaces con Tailwind CSS y Shadcn Vue.", card_team_body),
        Paragraph("• Componentes interactivos e integración con backend.", card_team_body),
        Spacer(1, 3),
        Paragraph("<b>HU Asignadas:</b><br/>HU-003 (Biometría), HU-004 (Cuentas), HU-007 (Transferencias).", ParagraphStyle('HuAsig3', parent=card_team_body, textColor=colors.HexColor("#93c5fd"))),
    ]

    team_data = [[card1_content, card2_content, card3_content]]
    team_table = Table(team_data, colWidths=[164, 164, 164])
    team_table.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, -1), colors.HexColor("#0f172a")),
        ('BOX', (0, 0), (0, 0), 1, colors.HexColor("#2563eb")),
        ('BOX', (1, 0), (1, 0), 1, colors.HexColor("#0284c7")),
        ('BOX', (2, 0), (2, 0), 1, colors.HexColor("#4f46e5")),
        ('TOPPADDING', (0, 0), (-1, -1), 8),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 8),
        ('LEFTPADDING', (0, 0), (-1, -1), 7),
        ('RIGHTPADDING', (0, 0), (-1, -1), 7),
        ('VALIGN', (0, 0), (-1, -1), 'TOP'),
    ]))
    story.append(team_table)
    story.append(Spacer(1, 14))

    # =========================================================================
    # SECCIÓN 2: MARCO CONCEPTUAL (3 C's e INVEST)
    # =========================================================================
    story.append(Paragraph("2. Marco Metodológico Ágil (3 C's y Criterios INVEST)", h1_style))
    story.append(HRFlowable(width="100%", thickness=1, color=c_blue, spaceBefore=2, spaceAfter=8))

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
        ('TOPPADDING', (0, 0), (-1, -1), 5),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 5),
        ('LEFTPADDING', (0, 0), (-1, -1), 7),
        ('RIGHTPADDING', (0, 0), (-1, -1), 7),
    ]))
    story.append(c_cards_table)
    story.append(Spacer(1, 14))

    # =========================================================================
    # SECCIÓN 3: MATRIZ DE TRAZABILIDAD Y CORRESPONDENCIA (CON RESPONSABLE)
    # =========================================================================
    story.append(Paragraph("3. Matriz de Correspondencia y Asignación de Historias", h1_style))
    story.append(HRFlowable(width="100%", thickness=1, color=c_blue, spaceBefore=2, spaceAfter=8))

    matriz_data = [
        [
            Paragraph("<b>Historia</b>", table_header),
            Paragraph("<b>Título de la Historia</b>", table_header),
            Paragraph("<b>Módulo del Sistema</b>", table_header),
            Paragraph("<b>Requisitos (RF)</b>", table_header),
            Paragraph("<b>Responsable Asignado</b>", table_header),
            Paragraph("<b>Pts</b>", table_header),
        ],
        [
            Paragraph("<b>HU-001</b>", table_cell_bold),
            Paragraph("Gestión de usuarios, roles y permisos", table_cell),
            Paragraph("Usuarios y Seguridad", table_cell),
            Paragraph("RF-001 al RF-003", table_cell),
            Paragraph("<b>Carlos D. Ugarte</b>", table_cell),
            Paragraph("5", table_cell),
        ],
        [
            Paragraph("<b>HU-002</b>", table_cell_bold),
            Paragraph("Registro y administración de clientes", table_cell),
            Paragraph("Expediente Clientes (KYC)", table_cell),
            Paragraph("RF-004 y RF-005", table_cell),
            Paragraph("<b>Carlos D. Ugarte</b>", table_cell),
            Paragraph("5", table_cell),
        ],
        [
            Paragraph("<b>HU-003</b>", table_cell_bold),
            Paragraph("Registro de autenticación biométrica WebAuthn", table_cell),
            Paragraph("Biometría y Criptografía", table_cell),
            Paragraph("RF-016", table_cell),
            Paragraph("<b>Juan E. Cordero</b>", table_cell),
            Paragraph("8", table_cell),
        ],
        [
            Paragraph("<b>HU-004</b>", table_cell_bold),
            Paragraph("Apertura y administración de cuentas bancarias", table_cell),
            Paragraph("Cuentas Ahorro/Corriente", table_cell),
            Paragraph("RF-006 al RF-008", table_cell),
            Paragraph("<b>Juan E. Cordero</b>", table_cell),
            Paragraph("5", table_cell),
        ],
        [
            Paragraph("<b>HU-005</b>", table_cell_bold),
            Paragraph("Procesamiento de depósitos bancarios", table_cell),
            Paragraph("Caja y Ventanilla", table_cell),
            Paragraph("RF-009 al RF-013", table_cell),
            Paragraph("<b>Benjamín Yucra</b>", table_cell),
            Paragraph("5", table_cell),
        ],
        [
            Paragraph("<b>HU-006</b>", table_cell_bold),
            Paragraph("Retiros bancarios con verificación biométrica", table_cell),
            Paragraph("Caja Segura", table_cell),
            Paragraph("RF-014 al RF-019", table_cell),
            Paragraph("<b>Benjamín Yucra</b>", table_cell),
            Paragraph("8", table_cell),
        ],
        [
            Paragraph("<b>HU-007</b>", table_cell_bold),
            Paragraph("Transferencias entre cuentas bancarias", table_cell),
            Paragraph("Banca Digital", table_cell),
            Paragraph("RF-011 y RF-017", table_cell),
            Paragraph("<b>Juan E. Cordero</b>", table_cell),
            Paragraph("5", table_cell),
        ],
        [
            Paragraph("<b>HU-008</b>", table_cell_bold),
            Paragraph("Gestión y simulación de préstamos (Francés/Alemán)", table_cell),
            Paragraph("Créditos y Amortización", table_cell),
            Paragraph("RF-020 al RF-025", table_cell),
            Paragraph("<b>Benjamín Yucra</b>", table_cell),
            Paragraph("8", table_cell),
        ],
        [
            Paragraph("<b>HU-009</b>", table_cell_bold),
            Paragraph("Auditoría, trazabilidad y reportes de seguridad", table_cell),
            Paragraph("Auditoría y Supervisión", table_cell),
            Paragraph("RF-026 al RF-030", table_cell),
            Paragraph("<b>Carlos D. Ugarte</b>", table_cell),
            Paragraph("5", table_cell),
        ],
    ]
    matriz_table = Table(matriz_data, colWidths=[46, 150, 100, 80, 100, 28])
    matriz_table.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, 0), c_dark),
        ('GRID', (0, 0), (-1, -1), 0.5, c_border),
        ('ROWBACKGROUNDS', (0, 1), (-1, -1), [colors.white, colors.HexColor("#f8fafc")]),
        ('ALIGN', (0, 1), (0, -1), 'CENTER'),
        ('ALIGN', (3, 1), (3, -1), 'CENTER'),
        ('ALIGN', (5, 1), (5, -1), 'CENTER'),
        ('TOPPADDING', (0, 0), (-1, -1), 4),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 4),
        ('LEFTPADDING', (0, 0), (-1, -1), 4),
        ('RIGHTPADDING', (0, 0), (-1, -1), 4),
    ]))
    story.append(matriz_table)

    story.append(PageBreak())

    # =========================================================================
    # SECCIÓN 4: ESPECIFICACIÓN EN 3 PARTES / RESPONSABLES
    # =========================================================================
    story.append(Paragraph("4. Especificación Detallada de Historias de Usuario (Por Responsable)", h1_style))
    story.append(HRFlowable(width="100%", thickness=1, color=c_blue, spaceBefore=2, spaceAfter=8))

    def render_hu_card(hu_id, title, responsible, role, want, benefit, criteria_list, technical_details, priority="Alta", points="5"):
        card_content = []

        header_text = f"<b>{hu_id}: {title}</b> &nbsp;|&nbsp; Responsable: <b>{responsible}</b> &nbsp;|&nbsp; Pts: <b>{points}</b>"
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
            ('TOPPADDING', (0, 0), (-1, -1), 4),
            ('BOTTOMPADDING', (0, 0), (-1, -1), 4),
            ('LEFTPADDING', (0, 0), (-1, -1), 7),
            ('RIGHTPADDING', (0, 0), (-1, -1), 7),
        ]))
        return KeepTogether([t, Spacer(1, 8)])

    # PARTE I: CARLOS D. UGARTE
    story.append(Paragraph("PARTE I: Requisitos, Seguridad y Expedientes (Responsable: Carlos D. Ugarte)", h2_style))
    story.append(HRFlowable(width="100%", thickness=0.5, color=colors.HexColor("#2563eb"), spaceBefore=1, spaceAfter=6))

    story.append(render_hu_card(
        hu_id="HU-001",
        title="Gestión de usuarios, roles y permisos",
        responsible="Carlos D. Ugarte (PO + Analista)",
        role="Administrador del banco",
        want="Registrar, modificar, activar, desactivar y asignar roles a los usuarios del sistema",
        benefit="Controlar el acceso a las funcionalidades según las responsabilidades de cada funcionario y asegurar el aislamiento institucional multi-tenant.",
        criteria_list=[
            "El administrador puede registrar usuarios asignando roles: Administrador, Cajero, Ejecutivo de Crédito o Cliente.",
            "Las contraseñas se almacenan mediante el algoritmo criptográfico Bcrypt / Argon2 (Hash seguro).",
            "Los usuarios con estado 'inactivo' tienen el acceso revocado automáticamente en el middleware.",
            "Cada funcionario está asociado de forma obligatoria a un banco específico (Banco Unión o BMSC).",
        ],
        technical_details="Controlador: UsuarioController.php | Modelo: User.php | Trait: BelongsToBanco | Vistas Vue: usuarios/ (Index, Create, Edit).",
        priority="Alta",
        points="5"
    ))

    story.append(render_hu_card(
        hu_id="HU-002",
        title="Registro y administración de clientes (KYC)",
        responsible="Carlos D. Ugarte (PO + Analista)",
        role="Funcionario bancario (Cajero / Ejecutivo)",
        want="Registrar, consultar, modificar y habilitar acceso web a los clientes del banco",
        benefit="Mantener un expediente financiero actualizado y facilitar la apertura de productos con prevención de duplicidad.",
        criteria_list=[
            "Registro completo: Nombres, apellidos, documento de identidad (CI), teléfono, dirección y correo electrónico.",
            "Validación estricta para evitar carnets de identidad duplicados dentro del mismo banco.",
            "Asignación automática del banco correspondiente según la sesión activa del funcionario.",
            "Generación de credenciales de 'Acceso Web' para que el cliente ingrese a Mi Banca Digital.",
        ],
        technical_details="Controlador: ClienteController.php | Modelo: Cliente.php | Reglas: ClienteRequest | Rutas: /clientes.",
        priority="Alta",
        points="5"
    ))

    story.append(render_hu_card(
        hu_id="HU-009",
        title="Auditoría, trazabilidad y reportes de seguridad",
        responsible="Carlos D. Ugarte (PO + Analista)",
        role="Administrador del banco / Auditor",
        want="Consultar los registros históricos de auditoría de todas las operaciones y exportar reportes",
        benefit="Garantizar la trazabilidad total, no repudio y cumplimiento de las normativas de fiscalización de la ASFI.",
        criteria_list=[
            "Registro automático e inmutable de cada evento sensible: Login, Depósito, Retiro, Transferencia y Crédito.",
            "Captura de metadatos forenses: ID de usuario, rol, banco, IP de origen, User-Agent, fecha y timestamp preciso.",
            "Filtros de búsqueda por rango de fechas, usuario responsable, tipo de acción y módulo del sistema.",
            "Exportación de reportes de auditoría en formato CSV y PDF para supervisión regulatoria.",
        ],
        technical_details="Controlador: ReporteController.php | Modelo: Auditoria.php | Middleware: RegistrarAuditoria | Vista: reportes/Index.vue.",
        priority="Media",
        points="5"
    ))

    story.append(PageBreak())

    # PARTE II: BENJAMÍN YUCRA
    story.append(Paragraph("PARTE II: Lógica de Negocio y Transacciones Financieras (Responsable: Benjamín Yucra)", h2_style))
    story.append(HRFlowable(width="100%", thickness=0.5, color=colors.HexColor("#0284c7"), spaceBefore=1, spaceAfter=6))

    story.append(render_hu_card(
        hu_id="HU-005",
        title="Procesamiento de depósitos bancarios en ventanilla",
        responsible="Benjamín Yucra (Scrum Master + Backend)",
        role="Cajero bancario",
        want="Registrar depósitos en efectivo o cheque en las cuentas de los clientes",
        benefit="Acreditar fondos de forma inmediata, garantizando la actualización atómica del saldo y emitiendo comprobante fiscal.",
        criteria_list=[
            "El cajero busca la cuenta destino y el sistema autocompleta el nombre del titular para confirmación visual.",
            "La acreditación de saldo se ejecuta dentro de una transacción atómica de base de datos (DB::transaction).",
            "Si ocurre cualquier excepción de conexión o base de datos, se ejecuta un Rollback automático.",
            "Generación de comprobante digital con código de referencia único, fecha, hora y cajero responsable.",
        ],
        technical_details="Controlador: DepositoController.php | Modelo: Deposito.php | Eventos: TransaccionRealizada | ComprobanteBancario.vue.",
        priority="Alta",
        points="5"
    ))

    story.append(render_hu_card(
        hu_id="HU-006",
        title="Retiros bancarios con verificación biométrica",
        responsible="Benjamín Yucra (Scrum Master + Backend)",
        role="Cajero bancario",
        want="Procesar retiros de dinero en ventanilla exigiendo la verificación biométrica de la huella del cliente",
        benefit="Garantizar que solo el titular legítimo retire fondos, erradicando la suplantación de identidad y sobregiros.",
        criteria_list=[
            "Verificación previa de que el saldo disponible sea estrictamente mayor o igual al monto solicitado.",
            "Bloqueo pesimista a nivel de fila (lockForUpdate) para erradicar condiciones de carrera o doble gasto.",
            "Invocación obligatoria de la validación biométrica WebAuthn antes de liberar los fondos.",
            "Al aprobarse, se descuenta el saldo contable y disponible de manera atómica y se emite comprobante.",
        ],
        technical_details="Controlador: RetiroController.php | Modelo: Retiro.php | Integración: BiometricScannerModal.vue con WebAuthn API.",
        priority="Muy Alta",
        points="8"
    ))

    story.append(render_hu_card(
        hu_id="HU-008",
        title="Gestión, simulación y desembolso de préstamos",
        responsible="Benjamín Yucra (Scrum Master + Backend)",
        role="Ejecutivo de crédito / Prestatario",
        want="Simular cuotas bajo los métodos Francés y Alemán, registrar solicitudes y procesar el desembolso",
        benefit="Determinar la capacidad de pago del cliente y formalizar el otorgamiento de préstamos bancarios.",
        criteria_list=[
            "Simulador interactivo que genera el plan de pagos completo (cuota mensual, interés, amortización y saldo).",
            "Cálculo exacto bajo el Sistema Francés (cuotas constantes) y Sistema Alemán (amortización de capital constante).",
            "Flujo de estados: Solicitado -> En Evaluación -> Aprobado / Rechazado -> Desembolsado -> Pagado.",
            "Acreditación automática del capital a la cuenta bancaria del prestatario tras la aprobación formal.",
        ],
        technical_details="Controlador: PrestamoController.php, PagoPrestamoController.php | Modelos: Prestamo.php, CuotaAmortizacion.php.",
        priority="Alta",
        points="8"
    ))

    story.append(PageBreak())

    # PARTE III: JUAN E. CORDERO
    story.append(Paragraph("PARTE III: Experiencia de Usuario, Interfaces y Banca Digital (Responsable: Juan E. Cordero)", h2_style))
    story.append(HRFlowable(width="100%", thickness=0.5, color=colors.HexColor("#4f46e5"), spaceBefore=1, spaceAfter=6))

    story.append(render_hu_card(
        hu_id="HU-003",
        title="Registro de autenticación biométrica WebAuthn",
        responsible="Juan E. Cordero (Developer - Frontend)",
        role="Cliente bancario / Cajero operativo",
        want="Registrar un autenticador biométrico compatible con huella dactilar mediante el estándar WebAuthn",
        benefit="Disponer de un mecanismo criptográfico de alta seguridad y no repudio para autorizar retiros de ventanilla.",
        criteria_list=[
            "El proceso de enrolamiento solicita la captura de la huella a través de la API WebAuthn del navegador.",
            "El servidor almacena exclusivamente el ID de credencial y la Clave Pública, nunca la imagen en bruto.",
            "Se valida criptográficamente el challenge y la firma digital emitida por el autenticador.",
            "El cliente o cajero visualiza el estado de enrolamiento mediante un badge verde 'Enrolado'.",
        ],
        technical_details="Controlador: BiometriaController.php | Modelo: HuellaBiometrica.php | Componente Vue: BiometricScannerModal.vue.",
        priority="Muy Alta",
        points="8"
    ))

    story.append(render_hu_card(
        hu_id="HU-004",
        title="Apertura y administración de cuentas bancarias",
        responsible="Juan E. Cordero (Developer - Frontend)",
        role="Funcionario bancario (Cajero / Administrador)",
        want="Crear y administrar cuentas de ahorro y corrientes asociadas a un cliente titular",
        benefit="Permitir a los clientes realizar operaciones de depósito, retiro y transferencias con numeración única.",
        criteria_list=[
            "El sistema genera un número de cuenta único con prefijo de entidad (10 dígitos estructurados).",
            "Interfaz responsiva con Vue 3 e Inertia.js para consultar en tiempo real el saldo contable y disponible.",
            "Diseño de tarjetas de cuenta con formato monetario estricto (Bs. BOB) y soporte de modo oscuro/claro.",
            "Visualización del extracto bancario con listado paginado de movimientos históricos.",
        ],
        technical_details="Controlador: CuentaController.php | Modelo: Cuenta.php | Vistas Vue: cuentas/Index.vue, Show.vue.",
        priority="Alta",
        points="5"
    ))

    story.append(render_hu_card(
        hu_id="HU-007",
        title="Transferencias de fondos entre cuentas bancarias",
        responsible="Juan E. Cordero (Developer - Frontend)",
        role="Cliente bancario / Funcionario",
        want="Realizar transferencias de dinero entre cuentas habilitadas con confirmación de titular destino",
        benefit="Enviar dinero de forma ágil y segura tanto en ventanilla como en línea desde Mi Banca Digital.",
        criteria_list=[
            "Búsqueda reactiva de la cuenta destino con consulta a la API para mostrar el titular antes de confirmar.",
            "Validación estricta en el formulario de Vue para evitar cuentas origen y destino idénticas.",
            "Modal de confirmación con doble validación visual de monto y destinatario antes del envío final.",
            "Actualización reactiva de saldos en pantalla y emisión inmediata del comprobante descargable.",
        ],
        technical_details="Controlador: TransferenciaController.php | Modelo: Transferencia.php | Vista: transferencias/Create.vue.",
        priority="Alta",
        points="5"
    ))

    story.append(PageBreak())

    # =========================================================================
    # SECCIÓN 5: CONCLUSIÓN Y FIRMAS DE LOS 3 MIEMBROS + DOCENTE
    # =========================================================================
    story.append(Paragraph("5. Verificación del Producto y Firmas de Conformidad", h1_style))
    story.append(HRFlowable(width="100%", thickness=1, color=c_blue, spaceBefore=2, spaceAfter=8))

    story.append(Paragraph(
        "El equipo de desarrollo certifica que las 9 Historias de Usuario aquí detalladas han sido distribuidas, "
        "desarrolladas, verificadas en pruebas de integración y desplegadas al 100% en la plataforma bancaria, "
        "cumpliendo con la arquitectura multi-tenant y los criterios de usabilidad y seguridad establecidos.",
        body_style
    ))
    story.append(Spacer(1, 14))

    # Firmas de los 3 integrantes + Docente (4 firmas)
    firmas_data = [
        [
            Paragraph("<b>CARLOS D. UGARTE</b><br/>Product Owner + Analista<br/>UAGRM FICH", ParagraphStyle('FirmaPO', alignment=1, fontSize=8, leading=11)),
            Paragraph("<b>BENJAMÍN YUCRA</b><br/>Scrum Master + Backend<br/>UAGRM FICH", ParagraphStyle('FirmaSM', alignment=1, fontSize=8, leading=11)),
            Paragraph("<b>JUAN E. CORDERO</b><br/>Developer – Frontend<br/>UAGRM FICH", ParagraphStyle('FirmaDEV', alignment=1, fontSize=8, leading=11)),
        ],
        [
            Paragraph("<br/><br/>___________________________<br/><b>Firma PO / Analista</b>", ParagraphStyle('F1', alignment=1, fontSize=7.5, leading=10)),
            Paragraph("<br/><br/>___________________________<br/><b>Firma SM / Backend</b>", ParagraphStyle('F2', alignment=1, fontSize=7.5, leading=10)),
            Paragraph("<br/><br/>___________________________<br/><b>Firma Frontend</b>", ParagraphStyle('F3', alignment=1, fontSize=7.5, leading=10)),
        ],
        [
            Paragraph("<br/><b>DOCENTE EVALUADOR TITULAR:</b><br/><b>Msc. Ing. Ligeron Rolando</b> — Docente de Ingeniería de Software 1 (INF-312)", ParagraphStyle('DocTit', alignment=1, fontSize=8.5, leading=12)),
            Paragraph("", ParagraphStyle('Blank')),
            Paragraph("<br/><br/>___________________________<br/><b>Sello y Calificación</b>", ParagraphStyle('DocFirma', alignment=1, fontSize=7.5, leading=10)),
        ]
    ]
    firmas_table = Table(firmas_data, colWidths=[168, 168, 168])
    firmas_table.setStyle(TableStyle([
        ('SPAN', (0, 2), (1, 2)),
        ('GRID', (0, 0), (-1, -1), 0.5, colors.HexColor("#cbd5e1")),
        ('BACKGROUND', (0, 0), (-1, 0), colors.HexColor("#f1f5f9")),
        ('BACKGROUND', (0, 2), (-1, 2), colors.HexColor("#eff6ff")),
        ('TOPPADDING', (0, 0), (-1, -1), 6),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 6),
    ]))
    story.append(firmas_table)

    doc.build(story, canvasmaker=NumberedCanvas)
    print(f"PDF generado con éxito: {filename}")

if __name__ == "__main__":
    target_path = "c:/xampp/htdocs/sistemaBancario/Documento_Historias_de_Usuario_Sistema_Bancario.pdf"
    build_pdf(target_path)
    shutil.copyfile(target_path, "c:/xampp/htdocs/sistemaBancario/public/Documento_Historias_de_Usuario_Sistema_Bancario.pdf")
    shutil.copyfile(target_path, "c:/xampp/htdocs/Bancario/Documento_Historias_de_Usuario_Sistema_Bancario.pdf")
    print("PDF copiado a public/ y a Bancario/")
