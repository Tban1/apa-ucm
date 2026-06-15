import { Head, useForm, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function ConfiguracionSemestres({ periodo, semestres }) {
    const { flash } = usePage().props;

    const form = useForm({
        fecha_cierre_s1: semestres?.s1?.fecha_cierre ?? '',
        fecha_cierre_s2: semestres?.s2?.fecha_cierre ?? '',
    });

    function submit(e) {
        e.preventDefault();
        form.post('/admin/configuracion-semestres');
    }

    if (!periodo) {
        return (
            <AppLayout title="Configuración de Semestres">
                <div className="max-w-2xl mx-auto">
                    <div className="bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-3 text-sm text-yellow-800">
                        No hay período activo. Debes crear un período antes de configurar los semestres.
                    </div>
                </div>
            </AppLayout>
        );
    }

    return (
        <>
            <Head title="Configuración de Semestres" />
            <AppLayout title="Configuración de Semestres">
                <div className="max-w-2xl mx-auto space-y-4">

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

                    {/* Información del período */}
                    <div className="bg-white rounded-xl border border-gray-200 p-4">
                        <p className="text-sm font-medium text-gray-900">
                            Período: {periodo.nombre}
                        </p>
                        <p className="text-xs text-gray-500 mt-1">
                            Define las fechas de cierre del I y II Semestre para la declaración APA.
                        </p>
                    </div>

                    {/* Formulario */}
                    <div className="bg-white rounded-xl border border-gray-200 p-6">
                        <h2 className="text-base font-semibold text-gray-900 mb-4">
                            Fechas de Cierre de Semestres
                        </h2>

                        <form onSubmit={submit} className="space-y-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Fecha cierre I Semestre
                                </label>
                                <input
                                    type="date"
                                    value={form.data.fecha_cierre_s1}
                                    onChange={e => form.setData('fecha_cierre_s1', e.target.value)}
                                    className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1B2D6B]/30"
                                    disabled={form.processing}
                                />
                                {form.errors.fecha_cierre_s1 && (
                                    <p className="text-xs text-red-600 mt-1">{form.errors.fecha_cierre_s1}</p>
                                )}
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Fecha cierre II Semestre
                                </label>
                                <input
                                    type="date"
                                    value={form.data.fecha_cierre_s2}
                                    onChange={e => form.setData('fecha_cierre_s2', e.target.value)}
                                    className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1B2D6B]/30"
                                    disabled={form.processing}
                                />
                                {form.errors.fecha_cierre_s2 && (
                                    <p className="text-xs text-red-600 mt-1">{form.errors.fecha_cierre_s2}</p>
                                )}
                                <p className="text-xs text-gray-500 mt-1">
                                    La fecha del II Semestre debe ser posterior a la del I Semestre
                                </p>
                            </div>

                            <div className="flex justify-end gap-3 pt-4 border-t border-gray-200">
                                <button
                                    type="button"
                                    onClick={() => window.history.back()}
                                    className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                                    disabled={form.processing}
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={form.processing}
                                    className="px-4 py-2 text-sm font-medium text-white bg-[#1B2D6B] rounded-lg hover:bg-[#152558] disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                >
                                    {form.processing ? 'Guardando...' : 'Guardar configuración'}
                                </button>
                            </div>
                        </form>
                    </div>

                    {/* Información adicional */}
                    <div className="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 text-sm text-blue-800">
                        <p className="font-medium">Nota importante:</p>
                        <ul className="list-disc list-inside mt-2 space-y-1 text-xs">
                            <li>Los académicos solo podrán declarar el I Semestre mientras este plazo esté abierto</li>
                            <li>Una vez cerrado el I Semestre, se habilitará automáticamente la declaración del II Semestre</li>
                            <li>El cierre del II Semestre marca el fin del período de declaración APA</li>
                        </ul>
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
