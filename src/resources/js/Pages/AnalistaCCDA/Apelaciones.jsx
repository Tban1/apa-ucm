import { Head, Link, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function Apelaciones({ periodo, pendientes }) {
    const { flash } = usePage().props;

    return (
        <>
            <Head title="Apelaciones CCDA" />
            <AppLayout title="Apelaciones 2do Nivel — CCDA">
                {periodo && (
                    <p className="text-sm text-gray-500 -mt-4 mb-6">
                        Período: <span className="font-medium text-gray-700">{periodo.nombre} {periodo.anio}</span>
                    </p>
                )}

                {flash?.success && (
                    <div className="mb-5 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                        {flash.success}
                    </div>
                )}

                <div className="mb-5 rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
                    Expedientes cuya calificación original fue <strong>Regular</strong> o <strong>Deficiente</strong> y cuya apelación fue derivada al 2do nivel CCDA por el secretario.
                </div>

                {pendientes.length === 0 ? (
                    <div className="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
                        <p className="text-gray-400 text-sm">No hay apelaciones CCDA pendientes.</p>
                    </div>
                ) : (
                    <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="bg-gray-50 border-b border-gray-100 text-xs text-gray-500 uppercase tracking-wide">
                                    <th className="text-left px-5 py-3 font-medium">Académico</th>
                                    <th className="text-left px-5 py-3 font-medium">Facultad</th>
                                    <th className="text-left px-5 py-3 font-medium">Categoría</th>
                                    <th className="text-left px-5 py-3 font-medium">Calif. original</th>
                                    <th className="text-left px-5 py-3 font-medium">Estado CCDA</th>
                                    <th className="px-5 py-3" />
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {pendientes.map(exp => (
                                    <tr key={exp.id} className="hover:bg-gray-50 transition-colors">
                                        <td className="px-5 py-3.5">
                                            <p className="font-medium text-gray-900">{exp.academico.name}</p>
                                            <p className="text-xs text-gray-400">{exp.academico.rut}</p>
                                        </td>
                                        <td className="px-5 py-3.5 text-gray-600">{exp.facultad ?? '—'}</td>
                                        <td className="px-5 py-3.5 text-gray-600">{exp.categoria ?? '—'}</td>
                                        <td className="px-5 py-3.5">
                                            {exp.calificacion_original ? (
                                                <>
                                                    <span className="text-xs font-semibold px-2 py-0.5 rounded-full bg-red-100 text-red-700">
                                                        {exp.calificacion_original.concepto}
                                                    </span>
                                                    <span className="text-xs text-gray-400 ml-1">
                                                        ({exp.calificacion_original.nota_final})
                                                    </span>
                                                </>
                                            ) : '—'}
                                        </td>
                                        <td className="px-5 py-3.5">
                                            {exp.ya_resuelta ? (
                                                <span className="text-xs font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-700">
                                                    Resuelto: {exp.concepto_resolucion}
                                                </span>
                                            ) : (
                                                <span className="text-xs font-semibold px-2 py-0.5 rounded-full bg-purple-100 text-purple-700">
                                                    Pendiente
                                                </span>
                                            )}
                                        </td>
                                        <td className="px-5 py-3.5 text-right">
                                            <Link
                                                href={`/analista/apelaciones/${exp.id}`}
                                                className="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg bg-[#1B2D6B] text-white hover:bg-[#152558] transition-colors"
                                            >
                                                {exp.ya_resuelta ? 'Ver' : 'Evaluar'}
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </AppLayout>
        </>
    );
}
