<script setup lang="ts">
import BiometricScannerModal from '@/components/BiometricScannerModal.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowDownLeft,
    ArrowLeft,
    ArrowUpRight,
    CheckCircle2,
    CreditCard,
    FileText,
    Fingerprint,
    KeyRound,
    Mail,
    MapPin,
    Phone,
    Plus,
    Scan,
    Shield,
    ShieldCheck,
    Trash2,
    User,
    Wallet,
} from 'lucide-vue-next';
import { ref } from 'vue';

interface Tarjeta {
    id: number;
    numero_tarjeta: string;
    estado: string;
    fecha_vencimiento: string;
}

interface Cuenta {
    id: number;
    numero_cuenta: string;
    tipo: string;
    moneda: string;
    saldo: number;
    limite_retiro_diario: number;
    estado: string;
    tarjeta: Tarjeta | null;
}

interface Prestamo {
    id: number;
    codigo: string;
    tipo: string;
    monto_solicitado: number;
    monto_aprobado: number | null;
    cuota_mensual: number | null;
    saldo_pendiente: number;
    estado: string;
}

interface Huella {
    id: number;
    dedo: string;
    nombre_dedo?: string;
    credential_id?: string;
    calidad: number;
    estado: string;
    created_at: string;
    registrado_por?: {
        name: string;
        apellidos?: string;
    };
}

interface Cliente {
    id: number;
    nombres: string;
    apellidos: string;
    tipo_documento: string;
    numero_documento: string;
    email: string;
    telefono: string | null;
    direccion: string | null;
    fecha_nacimiento: string | null;
    estado: string;
    created_at: string;
    cuentas: Cuenta[];
    prestamos: Prestamo[];
    huellas?: Huella[];
    usuario?: {
        id: number;
        username: string;
        email: string;
        estado: string;
    } | null;
}

const props = defineProps<{
    cliente: Cliente;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Clientes', href: '/clientes' },
    { title: `${props.cliente.nombres} ${props.cliente.apellidos}`, href: `/clientes/${props.cliente.id}` },
];

const showBiometricModal = ref(false);

const onHuellaEnrolada = () => {
    showBiometricModal.value = false;
    router.reload({ only: ['cliente'] });
};

