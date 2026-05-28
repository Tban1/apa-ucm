import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';

const ESTADO_LABELS = {
    pendiente:     { label: 'Pendiente',    cls: 'bg-gray-100 text-gray-600' },
    en_carga:      { label: 'En revisión',  cls: 'bg-blue-100 text-blue-700' },
    carga_cerrada: { label: 'Completo',     cls: 'bg-green-100 text-green-700' },
    en_evaluacion: { label: 'En eval.',     cls: 'bg-purple-100 text-purple-700' },
    evaluado:      { label: 'Evaluado',     cls: 'bg-indigo-100 text-indigo-700' },
    apelado:       { label: 'Apelado',      cls: 'bg-orange-100 text-orange-700' },
    cerrado:       { label: 'Cerrado',      cls: 'bg-slate-100 text-slate-600' },
};

export default function EstadoProceso({ periodo, periodos, facultades }) {
    const [expandida, setExpandida] = useState(null);

    const totalAcademicos = facultades.reduce((s, f) => s + f.total, 0);
    const totalEvaluados  = facultades.reduce((s, f) => s + f.evaluados, 0);
    const totalCerrados   = facultades.filter(f => f.proceso_cerrado).length;

    return (
        <>
            <Head title="Estado del Proceso" />
            <AppLayout title="Estado del Proceso por Facultad">

                {/* Cabecera */}
                <div className="flex items-center justify-between -mt-4 mb-6">
                    <div>
                        {periodo ? (
                            <p className="text-sm text-gray-500">
                                Período: <span className="font-medium text-gray-700">{periodo.nombre} {periodo.anio}</span>
                                {periodo.estado === 'activo' && (
                                    <span className="ml-2 text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">Activo</span>
                                )}
                            </p>
                        ) : (
                            <p className="text-sm text-gray-400">Sin período activo</p>
                        )}
                    </div>
                    <div className="flex gap-3">
                        {periodo && (
                            <>
                                <a
                                    href="/analista/reporte-calificaciones"
                                    target="_blank"
                                    rel="noreferrer"
                                    className="flex items-center gap-1.5 text-xs font-medium text-[#1B2D6B] border border-[#1B2D6B]/30 bg-white hover:bg-[#1B2D6B]/5 px-3 py-1.5 rounded-lg transition-colors"
                                >
                                    <PrintIcon /> Reporte calificaciones
                                </a>
                                <a
                                    href="/analista/incumplimientos"
                                    target="_blank"
                                    rel="noreferrer"
                                    className="flex items-center gap-1.5 text-xs font-medium text-red-700 border border-red-200 bg-white hover:bg-red-50 px-3 py-1.5 rounded-lg transition-colors"
                                >
                                    <PrintIcon /> Incumplimientos
                                </a>
                            </>
                        )}
                        <button
                            onClick={() => router.reload({ preserveScroll: true })}
                            className="flex items-center gap-1.5 text-xs text-gray-400 hover:text-gray-600 transition-colors"
                        >
                            <RefreshIcon /> Actualizar
                        </button>
                    </div>
                </div>

                {!periodo ? (
                    <div className="bg-white rounded-xl border border-dashed border-gray-300 p-10 text-center">
                        <p className="text-gray-400 text-sm">No hay un período activo.</p>
                    </div>
                ) : (
                    <>
                        {/* Stats globales */}
                        <div className="grid grid-cols-3 gap-4 mb-6">
                            <StatCard label="Total académicos" value={totalAcademicos} />
                            <StatCard label="Evaluados" value={totalEvaluados}
                                sub={totalAcademicos > 0 ? `${Math.round(totalEvaluados / totalAcademicos * 100)}%` : '—'}
                                accent />
                            <StatCard label="Facultades cerradas" value={`${totalCerrados} / ${facultades.length}`} />
                        </div>

                        {/* Tabla por facultad */}
                        {facultades.length === 0 ? (
                            <div className="bg-white rounded-xl border border-dashed border-gray-300 p-10 text-center">
                                <p className="text-gray-400 text-sm">No hay nóminas cargadas para este período.</p>
                            </div>
                        ) : (
                            <div className="space-y-3">
                                {facultades.map(f => {
                                    const abierta = expandida === f.id;
                                    const pct = f.total > 0 ? Math.round(f.evaluados / f.total * 100) : 0;

                                    return (
                                        <div key={f.id} className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                                            <div
                                                className="flex items-center gap-4 px-5 py-4 cursor-pointer hover:bg-gray-50 transition-colors"
                                                onClick={() => setExpandida(abierta ? null : f.id)}
                                            >
                                                {/* Nombre facultad */}
                                                <div className="flex-1 min-w-0">
                                                    <div className="flex items-center gap-2">
                                                        <p className="font-semibold text-gray-900 text-sm truncate">{f.nombre}</p>
                                                        {f.proceso_cerrado && (
                                                            <span className="shrink-0 text-xs font-medium bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">
                                                                Cerrado
                                                            </span>
                                                        )}
                                                        {!f.proceso_cerrado && f.recepcion_cerrada && (
                                                            <span className="shrink-0 text-xs font-medium bg-amber-50 text-amber-700 px-2 py-0.5 rounded-full">
                                                                Recepción cerrada
                                                            </span>
                                                        )}
                                                    </div>
                                                    {/* Barra de progreso */}
                                                    <div className="flex items-center gap-2 mt-1.5">
                                                        <div className="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                                            <div
                                                                className="h-full bg-[#1B2D6B] rounded-full transition-all"
                                                                style={{ width: `${pct}%` }}
                                                            />
                                                        </div>
                                                        <span className="text-xs text-gray-500 shrink-0">
                                                            {f.evaluados}/{f.total} evaluados ({pct}%)
                                                        </span>
                                                    </div>
                                                </div>

                                                {/* Badges de estados */}
                                                <div className="hidden sm:flex items-center gap-1.5 shrink-0">
                                                    {Object.entries(f.estados)
                                                        .filter(([, v]) => v > 0)
                                                        .map(([estado, cnt]) => {
                                                            const s = ESTADO_LABELS[estado] ?? { label: estado, cls: 'bg-gray-100 text-gray-600' };
                                                            return (
                                                                <span key={estado} className={`text-xs font-medium px-2 py-0.5 rounded-full ${s.cls}`}>
                                                                    {cnt} {s.label}
                                                                </span>
                                                            );
                                                        })}
                                                </div>

                                                {/* Chevron */}
                                                <ChevronIcon open={abierta} />
                                            </div>

                                            {/* Detalle expandido */}
                                            {abierta && (
                                                <div className="border-t border-gray-100 px-5 py-4 bg-gray-50/50">
                                                    <div className="flex flex-wrap gap-3 mb-3">
                                                        {Object.entries(f.estados).map(([estado, cnt]) => {
                                                            const s = ESTADO_LABELS[estado] ?? { label: estado, cls: 'bg-gray-100 text-gray-600' };
                                                            return (
                                                                <div key={estado} className="text-center">
                                                                    <p className={`text-lg font-bold ${s.cls.includes('text-') ? s.cls.split(' ').find(c => c.startsWith('text-')) : 'text-gray-700'}`}>
                                                                        {cnt}
                                                                    </p>
                                                                    <p className="text-xs text-gray-500">{s.label}</p>
                                                                </div>
                                                            );
                                                        })}
                                                        {f.con_licencia > 0 && (
                                                            <div className="text-center">
                                                                <p className="text-lg font-bold text-amber-600">{f.con_licencia}</p>
                                                                <p className="text-xs text-gray-500">Caso especial</p>
                                                            </div>
                                                        )}
                                                    </div>
                                                    {f.proceso_cerrado && f.acta_id && (
                                                        <a
                                                            href={`/secretario/acta-cierre/${f.acta_id}`}
                                                            target="_blank"
                                                            rel="noreferrer"
                                                            className="inline-flex items-center gap-1.5 text-xs font-medium text-indigo-700 hover:underline"
                                                        >
                                                            <PrintIcon /> Ver acta de cierre
                                                        </a>
                                                    )}
                                                </div>
                                            )}
                                        </div>
                                    );
                                })}
                            </div>
                        )}
                    </>
                )}
            </AppLayout>
        </>
    );
}

function StatCard({ label, value, sub, accent = false }) {
    return (
        <div className={`rounded-xl border p-5 ${accent ? 'border-[#1B2D6B]/20 bg-[#1B2D6B]/5' : 'border-gray-200 bg-white'}`}>
            <p className="text-xs text-gray-500">{label}</p>
            <p className={`text-2xl font-bold mt-0.5 ${accent ? 'text-[#1B2D6B]' : 'text-gray-900'}`}>{value}</p>
            {sub && <p className="text-xs text-gray-400 mt-0.5">{sub} completado</p>}
        </div>
    );
}

function ChevronIcon({ open }) {
    return (
        <svg className={`w-4 h-4 text-gray-400 shrink-0 transition-transform ${open ? 'rotate-180' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
        </svg>
    );
}

function PrintIcon() {
    return (
        <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5}
                d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.38-4.171l.36 4.171M6.34 18H5.25A2.25 2.25 0 013 15.75V9a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 9v6.75a2.25 2.25 0 01-2.25 2.25H17.66m-11.32 0l-.36-4.171M17.66 18l.36-4.171M6.34 18h11.32M9 9.75h6M9 12.75h6" />
        </svg>
    );
}

function RefreshIcon() {
    return (
        <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
        </svg>
    );
}
