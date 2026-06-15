import { Head, useForm, usePage } from '@inertiajs/react';
import { useMemo } from 'react';
import AppLayout from '@/Layouts/AppLayout';

const CAMPOS = [
    { key: 'pct_docencia',       label: 'Actividades de Docencia' },
    { key: 'pct_investigacion',  label: 'Actividades de Investigación' },
    { key: 'pct_extension',      label: 'Extensión y Vinculación' },
    { key: 'pct_administracion', label: 'Administración Académica' },
];

export default function DeclaracionApa({ periodo, nomina, semestre, semestreLabel, yaDeclarado, fechaCierre, datos }) {
    const { flash } = usePage().props;

    const form = useForm({
        semestre:           semestre,
        pct_docencia:       datos?.pct_docencia ?? '',
        pct_investigacion:  datos?.pct_investigacion ?? '',
        pct_extension:      datos?.pct_extension ?? '',
        pct_administracion: datos?.pct_administracion ?? '',
    });

    const total = useMemo(() => {
        return CAMPOS.reduce((acc, c) => acc + (parseFloat(form.data[c.key]) || 0), 0);
    }, [form.data]);

    const totalOk = Math.abs(total - 100) < 0.01 && total > 0;

    function submit(e) {
        e.preventDefault();
        form.post('/academico/declaracion-apa');
    }

    if (!periodo) {
        return (
            <AppLayout title="Declaración APA">
                <div className="max-w-lg mx-auto">
                    <p className="text-sm text-gray-400">No hay período activo.</p>
                </div>
            </AppLayout>
        );
    }

    return (
        <>
            <Head title={`Declaración APA — ${semestreLabel}`} />
            <AppLayout title={`Declaración APA — ${semestreLabel}`}>
                <div className="max-w-lg mx-auto space-y-4">

                    {flash?.success && (
                        <div className="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                            {flash.success}
                        </div>
                    )}

                    {flash?.error && (
                        <div className="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                            {flash.error}
                        </div>
                    )}

                    {/* Información del período y semestre */}
                    <div className="bg-white rounded-xl border border-gray-200 p-4">
                        <p className="text-xs text-gray-500">
                            Período: {periodo.nombre}
                        </p>
                        {fechaCierre && (
                            <p className="text-xs text-gray-500 mt-1">
                                Fecha de cierre: {fechaCierre}
                            </p>
                        )}
                    </div>

                    {/* Formulario */}
                    <div className="bg-white rounded-xl border border-gray-200 p-6">
                        <h2 className="text-base font-semibold text-gray-900">
                            {semestreLabel}
                        </h2>
                        <p className="text-sm text-gray-500 mt-1">
                            Ingresa el porcentaje de tiempo para cada área según lo acordado con tu director de departamento.
                        </p>

                        {yaDeclarado ? (
                            <div className="mt-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                                Este semestre ya fue confirmado.
                            </div>
                        ) : (
                            <form onSubmit={submit} className="mt-6 space-y-4">
                                {CAMPOS.map(c => (
                                    <div key={c.key}>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">
                                            {c.label}
                                        </label>
                                        <div className="flex items-center gap-2">
                                            <input
                                                type="number"
                                                min={0}
                                                max={100}
                                                step={0.01}
                                                value={form.data[c.key]}
                                                onChange={e => form.setData(c.key, e.target.value)}
                                                className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1B2D6B]/30"
                                                disabled={form.processing}
                                            />
                                            <span className="text-sm text-gray-400 shrink-0">%</span>
                                        </div>
                                        {form.errors[c.key] && (
                                            <p className="text-xs text-red-600 mt-1">{form.errors[c.key]}</p>
                                        )}
                                    </div>
                                ))}

                                <div className={`rounded-lg px-4 py-3 text-sm font-medium ${
                                    totalOk ? 'bg-green-50 text-green-800 border border-green-200'
                                            : 'bg-red-50 text-red-700 border border-red-200'
                                }`}>
                                    Total: {total.toFixed(2)}%
                                    {!totalOk && total > 0 && (
                                        <span className="block text-xs font-normal mt-0.5">Debe sumar exactamente 100%</span>
                                    )}
                                </div>

                                <p className="text-xs text-gray-400">
                                    Una vez confirmado no puede modificarse. Si necesitas cambios, contacta al analista CCDA.
                                </p>

                                <button
                                    type="submit"
                                    disabled={form.processing || !totalOk}
                                    className="w-full bg-[#1B2D6B] text-white text-sm font-medium py-2.5 rounded-lg hover:bg-[#152558] disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                >
                                    {form.processing ? 'Confirmando...' : `Confirmar ${semestreLabel}`}
                                </button>
                            </form>
                        )}
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