const eliminarHuella = async (huellaId: number) => {
    if (!confirm('¿Confirma que desea dar de baja este registro biométrico?')) return;
    try {
        const res = await fetch(`/api/biometria/${huellaId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as any)?.content || '',
            },
        });
        if (res.ok) {
            router.reload({ only: ['cliente'] });
        }
    } catch {
        alert('Error al eliminar la huella biométrica.');
    }
};

const habilitarAcceso = () => {
    const password = prompt('Ingresa la contraseña para el acceso a Banca Digital (o presiona Aceptar para usar password123):', 'password123');
    if (password === null) return;
    router.post(`/clientes/${props.cliente.id}/acceso-web`, { password });
};

const money = (n: number) => `Bs. ${Number(n || 0).toLocaleString('es-BO', { minimumFractionDigits: 2 })}`;
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Expediente: ${cliente.nombres} ${cliente.apellidos}`" />

        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-bold text-xl shadow-lg shadow-blue-500/20">
                        {{ cliente.nombres[0] }}{{ cliente.apellidos[0] }}
                    </div>
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold tracking-tight text-white">
                                {{ cliente.nombres }} {{ cliente.apellidos }}
                            </h1>
                            <span
                                :class="cliente.estado === 'activo' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' : 'bg-rose-500/20 text-rose-300 border-rose-500/30'"
                                class="rounded-full px-2.5 py-0.5 text-xs font-semibold border capitalize"
                            >
                                {{ cliente.estado }}
                            </span>
                        </div>
                        <p class="text-sm text-slate-400 mt-0.5 font-mono">
                            {{ cliente.tipo_documento.toUpperCase() }}: {{ cliente.numero_documento }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <Link
                        :href="`/cuentas/create?cliente_id=${cliente.id}`"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-3.5 py-2 text-xs font-semibold text-white shadow-md shadow-emerald-500/20 hover:from-emerald-500 hover:to-teal-500 transition-all cursor-pointer"
                    >
                        <Wallet class="h-4 w-4" />
                        Abrir Cuenta
                    </Link>
                    <Link
                        :href="`/prestamos/create?cliente_id=${cliente.id}`"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 px-3.5 py-2 text-xs font-semibold text-white shadow-md shadow-amber-500/20 hover:from-amber-500 hover:to-orange-500 transition-all cursor-pointer"
                    >
                        <CreditCard class="h-4 w-4" />
                        Solicitar Préstamo
                    </Link>
                    <Link
                        href="/clientes"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-slate-800 px-3.5 py-2 text-xs font-semibold text-slate-300 hover:bg-slate-700 transition-all"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Volver
                    </Link>
                </div>
            </div>

            <!-- Ficha de Datos Personales -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-sm">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-4 flex items-center gap-2">
                    <User class="h-4 w-4 text-blue-400" />
                    Información General del Expediente
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div class="rounded-xl bg-slate-950/50 p-3.5 border border-slate-800/80">
                        <div class="text-xs text-slate-500 mb-1 flex items-center gap-1.5">
                            <Mail class="h-3.5 w-3.5 text-blue-400" />
                            Correo Electrónico
                        </div>
                        <div class="font-medium text-slate-200 truncate">{{ cliente.email }}</div>
                    </div>
                    <div class="rounded-xl bg-slate-950/50 p-3.5 border border-slate-800/80">
                        <div class="text-xs text-slate-500 mb-1 flex items-center gap-1.5">
                            <Phone class="h-3.5 w-3.5 text-emerald-400" />
                            Teléfono / Contacto
                        </div>
                        <div class="font-medium text-slate-200">{{ cliente.telefono || 'No registrado' }}</div>
                    </div>
                    <div class="rounded-xl bg-slate-950/50 p-3.5 border border-slate-800/80">
                        <div class="text-xs text-slate-500 mb-1 flex items-center gap-1.5">
                            <MapPin class="h-3.5 w-3.5 text-rose-400" />
                            Domicilio
                        </div>
                        <div class="font-medium text-slate-200 truncate">{{ cliente.direccion || 'Sin dirección' }}</div>
                    </div>
                    <div class="rounded-xl bg-slate-950/50 p-3.5 border border-slate-800/80">
                        <div class="text-xs text-slate-500 mb-1 flex items-center gap-1.5">
                            <Shield class="h-3.5 w-3.5 text-amber-400" />
                            Fecha de Registro
                        </div>
                        <div class="font-medium text-slate-200">{{ new Date(cliente.created_at).toLocaleDateString('es-BO') }}</div>
                    </div>
                </div>

                <!-- Estado de Acceso a Banca Digital -->
                <div class="mt-4 pt-4 border-t border-slate-800/80 flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 rounded-xl border" :class="cliente.usuario ? 'bg-cyan-500/10 border-cyan-500/30 text-cyan-400' : 'bg-slate-800 border-slate-700 text-slate-400'">
                            <KeyRound class="h-4 w-4" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-200 flex items-center gap-2">
                                Acceso a Banca por Internet / Móvil
                                <span v-if="cliente.usuario" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-cyan-500/15 text-cyan-300 border border-cyan-500/30 font-mono">
                                    Activo (@{{ cliente.usuario.username }})
                                </span>
                                <span v-else class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700">
                                    Sin usuario digital
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-0.5">
                                {{ cliente.usuario ? `El cliente puede iniciar sesión en la plataforma con el correo ${cliente.email} o usuario ${cliente.usuario.username}.` : 'El cliente no tiene habilitado el acceso para iniciar sesión como usuario.' }}
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="habilitarAcceso"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-semibold border transition-all cursor-pointer flex items-center gap-1.5 shadow-sm"
                        :class="cliente.usuario ? 'border-slate-700 bg-slate-800/80 text-slate-300 hover:text-white hover:bg-slate-700' : 'border-cyan-500/40 bg-gradient-to-r from-cyan-600 to-blue-600 text-white shadow-cyan-500/20 hover:from-cyan-500 hover:to-blue-500'"
                    >
                        <KeyRound class="h-3.5 w-3.5" />
                        {{ cliente.usuario ? 'Cambiar Contraseña Digital' : 'Habilitar Acceso Digital Ahora' }}
                    </button>
                </div>
            </div>

            <!-- Cuentas Bancarias Vinculadas -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <Wallet class="h-5 w-5 text-emerald-400" />
                        Cuentas Bancarias Vinculadas ({{ cliente.cuentas.length }})
                    </h3>
                    <Link
                        :href="`/cuentas/create?cliente_id=${cliente.id}`"
                        class="text-xs font-semibold text-emerald-400 hover:text-emerald-300 flex items-center gap-1"
                    >
                        <Plus class="h-3.5 w-3.5" />
                        Nueva Cuenta
                    </Link>
                </div>

                <div v-if="cliente.cuentas.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="cta in cliente.cuentas"
                        :key="cta.id"
                        class="rounded-xl border border-slate-800 bg-slate-950/70 p-5 hover:border-slate-700 transition-all flex flex-col justify-between"
                    >
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20">
                                    Cuenta de {{ cta.tipo }}
                                </span>
                                <span
                                    :class="cta.estado === 'activa' ? 'text-emerald-400' : 'text-rose-400'"
                                    class="text-xs font-medium capitalize"
                                >
                                    ● {{ cta.estado }}
                                </span>
                            </div>
                            <div class="mt-3 font-mono text-lg font-bold text-white tracking-wider">
                                {{ cta.numero_cuenta }}
                            </div>
                            <div class="mt-2 text-2xl font-black text-emerald-400">
                                {{ money(cta.saldo) }}
                            </div>
                            <div class="mt-1 text-xs text-slate-400">
                                Límite diario: {{ money(cta.limite_retiro_diario) }}
                            </div>

                            <div v-if="cta.tarjeta" class="mt-3 pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-300">
                                <span class="flex items-center gap-1 text-indigo-400 font-mono">
                                    <CreditCard class="h-3.5 w-3.5" />
                                    **** {{ cta.tarjeta.numero_tarjeta.slice(-4) }}
                                </span>
                                <span class="text-slate-500">Exp: {{ cta.tarjeta.fecha_vencimiento }}</span>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-800/80 flex items-center justify-between gap-2">
                            <Link
                                :href="`/cuentas/${cta.id}`"
                                class="text-xs font-semibold text-blue-400 hover:text-blue-300"
                            >
                                Ver Detalle →
                            </Link>
                            <div class="flex gap-1.5">
                                <Link
                                    :href="`/depositos/create?cuenta=${cta.numero_cuenta}`"
                                    class="rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 px-2 py-1 text-xs font-semibold text-emerald-400 border border-emerald-500/30 flex items-center gap-1"
                                    title="Depositar"
                                >
                                    <ArrowDownLeft class="h-3 w-3" />
                                    Depositar
                                </Link>
                                <Link
                                    :href="`/retiros/create?cuenta=${cta.numero_cuenta}`"
                                    class="rounded-lg bg-amber-500/10 hover:bg-amber-500/20 px-2 py-1 text-xs font-semibold text-amber-400 border border-amber-500/30 flex items-center gap-1"
                                    title="Retirar"
                                >
                                    <ArrowUpRight class="h-3 w-3" />
                                    Retirar
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-8 text-slate-500 text-sm">
                    Este cliente aún no tiene cuentas bancarias abiertas.
                </div>
            </div>

            <!-- Préstamos y Créditos Vinculados -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <CreditCard class="h-5 w-5 text-amber-400" />
                        Préstamos y Solicitudes Crediticias ({{ cliente.prestamos.length }})
                    </h3>
                    <Link
                        :href="`/prestamos/create?cliente_id=${cliente.id}`"
                        class="text-xs font-semibold text-amber-400 hover:text-amber-300 flex items-center gap-1"
                    >
                        <Plus class="h-3.5 w-3.5" />
                        Nueva Solicitud
                    </Link>
                </div>

                <div v-if="cliente.prestamos.length > 0" class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-300">
                        <thead class="bg-slate-950/60 text-xs uppercase tracking-wider text-slate-400 border-b border-slate-800">
                            <tr>
                                <th class="px-4 py-3">Código</th>
                                <th class="px-4 py-3">Tipo</th>
                                <th class="px-4 py-3">Monto Solicitado</th>
                                <th class="px-4 py-3">Cuota Mensual</th>
                                <th class="px-4 py-3">Saldo Pendiente</th>
                                <th class="px-4 py-3">Estado</th>
                                <th class="px-4 py-3 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80">
                            <tr v-for="p in cliente.prestamos" :key="p.id" class="hover:bg-slate-800/30">
                                <td class="px-4 py-3 font-mono font-bold text-white">{{ p.codigo }}</td>
                                <td class="px-4 py-3 capitalize">{{ p.tipo }}</td>
                                <td class="px-4 py-3 font-semibold">{{ money(p.monto_solicitado) }}</td>
                                <td class="px-4 py-3 font-semibold text-amber-400">{{ money(p.cuota_mensual || 0) }}</td>
                                <td class="px-4 py-3 font-semibold text-rose-400">{{ money(p.saldo_pendiente) }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold uppercase bg-slate-800 text-slate-300">
                                        {{ p.estado.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Link
                                        :href="`/prestamos/${p.id}`"
                                        class="text-xs font-semibold text-blue-400 hover:text-blue-300"
                                    >
                                        Ver Detalle →
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="text-center py-8 text-slate-500 text-sm">
                    No existen solicitudes ni préstamos vigentes registrados.
                </div>
            </div>

            <!-- Sección Biometría y Huellas Dactilares (RF-016) -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 shadow-xl backdrop-blur-sm space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            <Fingerprint class="h-6 w-6" />
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                                Seguridad Biométrica (FIDO2 / WebAuthn)
                                <span class="rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] text-emerald-400 border border-emerald-500/20 font-bold">
                                    RF-016
                                </span>
                            </h2>
                            <p class="text-xs text-slate-400">
                                Enrolamiento de huellas dactilares para autorización de retiros de alta seguridad en ventanilla y cajeros.
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="showBiometricModal = true"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 px-4 py-2 text-xs font-bold text-white shadow-lg shadow-emerald-500/20 transition-all cursor-pointer"
                    >
                        <Scan class="h-4 w-4" />
                        Enrolar Nueva Huella
                    </button>
                </div>

                <!-- Lista de Huellas Enroladas -->
                <div v-if="cliente.huellas && cliente.huellas.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 pt-2">
                    <div
                        v-for="h in cliente.huellas"
                        :key="h.id"
                        class="rounded-xl bg-slate-950/80 border border-slate-800 p-3.5 flex items-start justify-between gap-3 hover:border-emerald-500/30 transition-all"
                    >
                        <div class="flex items-start gap-2.5">
                            <div class="p-2 rounded-lg bg-emerald-500/10 text-emerald-400 mt-0.5">
                                <Fingerprint class="h-5 w-5" />
                            </div>
                            <div>
                                <div class="font-bold text-white text-xs">{{ h.nombre_dedo || h.dedo.replace('_', ' ') }}</div>
                                <div class="text-[11px] text-emerald-400 font-mono mt-0.5">Calidad: {{ h.calidad }}% • Activo</div>
                                <div class="text-[10px] text-slate-500 mt-1">
                                    Enrolado el {{ new Date(h.created_at).toLocaleDateString('es-BO') }}
                                </div>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="eliminarHuella(h.id)"
                            title="Dar de baja huella"
                            class="text-slate-500 hover:text-rose-400 p-1 rounded-lg hover:bg-slate-800 transition-colors cursor-pointer"
                        >
                            <Trash2 class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <div v-else class="rounded-xl bg-slate-950/40 border border-dashed border-slate-800 p-6 text-center text-xs text-slate-400">
                    <Fingerprint class="h-8 w-8 text-slate-600 mx-auto mb-2" />
                    El cliente aún no tiene huellas dactilares enroladas. Haga clic en <strong class="text-emerald-400">"Enrolar Nueva Huella"</strong> para capturarla mediante el sensor óptico bancario o Windows Hello / WebAuthn.
                </div>
            </div>
        </div>

        <!-- Modal de Enrolamiento Biométrico -->
        <BiometricScannerModal
            :show="showBiometricModal"
            modo="enrolar"
            :cliente-id="cliente.id"
            :cliente-nombre="`${cliente.nombres} ${cliente.apellidos}`"
            :cliente-documento="cliente.numero_documento"
            @close="showBiometricModal = false"
            @enrolled="onHuellaEnrolada"
        />
    </AppLayout>
</template>
