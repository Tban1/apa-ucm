import { Head, Link, useForm, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

const estadoLabels = {
    pendiente:     { label: 'Pendiente',    color: 'text-yellow-700 bg-yellow-100' },
    en_carga:      { label: 'En carga',      color: 'text-blue-700 bg-blue-100' },
    en_evaluacion: { label: 'En evaluación', color: 'text-purple-700 bg-purple-100' },
    evaluado:      { label: 'Evaluado',      color: 'text-green-700 bg-green-100' },
    apelado:       { label: 'En apelación',  color: 'text-orange-700 bg-orange-100' },
    cerrado:       { label: 'Cerrado',       color: 'text-gray-700 bg-gray-100' },
};

const califColors = {
    muy_bueno:  'text-green-700',
    bueno:      'text-blue-700',
    aceptable:  'text-amber-700',
    deficiente: 'text-red-700',
};

const apelacionEstados = {
    solicitada: { label: 'Solicitada — pendiente de revisión', cls: 'bg-yellow-50 border-yellow-200 text-yellow-800' },
    en_revision:{ label: 'Aprobada — puede cargar evidencias', cls: 'bg-blue-50 border-blue-200 text-blue-800' },
    resuelta:   { label: 'Resuelta — en re-evaluación CCA',   cls: 'bg-purple-50 border-purple-200 text-purple-800' },
    rechazada:  { label: 'Rechazada',                         cls: 'bg-red-50 border-red-200 text-red-800' },
};

export default function Academico({ stats, periodo }) {
    const { flash } = usePage().props;
    const estado      = estadoLabels[stats?.estado_nomina];
    const calificacion = stats?.calificacion ?? null;
    const apelacion   = stats?.apelacion ?? null;

    const puedeApelar = stats?.estado_nomina === 'evaluado' && !apelacion;

    const { data, setData, post, processing, errors, reset } = useForm({ motivo: '' });

    function submitApelacion(e) {
        e.preventDefault();
        post('/academico/apelacion', { onSuccess: () => reset() });
    }

    return (
        <>
            <Head title="Panel Académico" />
            <AppLayout title="Mi Panel">

                {periodo && (
                    <p className="text-sm text-gray-500 -mt-4 mb-6">
                        Período activo: <span className="font-medium text-gray-700">{periodo.nombre}</span>
                    </p>
                )}

                {flash?.success && (
                    <div className="mb-5 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                        {flash.success}
                    </div>
                )}
                {flash?.error && (
                    <div className="mb-5 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                        {flash.error}
                    </div>
                )}

                <div className="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
                    <StatCard label="Evidencias cargadas" value={stats?.evidencias_cargadas ?? '—'} />
                    <StatCard
                        label="Estado expediente"
                        value={
                            estado
                                ? <span className={`text-base px-2.5 py-1 rounded-full font-semibold ${estado.color}`}>{estado.label}</span>
                                : '—'
                        }
                    />
                    <StatCard
                        label="Calificación final"
                        value={
                            calificacion
                                ? <span className={`text-xl font-bold ${califColors[calificacion.calificacion] ?? 'text-gray-900'}`}>
                                    {calificacion.label}
                                  </span>
                                : '—'
                        }
                    />
                </div>

                {/* Banner calificación */}
                {calificacion && (
                    <div className="bg-green-50 border border-green-200 rounded-xl p-5 mb-6">
                        <p className="text-xs font-semibold text-green-700 uppercase tracking-wide mb-1">
                            Calificación APA — Resultado final
                        </p>
                        <p className={`text-3xl font-bold ${califColors[calificacion.calificacion]}`}>
                            {calificacion.label}
                        </p>
                        <p className="text-sm text-green-700 mt-1">
                            Puntaje: <span className="font-semibold">{calificacion.puntaje_total} / 100</span>
                            <span className="mx-2 text-green-400">·</span>
                            Registrada el {calificacion.fecha}
                        </p>
                    </div>
                )}

                {/* Estado apelación activa */}
                {apelacion && (
                    <div className={`border rounded-xl p-5 mb-6 ${apelacionEstados[apelacion.estado]?.cls ?? 'bg-gray-50 border-gray-200'}`}>
                        <p className="text-xs font-semibold uppercase tracking-wide mb-1 opacity-70">Apelación</p>
                        <p className="text-sm font-semibold">
                            {apelacionEstados[apelacion.estado]?.label ?? apelacion.estado}
                        </p>
                        {apelacion.resolucion && (
                            <p className="text-sm mt-1 opacity-80">{apelacion.resolucion}</p>
                        )}
                        {apelacion.estado === 'en_revision' && (
                            <Link
                                href="/academico/evidencias"
                                className="inline-block mt-3 text-sm font-medium text-white bg-[#1B2D6B] px-4 py-2 rounded-lg hover:bg-[#152558] transition-colors"
                            >
                                Cargar evidencias de apelación
                            </Link>
                        )}
                    </div>
                )}

                {/* Formulario solicitar apelación */}
                {puedeApelar && (
                    <div className="bg-white border border-gray-200 rounded-xl p-5 mb-6">
                        <h2 className="text-sm font-semibold text-gray-800 mb-1">Solicitar apelación</h2>
                        <p className="text-xs text-gray-500 mb-4">
                            Si no está de acuerdo con su calificación, puede solicitar una apelación ante el secretario de facultad.
                        </p>
                        <form onSubmit={submitApelacion} className="space-y-3">
                            <div>
                                <label className="block text-xs font-medium text-gray-600 mb-1">
                                    Motivo de la apelación <span className="text-gray-400">(mínimo 20 caracteres)</span>
                                </label>
                                <textarea
                                    rows={4}
                                    value={data.motivo}
                                    onChange={e => setData('motivo', e.target.value)}
                                    placeholder="Explique por qué solicita la revisión de su calificación..."
                                    className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1B2D6B]/30 focus:border-[#1B2D6B] resize-none"
                                />
                                {errors.motivo && <p className="text-xs text-red-600 mt-1">{errors.motivo}</p>}
                            </div>
                            <div className="flex justify-end">
                                <button
                                    type="submit"
                                    disabled={processing || data.motivo.length < 20}
                                    className="px-5 py-2.5 bg-orange-600 text-white text-sm font-medium rounded-lg hover:bg-orange-700 disabled:opacity-40 transition-colors"
                                >
                                    {processing ? 'Enviando...' : 'Enviar solicitud'}
                                </button>
                            </div>
                        </form>
                    </div>
                )}

                <div className="bg-white rounded-xl border border-gray-200 p-6 flex items-center justify-between">
                    <div>
                        <h2 className="font-semibold text-gray-800">Carga de evidencias</h2>
                        <p className="text-sm text-gray-500 mt-1">
                            Suba sus documentos por categoría APA para el período activo.
                        </p>
                    </div>
                    <Link
                        href="/academico/evidencias"
                        className="px-4 py-2 bg-[#1B2D6B] text-white text-sm font-medium rounded-lg hover:bg-[#152558] transition-colors shrink-0 ml-4"
                    >
                        Ir a evidencias
                    </Link>
                </div>

            </AppLayout>
        </>
    );
}

function StatCard({ label, value }) {
    return (
        <div className="bg-white rounded-xl border border-gray-200 p-5">
            <p className="text-sm text-gray-500">{label}</p>
            <div className="text-2xl font-bold text-gray-900 mt-1">{value}</div>
        </div>
    );
}
