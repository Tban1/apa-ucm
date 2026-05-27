import { Head, useForm, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function Informe({ nomina, categorias, informe }) {
    const { flash } = usePage().props;

    const initial = {
        puntaje: informe?.puntaje ?? 0,
        observacion_general: informe?.observacion_general ?? '',
    };
    categorias.forEach(c => {
        initial[`obs_${c.slug}`] = informe?.observaciones?.[c.slug] ?? '';
    });

    const { data, setData, post, processing, errors } = useForm(initial);

    function submit(e) {
        e.preventDefault();
        post(`/jefe/academicos/${nomina.id}/informe`);
    }

    const calificacionLabel = (pts) => {
        if (pts >= 80) return { label: 'Muy Bueno',  cls: 'text-green-700 bg-green-50 border-green-200' };
        if (pts >= 60) return { label: 'Bueno',      cls: 'text-blue-700 bg-blue-50 border-blue-200' };
        if (pts >= 40) return { label: 'Aceptable',  cls: 'text-amber-700 bg-amber-50 border-amber-200' };
        return              { label: 'Deficiente',   cls: 'text-red-700 bg-red-50 border-red-200' };
    };

    const calif = calificacionLabel(data.puntaje);

    return (
        <>
            <Head title={`Informe Jefatura — ${nomina.academico.name}`} />
            <AppLayout title="Informe de Jefatura">

                {flash?.success && (
                    <div className="mb-5 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                        {flash.success}
                    </div>
                )}

                {/* Datos del académico */}
                <div className="bg-white rounded-xl border border-gray-200 p-5 mb-6">
                    <p className="font-semibold text-gray-900">{nomina.academico.name}</p>
                    <p className="text-sm text-gray-500">{nomina.academico.rut} · {nomina.academico.email}</p>
                    {nomina.academico.departamento && (
                        <p className="text-sm text-gray-500">{nomina.academico.departamento}</p>
                    )}
                </div>

                <form onSubmit={submit} className="space-y-6">

                    {/* Puntaje global */}
                    <div className="bg-white rounded-xl border border-gray-200 p-6">
                        <h2 className="font-semibold text-gray-800 mb-4">Puntaje global de jefatura</h2>
                        <div className="flex items-center gap-6">
                            <div className="flex-1">
                                <input
                                    type="range"
                                    min="0" max="100"
                                    value={data.puntaje}
                                    onChange={e => setData('puntaje', Number(e.target.value))}
                                    className="w-full accent-[#1B2D6B]"
                                />
                                <div className="flex justify-between text-xs text-gray-400 mt-1">
                                    <span>0</span><span>100</span>
                                </div>
                            </div>
                            <div className={`text-center border rounded-xl px-5 py-3 min-w-[120px] ${calif.cls}`}>
                                <p className="text-2xl font-bold">{data.puntaje}</p>
                                <p className="text-xs font-semibold mt-0.5">{calif.label}</p>
                            </div>
                        </div>
                        {errors.puntaje && (
                            <p className="text-xs text-red-600 mt-2">{errors.puntaje}</p>
                        )}
                    </div>

                    {/* Observaciones por categoría */}
                    <div className="bg-white rounded-xl border border-gray-200 p-6">
                        <h2 className="font-semibold text-gray-800 mb-4">Observaciones por área</h2>
                        <div className="space-y-4">
                            {categorias.map(c => (
                                <div key={c.id}>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        {c.nombre}
                                    </label>
                                    <textarea
                                        rows={2}
                                        value={data[`obs_${c.slug}`]}
                                        onChange={e => setData(`obs_${c.slug}`, e.target.value)}
                                        placeholder="Observación opcional..."
                                        className="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#0096D6]/30 resize-none"
                                    />
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* Observación general */}
                    <div className="bg-white rounded-xl border border-gray-200 p-6">
                        <h2 className="font-semibold text-gray-800 mb-3">Observación general</h2>
                        <textarea
                            rows={4}
                            value={data.observacion_general}
                            onChange={e => setData('observacion_general', e.target.value)}
                            placeholder="Comentario global sobre el desempeño del académico..."
                            className="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#0096D6]/30 resize-none"
                        />
                        {errors.observacion_general && (
                            <p className="text-xs text-red-600 mt-1">{errors.observacion_general}</p>
                        )}
                    </div>

                    {/* Acciones */}
                    <div className="flex items-center justify-between">
                        <a
                            href={`/jefe/academicos/${nomina.id}/imprimir`}
                            target="_blank"
                            rel="noreferrer"
                            className={`text-sm font-medium text-gray-600 hover:text-gray-900 underline ${!informe ? 'pointer-events-none opacity-40' : ''}`}
                        >
                            Imprimir / Exportar PDF
                        </a>
                        <button
                            type="submit"
                            disabled={processing}
                            className="bg-[#1B2D6B] hover:bg-[#152558] disabled:opacity-50 text-white text-sm font-semibold px-6 py-2.5 rounded-lg transition-colors"
                        >
                            {informe ? 'Actualizar informe' : 'Guardar informe'}
                        </button>
                    </div>
                </form>
            </AppLayout>
        </>
    );
}
