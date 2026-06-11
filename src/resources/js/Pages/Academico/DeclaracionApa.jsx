import { Head, useForm, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';

const CAMPOS = [
    { key: 'pct_docencia',       label: 'Actividades de Docencia' },
    { key: 'pct_investigacion',  label: 'Actividades de Investigación' },
    { key: 'pct_extension',      label: 'Extensión y Vinculación' },
    { key: 'pct_administracion', label: 'Administración Académica' },
];

function FormSemestre({ semestre, semestres, nomina }) {
    const s = semestres.find(s => s.numero === semestre);

    const form = useForm({
        semestre:           semestre,
        pct_docencia:       s?.datos?.pct_docencia       ?? '',
        pct_investigacion:  s?.datos?.pct_investigacion  ?? '',
        pct_extension:      s?.datos?.pct_extension      ?? '',
        pct_administracion: s?.datos?.pct_administracion ?? '',
    });

    const total = useMemo(() => {
        return CAMPOS.reduce((acc, c) => acc + (parseFloat(form.data[c.key]) || 0), 0);
    }, [form.data]);

    const totalOk = Math.abs(total - 100) < 0.01 && total > 0;

    function submit(e) {
        e.preventDefault();
        form.post('/academico/declaracion-apa');
    }

    return (
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
                className="w-full bg-[#1B2D6B] text-white text-sm font-medium py-2.5 rounded-lg hover:bg-[#152558] disabled:opacity-50"
            >
                {form.processing ? 'Confirmando...' : `Confirmar ${semestres.find(s => s.numero === semestre)?.label}`}
            </button>
        </form>
    );
}

export default function DeclaracionApa({ periodo, nomina, semestres, semestre_activo, semestre_total }) {
    const { flash } = usePage().props;
    const [viendoSemestre, setViendoSemestre] = useState(semestre_activo);

    if (!periodo) {
        return (
            <AppLayout title="Declaración APA">
                <p className="text-sm text-gray-400">No hay período activo.</p>
            </AppLayout>
        );
    }

    const confirmados = semestres.filter(s => s.confirmado).length;

    return (
        <>
            <Head title="Declaración APA" />
            <AppLayout title="Declaración APA">
                <div className="max-w-lg mx-auto space-y-4">

                    {flash?.success && (
                        <div className="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                            {flash.success}
                        </div>
                    )}

                    {/* Progreso */}
                    <div className="bg-white rounded-xl border border-gray-200 p-4">
                        <p className="text-xs text-gray-500 mb-3">
                            Período: {periodo.nombre} &nbsp;·&nbsp;
                            {confirmados} de {semestre_total} semestres confirmados
                        </p>
                        <div className="flex gap-2">
                            {semestres.map(s => (
                                <button
                                    key={s.numero}
                                    type="button"
                                    onClick={() => !s.confirmado && setViendoSemestre(s.numero)}
                                    className={`flex-1 py-1.5 text-xs rounded-md font-medium transition-colors
                                        ${s.confirmado
                                            ? 'bg-green-100 text-green-700 cursor-default'
                                            : viendoSemestre === s.numero
                                                ? 'bg-[#1B2D6B] text-white'
                                                : 'bg-gray-100 text-gray-500 hover:bg-gray-200'
                                        }`}
                                >
                                    {s.confirmado ? '✓ ' : ''}{s.label}
                                </button>
                            ))}
                        </div>
                    </div>

                    {/* Formulario del semestre activo */}
                    <div className="bg-white rounded-xl border border-gray-200 p-6">
                        <h2 className="text-base font-semibold text-gray-900">
                            {semestres.find(s => s.numero === viendoSemestre)?.label}
                        </h2>
                        <p className="text-sm text-gray-500 mt-1">
                            Ingresa el porcentaje de tiempo para cada área según lo acordado con tu director de departamento.
                        </p>

                        {semestres.find(s => s.numero === viendoSemestre)?.confirmado ? (
                            <div className="mt-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                                Este semestre ya fue confirmado.
                            </div>
                        ) : (
                            <FormSemestre
                                semestre={viendoSemestre}
                                semestres={semestres}
                                nomina={nomina}
                            />
                        )}
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
