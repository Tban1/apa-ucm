import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { useState } from 'react';

const BADGE = {
    verde:    'bg-green-100 text-green-800',
    amarillo: 'bg-yellow-100 text-yellow-800',
    rojo:     'bg-red-100 text-red-800',
    gris:     'bg-gray-100 text-gray-600',
};

function NotaBadge({ academico }) {
    if (academico.sin_calificacion) {
        return <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${BADGE.gris}`}>S/C</span>;
    }
    if (!academico.nota_final) {
        return <span className={`text-xs px-2 py-0.5 rounded-full ${BADGE.gris}`}>Pendiente</span>;
    }
    const color = academico.nota_vencida ? BADGE.rojo : BADGE.verde;
    return (
        <span className={`text-xs px-2 py-0.5 rounded-full font-semibold ${color}`}>
            {Number(academico.nota_final).toFixed(1)}
            {academico.nota_vencida && ' ⚠'}
        </span>
    );
}

function ModalComentario({ academico, onClose }) {
    const { data, setData, post, processing, errors } = useForm({
        comentario: academico.comentario ?? '',
    });

    function submit(e) {
        e.preventDefault();
        post(route('vicerrectora.comentar', academico.id), {
            onSuccess: onClose,
        });
    }

    return (
        <div className="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div className="bg-white rounded-xl shadow-lg w-full max-w-lg p-6 space-y-4">
                <h3 className="text-sm font-semibold text-gray-800">
                    Comentario — {academico.academico}
                </h3>
                <form onSubmit={submit} className="space-y-3">
                    <textarea
                        value={data.comentario}
                        onChange={e => setData('comentario', e.target.value)}
                        rows={5}
                        placeholder="Ingrese su comentario..."
                        className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1B2D6B]/30"
                    />
                    {errors.comentario && <p className="text-xs text-red-600">{errors.comentario}</p>}
                    <div className="flex gap-3 justify-end">
                        <button type="button" onClick={onClose}
                            className="text-sm text-gray-500 hover:text-gray-700">
                            Cancelar
                        </button>
                        <button type="submit" disabled={processing}
                            className="bg-[#1B2D6B] text-white text-sm px-4 py-2 rounded-lg hover:bg-[#152558] disabled:opacity-60">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}

export default function VicerrectoraDashboard({ periodo, academicos }) {
    const [filtroFacultad, setFiltroFacultad] = useState('');
    const [modalAcademico, setModalAcademico] = useState(null);

    const filtrados = filtroFacultad
        ? academicos.filter(a => a.facultad === filtroFacultad)
        : academicos;

    const facultades = [...new Set(academicos.map(a => a.facultad).filter(Boolean))].sort();

    return (
        <>
            <Head title="Panel Vicerrectoría" />
            <AppLayout title="Panel Vicerrectoría Académica">

                {!periodo && (
                    <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm text-yellow-800">
                        No hay un período académico activo.
                    </div>
                )}

                {periodo && (
                    <div className="space-y-4">
                        <div className="flex items-center gap-4">
                            <p className="text-sm text-gray-500">
                                Período: <span className="font-medium text-gray-800">{periodo.nombre}</span>
                            </p>
                            <select
                                value={filtroFacultad}
                                onChange={e => setFiltroFacultad(e.target.value)}
                                className="ml-auto text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-[#1B2D6B]/30"
                            >
                                <option value="">Todas las facultades</option>
                                {facultades.map(f => (
                                    <option key={f} value={f}>{f}</option>
                                ))}
                            </select>
                        </div>

                        <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                            <table className="w-full text-sm">
                                <thead className="bg-gray-50 border-b border-gray-100">
                                    <tr>
                                        <th className="text-left px-4 py-3 font-medium text-gray-600">Académico</th>
                                        <th className="text-left px-4 py-3 font-medium text-gray-600">Facultad</th>
                                        <th className="text-left px-4 py-3 font-medium text-gray-600">Categoría</th>
                                        <th className="text-center px-4 py-3 font-medium text-gray-600">Nota</th>
                                        <th className="text-left px-4 py-3 font-medium text-gray-600">Vigencia</th>
                                        <th className="text-left px-4 py-3 font-medium text-gray-600">Comentario</th>
                                        <th className="px-4 py-3" />
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-50">
                                    {filtrados.map(a => (
                                        <tr key={a.id} className="hover:bg-gray-50/60">
                                            <td className="px-4 py-3 font-medium text-gray-800">{a.academico}</td>
                                            <td className="px-4 py-3 text-gray-600">{a.facultad}</td>
                                            <td className="px-4 py-3 text-gray-600 capitalize">{a.categoria}</td>
                                            <td className="px-4 py-3 text-center">
                                                <NotaBadge academico={a} />
                                            </td>
                                            <td className="px-4 py-3 text-gray-500 text-xs">
                                                {a.nota_vencida
                                                    ? <span className="text-red-600 font-medium">Vencida {a.vigente_hasta}</span>
                                                    : a.vigente_hasta ?? '—'}
                                            </td>
                                            <td className="px-4 py-3 text-gray-500 text-xs max-w-xs truncate">
                                                {a.comentario ?? <span className="italic text-gray-300">Sin comentario</span>}
                                            </td>
                                            <td className="px-4 py-3 flex gap-2 justify-end">
                                                <Link href={route('vicerrectora.expedientes.show', a.id)}
                                                    className="text-xs text-[#1B2D6B] hover:underline">
                                                    Ver expediente
                                                </Link>
                                                <button
                                                    onClick={() => setModalAcademico(a)}
                                                    className="text-xs text-gray-500 hover:text-gray-800">
                                                    Comentar
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                    {filtrados.length === 0 && (
                                        <tr>
                                            <td colSpan={7} className="px-4 py-8 text-center text-sm text-gray-400">
                                                Sin académicos para mostrar.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}

                {modalAcademico && (
                    <ModalComentario
                        academico={modalAcademico}
                        onClose={() => setModalAcademico(null)}
                    />
                )}
            </AppLayout>
        </>
    );
}
