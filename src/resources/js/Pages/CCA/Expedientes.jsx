import { Head, Link, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

const ESTADO_BADGE = {
    carga_cerrada: 'bg-blue-100 text-blue-700',
    en_evaluacion: 'bg-purple-100 text-purple-700',
    evaluado:      'bg-green-100 text-green-700',
};

export default function Expedientes({ periodo, expedientes, evaluacionHabilitada, fechaAperturaEval }) {
    const { flash } = usePage().props;

    return (
        <>
            <Head title="Expedientes CCA" />
            <AppLayout title="Expedientes para Evaluación">
                {periodo && (
                    <p className="text-sm text-gray-500 -mt-4 mb-6">
                        Período: <span className="font-medium text-gray-700">{periodo.nombre} {periodo.anio}</span>
                    </p>
                )}

                {flash?.error && (
                    <div className="mb-5 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                        {flash.error}
                    </div>
                )}

                {!evaluacionHabilitada && (
                    <div className="bg-amber-50 border border-amber-200 rounded-xl p-6 text-center">
                        <p className="text-amber-800 font-semibold text-sm mb-1">
                            Período de entrega de evidencias aún vigente
                        </p>
                        <p className="text-amber-700 text-sm">
                            La evaluación se habilitará cuando cierre la etapa de carga de evidencias
                            {fechaAperturaEval && (
                                <span className="font-semibold"> ({fechaAperturaEval})</span>
                            )}.
                        </p>
                    </div>
                )}

                {evaluacionHabilitada && expedientes.length === 0 && (
                    <div className="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
                        <p className="text-gray-400 text-sm">
                            No hay expedientes validados por el secretario disponibles para evaluación.
                        </p>
                    </div>
                )}

                {evaluacionHabilitada && expedientes.length > 0 && (
                    <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="bg-gray-50 border-b border-gray-100 text-xs text-gray-500 uppercase tracking-wide">
                                    <th className="text-left px-5 py-3 font-medium">Académico</th>
                                    <th className="text-left px-5 py-3 font-medium">Facultad</th>
                                    <th className="text-left px-5 py-3 font-medium">Categoría</th>
                                    <th className="text-left px-5 py-3 font-medium">Estado</th>
                                    <th className="text-left px-5 py-3 font-medium">Mi evaluación</th>
                                    <th className="px-5 py-3" />
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {expedientes.map(exp => (
                                    <tr key={exp.id} className="hover:bg-gray-50 transition-colors">
                                        <td className="px-5 py-3.5">
                                            <p className="font-medium text-gray-900">{exp.academico.name}</p>
                                            <p className="text-xs text-gray-400">{exp.academico.rut}</p>
                                        </td>
                                        <td className="px-5 py-3.5 text-gray-600">
                                            {exp.facultad ?? '—'}
                                        </td>
                                        <td className="px-5 py-3.5 text-gray-600">
                                            {exp.categoria ?? '—'}
                                        </td>
                                        <td className="px-5 py-3.5">
                                            <span className={`text-xs font-semibold px-2.5 py-0.5 rounded-full ${ESTADO_BADGE[exp.estado] ?? 'bg-gray-100 text-gray-600'}`}>
                                                {exp.estado_label}
                                            </span>
                                            {exp.con_licencia && (
                                                <span className="ml-1.5 text-xs text-amber-600">· Licencia</span>
                                            )}
                                        </td>
                                        <td className="px-5 py-3.5">
                                            {exp.yo_evaluado ? (
                                                <span className="text-xs text-green-700 font-medium">✓ Registrada</span>
                                            ) : (
                                                <span className="text-xs text-gray-400">Pendiente</span>
                                            )}
                                            {exp.concepto_final && (
                                                <p className="text-xs text-gray-500 mt-0.5">
                                                    Final: {exp.concepto_final} ({exp.nota_final})
                                                </p>
                                            )}
                                        </td>
                                        <td className="px-5 py-3.5 text-right">
                                            <Link
                                                href={`/cca/expedientes/${exp.id}`}
                                                className="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg bg-[#1B2D6B] text-white hover:bg-[#152558] transition-colors"
                                            >
                                                {exp.estado === 'evaluado' && exp.yo_evaluado ? 'Ver' : 'Evaluar'}
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
