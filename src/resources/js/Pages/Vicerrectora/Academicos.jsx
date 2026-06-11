import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useState, useMemo } from 'react';
import AppLayout from '@/Layouts/AppLayout';

const CONCEPTOS = [
    { value: 'excelente',  label: 'Excelente' },
    { value: 'muy_bueno',  label: 'Muy Bueno' },
    { value: 'bueno',      label: 'Bueno' },
    { value: 'regular',    label: 'Regular' },
    { value: 'deficiente', label: 'Deficiente' },
];

export default function Academicos({ periodo, academicos }) {
    const { flash } = usePage().props;
    const [comentandoId, setComentandoId] = useState(null);
    const form = useForm({ comentario: '' });

    const [busqueda, setBusqueda]         = useState('');
    const [filtroFacultad, setFiltroFacultad] = useState('');
    const [filtroConcepto, setFiltroConcepto] = useState('');

    function enviarComentario(evaluacionId) {
        form.post(`/vicerrectora/evaluaciones/${evaluacionId}/comentario`, {
            preserveScroll: true,
            onSuccess: () => { setComentandoId(null); form.reset(); },
        });
    }

    const facultades = useMemo(
        () => [...new Set(academicos.map(a => a.facultad).filter(Boolean))].sort(),
        [academicos]
    );

    const filtrados = useMemo(() => {
        const q = busqueda.trim().toLowerCase();
        return academicos.filter(a => {
            if (q && !a.name.toLowerCase().includes(q) && !a.rut.toLowerCase().includes(q)) return false;
            if (filtroFacultad && a.facultad !== filtroFacultad) return false;
            if (filtroConcepto && a.calificacion !== filtroConcepto) return false;
            return true;
        });
    }, [academicos, busqueda, filtroFacultad, filtroConcepto]);

    return (
        <>
            <Head title="Calificaciones" />
            <AppLayout title="Calificaciones — Vicerrectoría">

                {flash?.success && (
                    <div className="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                        {flash.success}
                    </div>
                )}

                {!periodo ? (
                    <p className="text-gray-400 text-sm">No hay período activo.</p>
                ) : (
                    <div className="space-y-4">
                        {/* Filtros */}
                        <div className="flex flex-wrap gap-3">
                            <input
                                type="text"
                                value={busqueda}
                                onChange={e => setBusqueda(e.target.value)}
                                placeholder="Buscar por nombre o RUT..."
                                className="flex-1 min-w-48 text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-[#1B2D6B]/30"
                            />
                            <select
                                value={filtroFacultad}
                                onChange={e => setFiltroFacultad(e.target.value)}
                                className="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-[#1B2D6B]/30"
                            >
                                <option value="">Todas las facultades</option>
                                {facultades.map(f => (
                                    <option key={f} value={f}>{f}</option>
                                ))}
                            </select>
                            <select
                                value={filtroConcepto}
                                onChange={e => setFiltroConcepto(e.target.value)}
                                className="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-[#1B2D6B]/30"
                            >
                                <option value="">Todos los conceptos</option>
                                {CONCEPTOS.map(c => (
                                    <option key={c.value} value={c.value}>{c.label}</option>
                                ))}
                            </select>
                        </div>

                        {/* Resultados */}
                        {filtrados.length === 0 ? (
                            <p className="text-gray-400 text-sm">No hay académicos para mostrar.</p>
                        ) : filtrados.map(a => (
                            <div key={a.nomina_id} className="bg-white rounded-xl border border-gray-200 p-4">
                                <div className="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p className="font-semibold text-gray-900">{a.name}</p>
                                        <p className="text-xs text-gray-500">{a.rut} · {a.facultad} · {a.categoria}</p>
                                        {a.nota_final !== null ? (
                                            <p className="text-sm mt-2">
                                                Nota final: <strong>{a.nota_final}</strong> — {a.concepto}
                                                {a.vigente_hasta && (
                                                    <span className="text-gray-400 ml-2">Vigente hasta {a.vigente_hasta}</span>
                                                )}
                                            </p>
                                        ) : (
                                            <p className="text-sm text-gray-400 mt-2">Sin calificación final</p>
                                        )}
                                        {a.comentario && (
                                            <p className="text-xs text-gray-600 mt-2 bg-gray-50 rounded px-3 py-2">
                                                Comentario ({a.comentario_fecha}): {a.comentario}
                                            </p>
                                        )}
                                    </div>
                                    <div className="flex flex-col gap-2 items-end">
                                        <Link href={`/vicerrectora/academicos/${a.nomina_id}`}
                                            className="text-xs text-[#0096D6] hover:underline">
                                            Ver expediente
                                        </Link>
                                        {a.evaluacion_id && comentandoId !== a.evaluacion_id ? (
                                            <button onClick={() => setComentandoId(a.evaluacion_id)}
                                                className="text-xs font-medium text-[#1B2D6B] hover:underline">
                                                Agregar comentario
                                            </button>
                                        ) : null}
                                    </div>
                                </div>
                                {comentandoId === a.evaluacion_id && (
                                    <div className="mt-3 pt-3 border-t border-gray-100 space-y-2">
                                        <textarea rows={2} value={form.data.comentario}
                                            onChange={e => form.setData('comentario', e.target.value)}
                                            placeholder="Comentario de la vicerrectoría..."
                                            className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm resize-none" />
                                        <div className="flex gap-2">
                                            <button onClick={() => enviarComentario(a.evaluacion_id)}
                                                disabled={form.processing || form.data.comentario.length < 5}
                                                className="text-xs bg-[#1B2D6B] text-white px-3 py-1.5 rounded disabled:opacity-50">
                                                Guardar
                                            </button>
                                            <button onClick={() => setComentandoId(null)}
                                                className="text-xs text-gray-400">Cancelar</button>
                                        </div>
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>
                )}
            </AppLayout>
        </>
    );
}
